<?php namespace Core\Controllers;
use Exception;
use Configuration\Main as Config;

/**
 * Main controller class that all controllers will have to extend from
 *
 * @author Samuel Bartík
 * @version 0.1
 * @copyright Copyright (c) 2018, Samuel Bartík
 */
class Controller {
	/**
   * This method is responsible for rendering view and extracting all variables passed into this method as
   * associative array([VAR_NAME => DATA,...])
   *
   * @param String $view
   * @param Array $data
   * @return void
   * @throws Exception If view is not found
   */
	protected function renderView($view, $data = array()){
		if(file_exists(Config::VIEWS_DIRECTORY.$view.Config::VIEWS_EXTENSION)){
    	extract($data);
			require (Config::VIEWS_DIRECTORY.$view.Config::VIEWS_EXTENSION);
			return;
    }

    throw new Exception("View not found", 500);
	}

}
