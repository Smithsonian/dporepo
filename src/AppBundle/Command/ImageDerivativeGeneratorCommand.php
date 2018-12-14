<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

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
      ->addArgument('height', InputArgument::REQUIRED, 'Height in px.');
  }

  /**
   * Example:
   * bin/console app:get-processing-assets
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $path = $input->getArgument('path');
    $width = $input->getArgument('width');
    $height = $input->getArgument('height');
    $res = $this->resizeImage($path,$width,$height);
    $output->writeln([
      '',
      '<bg=cyan;options=bold> ' . $res . ' </>',
      '',
    ]);
  }

  function resizeImage($path,$width,$height){
    // validate if file exist
    if (!file_exists($path)) return "Image was not found";
    // get image size 
    if(!list($widthOriginal, $heightOriginal) = getimagesize($path)) return "Unsupported image type";
    // get file name
    $filename = basename($path);
    // get file path
    $filepath = str_replace($filename, "", $path);
    // create new file name with size
    $newfilename = str_replace(".", "_".$width."px_".$height."px.", $filename);
    // create new file path
    $newPath = $filepath."$newfilename";
    // get extension
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $ext = ($ext == 'jpeg') ? 'jpg' : $ext;
    switch($ext){
      case 'jpg': 
        $img = imagecreatefromjpeg($path); 
        break;
      case 'png': 
        $img = imagecreatefrompng($path); 
        break;
      default : 
        return "Unsupported image type";
    }

    $newImg = imagecreatetruecolor($width, $height);

    // if type is png these set of properties will preserve transparency
    if($ext == "png"){
      imagecolortransparent($newImg, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
      imagealphablending($newImg, false);
      imagesavealpha($newImg, true);
    }
    // re-sampling image 
    imagecopyresampled($newImg, $img, 0, 0, 0, 0, $width, $height, $widthOriginal, $heightOriginal);
    // if file exist file will be deleted before recreate it
    if (file_exists($newPath)) {
      unlink($newPath);
    }
    // if ext exist
    switch($ext){
      case 'jpg': 
        // generate jpg image
        imagejpeg($newImg, $newPath); 
        break;
      case 'png': 
        // generate png image
        imagepng($newImg, $newPath); 
        break;
    }
    // validate if file exist
    if (file_exists($newPath)) {
      return $newPath." was created successfully";
    }else{
      return $newPath." was not created";
    }
    
  }
}