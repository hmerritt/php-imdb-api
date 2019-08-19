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
                   $section_table_rows = $search_section->find(".findList tr td.result_text");
                   if (count($section_table_rows) > 0)
                   {
                       // Loop each table row within the section
                       foreach ($section_table_rows as $section_row)
                       {
                           // Create row array
                           // To append to $response
                           $row = [];

                           // Link ojbect
                           $item_link = $section_row->find("a");

                           // Text value
                           $row["value"] = $item_link->text;
                           // Skip item if no text value
                           if ($row["value"] == "") {
                               continue;
                           }

                           // Link
                           $item_link = $item_link->href;
                           // Get the id of the link
                           @preg_match('/\/[A-Za-z]{2}[0-9]+/', $item_link, $imdbId_matches);
                           $link_imdbId = $imdbId_matches[0];
                           // Imdb Id
                           $row["id"] = $link_imdbId;

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
    * Load the html of a URL and return the DOM
    *
    * @return object
    */
    private function loadDom($url) {
        $dom = new Dom;
        $dom->loadFromUrl($url);
        return $dom;
    }

}
