<?php
/**
*  PHP Imdb Api
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*
* @package hmerritt/imdb-api
* @author Harry Merritt
*/
namespace hmerritt\Imdb;


use PHPHtmlParser\Dom;


class Imdb {

   /**  @var string $imdb_base_url the base or 'pre-fix' for all imdb links */
   private $imdb_base_url = "https://www.imdb.com/";


   /**
    * Search IMDB for @param $query and return the all search results
    *
    * @param $query string - search string
    * @param $category string - category to search within (films || people)
    *
    * @return array
    */
   public function search($query, $category="all") {
       // Replace all spaces with '+'
       $query = preg_replace("/\s/", "+", $query);
       // Create search url
       $url = $this->imdb_base_url . "find?q=$query&s=$category";
       // Load html from search url
       $dom = $this->loadDom($url);
       // Inspect html for first search result
       $dom_findSection = $dom->find(".findSection");
       // Create response array
       $response = [
         "titles" => [],
         "names" => [],
         "companies" => []
       ];

       if (count($dom_findSection) > 0)
       {
           // Loop each search section
           foreach ($dom_findSection as $search_section)
           {
               // Get section name
               $section_name = @strtolower($search_section->find(".findSectionHeader")->text);
               // Check if section name exists in response
               // Only add items from existing values in resonse array
               if (array_key_exists($section_name, $response))
               {
                   // Get every row within the section table
                   $section_table_rows = $search_section->find(".findList tr");
                   if (count($section_table_rows) > 0)
                   {
                       // Loop each table row within the section
                       foreach ($section_table_rows as $section_row)
                       {
                           // Create row array
                           // To append to $response
                           $row = [];

                           // Link ojbect
                           $item_link = $this->htmlFind($section_row, 'td.result_text a');
                           // Text value
                           $row["title"] = $item_link->text;
                           // Skip item if no text value
                           if ($row["title"] == "") {
                               continue;
                           }

                          // Image object
                          $row["image"] = $this->htmlFind($section_row, 'td.primary_photo img')->src;
                          if (preg_match('/@/', $row["image"]))
                          {
                              $row["image"] = preg_split('~@(?=[^@]*$)~', $row["image"])[0] . "@.jpg";
                          }

                           // Get the id of the link
                           // Imdb Id
                           $row["id"] = $this->extractImdbId($item_link->href);

                           // Add row to $response
                           array_push($response[$section_name], $row);
                       }
                   }
               }
           }
       }
       // Return resonse
       return $response;
   }


