<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 10/08/2018
 * Time: 06:11
 */


namespace PhPeteur\AirwatchCommandLine\Commands;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchSystemUserUpdate;


/*
 * Updates the details of a device enrollment user.
 */
class SystemUserUpdateCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchSystemUserUpdate( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchSystemUserUpdate object :/");

        $this->setName('system-user-update');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchSystemUserUpdate::CLASS_SENTENCE_AIM);
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

        $arInterestingParams = (count($arInterestingParams) > 0) ? $arInterestingParams : null ;

        $resquery = parent::run_post($arInterestingParams, $input );
        //var_dump($resquery);
        //print_r($resquery);
        if (array_key_exists('status', $resquery)) {
            if (strncmp('200',$resquery['status'],3) == 0)
                $output->writeln('User with id ' . $arInterestingParams['id'] . ' updated.');
        }
        else if (array_key_exists('statuscode', $resquery)) {
            $output->writeln('User with id ' . $arInterestingParams['id'] . ' not update. error:'.$resquery['statuscode'] . '.');
        }

        return ($resquery);
    }

}

?>