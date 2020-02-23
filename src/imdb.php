<?php
namespace hmerritt;

/**
*  Class Imdb
*
*
* @package hmerritt/imdb-api
* @author Harry Merritt
*/
class Imdb
{

    /**
     * IMDB base url
     *
     * @var string
     */
    protected $baseUrl = 'https://www.imdb.com/';

    /**
     * Returns default options combined with any user options
     *
     * @param string $options
     * @return array $defaults
     */
    private function populateOptions(array $options = []): array
    {
        //  Default options
        $defaults = [
            'cache'        => true,
            'curlHeaders'  => ['Accept-Language: en-US,en;q=0.5'],
            'techSpecs'    => true,
        ];

        //  Merge any user options with the default ones
        foreach ($options as $key => $option) {
            $defaults[$key] = $option;
        }

        //  Return final options array
        return $defaults;
    }

    /**
     * Gets film data from IMDB. Will first search if the
     * film name is passed instead of film-id
     * @param string $film
     * @param array  $options
     * @return array $filmData
     */
    public function film(string $film, array $options = []): array
    {
        //  Combine user options with default ones
        $options = $this->populateOptions($options);

        //  Initiate response object
        // -> handles what the api returns
        $response = new Response;

        $dom = new Dom;
        $page = $dom->fetch("https://www.imdb.com/title/tt0816692/", $options);

        return [
            "id" => "tt0816692",
            "title" => "Interstellar",
            "length" => "2h 49min",
            "year" => "2014"
        ];
    }

    /**
     * Searches IMDB for films, people and companies
     * @param string $search
     * @param array  $options
     * @return array $searchData
     */
    public function search(string $search, array $options = []): array
    {
        //  Combine user options with default ones
        $options = $this->populateOptions($options);

        return [
            "titles" => [],
            "names" => [],
            "companies" => []
        ];
    }

}
