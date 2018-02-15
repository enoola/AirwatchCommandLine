<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 08/02/2018
 * Time: 08:17
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchWebservices\Services\AirwatchMDMProductsReprocessAction;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;

class MDMProductsReprocessActionCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchMDMProductsReprocessAction( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMProductReprocessAction object :/");

        $this->setName('mdm-product-reprocess');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
            $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        //$this->addOption('searchby', null, InputOption::VALUE_REQUIRED, $this->_oAW->getPossibleSearchParams()['searchby'] );
        $this->addOption('ProductID', null, InputOption::VALUE_REQUIRED, $this->_oAW->getPossibleSearchParams()['ProductID']);
        $this->addArgument('DeviceIds', InputArgument::REQUIRED, $this->_oAW->getPossibleSearchParams()['DeviceIds'] );
        $this->addOption('ForceFlag', null, InputOption::VALUE_REQUIRED, $this->_oAW->getPossibleSearchParams()['ForceFlag']);


        $this->setDescription('Initiates reprocessing of a product or product and device(s) by the policy engine. Supports a reprocess and a forced reprocess.');
    }

    protected function doRun(InputInterface $input, OutputInterface $output) : array
    {
        $arInterestingParams = [];
        $clPossileParam = $this->_oAW->getPossibleSearchParams();



        foreach ($input->getOptions() as $optName => $optValue) {
            if (array_key_exists($optName, $clPossileParam ) ) {
                if (!is_null( $optValue ) ) {
                    $arInterestingParams[$optName] = $optValue;
                }
            }
        }
        //var_dump($input->getArguments('ids'));

        $arInterestingParams['DeviceIds'] = null;
        $arDevIds = explode(',',$input->getArgument('DeviceIds'));
        $arFormattedDevIds = null;
        foreach ($arDevIds as $oneId) {
            $arFormattedDevIds[]= ['ID'=>$oneId];
        }
        $arInterestingParams['DeviceIds'] = $arFormattedDevIds;
        $arActionResponse = $this->_oAW->Action($arInterestingParams);

        //something went wrong..
        if (!array_key_exists('data', $arActionResponse)) {
            $output->writeln('an error occured will stop : ');
            var_dump($arActionResponse);
            exit;
        }
        //if an exception is thrown we let it go

        $arInterestingParams = ( count($arInterestingParams) > 0 ) ? $arInterestingParams : null;

        $resquery = $this->run_search($arInterestingParams, $input);

        //$output->writeln('Product: "'..'" with ID '""' had been reprocessed for device with ID '.');
        return ( $resquery );
    }
}