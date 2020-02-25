<?php

use PHPUnit\Framework\TestCase;
use hmerritt\Response;

class ResponseTest extends TestCase {

    public function testAddFunction()
    {
        $response = new Response;
        $response->add("id", "tt0816692");
        $response->add("title", "Interstellar");

        $expected = [
            "id" => "tt0816692",
            "title" => "Interstellar"
        ];

        $this->assertEquals($expected, $response->store);
    }

    public function testGetFunction()
    {
        $response = new Response;
        $response->add("title", "Interstellar");

        $this->assertEquals("Interstellar", $response->get("title"));
    }

}
