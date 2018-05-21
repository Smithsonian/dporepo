<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use AppBundle\Controller\BagitController;

class BagitValidationCommand extends ContainerAwareCommand
{
    private $bagit;
    protected $container;

    public function __construct(BagitController $bagit)
    {
        // $this->container = $this->getContainer();
        $this->bagit = $bagit;

        // this is required due to parent constructor, which sets up name 
        parent::__construct();
    }

    protected function configure()
    {

        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:bagit-validate')
            // the short description shown while running "php bin/console list"
            ->setDescription('Validates a bag.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to validate a BagIt "bag", which consists of a "payload" (the arbitrary content) and "tags", which are metadata files intended to document the storage and transfer of the "bag".')
            // add arguments
            // ->addArgument('create_data_dir', InputArgument::REQUIRED, 'Create a data directory? Default: true')
            // ->addArgument('overwrite_manifest', InputArgument::OPTIONAL, 'Overwrite the manifest? Default: false')
            ->addArgument('flag_warnings_as_errors', InputArgument::OPTIONAL, 'Flag warnings as errors. Default: false');
    }

    /**
     * Example:
     * php bin/console app:bagit-validate /var/www/html/dporepo/web/uploads/repository/4 true false false
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = '';
        $container = $this->getContainer();

        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            '',
            '<bg=green;options=bold>  BagIt Validator  </>',
            '<bg=green;options=bold>  ===============  </>',
            '',
        ]);

        $directory_to_validate = $this->bagit->needs_validation_checker($container);

        if(!empty($directory_to_validate)) {

          $params = array(
            'localpath' => $directory_to_validate,
            // 'create_data_dir' => false,
            // 'overwrite_manifest' => false,
            'flag_warnings_as_errors' => false,
          );

          $result = $this->bagit->bagit_validate($params, $container);

          if(isset($result['result'])) {
            $output->writeln('<comment>Validation Result: ' . $result['result'] . "</comment>\n");
          }

          if(isset($result['errors'])) {
            foreach ($result['errors'] as $key => $value) {
              $output->writeln('<error>' . $value . '</error>');
            }
          }
          // var_dump($result); die();
        }

        // retrieve the argument value using getArgument()
        // $output->writeln('create_data_dir: ' . $input->getArgument('create_data_dir'));
        // $output->writeln('overwrite_manifest: ' . $input->getArgument('overwrite_manifest'));
        // $output->writeln('flag_warnings_as_errors: ' . $input->getArgument('flag_warnings_as_errors'));
        // $output->writeln('NEEDS TO BE VALIDATED: ' . $directory_to_validate);        
    }
}