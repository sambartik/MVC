<?php namespace Configuration;

class Main{
  const DATABASE = [
    'host' => 'localhost',
    'name' => 'database',
    'user' => 'root',
    'password' => ''
  ];
  const VIEWS_EXTENSION = '.view.php';
  const VIEWS_DIRECTORY = 'resources/views/';
  const CONTROLLERS_NAMESPACE = '\\Core\\Controllers\\';
  CONST SESSION_COOKIE = 'login-token';
  CONST SESSION_EXPIRE = ((3600*24)*7);
  CONST DEFAULT_LANGUAGE = 'en';
  CONST LANG_COOKIE_NAME = 'language';
  CONST LANG_COOKIE_EXPIRE = ((3600*24)*365);
}
