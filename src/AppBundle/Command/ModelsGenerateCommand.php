<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use AppBundle\Service\RepoGenerateModel;

class ModelsGenerateCommand extends ContainerAwareCommand
{
  protected $container;
  private $model_generate;

  public function __construct(RepoGenerateModel $model_generate)
  {
    // Model generate service.
    $this->model_generate = $model_generate;
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
      ->addArgument('uuid', InputArgument::REQUIRED, 'Job UUID.')
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
    if (!empty($input->getArgument('uuid'))) {
      // Processing recipe name. Default: web-hd.
      $recipe_name = !empty($input->getArgument('recipe_name')) ? $input->getArgument('recipe_name') : 'web-hd';
      // Set up flysystem.
      $container = $this->getContainer();
      $flysystem = $container->get('oneup_flysystem.processing_filesystem');
      // Generate model/assets.
      $result = $this->model_generate->generateModelAssets($input->getArgument('uuid'), $recipe_name, $flysystem);
    }

    // echo '<pre>';
    // var_dump($result);
    // echo '</pre>';
    // die();

    // Output results.
    if (!empty($result)) {
      $output->writeln('<comment>Generated Model(s):</comment>' . "\n");
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
      if (!$errors) $output->writeln('<comment>Model generation complete.</comment>');
    }

    return $result;
  }
}