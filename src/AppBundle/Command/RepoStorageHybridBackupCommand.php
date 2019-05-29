<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;

use AppBundle\Controller\RepoStorageStructureHybridController;
use AppBundle\Controller\FilesystemHelperController;

class RepoStorageHybridBackupCommand extends Command
{
  private $repo_storage_controller;

  /**
   * @var string $uploads_directory
   */
  private $uploads_directory;

  /**
   * @var string $project_directory
   */
  private $project_directory;

  public function __construct(KernelInterface $kernel, $conn, string $uploads_directory, FilesystemHelperController $fs)
  {
    // Storage controller
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->uploads_directory = $this->project_directory . $uploads_directory;
    $this->repo_storage_controller = new RepoStorageStructureHybridController($kernel, $conn, $this->uploads_directory, $fs);

    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:mysql-backup')
      // The short description shown while running "php bin/console list".
      ->setDescription('Create a backup from MySQL and write the resulting file to Drastic.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command generates a full backup of the repository MySQL database, and writes the backup file to Drastic via WebDAV.')
      // Add arguments...
      ->addArgument('noschema', InputArgument::OPTIONAL, 'Set this parameter to omit the schema when backing up.')
      ->addArgument('nodata', InputArgument::OPTIONAL, 'Set this to omit the data when backing up.');
  }

  /**
   * Example:
   * php bin/console app:mysql-backup
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $result = '';

    // Outputs multiple lines to the console (adding "\n" at the end of each line).
    $output->writeln([
      '',
      '<bg=blue;options=bold> Generating backup file </>',
      '',
      'Command: ' . 'php bin/console app:mysql-backup ' . $input->getArgument('noschema') . ' ' . $input->getArgument('nodata') . "\n",
    ]);

    if ($input->getArgument('noschema') && $input->getArgument('nodata')) {
      $output->writeln('<warning>Both schema and data parameters were set to false. Backup not executed.</warning>');
    }
    else {
      // Parameters to pass to the backup method.
      $include_schema = $input->getArgument('noschema') ? false : true;
      $include_data = $input->getArgument('nodata') ? false : true;

      // Execute the backup.
      $result = $this->repo_storage_controller->backup($include_schema, $include_data);

      // Output results.
      if (isset($result['result']) && ($result['result'] === 'success')) {
        if(isset($result['tables']) && is_array($result['tables']) && count($result['tables']) > 0) {
          foreach($result['tables'] as $t) {
            $output->writeln('Table: ' . $t . "\n");
          }
        }
        $output->writeln('<comment>Backup complete.</comment>' . "\n");
      }

      // Output errors.
      if (isset($result['errors'])) {
        foreach ($result['errors'] as $key => $value) {
          $output->writeln('<error>' . $value . '</error>');
        }
      }
    }

  }


}