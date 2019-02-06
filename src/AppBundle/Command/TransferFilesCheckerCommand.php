<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\HttpKernel\KernelInterface;

use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Service\RepoFileTransfer;
use AppBundle\Utils\AppUtilities;

class TransferFilesCheckerCommand extends ContainerAwareCommand
{
  protected $container;
  private $repo_storage_controller;
  private $fileTransfer;
  private $project_directory;
  private $u;

  public function __construct(KernelInterface $kernel, RepoFileTransfer $fileTransfer, string $uploads_directory, bool $external_file_storage_on, object $conn)
  {
    // Storage controller
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    // Repo File Transfer service.
    $this->fileTransfer = $fileTransfer;
    // Uploads directory
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->uploads_directory = $this->project_directory . $uploads_directory;
    // App Utilities
    $this->u = new AppUtilities();
    // External file storage status.
    $this->external_file_storage_on = $external_file_storage_on;
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:transfer-files-checker')
      // The short description shown while running "php bin/console list".
      ->setDescription('Checks to see if there are completed workflows and transfers files to external storage (if they haven\'t already been transferred.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command checks to see if there are completed workflows and transfers files to external storage.');
  }

  /**
   * Example:
   * php bin/console app:files-transfer-files-checker
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Only execute if the external file storage is set to '1' (true) in parameters.yml.
    if ($this->external_file_storage_on) {

      $result = '';
      $errors = false;

      // Outputs multiple lines to the console (adding "\n" at the end of each line).
      $output->writeln([
        '',
        '<bg=blue;options=bold> Checking Workflows / Transferring Files </>',
        '',
        'Command: ' . 'php bin/console app:transfer-files-checker' . "\n",
      ]);

      // Check for a workflow with the 'step_state' set to 'done'.
      $data = $this->repo_storage_controller->execute('getWorkflows', array());

      // $this->u->dumper($data);

      if (!empty($data)) {

        foreach ($data as $key => $value) {
          if (($value['step_state'] === 'done') && is_dir($this->uploads_directory . $value['ingest_job_uuid'])) {
            // Make sure the directory contains files.
            $files = glob($this->uploads_directory . $value['ingest_job_uuid'] . DIRECTORY_SEPARATOR . '*');

            // If the directory isn't empty, transfer files to the external file storage.
            if (!empty($files)) {

              // Set up flysystem.
              $container = $this->getContainer();
              $flysystem = $container->get('oneup_flysystem.assets_filesystem');
              $conn = $container->get('doctrine.dbal.default_connection');
              // Transfer files.
              $result = $this->fileTransfer->transferFiles($value['ingest_job_uuid'], $flysystem, $conn);

              // After files are transferred, rename the directory by prepending 'transferred_' to the directory name.
              // Another cron job can do the actual purge, later on- perhaps overnight during off-hours.
              rename($this->uploads_directory . $value['ingest_job_uuid'], $this->uploads_directory . 'transferred_' .  $value['ingest_job_uuid']);

            }
          }
        }

      }

    }

  }

}