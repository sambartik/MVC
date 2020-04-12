<?php namespace Core;
use \Exception;
use Configuration\Main as Config;

/**
 * Router class for routing requests to the controllers
 *
 * @author Samuel Bartík
 * @version 0.1
 * @copyright Copyright (c) 2018, Samuel Bartík
 * @method static void get($url, $handler) registers handler to the specified url for HTTP GET request
 * @method static void post($url, $handler) registers handler to the specified url for HTTP POST request
 * @method static void head($url, $handler) registers handler to the specified url for HTTP HEAD request
 * @method static void put($url, $handler) registers handler to the specified url for HTTP PUT request
 * @method static void delete($url, $handler) registers handler to the specified url for HTTP DELETE request
 * @method static void connect($url, $handler) registers handler to the specified url for HTTP CONNECT request
 * @method static void options($url, $handler) registers handler to the specified url for HTTP OPTIONS request
 * @method static void trace($url, $handler) registers handler to the specified url for HTTP TRACE request
 * @method static void patch($url, $handler) registers handler to the specified url for HTTP PATCH request
 */
class Router{
  /**
   * Scheme: [ HTTP_METHOD => [ URL => HANDLER,... ],... ]
   *
   * @var array Array that holds all routes.
   */
  private static $routes = [
    'GET' => [],
    'POST' => [],
    'HEAD' => [],
    'PUT' => [],
    'DELETE' => [],
    'CONNECT' => [],
    'OPTIONS' => [],
    'TRACE' => [],
    'PATCH' => []
  ];

  private static $middlewares = [];


  /**
   * Main method, which is responsible for dispatching a router, i.e. for processing the requests and calling
   * a specific handler.
   *
   * @param string $requestedURL
   * @param string $httpMethod
   * @return void
   * @throws Exception If route is not found, if handler pattern is wrong, or if handler isn't callable
   */
  public static function dispatch($requestedURL, $httpMethod){
    $requestedUrlExploded = self::parseURL($requestedURL);
    $matched = false;

    if( !isset(self::$routes[$httpMethod]) ) throw new Exception("Route not found", 404);

    foreach (self::$routes[$httpMethod] as $mappedUrl => $handler) {
      if($matched) return;

      $parameters = [];
      $mappedUrlExploded = self::parseURL($mappedUrl);

      for($i = 0; $i < count($mappedUrlExploded); $i++){
        $urlSegment = $mappedUrlExploded[$i];
        if( preg_match('/^\{.*\}$/', $urlSegment) ){
          if(!isset($requestedUrlExploded[$i])) break;

          $mappedUrlExploded[$i] = $requestedUrlExploded[$i];
          $parameters[] = $requestedUrlExploded[$i];
        }
      }

      if($requestedUrlExploded === $mappedUrlExploded){
        $matched = true;
        foreach (self::$middlewares as $middleware) {
          if(strncmp($requestedURL, $middleware["url"], strlen($middleware["url"])) != 0) continue;
          $middlewareResponse = $middleware["handler"]($requestedURL);
          if($middlewareResponse != null && $middlewareResponse == false)
            return;
        }
        if( is_callable($handler) ){
          call_user_func_array($handler, $parameters);
          return;
        } elseif( is_string($handler) && preg_match('/^(.*)@(.*)$/', $handler) ){

          $controllerAndMethodName = explode('@', $handler);
          $controllerAndMethodName[0] = Config::CONTROLLERS_NAMESPACE.$controllerAndMethodName[0];
          $controllerInstance = new $controllerAndMethodName[0];
          call_user_func_array(array($controllerInstance, $controllerAndMethodName[1]), $parameters);
          return;

        } else{ throw new Exception("Using wrong pattern, or function isn't callable: $mappedUrl $handler", 500); }
      }
    }

    throw new Exception("Route not found: $httpMethod $requestedURL", 404);
  }

  public static function __callStatic($methodName, $parameters){
    $methodName = strtoupper($methodName);
    if( !isset(self::$routes[$methodName]) || !is_string($parameters[0]) ) return;

    $url = trim($parameters[0], '/');
    $handler = $parameters[1];
    self::$routes[$methodName][$url] = $handler;
  }

  /**
   * Adds middleaware to the middleaware stack
   *
   * @param string $url
   * @param string $handler
   * @return void
   */
  public static function addMiddleware($url, $handler){
    self::$middlewares[] = [ "url" => $url, "handler" => $handler ];
  }

  /**
   * Method that will be used internally to parse all URLs into paths
   *
   * @param string $urlToParse
   * @return string
   */
  private static function parseURL($urlToParse){

    $parsedUrl = parse_url($urlToParse);
    $path = trim($parsedUrl["path"]);
    $path = trim($path, "/");

    return explode('/', $path);
  }
}
