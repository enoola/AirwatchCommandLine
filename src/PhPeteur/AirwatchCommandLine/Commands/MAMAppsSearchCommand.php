<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 15/01/2018
 * Time: 09:21
 */

namespace PhPeteur\AirwatchCommandLine\Commands;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMAMAppsSearch;

//extends MAMAppsCommand, we'll decide later on.
class MAMAppsSearchCommand extends AirwatchCmd
{

    protected function configure()
    {
        //$this->_fieldnameToShowInDataResult = "Application";
        $this->_oAW = new AirwatchMAMAppsSearch( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('mam-apps-search');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription("gives access to aw mam apps search");
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
     *search?type={type}&applicationtype={applicationtype}&applicationname=
{applicationname}&category={category}&locationgroupid={locationgroupid}&bundleid={bundleid}&platform=
{platform}&model={model}&status={status}&orderby={orderby}&page={page}&pagesize={pagesize}
     */


    /*
    public function run_search($arSearchParams, InputInterface $input)
    {
            $resquery = $this->_oAW->Search($arSearchParams);

            $arFieldsToDisplay = $this->_oAW->getDefaultFieldsToShow();
            if (parent::isOptionShowAllFieldsOn($input)) {
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
                $arOneAppWithInterestingFields['Id'] = $arOneApp['Id']['Value'];
                $arAllAppsWithInterestingFields['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ][] = $arOneAppWithInterestingFields;
            }

            $arAllAppsWithInterestingFields['data']['Page'] = $resquery['data']['Page'];
            $arAllAppsWithInterestingFields['data']['PageSize'] = $resquery['data']['PageSize'];
            $arAllAppsWithInterestingFields['data']['Total'] = $resquery['data']['Total'];

            return ( $arAllAppsWithInterestingFields );
        }
    }
    */

 /*   protected function displayHorizontalSearchResults($resSearch, InputInterface $input, OutputInterface $output) {

        $arFieldsToDisplay = $this->_oAW->getDefaultFieldsToShow();
        if (parent::isOptionShowAllFieldsOn($input)) {
            $arFieldsToDisplay = $this->_oAW->getAllFieldsToShow();
        }
        $table = new Table($output);
        $table->setHeaders($arFieldsToDisplay);
        if (!is_null($resSearch['data'][self::FIELDNAME_TO_SHOW_INDATARESULT])) {
            foreach ($resSearch['data'][self::FIELDNAME_TO_SHOW_INDATARESULT] as $oneApp) {
                   $table->addRow($oneApp);
            }
        } else {
            $output->writeln('<red>Nothing to display</red>');
        }
        $table->render();

    }*/

 /*
    protected function displayVerticalSearchResults($resSearch, OutputInterface $output)
    {
        $arHeadersName = ['FieldName', 'Value'];

        $table = new Table($output);
        $table->setHeaders($arHeadersName);
        if (!is_null($resSearch['data'][self::FIELDNAME_TO_SHOW_INDATARESULT])) {
            foreach ($resSearch['data'][self::FIELDNAME_TO_SHOW_INDATARESULT] as $oneApp) {
               // $table->addRow([$oneApp['ApplicationName'], '-----------']);
                foreach ($oneApp as $field => $value) {
                    $table->addRow([$field, $value]);
                }
                //would be nice to have a separation but don't know how yet
            }
        } else {
            $output->writeln('<red>Nothing to display</red>');
        }
        $table->render();
    }
*/



}