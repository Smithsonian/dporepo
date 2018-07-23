<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;

use AppBundle\Controller\BagitController;

class BagitValidationCommand extends ContainerAwareCommand
{
  private $bagit;
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
      ->setName('app:bagit-validate')
      // The short description shown while running "php bin/console list".
      ->setDescription('Validates a bag.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command allows you to validate a BagIt "bag", which consists of a "payload" (the arbitrary content) and "tags", which are metadata files intended to document the storage and transfer of the "bag".')
      // Add arguments...
      ->addArgument('localpath', InputArgument::OPTIONAL, 'The path to the directory to validate.');
      // ->addArgument('create_data_dir', InputArgument::OPTIONAL, 'Create a data directory? Default: true')
      // ->addArgument('overwrite_manifest', InputArgument::OPTIONAL, 'Overwrite the manifest? Default: false')
      // ->addArgument('flag_warnings_as_errors', InputArgument::OPTIONAL, 'Flag warnings as errors. Default: false');
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
      '<bg=green;options=bold>  =================  </>',
      '<bg=green;options=bold>   BagIt Validator   </>',
      '<bg=green;options=bold>  =================  </>',
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

      // Parameters to pass to the bagit_validate method.
      $params = array(
        'localpath' => $directory_to_validate,
        'flag_warnings_as_errors' => false,
      );

      // Run the validation.
      $result = $this->bagit->bagit_validate($params, $container);

      // Output validation results.
      if (isset($result['result'])) {
        $output->writeln('<comment>Validation Result: ' . $result['result'] . "</comment>\n");
      }

      // Output errors.
      if (isset($result['errors'])) {
        foreach ($result['errors'] as $key => $value) {
          $output->writeln('<error>' . $value . '</error>');
        }
      }

      $command = $this->getApplication()->find('app:files-validate');

      $arguments = array(
          'command' => 'app:files-validate',
          'localpath'    => $directory_to_validate
      );

      $greetInput = new ArrayInput($arguments);
      $returnCode = $command->run($greetInput, $output);
    }

    // If there's no $directory_to_validate, display a message.
    if(empty($directory_to_validate)) {
      $output->writeln('<comment>No jobs found to validate</comment>');
    }   
  }
}