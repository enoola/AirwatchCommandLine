<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 10/01/2018
 * Time: 22:27
 */

namespace PhPeteur\AirwatchCommandLine\AirwatchCmd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

abstract class AirwatchCmd extends Command
{
    const CMD_STATUS_OK = 1;
    const CMD_STATUS_KO = 0;
    const CMD_STATUS_IF = 2;
    protected $_config;
    protected $_oAW;


    public function __construct($iconfig)
    {
        $this->_config = $iconfig;
        parent::__construct();
    }

    protected function configure() {
    }

    protected function addGenericSearchOptions()
    {
        $this->addOption('showallfields',null,InputOption::VALUE_NONE,'show all the fields available');
        $this->addOption('showdefaultfields',null,InputOption::VALUE_NONE,'[default]show default fields configured in config file');
        $this->addOption('rendervertical',null,InputOption::VALUE_NONE,'display result(s) vertically');
        $this->addOption('renderhorizontal',null,InputOption::VALUE_NONE,'[default]display result(s) horizontally');
    }

    protected function isOptionShowAllFieldsOn(InputInterface $input) {
        return ( $input->getOption('showallfields') !== false );
    }

    protected function isOptionRenderVerticalOn(InputInterface $input) {
        return ( $input->getOption('rendervertical') !== false );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $colors = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white'];
        foreach ($colors as $color) {
            $style = new OutputFormatterStyle($color);
            $output->getFormatter()->setStyle($color, $style);
        }

        try {
            $ret = $this->doRun($input, $output);
            if ($output->isVerbose()) {
                $output->writeln("Verbose invoked...");
                var_dump($ret);
            }

        } catch (QueryException $e) {
            $output->write(json_encode($e->getResponse(), JSON_PRETTY_PRINT));
        }

    }

