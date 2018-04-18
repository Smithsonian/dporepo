<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Finder\Finder;

class BagitController extends Controller
{

  /**
   * @var string $bagitPath
   */
  public $bagitPath;

  /**
   * @var string $testBagFiles
   */
  public $testBagFiles;

  /**
   * @var string $testBagDestination
   */
  public $testBagDestination;

  /**
   * @var string $testBagTarballDestination
   */
  public $testBagTarballDestination;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->bagitPath = __DIR__ . '/../../../vendor/scholarslab/bagit/lib/bagit.php';
    $this->testBagFiles = __DIR__ . '/../../../web/uploads/testbag_files';
    $this->testBagDestination = __DIR__ . '/../../../web/uploads/testbag';
    $this->testBagTarballDestination = __DIR__ . '/../../../web/uploads';
  }

  /**
   * @Route("/admin/bagit", name="bagit-landing-page")
   */
  public function indexAction(Request $request)
  {
    return $this->render('default/bagit.html.twig');
  }

  /**
   * @Route("/admin/bagit/create", name="bagit-create")
   */
  public function createAction(Request $request)
  {
    require $this->bagitPath;

    // Create a new bag at $this->testBagDestination
    $bag = new \BagIt($this->testBagDestination);
    
    $finder = new Finder();
    $finder->files()->in($this->testBagFiles);

    // Add files to the bag
    foreach ($finder as $file) {
      $bag->addFile($file->getRealPath(), basename($file->getRealPath()));
    }

    // Update the hashes
    $bag->update();

    // Create a tarball with the name testbag.tgz
    $bag->package($this->testBagTarballDestination . '/testbag.tgz');

    $this->addFlash('message', 'Bag successfully created!');
    return $this->redirect('/admin/bagit');
  }

  /**
   * @Route("/admin/bagit/check", name="bagit-check")
   */
  public function checkerAction(Request $request)
  {
    $folderpath = false;

    // Check to see if the directory exists.
    if(is_dir(__DIR__ . '/../../..' . $request->request->get('folderpath'))) {
      $folderpath = __DIR__ . '/../../..' . $request->request->get('folderpath');
    }

    // If the directory is not found, don't continue and return a message.
    if(!$folderpath) return new JsonResponse(array('message' => 'Directory not found'));

    // Otherwise continue...
    require $this->bagitPath;
    $checkfiles = $this->validatefolder($folderpath);

    if (isset($checkfiles['files']) && count($checkfiles['files']) === 0) {

      $bag = new \BagIt($folderpath);
      $validation = $bag->validate();

      if (count($validation) > 0) {
        foreach ($validation as $message) {
          $full_message = '';
          foreach ($message as $k => $value) {
            $full_message .= ' ' . $value;
          }
          $checkfiles[] = $full_message;
        }
      }
    }

    if(count($checkfiles) > 0) {
      return new JsonResponse($checkfiles);
    } else {
      return new JsonResponse(array());
    }

  }

  /**
   * @Route("/admin/bagit/update", name="bagit-update")
   */
  public function updateAction(Request $request)
  {

    $folderpath = false;

    // Check to see if the directory exists.
    if(is_dir(__DIR__ . '/../../..' . $request->request->get('folderpath'))) {
      $folderpath = __DIR__ . '/../../..' . $request->request->get('folderpath');
    }

    // If the directory is not found, don't continue and return a message.
    if(!$folderpath) return new JsonResponse(array('message' => 'Directory not found'));

    // Otherwise continue...
    require $this->bagitPath;
    //$fileparts = pathinfo($folderpath);

    $bag = new \BagIt($folderpath);
    $bag->update();

    return new JsonResponse( array("flag" => true) );
  }

  public function validatefolder($path)
  {

    $missingFiles = [];

    // Check to see if the data directory exists.
    if(!is_dir($path . '/data')) {
      $missingFiles[] = 'data directory';
    }
    // Check to see if there are files in the data direcotry.
    // TODO: Check to see if the total amount of files matches what's in the manifest.
    if(is_dir($path . '/data')) {
      $files = array();
      $finder = new Finder();
      $finder->files()->in($path . '/data');
      // Construct an array of files found in the data directory.
      foreach ($finder as $file) {
        $files[] = $file->getRealPath();
      }
      // If the directory is empty, add it to the missingFiles array.
      if(empty($files)) $missingFiles[] = 'data directory is empty';
    }
    // Check to see if the bag-info.txt is present.
    if (!file_exists($path . '/bag-info.txt')) $missingFiles[] = 'Baginfo';
    // Check to see if the manifest-sha1.txt is present.
    if (!file_exists($path . '/manifest-sha1.txt')) $missingFiles[] = 'Manifest'; //  && !file_exists($path.'/manifest-md5.txt')
    // Check to see if the bagit.txt is present.
    if (!file_exists($path . '/bagit.txt')) $missingFiles[] = 'Bagit';
    // Check to see if the tagmanifest-sha1.txt is present.
    if (!file_exists($path . '/tagmanifest-sha1.txt')) $missingFiles[] = 'Tag Manifest';

    $missing = array("files" => $missingFiles);

    return $missing;
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
  public function dumper($data = false, $die = true, $ip_address=false)
  {
    if(!$ip_address || $ip_address == $_SERVER["REMOTE_ADDR"]){
      echo '<pre>';
      var_dump($data);
      echo '</pre>';
      if($die) die();
    }
  }

}