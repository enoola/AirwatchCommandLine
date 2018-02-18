<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 03/02/2018
 * Time: 10:25
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDeviceProfilesSearch;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

class MDMDeviceProfilesSearchCommand extends AirwatchCmd
{

    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDeviceProfilesSearch( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDeviceInformationsSearch object :/");

        $this->setName('mdm-device-profiles-search');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }

        $this->setDescription(AirwatchMDMDeviceProfilesSearch::CLASS_SENTENCE_AIM);

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        if (is_null ( $input->getOption('id')) )
            throw new \Exception("I need an id at least");

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

    /*
     * to be implemented properly
     */
    protected function run_search($arSearchParams, InputInterface $input): array
    {
        $resquery = parent::run_search($arSearchParams, $input);
       /*
        *
        * if (!is_null($resquery['data']) && array_key_exists($this->_oAW->getFieldnameToPickInDataResultResponse(), $resquery['data']) && !is_null($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()])) {
            foreach ($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()] as $k => $arOneEntry) {
                //I didn;t see fields with multiple devices
                //$resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][$k]['LocationGroupId'] = json_decode($arOneEntry['LocationGroupId'], true)['Id']['Value'];
                ///$resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][$k]['LocationGroupId'] = json_decode($arOneEntry['LocationGroupId'], true);
            }
        }*/
        return ($resquery);
    }

}