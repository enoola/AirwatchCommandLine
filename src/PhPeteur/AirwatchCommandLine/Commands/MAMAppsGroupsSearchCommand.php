<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 22/01/2018
 * Time: 13:58
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMAMAppsGroupsSearch;

class MAMAppsGroupsSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMAMAppsGroupsSearch($this->_config);
        if (is_null($this->_oAW))
            throw new AirwatchCmdException('unable to create AirwatchMAMAppsGroupsSearch object within' . __CLASS__, 42);

        $this->setName('mam-appsgroups-search');
        if (!is_null($this->_oAW->getPossibleSearchParams())) {
            foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
                $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);
            }
        }
        $this->setDescription(AirwatchMAMAppsGroupsSearch::CLASS_SENTENCE_AIM);

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output)
    {

        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        foreach ($input->getOptions() as $optName => $optValue) {
            if (array_key_exists($optName, $clPossileParam)) {
                if (!is_null($optValue)) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }
        $arInterestingParams = count($arInterestingParams) > 0 ? $arInterestingParams : null;

        $resquery = parent::run_search($arInterestingParams, $input );

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

        return ($resquery);
    }

    /*
    protected function run_searcho($arSearchParams, InputInterface $input): array
    {
        $resquery = $this->_oAW->Search($arSearchParams);

        $arFieldsToDisplay = $this->_oAW->getDefaultFieldsToShow();

        if (!is_null($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ])) {
            $table = new Table($output);

            $table->setHeaders( $this->shorten_HeadersName($arFieldsToDisplay) );
            foreach ($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] as $arOneAdmin) {
                $arOneRowWithInterestingFields = [];
                foreach ($arFieldsToDisplay as $fieldName) {
                    $arOneRowWithInterestingFields[$fieldName] = ( array_key_exists($fieldName, $arOneAdmin) ? $arOneAdmin[$fieldName] : "N/A" );
                }

                $arOneRowWithInterestingFields = $this->handleSpecifcs_inRowWithInterestingFields($arOneRowWithInterestingFields);

                // IsActiveDirectoryUser: we do not have false but an empty value if not part of
                //var_dump($arOneRowWithInterestingFields);
                //exit;
                $table->addRow($arOneRowWithInterestingFields);
            }
            $table->render();
        } else {
            $output->writeln('<red>Nothing to display</red>');
        }

        return ($resquery);
    }



    /*
     * the KISS function (Keep It Simple & Stupid
     * Basically it will handle specific fields (such as the one supposed to be numeric and are array,
     * or shall be boolean but blank when false...
     *
    private function handleSpecifcs_inRowWithInterestingFields ($arOneRowWithInterestingFields) :array
    {
        // shall be numeric instead we do have an array with value containing the Id... so specific but KISS
        if (array_key_exists('OrganizationGroups', $arOneRowWithInterestingFields) ){ //&& (count($arOneRowWithInterestingFields['OrganizationGroups']) > 0)
            $flatEntry = '';
            foreach ($arOneRowWithInterestingFields['OrganizationGroups'] as $arValues) {
                $flatEntry .= $arValues['Name'] . '('.$arValues['Id'].')';
            }
            $arOneRowWithInterestingFields['OrganizationGroups'] = $flatEntry;
        }
        if (array_key_exists('UserGroups', $arOneRowWithInterestingFields) ) { //&& (count($arOneRowWithInterestingFields['UserGroups']) > 0)
            $flatEntry = '';
            foreach ($arOneRowWithInterestingFields['UserGroups'] as $arValues) {
                $flatEntry .= $arValues['Name'] . '(Id)'.PHP_EOL;
            }
            $arOneRowWithInterestingFields['UserGroups'] = $flatEntry;
        }

        return ($arOneRowWithInterestingFields);
    }*/

    /*
     * our header are quite long so this function intend to shorten them
     */
    private function shorten_HeadersName($arHeaders) : array
    {
        $arShortenNames = [ 'ManagedByOrganizationGroupID' => 'OGID',
                            'IsActive' => 'IsOn',
                            'ApplicationGroupID'=>'AppGID',];

        foreach ($arShortenNames as $curName => $newName) {
            foreach ($arHeaders as $k => $nametbr) {
                if (strcasecmp($nametbr, $curName)==0) {
                    $arHeaders[$k] = $newName;
                    continue;
                }
            }
        }

        return ($arHeaders);
    }



    protected function displayHorizontalSearchResults($resSearch, InputInterface $input, OutputInterface $output )
    {

        $arFieldsToDisplay = $this->_oAW->getDefaultFieldsToShow();
        if (self::isOptionShowAllFieldsOn($input)) {
            $arFieldsToDisplay = $this->_oAW->getAllFieldsToShow();
        }
        $table = new Table($output);
        $table->setHeaders($this->shorten_HeadersName($arFieldsToDisplay));
        if (!is_null($resSearch['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ])) {
            //foreach ($resSearch['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] as $oneApp) {
            foreach ($resSearch['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] as $oneApp) {
                $table->addRow($oneApp);
            }
        } else {
            $output->writeln('<red>Nothing to display</red>');
        }
        $table->render();
    }

}