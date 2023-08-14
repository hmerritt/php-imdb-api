<?php

use PHPUnit\Framework\TestCase;
use hmerritt\Imdb;
use hmerritt\Cache;
use hmerritt\Response;

class ImdbTest extends TestCase {

    public function testFilm()
    {
        $imdb = new Imdb;
        $film = $imdb->film('tt0816692', [ 'cache' => false ]);

        $this->assertEquals('tt0816692', $film['id']);
        $this->assertEquals('Interstellar', $film['title']);
        $this->assertEquals('Adventure', $film['genres'][0]);
        $this->assertEquals('Drama', $film['genres'][1]);
        $this->assertEquals('Sci-Fi', $film['genres'][2]);
        $this->assertEquals('2h 49m', $film['length']);
        $this->assertEquals('2014', $film['year']);
        $this->assertEquals("When Earth becomes uninhabitable in the future, a farmer and ex-NASA pilot, Joseph Cooper, is tasked to pilot a spacecraft, along with a team of researchers, to find a new planet for humans.", $film['plot']);
        $this->assertEquals('8.7', $film['rating']);
        $this->assertEquals('vi1586278169', $film['trailer']["id"]);
        $this->assertEquals('https://www.imdb.com/video/vi1586278169', $film['trailer']["link"]);
        $this->assertContains($film['cast'][0]["character"], ['Cooper']);
        $this->assertContains($film['cast'][0]["actor"], ['Matthew McConaughey']);
        $this->assertContains($film['cast'][0]["actor_id"], ['nm0000190']);
        $this->assertContains($film['cast'][0]["avatar"], ['https://m.media-amazon.com/images/M/MV5BMTg0MDc3ODUwOV5BMl5BanBnXkFtZTcwMTk2NjY4Nw@@._V1_QL75_UX140_CR0,21,140,140_.jpg']);
    }

    public function testFilmBySearching()
    {
        $imdb = new Imdb;
        $film = $imdb->film('Interstellar', [ 'cache' => false ]);

        $this->assertEquals('tt0816692', $film['id']);
        $this->assertEquals('Interstellar', $film['title']);
        $this->assertEquals('Adventure', $film['genres'][0]);
        $this->assertEquals('Drama', $film['genres'][1]);
        $this->assertEquals('Sci-Fi', $film['genres'][2]);
        $this->assertEquals('2h 49m', $film['length']);
        $this->assertEquals('2014', $film['year']);
        $this->assertEquals("When Earth becomes uninhabitable in the future, a farmer and ex-NASA pilot, Joseph Cooper, is tasked to pilot a spacecraft, along with a team of researchers, to find a new planet for humans.", $film['plot']);
        $this->assertEquals('8.7', $film['rating']);
        $this->assertEquals('vi1586278169', $film['trailer']["id"]);
        $this->assertEquals('https://www.imdb.com/video/vi1586278169', $film['trailer']["link"]);
        $this->assertContains($film['cast'][0]["character"], ['Cooper']);
        $this->assertContains($film['cast'][0]["actor"], ['Matthew McConaughey']);
        $this->assertContains($film['cast'][0]["actor_id"], ['nm0000190']);
        $this->assertContains($film['cast'][0]["avatar"], ['https://m.media-amazon.com/images/M/MV5BMTg0MDc3ODUwOV5BMl5BanBnXkFtZTcwMTk2NjY4Nw@@._V1_QL75_UX140_CR0,21,140,140_.jpg']);
        $this->assertContains($film['cast'][0]["avatar_hq"], ['https://m.media-amazon.com/images/M/MV5BMTg0MDc3ODUwOV5BMl5BanBnXkFtZTcwMTk2NjY4Nw@@.jpg']);
    }

    public function testFilmOptions()
    {
        $imdb = new Imdb;
        $cache = new Cache;
        $film = $imdb->film('tt0065531', [
            'cache'       => false,
            'curlHeaders' => ['Accept-Language: de-DE, de;q=0.5'],
            'techSpecs'   => false
        ]);

        $this->assertEquals('Vier im roten Kreis', $film['title']);
        $this->assertEquals(0, count($film['technical_specs']));
        $this->assertEquals(false, $cache->has('tt0065531'));
    }

    public function testFilmCache()
    {
        $imdb = new Imdb;
        $cache = new Cache;
        $film = $imdb->film('tt0816692', [ 'cache' => true, 'techSpecs' => false ]);
        $cache_film = $cache->get('tt0816692')->film;

        $this->assertEquals(true, $cache->has('tt0816692'));
        $this->assertEquals('Interstellar', $cache_film['title']);
    }

    public function testSearch()
    {
        $imdb = new Imdb;
        $search = $imdb->search('Interstellar');

        $this->assertEquals('Interstellar', $search['titles'][0]['title']);
        $this->assertEquals('tt0816692', $search['titles'][0]['id']);

        $search_2 = $imdb->search('The Life and Death of Colonel Blimp');

		$this->assertEquals('The Life and Death of Colonel Blimp', $search_2['titles'][0]['title']);
		$this->assertEquals('tt0036112', $search_2['titles'][0]['id']);
    }
}
