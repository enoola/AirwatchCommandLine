<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 02/02/2018
 * Time: 20:31
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDeviceSecuritySearch;

/*
 * Retrieve Security Information
 * Functionality – Retrieves the security information of the device identified by device ID.
 */

class MDMDeviceSecuritySearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDeviceSecuritySearch($this->_config);
        if (is_null($this->_oAW))
            die ("Unable to create AirwatchMDMDeviceSecuritySearch object :/");

        $this->setName('mdm-device-security-search');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
            $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);
        }

        $this->setDescription(AirwatchMDMDeviceSecuritySearch::CLASS_SENTENCE_AIM);

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output): array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        if (is_null($input->getOption('id')))
            throw new \Exception("I need an id at least");

        $arPossibleSearchBy = ["Macaddress", "Udid", "Serialnumber", "ImeiNumber"];
        if (!is_null($input->getOption('searchby'))) {
            if (!in_array($input->getOption('searchby'), $arPossibleSearchBy))
                throw new \Exception('Possible value for searchby :' . implode(',', $arPossibleSearchBy) . '.');
        }

        foreach ($input->getOptions() as $optName => $optValue) {
            if (array_key_exists($optName, $clPossileParam)) {
                if (!is_null($optValue)) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }
        $arInterestingParams = (count($arInterestingParams) > 0) ? $arInterestingParams : null;

        $resquery = $this->run_search_custo($arInterestingParams, $input);

        if (parent::isOptionRenderVerticalOn($input)) {
            $this->displayVerticalSearchResults($resquery, $output);
        } else {
            $this->displayHorizontalSearchResults($resquery, $input, $output);
        }

        $bWeHaveResults = !is_null($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()]);
        $nb_entry_showed = (!$bWeHaveResults) ? '0' : count($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()]);
        $output->writeln('I displayed : ' . $nb_entry_showed . ' result(s).');
        if ($bWeHaveResults && !is_null($resquery['data']['Total'])) {
            $output->writeln('Current page : ' . $resquery['data']['Page'] . '.');
            $output->writeln('Current page size : ' . $resquery['data']['PageSize'] . '.');
            $output->writeln('Total number of entries available : ' . $resquery['data']['Total'] . '.');
        }
        return ($resquery);
    }




}