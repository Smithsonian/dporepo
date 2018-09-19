<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use AppBundle\Controller\ValidateImagesController;

class FilesValidationCommand extends Command
{
  private $validate_images;

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
      ->setDescription('Validate uploaded files.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command validates the integrity of uploaded files.')
      // Add arguments...
      ->addArgument('localpath', InputArgument::OPTIONAL, 'The path to the directory to validate.');
  }

  /**
   * Example:
   * php bin/console app:files-validate /var/www/html/dporepo/web/uploads/repository/4
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $result = '';

    // Outputs multiple lines to the console (adding "\n" at the end of each line).
    $output->writeln([
      '',
      '<bg=blue;options=bold>                   </>',
      '<bg=blue;options=bold> Files Validator   </>',
      '<bg=blue;options=bold> ================= </>',
      '',
    ]);

    if (!empty($input->getArgument('localpath'))) {

      // Parameters to pass to the validate_images method.
      $params = array(
        'localpath' => $input->getArgument('localpath'),
      );

      // Run the validation.
      $result = $this->validate_images->validate($params);

      // Output validation results.
      if (!empty($result)) {
        $output->writeln('<comment>Validated Files:</comment>' . "\n");
        foreach ($result as $key => $value) {
          $output->writeln('---------------------------' . "\n");
          foreach ($value as $k => $v) {
            if ($k !== 'errors') {
              $output->writeln($k . ': ' . $v . "\n");
            } else {
              if (!empty($v)) {
                foreach ($v as $ek => $ev) {
                  $output->writeln('<error>' . $ev . '</error>' . "\n");
                }
              }
            }
          }
        }
      }

    }

    // If there's no $input->getArgument('localpath'), display a message.
    if(empty($input->getArgument('localpath'))) {
      $output->writeln('<comment>No jobs found to validate</comment>');
    }   
  }
}