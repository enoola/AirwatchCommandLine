<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 04/02/2018
 * Time: 10:24
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchSystemGroupUsersSearch;

/*
 * Fetch Enrollment Users in an Organization Group (*Refactored)
 * Functionality – Retrieves the details of all the enrollment users in an organization group.
 */
class SystemGroupUsersSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchSystemGroupUsersSearch($this->_config);
        if (is_null($this->_oAW))
            throw new AirwatchCmdException('Unable to create AirwatchSystemGroupUsersSearch object within' . __CLASS__, 42);

        $this->setName('system-groupusers-search');
        if (!is_null($this->_oAW->getPossibleSearchParams())) {
            foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
                $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);

            }
        }
        $this->setDescription(AirwatchSystemGroupUsersSearch::CLASS_SENTENCE_AIM);

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

        if (is_null ( $input->getOption('id')) )
            throw new \Exception("I need an id at least");

        foreach ($input->getOptions() as $optName => $optValue) {
            if (array_key_exists($optName, $clPossileParam ) ) {
                if (!is_null( $optValue ) ) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }
        $arInterestingParams = ( count($arInterestingParams) > 0 ) ? $arInterestingParams : null;

        $resquery = $this->run_search_custo_multientries($arInterestingParams, $input);

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