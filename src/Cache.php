<?php
namespace hmerritt;

/**
*  Class Cache
*
*
* @package hmerritt/imdb-api
* @author Harry Merritt
*/
class Cache
{

    /**
     * Initiate cache database
     *
     * @var \Filebase\Database
     */
    private $cache = new \Filebase\Database([
        'dir'            => __DIR__ . DIRECTORY_SEPARATOR . 'cache/films/',
        'backupLocation' => __DIR__ . DIRECTORY_SEPARATOR . 'cache/films/backups/',
        'format'         => \Filebase\Format\Json::class,
        'cache'          => true,
        'cache_expires'  => 31540000,
        'pretty'         => false
    ]);

    /**
     * Add (or modify) an item in the cache
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function add(string $key, $value)
    {
        $file = $this->get($key);
        $file->film = $value;
        $file->save();
        return true;
    }

    /**
     * Counts all files in the cache
     *
     * @return int
     */
    public function count(): int
    {
        return $this->$cache->count();
    }

    /**
     * Deletes an item from the cache
     *
     * @return bool
     */
    public function delete(string $key): bool
    {
        $file = $this->get($key);
        $file->delete();
        return true;
    }

    /**
     * Check if an item exists in the cache
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return $this->$cache->has($key);
    }

    /**
     * Get an item from the cache
     *
     * @param string $key
     * @return array
     */
    public function get(string $key): array
    {
        return $this->$cache->get($key);
    }

}
