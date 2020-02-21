<?php
namespace hmerritt\Imdb;

use PHPHtmlParser\Dom;

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
     * Returns default options plus any user options
     *
     * @param string $options
     * @return array
     */
    private function getOptions(array $options = []): array
    {
        $defaults = [
            'cache'      => true,
            'techSpecs'  => true,
        ];

        foreach ($options as $key => $option) {
            $defaults[$key] = $option;
        }

        return $defaults;
    }

    /**
     * Gets film data from IMDB. Will first search if the
     * film name is passed instead of film-id
     * @param string $film
     * @param array  $options
     * @return $filmData
     */
    public function film(string $film, array $options = [])
    {
        $options = $this->getOptions($options);
        return $options;
    }

    /**
     * Searches IMDB for films, people and companies
     * @param string $search
     * @param array  $options
     * @return $searchData
     */
    public function search(string $search, array $options = [])
    {
        $options = $this->getOptions($options);
    }

}
