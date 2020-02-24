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
     * @return array $response
     */
    public function film(string $film, array $options = []): array
    {
        //  Combine user options with default ones
        $options = $this->populateOptions($options);

        //  Initiate response object
        // -> handles what the api returns
        $response = new Response;

        //  Initiate dom object
        //  -> handles page scraping
        $dom = new Dom;

        //  Initiate html-pieces object
        //  -> handles finding specific content from the dom
        $htmlPieces = new HtmlPieces;

        //  Load imdb page and parse the dom
        $page = $dom->fetch($this->baseUrl."title/".$film, $options);

        //  Add all film data to response $store
        $response->add("id", $film);
        $response->add("title", $htmlPieces->get($page, "title"));
        $response->add("year", $htmlPieces->get($page, "year"));
        $response->add("length", $htmlPieces->get($page, "length"));
        $response->add("plot", $htmlPieces->get($page, "plot"));
        $response->add("rating", $htmlPieces->get($page, "rating"));
        $response->add("rating_votes", $htmlPieces->get($page, "rating_votes"));
        $response->add("poster", $htmlPieces->get($page, "poster"));
        $response->add("trailer", $htmlPieces->get($page, "trailer"));
        $response->add("cast", $htmlPieces->get($page, "cast"));
        $response->add("technical_specs", $htmlPieces->get($page, "technical_specs"));

        //  Return the response $store
        return $response->return();
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

        //  Initiate response object
        // -> handles what the api returns
        $response = new Response;

        $response->add("titles", []);
        $response->add("names", []);
        $response->add("companies", []);

        return $response->return();
    }

}
