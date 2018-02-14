<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 03/02/2018
 * Time: 09:52
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDeviceUserSearch;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

/*
 * Retrieve Enrollment User Details of the Device
 * Functionality – Retrieves the details of the enrollment user associated to the device.
 */
class MDMDeviceUserSearchCommand extends AirwatchCmd
{


    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDeviceUserSearch( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDeviceUserSearch object :/");

        $this->setName('mdm-device-user-search');
        $arOptions = $this->_oAW->getPossibleSearchParams();

        foreach ($arOptions as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }

        $this->setDescription('Retrieves the details of the enrollment user associated to the device.');

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        if (is_null ( $input->getOption('id')) )
            throw new \Exception("I need an id at least");

        $arPossibleSearchBy = ["Macaddress", "Udid", "Serialnumber","ImeiNumber" ];
        if (!is_null ($input->getOption('searchby'))){
            if (!in_array($input->getOption('searchby'),$arPossibleSearchBy))
                throw new \Exception('Possible value for searchby :'.implode(',',$arPossibleSearchBy).'.');
        }

        foreach ($input->getOptions() as $optName => $optValue) {
            if (array_key_exists($optName, $clPossileParam ) ) {
                if (!is_null( $optValue ) ) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }
        $arInterestingParams = ( count($arInterestingParams) > 0 ) ? $arInterestingParams : null;

        $resquery = $this->run_search($arInterestingParams, $input);


        if (parent::isOptionRenderVerticalOn($input)) {
            $this->displayVerticalSearchResults($resquery, $output);
        } else {
            $this->displayHorizontalSearchResults($resquery, $input, $output);
        }

        $bWeHaveResults = !is_null($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] );
        $nb_entry_showed = (!$bWeHaveResults ) ? '0' : count($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()]);
        $output->writeln('I displayed : '.$nb_entry_showed.' result(s).');
        if ( $bWeHaveResults ) {
            $output->writeln('Current page : ' . $resquery['data']['Page'] . '.');
            $output->writeln('Current page size : ' . $resquery['data']['PageSize'] . '.');
            $output->writeln('Total number of entries available : ' . $resquery['data']['Total'] . '.');
        }
        return ( $resquery );
    }


    protected function run_search($arSearchParams, InputInterface $input) : array
    {
        $resquery = $this->_oAW->Search($arSearchParams);


        // so no getFieldnameToPickInDataResultResponse so far !
        //$this->_oAW->setFieldnameToPickInDataResultResponse('custo_DeviceGPSInfos');

        $arAllAppsWithInterestingFields = ['data'];


        if (is_null($resquery['data']) || (is_array($resquery['data']) && (count($resquery['data']) == 0))) {

            $arAllAppsWithInterestingFields['data'] = [$this->_oAW->getFieldnameToPickInDataResultResponse() => null];
            $arAllAppsWithInterestingFields['data']['Page'] = null;
            $arAllAppsWithInterestingFields['data']['PageSize'] = null;
            $arAllAppsWithInterestingFields['data']['Total'] = null;
            return ($arAllAppsWithInterestingFields);
        }


        $arFieldsToDisplay = $this->_oAW->getDefaultFieldsToShow();
        if ($this->isOptionShowAllFieldsOn($input)) {
            $arFieldsToDisplay = $this->_oAW->getAllFieldsToShow();
        }

        $arAllAppsWithInterestingFields = [];

        if (!is_null($resquery['data'])) {


            $arAllAppsWithInterestingFields['data'] = [$this->_oAW->getFieldnameToPickInDataResultResponse() => []];
            $arOneAppWithInterestingFields = [];
            // /!\ /!\/!\/!\/!\/!\/!\/!\ HERE WE ARE MESSING A BIT !
            if (count($resquery['data']) > 0) {
                foreach ($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()] as $fieldName => $OneInfo) {

                    if (in_array($fieldName, $arFieldsToDisplay)) {

                        if (is_array($OneInfo)) {
                            $arOneAppWithInterestingFields[$fieldName] = $this->quicklyConvertArrayToString($OneInfo);
                        } else {
                            $arOneAppWithInterestingFields[$fieldName] = $OneInfo;
                        }
                    }
                }

                if (array_key_exists('Id', $arOneAppWithInterestingFields))
                    $arOneAppWithInterestingFields['Id'] = $resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()]['Id']['Value'];
                if (array_key_exists('DeviceId', $resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()]))
                    $arOneAppWithInterestingFields['DeviceId'] = $resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()]['DeviceId']['Value'];
            }
            $arAllAppsWithInterestingFields['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][] = $arOneAppWithInterestingFields;
        }

        $arAllAppsWithInterestingFields['data']['Page'] = null;
        $arAllAppsWithInterestingFields['data']['PageSize'] = null;
        $arAllAppsWithInterestingFields['data']['Total'] = null;

        return ( $arAllAppsWithInterestingFields );
    }
}