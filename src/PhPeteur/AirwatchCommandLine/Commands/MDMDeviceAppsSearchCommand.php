<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 30/01/2018
 * Time: 09:47
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDeviceAppsSearch;

class MDMDeviceAppsSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDeviceAppsSearch( $this->_config );
        if (is_null( $this->_oAW))
            die (">>unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('mdm-device-apps-search');
        $arOptions = $this->_oAW->getPossibleSearchParams();

        foreach ($arOptions as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }

        //We might want some specific argument handling

        $this->setDescription('Retrieves the details of the applications that are present on the device.');

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
                foreach ($resquery['data'] as $arOneEntry) {
                    foreach ($arOneEntry as $fieldName => $OneInfo) {

                        if (in_array($fieldName, $arFieldsToDisplay)) {

                            if (is_array($OneInfo)) {
                                $arOneAppWithInterestingFields[$fieldName] = $this->quicklyConvertArrayToString($OneInfo);
                            } else {
                                $arOneAppWithInterestingFields[$fieldName] = $OneInfo;
                            }
                        }
                    }
                    if (array_key_exists('Id', $arOneAppWithInterestingFields) )
                        $arOneAppWithInterestingFields['Id'] = $arOneEntry['Id']['Value'];
                    if (array_key_exists('DeviceId', $arOneAppWithInterestingFields) )
                        $arOneAppWithInterestingFields['DeviceId'] = $arOneEntry['DeviceId']['Value'];

                    $arAllAppsWithInterestingFields['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][] = $arOneAppWithInterestingFields;
                }
            }

        }

        $arAllAppsWithInterestingFields['data']['Page'] = null;
        $arAllAppsWithInterestingFields['data']['PageSize'] = null;
        $arAllAppsWithInterestingFields['data']['Total'] = null;

        return ( $arAllAppsWithInterestingFields );
    }
//    private function run_search)
}