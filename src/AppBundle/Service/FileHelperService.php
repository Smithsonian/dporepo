<?php
// src/AppBundle/Service/FileHelperService.php
namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;

use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Utils\AppUtilities;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FileHelperService
{
  /**
   * @var string $project_directory
   */
  private $project_directory;

  /**
   * @var string $uploads_directory
   */
  private $uploads_directory;

  /**
   * @var string $external_file_storage_path
   */
  private $external_file_storage_path;

  /**
   * @var string $external_file_storage_on
   */
  public $external_file_storage_on;

  /**
   * Constructor
   */
  public function __construct(string $uploads_directory, string $external_file_storage_on, string $external_file_storage_path)
  {
    $this->project_directory = str_replace('/web', '', $_SERVER['DOCUMENT_ROOT']);
    $this->uploads_directory = $uploads_directory;
    $this->external_file_storage_path = $external_file_storage_path;
    $this->external_file_storage_on = $external_file_storage_on;
  }

  /***
   * @param $file_path A dubious path that might be a filesystem path, a relative path under the web root, or a remote path,
   * formatted according to the web application's filesystem.
   * Returns an array representing useful variants of the incoming path.
   */
  public function getAlternateFilePaths($file_path, $verbose = false) {
    $path_type = 'unknown';
    $paths = array(
      'alternate_paths' => array(),
      'incoming_path' => $file_path
    );

    // Web root full path.
    // Nix: /Users/quoadmin/_http2/dporepo_dev/web
    // Windows: C:\xampp\htdocs\dporepo_dev\web
    if(!empty($_SERVER["DOCUMENT_ROOT"])) {
      $paths['verbose']['application_web_directory'] = $_SERVER["DOCUMENT_ROOT"];
      $paths['verbose']['application_directory'] = str_replace(DIRECTORY_SEPARATOR . "web", "", $_SERVER["DOCUMENT_ROOT"]);
    }
    else {
      // Document root is specific to the web server.
      // When this is run from the command line we don't get a document root.
      $paths['verbose']['application_directory'] = getcwd();
      $paths['verbose']['application_web_directory'] = $paths['verbose']['application_directory'] . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR;
    }

    $paths['verbose']['application_uploads_relative_path'] = $this->uploads_directory;
    // Nix: web/uploads/repository
    // Windows: web\uploads\repository

    // First, normalize the incoming path.
    // We might get a path with mixed separators, such as C:\xampp\htdocs\dpo\web/uploads/repository/etc/etc
    // The first thing we should do is normalize the separators to determine what kind of path it is.
    if(
      (strpos($file_path, "/") !== false && strpos($file_path, "\\") !== false)
      || (strpos($file_path, "/") !== false && DIRECTORY_SEPARATOR == "\\")
      || (strpos($file_path, "\\") !== false && DIRECTORY_SEPARATOR == "/")
    )
    {
      // Oh boy.
      // This is probably a local path, replace the separators and test it.
      $relative_path_compare = str_replace("\\", DIRECTORY_SEPARATOR, $file_path);
      $relative_path_compare = str_replace("/", DIRECTORY_SEPARATOR, $relative_path_compare);

      if(strpos($relative_path_compare, $paths['verbose']['application_web_directory']) === 0) {
        // This is a local path with foobared separators. Update it to normalized separators.
        $file_path = $relative_path_compare;
        //print("Assuming you meant: " . $file_path . "\r\n");
      }
      else {
        // Yuck- assume that this is a remote path and update the separators accordingly.
        $file_path = str_replace("\\", "/", $file_path);
        //print("Assuming you meant: " . $file_path . "\r\n");
      }
    }

    $paths['verbose']['application_uploads_directory'] = $paths['verbose']['application_directory'] . DIRECTORY_SEPARATOR . $this->uploads_directory;
    // Nix: /Users/quoadmin/_http2/dporepo_dev/web/uploads/repository
    // Windows: C:\xampp\htdocs\dporepo_dev\web\uploads\repository

    $paths['verbose']['system_directory_separator'] = DIRECTORY_SEPARATOR;
    // Nix: /
    // Windows: \

    $paths['verbose']['remote_storage_on'] = $this->external_file_storage_on;
    $paths['verbose']['remote_storage_path'] = NULL;
    if(isset($this->external_file_storage_on) && 1 == $this->external_file_storage_on) {
      $paths['verbose']['remote_storage_path'] = $this->external_file_storage_path;
      // /3DRepo/uploads/
    }

    // Normalize the path for comparison.
    $file_path_compare = str_replace("\\", DIRECTORY_SEPARATOR, $file_path);
    $file_path_compare = str_replace("/", DIRECTORY_SEPARATOR, $file_path_compare);

    // Now the fun part. Figure out what kind of path this is.
    // Depending on the kind of path we're given, generate the alternate path types.

    // An uploaded file might start with the full path for the uploads directory location on the local filesystem.
    if(strpos($file_path_compare, $paths['verbose']['application_uploads_directory']) === 0) {
      $path_type = "local_uploads_directory";
      $paths['alternate_paths']['local_uploads_directory'] = $file_path_compare;
      $paths['alternate_paths']['local_uploads_relative_path'] =
        "web" . str_replace($paths['verbose']['application_web_directory'], "", $file_path_compare);

      $paths['alternate_paths']['remote_storage_path'] = null;
      if($paths['verbose']['remote_storage_on'] == 1) {
        // Remote paths always use forward slashes.
        $paths['alternate_paths']['remote_storage_path'] = $paths['verbose']['remote_storage_path']
          . str_replace($paths['verbose']['application_uploads_directory'] . DIRECTORY_SEPARATOR, "",
            str_replace("\\", "/", $file_path));
      }
    }
    elseif(strpos($file_path_compare, $paths['verbose']['application_uploads_directory']) === 0) {
      $path_type = "local_uploads_directory";
      $paths['alternate_paths']['local_uploads_directory'] = $file_path_compare;
      $paths['alternate_paths']['local_uploads_relative_path'] = "web" . str_replace($paths['verbose']['application_web_directory'], "", $file_path_compare);

      $paths['alternate_paths']['remote_storage_path'] = null;
      if($paths['verbose']['remote_storage_on'] == 1) {
        // Remote paths always use forward slashes.
        $paths['alternate_paths']['remote_storage_path'] = $paths['verbose']['remote_storage_path']
          . str_replace($paths['verbose']['application_uploads_directory'] . DIRECTORY_SEPARATOR, "",
            str_replace("\\", "/", $file_path));
      }
    }
    // An uploaded file might start with the relative path for the uploads directory.
    elseif(strpos($file_path_compare, $paths['verbose']['application_uploads_relative_path']) === 0) {
      $path_type = "local_uploads_relative_path";
      $paths['alternate_paths']['local_uploads_relative_path'] = $file_path_compare;
      $paths['alternate_paths']['local_uploads_directory'] =
        str_replace(DIRECTORY_SEPARATOR . "webweb", DIRECTORY_SEPARATOR . "web", $paths['verbose']['application_web_directory'] . $file_path_compare);

      $paths['alternate_paths']['remote_storage_path'] = null;
      if($paths['verbose']['remote_storage_on'] == 1) {
        // Remote paths always use forward slashes.
        $paths['alternate_paths']['remote_storage_relative_path'] = str_replace("\\", "/", $file_path);
        $temp = $paths['verbose']['remote_storage_path'] . str_replace("\\", "/", $file_path);
        $temp = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $temp);
        $temp = str_replace("uploads/uploads", 'uploads', $temp);
        $paths['alternate_paths']['remote_storage_path'] = $temp;
      }
    }
    // An uploaded file might also conceivably start with the relative path for the uploads directory, minus "web".
    elseif(strpos($file_path_compare, str_replace("web" . DIRECTORY_SEPARATOR, "", $paths['verbose']['application_uploads_relative_path'])) === 0) {
      $path_type = "local_uploads_relative_path";
      $paths['alternate_paths']['local_uploads_relative_path'] = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, "web" . DIRECTORY_SEPARATOR . $file_path_compare);
      $paths['alternate_paths']['local_uploads_directory'] =
        str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR,$paths['verbose']['application_web_directory'] . DIRECTORY_SEPARATOR . $file_path_compare);

      $paths['alternate_paths']['remote_storage_path'] = null;
      if($paths['verbose']['remote_storage_on'] == 1) {
        // Remote paths always use forward slashes.
        $paths['alternate_paths']['remote_storage_path'] =
          str_replace("\\", "/",
            $paths['verbose']['remote_storage_path'] . str_replace($paths['verbose']['application_uploads_relative_path'] . DIRECTORY_SEPARATOR, "",
            $paths['alternate_paths']['local_uploads_relative_path']
          )
      );
      }
    }
    // Otherwise maybe this might be a remote path.
    elseif(null !== $paths['verbose']['remote_storage_path']) {
      if (strpos($file_path, $paths['verbose']['remote_storage_path']) === 0) {
        $path_type = "remote_storage_path";
        $paths['alternate_paths']['remote_storage_path'] = $file_path;

        $paths['alternate_paths']['local_uploads_relative_path'] =
          $paths['verbose']['application_uploads_relative_path'] . str_replace($paths['verbose']['remote_storage_path'], DIRECTORY_SEPARATOR, $file_path_compare);
        $paths['alternate_paths']['local_uploads_relative_path'] = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $paths['alternate_paths']['local_uploads_relative_path']);

        $paths['alternate_paths']['local_uploads_directory'] =
          $paths['verbose']['application_uploads_directory'] . str_replace($paths['verbose']['remote_storage_path'], DIRECTORY_SEPARATOR, $file_path_compare);
        $paths['alternate_paths']['local_uploads_directory'] = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $paths['alternate_paths']['local_uploads_directory']);
      }
      else {
        // Try adding or removing a slash to the path to see if it's a remote path.
        // If remote path does not start with a slash but given path does, remove the preceding slash.
        // Forward slashes are always used for remote paths, not DIRECTORY_SEPARATOR, which is a backslash on Windows.
        if (substr($file_path, 0, 1) === "/" && substr($paths['verbose']['remote_storage_path'], 0, 1) !== "/") {
          $file_path_compare = substr($file_path, 1);
        }
        // If remote path starts with a slash but given path does not, add a preceding slash
        elseif (substr($file_path, 0, 1) !== "/" && substr($paths['verbose']['remote_storage_path'], 0, 1) === "/") {
          $file_path_compare = "/" . $file_path;
        }
        if (strpos($file_path_compare, $paths['verbose']['remote_storage_path']) === 0) {
          $path_type = "remote_storage_path";
          $paths['alternate_paths']['remote_storage_path'] = $file_path_compare;

          $paths['alternate_paths']['local_uploads_relative_path'] = str_replace("//", "/", 'web/' . $file_path);
          $paths['alternate_paths']['local_uploads_relative_path'] = str_replace("\\" . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $paths['alternate_paths']['local_uploads_relative_path']);
          $paths['alternate_paths']['local_uploads_relative_path'] = str_replace("/", DIRECTORY_SEPARATOR, $paths['alternate_paths']['local_uploads_relative_path']);

          $paths['alternate_paths']['local_uploads_directory'] = str_replace("//", "/", $paths['verbose']['application_directory'] . "/" . $file_path);
          $paths['alternate_paths']['local_uploads_directory'] = str_replace("\\", DIRECTORY_SEPARATOR, $paths['alternate_paths']['local_uploads_relative_path']);
          $paths['alternate_paths']['local_uploads_directory'] = str_replace("/", DIRECTORY_SEPARATOR, $paths['alternate_paths']['local_uploads_relative_path']);
        }
        else {
          // Last guess:
          // This might be a partial remote path, minus the first part of the root.
          // e.g. /uploads/repository/2DADBA11-F3B5-589C-A2ED-31EAB04338EA\FSGA - Incense burner v1-t\data\f1978_40-master\processed\f1978_40-master.obj
          // instead of 3DRepo/uploads/repository/2DADBA11-F3B5-589C-A2ED-31EAB04338EA\FSGA - Incense burner v1-t\data\f1978_40-master\processed\f1978_40-master.obj

          // Might also be a partial remote path, minus the uploads path location.
          // e.g. 3BDA8165-FF44-7E73-405E-45D6C384EA3D\NPG%20-%20Lyndon%20B.%20Johnson%20v1-t\data\model\npg_91_28-delivery_web.obj

          $file_path_compare = str_replace("\\", "/", $file_path);
          $file_path_compare = str_replace("//", "/", $file_path_compare);

          if (substr($file_path_compare, 0, 1) !== "/") {
            $file_path_compare = "/" . $file_path_compare;
          }

          $remote_location_parts = explode("/", $paths['verbose']['remote_storage_path']);
          if(count($remote_location_parts) > 1) {
            $file_path_compare  = "/" . $remote_location_parts[1] . $file_path_compare;

            if(strpos($file_path_compare, $paths['verbose']['remote_storage_path']) === 0) {
              $path_type = "remote_storage_path";
              $paths['alternate_paths']['remote_storage_path'] = $file_path_compare;
              $paths['alternate_paths']['local_uploads_relative_path'] = 'web' . $file_path;
              $paths['alternate_paths']['local_uploads_directory'] =
                str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $paths['verbose']['application_web_directory'] . $file_path);
            }
          }
        }
      }
    } // If we have a remote storage path for comparison.

    $paths['incoming_path_type'] = $path_type;

    if(true !== $verbose) {
      unset($paths['verbose']);
    }

    return $paths;
  }

  /***
   * Given a path, determines whether it is a local full path, local relative path, or remote path.
   * The function getAlternateFilePaths is preferred because it returns the type and accurate/sanitized versions of the path.
   * This function is useful if the calling function is assured of the format of $file_path,
   * and $file_path can be used as-is once the path type is known.
   * @param $file_path
   */
  public function getFilePathType($file_path) {

    $path_data = getAlternateFilePaths($file_path);

    return $path_data['incoming_path_type'];
  }

  /**
   * Blindly replaces slashes depending on filesystem DIRECTORY_SEPARATOR value.
   * @param $file_path
   * @return mixed
   */
  public function normalizePathForFilesystem($file_path) {
    $new_file_path = $file_path;
    // If on a Windows based system, replace forward slashes with backslashes.
    if (DIRECTORY_SEPARATOR === '\\') {
      $new_file_path = str_replace('/', '\\', $file_path);
    }
    // If on a *nix based system, replace backslashes with forward slashes.
    if (DIRECTORY_SEPARATOR === '/') {
      $new_file_path = str_replace('\\', '/', $file_path);
    }
    return $new_file_path;
  }

}