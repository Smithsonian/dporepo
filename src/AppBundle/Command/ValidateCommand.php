<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;

use AppBundle\Controller\ImportController;
use AppBundle\Service\RepoValidateData;
use AppBundle\Service\RepoImport;

class ValidateCommand extends Command
{
  private $repoImport;
  private $validate;

  public function __construct(RepoImport $repoImport, RepoValidateData $validate)
  {
    // Repo Import service
    $this->repoImport = $repoImport;
    // Repo Validate Data service
    $this->validate = $validate;
    // TODO: move this to parameters.yml and bind in services.yml.
    $ds = DIRECTORY_SEPARATOR;
    // $this->uploads_directory = $ds . 'web' . $ds . 'uploads' . $ds . 'repository' . $ds;
    $this->uploads_directory = __DIR__ . '' . $ds . '..' . $ds . '..' . $ds . '..' . $ds . 'web' . $ds . 'uploads' . $ds . 'repository' . $ds;
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:validate')
      // The short description shown while running "php bin/console list".
      ->setDescription('Validate uploaded assets.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command will 1) validate a BagIt "bag", which consists of a "payload" (the arbitrary content) and "tags", which are metadata files intended to document the storage and transfer of the "bag", and 2) validate the integrity of uploaded files.')
      // Add arguments...
      ->addArgument('uuid', InputArgument::OPTIONAL, 'Job UUID.')
      ->addArgument('parent_project_id', InputArgument::OPTIONAL, 'parent_project_id.')
      ->addArgument('parent_record_id', InputArgument::OPTIONAL, 'parent_record_id.')
      ->addArgument('parent_record_type', InputArgument::OPTIONAL, 'parent_record_type.')
      ->addArgument('localpath', InputArgument::OPTIONAL, 'The path to the directory to validate.');
  }

  /**
   * Example:
   * php bin/console app:bagit-validate /var/www/html/dporepo/web/uploads/repository/4
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $result = '';

    // Outputs multiple lines to the console (adding "\n" at the end of each line).
    $output->writeln([
      '',
      '<bg=green;options=bold>  =====================  </>',
      '<bg=green;options=bold>  == Validate Assets ==  </>',
      '<bg=green;options=bold>  =====================  </>',
      '',
    ]);

    // First, check to see if the external storage is accessible (Drastic).
    // If the external storage is not accessible, then the job status will be set to 'failed', 
    // which will prevent any further validations and file transfers from executing.
    $external_storage_check = $this->getApplication()->find('app:transfer-files');

    $arguments_external_storage_check = array(
        'command' => 'app:transfer-files',
        'uuid' => $input->getArgument('uuid'),
        'check_external_storage' => true
    );

    $input_external_storage_check = new ArrayInput($arguments_external_storage_check);
    $return_external_storage_check = $external_storage_check->run($input_external_storage_check, $output);

    // If a localpath is passed, use it as the path to the files to validate.
    if ( !empty($input->getArgument('localpath')) ) {
      $directory_to_validate = $input->getArgument('localpath');
    }

    // If a localpath is NOT passed, check the database for a job with the 'job_status' set to 'bagit validation in progress'.
    if ( empty($input->getArgument('localpath')) ) {
      $directory_to_validate = $this->validate->needsValidationChecker('bagit validation in progress', $this->uploads_directory);
    }

    if (!empty($directory_to_validate)) {

      // Run the BagIt validation.
      $command_bagit = $this->getApplication()->find('app:bagit-validate');

      $arguments_bagit = array(
          'command' => 'app:bagit-validate',
          'localpath' => $directory_to_validate
      );

      $input_bagit = new ArrayInput($arguments_bagit);
      $return_bagit = $command_bagit->run($input_bagit, $output);

      // Run the files validation.
      $command_files = $this->getApplication()->find('app:files-validate');

      $arguments_files = array(
          'command' => 'app:files-validate',
          'localpath' => $directory_to_validate
      );

      $input_files = new ArrayInput($arguments_files);
      $return_files = $command_files->run($input_files, $output);      

      // Run the metadata ingest.
      $params = array(
        'uuid' => $input->getArgument('uuid'),
        'parent_project_id' => $input->getArgument('parent_project_id'),
        'parent_record_id' => $input->getArgument('parent_record_id'),
        'parent_record_type' => $input->getArgument('parent_record_type'),
      );

      $import_results = $this->repoImport->import_csv($params);
      
      // echo '<pre>';
      // var_dump($import_results);
      // echo '</pre>';
      // die();

      if (isset($import_results['errors'])) {
        $output->writeln('<comment>Metadata ingest failed. Job log IDs: ' . implode(', ', $import_results['errors']) . '</comment>');
      } else {
        $output->writeln('<comment>Metadata ingest complete. Job log IDs: ' . implode(', ', $import_results['job_log_ids']) . '</comment>');

        // Transfer files.
        $command_file_transfer = $this->getApplication()->find('app:transfer-files');

        $arguments_file_transfer = array(
            'command' => 'app:transfer-files',
            'uuid' => $input->getArgument('uuid')
        );

        $input_file_transfer = new ArrayInput($arguments_file_transfer);
        $return_file_transfer = $command_file_transfer->run($input_file_transfer, $output);
      }

    }

    // If there's no $directory_to_validate, display a message.
    if(empty($directory_to_validate)) {
      $output->writeln('<comment>No jobs found to validate</comment>');
    }   
  }
}