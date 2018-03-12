<?php
// src/AppBundle/Utils/AppUtilities.php
namespace AppBundle\Utils;

use ReflectionClass;

class AppUtilities
{
  /**
   * Dumper
   *
   * For debugging. Outputs data using var_dump(), encapsulated by the <pre> tag, with the option to die() or let it ride.
   * If an IP address is passed, then only that IP address will be able to view the output.
   *
   * @param   mixed   $data         The data value
   * @param   bool    $die          The data value
   * @param   string  $ip_address   The data value
   * @return  mixed   The formatted data
   */
  public function dumper($data = false, $die = true, $ip_address=false){
    if(!$ip_address || $ip_address == $_SERVER["REMOTE_ADDR"]){
      echo '<pre>';
      var_dump($data);
      echo '</pre>';
      if($die) die();
    }
  }

  /**
   * Get a protected property from an object.
   * See: https://stackoverflow.com/a/28352585/1298317
   *
   * @param   object  $obj   The object
   * @param   string  $prop  The property
   * @return  array          The data array
   */
  public function getProtectedProperty($obj, $prop) {
    $reflection = new ReflectionClass($obj);
    $property = $reflection->getProperty($prop);
    $property->setAccessible(true);
    return $property->getValue($obj);
  }

  /**
   * Remove Underscores and Convert to Title Case
   *
   * @param   string  $str  The string to modify
   * @return  string         The modified string
   */
  public function removeUnderscoresTitleCase($str) {
    return ucwords(str_replace('_', ' ', $str));
  }
}