<?php

/**
* Created by PhpStorm.
* User: enola
* Date: 10/01/2018
* Time: 21:32
*/


require (__DIR__.'/vendor/autoload.php');

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Application;
//use PhPeteur\AirwatchSystemUsers;
use \PhPeteur\AirwatchCommandLine\Commands\SystemInfoCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemUsersSearchCommand;
use PhPeteur\AirwatchCommandLine\Commands\MAMAppsSearchCommand;
#use PhPeteur\Commands\MAMAppsPlaystoreSearchCommand;
#use PhPeteur\Commands\MDMDevicesSearchCommand;
#use \PhPeteur\Commands\MDMSmartGroupsSearchCommand;
#use \PhPeteur\Commands\MDMProductsSearchCommand;
#use \PhPeteur\Commands\SystemAdminsSearchCommand;
#use \PhPeteur\Commands\MAMAppsGroupsSearchCommand;
#use \PhPeteur\Commands\MAMAppRemovalLogsSearchCommand;
#use \PhPeteur\Commands\SystemGroupsSearchCommand;
#use \PhPeteur\Commands\MDMDeviceAppsSearchCommand;
#use \PhPeteur\Commands\MDMDeviceInformationsSearchCommand;
#use \PhPeteur\Commands\MDMDeviceComplianceDetailsSearchCommand;
#use \PhPeteur\Commands\MDMComplianceAttributesOGComplianceAttrSearchCommand;
#use \PhPeteur\Commands\MDMDevicesAppStatusSearchCommand;
#use \PhPeteur\Commands\MDMDeviceEventLogSearchCommand;
#use \PhPeteur\Commands\MDMDeviceGPSSearchCommand;
#use \PhPeteur\Commands\MDMDevicesBulkGPSSearchCommand;
#use \PhPeteur\Commands\MDMDevicesBulkDeviceSearchCommand;
#use \PhPeteur\Commands\MAMAppsGroupSearchCommand;
#use \PhPeteur\Commands\MDMSmartGroupSearchCommand;
#use \PhPeteur\Commands\MDMDeviceSmartGroupsSearchCommand;
#use \PhPeteur\Commands\MDMDeviceSecuritySearchCommand;
#use \PhPeteur\Commands\MDMDeviceEnrolledDevicesCountSearchCommand;
#use \PhPeteur\Commands\MDMDeviceUserSearchCommand;
#use \PhPeteur\Commands\MDMDeviceProfilesSearchCommand;
#use \PhPeteur\Commands\MDMDeviceNetworkSearchCommand;
#use \PhPeteur\Commands\SystemGroupSearchCommand;
#use \PhPeteur\Commands\SystemGroupChildrenSearchCommand;
#use \PhPeteur\Commands\SystemGroupAdminsSearchCommand;
#use \PhPeteur\Commands\SystemGroupUsersSearchCommand;
#use \PhPeteur\Commands\SystemGroupRolesSearchCommand;
#use \PhPeteur\Commands\MDMDeviceAdminAppsSearchCommand;
#use \PhPeteur\Commands\MDMDevicesBulkSettingsSearchCommand;
#use \PhPeteur\Commands\MDMDeviceCertificatesSearchCommand;
#use \PhPeteur\Commands\MDMDeviceNotesSearchCommand;
#use \PhPeteur\Commands\MDMDevicesExtensiveSearchCommand;
#use \PhPeteur\Commands\MDMDevicesLiteSearchCommand;
#use \PhPeteur\Commands\MDMDevicesSecurityInformationsSearchCommand;
#use \PhPeteur\Commands\MDMDevicesNetworkInformationsSearchCommand;
#use \PhPeteur\Commands\MDMCompliancePoliciesSearchCommand;
#use \PhPeteur\Commands\MDMDeviceCustomAttributesSearchCommand;
#use \PhPeteur\Commands\MDMDeviceCustomAttributesChangeReportSearchCommand;
#//use \PhPeteur\Commands\MDMRelayServersSearchCommand;
#use \PhPeteur\Commands\SystemUserSearchCommand;
#use \PhPeteur\Commands\SystemUsersEnrolledDevicesSearchCommand;
#use \PhPeteur\Commands\MDMProductFailedSearchCommand;
#use \PhPeteur\Commands\MDMProductInProgressSearchCommand;
#use \PhPeteur\Commands\MDMProductAssignedSearchCommand;



