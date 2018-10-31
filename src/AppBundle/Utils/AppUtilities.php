<?php
// src/AppBundle/Utils/AppUtilities.php
namespace AppBundle\Utils;

use ReflectionClass;

class AppUtilities
{
  /**
   * @var string $project_directory
   */
  private $project_directory;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->project_directory = str_replace(DIRECTORY_SEPARATOR . 'web', '', $_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR;
  }

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

  /**
   * Patch Vendor Overrides
   * $this->u->patchVendorOverrides();
   *
   * @return null
   */
  public function patchVendorOverrides() {

    $vendor_directory = 'vendor/';
    $overrides_directory = 'src/VendorOverrides/';
    
    $overrides = array(
      'sabre' => '/http/lib/Client.php',
      'scholarslab' => '/bagit/lib/bagit_utils.php',
      'league' => '/flysystem-webdav/src/WebDAVAdapter.php',
      'league' => '/flysystem/src/Filesystem.php',
    );

    foreach ($overrides as $key => $value) {
      $source = $this->project_directory . $overrides_directory . $key . $value;
      $destination = $this->project_directory . $vendor_directory . $key . $value;
      $text_file = $this->project_directory . $vendor_directory . $key . '/overridden.txt';
      // Check to see if 1) the source is a file, and 2) if the overridden.txt file hasn't been written 
      // to the root of the vendor directory. (This means that the source has been updated via composer.)
      if (is_file($source) && !is_file($text_file)) {
        // Copy the source override to the destination.
        copy($source, $destination);
        // Write the overridden.txt file to the root of the vendor directory.
        $handle = fopen($text_file, 'w');
        fwrite($handle, '');
        // Before calling fclose on the resource, check if it’s still valid using is_resource.
        if (is_resource($handle)) fclose($handle);
      }
    }

  }
}