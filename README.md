# PHP IMDB API

[![Latest Stable Version](https://poser.pugx.org/hmerritt/imdb-api/v/stable)](https://packagist.org/packages/hmerritt/imdb-api)
[![CircleCI](https://circleci.com/gh/hmerritt/php-imdb-api/tree/master.svg?style=svg)](https://circleci.com/gh/hmerritt/php-imdb-api/tree/master)
[![Coverage Status](https://coveralls.io/repos/github/hmerritt/php-imdb-api/badge.svg?branch=master)](https://coveralls.io/github/hmerritt/php-imdb-api?branch=master)

PHP IMDB-API that can fetch film data and search results.

## Install

Install the latest version using [composer](https://getcomposer.org/).

```
$ composer require hmerritt/imdb-api
```

## Usage

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use hmerritt\Imdb;

$imdb = new Imdb;

// Search imdb
// -> returns array of films and people found
$imdb->search("Apocalypse");

// Get film data
// -> returns array of film data (title, year, rating...)
$imdb->film("tt0816692");
```

### Options

| Name          | Type   | Default Value                         | Description                                                                                   |
| ------------- | ------ | ------------------------------------- | --------------------------------------------------------------------------------------------- |
| `curlHeaders` | array  | `['Accept-Language: en-US,en;q=0.5']` | Custom headers can be passed to `cURL` when fetching the IMDB page                            |
| `cache`       | bool   | `true`                                | Caches film data to speed-up future requests for the same film                                |
| `techSpecs`   | bool   | `true`                                | Loads a films technical specifications (this will take longer as it makes a separate request) |
| `category`    | string | `all`                                 | What category to search for (films `tt`, people `nm` or companies `co`)                       |

```php
$imdb = new Imdb;

//  Options are passed as an array as the second argument
//  These are the default options
$imdb->film("tt0816692", [
    'cache'        => true,
    'curlHeaders'  => ['Accept-Language: en-US,en;q=0.5'],
    'techSpecs'    => true,
]);

$imdb->search("Interstellar", [
    'category'     => 'all',
    'curlHeaders'  => ['Accept-Language: en-US,en;q=0.5'],
]);
```

### Best Match

If you do not know the imdb-id of a film, a search string can be entered. This will search imdb and use the first result as the film to fetch data for.

> Note that this will take longer than just entering the ID as it needs to first search imdb before it can get the film data.

```php
// Searches imdb and gets the film data of the first result
// -> will return the film data for 'Apocalypse Now'
$imdb->film("Apocalypse");
```

## Features

### Film Data

```
- Title
- Genres
- Year
- Length
- Plot
- Rating
- Rating Votes (# of votes)
- Poster
- Trailer
    - id
    - link
- Cast
    - actor name
    - actor id
    - character
    - avatar
    - avatar_hq (high quality avatar)
- Technical Specs
```

### Search

Search IMDB to return an array of films, people and companies

```
- Films
    - id
    - title
    - image
- People
    - id
    - name
    - image
- Companies
    - id
    - name
    - image
```

## Dependencies

> All dependencies are managed automatically by `composer`.

-   [php-html-parser](https://github.com/paquettg/php-html-parser)
-   [filebase](https://github.com/tmarois/Filebase)
