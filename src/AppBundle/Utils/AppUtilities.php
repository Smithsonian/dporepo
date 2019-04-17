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
    $this->project_directory = str_replace('web', '', $_SERVER['DOCUMENT_ROOT']);
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
   * Create UUID
   * 
   * Creates an RFC 4122 version 4 UUID
   * See: http://guid.us/GUID/PHP
   *
   * @return string
   */
  public function createUuid() {

    if (function_exists('com_create_guid')){
      return com_create_guid();
    } else {
      mt_srand((double)microtime()*10000); //optional for php 4.2.0 and up.
      $charid = strtoupper(md5(uniqid(rand(), true)));
      $hyphen = chr(45);// "-"
      $uuid = substr($charid, 0, 8).$hyphen
          .substr($charid, 8, 4).$hyphen
          .substr($charid,12, 4).$hyphen
          .substr($charid,16, 4).$hyphen
          .substr($charid,20,12);
      return $uuid;
    }

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
      'league1' => '/flysystem-webdav/src/WebDAVAdapter.php',
      'league2' => '/flysystem/src/Filesystem.php',
    );

    foreach ($overrides as $key => $value) {
      // The original key, for the text file to be written.
      $original_key = $key;
      // Remove numbers from $key (to deal with league1 and league2).
      $key = preg_replace('/[0-9]+/', '', $key);
      $source = $this->project_directory . $overrides_directory . $key . $value;
      $destination = $this->project_directory . $vendor_directory . $key . $value;
      $text_file = $this->project_directory . $vendor_directory . $key . '/overridden_' . $original_key . '.txt';
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


  /**
   * Takes an image specified by $path and generates a derivative image specified by width and optionally height.
   * Stores the derivative image in the same filesystem location as $path, named $new_filename.
   * @param $path Local filesystme path for an imag
   * @param $width
   * @param null $height
   * @param null $new_filename
   * @return string
   */
  public function resizeImage($path, $width, $height = NULL, $new_filename = NULL) {

    // Validate file exists
    if (!file_exists($path)) return "Image was not found";

    // Get image size, fail if unsupported image.
    if(!list($widthOriginal, $heightOriginal) = getimagesize($path)) {
      return "Error: Unsupported image type";
    }

    if(NULL !== $height && $height < 0) {
      return "Error: Height cannot be less than zero.";
    }
    if($width < 0) {
      return "Error: Width cannot be less than zero.";
    }

    // Get file name
    $filename = basename($path);

    // Get file path
    $filepath = str_replace($filename, "", $path);

    // If height is NULL set it to the corresponding dimension to $width
    // Based on the ratio of $widthOriginal to $heightOriginal
    if(NULL == $height || $height < 1) {
      $height = ceil($heightOriginal / $widthOriginal * $width);
    }

    // Create new file name with size
    if(NULL == $new_filename) {
      $new_filename = str_replace(".", "_" . $width . "x" . $height . ".", $filename);
    }

    // Create new file path
    $new_path = $filepath . $new_filename;

    // Get extension
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $ext = ($ext == 'jpeg') ? 'jpg' : $ext;

    //@todo these functions result in out of memory errors for large images
    // Cheesy hack to set the memory to unlimited, followed by re-setting it after the expensive function.
    $php_current_memory = ini_get('memory_limit');
    ini_set("memory_limit", -1);

    // Temp hack to limit the size of the file used to generate derivatives.
    switch($ext){
      case 'jpg':
        $img = imagecreatefromjpeg($path);
        break;
      case 'png':
        $img = imagecreatefrompng($path);
        break;
      default :
        print("Unsupported image type $ext\r\n");
        ini_set("memory_limit", $php_current_memory);
        return "Unsupported image type $ext";
    }

    $newImg = imagecreatetruecolor($width, $height);

    // If type is png this set of properties will preserve transparency
    if($ext == "png"){
      imagecolortransparent($newImg, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
      imagealphablending($newImg, false);
      imagesavealpha($newImg, true);
    }
    // Re-sampling image
    imagecopyresampled($newImg, $img, 0, 0, 0, 0, $width, $height, $widthOriginal, $heightOriginal);

    // If file exists it will be re-created
    if (file_exists($new_path)) {
      unlink($new_path);
    }

    // Create the same type of image as the original, based on the extension.
    switch($ext) {
      case 'jpg':
        // generate jpg image
        imagejpeg($newImg, $new_path);
        break;
      case 'png':
        // generate png image
        imagepng($newImg, $new_path);
        break;
    }

    ini_set("memory_limit", $php_current_memory);

    // Validate file exists
    if (file_exists($new_path)) {
      return "$width" . "x" . "$height";
    }
    else{
      return "Error: " . $new_path." was not created";
    }

  }

}