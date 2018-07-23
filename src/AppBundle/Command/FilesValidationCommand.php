<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use AppBundle\Controller\ValidateImagesController;

class FilesValidationCommand extends ContainerAwareCommand
{
  private $validate_images;
  protected $container;

  public function __construct(ValidateImagesController $validate_images)
  {
    // Validate Images Controller.
    $this->validate_images = $validate_images;
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:files-validate')
      // The short description shown while running "php bin/console list".
      ->setDescription('Validates files.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command allows you to validate files.')
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
      '<bg=green;options=bold>   Files Validator   </>',
      '<bg=green;options=bold>  =================  </>',
      '',
    ]);

    // If a localpath is passed, use it as the path to the files to validate.
    if ( !empty($input->getArgument('localpath')) ) {
      $directory_to_validate = $input->getArgument('localpath');
    }

    // If a localpath is NOT passed, check the database for a job with the 'job_status' set to 'in progress'.
    if ( empty($input->getArgument('localpath')) ) {
      $directory_to_validate = $this->validate_images->needs_validation_checker($container);
    }

    var_dump($directory_to_validate); die();

    if (!empty($directory_to_validate)) {

      // Parameters to pass to the validate_images method.
      $params = array(
        'localpath' => $directory_to_validate,
      );

      // Run the validation.
      $result = $this->validate_images->validate($params, $container);

      // Output validation results.
      if (isset($result['result'])) {
        $output->writeln('<comment>Validation Result:</comment>' . "\n");
        foreach ($result as $key => $value) {
          $output->writeln('---------------------------' . "\n");
          $output->writeln('File Name: ' . $value['file_name'] . "\n");
          foreach ($value as $k => $v) {
            $output->writeln($k . ': ' . $v . "\n");
          }
        }
      }

      // Output errors.
      if (isset($result['errors'])) {
        foreach ($result['errors'] as $key => $value) {
          $output->writeln('<error>' . $value . '</error>');
        }
        foreach ($result as $key => $value) {
          $output->writeln('---------------------------' . "\n");
          $output->writeln('Errors' . "\n");
          $output->writeln('File Name: ' . $value['file_name'] . "\n");
          foreach ($value['errors'] as $k => $v) {
            $output->writeln($v . "\n");
          }
        }
      }
    }

    // If there's no $directory_to_validate, display a message.
    if(empty($directory_to_validate)) {
      $output->writeln('<comment>No jobs found to validate</comment>');
    }   
  }
}