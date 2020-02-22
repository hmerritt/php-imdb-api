<?php
namespace hmerritt\Dom;

/**
*  Class Imdb
*
*
* @package hmerritt/imdb-api
* @author Harry Merritt
*/
class Dom
{

    /**
     * Fetch and parse the DOM of a remote site
     *
     * @param string $url
     *
     * @return Dom
     */
    public function fetch(string $url, array $options): Dom
    {
        $dom = new Dom;
        $dom->loadFromUrl($url, [
            "curlHeaders" => $options["curlHeaders"]
        ]);
        return $dom;
    }

    /**
     * Find object within DOM (if it exists) and reutrn an attribute
     *
     * @param object $dom
     * @param string $selection
     *
     * @return array
     */
    public function find(object $dom, string $selection): array
    {
        $found = $dom->find($selection);
        if (count($found) > 0) {
            return $found;
        }
        else {
            return $this->emptyElement();
        }
    }

    /**
     * Create and parse an empty html string as a DOM element
     *
     * @return Dom
     */
    private function emptyElement(): Dom
    {
        $dom = new Dom;
        $dom->load('<a src="" href="" data-video=""></a>');
        return $dom;
    }

}
