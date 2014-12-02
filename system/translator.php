<?php

require_once MODEL.'session.php';

/**
 * Translator class
 * To use it :
 * <ol>
 * <li>Add the json with translation you want in app/config. <strong>Be carefull ! There is no index anti-fail</strong></li>
 * <li>Then register the array you want by adding it in init() function</li>
 * <li>You can now access the value by Translator::get("the.subcategory.name") or by get / index in /translator[:name]</li>
 * </ol>
 */
class Translator{
	
	private static $languages = array();
	private static $prefered_language = 'fr';

	public static function init() {
		
		
		self::registerLanguage(array("fr", "en"));


		if(Session::isActive()){
			self::$prefered_language = Session::get()->getLanguageSetting() == "auto" ? self::GetLanguageFromHttpRequest() : Session::get()->getLanguageSetting();
		}else{
			self::$prefered_language = self::GetLanguageFromHttpRequest();
		}

	}
	/**
	 *
	 * @param The $name of the string "menu.home"
	 * @param $language Optional if you want to override
	 */
	public static function get($name = "all", $language = false) {
		$array_navigator = self::getLanguageArray($language);
		if($name == "all"){
			return $array_navigator;
		}
		$array_requested_key = explode(".", $name);
		foreach ($array_requested_key as $key) {
			if(isset($array_navigator[$key])){
			$array_navigator = $array_navigator[$key];				
			}else{
				return "null";
			}
		}

		return $array_navigator;
	}

	/**
	 *
	 * @return string The language letters
	 */
	private static function GetLanguageFromHttpRequest() {
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$string_accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}else{
			return 'fr';
		}
		$string_accept_array = preg_split( "#(,|;)#", $string_accept);

		foreach ($string_accept_array as $accept) {
			$accept = strtolower($accept);
			if(Utils::stringStartsWith($accept, "q=")){
				continue;
			}else{
				$accept = explode("-", $accept)[0];

				if(in_array($accept, array_keys(self::$languages))){
					return $accept;
				}
			}
		}

		return "fr";

	}

	private static function getLanguageArray($language = false){
		if(!$language){
			return self::$languages[self::$prefered_language];
		}else{
			return isset(self::$languages[self::$prefered_language]) ? self::$languages[self::$prefered_language] : self::$languages["fr"];
		}
	}
	private static function registerLanguage($languages) {

		foreach ($languages as $language) {

			$filename = TRANSLATIONS.$language."_translations.json";
			if(file_exists($filename)){
				$lang_array = json_decode(file_get_contents($filename), true);
				
				self::$languages = array_merge(self::$languages, array($language => $lang_array));
			}
		}
				
		
	}

}