    /**
     * Get indivial film data via tt-id
     *
     * @param $query string - film name or imdb-id
     *
     * @return array
     */
    public function film($query, $techSpecs=false) {
        // Define response array
        $response = [
          "title" => "",
          "year" => "",
          "length" => "",
          "rating" => "",
          "rating_votes" => "",
          "poster" => "",
          "plot" => "",
          "cast" => [],
          "technical_specs" => []
        ];
        // Set filmId to querry
        $filmId = $query;
        // Check for 'tt' at start of $query
        if (substr($query, 0, 2) !== "tt")
        {
            // String contains all numbers
            // Decide if $query is imdb-id or search string
            if (ctype_digit($query))
            {
                $filmId = "tt" . $query;
            } else
            {
                // $query is non-specific imdb-id
                // Search $query and use first result
                $query_search = $this->search($query, $category="tt");
                // If any films found
                if (count($query_search["titles"]) > 0)
                {
                    // Use first film returned from search
                    $filmId = $query_search["titles"][0]["id"];
                } else {
                    return $response;
                }
            }
        }

        // Set film url
        $film_url = $this->imdb_base_url . "title/$filmId/";
        // Load page
        $film_page = $this->loadDom($film_url);

        $response["title"] =        $this->textClean($this->htmlFind($film_page, '.title_wrapper h1')->text);
        $response["year"] =         $this->textClean($this->htmlFind($film_page, '.title_wrapper h1 #titleYear a')->text);
        $response["rating"] =       $this->textClean($this->htmlFind($film_page, '.ratings_wrapper .ratingValue strong span')->text);
        $response["rating_votes"] = $this->textClean($this->htmlFind($film_page, '.ratings_wrapper span[itemprop=ratingCount]')->text);
        $response["length"] =       $this->textClean($this->htmlFind($film_page, '.subtext time')->text);
        $response["plot"] =         $this->textClean($this->htmlFind($film_page, '.plot_summary .summary_text')->text);

        // If rating votes exists
        if ($this->count($response["rating_votes"]) > 0)
        {
            // Remove all non-numbers
            $response["rating_votes"] = preg_replace("/[^0-9 ]/", "", $response["rating_votes"]);
        }

        // Get poster src
        $response["poster"] = $this->htmlFind($film_page, '.poster img')->src;
        // If '@' appears in poster link
        if (preg_match('/@/', $response["poster"]))
        {
            // Remove predetermined size to get original image
            $response["poster"] = preg_split('~@(?=[^@]*$)~', $response["poster"])[0] . "@.jpg";
        }


        // Get all cast list
        $cast_list_all = $film_page->find('table.cast_list tr');
        if (count($cast_list_all) > 0) {
            // Loop all cast
            foreach ($cast_list_all as $cast_row)
            {
                // Skip row if no image (non-cast row)
                if (count($cast_row->find('.primary_photo')) == 0)
                {
                    continue;
                }

                // Create $actor array to store charactor data
                $actor = [
                  "actor" => "",
                  "actor_id" => "",
                  "character" => ""
                ];

                // Find character link
                $character_link = $cast_row->find('.character a');
                // If character link does not exist
                if (count($character_link) == 0)
                {
                    $actor["character"] = $this->textClean($this->htmlFind($cast_row, '.character')->text);
                } else
                {
                    $actor["character"] = $this->textClean($character_link->text);
                }

                // Find actor link
                $actor_row = $cast_row->find('td')[1];
                $actor_link = $actor_row->find('a');
                if (count($actor_link) > 0)
                {
                    // Set actor name to text within link
                    $actor["actor"] = $this->textClean($actor_link->text);
                    $actor["actor_id"] = $this->extractImdbId($actor_link->href);
                } else
                {
                    // No link found
                    // Set actor name to whatever is there
                    $actor["actor"] = $this->textClean($actor_row->text);
                }

                // Add $cast array to main cast
                array_push($response["cast"], $actor);
            }
        }


        // Fetch technical specs
        if ($techSpecs)
        {
            // Load technical specs page
            $film_techSpecs_url = $film_url . "technical";
            $film_techSpecs = $this->loadDom($film_techSpecs_url);

            // Search dom for techspecs table
            $techSpecs_table = $film_techSpecs->find('.dataTable tr');
            // If table exists
            if (count($techSpecs_table) > 0)
            {
                // Loop each row within table
                foreach ($techSpecs_table as $techSpecs_row)
                {
                    // Get row title
                    $row_title = $this->textClean($techSpecs_row->find('td')[0]->text);
                    // Get row value
                    $row_value = str_replace("  ", " <br> ", $this->textClean($techSpecs_row->find('td')[1]->text));

                    // Create response var
                    $row = [$row_title, $row_value];

                    // Add row to technical specs
                    array_push($response["technical_specs"], $row);
                }
            }
        }

        return $response;
    }


    /**
     * Load the html of a URL and return the DOM
     *
     * @param $url string - url to load html from
     *
     * @return object
     */
    private function loadDom($url) {
        // Create a new dom
        // Load html into dom
        $dom = new Dom;
        $dom->loadFromUrl($url);
        return $dom;
    }


    /**
     * Find object within DOM (if it exists) and reutrn an attribute
     *
     * @param $dom object - searchable dom object
     * @param $selection strting - css selector of what to find in dom
     * @param $return strting - what attribute to return (e.g. text, src, href)
     *
     * @return string|array
     */
    private function htmlFind($dom, $selection) {
        // Make selection within $dom object
        $found = $dom->find($selection);
        // If anything was found in selection
        if (count($found) > 0)
        {
            return $found;
        } else
        {
            return $this->emptyDomElement();
        }
    }


    /**
     * Extract an imdb-id from a string '/ttxxxxxxx/'
     * Returns string of id or empty string if none found
     *
     * @param $str string - string to extract ID from
     *
     * @return string
     */
    private function emptyDomElement() {
        $dom = new Dom;
        $dom->load('<a src="" href=""></a>');
        return $dom;
    }


    /**
     * Count (either array items or string length)
     *
     * @param $item array|string - item to count
     *
     * @return string
     */
    private function count($item) {
        return (is_array($item) ? count($item) : strlen($item));
    }


    /**
     * Extract an imdb-id from a string '/ttxxxxxxx/'
     * Returns string of id or empty string if none found
     *
     * @param $str string - string to extract ID from
     *
     * @return string
     */
    private function extractImdbId($str) {
        try {
            // Search string for 2 letters followed by numbers
            // '/yyxxxxxxx'
            preg_match('/\/[A-Za-z]{2}[0-9]+/', $str, $imdbIds);
            $id = substr($imdbIds[0], 1);
            if ($id == NULL)
            {
                throw new Exception("No id found");
            }
        } catch (Exception $err)
        {
            $id = "";
        }
        return $id;
    }


    /**
     * Cleans-up extracted text
     * trim + html specical char decode
     *
     * @param $str string - string to clean up
     *
     * @return string
     */
    private function textClean($str) {
        return trim(html_entity_decode($str));
    }


}
