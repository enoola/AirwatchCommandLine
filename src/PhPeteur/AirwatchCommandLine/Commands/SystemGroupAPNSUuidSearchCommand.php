<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 28/04/2019
 * Time: 07:49
 */


namespace PhPeteur\AirwatchCommandLine\Commands;


use PhPeteur\AirwatchWebservices\Services\AirwatchSystemGroupAPNSUUIdSearch;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

/*
 * This endpoint is to get the details of APNs certificate Blob(.pem) uploaded to the AirWatch server.
 */
class SystemGroupAPNSUuidSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchSystemGroupAPNSUUIdSearch($this->_config);
        if (is_null($this->_oAW))
            throw new AirwatchCmdException('Unable to create AirwatchSystemGroupAPNSUuidSearch object within' . __CLASS__, 42);

        $this->setName('system-groupapnsuuid-search');
        if (!is_null($this->_oAW->getPossibleSearchParams())) {
            foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
                $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);

            }
        }
        $this->setDescription(AirwatchSystemGroupAPNSUUIdSearch::CLASS_SENTENCE_AIM);

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        if (is_null ( $input->getOption('uuid')) )
            throw new \Exception("I need an uuid at least");

        foreach ($input->getOptions() as $optName => $optValue) {
            if (array_key_exists($optName, $clPossileParam ) ) {
                if (!is_null( $optValue ) ) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }
        $arInterestingParams = ( count($arInterestingParams) > 0 ) ? $arInterestingParams : null;


        $resquery = $this->run_search($arInterestingParams, $input);
        //var_dump($resquery);


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

    protected function run_search($arSearchParams, InputInterface $input, $szContentType = 'application/json;version=2') : array
    {
        $resquery = $this->_oAW->SearchV2($arSearchParams, $szContentType);


        // so no getFieldnameToPickInDataResultResponse so far !
        $this->_oAW->setFieldnameToPickInDataResultResponse('default_systemapns_fields_to_show');

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

        $arAPNSWithInterestingFields = [];

        if (!is_null($resquery['data'])) {


            $arAPNSWithInterestingFields['data'] = [$this->_oAW->getFieldnameToPickInDataResultResponse() => []];
            $arTheAPNSWithInterestingFields = [];
            // /!\ /!\/!\/!\/!\/!\/!\/!\ HERE WE ARE MESSING A BIT !
            if (count($resquery['data']) > 0) {
                //foreach ()
                foreach ($resquery['data'] as $fieldName => $OneInfo) {

                    if (in_array($fieldName, $arFieldsToDisplay)) {

                        if (is_array($OneInfo)) {
                            $arTheAPNSWithInterestingFields[$fieldName] = $this->quicklyConvertArrayToString($OneInfo);
                        } else {
                            $arTheAPNSWithInterestingFields[$fieldName] = $OneInfo;
                        }
                    }
                }
            }
            $arAPNSWithInterestingFields['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][] = $arTheAPNSWithInterestingFields;
        }

        $arAPNSWithInterestingFields['data']['Page'] = null;
        $arAPNSWithInterestingFields['data']['PageSize'] = null;
        $arAPNSWithInterestingFields['data']['Total'] = null;

        return ( $arAPNSWithInterestingFields );
    }
}