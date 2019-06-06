<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Utils\AppUtilities;

use AppBundle\Service\RepoGenerateModel;

class ModelsGenerateCommand extends ContainerAwareCommand
{
  protected $container;
  private $model_generate;
  public $u;

  public function __construct(RepoGenerateModel $model_generate)
  {
    // Model generate service.
    $this->model_generate = $model_generate;
    $this->u = new AppUtilities();
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:model-generate')
      // The short description shown while running "php bin/console list".
      ->setDescription('Generate models and assets.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command genearates models and related assets.')
      // Add arguments...
      ->addArgument('uuid', InputArgument::OPTIONAL, 'Job UUID.')
      ->addArgument('recipe_name', InputArgument::OPTIONAL, 'Processing recipe name.');
  }

  /**
   * Example:
   * php bin/console app:model-generate 3df_5b9fac451dea33.24849044
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $result = '';
    $errors = false;

    $job_description = 'Generating Models';

    // Outputs multiple lines to the console (adding "\n" at the end of each line).
    $output->writeln([
      '',
      '<bg=blue;options=bold> ' . $job_description . ' </>',
      '',
      'Command: ' . 'php bin/console app:model-generate ' . $input->getArgument('uuid') . "\n",
    ]);

    // Generate model/assets and transfer model metadata to metadata storage.
    // Processing recipe name. Default: web-hd.
    $recipe_name = !empty($input->getArgument('recipe_name')) ? $input->getArgument('recipe_name') : '';
    // Set up flysystem.
    $container = $this->getContainer();
    $flysystem = $container->get('oneup_flysystem.processing_filesystem');
    // Generate model/assets.
    $result = $this->model_generate->generateModelAssets($input->getArgument('uuid'), $recipe_name, $flysystem);

    // $this->u->dumper($result);

    // Output results.
    if (!empty($result) && isset($result['state']) && ($result['state'] === 'done')) {
      $output->writeln('<comment>Model(s) Generated</comment>' . "\n");
    }

    // Output messages.
    if (!empty($result) && array_key_exists('messages', $result)) {
      $output->writeln('<comment>Result</comment>' . "\n");
      foreach ($result['messages'] as $key => $value) {
        $output->writeln('---------------------------' . "\n");
        $output->writeln('<comment>' . $value . '</comment>' . "\n");
      }
    }

    // Output errors.
    if (!empty($result) && array_key_exists('errors', $result)) {
      $output->writeln('<comment>Errors</comment>' . "\n");
      foreach ($result['errors'] as $key => $value) {
        $output->writeln('---------------------------' . "\n");
        $output->writeln('<error>' . $value . '</error>' . "\n");
      }
    }

    return $result;
  }
}