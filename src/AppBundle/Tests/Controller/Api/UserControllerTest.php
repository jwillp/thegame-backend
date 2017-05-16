<?php 

namespace AppBundle\Tests\Controller\Api;


use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\RequestException;

/**
* TokenContollerTest
*/
class UserControllerTest extends ApiTestCase
{
    // test_method testRegisterAction "src/AppBundle/Tests/Controller/Api/UserControllerTest"
    public function testRegisterAction() {
        $data = array(
            'username' => 'james_bond',
            'email' => 'james.bond@mi6.co.uk',
            'plain_password' => 'password'
        );

        // Create game response
        $response = $this->client->post($this->getBaseURI() . '/api/users/register', 
            [
                'body' => json_encode($data)
            ]
        );

        $this->assertEquals(201, $response->getStatusCode());
    }

    // test_method testRegisterActionValidation "src/AppBundle/Tests/Controller/Api/UserControllerTest"
    public function testRegisterActionValidation() {
        // invalid registration
        $data = array(
            //'username'      => 'bar',
            'email'         => 'john__doe.com',
            //'plain_password' => 'password'
        );

        // Create game response
        $response = $this->client->post($this->getBaseURI() . '/api/users/register', 
            [
                'body' => json_encode($data),
                'http_errors' => false
            ]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertResponsePropertiesExist($response, array(
            'type',
            'title',
            'errors',
        ));

        $this->assertResponsePropertiesExist($response, ['errors.email[0]']);

        $this->assertResponsePropertiesExist($response, ['errors.username[0]']);

        $this->assertResponsePropertiesExist($response, ['errors.plain_password.first[0]']);
    }

    // test_method testLoginAction "src/AppBundle/Tests/Controller/Api/UserControllerTest"
    public function testLoginAction() {
        $response = $this->client->post($this->getBaseURI() . '/api/users/login', 
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

    // test_method testBadToken "src/AppBundle/Tests/Controller/Api/
    public function testBadToken() {
        $response = $this->client->post($this->getBaseURI() . '/api/users/login', 
            [
            'body' => '[]',
            'headers' => [
                    'Authorization' => 'Bearer WRONG'
                ],
            'http_errors' => false
            ]
        );
        
        $this->assertEquals(401, $response->getStatusCode());
    }
}