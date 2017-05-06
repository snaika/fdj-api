<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GameControllerTest extends WebTestCase
{

    public function testCompleteScenario()
    {
        $i = random_int(0, 999);

        // Create a new client to browse the application
        $client = static::createClient();

        // Get all games
        $crawler = $client->request('GET', '/game');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /game");

        // Create a new entry in the database
        $client->request(
            'POST',
            '/game',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{
                "title": "test'. $i.'",
                "IMG": "test.jpg",
                "HREF": "https://www.fdj.fr/jeux/test",
                "status" : 0
                }'
        );
        $this->assertEquals(201, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for POST /game");
        $id = $client->getResponse()->isSuccessful() ? json_decode($client->getResponse()->getContent())->id : 1;

        // Create an existing entry in the database
        $client->request(
            'POST',
            '/game',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{
                "title": "test'. $i.'",
                "IMG": "test.jpg",
                "HREF": "https://www.fdj.fr/jeux/test",
                "status" : 0
                }'
        );
        $this->assertEquals(400, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code repost same data POST /game");

        // Get a game from db
        $client->request(
            'GET',
            '/game/'.$id
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /game$id");
        $rawGame =  $client->getResponse()->isSuccessful() ? $client->getResponse()->getContent() : "{}";

        // Update an existing entry in the database
        $client->request(
            'PUT',
            '/game/'.$id,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{
                "title": "test'. $i.' test",
                "IMG": "test.jpg",
                "HREF": "https://www.fdj.fr/jeux/test",
                "status" : 1
                }'
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for PUT /game$id");

        // Check if game is persisted
        $this->assertNotRegExp('/'.$rawGame.'/', $client->getResponse()->getContent());

        // Delete an existing entry in the database
        $client->request(
            'DELETE',
            '/game/'.$id
        );
        $this->assertEquals(204, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code on DELETE /game$id");

        // Get a non existing game from db
        $client->request(
            'GET',
            '/game/'.$id
        );
        $this->assertEquals(404, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /game$id");


    }


}
