<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use AppBundle\Service\RepoProcessingService;

class GetProcessingAssetsCommand extends ContainerAwareCommand
{
  protected $container;
  private $processing_service;

  public function __construct(RepoProcessingService $processing_service)
  {
    // Repo processing service.
    $this->processing_service = $processing_service;
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:get-processing-assets')
      // The short description shown while running "php bin/console list".
      ->setDescription('Get processing assets from the processing service.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command pulls processing assets from the processing service.');
  }

  /**
   * Example:
   * bin/console app:get-processing-assets
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $result = '';
    $errors = false;

    // Outputs multiple lines to the console (adding "\n" at the end of each line).
    $output->writeln([
      '',
      '<bg=blue;options=bold> Get Processing Assets </>',
      '',
      'Command: ' . 'php bin/console app:get-processing-assets' . "\n",
    ]);

    // Set up flysystem.
    $container = $this->getContainer();
    $flysystem = $container->get('oneup_flysystem.processing_filesystem');
    // Execute processing job.
    $result = $this->processing_service->get_processing_assets($flysystem);

    if (empty($result)) $output->writeln('<comment>No assets transferred, everything is up-to-date!</comment>');

    // // Output errors
    // if (!empty($result)) {
    //   foreach ($result as $key => $value) {
    //     foreach ($value as $k => $v) {
    //       if ($k !== 'errors') {
    //         $output->writeln($k . ': ' . $v . "\n");
    //       } else {
    //         if (!empty($v)) {
    //           $errors = TRUE;
    //           foreach ($v as $ek => $ev) {
    //             $output->writeln('<error>' . $ev . '</error>' . "\n");
    //           }
    //         }
    //       }
    //     }
    //   }
    // }

    if (isset($result['state'])) $output->writeln('<comment>Processed assets transferred to the repository</comment>');

    return $result;
  }
}