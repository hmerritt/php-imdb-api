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
     * Add (or modify) items to the response $store
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

    /**
     * Returns default response for an endpoint
     *
     * @param string $endpoint
     * @return array $defaults
     */
    public function default(string $endpoint): array
    {
        $response = [];
        switch($endpoint)
        {
            case "film":
                $response = [
                    "id" => "",
                    "title" => "",
                    "genres" => [],
                    "year" => "",
                    "length" => "",
                    "plot" => "",
                    "rating" => "",
                    "rating_votes" => "",
                    "poster" => "",
                    "trailer" => [
                        "id" => "",
                        "link" => ""
                    ],
                    "cast" => [],
                    "technical_specs" => []
                ];
                break;

            case "search":
                $response = [
                    "titles" => [],
                    "names" => [],
                    "companies" => []
                ];
                break;
        }
        return $response;
    }

}
