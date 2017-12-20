<?php
// src/AppBundle/Utils/GumpParseErrors.php
namespace AppBundle\Utils;

class GumpParseErrors
{

	/**
   * Gump Parse Errors
   *
   * Parse errors returned by Gump.
   *
   * @param     array     $gump_failed_validation_array    The failed validation array returned by Gump
   * @param     string    $array_prepend                   Prepend the array with the supplied character
   * @return    array     The array of errors
   */
	public static function gump_parse_errors($gump_failed_validation_array, $array_prepend = false){
		$error_array = array();
		foreach($gump_failed_validation_array as $single_error){
			if($single_error["rule"] == "validate_required"){
				$error_array[$single_error["field"]] = ucwords(str_replace("_", " ",$single_error["field"])) . " is missing.";
			}else{
				if($single_error["value"]){
					$error_array[$single_error["field"]] = ucwords(str_replace("_", " ",$single_error["field"])) . " is invalid.";
				}
			}
		}
		if(!empty($array_prepend) && !empty($error_array)){
			return array($array_prepend => $error_array);
		}else{
			return $error_array;
		}
	}

}
