<?php

use PHPUnit\Framework\TestCase;
use hmerritt\Imdb;

class ImdbTest extends TestCase {

    public function testFilm()
    {
        $imdb = new Imdb;
        $film = $imdb->film('tt0816692');

        $this->assertEquals('tt0816692', $film['id']);
        $this->assertEquals('Interstellar&nbsp; ', $film['title']);
        $this->assertEquals('2h 49min', $film['length']);
        $this->assertEquals('2014', $film['year']);
    }

    public function testSearch()
    {
        $imdb = new Imdb;
        $search = $imdb->search('Interstellar');

        $this->assertEquals([], $search['titles']);
        $this->assertEquals([], $search['names']);
        $this->assertEquals([], $search['companies']);
    }

}
