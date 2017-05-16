<?php 

namespace AppBundle\Tests\Controller\Api;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Exception;
use GuzzleHttp\Client;
use Guzzle\Http\Message\AbstractMessage;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
*  Api Test case
*/
abstract class ApiTestCase extends KernelTestCase
{
    private static $staticClient;

    /**
     * @var History
     */
    private static $history;

    /**
     * Client handlers
     */
    protected static $container = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var FormatterHelper
     */
    private $formatterHelper;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * @var ConsoleOutput
     */
    private $output;

    public static function setUpBeforeClass() {
        self::$history = Middleware::history(self::$container);
        $stack = HandlerStack::create();
        // Add the history middleware to the handler stack.
        $stack->push(self::$history);

        self::$staticClient = new Client([
            'base_uri' => 'http://localhost',
            'defaults' => [
                'exceptions' => false
            ],
            'handler' => $stack,
        ]);


        self::bootKernel();
    }

    protected function setUp() {
        $this->client = self::$staticClient;
        $this->purgeDatabase();

        // create a basic user
        $this->createUser('joe', 'pass');
    }
    
    /**
     * Clean up Kernel usage in this test.
     */
    protected function tearDown() {
        // purposefully not calling parent class, which shuts down the kernel
    }

    protected function getService($id) {
        return self::$kernel->getContainer()->get($id);
    }

