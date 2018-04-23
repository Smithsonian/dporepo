<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Finder\Finder;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class BagitController extends Controller
{
  /**
   * @var object $u
   */
  public $u;

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
  public function __construct(AppUtilities $u)
  {
    // Usage: $this->u->dumper($variable);
    $this->u = $u;
    // Directory paths
    $this->bagitPath = __DIR__ . '/../../../vendor/scholarslab/bagit/lib/bagit.php';
    $this->testBagFiles = __DIR__ . '/../../../web/uploads/testbag_files';
    $this->testBagDestination = __DIR__ . '/../../../web/uploads/testbag';
    $this->testBagTarballDestination = __DIR__ . '/../../../web/uploads';
  }

  /**
   * @Route("/admin/bagit", name="bagit-landing-page")
   *
   * @return render
   */
  public function indexAction()
  {
    return $this->render('default/bagit.html.twig');
  }

  /**
   * @Route("/admin/bagit/create", name="bagit-create")
   *
   * @return redirect
   */
  public function createAction()
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
   *
   * @param object $request  Symfony's request object
   * @return JsonResponse  The results of the check in JSON format
   */
  public function checkerAction(Request $request)
  {
    $folderpath = false;
    $checkfiles = [];

    // Check to see if the directory exists.
    if(is_dir(__DIR__ . '/../../..' . $request->request->get('folderpath'))) {
      $folderpath = __DIR__ . '/../../..' . $request->request->get('folderpath');
    }

    // If the directory is not found, don't continue and return a message.
    if(!$folderpath) return new JsonResponse(array('message' => 'Directory not found'));

    // Otherwise continue...
    require $this->bagitPath;
    $checkfiles = $this->validatefolder($folderpath);

    // If all of the BagIt files are present
    // (bag-info.txt, bagit.txt, manifest-sha1.txt, tagmanifest-sha1.txt),
    // proceed to validate the payload.
    if (empty($checkfiles)) {

      $bag = new \BagIt($folderpath);
      $validation = $bag->validate();

      if (count($validation) > 0) {
        foreach ($validation as $message) {
          $checkfiles[] = implode(': ', $message);
        }
      }
    }

    return new JsonResponse($checkfiles);
  }

  /**
   * @Route("/admin/bagit/update", name="bagit-update")
   *
   * @param object $request Symfony's request object
   * @return JsonResponse  The results of the check in JSON format
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
    $bag = new \BagIt($folderpath);
    $bag->update();

    return new JsonResponse( array("flag" => true) );
  }

  /**
   * @param int $path  The target directory path
   * @return array
   */
  public function validatefolder($path = null)
  {
    if(!empty($path)) {
      $missingFiles = [];

      // Check to see if the bag-info.txt is present.
      if (!file_exists($path . '/bag-info.txt')) $missingFiles[] = 'Baginfo';
      // Check to see if the manifest-sha1.txt is present.
      if (!file_exists($path . '/manifest-sha1.txt')) $missingFiles[] = 'Manifest'; //  && !file_exists($path.'/manifest-md5.txt')
      // Check to see if the bagit.txt is present.
      if (!file_exists($path . '/bagit.txt')) $missingFiles[] = 'Bagit';
      // Check to see if the tagmanifest-sha1.txt is present.
      if (!file_exists($path . '/tagmanifest-sha1.txt')) $missingFiles[] = 'Tag Manifest';
    }

    return $missingFiles;
  }

}