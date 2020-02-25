<?php

// Return data as json
header('Content-Type: application/json');

// Import libs
require "../vendor/autoload.php";
use hmerritt\Imdb;


//  Get url params
$type = $_GET["type"];
$query = $_GET["q"];

// Initialise Imdb
$imdb = new Imdb();

switch($type)
{
	case "film":
		$response = $imdb->film($query);
		break;

	case "search":
		$response = $imdb->search($query);
		break;
}

//  -> Return loaded film data
echo json_encode($response, JSON_PRETTY_PRINT);
