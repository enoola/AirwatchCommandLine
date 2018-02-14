<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 01/02/2018
 * Time: 17:10
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDevicesBulkDeviceSearch;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

/*
 * Retrieve Bulk Device Information
 * Functionality – Retrieves information about multiple devices identified by the specified Id type.
 */
class MDMDevicesBulkDeviceSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDevicesBulkDeviceSearch( $this->_config );
        if (is_null( $this->_oAW))
            die (">>unable to create AirwatchMDMDeviceInformationsSearch object :/");

        $this->setName('mdm-devices-device-search'); //not super great for usage purpose :/

        $this->addOption('searchby', null, InputOption::VALUE_REQUIRED, $this->_oAW->getPossibleSearchParams()['searchby'] );
        $this->addArgument('ids', InputArgument::REQUIRED, $this->_oAW->getPossibleSearchParams()['ids'] );

        $this->setDescription('Retrieves information about multiple devices identified by the specified Id type.');

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

        $arInterestingParams['ids'] = explode(',',$input->getArgument('ids'));

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
}
