<?php
/**
 * Created by PhpStorm.
 * User: enola
 * Date: 15/01/2018
 * Time: 20:01
 */

namespace PhPeteur\AirwatchCommandLine\Commands;


use PhPeteur\AirwatchCommandLine\AirwatchCmd\AirwatchCmd;
use PhPeteur\AirwatchWebservices\Services\AirwatchMAMAppsPlayStoreSearch;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class MAMAppsPlaystoreSearchCommand extends AirwatchCmd
{

    public function configure()
    {
        parent::configure();
        $this->setName('mam-apps-playstore-search')
            ->addArgument('appname', InputArgument::REQUIRED, 'appname to search in playstore')
            ->setDescription(AirwatchMAMAppsPlayStoreSearch::CLASS_SENTENCE_AIM);

        $this->_oAW = new AirwatchMAMAppsPlayStoreSearch( $this->_config );

    }

    protected function doRun(InputInterface $input, OutputInterface $output)
    {
        if (is_null( $this->_oAW))
            die ("Unable to create AirwatchMAMAppsPlayStoreSearch object :/");
        $res = $this->_oAW->Search( $input->getArgument('appname') );


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