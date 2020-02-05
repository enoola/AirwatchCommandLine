<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 31/01/2018
 * Time: 11:42
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDevicesAppStatusSearch;

/*
 * Retrieves the application status for a combination of input elements.
 * NOT SURE HOW TO CALL PROPERLY YET
 */
class MDMDevicesAppStatusSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDevicesAppStatusSearch( $this->_config );
        if (is_null( $this->_oAW))
            die (">>unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('mdm-devices-appstatus-search');
        $arOptions = $this->_oAW->getPossibleSearchParams();
        $arOptions['versionapp'] = $arOptions['version'];
        unset($arOptions['version']);

        foreach ($arOptions as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }

        //We might want some specific argument handling

        $this->setDescription(AirwatchMDMDevicesAppStatusSearch::CLASS_SENTENCE_AIM);

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        if (is_null ( $input->getOption('id')) )
            throw new \Exception("I need an id at least");
        if (is_null ( $input->getOption('groupid')) )
            throw new \Exception("I need an groupid at least");
        if (is_null ( $input->getOption('versionapp')) )
            throw new \Exception("I need an version at least");

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
        $arInterestingParams['version'] = $input->getOptions()['versionapp'];
        unset($arInterestingParams['versionapp']);

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

    /*
       * we do not have a key below ['data'], so we cannot use the generic method,
       * I decided to rewrite the response, and added field called 'custo_DeviceInfos'
       * this way rendervertical and renderhorizontal will be able to display without an overload :)
   */
    protected function run_search($arSearchParams, InputInterface $input, $szContentType = AirwatchCmd::HTTP_DEFAULT_CONTENT_TYPE) : array
    {
        $resquery = $this->_oAW->Search($arSearchParams, $szContentType);

        // so no getFieldnameToPickInDataResultResponse so far !
        $this->_oAW->setFieldnameToPickInDataResultResponse('custo_AppStatus');


        if (is_null($resquery['data'])) {
            $arAllAppsWithInterestingFields = ['data'];
            $arAllAppsWithInterestingFields['data'] = [$this->_oAW->getFieldnameToPickInDataResultResponse() => null];
        }


        $arFieldsToDisplay = $this->_oAW->getDefaultFieldsToShow();
        if ($this->isOptionShowAllFieldsOn($input)) {
            $arFieldsToDisplay = $this->_oAW->getAllFieldsToShow();
        }

        $arAllAppsWithInterestingFields = [];

        if (!is_null($resquery['data'])) {


            $arAllAppsWithInterestingFields['data'] = [$this->_oAW->getFieldnameToPickInDataResultResponse() => []];
            $arOneAppWithInterestingFields = [];
            foreach ($resquery['data'] as $fieldName => $OneInfo) {
                if (in_array($fieldName, $arFieldsToDisplay)) {

                    if (is_array($OneInfo)) {
                        $arOneAppWithInterestingFields[$fieldName] = $this->quicklyConvertArrayToString($OneInfo);
                    } else {
                        $arOneAppWithInterestingFields[$fieldName] = $OneInfo;
                    }
                }
            }

            $arAllAppsWithInterestingFields['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][] = $arOneAppWithInterestingFields;
        }

        $arAllAppsWithInterestingFields['data']['Page'] = null;
        $arAllAppsWithInterestingFields['data']['PageSize'] = null;
        $arAllAppsWithInterestingFields['data']['Total'] = null;

        return ( $arAllAppsWithInterestingFields );
    }
}