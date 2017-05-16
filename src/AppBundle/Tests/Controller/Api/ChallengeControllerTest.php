<?php 

namespace AppBundle\Tests\Controller\Api;


use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\RequestException;

/**
* ChallengeControllerTest
*/
class ChallengeControllerTest extends ApiTestCase
{
    // test_method testNewAction "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testNewAction() {
        // Create Game
        $game = $this->createGame();

        // Create challenge
        $data = array(
            'title' => 'Generic Challenge',
            'description' => 'Description of a generic challenge',
            'nbPoints' => 60,
            'game' => $game['id'],
        );

        // Create challlenge response
        $response = $this->client
        ->post('/thegame/web/app_test.php/api/games/'. $game['id'] .'/challenges/new', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));

        $finishedData = json_decode($response->getBody(true), true);

        $this->assertArrayHasKey('id', $finishedData);
        $this->assertEquals('Generic Challenge', $finishedData['title']);
        $this->assertEquals(60, $finishedData['nb_points']);
        $this->assertFalse($finishedData['deleted']);
    }

    // test_method testNewActionValidaton "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testNewActionValidaton() {
        // Create Game
        $game = $this->createGame();

        // Create challenge
        $data = array(
            //'title' => 'Generic Challenge',
            'description' => 'Description of a generic challenge',
            //'nbPoints' => 60,
            'game' => $game['id'],
        );

        // Create challlenge response
        $response = $this->client
        ->post('/thegame/web/app_test.php/api/games/'. $game['id'] .'/challenges/new', [
            'body' => json_encode($data),
            'http_errors' => false,
            'headers' => $this->getAuthorizedHeaders('joe')
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
        $this->assertResponsePropertyEquals(
            $response, 
            'errors.nb_points[0]', 
            'Please enter a number of points'
        );
    }

    // test_method testShowAction "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testShowAction() {
        // Create Game
        $game = $this->createGame();

        // Create challenge
        $challenge = $this->createChallenge($game['id']);
        $id = $challenge['id'];
        $response = $this->client->get(
            '/thegame/web/app_test.php/api/challenges/'.$id, 
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    // test_method testShowActionValidation "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testShowActionValidation() {
        // Id not existing
        $response = $this->client->get(
            '/thegame/web/app_test.php/api/challenges/-1',
            [
                'http_errors' => false,
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertResponsePropertyExists($response, 'errors');
        //$this->assertResponsePropertyEquals($response, 'title', 'Could not find game with id -1');
    }

    // test_method testListAction "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testListAction() {
        // Create Game
        $game = $this->createGame();

        // Create loads of challenges
        for ($i=0; $i < 25; $i++) { 
            $challenge = $this->createChallenge(
                $game['id'], 
                'Challenge #' . $i
            );
        }

        $response = $this->client->get(
            '/thegame/web/app_test.php/api/games/'. $game['id'] . '/challenges',
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        $finishedData = json_decode($response->getBody(true), true);
        $this->assertEquals(200, $response->getStatusCode());

        // Pagination

        $this->assertResponsePropertyEquals($response, 'items[5].title', 'Challenge #5');
        $this->assertResponsePropertyEquals($response, 'current_page', 1);
        $this->assertResponsePropertyEquals($response, 'previous_page', null);
        $this->assertResponsePropertyEquals($response, 'next_page', 2);
        $this->assertResponsePropertyEquals($response, 'nb_pages', 3);
        $this->assertResponsePropertyEquals($response, 'per_page', 10);
        $this->assertResponsePropertyEquals($response, 'count', 10);
        $this->assertResponsePropertyEquals($response, 'total', 25);
        
        // Test next link (2)
        $next = $this->readResponseProperty($response, 'links.next');
        $response = $this->client->get($next, [
                'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 'items[5].title', 'Challenge #15');
        $this->assertResponsePropertyEquals($response, 'current_page', 2);
        $this->assertResponsePropertyEquals($response, 'previous_page', 1);
        $this->assertResponsePropertyEquals($response, 'next_page', 3);
        $this->assertResponsePropertyEquals($response, 'nb_pages', 3);
        $this->assertResponsePropertyEquals($response, 'per_page', 10);
        $this->assertResponsePropertyEquals($response, 'count', 10);
        $this->assertResponsePropertyEquals($response, 'total', 25);

        // Test next link (3)
        $next = $this->readResponseProperty($response, 'links.next');
        $response = $this->client->get($next, [
            'headers' => $this->getAuthorizedHeaders('joe')
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 'items[4].title', 'Challenge #24');
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
        $this->assertResponsePropertyEquals($response, 'items[5].title', 'Challenge #15');
        $this->assertResponsePropertyEquals($response, 'current_page', 2);
        $this->assertResponsePropertyEquals($response, 'previous_page', 1);
        $this->assertResponsePropertyEquals($response, 'next_page', 3);
        $this->assertResponsePropertyEquals($response, 'nb_pages', 3);
        $this->assertResponsePropertyEquals($response, 'per_page', 10);
        $this->assertResponsePropertyEquals($response, 'count', 10);
        $this->assertResponsePropertyEquals($response, 'total', 25);
    }

    // test_method testUpdateAction "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testUpdateAction() {
        // Create Game
        $game = $this->createGame();

        // Create challenge
        $challenge = $this->createChallenge($game['id']);
        $response = $this->client->get(
            '/thegame/web/app_test.php/api/challenges/' . $challenge['id'], 
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        $this->assertEquals(200, $response->getStatusCode());

        // Update resource
        $challenge['title'] = 'Updated Title';
        $response = $this->client->put(
            '/thegame/web/app_test.php/api/challenges/'. $challenge['id'], 
            [
                'body' => json_encode($challenge),
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        $challenge = json_decode($response->getBody(true), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Updated Title', $challenge['title']);
    }

    // test_method testDeleteAction "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testDeleteAction() {
        // Create game
        $game = $this->createGame();

        // Create challenge
        $challenge = $this->createChallenge($game['id']);

        // Try to delete
        $response = $this->client->delete(
            '/thegame/web/app_test.php/api/challenges/' . $challenge['id'],
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        // verifiy flag from response
        $challenge = json_decode($response->getBody(true), true);
        $this->assertTrue($challenge['deleted']);
    }

    // test_method testCompleteChallengeAction "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testCompleteChallengeAction() {
        // Create game
        $game = $this->createGame();

        // Create challenge
        $challenge = $this->createChallenge($game['id']);

        // Complete challenge
        $response = $this->client->post(
            '/thegame/web/app_test.php/api/challenges/'.$challenge['id'].'/complete',
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 'scores[0].nb_times', 1);
        $this->assertResponsePropertyEquals($response, 'scores[0].user.username', 'joe');
    }

    // test_method testCancelChallengeAction "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testCancelChallengeAction() {
        // Create game
        $game = $this->createGame();

        // Create challenge
        $challenge = $this->createChallenge($game['id']);

        // Complete challenge (nb_times: 0 => 1)
        $response = $this->client->post(
            '/thegame/web/app_test.php/api/challenges/'.$challenge['id'].'/complete',
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 'scores[0].nb_times', 1);

        // Cancel complete challenge (nb_times: 1 => 0)
        $response = $this->client->post(
            '/thegame/web/app_test.php/api/challenges/'.$challenge['id'].'/cancel',
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 'scores[0].nb_times', 0);

        // Cancel again (nb_times: 0 => 0)
        $response = $this->client->post(
            '/thegame/web/app_test.php/api/challenges/'.$challenge['id'].'/cancel',
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponsePropertyEquals($response, 'scores[0].nb_times', 0);
        $this->assertResponsePropertyEquals($response, 'scores[0].user.username', 'joe');
    }

    // test_method testCreateActionEvents "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testCreateActionEvents() {
        // Create game
        $game = $this->createGame();

        // Create challenge
        $challenge = $this->createChallenge($game['id']);

        // Check event
        $response = $response = $this->getEventsResponse();
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].iid', 
            'USER_CREATED_CHALLENGE_EVENT'
        );
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].target.type', 
            'CHALLENGE'
        );
    }

    // test_method testUpdatePointsActionEvents "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testUpdatePointsActionEvents() {
        // Create game
        $game = $this->createGame();

        // Create challenge
        $challenge = $this->createChallenge($game['id']);

        // Sleep so events wont have the same datetimes
        sleep(1);

        // update challenge points
        $challenge['nb_points'] = $challenge['nb_points'] + 1;
        $response = $this->client->put(
            '/thegame/web/app_test.php/api/challenges/'. $challenge['id'], 
            [
                'body' => json_encode($challenge),
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );

        // Check event
        $response = $response = $this->getEventsResponse();
        // since events ordered by most recent index 0 is most recent
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].iid', 
            'USER_UPDATED_CHALLENGE_POINTS_EVENT'
        );
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].target.type', 
            'CHALLENGE'
        );
    }

    // test_method testCompleteChallengeActionEvents "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testCompleteChallengeActionEvents() {
        // Create game
        $game = $this->createGame();

        // Create challenge
        $challenge = $this->createChallenge($game['id']);

        // Sleep so events wont have the same datetimes
        sleep(1);

        // Complete challenge (nb_times: 0 => 1)
        $response = $this->client->post(
            '/thegame/web/app_test.php/api/challenges/'.$challenge['id'].'/complete',
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );

        $response = $this->getEventsResponse();
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].iid', 
            'USER_COMPLETED_CHALLENGE_EVENT'
        );
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].target.type', 
            'CHALLENGE'
        );
    }

    // test_method testCanceledChallengeActionEvents "src/AppBundle/Tests/Controller/Api/ChallengeControllerTest"
    public function testCanceledChallengeActionEvents() {
        // Create game
        $game = $this->createGame();

        // Create challenge
        $challenge = $this->createChallenge($game['id']);

        // sleep to avoid event datetimes to be too similar
        sleep(1);

        // Complete challenge (nb_times: 0 => 1)
        $response = $this->client->post(
            '/thegame/web/app_test.php/api/challenges/'.$challenge['id'].'/cancel',
            [
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );

        $response = $this->getEventsResponse();
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].iid', 
            'USER_CANCELED_CHALLENGE_EVENT'
        );
        $this->assertResponsePropertyEquals(
            $response, 
            'items[0].target.type', 
            'CHALLENGE'
        );
    }


    /**
     * Creates a test game
     */
    private function createGame() {
        $data = array(
            'title' => 'Automated Test Generated Game #'.rand(0,999),
            'description' => 'A cool desc',
            'start_date' => '2017/05/30 20:00',
            'end_date' => '2017/05/30 23:59',
        );

        // Create game response
        $response = $this->client->post(
            '/thegame/web/app_test.php/api/games/new', 
            [
                'body' => json_encode($data),
                'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );
        return json_decode($response->getBody(true), true);
    }

    /**
     * Creates a challenge
     */
    private function createChallenge($gameId, $title = '') {
        $title = $title ? $title :  'Generic Challenge #'.rand(0,999);
        // Create challenge
        $data = array(
            'title' => $title,
            'description' => 'Description of a generic challenge',
            'nbPoints' => 60,
            'game' => $gameId,
        );

        // Create challlenge response
        $response = $this->client
        ->post('/thegame/web/app_test.php/api/games/'. $gameId .'/challenges/new', 
            [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('joe')
            ]
        );

        return json_decode($response->getBody(true), true);
    }

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