<?php
/**
*  PHP Imdb Api
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*
* @package Imdb
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
                           $item_link = $section_row->find("td.result_text a");
                           // Text value
                           $row["title"] = $item_link->text;
                           // Skip item if no text value
                           if ($row["title"] == "") {
                               continue;
                           }

                          // Image object
                          $item_image = $section_row->find("td.primary_photo img");
                          $row["image"] = "";
                          if (count($item_image) > 0) {
                              $row["image"] = $item_image->src;
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
           return $response;
       } else
       {
           // no results found
           return $response;
       }
   }


    /**
     * Get indivial film data via tt-id
     *
     * @param $query string - film name or imdb-id
     *
     * @return array
     */
    public function film($query) {
        // Define response array
        $response = [
          "title" => "",
          "year" => "",
          "length" => "",
          "rating" => "",
          "poster" => "",
          "plot" => "",
          "cast" => []
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

        $response["title"] =  $this->textClean($film_page->find('.title_wrapper h1')->text);
        $response["year"] =   $this->textClean($film_page->find('.title_wrapper h1 #titleYear a')->text);
        $response["rating"] = $this->textClean($film_page->find('.ratings_wrapper .ratingValue strong span')->text);
        $response["length"] = $this->textClean($film_page->find('.subtext time')->text);
        $response["poster"] = $film_page->find('.poster img')->src;
        $response["plot"] =   $this->textClean($film_page->find('.plot_summary .summary_text')->text);

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
                    $actor["character"] = $this->textClean($cast_row->find('.character')->text);
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
            if ($id == null || $id == undefined || count($id) == 0)
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
