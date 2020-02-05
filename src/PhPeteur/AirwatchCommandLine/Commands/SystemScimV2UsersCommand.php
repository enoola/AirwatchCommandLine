<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 03/02/2020
 * Time: 10:09
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchSystemScimV2Users;


class SystemScimV2UsersCommand extends AirwatchCmd
{

    protected function configure()
    {
        $this->_oAW = new AirwatchSystemScimV2Users( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('system-scimv2-users');
        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchSystemScimV2Users::CLASS_SENTENCE_AIM);

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

        $resquery = $this->run_search($arInterestingParams, $input);

        if (parent::isOptionRenderVerticalOn($input)) {
            $this->displayVerticalSearchResults($resquery, $output);
        } else {
            $this->displayHorizontalSearchResults($resquery, $input, $output);
        }


        $bWeHaveResults = !is_null($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] );
        $nb_entry_showed = (!$bWeHaveResults ) ? '0' : count($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ]);
        $output->writeln('I displayed : '.$nb_entry_showed.' result(s).');
        if ( $bWeHaveResults ) {
            $output->writeln('Current page : ' . $resquery['data']['Page'] . '.');
            $output->writeln('Current page size : ' . $resquery['data']['PageSize'] . '.');
            $output->writeln('Total number of entries available : ' . $resquery['data']['Total'] . '.');
        }

    }
/*
    protected function run_search($arSearchParams, InputInterface $input) : array
    {
        echo '==>';
        $resquery = $this->_oAW->Search($arSearchParams);

        //var_dump($resquery);exit;
        if ( is_null($resquery['data']) )
        {
            echo "fuck off ?";
            $arAllAppsWithInterestingFields=['data'];
            $arAllAppsWithInterestingFields['data'] = [$this->_oAW->getFieldnameToPickInDataResultResponse()=>null];
            return ($arAllAppsWithInterestingFields);
        }


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
                else if (array_key_exists('ID', $arOneApp) && is_array($arOneApp['ID']))
                    $arOneAppWithInterestingFields['ID'] = $arOneApp['ID']['Value'];
                $arAllAppsWithInterestingFields['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ][] = $arOneAppWithInterestingFields;
            }


            $arAllAppsWithInterestingFields['data']['Page'] = null;
            $arAllAppsWithInterestingFields['data']['PageSize'] = null;
            $arAllAppsWithInterestingFields['data']['Total'] = null;
            if (array_key_exists('Page', $resquery['data']))
                $arAllAppsWithInterestingFields['data']['Page'] = $resquery['data']['Page'];
            if (array_key_exists('PageSize', $resquery['data']))
                $arAllAppsWithInterestingFields['data']['PageSize'] = $resquery['data']['PageSize'];
            if (array_key_exists('PageSize', $resquery['data']))
                $arAllAppsWithInterestingFields['data']['Total'] = $resquery['data']['Total'];


        }
        return ( $arAllAppsWithInterestingFields );
    }
*/

}