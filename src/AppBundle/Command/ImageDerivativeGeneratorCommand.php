<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Utils\AppUtilities;

class ImageDerivativeGeneratorCommand extends ContainerAwareCommand
{
  protected $container;

  public function __construct()
  {
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:derivative-image')
      // The short description shown while running "php bin/console list".
      ->setDescription('Resize image with width and height provided.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->setHelp('This command generate an image derivative.')
      ->addArgument('path', InputArgument::REQUIRED, 'Image path.')
      ->addArgument('width', InputArgument::REQUIRED, 'Width in px.')
      ->addArgument('height', InputArgument::OPTIONAL, 'Height in px.')
      ->addArgument('new_file_name', InputArgument::OPTIONAL, 'Name for new file');
  }

  /**
   * Example:
   * bin/console app:derivative-image
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $utils = new AppUtilities();

    $path = $input->getArgument('path');
    $width = $input->getArgument('width');
    $height = $input->getArgument('height');
    $new_file_name = $input->getArgument('new_file_name');
    $res = $utils->resizeImage($path, $width, $height, $new_file_name);
    /*$output->writeln([
      '',
      '<bg=cyan;options=bold> ' . $res . ' </>',
      '',
    ]);
    */
    $output->writeln($res);
  }

}