$configfile = __DIR__.'/config.yml';
if (!is_readable($configfile)) {
    die('Please copy config.yml.dist to config.yml'.PHP_EOL);
}
$cfg = Yaml::parseFile($configfile);


//var_dump($cfg);
//exit;
$application = new Application("Airwatch command line tool");

$application->add(new SystemInfoCommand( $cfg ) );
$application->add(new SystemUsersSearchCommand( $cfg ) );
$application->add(new MAMAppsSearchCommand( $cfg ) );
#$application->add(new MAMAppsPlaystoreSearchCommand( $cfg ) );
#$application->add(new MDMDevicesSearchCommand( $cfg ) );
#$application->add(new MDMSmartGroupsSearchCommand( $cfg ) );
#$application->add(new MDMProductsSearchCommand( $cfg ) );
#$application->add(new SystemAdminsSearchCommand( $cfg ));
#$application->add(new MAMAppsGroupsSearchCommand( $cfg ));
#$application->add(new SystemGroupsSearchCommand( $cfg ));
#$application->add(new MAMAppRemovalLogsSearchCommand( $cfg ));
#$application->add(new MDMDeviceAppsSearchCommand( $cfg ));
#$application->add(new MDMDeviceInformationsSearchCommand( $cfg ));
#$application->add(new MDMDeviceComplianceDetailsSearchCommand( $cfg ));
#$application->add(new MDMComplianceAttributesOGComplianceAttrSearchCommand( $cfg ));
#$application->add(new MDMDevicesAppStatusSearchCommand( $cfg ));
#$application->add(new MDMDeviceEventLogSearchCommand( $cfg ));
#$application->add(new MDMDeviceGPSSearchCommand( $cfg ));
#$application->add(new MDMDevicesBulkGPSSearchCommand( $cfg ));
#$application->add(new MDMDevicesBulkDeviceSearchCommand( $cfg ));
#$application->add(new MAMAppsGroupSearchCommand( $cfg ));
#$application->add(new MDMSmartGroupSearchCommand( $cfg ));
#$application->add(new MDMDeviceSmartGroupsSearchCommand( $cfg ));
#$application->add(new MDMDeviceSecuritySearchCommand( $cfg ));
#$application->add(new MDMDeviceEnrolledDevicesCountSearchCommand( $cfg ));
#$application->add(new MDMDeviceUserSearchCommand( $cfg ));
#$application->add(new MDMDeviceProfilesSearchCommand( $cfg ));
#$application->add(new MDMDeviceNetworkSearchCommand( $cfg ));
#$application->add(new SystemGroupSearchCommand( $cfg ));
#$application->add(new SystemGroupChildrenSearchCommand( $cfg ));
#$application->add(new SystemGroupAdminsSearchCommand( $cfg ));
#$application->add(new SystemGroupUsersSearchCommand( $cfg ));
#$application->add(new SystemGroupRolesSearchCommand( $cfg ));
#$application->add(new MDMDeviceAdminAppsSearchCommand( $cfg ));
#$application->add(new MDMDevicesBulkSettingsSearchCommand( $cfg ));
#$application->add(new MDMDeviceCertificatesSearchCommand( $cfg ));
#$application->add(new MDMDeviceNotesSearchCommand( $cfg ));
#$application->add(new MDMDevicesExtensiveSearchCommand( $cfg ));
#$application->add(new MDMDevicesLiteSearchCommand( $cfg ));
#$application->add(new MDMDevicesSecurityInformationsSearchCommand( $cfg ));
#$application->add(new MDMDevicesNetworkInformationsSearchCommand( $cfg ));
#$application->add(new MDMCompliancePoliciesSearchCommand( $cfg ));
#$application->add(new MDMDeviceCustomAttributesSearchCommand( $cfg ));
#$application->add(new MDMDeviceCustomAttributesChangeReportSearchCommand( $cfg ));
#$application->add(new SystemUsersEnrolledDevicesSearchCommand( $cfg ));
#//$application->add(new MDMRelayServersSearchCommand( $cfg ));
#$application->add(new SystemUserSearchCommand( $cfg ));
#$application->add(new MDMProductFailedSearchCommand( $cfg ));
#$application->add(new MDMProductInProgressSearchCommand( $cfg ));
#$application->add(new MDMProductAssignedSearchCommand( $cfg ));

//MAMAppsPlaystoreSearchCommand
// ... register commands


$application->run();