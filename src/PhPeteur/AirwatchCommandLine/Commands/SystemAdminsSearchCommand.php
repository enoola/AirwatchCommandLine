<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 21/01/2018
 * Time: 16:43
 */

namespace PhPeteur\AirwatchCommandLine\Commands;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchSystemAdminsSearch;


class SystemAdminsSearchCommand extends AirwatchCmd
{

    protected function configure()
    {
        $this->_oAW = new AirwatchSystemAdminsSearch($this->_config);
        if (is_null($this->_oAW))
            throw new AirwatchCmdException('unable to create AirwatchSystemAdminSearch object within' . __CLASS__, 42);

        $this->setName('system-admins-search');
        if (!is_null($this->_oAW->getPossibleSearchParams())) {
            foreach ($this->_oAW->getPossibleSearchParams() as $param => $pdescription) {
                $this->addOption($param, null, InputOption::VALUE_REQUIRED, $pdescription);
            }
        }
        $this->setDescription(AirwatchSystemAdminsSearch::CLASS_SENTENCE_AIM);

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

        $resquery = $this->run_search($arInterestingParams, $input);

        if (parent::isOptionRenderVerticalOn($input)) {
            $this->displayVerticalSearchResults($resquery, $output);
        } else {
            $this->displayHorizontalSearchResults($resquery, $input, $output);
        }

        $bWeHaveResults = !is_null($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse() ]);
        $nb_entry_showed = (!$bWeHaveResults) ? '0' : count($resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse()]);
        $output->writeln('I displayed : ' . $nb_entry_showed . ' result(s).');
        if ($bWeHaveResults) {
            $output->writeln('Current page : ' . $resquery['data']['Page'] . '.');
            $output->writeln('Current page size : ' . $resquery['data']['PageSize'] . '.');
            $output->writeln('Total number of entries available : ' . $resquery['data']['Total'] . '.');
        }

        return ($resquery);
    }


    /*
     * overloading parent function.
     */
    protected function run_search($arSearchParams, InputInterface $input,$szContentType='application/json;version=1') : array
    {
        $resquery = parent::run_search($arSearchParams, $input, $szContentType);
        if (array_key_exists($this->_oAW->getFieldnameToPickInDataResultResponse(), $resquery['data']))
        {
            if (!is_null($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()])) {
                foreach ($resquery['data'][$this->_oAW->getFieldnameToPickInDataResultResponse()] as $k => $oneAdmin){
                    $resquery['data'][ $this->_oAW->getFieldnameToPickInDataResultResponse()][$k] = self::handleSpecifcs_inRowWithInterestingFields($oneAdmin);
                }
            }
        }

        return ($resquery);
    }

    /*
     * the KISS function (Keep It Simple & Stupid
     * Basically it will handle specific fields (such as the one supposed to be numeric and are array,
     * or shall be boolean but blank when false...
     */
    private function handleSpecifcs_inRowWithInterestingFields ($arOneRowWithInterestingFields) :array
    {
        // shall be numeric instead we do have an array with value containing the Id... so specific but KISS
        /*if (array_key_exists('Id', $arOneRowWithInterestingFields)) {
            $arOneRowWithInterestingFields['Id'] = $arOneRowWithInterestingFields['Id']['Value'];
        }*/
        if(array_key_exists('IsActiveDirectoryUser', $arOneRowWithInterestingFields)) {
            if (empty( $arOneRowWithInterestingFields['IsActiveDirectoryUser'] ) || ( $arOneRowWithInterestingFields['IsActiveDirectoryUser'] == 0))
                $arOneRowWithInterestingFields['IsActiveDirectoryUser'] = 'false';
            else if ($arOneRowWithInterestingFields['IsActiveDirectoryUser'] == 1)
                $arOneRowWithInterestingFields['IsActiveDirectoryUser'] = 'true';
            else
                $arOneRowWithInterestingFields['IsActiveDirectoryUser'] = 'n/a';
        }
        return ($arOneRowWithInterestingFields);
    }
}