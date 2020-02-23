<?php
namespace hmerritt;

/**
*  Class Response
*
*
* @package hmerritt/imdb-api
* @author Harry Merritt
*/
class Response
{

    /**
     * A persistent array to store the response in
     *
     * @var array
     */
    public $store = [];

    /**
     * Add (or modify) to the response $store
     *
     * @param string $key
     * @param string $value
     * @return array $store
     */
    public function add(string $key, $value): array
    {
        //  Add item to $store
        $this->store[$key] = $value;

        return $this->store;
    }

    /**
     * Get an individual value from the response $store
     *
     * @param string $key
     * @return $store[$key]
     */
    public function get(string $key)
    {
        return $this->store[$key];
    }

    /**
     * Returns the entire $store array
     *
     * @return array $store
     */
    public function return(): array
    {
        return $this->store;
    }

}
