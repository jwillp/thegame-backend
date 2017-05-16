<?php 

namespace AppBundle\Tests\Controller\Api;


use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\RequestException;

/**
* GameControllerTest
*/
class GameControllerTest extends ApiTestCase
{
    // test_method testNewAction "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testNewAction() {
        $data = array(
            'title' => 'Automated Test Generated Game',
            'description' => 'A cool desc',
            'start_date' => '2017/05/30 20:00',
            'end_date' => '2017/05/30 23:59',
        );

        // Create game response
        $response = $this->client->post($this->getBaseURI() . '/api/games/new', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $finishedData = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('id', $finishedData);
        $this->assertEquals('Automated Test Generated Game', $finishedData['title']);

        // Default should be public
        $this->assertEquals('VISIBILITY_PUBLIC', $finishedData['visibility']);
    }

    // test_method testCreateActionEvents "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testCreateActionEvents() {
        // Create game
        $data = array(
            'title' => 'Automated Test Generated Game',
            'description' => 'A cool desc',
            'start_date' => '2017/05/30 20:00',
            'end_date' => '2017/05/30 23:59',
        );

        // Create game response
        $response = $this->client->post($this->getBaseURI() . '/api/games/new', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        // Check event
        $response = $response = $this->getEventsResponse();
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].iid', 
            'USER_CREATED_GAME_EVENT'
        );
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].target.type', 
            'GAME'
        );
    }


    // test_method testNewActionWithToken "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testNewActionWithToken() {
        // Send token for authentication
        $data = array(
            'title' => 'Automated Test Generated Game',
            'description' => 'A cool desc',
            'start_date' => '2017/05/30 20:00',
            'end_date' => '2017/05/30 23:59',
        );

        // Create game response
        $response = $this->client->post($this->getBaseURI() . '/api/games/new', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);
    }

    // test_method testNewActionValidaton "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testNewActionValidaton() {
        $data = array(
            //'title' => 'Automated Test Generated Game',
            'description' => 'A cool desc',
            'start_date' => '2017/05/30 20:00',
            'end_date' => '2017/05/30 23:59',
        );

        $response = $this->client->post($this->getBaseURI() . '/api/games/new', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe'),
            'http_errors' => false
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertResponsePropertiesExist($response, array(
            'type',
            'title',
            'errors',
        ));
        $this->assertResponsePropertyEquals(
            $response, 
            'errors.title[0]', 
            'Please enter a title'
        );
    }

    // test_method testShowAction "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testShowAction() {

        $data = array(
            'title' => 'Automated Test Generated Game',
            'description' => 'A generic description',
            'start_date' => '2017/05/30 20:00',
            'end_date' => '2017/05/30 23:59',
        );

        // Create game response
        $response = $this->client->post($this->getBaseURI() . '/api/games/new', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $finishedData = json_decode($response->getBody(true), true);
        $id = $finishedData['id'];
        $response = $this->client->get(
            '/thegame/web/app_test.php/api/games/' . $id,
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    // test_method testShowActionValidation "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testShowActionValidation() {
        // Id not existing
        $response = $this->client->get($this->getBaseURI() . '/api/games/-1',[
            'http_errors' => false,
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertResponsePropertyExists($response, 'errors');
        $this->assertResponsePropertyEquals($response, 'title', 'Could not find game with id -1');
    }

    // test_method testListAction "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testListAction() {
        // Create loads of games
        for ($i=0; $i < 25; $i++) { 
            $data = array(
                'title' => 'Game #'.$i,
                'description' => 'A generic description',
                'start_date' => '2017/05/30 20:00',
                'end_date' => '2017/05/30 23:59',
            );

            $this->client->post($this->getBaseURI() . '/api/games/new', [
                'body' => json_encode($data),
                'http_errors' => false,
                'headers' => $this->getAuthorizedHeaders('joe')
            ]);
        }

        $response = $this->client->get($this->getBaseURI() . '/api/games', [
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);
        $finishedData = json_decode($response->getBody(true), true);
        $this->assertEquals(200, $response->getStatusCode());
        
        // Pagination

        $this->assertResponsePropertyEquals($response, 'items[5].title', 'Game #5');
        $this->assertResponsePropertyEquals($response, 'current_page', 1);
        $this->assertResponsePropertyEquals($response, 'previous_page', null);
        $this->assertResponsePropertyEquals($response, 'next_page', 2);
        $this->assertResponsePropertyEquals($response, 'nb_pages', 3);
        $this->assertResponsePropertyEquals($response, 'per_page', 10);
        $this->assertResponsePropertyEquals($response, 'count', 10);
        $this->assertResponsePropertyEquals($response, 'total', 25);
        $this->assertResponsePropertyEquals($response, 'items[0].created_by.username', 'joe');
        $this->assertResponsePropertyEquals($response, 'items[0].administrators[0].username', 'joe');
        
        // Test next link (2)
        $next = $this->readResponseProperty($response, 'links.next');
        $response = $this->client->get($next,  [
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 'items[5].title', 'Game #15');
        $this->assertResponsePropertyEquals($response, 'current_page', 2);
        $this->assertResponsePropertyEquals($response, 'previous_page', 1);
        $this->assertResponsePropertyEquals($response, 'next_page', 3);
        $this->assertResponsePropertyEquals($response, 'nb_pages', 3);
        $this->assertResponsePropertyEquals($response, 'per_page', 10);
        $this->assertResponsePropertyEquals($response, 'count', 10);
        $this->assertResponsePropertyEquals($response, 'total', 25);

        // Test next link (3)
        $next = $this->readResponseProperty($response, 'links.next');
        $response = $this->client->get($next,  [
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 'items[4].title', 'Game #24');
        $this->assertResponsePropertyEquals($response, 'current_page', 3);
        $this->assertResponsePropertyEquals($response, 'previous_page', 2);
        $this->assertResponsePropertyEquals($response, 'next_page', null);
        $this->assertResponsePropertyEquals($response, 'nb_pages', 3);
        $this->assertResponsePropertyEquals($response, 'per_page', 10);
        $this->assertResponsePropertyEquals($response, 'count', 5);
        $this->assertResponsePropertyEquals($response, 'total', 25);

        // Test previous link (2)
        $previous = $this->readResponseProperty($response, 'links.prev');
        $response = $this->client->get($previous,  [
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 'items[5].title', 'Game #15');
        $this->assertResponsePropertyEquals($response, 'current_page', 2);
        $this->assertResponsePropertyEquals($response, 'previous_page', 1);
        $this->assertResponsePropertyEquals($response, 'next_page', 3);
        $this->assertResponsePropertyEquals($response, 'nb_pages', 3);
        $this->assertResponsePropertyEquals($response, 'per_page', 10);
        $this->assertResponsePropertyEquals($response, 'count', 10);
        $this->assertResponsePropertyEquals($response, 'total', 25);
    }

    // test_method testUpdateAction "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testUpdateAction() {
        // Create resource
        $data = array(
            'title' => 'Automated Test Generated Game #1',
            'description' => 'A generic description',
            'start_date' => '2017/05/30 20:00',
            'end_date' => '2017/05/30 23:59',
        );

        $response = $this->client->post($this->getBaseURI() . '/api/games/new', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $data = json_decode($response->getBody(true), true);

        // Update resource
        $data['title'] = 'Updated Title';
        $id = $data['id'];
        $response = $this->client->put($this->getBaseURI() . '/api/games/'. $id, [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);
        $data = json_decode($response->getBody(true), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Updated Title', $data['title']);
    }

    // test_method testInvalidJson "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testInvalidJson() {
        $invalidBody = '
        {
            "title" : "Missing comma -->"
            "description": "Generic description",
            "start_date": "2017/05/30 20:00",
            "end_date": "2017/05/30 23:59"
        }';

        $response = $this->client->post($this->getBaseURI() . '/api/games/new', [
            'body' => $invalidBody,
            'http_errors' => false,
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 
            'type', 'invalid_request_body_format');
    }

    // test_method testDeleteAction "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testDeleteAction() {
        $data = array(
            'title' => 'Automated Test Generated Game',
            'description' => 'A cool desc',
            'start_date' => '2017/05/30 20:00',
            'end_date' => '2017/05/30 23:59',
        );

        // Create game response
        $response = $this->client->post($this->getBaseURI() . '/api/games/new', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $data = json_decode($response->getBody(true), true);
        $id = $data['id'];

        // Try to delete
        $response = $this->client->delete(
            '/thegame/web/app_test.php/api/games/' . $id,
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        $this->assertEquals(200, $response->getStatusCode());


        // Verify deleted flag
        $response = $this->client->get(
            '/thegame/web/app_test.php/api/games/' . $id,
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        $data = json_decode($response->getBody(true), true);
        $this->assertTrue($data['deleted']);
    }

    // test_method testRequireAuthentication "src/AppBundle/Tests/Controller/Api/GameControllerTest"
    public function testRequireAuthentication() {

        // No authentication data
        $response = $this->client->post($this->getBaseURI() . '/api/games/new', [
            'body' => '[]',
            'http_errors' => false,
            //'headers' => $this->getAuthorizedHeaders('joe')
        ]);
        // Unauthorized
        $this->assertEquals(401, $response->getStatusCode());
    }


    // UTIL
     /**
     * Calls API for latest events and return response
     */
    private function getEventsResponse() {
        return $this->client->get(
            '/thegame/web/app_test.php/api/events',
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
    }
}