<?php

namespace AppBundle\Uploader;

use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;
// use ReflectionObject;

class FileNamer implements NamerInterface
{
  /**
   * @param object $file UploaderBundle's file object
   * 
   * See:
   * https://github.com/1up-lab/OneupUploaderBundle/blob/master/Resources/doc/custom_namer.md
   *
   * FileInterface provides:
   *
   * getSize()
   * getPathname()
   * getPath()
   * getMimeType()
   * getBasename()
   * getExtension()
   * getClientOriginalName()
   */
  public function name(FileInterface $file)
  {
    // Simply return the original file name with the unix timestamp prepended to it.
    // WARNING: Using the original name via getClientOriginalName() is not safe as it could have been manipulated by the end-user.
    // Moreover, it can contain characters that are not allowed in file names. You should sanitize the name before using it directly.
    // SEE: http://symfony.com/doc/3.4/reference/forms/types/file.html
    // TODO: Figure out an alternative method of keeping file names in tact.

    // echo '<pre>';
    // var_dump($file->getClientOriginalName());
    // echo '</pre>';
    // die();

    return sprintf('%s',
      $file->getClientOriginalName()
    );

    // return sprintf('%s/%s',
    //     // time(),
    //     uniqid(),
    //     $file->getClientOriginalName()
    // );
  }
}