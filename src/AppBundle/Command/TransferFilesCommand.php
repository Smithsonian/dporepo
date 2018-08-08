<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use AppBundle\Service\RepoFileTransfer;

class TransferFilesCommand extends ContainerAwareCommand
{
  protected $container;
  private $fileTransfer;

  public function __construct(RepoFileTransfer $fileTransfer)
  {
    // Repo File Transfer service.
    $this->fileTransfer = $fileTransfer;
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:transfer-files')
      // The short description shown while running "php bin/console list".
      ->setDescription('Transfer uploaded files to external storage.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command transfers uploaded files to an external storage location.')
      // Add arguments...
      ->addArgument('job_id', InputArgument::REQUIRED, 'Job ID.');
  }

  /**
   * Example:
   * php bin/console app:files-transfer-files 208
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $result = '';

    // Outputs multiple lines to the console (adding "\n" at the end of each line).
    $output->writeln([
      '',
      '<bg=blue;options=bold>                   </>',
      '<bg=blue;options=bold> Transfer Files    </>',
      '<bg=blue;options=bold> ================= </>',
      '',
    ]);

    if (!empty($input->getArgument('job_id'))) {

      // Transfer files to external storage.
      // Set up flysystem.
      $container = $this->getContainer();
      $flysystem = $container->get('oneup_flysystem.assets_filesystem');
      $conn = $container->get('doctrine.dbal.default_connection');
      // Transfer files.
      $result = $this->fileTransfer->transferFiles($input->getArgument('job_id'), $flysystem, $conn);

      // Output validation results.
      if (!empty($result)) {
        $output->writeln('<comment>Transferred Files:</comment>' . "\n");
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
  }
}