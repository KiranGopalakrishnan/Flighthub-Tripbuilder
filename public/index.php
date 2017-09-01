<?php

ini_set("display_errors",1);  //TODO: To be removed from the production build
error_reporting(E_ALL);       //TODO: To be removed from the production build
if (PHP_SAPI == 'cli-server') {
  // To help the built-in PHP dev server, check if the request was actually for
  // something which should probably be served as a static file
  $url  = parse_url($_SERVER['REQUEST_URI']);
  $file = __DIR__ . $url['path'];
  if (is_file($file)) {
    return false;
  }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';
//Load custom classes
require_once __DIR__.'/../src/classes/class.database.php';
require_once __DIR__.'/../src/classes/class.trip.php';
require_once __DIR__.'/../src/classes/class.airports.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});
//CORS control
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


$app->get('/api', function (Request $request, Response $response) {
  $file = '../public/docs.html';
   if (file_exists($file)) {
       return $response->write(file_get_contents($file));
   } else {
       throw new \Slim\Exception\NotFoundException($request, $response);
   }
});

/**
* Retrieves list of airports alphabetically
**@RequestType GET
*@param none
**@return JSON
**/
$app->get('/api/GET/airports[/limit={limitBy}]', function (Request $request, Response $response) {
  $result = null;
  $db = new database();
  $jsonCreater = new jsonCreater();
  $airports = new airports($db,$jsonCreater);
  $result = $airports->getAirports();
  //Setting the response to JSON
  $response = $response->withJson($result,200);
  return $response;

});
/**
* Get flights for a trip
**@RequestType GET
*@param fromAirport
*@param toAirport
**@return JSON
**/

$app->get('/api/GET/trips/flights/fromAirport={fromAirport}&toAirport={toAirport}', function (Request $request, Response $response) {

  $fromAirport = $request->getAttribute('fromAirport');
  $toAirport = $request->getAttribute('toAirport');
  $result = null;
  $db = new database();
  $jsonCreater = new jsonCreater();
  $airports = new airports($db,$jsonCreater);
  $trips = new trip($db,$jsonCreater,$airports);
  $result = $trips->getFlights($fromAirport,$toAirport);
  //Setting the response to JSON
  $response = $response->withJson($result,200);
  return $response;

});

/**
* Add flights to an existing trip
**@RequestType POST
*@param tripId - Passed via URL
*@param flightName
*@param fromAirportId
*@param toAirportId
**@return JSON
**/

$app->post('/api/POST/trips/flights/add', function (Request $request, Response $response) {

  $postData = $request->getParsedBody();
  $flightName = $postData["flightName"];
  $fromAirport = $postData["fromAirport"];
  $toAirport = $postData["toAirport"];

  $db = new database();
  $jsonCreater = new jsonCreater();
  $airports = new airports($db,$jsonCreater);
  $trips = new trip($db,$jsonCreater,$airports);
  $result = $trips->addFlight($flightName,$fromAirport,$toAirport);
  //Setting the response to JSON
  $response = $response->withJson($result,200);
  return $response;

});
/**
* Remove flights from a trip
**@RequestType DELETE
*@param tripId - Passed via URL
*@param flightId - Passed via URL
**@return JSON
**/
$app->delete('/api/DELETE/trips/{tripId}/flights/delete',  function (Request $request, Response $response) {

  $data = $request->getParsedBody();
  $flightId = $data["flightId"];
  $tripId = $request->getAttribute('tripId');
  $result = null;
  $db = new database();
  $jsonCreater = new jsonCreater();
  $airports = new airports($db,$jsonCreater);
  $trips = new trip($db,$jsonCreater,$airports);
  $result = $trips->deleteFlight($tripId,$flightId);
  //Setting the response to JSON
  $response = $response->withJson($result,200);
  return $response;

});


// Run app
$app->run();
