<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 03/08/2018
 * Time: 09:33
 */

namespace PhPeteur\AirwatchCommandLine\Commands;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchSystemUserChangeOG;

/*
 * Delete Enrollment User's defined by id
 * Functionality – Delete enrollment user's identified by the enrollment user Id.
 */
class SystemUserChangeOGCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchSystemUserChangeOG( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('system-user-change-og');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchSystemUserChangeOG::CLASS_SENTENCE_AIM);
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

        //$resquery = null;
        $resquery = parent::run_post($arInterestingParams, $input );

        //print_r($resquery);
        if (array_key_exists('status', $resquery)) {
            if (strncmp('200',$resquery['status'],3) == 0)
                $output->writeln('User with id ' . $arInterestingParams['id'] . ' deleted.');
        }
        else if (array_key_exists('statuscode', $resquery)) {
//            if ($resquery['statuscode'] == 200)
//                $output->writeln('User with id ' . $arInterestingParams['id'] . ' deleted.');
            $output->writeln('User with id ' . $arInterestingParams['id'] . ' not deleted. error:'.$resquery['statuscode'] . '.');
        }
        //var_dump($resquery);
        /*
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
        */
        return ($resquery);
    }

}

?>