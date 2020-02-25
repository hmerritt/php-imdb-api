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

    function __construct()
    {
        /**
         * Initiate cache database
         *
         * @var \Filebase\Database
         */
        $this->cache = new \Filebase\Database([
            'dir'            => __DIR__ . DIRECTORY_SEPARATOR . 'cache/films/',
            'backupLocation' => __DIR__ . DIRECTORY_SEPARATOR . 'cache/films/backups/',
            'format'         => \Filebase\Format\Json::class,
            'cache'          => true,
            'cache_expires'  => 31540000,
            'pretty'         => false
        ]);
    }

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
     * Get an item from the cache
     *
     * @param string $key
     * @return object
     */
    public function get(string $key): object
    {
        return $this->cache->get($key);
    }

    /**
     * Check if an item exists in the cache
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

}
