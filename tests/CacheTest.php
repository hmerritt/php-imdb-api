<?php

use PHPUnit\Framework\TestCase;
use hmerritt\Imdb;
use hmerritt\Response;
use hmerritt\Cache;

class CacheTest extends TestCase {

    public function testCache()
    {
        $cache = new Cache;
        $cache_testStore = [
            "title" => "Interstellar"
        ];

        $cache->add("test", $cache_testStore);
        $cache_testFile = $cache->get("test");

        $this->assertEquals($cache_testStore, $cache_testFile->film);
    }

}
