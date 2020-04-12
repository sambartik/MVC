<?php namespace Core\Controllers;

class ErrorController extends Controller {
	public function index($error){
    $variables = [
      "error_code" => $error->getCode(),
      "error_message" => $error->getMessage(),
      "error_trace" => $error->getTraceAsString()
    ];
    http_response_code($variables["error_code"]);
		$this->renderView('error', $variables);
	}
}
