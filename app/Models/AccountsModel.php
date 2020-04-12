<?php namespace Core\Models;

use Core\Database;
use Configuration\Main as Config;

/**
 * Model that manages user accounts - logins, registers etc.
 *
 * @author Samuel Bartík
 * @version 0.1
 * @copyright Copyright (c) 2018, Samuel Bartík
 */
class AccountsModel {

  public static function register($firstName, $lastName, $email, $password, $passwordRepeat, $recaptchaResponse = null){
    $response = [];

    if(Database::query("SELECT 1 FROM Users WHERE Email = ?", [$_POST["email"]]) != null){
      $response["success"] = false;
      $response["errors"][] = "User with this email address already exists!";
      return $response;
    }

    Database::query("INSERT INTO Users(FirstName, LastName, Password, Email, Newsletter) VALUES (?,?,?,?,?)", [ $_POST["first_name"], $_POST["last_name"], password_hash($_POST["password"], PASSWORD_DEFAULT), $_POST["email"], (isset($_POST["newsletter"])) ? true : false ]);
    Database::query("INSERT INTO ActivationTokens(UserID, Token) VALUES(LAST_INSERT_ID(), ?)", [ generateToken(80) ]);

    //$token = Database::query("SELECT * FROM Activation_Tokens WHERE User_ID = LAST_INSERT_ID()");

    //echo "true";
    //echo "<br> <a href='https://hromadaher.cz/activate/".$_POST["email"]."/".$token[0]["Token"]."'>Activate your account here.</a>";
    $response["success"] = true;
    $response["errors"] = null;
    return $response;
  }

  public static function login($email, $password) {
    $response = [];

    if(($user = Database::query("SELECT * FROM Users WHERE Email = ?", [$_POST["email"]])) == null){
      $response["success"] = false;
      $response["errors"][] = "This combination of email and password is incorrect.";
      return $response;
    } if(!password_verify($_POST["password"], $user[0]["Password"])){
      $response["success"] = false;
      $response["errors"][] = "This combination of email and password is incorrect.";
      return $response;
    } if($user[0]["Activated"] == false){
      $response["success"] = false;
      $response["errors"][] = "You have to activate your account first before you can proceed.";
      return $response;
    }

    $userID = $user[0]["ID"];
    $userIP = getUserIP();
    $browser = getBrowserInfo();
    $token = generateToken(80);
    $expireDate = time()+Config::SESSION_EXPIRE;

    Database::query("INSERT INTO SessionTokens(UserID, Token, UserAgent, Browser, OS, IP, ExpiresAt) VALUES(?,?,?,?,?,?,?)", [ $userID, $token, $browser["userAgent"], $browser["name"], $browser["platform"], $userIP, date("Y-m-d H:i:s", $expireDate)]);
    setcookie(Config::SESSION_COOKIE, $token, $expireDate, "/", null, true, true);

    $response["success"] = true;
    $response["errors"] = null;

    return $response;
  }

  public static function activateAccount($email, $token) {
    $response = [];

    if(($user = Database::query("SELECT * FROM Users WHERE Email = ?", [$email])) == null){
      $response["success"] = false;
      $response["errors"][] = "This combination of email and token is incorrect.";
      return $response;
    } if(Database::query("SELECT * FROM ActivationTokens WHERE UserID = ? AND Token = ?", [ $user[0]["ID"], $token ]) == null){
      $response["success"] = false;
      $response["errors"][] = "This combination of email and token is incorrect.";
      return $response;
    }

    Database::query("UPDATE Users SET Activated = 1 WHERE ID = ?", [ $user[0]["ID"] ]);
    Database::query("DELETE FROM ActivationTokens WHERE UserID = ?", [ $user[0]["ID"] ]);

    $response["success"] = true;
    $response["errors"] = null;
    return $response;
  }

  public static function getLoggedUser() {
    if(!isset($_COOKIE["login-token"]) || $_COOKIE["login-token"] == null)
      return;
    $token = Database::query("SELECT * FROM SessionTokens WHERE Token = ? AND UserAgent = ? AND IP = ? AND ExpiresAt > ?", [ $_COOKIE["login-token"], $_SERVER['HTTP_USER_AGENT'], getUserIP(), date("Y-m-d H:i:s", time()) ]);
    if($token == null)
      return;

    $user = Database::query("SELECT * FROM Users WHERE ID = ?", [ $token[0]["UserID"] ])[0];
    $userTokens = Database::query("SELECT * FROM SessionTokens WHERE UserID = ?", [$user["ID"]]);
    $user["tokens"] = $userTokens;

    return $user;
  }

}
