<?php 

namespace AppBundle\Tests\Controller\Api;


use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\RequestException;

/**
* TokenContollerTest
*/
class TokenContollerTest extends ApiTestCase
{
    // test_method testCreateTokenAction "src/AppBundle/Tests/Controller/Api/TokenContollerTest"
    public function testCreateTokenAction() {
        $response = $this->client->post('/thegame/web/app_test.php/api/tokens/', 
            [
            'auth' => ['joe', 'pass']
            ]
        );
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyExists(
            $response,
            'token'
        );
    }

    // test_method testCreateTokenActionValidation "src/AppBundle/Tests/Controller/Api/TokenContollerTest"
    public function testCreateTokenActionValidation() {
        // Try with wrong password
        $response = $this->client->post('/thegame/web/app_test.php/api/tokens/', 
            [
            'auth' => ['joe', 'wrong_pass'],
            'http_errors' => false
            ]
        );
        $this->assertEquals(401, $response->getStatusCode());
        //$this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->assertResponsePropertyEquals($response, 'title', 'Unauthorized');
    }
}