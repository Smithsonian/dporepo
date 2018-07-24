<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;

use AppBundle\Controller\BagitController;

class ValidateCommand extends ContainerAwareCommand
{
  protected $container;

  public function __construct(BagitController $bagit)
  {
    // BagIt Controller.
    $this->bagit = $bagit;
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
      ->addArgument('localpath', InputArgument::OPTIONAL, 'The path to the directory to validate.');
  }

  /**
   * Example:
   * php bin/console app:bagit-validate /var/www/html/dporepo/web/uploads/repository/4
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $result = '';
    $container = $this->getContainer();

    // Outputs multiple lines to the console (adding "\n" at the end of each line).
    $output->writeln([
      '',
      '<bg=green;options=bold>  =====================  </>',
      '<bg=green;options=bold>  == Validate Assets ==  </>',
      '<bg=green;options=bold>  =====================  </>',
      '',
    ]);

    // If a localpath is passed, use it as the path to the files to validate.
    if ( !empty($input->getArgument('localpath')) ) {
      $directory_to_validate = $input->getArgument('localpath');
    }

    // If a localpath is NOT passed, check the database for a job with the 'job_status' set to 'in progress'.
    if ( empty($input->getArgument('localpath')) ) {
      $directory_to_validate = $this->bagit->needs_validation_checker($container);
    }

    if (!empty($directory_to_validate)) {

      // var_dump($directory_to_validate); die();

      // Run the BagIt validation.
      $command_bagit = $this->getApplication()->find('app:bagit-validate');

      $arguments_bagit = array(
          'command' => 'app:files-validate',
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
    }

    // If there's no $directory_to_validate, display a message.
    if(empty($directory_to_validate)) {
      $output->writeln('<comment>No jobs found to validate</comment>');
    }   
  }
}