<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 03/02/2020
 * Time: 10:09
 */

namespace PhPeteur\AirwatchCommandLine\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchSystemScimV2Users;

//awcmd system-scimv2-users --uuid 'e1d38318-3fc7-4834-b203-ed0110de1310' --rendervertical

class SystemScimV2UsersCommand extends AirwatchCmd
{
    protected function configure()
    {
        $this->_oAW = new AirwatchSystemScimV2Users( $this->_config );
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMDMDevicesSearch object :/");

        $this->setName('system-scimv2-users');
        foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription)
        {
            $this->addOption($param,null, InputOption::VALUE_REQUIRED, $pdescription);
        }
        $this->setDescription(AirwatchSystemScimV2Users::CLASS_SENTENCE_AIM);

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

        $resquery = self::run_search_custo($arInterestingParams, $input,'application/scim+json;version=2');

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

    }

    protected function run_search_custo($arSearchParams, InputInterface $input, $szContentType = 'application/scim+json;version=2') : array
    {
        $resquery = parent::run_search_custo($arSearchParams, $input, $szContentType);

        $resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][0]['schemas'] =
            implode( ','.PHP_EOL, json_decode($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][0]['schemas'] ) ) ;

        $argrps = json_decode($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][0]['groups'],true);
        $szgrps = '';
        $ncpt = count($argrps[0]);

        $i = 1;
        foreach ($argrps[0] as $k => $val) {
            $szgrps .= $k. ' = '.$val;
            if ($i < $ncpt) {
                $i++;
                $szgrps .= ', '.PHP_EOL;
            }
        }

        $resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][0]['groups'] = $szgrps;

        $armeta = json_decode($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][0]['meta'], true);
        $szmeta = '';

        $ncpt = count($armeta);

        $i = 1;
        foreach ($armeta as $k => $val) {
            $szmeta .= $k.' = '.$val;
            if ($i < $ncpt) {
                $i++;
                $szmeta .= ','.PHP_EOL;
            }
        }

        $resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()][0]['meta'] = $szmeta;

        return ($resquery);
    }


}