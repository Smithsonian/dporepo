<?php

namespace AppBundle\Utils;

use Symfony\Component\Process\Process;

class GitLoader extends \Twig_Extension
{

  public function getFunctions()
  {
    return [
      new \Twig_SimpleFunction('get_git_data', array($this, 'getGitData'))
    ];
  }

  public function getGitData($get = 'version')
  {
    switch ($get) {
      case 'version':
        $command = 'git describe --tags';
        break;
      case 'current branch':
        $command = 'git describe --all';
        break;
      case 'latest commit':
        $command = 'git rev-parse --short HEAD';
        break;
      default:
        $command = 'git describe --tags';
    }

    $process = new Process($command);
    $process->run();

    if (!$process->isSuccessful()) {
      return $get . ': undefined (git command: ' . $command . ')';
    }

    return $process->getOutput();
  }

}