<?php namespace Core;
use Core\Router;

Router::get('/{variable}', 'HomeController@index');

/*Router::get('/hello/{variable}', function($variable){
  echo("Hello ".$variable."!");
});*/

Router::get('bio/prezentacia', function(){
  $filepath = "public/uploads/prezentacia.pptx";
  $fileName = "Sklenníkový Efekt.pptx";
// Process download
  if(file_exists($filepath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$fileName.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    flush(); // Flush system output buffer
    readfile($filepath);
    die();
  }
});
