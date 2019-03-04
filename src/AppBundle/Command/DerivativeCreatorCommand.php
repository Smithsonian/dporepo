<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use AppBundle\Service\RepoDerivativeGenerate;

class DerivativeCreatorCommand extends ContainerAwareCommand
{
  protected $container;
  private $derivative_generator;

  public function __construct(RepoDerivativeGenerate $derivative_generator)
  {
    // Repo Derivative Generator service.
    $this->derivative_generator = $derivative_generator;
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:derivatives-generate')
      // The short description shown while running "php bin/console list".
      ->setDescription('Generate derivatives for capture dataset images.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command loops through all capture dataset images and generates thumbnails and mid-size images.')
      // Add arguments...
      ->addArgument('uuid', InputArgument::REQUIRED, 'Job UUID.');
  }

  /**
   * Example:
   * php bin/console app:derivatives-generate 3df_5b9fac451dea33.24849044
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $result = '';
    $errors = false;

    $job_description = 'Generating Derivative Images';

    // Outputs multiple lines to the console (adding "\n" at the end of each line).
    $output->writeln([
      '',
      '<bg=blue;options=bold> ' . $job_description . ' </>',
      '',
      'Command: ' . 'php bin/console app:derivatives-generate ' . $input->getArgument('uuid') . "\n",
    ]);

    // Generate derivatives.
    if (!empty($input->getArgument('uuid'))) {
      // Right now we only generate derivative images for capture dataset images.
      $result = $this->derivative_generator->generateCaptureDatasetDerivatives(trim($input->getArgument('uuid')));

      // Later we might generate additional derivatives.
    }

    // Output validation results.
    if (!empty($result)) {
      $output->writeln('<comment>Generated derivatives:</comment>' . "\n");
      foreach ($result as $key => $value) {
        $output->writeln('---------------------------' . "\n");
        foreach ($value as $k => $v) {
          if ($k !== 'errors') {
            $output->writeln($k . ': ' . $v . "\n");
          }
          else {
            if (!empty($v)) {
              $errors = TRUE;
              foreach ($v as $ek => $ev) {
                $output->writeln('<error>' . $ev . '</error>' . "\n");
              }
            }
          }
        }
      }
      if (!$errors) $output->writeln('<comment>Derivative generation complete.</comment>');
    }
    else {
      $output->writeln('<comment>No result returned from derivative generator.</comment>');
    }

    return $result;
  }
}