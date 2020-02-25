<?php

use PHPUnit\Framework\TestCase;
use hmerritt\Imdb;

class ImdbTest extends TestCase {

    public function testFilm()
    {
        $imdb = new Imdb;
        $film = $imdb->film('tt0816692');

        $this->assertEquals('tt0816692', $film['id']);
        $this->assertEquals('Interstellar', $film['title']);
        $this->assertEquals('2h 49min', $film['length']);
        $this->assertEquals('2014', $film['year']);
        $this->assertEquals('vi1586278169', $film['trailer']["id"]);
        $this->assertEquals('https://www.imdb.com/videoplayer/vi1586278169', $film['trailer']["link"]);
        $this->assertEquals('Murph (Older)', $film['cast'][0]["character"]);
        $this->assertEquals('Ellen Burstyn', $film['cast'][0]["actor"]);
        $this->assertEquals('nm0000995', $film['cast'][0]["actor_id"]);
    }

    public function testSearch()
    {
        $imdb = new Imdb;
        $search = $imdb->search('Interstellar');

        $this->assertEquals('Interstellar', $search['titles'][0]['title']);
        $this->assertEquals('tt0816692', $search['titles'][0]['id']);
    }

}
