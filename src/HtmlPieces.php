<?php
namespace hmerritt;

/**
*  Class HtmlPieces
*
*
* @package hmerritt/imdb-api
* @author Harry Merritt
*/
class HtmlPieces
{

    /**
     * Attempts to find and return a specific element
     * from the IMDB dom
     *
     * @param object $dom
     * @param string $element
     * @return string
     */
    public function get(object $page, string $element)
    {
        //  Initiate dom object
        //  -> handles page scraping
        $dom = new Dom;

        switch ($element) {
            case "title":
                return $this->strClean($dom->find($page, '.title_wrapper h1')->text);
                break;

            default:
                return "";
        }
    }

    /**
     * Cleans-up string
     * -> removes white-space and html entitys
     *
     * @param string $string
     * @return string
     */
    private function strClean(string $string)
    {
        return empty($string) ? "" : str_replace(chr(194).chr(160), '', html_entity_decode(trim($string)));
    }

}
