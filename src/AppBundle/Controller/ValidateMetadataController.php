<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Service\RepoValidateData;
use Symfony\Component\DependencyInjection\Container;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class ValidateMetadataController extends Controller
{
    /**
     * @var object $u
     */
    public $u;

    /**
     * @var $repoValidate
     */
    private $repoValidate;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repoValidate = new RepoValidateData();
    }

    /**
     * Matches /admin/projects/validate_metadata/*
     *
     * @Route("/admin/projects/validate_metadata/{id}", name="validate_metadata", methods={"GET"}, defaults={"id" = null})
     *
     * @param   object  Request       Request object
     * @return  array                 Redirect or render
     */
    function validate_metadata(Request $request)
    {
      $id = $request->query->get('id');
      // TODO: feed this into this method.
      $blacklisted_fields = array(
        'project_repository_id',
      );

      // If there's a project ID passed, relate the uploaded data to that project ID.
      if(!empty($id)) {
        $this->u->dumper('do something with the id');
      }

      $uploads_directory = __DIR__ . '/../../../web/uploads/';

      $finder = new Finder();
      $finder->files()->in($uploads_directory);

      // Assign keys to each CSV, with projects first, subjects second, and items third.
      foreach ($finder as $file) {
        if(stristr($file->getRealPath(), 'projects')) {
          $csv[0] = $file->getContents();
        }
        if(stristr($file->getRealPath(), 'subjects')) {
           $csv[1] = $file->getContents();
        }
        if(stristr($file->getRealPath(), 'items')) {
           $csv[2] = $file->getContents();
        }
      }

      // Sort the CSV array by key.
      ksort($csv);

      $json_validation_result = array();

      foreach ($csv as $csv_key => $csv_value) {

        // Convert the CSV to JSON.
        $array = array_map('str_getcsv', explode("\n", $csv_value));
        $json = json_encode($array);

        // Convert the JSON to a PHP array.
        $json_array = json_decode($json, false);

        // Read the first key from the array, which is the column headers.
        $target_fields = $json_array[0];

        // TODO: move into a vz-specific method?
        // [VZ IMPORT ONLY] Convert field names to satisfy the validator.
        foreach ($target_fields as $tfk => $tfv) {
          // [VZ IMPORT ONLY] Convert the 'import_subject_id' field name to 'subject_repository_id'.
          if($tfv === 'import_subject_id') {
            $target_fields[$tfk] = 'subject_repository_id';
          }
        }

        // Remove the column headers from the array.
        array_shift($json_array);

        // $this->u->dumper($target_fields);
        // $this->u->dumper($json_array);

        foreach ($json_array as $key => $value) {
          // Replace numeric keys with field names.
          foreach ($value as $k => $v) {
            $field_name = $target_fields[$k];
            unset($json_array[$key][$k]);
            $json_array[$key][$field_name] = $v;
          }
          // Convert the array to an object.
          $json_object[$csv_key][] = (object)$json_array[$key];
        }

        // $this->u->dumper($json_array);
        // $this->u->dumper($json_object);

        // Projects
        if($csv_key === 0) {
          // $json_validation_result[$csv_key] = (object)$this->repoValidate->validateData($json_object[$csv_key], 'project', $blacklisted_fields);
          // $this->u->dumper($json_validation_result);
        }
        // Subjects
        if($csv_key === 1) {
          // $this->u->dumper($json_object[$csv_key]);
          $json_validation_result['subject'] = (object)$this->repoValidate->validateData($json_object[$csv_key], 'subject', $blacklisted_fields);
          // $this->u->dumper($json_validation_result,0);
        }
        // Items
        if($csv_key === 2) {
          // $this->u->dumper($json_object[$csv_key]);
          $json_validation_result['item'] = (object)$this->repoValidate->validateData($json_object[$csv_key], 'item', $blacklisted_fields);
          // $this->u->dumper($json_validation_result);
        }

      }

      $response = new JsonResponse($json_validation_result);
      return $response;
    }

}
