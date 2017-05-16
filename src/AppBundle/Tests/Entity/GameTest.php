<?php 

namespace Tests\AppBundle\Entity;


use PHPUnit\Framework\TestCase;

use AppBundle\Entity\Game;
use AppBundle\Entity\User;

/**
* GameTest
*/
class GameTest extends TestCase {

    // test_method testVisibility "src/AppBundle/Tests/Entity/GameTest"
    public function testVisibility() {
        // create Users
        $userAllowed1 = new User();
        $userAllowed1->setUsername("userAllowed1");

        $userAllowed2 = new User();
        $userAllowed2->setUsername("userAllowed2");

        $userRestricted = new User();
        $userRestricted->setUsername("userRestricted");

        $game = new Game();
        $game->setCreatedBy($userAllowed1);
        $game->addAuthorizedPlayer($userAllowed1);
        $game->addAuthorizedPlayer($userAllowed2);

        // VISIBILITY PUBLIC
        $game->setVisibility($game::VISIBILITY_PUBLIC);
        $this->assertTrue($game->isVisibleTo($userAllowed1));
        $this->assertTrue($game->isVisibleTo($userAllowed2));
        $this->assertTrue($game->isVisibleTo($userRestricted));


        // VISIBILITY PRIVATE
        $game->setVisibility($game::VISIBILITY_PRIVATE);
        $this->assertTrue($game->isVisibleTo($userAllowed1));
        $this->assertTrue($game->isVisibleTo($userAllowed2));
        $this->assertFalse($game->isVisibleTo($userRestricted));
    }
}
