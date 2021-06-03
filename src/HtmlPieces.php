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
                $patterns = [".title_wrapper h1", "h1[data-testid=hero-title-block__title]"];
                $title = $this->findMatchInPatterns($dom, $page, $patterns);

                return $this->strClean($title);
                break;

            case "year":
                $patterns = [".title_wrapper h1 #titleYear a", "section section div div div ul li a"];
                $year = $this->findMatchInPatterns($dom, $page, $patterns);

                return $this->strClean($year);
                break;

            case "length":
                $patterns = [".subtext time", "section section div div div ul li"];
                $length = "";

                $length = $dom->find($page, $patterns[0])->text;
                if ($this->count($length) > 0) return $this->strClean($length);

                $iter = $dom->find($page, $patterns[1]);
                if ($this->count($iter) === 0) return $length;

                // Loop row below main title
                // 2014 · 12A · 2h 49min
                foreach ($iter as $iterRow) {
                    // Get row text
                    $rowText = $iterRow->text;
                    if ($this->count($rowText) === 0) continue;

                    // Attempt to match length (runtime) from text
                    $isMatch = preg_match("/([0-9]+[h|m] [0-9]+[h|m])|([0-9]+[h|m])/", $rowText);

                    if ($isMatch > 0) {
                        $length = $rowText;
                    }
                }

                return $this->strClean($length);
                break;

            case "plot":
                $patterns = [".plot_summary .summary_text", "p[data-testid=plot] div"];
                $plot = $this->findMatchInPatterns($dom, $page, $patterns);

                return $this->strClean($plot);
                break;

            case "rating":
                $patterns = [".ratings_wrapper .ratingValue span[itemprop=ratingValue]", "div[data-testid=hero-title-block__aggregate-rating__score]"];
                $rating = $this->findMatchInPatterns($dom, $page, $patterns);

                return $this->strClean($rating);
                break;

            case "rating_votes":
                $patterns = [".ratings_wrapper span[itemprop=ratingCount]", "div[class*=TotalRatingAmount]"];
                $rating_votes = $this->findMatchInPatterns($dom, $page, $patterns);

                return preg_replace("/[^0-9 ]/", "", $this->strClean($rating_votes));
                break;

            case "poster":
                $patterns = [".poster img", ".ipc-poster img"];
                $poster = $this->findMatchInPatterns($dom, $page, $patterns, "src");
                $poster = preg_match('/@/', $poster) ? preg_split('~@(?=[^@]*$)~', $poster)[0] . "@.jpg" : $poster;

                return $this->strClean($poster);
                break;

            case "trailer":
                // section section div section section div div div div div a[aria-label^=Watch]
                // div a[class*=hero-media][aria-label^=Watch]
                $patterns = [".slate a[data-video]", "div a[aria-label^=Watch]"];
                $trailerLink = $dom->find($page, $patterns[1]);

                if ($this->count($trailerLink)) {
                    $href = $trailerLink->getAttribute("href");
                    preg_match("/\/video\/(vi[a-zA-Z0-9]+)/", $href, $matches);
                    $trailerId = $this->count($matches) > 1 ? $matches[1] : "";
                    $trailerLink = $this->count($trailerId) ? "https://www.imdb.com/video/".$trailerId : "";
                } else {
                    $trailerId   = "";
                    $trailerLink = "";
                }

                return [
                    "id" => $trailerId,
                    "link" => $trailerLink
                ];
                break;

            case "cast":
                $cast = [];
                $findAllCast = $dom->find($page, 'section.title-cast div.title-cast__grid div');
                foreach ($findAllCast as $castRow)
                {
                    if ($this->count($castRow->find('img')) === 0) {
                        continue;
                    }

                    $actor = [];
                    $actor["actor"] = "";
                    $actor["actor_id"] = "";
                    $actor["character"] = "";

                    // Actor
                    $actorLink = $castRow->find('a[data-testid=title-cast-item__actor]');
                    if ($this->count($actorLink)) {
                        $actor["actor"] = $actorLink->text;
                    }

                    // Actor ID
                    $link = $castRow->find('a');
                    if ($this->count($link)) {
                        $href = $link->getAttribute("href");
                        preg_match("/(nm[0-9]+)/", $href, $matches);
                        if ($this->count($matches)) {
                            $actor["actor_id"] = $matches[0];
                        }
                    }

                    // Character
                    $characterLink = $castRow->find('a[data-testid=cast-item-characters-link]');
                    if ($this->count($characterLink)) {
                        $actor["character"] = $characterLink->text;
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
                if ($this->count($table) > 0) {
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

            case "titles":
            case "names":
            case "companies":
                $response = [];
                $sections = $dom->find($page, ".findSection");
                if ($this->count($sections) > 0)
                {
                    foreach ($sections as $section)
                    {
                        $sectionName = @strtolower($section->find(".findSectionHeader")->text);
                        if ($sectionName === $element) {
                            $sectionRows = $section->find(".findList tr");
                            if ($this->count($sectionRows) > 0)
                            {
                                foreach ($sectionRows as $sectionRow)
                                {
                                    $row = [];

                                    $link = $dom->find($sectionRow, 'td.result_text a');
                                    $row["title"] = $link->text;
                                    if ($row["title"] == "") {
                                        continue;
                                    }

                                    $row["image"] = $dom->find($sectionRow, 'td.primary_photo img')->src;
                                    if (preg_match('/@/', $row["image"]))
                                    {
                                        $row["image"] = preg_split('~@(?=[^@]*$)~', $row["image"])[0] . "@.jpg";
                                    }
                                    $row["image"] = empty($row["image"]) ? "" : $row["image"];

                                    $row["id"] = $this->extractImdbId($link->href);

                                    array_push($response, $row);
                                }
                            }
                        }
                    }
                }
                return $response;
                break;

            default:
                return "";
        }
    }

    /**
     * Attempt to extract text using an array of match patterns
     *
     * @param  object $page
     * @param  array  $patterns
     * @return string 
     */
    public function findMatchInPatterns(object $dom, object $page, array $patterns, string $type = "text")
    {
        $str = "";
        foreach ($patterns as $pattern)
        {
            if ($type === "src") {
                $el = $dom->find($page, $pattern);
                $str = $this->count($el) > 0 ? $el->getAttribute("src") : "";
            } elseif ($type === "href") {
                $el = $dom->find($page, $pattern);
                $str = $this->count($el) > 0 ? $el->getAttribute("href") : "";
            } else {
                $str = $dom->find($page, $pattern)->text;
            }
            if ($this->count($str) > 0) break;
        }
        return $str;
    }

    /**
     * Extract an imdb-id from a string '/ttxxxxxxx/'
     * Returns string of id or empty string if none found
     *
     * @param string $str
     * @return string
     */
    public function extractImdbId($str)
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
    public function strClean($string)
    {
        return empty($string) ? "" : str_replace(chr(194).chr(160), '', html_entity_decode(trim($string)));
    }

    /**
     * Count (either array items or string length)
     *
     * @param array|string $item
     * @return string
     */
    public function count($item)
    {
        return (is_countable($item) ? count($item) : (is_string($item) ? strlen($item) : 0));
    }

}
