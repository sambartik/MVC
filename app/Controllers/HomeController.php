<?php namespace Core\Controllers;

class HomeController extends Controller {

	public function index($variable){
		$this->renderView('index', array("variable" => '/'.$variable));
	}

}
