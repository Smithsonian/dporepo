<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use AppBundle\Service\RepoValidateAssets;
use AppBundle\Controller\ExtractImageMetadataController;

class FilesValidationCommand extends ContainerAwareCommand
{
  protected $container;
  private $validate_assets;
  private $extract_image_metadata;

  public function __construct(RepoValidateAssets $validate_assets, ExtractImageMetadataController $extract_image_metadata)
  {
    // Validate Assets Controller.
    $this->validate_assets = $validate_assets;
    // Image Metadata Extractor Controller.
    $this->extract_image_metadata = $extract_image_metadata;

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
      '<bg=blue;options=bold> Validating Files </>',
      '',
      'Command: ' . 'php bin/console app:files-validate ' . $input->getArgument('localpath') . "\n",
    ]);

    if (!empty($input->getArgument('localpath'))) {

      // Parameters to pass to the validate_assets method.
      $params = array(
        'localpath' => $input->getArgument('localpath'),
      );

      // Set up flysystem.
      $container = $this->getContainer();
      $filesystem = $container->get('oneup_flysystem.assets_filesystem');
      // Run the validation.
      $result = $this->validate_assets->validate_assets($params, $filesystem);

      // Output validation results.
      if (empty($result)) {
        $output->writeln('<comment>Files validation complete.</comment>');
      }

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

      // Extract the metadata.
      $result = $this->extract_image_metadata->extractMetadata($params);

      // Output metadata results.
      if (!empty($result)) {
        $output->writeln('<comment>Metadata from Files:</comment>' . "\n");
        foreach ($result as $key => $value) {
          $output->writeln('---------------------------' . "\n");
          foreach ($value as $k => $v) {
            if ($k == 'metadata') {
              foreach($v as $vk => $vv) {
                $output->writeln($vk . ': ' . $vv . "\n");
              }
            } else {
              if (!empty($v)) {
                if($k == 'errors' || $k == 'warnings') {
                  $html_tag = substr($k, 0, -1);
                  foreach ($v as $ek => $ev) {
                    $output->writeln('<' . $html_tag . '>' . $ev . '</' . $html_tag . '>' . "\n");
                  }
                }
              }
            }
          }
        }
      }

    }

  }
}