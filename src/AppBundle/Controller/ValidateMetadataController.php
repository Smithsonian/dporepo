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
     * @param string $uploads_directory The upload directory
     * @return array Import result and/or any messages
     */
    public function construct_import_data($uploads_directory = null)
    {

      $json_object = array();

      if(!empty($uploads_directory)) {

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

          foreach ($json_array as $key => $value) {
            // Replace numeric keys with field names.
            foreach ($value as $k => $v) {
              $field_name = $target_fields[$k];
              unset($json_array[$key][$k]);
              // If present, bring the project_repository_id into the array.
              $json_array[$key][$field_name] = ($field_name === 'project_repository_id') ? (int)$id : null;
              // TODO: move into a vz-specific method?
              // [VZ IMPORT ONLY] Strip 'USNM ' from the 'subject_repository_id' field.
              $json_array[$key][$field_name] = ($field_name === 'subject_repository_id') ? (int)str_replace('USNM ', '', $v) : $v;
            }
            // Convert the array to an object.
            $json_object[$csv_key][] = (object)$json_array[$key];
          }

        }

      }

      // $this->u->dumper($json_object);

      return $json_object;
    }

    /**
     * Matches /admin/projects/validate_metadata/*
     *
     * Route_DISABLED("/admin/projects/validate_metadata/{id}", name="validate_metadata", methods={"GET"}, defaults={"id" = null})
     *
     * @param   int     $id           The project ID
     * @param   object  Request       Request object
     * @return  array                 Redirect or render
     */
    public function validate_metadata($id = null)
    {

      // $this->u->dumper($id);

      $blacklisted_fields = $csv_data = $json_validation_result = array();

      // TODO: feed this into this method.
      if(empty($id)) {
        $blacklisted_fields = array(
          'project_repository_id',
        );
      }

      $uploads_directory = __DIR__ . '/../../../web/uploads/';
      $csv_data = $this->construct_import_data($uploads_directory);

      if(!empty($csv_data)) {

        foreach ($csv_data as $csv_key => $csv_value) {
          // Projects
          if($csv_key === 0) {
            // $json_validation_result[$csv_key] = (object)$this->repoValidate->validateData($csv_data[$csv_key], 'project', $blacklisted_fields);
            // $this->u->dumper($json_validation_result);
          }
          // Subjects
          if($csv_key === 1) {
            // $this->u->dumper($csv_data[$csv_key]);
            $json_validation_result['subject'] = (object)$this->repoValidate->validateData($csv_data[$csv_key], 'subject', $blacklisted_fields);
            // $this->u->dumper($json_validation_result,0);
          }
          // Items
          if($csv_key === 2) {
            // $this->u->dumper($csv_data[$csv_key]);
            $json_validation_result['item'] = (object)$this->repoValidate->validateData($csv_data[$csv_key], 'item', $blacklisted_fields);
            // $this->u->dumper($json_validation_result);
          }
        }

      }

      return $json_validation_result;
    }

}
