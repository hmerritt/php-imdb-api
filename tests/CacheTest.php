<?php

use PHPUnit\Framework\TestCase;
use hmerritt\Imdb;
use hmerritt\Response;
use hmerritt\Cache;

class CacheTest extends TestCase {

    public function testAddToCache()
    {
        $cache = new Cache;
        $testData = [
            "title" => "Interstellar"
        ];

        $cache->add("test", $testData);
        $testFile = $cache->get("test");

        $this->assertEquals($testData, $testFile->film);

        $cache->delete("test");
    }

    public function testHasCache()
    {
        $cache = new Cache;
        $cache->add("testHas", [ "test" => "has" ]);

        $this->assertEquals(true, $cache->has("testHas"));
        $this->assertEquals(false, $cache->has("testHasNot"));

        $cache->delete("testHas");
    }

    public function testDeleteFromCache()
    {
        $cache = new Cache;
        $cache->add("testHas", [ "test" => "has" ]);

        $this->assertEquals(true, $cache->has("testHas"));
        $cache->delete("testHas");
        $this->assertEquals(false, $cache->has("testHas"));
    }

}
