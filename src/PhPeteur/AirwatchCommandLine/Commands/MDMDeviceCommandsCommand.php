<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 08/08/2018
 * Time: 11:53
 */

namespace PhPeteur\AirwatchCommandLine\Commands;


use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDeviceCommands;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

/*
 * Delete device defined by id (id,macaddr,serialnum,udid)
 * Functionality – Deletes the device information from the AirWatch Console and un-enrolls the device.
 */
class MDMDeviceCommandsCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDeviceCommands( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDeviceCommands object :/");

        $this->setName('mdm-device-commands');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchMDMDeviceCommands::CLASS_SENTENCE_AIM);
        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output){

        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        foreach ($input->getOptions() as $optName => $optValue) {
            if (array_key_exists($optName, $clPossileParam ) ) {
                if (!is_null( $optValue ) ) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }

        $arInterestingParams = count($arInterestingParams) >0 ? $arInterestingParams : null;

        $resquery = parent::run_post($arInterestingParams, $input );

        if (array_key_exists('status', $resquery) && ( strcmp( $resquery['status'], '202 Accepted') == 0) ) {
                parent::myoutput($output,parent::CMD_STATUS_OK, 'Command ' .$arInterestingParams['command'] . 'issued to device with id : '. $arInterestingParams['id'] . ' Successfuly.');
            }
            else {
                parent::myoutput($output,parent::CMD_STATUS_OK, 'Failed : '. $resquery['status']);
            }
            
        return ($resquery);
    }

}

?>