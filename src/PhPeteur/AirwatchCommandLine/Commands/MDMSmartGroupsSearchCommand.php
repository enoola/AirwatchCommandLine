<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 18/01/2018
 * Time: 08:21
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMDMSmartGroupsSearch;
use PhPeteur\AirwatchWebservices\Exception\AirwatchCmdException;

class MDMSmartGroupsSearchCommand extends AirwatchCmd
{

    protected function configure()
    {
        $this->_oAW = new AirwatchMDMSmartGroupsSearch( $this->_config );
        if (is_null( $this->_oAW) )
            throw new AirwatchCmdException('unable to create AirwatchMDMSmartGroupsSearch object within'.__CLASS__,42);

        $this->setName('mdm-smartgroups-search');
        if (! is_null ( $this->_oAW->getPossibleSearchParams() ) ) {
            foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
                $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);
            }
        }
        $this->setDescription('Searches for smartgroups using the query information provided.');
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

        $resquery = $this->run_search($arInterestingParams, $input);

        if (parent::isOptionRenderVerticalOn($input)) {
            $this->displayVerticalSearchResults($resquery, $output);
        } else {
            $this->displayHorizontalSearchResults($resquery, $input, $output);
        }

        $bWeHaveResults = !is_null($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ] );
        $nb_entry_showed = (!$bWeHaveResults ) ? '0' : count($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ]);
        $output->writeln('I displayed : '.$nb_entry_showed.' result(s).');
        if ( $bWeHaveResults ) {
            $output->writeln('Current page : ' . $resquery['data']['Page'] . '.');
            $output->writeln('Current page size : ' . $resquery['data']['PageSize'] . '.');
            $output->writeln('Total number of entries available : ' . $resquery['data']['Total'] . '.');
        }

        return ( $resquery );
    }

}