    /*
     * kind'a generic :)
     */
    protected function run_search($arSearchParams, InputInterface $input) : array
    {
        $resquery = $this->_oAW->Search($arSearchParams);

        //var_dump($resquery);
        if ( is_null($resquery['data']) )
        {
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

    protected function run_search_custo($arSearchParams, InputInterface $input) : array
    {
        $resquery = $this->_oAW->Search($arSearchParams);

        // so no getFieldnameToPickInDataResultResponse so far !
        $this->_oAW->setFieldnameToPickInDataResultResponse('custo_Fields');


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

    protected function run_search_custo_multientries($arSearchParams, InputInterface $input) : array
    {
        $resquery = $this->_oAW->Search($arSearchParams);


        // so no getFieldnameToPickInDataResultResponse so far !
        $this->_oAW->setFieldnameToPickInDataResultResponse('custo_SysOGUserssInfos');

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

            // /!\ /!\/!\/!\/!\/!\/!\/!\ HERE WE ARE MESSING A BIT !
            if (count($resquery['data']) > 0) {
                foreach ($resquery['data'] as $arOneEntry) {
                    $arOneAppWithInterestingFields = [];
                    foreach ($arOneEntry as $fieldName => $OneInfo) {

                        if (in_array($fieldName, $arFieldsToDisplay)) {

                            if (is_array($OneInfo)) {
                                $arOneAppWithInterestingFields[$fieldName] = $this->quicklyConvertArrayToString($OneInfo);
                            } else {
                                $arOneAppWithInterestingFields[$fieldName] = $OneInfo;
                            }
                        }
                    }

                    if (array_key_exists('Id', $arOneAppWithInterestingFields))
                        $arOneAppWithInterestingFields['Id'] = $arOneEntry['Id']['Value'];

                    $arAllAppsWithInterestingFields['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][] = $arOneAppWithInterestingFields;
                }
            }
        }

        $arAllAppsWithInterestingFields['data']['Page'] = null;
        $arAllAppsWithInterestingFields['data']['PageSize'] = null;
        $arAllAppsWithInterestingFields['data']['Total'] = null;

        return ( $arAllAppsWithInterestingFields );
    }


    protected function run_delete($arSearchParams, InputInterface $input) : array
    {
        $resquery = $this->_oAW->Delete($arSearchParams);
        //echo '---->';
        //var_dump($resquery);
        //echo '<-'.$this->_oAW->getFieldnameToPickInDataResultResponse().'---';
        if ( !array_key_exists('data',$resquery ) )
        {
            $arAllAppsWithInterestingFields=['data'];
            $arAllAppsWithInterestingFields['data'] = [$this->_oAW->getFieldnameToPickInDataResultResponse()=>null];
            $arAllAppsWithInterestingFields['statuscode'] = $resquery['statuscode'];
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
        $arAllAppsWithInterestingFields['statuscode'] = $resquery['statuscode'];
        return ( $arAllAppsWithInterestingFields );
    }



    protected function displayHorizontalSearchResults($resSearch, InputInterface $input, OutputInterface $output )
    {

        $arFieldsToDisplay = $this->_oAW->getDefaultFieldsToShow();
        if (self::isOptionShowAllFieldsOn($input)) {
            $arFieldsToDisplay = $this->_oAW->getAllFieldsToShow();
        }
        $table = new Table($output);
        $table->setHeaders($arFieldsToDisplay);


        if (!is_null($resSearch['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ])) {

            foreach ($resSearch['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] as $oneApp) {
                //it seems we need to reorder each row according to the headers...
                $oneAppOrdered = [];
                foreach ($arFieldsToDisplay as $k => $fieldname ) {
                    $oneAppOrdered[] = $oneApp[$fieldname];
                }

                $table->addRow($oneAppOrdered);
            }
        } else {
            $output->writeln('<red>Nothing to display</red>');
        }
        $table->render();
    }
        /*
         * Tests parameters inserted by users
         * arParamsIn : Parameters inserted by user
         * arPossibleParams: against possible param we got from AirwatchServiceClass
         */
    protected function _testParams($arParamsIn, $arPossibleParams){

        //clPossileParam = $this->_oAW->getPossibleParams();

        foreach ($arParamsIn as $optName => $optValue) {
            if (array_key_exists($optName, $arPossibleParams ) ) {
                if (!is_null( $optValue ) ) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }
    }

    protected function displayVerticalSearchResults($resSearch, OutputInterface $output)
    {
        $arHeadersName = ['FieldName', 'Value'];

        $table = new Table($output);
        $table->setHeaders($arHeadersName);

        if (!is_null($resSearch['data']) && !is_null($resSearch['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ])) {
            foreach ($resSearch['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] as $oneApp) {
                 $table->addRow(new TableSeparator());
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

    /*
     * meant to allow to display all fields even those that are arrays...
     * no specifics but we'll have a view on what's in each fields.
     * well I will go with jsonencode
     */
    protected function quicklyConvertArrayToString($arToConvert) {
        //$str = json_encode($arToConvert,JSON_PRETTY_PRINT);
        $str = str_replace("\n",'',json_encode($arToConvert));
        $str = str_replace ("\r",'',$str);

        return ($str);
    }

    /*
     * A method to output string with [OK]/[KO] at the begining
     * OK->gree
     * KO->red
     * IF->yellow
     */
    protected function myoutput(OutputInterface $output, $nStatus = self::CMD_STATUS_OK, $szText) {
        $beginText = '';
        switch ($nStatus) {
            case self::CMD_STATUS_OK :
                $beginText = '<green>[OK]</green>';
                break;
            case self::CMD_STATUS_KO :
                $beginText = '<red>[KO]</red>';
                break;
            case self::CMD_STATUS_IF :
                $beginText = '<yellow>[IF]</yellow>';
                break;
            default :
                $beginText = '<blue>[???]</blue>';
                break;
        }

        $output->writeln($beginText.' '.$szText);
    }

    abstract protected function doRun(InputInterface $input, OutputInterface $output);
}

?>