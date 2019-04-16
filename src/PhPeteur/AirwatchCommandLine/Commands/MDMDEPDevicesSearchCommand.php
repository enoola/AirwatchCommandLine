<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 16/04/2019
 * Time: 14:09
 */

namespace PhPeteur\AirwatchCommandLine\Commands;


use PhPeteur\AirwatchWebservices\Services\AirwatchMDMDEPDevicesSearch;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

/*
 * php console.php mdm-dep-devices-search --OrganizationGroupUuid "031e8ee4-0e5e-4364-a5ae-bba84fc5bbc5"
 */
class MDMDEPDevicesSearchCommand extends AirwatchCmd
{

    protected function configure()
    {
        //$this->_fieldnameToShowInDataResult = "Application";
        $this->_oAW = new AirwatchMDMDEPDevicesSearch($this->_config);
        if (is_null($this->_oAW))
            die ("Unable to create AirwatchMDMDEPDevicesSearch object :/");

        $this->setName('mdm-dep-devices-search');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
            $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchMDMDEPDevicesSearch::CLASS_SENTENCE_AIM);
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

        $resquery = parent::run_search_custo_multientries($arInterestingParams, $input);

        var_dump($resquery);

        if (parent::isOptionRenderVerticalOn($input)) {
            $this->displayVerticalSearchResults($resquery, $output);
        } else {
            $this->displayHorizontalSearchResults($resquery, $input, $output);
        }
        $bWeHaveResults = !is_null($resquery['data']);
        $nb_entry_showed = (!$bWeHaveResults) ? '0' : count($resquery['data']);
        $output->writeln('I displayed : ' . $nb_entry_showed . ' result(s).');


        return ($resquery);
    }


}