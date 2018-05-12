<?php
namespace AppBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Symfony\Component\Finder\Finder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// use AppBundle\Controller\ValidateMetadataController;

class UploadListener
{
  /**
   * @var ObjectManager
   */
  private $om;

  private $client;

  private $validate;

  public function __construct(ObjectManager $om)
  {
    $this->om = $om;

    $this->client = new Client([
      'base_uri' => ''
    ]);

    // $this->validate = new ValidateMetadataController();
  }
  
  /**
   * @param object $event UploaderBundle's event object
   *
   * See (even though the documentation is a bit outdated):
   * https://github.com/1up-lab/OneupUploaderBundle/blob/master/Resources/doc/custom_logic.md
   */
  public function onUpload(PostPersistEvent $event)
  {
    $request = $event->getRequest();
    $file = $event->getFile();
    $post = $request->request->all();
    $full_path = !empty($post['fullPath']) ? $post['fullPath'] : false;
    $job_id = !empty($post['jobId']) ? $post['jobId'] : false;
    $prevalidate = (!empty($post['prevalidate']) && ($post['prevalidate'] === 'true')) ? true : false;
    $parent_record_id = !empty($post['parentRecordId']) ? $post['parentRecordId'] : false;

    // echo '<pre>';
    // var_dump($full_path);
    // echo '</pre>';
    // echo '<pre>';
    // var_dump($job_id);
    // echo '</pre>';
    // echo '<pre>';
    // var_dump($prevalidate);
    // echo '</pre>';
    // echo '<pre>';
    // var_dump($parent_record_id);
    // echo '</pre>';
    // die();

    // Pre-validate
    if ($prevalidate && $parent_record_id) {

      // Run the CSV validation.
      

      // Remove the uploaded CSV file.
      if (is_file($file->getPathname())) {
        unlink($file->getPathname());
      }
    }

    if (!$prevalidate && $job_id && $parent_record_id) {

      $job_id_directory = str_replace($file->getBasename(), '', $file->getPathname()) . $job_id;

      // Create a directory with the job ID as the name if not present.
      if (!file_exists($job_id_directory)) {
        mkdir($job_id_directory, 0755, true);
      }

      // If there's a full path, then build-out the directory structure.
      if ($full_path) {

        $new_directory_path = str_replace('/' . $file->getBasename(), '', $full_path);

        // Create a directory with the new_directory_path as the name if not present.
        if (!file_exists($job_id_directory . '/' . $new_directory_path)) {
          mkdir($job_id_directory . '/' . $new_directory_path, 0755, true);
        }
        // Move the file into the directory
        if (!file_exists($job_id_directory . '/' . $new_directory_path . '/' . $file->getBasename())) {
          rename($file->getPathname(), $job_id_directory . '/' . $new_directory_path . '/' . $file->getBasename());
        } else {
          // Remove the uploaded file???
          if (is_file($file->getPathname())) {
            unlink($file->getPathname());
          }
        }
      }

      // If there isn't a full path, then move the files into the root of the jobId directory.
      if (!$full_path) {
        // Move the file into the directory
        if (!file_exists($job_id_directory . '/' . $file->getBasename())) {
          rename($file->getPathname(), $job_id_directory . '/' . $file->getBasename());
        } else {
          // Remove the uploaded file???
          if (is_file($file->getPathname())) {
            unlink($file->getPathname());
          }
        }
      }

    }

    // // Remember to remove the already uploaded file
    // throw new UploadException('Nope, I don\'t do files.');
    
    // $request->success = true;
    return $request;
  }
}