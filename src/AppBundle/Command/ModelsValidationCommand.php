<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use AppBundle\Service\RepoModelValidate;

class ModelsValidationCommand extends ContainerAwareCommand
{
  protected $container;
  private $model_validate;

  public function __construct(RepoModelValidate $model_validate)
  {
    // Repo File Transfer service.
    $this->model_validate = $model_validate;
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:model-validate')
      // The short description shown while running "php bin/console list".
      ->setDescription('Validate models and extract metadata.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command validates models and populates the metadata storage with extracted model metadata.')
      // Add arguments...
      ->addArgument('uuid', InputArgument::REQUIRED, 'Job UUID.');
  }

  /**
   * Example:
   * php bin/console app:model-validate 3df_5b9fac451dea33.24849044
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $result = '';
    $errors = false;

    $job_description = 'Validating Models';

    // Outputs multiple lines to the console (adding "\n" at the end of each line).
    $output->writeln([
      '',
      '<bg=blue;options=bold> ' . $job_description . ' </>',
      '',
      'Command: ' . 'php bin/console app:model-validate ' . $input->getArgument('uuid') . "\n",
    ]);

    // Validate model and transfer model metadata to metadata storage.
    if (!empty($input->getArgument('uuid'))) {
      // Set up flysystem.
      $container = $this->getContainer();
      $flysystem = $container->get('oneup_flysystem.processing_filesystem');
      // Validate models.
      $result = $this->model_validate->validateModels($input->getArgument('uuid'), $flysystem);
    }

    // Output validation results.
    if (!empty($result)) {
      $output->writeln('<comment>Validated Models:</comment>' . "\n");
      foreach ($result as $key => $value) {
        $output->writeln('---------------------------' . "\n");
        foreach ($value as $k => $v) {
          if ($k !== 'errors') {
            $output->writeln($k . ': ' . $v . "\n");
          } else {
            if (!empty($v)) {
              $errors = TRUE;
              foreach ($v as $ek => $ev) {
                $output->writeln('<error>' . $ev . '</error>' . "\n");
              }
            }
          }
        }
      }
      if (!$errors) $output->writeln('<comment>Model validation complete.</comment>');
    }

    return $result;
  }
}