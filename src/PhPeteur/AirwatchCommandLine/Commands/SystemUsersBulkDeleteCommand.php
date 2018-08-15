<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 15/08/2018
 * Time: 11:41
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchSystemUsersBulkDelete;

/*
 * Deletes a list of enrollment users from the AirWatch Console.
 *
 */
class SystemUsersBulkDeleteCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchSystemUsersBulkDelete( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('system-users-bulk-delete');

        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchSystemUsersBulkDelete::CLASS_SENTENCE_AIM);
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

        $arOfIds = ['BulkValues' => ['Value' => explode(',', $input->getOption('BulkValues')) ]];
        //$arOfIds['BulkValues'] = ['value' => explode(',', $input->getOption('ids'))];
        //$arOfIds['BulkValues'] = $this->quicklyConvertArrayToString(array ('Value' => explode(',', $input->getOption('BulkValues'))));
        //var_dump($arOfIds);
        //exit;

        //$arInterestingParams = count($arInterestingParams) >0 ? $arInterestingParams : null;

        $resquery = parent::run_delete($arOfIds, $input );

        /*
         * we need to treat the answer properly
         */
        //statuscode:400 none removed
        //status: 200 OK
        //  Total items = number of AcceptedItems ->OK
        //  Total items != number of AcceptedItems -> KO

        //ids we want to remove are on $arOfIds['BulkValues']['Value']
        $arOfIdsRemoved = $arOfIds['BulkValues']['Value'];
        $arOfIdsNotRemoved=[];

        if (array_key_exists('status', $resquery)) {
            if (strncmp('200',$resquery['status'],3) == 0)  //means one or more users have been deleted
            {
                if ($resquery['data']['TotalItems'] == $resquery['data']['AcceptedItems']) //everything has been deleted, ok
                {
                    $this->myoutput($output, self::CMD_STATUS_KO, "Every users deleted.");
                } else //at least one user failed
                {
                    $this->myoutput($output, self::CMD_STATUS_KO,"TotalItems:" . $resquery['data']['TotalItems'] ) . PHP_EOL;
                    $this->myoutput($output, self::CMD_STATUS_KO,"AcceptedItems:" . $resquery['data']['AcceptedItems'] ) . PHP_EOL;
                    $this->myoutput($output, self::CMD_STATUS_KO, "FailedItems:" . $resquery['data']['FailedItems'] . PHP_EOL );
                    foreach ($resquery['data']['Faults']['Fault'] as $oneNotRemoved) {
                        $arOfIdsNotRemoved[] = $oneNotRemoved['ItemValue'];
                    }
                    foreach ($arOfIdsNotRemoved as $oneNotRemoved) {
                        unset ($arOfIdsRemoved[array_search($oneNotRemoved, $arOfIdsRemoved)] );
                    }
                }
            }

        }
        else if (array_key_exists('statuscode', $resquery)) {
            if ($resquery['statuscode'] == 400) { //none of the provided users have been removed
                $this->myoutput($output, self::CMD_STATUS_KO,'none of the users provided have been deleted.');
                $arOfIdsNotRemoved = $arOfIds['BulkValues']['Value'];
                $arOfIdsRemoved = [];
            } else {
                die ('this shall not happen');
                $output->writeln('Users with ids ' . $input->getOption('BulkValues') . ' not deleted. error:' . $resquery['statuscode'] . '.');
            }
        }
        if (count($arOfIdsRemoved)>0)
            $this->myoutput($output, self::CMD_STATUS_IF, "Below Ids removed");
        foreach ($arOfIdsRemoved as $oneIdRemoved){
            $this->myoutput($output, self::CMD_STATUS_OK, "\t Removed:".$oneIdRemoved );
        }
        if (count($arOfIdsNotRemoved)>0)
            $this->myoutput($output, self::CMD_STATUS_IF, "Below Ids not removed");

        foreach ($arOfIdsNotRemoved as $oneNotRemoved) {
            $this->myoutput($output, self::CMD_STATUS_KO, "\t Not Removed:". $oneNotRemoved );
        }

        return ($resquery);
    }

}

?>