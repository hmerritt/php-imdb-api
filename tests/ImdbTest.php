<?php

use PHPUnit\Framework\TestCase;
use hmerritt\Imdb;
use hmerritt\Response;

class ImdbTest extends TestCase {

    public function testFilm()
    {
        $imdb = new Imdb;
        $film = $imdb->film('tt0816692', [ 'cache' => false ]);

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

    public function testFilmOptions()
    {
        $imdb = new Imdb;
        $film = $imdb->film('tt0065531', [
            'cache'       => false,
            'curlHeaders' => ['Accept-Language: de-DE, de;q=0.5'],
            'techSpecs'   => false,
        ]);

        $this->assertEquals('Vier im roten Kreis', $film['title']);
        $this->assertEquals(0, count($film['technical_specs']));
    }

    public function test404Page()
    {
        $imdb = new Imdb;
        $response = new Response;

        $film = $imdb->film('ttest404', [ 'cache' => false ]);
        $search = $imdb->search('ttest404040404004', [ 'category' => 'test404' ]);

        $emptyResponse = [
            'film' => $response->default('film'),
            'search' => $response->default('search'),
        ];
        $emptyResponse['film']['id'] = 'ttest404';

        $this->assertEquals($emptyResponse['film'], $film);
        $this->assertEquals($emptyResponse['search'], $search);
    }

}
