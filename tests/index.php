<?php

// Return data as json
header('Content-Type: application/json');

// Import libs
require "../vendor/autoload.php";
use hmerritt\Imdb\Imdb;


// Get url search param
$q = $_GET["film"];

// Initialise Imdb
// Load film data
$imdb = new Imdb();
$film = $imdb->film($q);  // tt0816692  tt8633464

// Return loaded film data
echo json_encode($film, JSON_PRETTY_PRINT);
