<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class ValidateImagesController extends Controller
{
    /**
     * @var object $u
     */
    public $u;

    /**
     * @var string $uploads_directory
     */
    private $uploads_directory;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u)
    {
      // Usage: $this->u->dumper($variable);
      $this->u = $u;
      // TODO: move this to parameters.yml and bind in services.yml.
      $ds = DIRECTORY_SEPARATOR;
      // $this->uploads_directory = $ds . 'web' . $ds . 'uploads' . $ds . 'repository' . $ds;
      $this->uploads_directory = __DIR__ . '' . $ds . '..' . $ds . '..' . $ds . '..' . $ds . 'web' . $ds . 'uploads' . $ds . 'repository' . $ds;
    }

    /**
     * Validate Images
     *
     * @param int  $job_id  The job ID
     * @return array 
     */
    public function validate_images($job_id = null)
    {

      $data = array();

      // Throw a 404 if the job record doesn't exist.
      if (empty($job_id)) throw new Exception('The Job directory doesn\'t exist');

      $search_directory = $this->uploads_directory . $job_id . DIRECTORY_SEPARATOR;

      // Search for the data directory.
      $finder = new Finder();
      $finder->path('data')->name('*.jpg');
      $finder->in($search_directory);

      foreach ($finder as $file) {

        $this->u->dumper($file->getPathname());

        // $path_array = explode(DIRECTORY_SEPARATOR, $file->getPathname());
        // $last_path_array_item = array_pop($path_array);

        // if (is_dir($file->getPathname()) && ($last_path_array_item === 'data')) {
        //   $target_directory = $file->getPathname() . $directory_path;
        // }
      }

      return $data;
    }

}
