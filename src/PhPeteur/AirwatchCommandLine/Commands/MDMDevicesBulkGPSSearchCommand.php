<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 31/01/2018
 * Time: 15:01
 */

namespace PhPeteur\AirwatchCommandLine\Commands;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDevicesBulkGPSSearch;

class MDMDevicesBulkGPSSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDevicesBulkGPSSearch( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDeviceInformationsSearch object :/");

        $this->setName('mdm-devices-gps-search');

        $this->addOption('searchby', null, InputOption::VALUE_REQUIRED, $this->_oAW->getPossibleSearchParams()['searchby'] );
        $this->addArgument('ids', InputArgument::REQUIRED, $this->_oAW->getPossibleSearchParams()['ids'] );

        $this->setDescription(AirwatchMDMDevicesBulkGPSSearch::CLASS_SENTENCE_AIM);

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

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
        //var_dump($input->getArguments('ids'));
        $arInterestingParams['ids'] = explode(',',$input->getArgument('ids'));
        //$arInterestingParams['ids'][0] = (int)$arInterestingParams['ids'][0] + 0;
        //$arInterestingParams['ids'][1] = (int)$arInterestingParams['ids'][1] + 0;
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
        if ( $bWeHaveResults && !is_null($resquery['data']['Total']) ) {
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
        $this->_oAW->setFieldnameToPickInDataResultResponse('custo_DeviceGPSInfos');

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
}
