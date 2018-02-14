<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 15/01/2018
 * Time: 22:00
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDevicesSearch;

class MDMDevicesSearchCommand extends AirwatchCmd
{

    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDevicesSearch( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('mdm-devices-search');
        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }

        $this->setDescription('Searches for devices using the query information provided.');

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        foreach ($input->getOptions() as $optName => $optValue) {
            if (array_key_exists($optName, $clPossileParam ) ) {
                if (!is_null( $optValue ) ) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }
        $arInterestingParams = ( count($arInterestingParams) > 0 ) ? $arInterestingParams : null;

        /*
         * those specific options are not cumulative...
         */
        $specialSearchField = null;
        if (!is_null($input->getOption('serialnumber'))){
            $specialSearchField = 'serialnumber';
        } elseif (!is_null($input->getOption('easid'))){
            $specialSearchField = 'easid';
        } elseif (!is_null($input->getOption('imei'))) {
            $specialSearchField = 'imei';
        }

        $specialSearchValue = (!array_key_exists($specialSearchField,$input->getOptions()) ) ? null : $input->getOption($specialSearchField) ;
        $resquery = $this->run_search_special($arInterestingParams, $input,$specialSearchField, $specialSearchValue);


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

    private function run_search_special($arSearchParams, $input, $specialSearchField = null, $specialSearchValue = null) : array
    {
        $resquery = null;

        if (!is_null($specialSearchField))
        {
            $resquery = $this->_oAW->SearchForSpecifics($specialSearchField, $specialSearchValue);

            $arFieldsToDisplay = $this->_oAW->getDefaultFieldsToShow();
            if ($this->isOptionShowAllFieldsOn($input)) {
                $arFieldsToDisplay = $this->_oAW->getAllFieldsToShow();
            }

            $arAllAppsWithInterestingFields = [];

            if (!is_null($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ])) {


                $arAllAppsWithInterestingFields['data'] = [ $this->_oAW->getFieldnameToPickInDataResultResponse() =>[]];

                foreach ($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] as $arOneApp) {
                    $arOneAppWithInterestingFields = [];

                    foreach ($arFieldsToDisplay as $fieldName) {
                        if (array_key_exists($fieldName, $arOneApp)) {
                            if (is_array($arOneApp[$fieldName])) {
                                $arOneAppWithInterestingFields[$fieldName] = $this->quicklyConvertArrayToString($arOneApp[$fieldName]);
                            } else {
                                $arOneAppWithInterestingFields[$fieldName] = $arOneApp[$fieldName];
                            }
                        } else {
                            $arOneAppWithInterestingFields[$fieldName] = "N/A";
                        }
                    }

                    if (array_key_exists('Id', $arOneApp) && is_array($arOneApp['Id']))
                        $arOneAppWithInterestingFields['Id'] = $arOneApp['Id']['Value'];

                    $arAllAppsWithInterestingFields['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ][] = $arOneAppWithInterestingFields;
                }

                $arAllAppsWithInterestingFields['data']['Page'] = $resquery['data']['Page'];
                $arAllAppsWithInterestingFields['data']['PageSize'] = $resquery['data']['PageSize'];
                $arAllAppsWithInterestingFields['data']['Total'] = $resquery['data']['Total'];

                return ( $arAllAppsWithInterestingFields );
            }
        }
            return ( parent::run_search($arSearchParams, $input) );
    }

}