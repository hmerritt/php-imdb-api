# PHP IMDB API
PHP IMDB-API that can fetch film data and search results.


## Install
Install the latest version using [composer](https://getcomposer.org/).

```
$ composer require hmerritt/imdb-api
```


## Features

### Film Data
- Title
- Year
- Rating
- Poster
- Length
- Plot
- Trailer
  - id
  - link
- Cast
  - actor name
  - actor id
  - image
- Technical Specs

### Search
Search IMDB to return an array of films, people and companies


## Usage
```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use hmerritt\Imdb\Imdb;

$imdb = new Imdb();

// Search imdb
// -> returns array of films and people found
$imdb->search("Apocalypse");

// Get film data
// -> returns array of film data (title, year, rating...)
$imdb->film("tt0816692");
```

### Best Match
If you do not know the imdb-id of a film, a search string can be entered. This will search imdb and use the first result as the film to fetch data for.

> Note that this will take longer than just entering the ID as it needs to first search imdb before it can get the film data.

```php
// Searches imdb and gets the film data of the first result
// -> will return the film data for 'Apocalypse Now'
$imdb->film("Apocalypse");
```


### Technical Specifications
A films technical specifications can also be fetched by setting the `$techSpecs` param to `true`.

> Note that this will take longer as it needs to make a second request to load the tech specs.

```php
// Gets film data
// also gets technical specs
// -> returns array of film data with `technical_specs`
$imdb->film("tt0816692", $techSpecs=true);
```



## Dependencies
> All dependencies are managed automatically by `composer`.

- [php-html-parser](https://github.com/paquettg/php-html-parser)
