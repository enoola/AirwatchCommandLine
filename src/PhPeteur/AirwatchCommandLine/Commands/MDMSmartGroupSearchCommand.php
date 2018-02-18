<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 02/02/2018
 * Time: 15:56
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use PhPeteur\AirwatchWebservices\Services\AirwatchMDMSmartGroupsSearch;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMDMSmartGroupSearch;
use PhPeteur\AirwatchWebservices\Exception\AirwatchCmdException;

/*
 * Retrieve Smart Group details
 * Functionality – Retrieves the smart group details created in an organization group.
 */

class MDMSmartGroupSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMSmartGroupSearch($this->_config);
        if (is_null($this->_oAW))
            throw new AirwatchCmdException('unable to create AirwatchMDMSmartGroupSearch object within' . __CLASS__, 42);

        $this->setName('mdm-smartgroup-search');
        if (!is_null($this->_oAW->getPossibleSearchParams())) {
            foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
                $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);

            }
        }
        $this->setDescription(AirwatchMDMSmartGroupSearch::CLASS_SENTENCE_AIM);

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        if (is_null ( $input->getOption('id')) )
            throw new \Exception("I need an id at least");

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
        $this->_oAW->setFieldnameToPickInDataResultResponse('custo_SMInfos');

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
                foreach ($resquery['data'] as $fieldName => $OneInfo) {

                    if (in_array($fieldName, $arFieldsToDisplay)) {

                        if (is_array($OneInfo)) {
                            $arOneAppWithInterestingFields[$fieldName] = $this->quicklyConvertArrayToString($OneInfo);
                        } else {
                            $arOneAppWithInterestingFields[$fieldName] = $OneInfo;
                        }
                    }
                }

                if (array_key_exists('Id', $resquery['data']))
                    $arOneAppWithInterestingFields['Id'] = $resquery['data']['Id']['Value'];
                if (array_key_exists('DeviceId', $resquery['data']))
                    $arOneAppWithInterestingFields['DeviceId'] = $resquery['data']['DeviceId']['Value'];
            }
            $arAllAppsWithInterestingFields['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][] = $arOneAppWithInterestingFields;
        }

        $arAllAppsWithInterestingFields['data']['Page'] = null;
        $arAllAppsWithInterestingFields['data']['PageSize'] = null;
        $arAllAppsWithInterestingFields['data']['Total'] = null;

        return ( $arAllAppsWithInterestingFields );
    }
}