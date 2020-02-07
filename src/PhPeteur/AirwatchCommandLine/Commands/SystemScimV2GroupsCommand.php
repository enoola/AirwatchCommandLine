<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 03/02/2020
 * Time: 10:09
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use PhPeteur\AirwatchWebservices\Services\AirwatchSystemScimV2Groups;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

/*
 * get group list
 */

//awcmd system-scimv2-groups --filter 'displayName co "Admins"' --rendervertical

class SystemScimV2GroupsCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchSystemScimV2Groups( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('system-scimv2-groups');
        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchSystemScimV2Groups::CLASS_SENTENCE_AIM);

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

        $resquery = self::run_search_custo($arInterestingParams, $input,'application/scim+json;version=2');

        if (parent::isOptionRenderVerticalOn($input)) {
            $this->displayVerticalSearchResults($resquery, $output);
        } else {
            $this->displayHorizontalSearchResults($resquery, $input, $output);
        }


        $bWeHaveResults = !is_null($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] );
        $nb_entry_showed = (!$bWeHaveResults ) ? '0' : count($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ]);
        $output->writeln('I displayed : '.$nb_entry_showed.' result(s).');
        if ( $bWeHaveResults ) {
            $output->writeln('Total Results : ' . $resquery['totalResults'] . '.');
            $output->writeln('Start Index : ' . $resquery['startIndex'] . '.');
            $output->writeln('Items per page : ' . $resquery['itemsPerPage'] . '.');
        }

    }


    protected function run_search_custo($arSearchParams, InputInterface $input, $szContentType = 'application/scim+json;version=2') : array
    {
        $resquery = $this->_oAW->Search($arSearchParams, $szContentType);

        $arFieldsToDisplay = $this->_oAW->getDefaultFieldsToShow();
        if ($this->isOptionShowAllFieldsOn($input)) {
            $arFieldsToDisplay = $this->_oAW->getAllFieldsToShow();
        }

        $arAllAppsWithInterestingFields = [];

        if (!is_null($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()])) {

            $arAllAppsWithInterestingFields['data'] = [$this->_oAW->getFieldnameToPickInDataResultResponse() => []];

            foreach ($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()] as $arOneApp) {
                $arOneAppWithInterestingFields = [];

                foreach ($arFieldsToDisplay as $fieldName) {
                    if (array_key_exists($fieldName, $arOneApp)) {
                        if (strcmp($fieldName, 'schemas') == 0) {

                            $ncpt = count($arOneApp['schemas']);

                            $i = 1;
                            $szschemas = '';
                            foreach ($arOneApp[$fieldName] as $k => $entry) {
                                $szschemas .= $entry;
                                if ($i < $ncpt) {
                                    $i++;
                                    $szschemas .= ',' . PHP_EOL;
                                }
                            }
                            $arOneAppWithInterestingFields[$fieldName] = $szschemas;
                        } else if (strcmp($fieldName, 'meta') == 0) {
                            $szmeta = '';
                            $ncpt = count($arOneApp[$fieldName]);
                            $i = 1;
                            foreach ($arOneApp[$fieldName] as $k => $val) {
                                $szmeta .= $k . ' = ' . $val;
                                if ($i < $ncpt) {
                                    $i++;
                                    $szmeta .= ',' . PHP_EOL;
                                }
                            }
                            $arOneAppWithInterestingFields[$fieldName] = $szmeta;
                        } else if (strcmp($fieldName, 'members') == 0) {

                            $arOneAppWithInterestingFields[$fieldName] = '';
                            $szmembers = '';
                             foreach ($arOneApp[$fieldName] as $arOneMember) {
                                $szmembers.=$arOneMember['display'] .',' . $arOneMember['value'] . PHP_EOL;
                                }
                                $arOneAppWithInterestingFields[$fieldName] = $szmembers;
                            }
                         else if (is_array($arOneApp[$fieldName])) {
                            $arOneAppWithInterestingFields[$fieldName] = $this->quicklyConvertArrayToString($arOneApp[$fieldName]);
                            echo '...' . PHP_EOL;
                        } else {
                            $arOneAppWithInterestingFields[$fieldName] = $arOneApp[$fieldName];
                        }
                    } else {
                        $arOneAppWithInterestingFields[$fieldName] = "N/A";
                    }
                }
                $arAllAppsWithInterestingFields['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][] = $arOneAppWithInterestingFields;
            }

            $arAllAppsWithInterestingFields['totalResults'] = $resquery['data']['totalResults'];
            $arAllAppsWithInterestingFields['startIndex'] = $resquery['data']['startIndex'];
            $arAllAppsWithInterestingFields['itemsPerPage'] = $resquery['data']['itemsPerPage'];

        }
        return ($arAllAppsWithInterestingFields);
    }

}