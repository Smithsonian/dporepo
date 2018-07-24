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
      ->setDescription('Validate a bag created via BagIt.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command validates a BagIt "bag", which consists of a "payload" (the arbitrary content) and "tags", which are metadata files intended to document the storage and transfer of the "bag".')
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
      '<bg=blue;options=bold>                   </>',
      '<bg=blue;options=bold> BagIt Validator   </>',
      '<bg=blue;options=bold> ================= </>',
      '',
    ]);

    if (!empty($input->getArgument('localpath'))) {

      // Parameters to pass to the bagit_validate method.
      $params = array(
        'localpath' => $input->getArgument('localpath'),
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

    }

    // If there's no $input->getArgument('localpath'), display a message.
    if(empty($input->getArgument('localpath'))) {
      $output->writeln('<comment>No jobs found to validate</comment>');
    }   
  }
}