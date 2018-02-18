<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 31/01/2018
 * Time: 10:27
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMDMComplianceAttributesOrgGroupsComplianceAttributesSearch;

class MDMComplianceAttributesOGComplianceAttrSearchCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMComplianceAttributesOrgGroupsComplianceAttributesSearch( $this->_config );
        if (is_null( $this->_oAW))
            die (">>unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('mdm-complianceattributes-og-search');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchMDMComplianceAttributesOrgGroupsComplianceAttributesSearch::CLASS_SENTENCE_AIM);
        parent::addGenericSearchOptions();
    }

    protected function doRun(InputInterface $input, OutputInterface $output){

        $arInterestingParams = [];

        if (is_null($input->getOption('vendorname') ) ) {
            throw new \Exception("I need a vendorname");
        }

        $arInterestingParams['vendorname']= $input->getOption('vendorname');//= count($arInterestingParams) >0 ? $arInterestingParams : null;

        $resquery = parent::run_search($arInterestingParams, $input );

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
        return ($resquery);
    }

}