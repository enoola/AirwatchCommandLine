<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 02/02/2018
 * Time: 16:32
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Exception\AirwatchCmdException;
use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDeviceSmartGroupsSearch;

/*
 * Retrieve Device Associated Smart Groups
 * Functionality – Retrieves all the smart groups associated with the device.
 */
class MDMDeviceSmartGroupsSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMDeviceSmartGroupsSearch($this->_config);
        if (is_null($this->_oAW))
            throw new AirwatchCmdException('unable to create AirwatchMDMSmartGroupSearch object within' . __CLASS__, 42);

        $this->setName('mdm-device-smartgroups-search');
        if (!is_null($this->_oAW->getPossibleSearchParams())) {
            foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
                $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);

            }
        }
        $this->setDescription('Retrieves all the smart groups associated with the device.');

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output): array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        if (is_null($input->getOption('id')))
            throw new \Exception("I need an id at least");

        foreach ($input->getOptions() as $optName => $optValue) {
            if (array_key_exists($optName, $clPossileParam)) {
                if (!is_null($optValue)) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }
        $arInterestingParams = (count($arInterestingParams) > 0) ? $arInterestingParams : null;

        $resquery = $this->run_search($arInterestingParams, $input);

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

    /*
     * we transform field SmartGroupId into a value rather than getting json, bit more costy in term of calculation but at least
     * it's redable and less redudant.
     */
    protected function run_search($arSearchParams, InputInterface $input): array
    {
        $resquery = parent::run_search($arSearchParams, $input);

        if (!is_null($resquery['data']) && array_key_exists($this->_oAW->getFieldnameToPickInDataResultResponse(), $resquery['data'])) {
            foreach ($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()] as $k => $arOneEntry) {
                $resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][$k]['SmartGroupId'] = json_decode($arOneEntry['SmartGroupId'], true)['Value'];
            }
        }
        return ($resquery);
    }

}