    /**
     * Purges the test database
     */
    private function purgeDatabase() {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    /**
     * Called when a test fails
     */
    protected function onNotSuccessfulTest($e) {
        if (self::$history && $lastResponse = end(self::$container)) {
            $this->printDebug('');
            $this->printDebug('<error>Failure!</error> when making the following request:');
            $this->printLastRequestUrl();
            $this->printDebug('');
            $this->debugResponse($lastResponse['response']);
        }
        throw $e;
    }

    protected function getAuthorizedHeaders($username, $headers = array()) {
        $token = $this->getService('lexik_jwt_authentication.encoder')
                      ->encode(['username' => $username]);
        $headers['Authorization'] = 'Bearer '.$token;
        return $headers;
    }

    /**
     * Creates a user
     */
    public function createUser($username, $password = 'password') {
        $data = array(
            'username' => $username,
            'email' => $username . '@email.com',
            'plain_password' => $password
        );

        // Create game response
        $response = $this->client->post('/thegame/web/app_test.php/api/users/register', 
            [
            'body' => json_encode($data)
            ]
        );

        return json_decode($response->getBody(true), true);
    }


    /**
     * Asserts the array of property names are in the JSON response
     *
     * @param $response
     * @param array $expectedProperties
     * @throws \Exception
     */
    public function assertResponsePropertiesExist($response, array $expectedProperties)
    {
        foreach ($expectedProperties as $propertyPath) {
            // this will blow up if the property doesn't exist
            $this->readResponseProperty($response, $propertyPath);
        }
    }
    /**
     * Asserts the specific propertyPath is in the JSON response
     *
     * @param $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @throws \Exception
     */
    public function assertResponsePropertyExists($response, $propertyPath)
    {
        // this will blow up if the property doesn't exist
        $this->readResponseProperty($response, $propertyPath);
    }
    /**
     * Asserts the given property path does *not* exist
     *
     * @param $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @throws \Exception
     */
    public function assertResponsePropertyDoesNotExist($response, $propertyPath)
    {
        try {
            // this will blow up if the property doesn't exist
            $this->readResponseProperty($response, $propertyPath);
            $this->fail(sprintf('Property "%s" exists, but it should not', $propertyPath));
        } catch (RuntimeException $e) {
            // cool, it blew up
            // this catches all errors (but only errors) from the PropertyAccess component
        }
    }
    /**
     * Asserts the response JSON property equals the given value
     *
     * @param $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @param mixed $expectedValue
     * @throws \Exception
     */
    public function assertResponsePropertyEquals($response, $propertyPath, $expectedValue)
    {
        $actual = $this->readResponseProperty($response, $propertyPath);
        $this->assertEquals(
            $expectedValue,
            $actual,
            sprintf(
                'Property "%s": Expected "%s" but response was "%s"',
                $propertyPath,
                $expectedValue,
                var_export($actual, true)
            )
        );
    }
    /**
     * Asserts the response property is an array
     *
     * @param $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @throws \Exception
     */
    public function assertResponsePropertyIsArray($response, $propertyPath)
    {
        $this->assertInternalType('array', $this->readResponseProperty($response, $propertyPath));
    }
    /**
     * Asserts the given response property (probably an array) has the expected "count"
     *
     * @param $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @param integer $expectedCount
     * @throws \Exception
     */
    public function assertResponsePropertyCount($response, $propertyPath, $expectedCount)
    {
        $this->assertCount((int)$expectedCount, $this->readResponseProperty($response, $propertyPath));
    }
    /**
     * Asserts the specific response property contains the given value
     *
     * e.g. "Hello world!" contains "world"
     *
     * @param $response
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     * @param mixed $expectedValue
     * @throws \Exception
     */
    public function assertResponsePropertyContains($response, $propertyPath, $expectedValue)
    {
        $actualPropertyValue = $this->readResponseProperty($response, $propertyPath);
        $this->assertContains(
            $expectedValue,
            $actualPropertyValue,
            sprintf(
                'Property "%s": Expected to contain "%s" but response was "%s"',
                $propertyPath,
                $expectedValue,
                var_export($actualPropertyValue, true)
            )
        );
    }

    /**
     * Reads a JSON response property and returns the value
     *
     * This will explode if the value does not exist
     *
     * @param $response
     * @param string $propertyPath e.g. firstName, project[0].users.username
     * @return mixed
     * @throws \Exception
     */
    public function readResponseProperty($response, $propertyPath) {
        if ($this->accessor === null) {
            $this->accessor = PropertyAccess::createPropertyAccessor();
        }
        $data = json_decode((string)$response->getBody());
        if ($data === null) {
            throw new \Exception(sprintf(
                'Cannot read property "%s" - the response is invalid (is it HTML?)',
                $propertyPath
            ));
        }
        try {
            return $this->accessor->getValue($data, $propertyPath);
        } catch (AccessException $e) {
            // it could be a stdClass or an array of stdClass
            $values = is_array($data) ? $data : get_object_vars($data);
            throw new AccessException(sprintf(
                'Error reading property "%s" from available keys (%s)',
                $propertyPath,
                implode(', ', array_keys($values))
            ), 0, $e);
        }
    }

    protected function printLastRequestUrl() {
        $transaction = end(self::$container);
        if ($transaction) {
            $lastRequest = $transaction['request'];
            $this->printDebug(sprintf('<comment>%s</comment>: <info>%s</info>', $lastRequest->getMethod(), $lastRequest->getUri()));
        } else {
            $this->printDebug('No request was made.');
        }
    }

    protected function debugResponse($response) {
        //$this->printDebug(AbstractMessage::getStartLineAndHeaders($response));
        $body = (string) $response->getBody();
        $contentType = implode($response->getHeader('Content-Type'));
        if ($contentType == 'application/json' || strpos($contentType, '+json') !== false) {
            $data = json_decode($body);
            if ($data === null) {
                // invalid JSON!
                $this->printDebug($body);
            } else {
                // valid JSON, print it pretty
                $this->printDebug(json_encode($data, JSON_PRETTY_PRINT));
            }
        } else {
            // the response is HTML - see if we should print all of it or some of it
            $isValidHtml = strpos($body, '</body>') !== false;
            if ($isValidHtml) {
                $this->printDebug('');
                $crawler = new Crawler($body);
                // very specific to Symfony's error page
                $isError = $crawler->filter('#traces-0')->count() > 0
                    || strpos($body, 'looks like something went wrong') !== false;
                if ($isError) {
                    $this->printDebug('There was an Error!!!!');
                    $this->printDebug('');
                } else {
                    $this->printDebug('HTML Summary (h1 and h2):');
                }
                // finds the h1 and h2 tags and prints them only
                foreach ($crawler->filter('h1, h2')->extract(array('_text')) as $header) {
                    // avoid these meaningless headers
                    if (strpos($header, 'Stack Trace') !== false) {
                        continue;
                    }
                    if (strpos($header, 'Logs') !== false) {
                        continue;
                    }
                    // remove line breaks so the message looks nice
                    $header = str_replace("\n", ' ', trim($header));
                    // trim any excess whitespace "foo   bar" => "foo bar"
                    $header = preg_replace('/(\s)+/', ' ', $header);
                    if ($isError) {
                        $this->printErrorBlock($header);
                    } else {
                        $this->printDebug($header);
                    }
                }
                /*
                 * When using the test environment, the profiler is not active
                 * for performance. To help debug, turn it on temporarily in
                 * the config_test.yml file:
                 *   A) Update framework.profiler.collect to true
                 *   B) Update web_profiler.toolbar to true
                 */
                $profilerUrl = $response->getHeader('X-Debug-Token-Link');
                if ($profilerUrl) {
                    $fullProfilerUrl = $response->getHeader('Host').$profilerUrl;
                    $this->printDebug('');
                    $this->printDebug(sprintf(
                        'Profiler URL: <comment>%s</comment>',
                        $fullProfilerUrl
                    ));
                }
                // an extra line for spacing
                $this->printDebug('');
            } else {
                $this->printDebug($body);
            }
        }
    }

    /**
     * Print a message out - useful for debugging
     *
     * @param $string
     */
    protected function printDebug($string) {
        $string = $string . "\n";
        if ($this->output === null) {
           $this->output = new ConsoleOutput();
        }
       $this->output->writeln($string);
    }

    /**
     * Print a debugging message out in a big red block
     *
     * @param $string
     */
    protected function printErrorBlock($string) {
        if ($this->formatterHelper === null) {
            $this->formatterHelper = new FormatterHelper();
        }
        $output = $this->formatterHelper->formatBlock($string, 'bg=red;fg=white', true);
        $this->printDebug($output);
    }
}