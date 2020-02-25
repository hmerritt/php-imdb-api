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
    public function get(object $page, string $element, array $options = [])
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

            case "poster":
                $poster = $dom->find($page, '.poster img')->src;
                $poster = preg_match('/@/', $poster) ? preg_split('~@(?=[^@]*$)~', $poster)[0] . "@.jpg" : $poster;
                return $this->strClean($poster);
                break;

            case "trailer":
                $trailerLink = $page->find('.slate a[data-video]');
                $trailerId = $this->count($trailerLink) ? $trailerLink->getAttribute("data-video") : "";
                $trailerLink = $this->count($trailerId) ? "https://www.imdb.com/videoplayer/".$trailerId : "";
                return [
                    "id" => $trailerId,
                    "link" => $trailerLink
                ];
                break;

            case "cast":
                $cast = [];
                $findAllCast = $dom->find($page, 'table.cast_list tr');
                foreach ($findAllCast as $castRow)
                {
                    if (count($castRow->find('.primary_photo')) === 0) {
                        continue;
                    }
                    $actor = [];

                    $characterLink = $castRow->find('.character a');
                    $actor["character"] = count($characterLink) ? $characterLink->text : $dom->find($castRow, '.character')->text;

                    $actorRow = $castRow->find('td')[1];
                    $actorLink = $actorRow->find('a');
                    if (count($actorLink) > 0) {
                        // Set actor name to text within link
                        $actor["actor"] = $actorLink->text;
                        $actor["actor_id"] = $this->extractImdbId($actorLink->href);
                    } else {
                        // No link found
                        // Set actor name to whatever is there
                        $actor["actor"] = $actorRow->text;
                    }

                    $actor["character"] = $this->strClean($actor["character"]);
                    $actor["actor"]     = $this->strClean($actor["actor"]);
                    $actor["actor_id"]  = $this->strClean($actor["actor_id"]);

                    array_push($cast, $actor);
                }
                return $cast;
                break;

            case "technical_specs":
                $technical_specs = [];
                $table = $dom->find($page, '.dataTable tr');
                if (count($table) > 0) {
                    foreach ($table as $row)
                    {
                        $row_title = $row->find('td')[0]->text(true);
                        $row_value = str_replace("  ", " <br> ", $row->find('td')[1]->text(true));
                        $row = [
                            $this->strClean($row_title),
                            $this->strClean($row_value)
                        ];
                        array_push($technical_specs, $row);
                    }
                }
                return $technical_specs;
                break;

            default:
                return "";
        }
    }

    /**
     * Extract an imdb-id from a string '/ttxxxxxxx/'
     * Returns string of id or empty string if none found
     *
     * @param string $str
     * @return string
     */
    private function extractImdbId($str)
    {
        // Search string for 2 letters followed by numbers
        // '/yyxxxxxxx'
        preg_match('/\/[A-Za-z]{2}[0-9]+/', $str, $imdbIds);
        $id = substr($imdbIds[0], 1);
        if ($id == NULL)
        {
            $id = "";
        }
        return $id;
    }

    /**
     * Cleans-up string
     * -> removes white-space and html entitys
     *    turns null into empty string
     *
     * @param $string
     * @return string
     */
    private function strClean($string)
    {
        return empty($string) ? "" : str_replace(chr(194).chr(160), '', html_entity_decode(trim($string)));
    }

    /**
     * Count (either array items or string length)
     *
     * @param array|string $item
     * @return string
     */
    private function count($item) {
        return (is_array($item) ? count($item) : strlen($item));
    }

}
