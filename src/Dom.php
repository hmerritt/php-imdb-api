<?php
namespace hmerritt;

/**
*  Class Dom
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
     * @return \PHPHtmlParser\Dom
     */
    public function fetch(string $url, array $options)
    {
        $dom = new \PHPHtmlParser\Dom;
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
     * @return array|object
     */
    public function find(object $dom, string $selection)
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
     * @return \PHPHtmlParser\Dom
     */
    private function emptyElement()
    {
        $dom = new \PHPHtmlParser\Dom;
        $dom->load('<a emptyElement="true" src="" href="" data-video=""></a>');
        return $dom;
    }

}
