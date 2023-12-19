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
     * Initiate cache
     *
     * @param string $type Type of cache: file | redis
     * @param array $redisClientOptions Options for redis
     */
    function __construct(string $type = "file", array $redisClientOptions = [])
    {
        $this->type = $type;
        $this->isRedis = $type === "redis";

        /**
         * @var \Predis\Client
         * @var \Filebase\Database
         */
        if ($this->isRedis) {
            $redisOpts = [
                'scheme'   => 'tcp',
                'host'     => '127.0.0.1',
                'port'     => 6379,
                'password' => '',
                'database' => 0,
            ];
            foreach ($redisClientOptions as $key => $option) {
                $redisOpts[$key] = $option;
            }

            $this->redis = new \Predis\Client([
                'scheme' => $redisOpts['scheme'],
                'host'   => $redisOpts['host'],
                'port'   => $redisOpts['port'],
            ], [
                'parameters' => [
                    'password' => $redisOpts['password'],
                    'database' => $redisOpts['database'],
                ],
            ]);
        } else {
            $this->cache = new \Filebase\Database([
                'dir'            => __DIR__ . DIRECTORY_SEPARATOR . 'cache/films/',
                'backupLocation' => __DIR__ . DIRECTORY_SEPARATOR . 'cache/films/backups/',
                'format'         => \Filebase\Format\Json::class,
                'cache'          => true,
                'cache_expires'  => 31540000,
                'pretty'         => false
            ]);
        }
    }

    public function isRedis()
    {
        return $this->isRedis;
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
        if ($this->isRedis) {
            $this->redis->set($key, json_encode($value));
        } else {
            $file = $this->get($key);
            $file->film = $value;
            $file->save();
        }
        return true;
    }

    /**
     * Deletes an item from the cache
     *
     * @return bool
     */
    public function delete(string $key): bool
    {
        if ($this->isRedis) {
            $this->redis->del($key);
        } else {
            $file = $this->get($key);
            $file->delete();
        }
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
        if ($this->isRedis) return json_decode($this->redis->get($key));
        else return $this->cache->get($key);
    }

    /**
     * Check if an item exists in the cache
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        if ($this->isRedis) return $this->redis->exists($key);
        else return $this->cache->has($key);
    }
}
