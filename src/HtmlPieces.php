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
    public $filmId;

    public function __construct(string $filmId = null)
    {
        $this->filmId = $filmId;
    }

    public function setFilmId(string $filmId = null)
    {
        $this->filmId = $filmId;
    }

    /**
     * Attempts to find and return a specific element
     * from the IMDB dom
     *
     * @param object $dom
     * @param string $element
     * @return string
     */
    public function get(object $page, string $element, string $url='')
    {
        //  Initiate dom object
        //  -> handles page scraping
        $dom = new Dom;

        switch ($element) {
            case "title":
                $patterns = ["h1[data-testid=hero__pageTitle] span", "h1[data-testid=hero__pageTitle]", "h1[data-testid=hero-title-block__title]", ".title_wrapper h1"];
                $title = $this->findMatchInPatterns($dom, $page, $patterns);

                return $this->strClean($title);
                break;

            case "genre":
                $allGenres = $dom->find($page, "div[data-testid=genres] a");
                $genres = [];

                if ($this->count($allGenres)) {
                    foreach ($allGenres as $genre) {
                        $genres[] = $this->strClean($genre->find('span')->text());
                    }
                }

                return $genres;
                break;

            case "year":
                $patterns = ["a[href='/title/{$this->filmId}/releaseinfo?ref_=tt_ov_rdat']", "[data-testid=hero-title-block__metadata] > li > a", ".title_wrapper h1 #titleYear a", ".title_wrapper .subtext a[title='See more release dates']", "section section div div div ul li a"];
                $year = $this->findMatchInPatterns($dom, $page, $patterns);

                // Detect OLD IMDB + TV show
                if ($this->count($year) > 4) {
                    // Extract year from text
                    // \d{4}.\d{4}
                    $matchYear = preg_replace(preg_quote("/[^\d{4}--\d{4}]/"), "", $year);
                    if ($this->count($matchYear) > 0) {
                        $year = $matchYear;
                    }
                }

                return $this->strClean($year);
                break;

            case "length":
                $patterns = ["section section div div div ul li", ".subtext time"];
                $length = "";

                $length = $dom->find($page, $patterns[1])->text;
                if ($this->count($length) > 0) return $this->strClean($length);

                $length = "";
                $iter = $dom->find($page, $patterns[0]);
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
                $patterns = ["p[data-testid=plot] > span[data-testid=plot-xl]", "[data-testid=plot-xl]", ".plot_summary .summary_text"];
                $plot = $this->findMatchInPatterns($dom, $page, $patterns);

                return $this->strClean($plot);
                break;

            case "rating":
                $patterns = ["[data-testid=hero-rating-bar__aggregate-rating__score] > span", ".ratings_wrapper .ratingValue span[itemprop=ratingValue]"];
                $rating = $this->findMatchInPatterns($dom, $page, $patterns);

                return $this->strClean($rating);
                break;

            case "rating_votes":
                $patterns = [".sc-bde20123-3", "[class*=TotalRatingAmount]", ".ratings_wrapper span[itemprop=ratingCount]"];
                $rating_votes = $this->findMatchInPatterns($dom, $page, $patterns);
                $rating_votes = $this->unwrapFormattedNumber($rating_votes);

                return preg_replace("/[^0-9]/", "", $this->strClean($rating_votes));
                break;

            case "poster":
                $patterns = [".ipc-poster .ipc-media img", ".poster img"];
                $poster = $this->findMatchInPatterns($dom, $page, $patterns, "src");
                $poster = preg_match('/@/', $poster) ? preg_split('~@(?=[^@]*$)~', $poster)[0] . "@.jpg" : $poster;

                return $this->strClean($poster);
                break;

            case "trailer":
                // section section div section section div div div div div a[aria-label^=Watch]
                // div a[class*=hero-media][aria-label^=Watch]
                $patterns = ["a[data-testid=videos-slate-overlay-1]", "div a[aria-label^=Watch]", ".slate a[data-video]"];
                $trailerLinkOld = $dom->find($page, $patterns[1]);
                $trailerLink = $dom->find($page, $patterns[0]);

                if ($this->count($trailerLink)) {
                    $href = $trailerLink->getAttribute("href");
                    preg_match("/\/video\/(vi[a-zA-Z0-9]+)/", $href, $matches);
                    $trailerId = $this->count($matches) > 1 ? $matches[1] : "";
                    $trailerLink = $this->count($trailerId) ? "https://www.imdb.com/video/".$trailerId : "";

                } elseif ($this->count($trailerLinkOld)) {
                    $trailerId = $this->count($trailerLinkOld) ? $trailerLinkOld->getAttribute("data-video") : "";
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
                $findAllCastOld = $dom->find($page, 'table.cast_list tr');
                $findAllCast = $dom->find($page, '[data-testid=title-cast] [data-testid=shoveler-items-container] > div');

                // Use $findAllCastOld
                if ($this->count($findAllCastOld)) {
                    foreach ($findAllCastOld as $castRow)
                    {
                        if ($this->count($castRow->find('.primary_photo')) === 0) {
                            continue;
                        }
                        $actor = [];

                        $characterLink = $castRow->find('.character a');
                        $actor["character"] = count($characterLink) ? $characterLink->text : $dom->find($castRow, '.character')->text;

                        $actorRow = $castRow->find('td')[1];
                        $actorLink = $actorRow->find('a');
                        if ($this->count($actorLink) > 0) {
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
                }

                // Use 'new' $findAllCast
                if ($this->count($findAllCast)) {
                    foreach ($findAllCast as $castRow)
                    {
                        if ($this->count($castRow->find('img')) === 0) {
                            continue;
                        }

                        $actor = [];
                        $actor["actor"] = "";
                        $actor["avatar"] = "";
                        $actor["avatar_hq"] = "";
                        $actor["actor_id"] = "";
                        $actor["character"] = "";

                        // Actor
                        $actorLink = $castRow->find('a[data-testid=title-cast-item__actor]');
                        if ($this->count($actorLink)) {
                            $actor["actor"] = $actorLink->text;
                        }

                        // Avatar
                        $actorAvatar = $castRow->find('img.ipc-image');
                        if ($this->count($actorAvatar)) {
                            $actor["avatar"] = $actorAvatar->getAttribute('src');
                            $actor["avatar_hq"] = preg_match('/@/', $actor["avatar"]) ? preg_split('~@(?=[^@]*$)~', $actor["avatar"])[0] . "@.jpg" : $actor["avatar"];

                            if ($actor["avatar"] == $actor["avatar_hq"]) {
                                $actor["avatar_hq"] = preg_match('/\.\_/', $actor["avatar_hq"]) ? preg_split('/\.\_.*/', $actor["avatar_hq"])[0] . ".jpg" : $actor["avatar_hq"];
                            }
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
                        $characterLink = $castRow->find('[data-testid=cast-item-characters-link] span');
                        if ($this->count($characterLink)) {
                            $actor["character"] = $characterLink->text;
                        }

                        $actor["character"] = $this->strClean($actor["character"]);
                        $actor["actor"]     = $this->strClean($actor["actor"]);
                        $actor["avatar"]    = $this->strClean($actor["avatar"]);
                        $actor["actor_id"]  = $this->strClean($actor["actor_id"]);

                        array_push($cast, $actor);
                    }
                }
                return $cast;
                break;

            case "tvShow":
                preg_match('/TV Series/i', $page, $matches, PREG_OFFSET_CAPTURE);
                return !!$this->count($matches);
                break;

            case "seasons":
                $seasons = [];
                $findAllSeasons = $dom->find($page, "#bySeason > option");
                $dom = new \PHPHtmlParser\Dom();
                foreach ($findAllSeasons as $seasonRow){
                    $season = [];
                    $seasonValue = $seasonRow->getAttribute('value');
                    $season['season'] = $seasonValue;
                    // Using imdb ajax api to get episodes
                    $season['episodes'] = $this->get($dom->loadFromUrl($url."/_ajax?season=".$seasonValue), "episodes");
                    array_push($seasons, $season);
                }
                return $seasons;
                break;

            case "episodes":
                $episodes = [];
                $findAllEpisodes = $dom->find($page, ".eplist > .list_item");
                foreach ($findAllEpisodes as $episodeRow){
                    $episode = [];
                    $hyperlink = $episodeRow->find("a[itemprop=url]");
                    $episode["id"]          = $this->extractImdbId($hyperlink->getAttribute("href"));
                    $episode['title']       = $episodeRow->find('a[itemprop=name]')->text;
                    $episode['description'] = $episodeRow->find(".item_description")->text;
                    $rating                 = $episodeRow->find(".ipl-rating-star__rating");
                    $episode["poster"]      = "";
                    if($this->count($rating)) {
                        $episode['rating'] = $rating->text;
                    }
                    $image = $hyperlink->find("img");
                    if($this->count($image)) {
                        $poster = $image->getAttribute("src");
                        $episode["poster"] = preg_match('/@/', $poster) ? preg_split('~@(?=[^@]*$)~', $poster)[0] . "@.jpg" : $poster;

                        if ($poster == $episode["poster"]) {
                            $episode["poster"] = preg_match('/\.\_/', $episode["poster"]) ? preg_split('/\.\_.*/', $episode["poster"])[0] . ".jpg" : $episode["poster"];
                        }
                    }
                    array_push($episodes, $episode);
                }
                return $episodes;
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
            case "people":
            case "companies":
                $response = [];
                $sections = $dom->find($page, ".ipc-page-section");
                if ($this->count($sections) > 0)
                {
                    foreach ($sections as $section)
                    {
                        $sectionName = @strtolower($dom->find($section, ".ipc-title__text")->text);
                        if ($sectionName === $element) {
                            $sectionRows = $section->find("ul li");
                            if ($this->count($sectionRows) > 0)
                            {
                                foreach ($sectionRows as $sectionRow)
                                {
                                    $row = [];

                                    $link = $dom->find($sectionRow, 'a');
                                    $row["title"] = $link->text;
                                    if ($row["title"] == "") {
                                        continue;
                                    }

                                    $row["image"] = $dom->find($sectionRow, '.ipc-image')->src;
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
     * Unwrap formatted number to original int - 1.5K -> 1500
     *
     * @param string $str
     * @return string
     */
    public function unwrapFormattedNumber($str)
    {
        $unwrap = $str;
        $divisors = ["K", "M", "B"];
        $divisorMap = [
            "K" => 1000,
            "M" => 1000000,
            "B" => 1000000000
        ];

        $strDivisor = substr($str, -1);
        if (in_array($strDivisor, $divisors)) {
            // Remove last charactor
            $strNum = substr($str, 0, -1);
            $num = floatval($strNum);

            $numActual = $num * $divisorMap[$strDivisor];

            $unwrap = strval($numActual);
        }

        return $unwrap;
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
        return empty($string) ? "" : str_replace(chr(194).chr(160), '', html_entity_decode(trim($string), ENT_QUOTES));
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
