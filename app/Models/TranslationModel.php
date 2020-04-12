<?php namespace Core\Models;

use Core\Database;
use Configuration\Main as Config;

/**
 * Translation model that translates phrases from database and manage language cookie
 *
 * @author Samuel Bartík
 * @version 0.1
 * @copyright Copyright (c) 2018, Samuel Bartík
 */
class TranslationModel {

  /**
   * This is the main function which is used for translation.
   *
   * @param string $key - A phrase key for translation
   * @param string $translateTo - Translates to this language, if null current user's language will be used
   * @return string Returns phrase, or not_found if phrase wasn't found
   */
  public static function translate($key, $translateTo = null) {
    if($translateTo == null) {
      $translateTo = self::getLanguage()["Code"];
    } else {
      $translateTo = strtoupper($translateTo);
      if(Database::query("SELECT 1 FROM Languages WHERE Code = ?", [$translateTo]) == null)
        $translateTo = self::getLanguage()["Code"];
    }

    if($translateTo == null)
      return "not_found";

    $phrase = Database::query("SELECT PhraseValue FROM Phrases WHERE Language = ? AND PhraseKey = ?", [strtoupper($translateTo), $key]);

    if($phrase == null || $phrase[0]["PhraseValue"] == null){
      $defaultLanguage = Database::query("SELECT 1 FROM Languages WHERE Code = ?", [strtoupper(Config::DEFAULT_LANGUAGE)]);
      if($defaultLanguage == null)
        return "not_found";

      $phrase = Database::query("SELECT PhraseValue FROM Phrases WHERE Language = ? AND PhraseKey = ?", [strtoupper(Config::DEFAULT_LANGUAGE), $key]);
      if($phrase == null || $phrase[0]["PhraseValue"] == null)
        return "not_found";
    }

    return $phrase[0]["PhraseValue"];
  }


  /**
   * This function returns users language and manages the language cookie.
   *
   * @return array|null Returns user's language, or null if default language wasn't found as well.
   */
  public static function getLanguage(){
    if(isset($_COOKIE[Config::LANG_COOKIE_NAME])){
      $cookie = strtoupper($_COOKIE[Config::LANG_COOKIE_NAME]);
      $cookieLang = Database::query("SELECT * FROM Languages WHERE Code = ?", [$cookie]);

      if($cookieLang != null)
        return $cookieLang[0];
    }

    $clientsLang = Database::query("SELECT * FROM Languages WHERE Code = ?",[strtoupper(Config::DEFAULT_LANGUAGE)]);

    $acceptableLangs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $acceptableLangs = preg_replace("/;?q=0\.[0-9]|;q=1/", "", $acceptableLangs);
    $acceptableLangs = explode(",", $acceptableLangs);

    for($i = 0; $i < sizeof($acceptableLangs); $i++)
      $acceptableLangs[$i] = strtoupper(preg_replace("/-[a-zA-Z]{2}$/", "", $acceptableLangs[$i]));
    $acceptableLangs = array_unique($acceptableLangs);

    foreach($acceptableLangs as $lang){
      $tempLang = $tempLang = Database::query("SELECT * FROM Languages WHERE Code = ?", [$lang]);
      if($tempLang == null) continue;

      $clientsLang = $tempLang;
      break;
    }

    if($clientsLang == null || Database::query("SELECT 1 FROM Languages WHERE Code = ?", [$clientsLang[0]["Code"]]) == null){
      setcookie(Config::LANG_COOKIE_NAME, null, -1);
      return null;
    }

    setcookie(Config::LANG_COOKIE_NAME, strtolower($clientsLang[0]["Code"]), time() + Config::LANG_COOKIE_EXPIRE, '/', null, true);
    return $clientsLang[0];
  }

  /**
   * This functions returns all available languages from a database
   *
   * @return array All available languages
   */
  public static function getAvailableLanguages(){
    return Database::query("SELECT * FROM Languages");
  }

}
