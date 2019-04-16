<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 15/04/2019
 * Time: 10:59
 */

namespace PhPeteur\AirwatchCommandLine\Commands;


use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDEPProfilesSearch;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;


class MDMDEPProfilesSearchCommand extends AirwatchCmd
{

    protected function configure()
    {
        //$this->_fieldnameToShowInDataResult = "Application";
        $this->_oAW = new AirwatchMDMDEPProfilesSearch($this->_config);
        if (is_null($this->_oAW))
            die ("Unable to create AirwatchMDMDEPProfilesSearch object :/");

        $this->setName('mdm-dep-profiles-search');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
            $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchMDMDEPProfilesSearch::CLASS_SENTENCE_AIM);
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

        $resquery = parent::run_search($arInterestingParams, $input);

        if (parent::isOptionRenderVerticalOn($input)) {
            $this->displayVerticalSearchResults($resquery, $output);
        } else {
            $this->displayHorizontalSearchResults($resquery, $input, $output);
        }
        $bWeHaveResults = !is_null($resquery[$this->_oAW->getFieldnameToPickInDataResultResponse()]);
        $nb_entry_showed = (!$bWeHaveResults) ? '0' : count($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()]);
        $output->writeln('I displayed : ' . $nb_entry_showed . ' result(s).');

        /*
        $output->writeln('Total number of entries available : ' . $resquery['data']['Total'] . '.');


        if ($bWeHaveResults) {
            $output->writeln('Current page : ' . $resquery['data']['Page'] . '.');
            $output->writeln('Current page size : ' . $resquery['data']['PageSize'] . '.');
            $output->writeln('Total number of entries available : ' . $resquery['data']['Total'] . '.');
        }
*/
        return ($resquery);
    }
}