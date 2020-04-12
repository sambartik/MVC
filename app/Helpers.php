<?php

function generateToken($length) {
  $token = "";
  $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
  $codeAlphabet.= "0123456789";
  $max = strlen($codeAlphabet);

  for ($i=0; $i < $length; $i++)
    $token .= $codeAlphabet[random_int(0, $max-1)];
  return $token;
}

function getBrowserInfo() {
  $userAgent = $_SERVER['HTTP_USER_AGENT'];
  $browser = 'Unknown';
  $platform = 'Unknown';
  $version= "";

  //First get the platform?
  if (preg_match('/linux/i', $userAgent))
      $platform = 'Linux';
  elseif (preg_match('/macintosh|mac os x/i', $userAgent))
      $platform = 'Apple';
  elseif (preg_match('/windows|win32/i', $userAgent))
      $platform = 'Windows';

  // Next get the name of the useragent yes seperately and for good reason
  if(preg_match('/MSIE/i',$userAgent) && !preg_match('/Opera/i', $userAgent)) {
      $browser = 'Internet Explorer';
      $ub = "MSIE";
  }
  elseif(preg_match('/Firefox/i', $userAgent)) {
      $browser = 'Mozilla Firefox';
      $ub = "Firefox";
  }
  elseif(preg_match('/OPR/i', $userAgent)) {
      $browser = 'Opera';
      $ub = "Opera";
  }
  elseif(preg_match('/Chrome/i', $userAgent)) {
      $browser = 'Google Chrome';
      $ub = "Chrome";
  }
  elseif(preg_match('/Safari/i', $userAgent)) {
      $browser = 'Apple Safari';
      $ub = "Safari";
  }
  elseif(preg_match('/Netscape/i',$userAgent)) {
      $browser = 'Netscape';
      $ub = "Netscape";
  }

  // finally get the correct version number
  $known = array('Version', $ub, 'other');
  $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
  if (!preg_match_all($pattern, $userAgent, $matches)) {
      // we have no matching number just continue
  }

  // see how many we have
  $i = count($matches['browser']);
  if ($i != 1) {
      //we will have two since we are not using 'other' argument yet
      //see if version is before or after the name
      if (strripos($userAgent,"Version") < strripos($userAgent,$ub)){
          $version= $matches['version'][0];
      }
      else {
          $version= $matches['version'][1];
      }
  }
  else {
      $version= $matches['version'][0];
  }

  // check if we have a number
  if ($version==null || $version=="") {$version="?";}

  return array(
      'userAgent' => $userAgent,
      'name'      => $browser,
      'version'   => $version,
      'platform'  => $platform,
      'pattern'    => $pattern
  );
}

function getUserIP() {
  $ipaddress = '';
  if (isset($_SERVER['HTTP_CLIENT_IP']))
      $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
  else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
      $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
  else if(isset($_SERVER['HTTP_X_FORWARDED']))
      $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
  else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
      $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
  else if(isset($_SERVER['HTTP_FORWARDED']))
      $ipaddress = $_SERVER['HTTP_FORWARDED'];
  else if(isset($_SERVER['REMOTE_ADDR']))
      $ipaddress = $_SERVER['REMOTE_ADDR'];
  else
      $ipaddress = 'UNKNOWN';

  return $ipaddress;
}

function formatArray($array){
  if($array == null) return;

  $result = [];
  foreach($array as $key => $value){
    if(gettype($value) == 'array'){
      foreach($array[$key] as $key2 => $value2){
        $formatedKey = $key2;
        if($key2 != strtoupper($key2))
          $formatedKey = preg_replace('/(?<!^)([A-Z])/', '_\\1', $formatedKey);
        $formatedKey = strtolower($formatedKey);
        $result[$key][$formatedKey] = $value2;
      }
    } else{
      $formatedKey = $key;
      if($key != strtoupper($key))
        $formatedKey = preg_replace('/(?<!^)([A-Z])/', '_\\1', $formatedKey);
      $formatedKey = strtolower($formatedKey);
      $result[$formatedKey] = $value;
    }
  }

  return $result;
}
