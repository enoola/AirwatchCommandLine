<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 15/08/2018
 * Time: 11:29
 */


namespace PhPeteur\AirwatchCommandLine\Commands;


use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDevicesBulkDelete;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

/*
 * Delete devices in bulk
 * Functionality - Deletes multiple devices identified by device ID or alternate ID.
 */
class MDMDevicesBulkDeleteCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDevicesBulkDelete( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('mdm-device-delete');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchMDMDeviceDelete::CLASS_SENTENCE_AIM);
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

        //$resquery = null;
        $resquery = parent::run_delete($arInterestingParams, $input );

        var_dump($resquery);
        /*
        if (array_key_exists('status', $resquery)) {
            if (strncmp('200',$resquery['status'],3) == 0)
                $output->writeln('Device with id ' . $arInterestingParams['id'] . ' deleted.');
        }
        else */
        if (array_key_exists('statuscode', $resquery)) {
            if (strcmp($resquery['statuscode'], '204 No Content') == 0) {
                $output->writeln('Device with id ' . $arInterestingParams['id'] . ' deleted.');
            }
            else {
                $output->writeln('Device with id ' . $arInterestingParams['id'] . ' not deleted. error:'.$resquery['statuscode'] . ' reason : '.$resquery['message']);
            }
        }
        //var_dump($resquery);
        /*
        if (parent::isOptionRenderVerticalOn($input)) {
            $this->displayVerticalSearchResults($resquery, $output);
        } else {
            $this->displayHorizontalSearchResults($resquery, $input, $output);
        }
        $bWeHaveResults = !is_null($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] );
        $nb_entry_showed = (!$bWeHaveResults ) ? '0' : count($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse()  ]);
        $output->writeln('I displayed : '.$nb_entry_showed.' result(s).');
        if ( $bWeHaveResults ) {
            $output->writeln('Current page : ' . $resquery['data']['Page'] . '.');
            $output->writeln('Current page size : ' . $resquery['data']['PageSize'] . '.');
            $output->writeln('Total number of entries available : ' . $resquery['data']['Total'] . '.');
        }
        */
        return ($resquery);
    }

}

?>