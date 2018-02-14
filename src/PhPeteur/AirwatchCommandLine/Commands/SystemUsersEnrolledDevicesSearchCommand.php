<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 06/02/2018
 * Time: 09:09
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchWebservices\Services\AirwatchSystemUsersEnrolledDevicesSearch;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

/*
 * Search Enrolled Device
 * Functionality – Retrieves enrolled device details for the query information provided in the request.
 */
class SystemUsersEnrolledDevicesSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchSystemUsersEnrolledDevicesSearch( $this->_config );
        if (is_null( $this->_oAW))
            die ("unable to create AirwatchSystemUsersEnrolledDevicesSearch object :/");

        $this->setName('system-users-enrolleddevices-search');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }

        $this->setDescription('Retrieves enrolled device details for the query information provided in the request.');

        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();

     // if( is_null ( $input->getOption('organizationgroupid')) || is_null ( $input->getOption('organizationgroup')))
     //       throw new \Exception("Wrong Parameters provided at least 'organizationid' or 'organizationgroup' is mandatory");



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
}