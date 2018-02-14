<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 12/01/2018
 * Time: 08:00
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

use PhPeteur\AirwatchWebservices\Services\AirwatchInfo;
use PhPeteur\AirwatchWebservices\Services\AirwatchSystemInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SystemInfoCommand extends AirwatchCmd
{
    //private $_oAW;

    protected function configure()
    {
       $this->setName("system-infos")
       ->setDescription("gives airwatch instance informations");

       $this->_oAW = new AirwatchSystemInfo( $this->_config );
    }

    protected function doRun(InputInterface $input, OutputInterface $output){

       $infos = $this->_oAW->getInfos() ;

        $output->writeln('Server uri : '. $infos['uri']);
        $output->writeln('Server response : '.$infos['status']);
        $output->writeln('Information gathered :');

        $output->writeln('Ressources : ');
        foreach ($infos['data']['Resources']['Workspaces'] as $one)
            $output->writeln("\t".$one['Name'].' : '. $one['Location'] );


    }


}