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
                $title = $dom->find($page, '.title_wrapper h1')->text;
                return $this->strClean($title);
                break;

            case "year":
                $year = $dom->find($page, '.title_wrapper h1 #titleYear a')->text;
                return $this->strClean($year);
                break;

            case "length":
                $length = $dom->find($page, '.subtext time')->text;
                return $this->strClean($length);
                break;

            case "plot":
                $plot = $dom->find($page, '.plot_summary .summary_text')->text;
                return $this->strClean($plot);
                break;

            case "rating":
                $rating = $dom->find($page, '.ratings_wrapper .ratingValue span[itemprop=ratingValue]')->text;
                return $this->strClean($rating);
                break;

            case "rating_votes":
                $rating_votes = $dom->find($page, '.ratings_wrapper span[itemprop=ratingCount]')->text;
                return preg_replace("/[^0-9 ]/", "", $this->strClean($rating_votes));
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
