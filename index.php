<?php
use Core\Autoloader;
use Core\Router;
use Core\Controllers\ErrorController;
use Configuration\Main as Config;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("app/Autoloader.php");
require("app/Helpers.php");

$loader = new AutoLoader;
$loader->addNamespace("\\Core\\", "app");
$loader->addNamespace("\\Configuration\\", "config");
$loader->register();

require("app/Routes.php");
try {
	Router::dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
} catch (Throwable $e) {
	//(new ErrorController())->index($e);
	echo $e;
}
