<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 15/01/2018
 * Time: 20:01
 */

namespace PhPeteur\AirwatchCommandLine\Commands;


use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMAMAppsAppleStoreSearch;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class MAMAppsApplestoreSearchCommand extends AirwatchCmd
{

    public function configure()
    {
        parent::configure();
        $this->setName('mam-apps-applestore-search')
            ->addArgument('appname', InputArgument::REQUIRED, 'appname to search in playstore')
            ->setDescription(AirwatchMAMAppsAppleStoreSearch::CLASS_SENTENCE_AIM);

        $this->_oAW = new AirwatchMAMAppsAppleStoreSearch( $this->_config );

    }

    protected function doRun(InputInterface $input, OutputInterface $output)
    {
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMAMAppsPlayStoreSearch object :/");
        //try {
            $res = $this->_oAW->Search($input->getArgument('appname'));
        //}
        /*catch (\GuzzleHttp\Exception\ClientException $e) {

            //we want to display a nice error to the user..
            //error are composed of an error code, message, and an activityId for support

            $err_decomposed = json_decode($e->getResponse()->getBody(), true);

            var_dump($e->getResponse());
            die (0);
        }
        */

        if (array_key_exists('Applications', $res['data']) )
        {
            $table = new Table($output);
            $table->setHeaders(['BundleID', 'ApplicationName', 'CurrentVersion']);
            foreach ($res['data']['Applications'] as $k => $arOneResult) {
                $table->addRow($arOneResult);
            }
            $table->render();

            $output->writeln('Total number of results displayed : ' . count($res['data']['Applications']) . '.');

        } else {
            $output->writeln('<red>No results.</red>');
        }

        return;
    }

}