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
use PhPeteur\AirwatchCommandLine\Commands\SystemInfoCommand;
use PhPeteur\AirwatchCommandLine\Commands\SystemUsersSearchCommand;
use PhPeteur\AirwatchCommandLine\Commands\MAMAppsSearchCommand;
use PhPeteur\AirwatchCommandLine\Commands\MAMAppsPlaystoreSearchCommand;
use PhPeteur\AirwatchCommandLine\Commands\MDMDevicesSearchCommand;
use PhPeteur\AirwatchCommandLine\Commands\MDMSmartGroupsSearchCommand;
use PhPeteur\AirwatchCommandLine\Commands\MDMProductsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemAdminsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MAMAppsGroupsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemGroupsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MAMAppsRemovalLogsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceAppsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceInformationsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceComplianceDetailsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMComplianceAttributesOGComplianceAttrSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDevicesAppStatusSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceEventLogSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceGPSSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDevicesBulkGPSSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDevicesBulkDeviceSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MAMAppsGroupSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMSmartGroupSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceSmartGroupsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceSecuritySearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceEnrolledDevicesCountSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceUserSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceProfilesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceNetworkSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemGroupSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemGroupChildrenSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemGroupAdminsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemGroupUsersSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemGroupRolesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceAdminAppsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDevicesBulkSettingsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceCertificatesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceNotesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDevicesExtensiveSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDevicesLiteSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDevicesSecurityInformationsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDevicesNetworkInformationsSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMCompliancePoliciesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceCustomAttributesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceCustomAttributesChangeReportSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemUsersEnrolledDevicesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemUserSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMProductFailedSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMProductInProgressSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMProductAssignedSearchCommand;
use PhPeteur\AirwatchCommandLine\Commands\MDMProductsReprocessActionCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMProductSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMSmartGroupDevicesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemUserDeleteCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemUserChangeOGCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceDeleteCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemUserUpdateCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemUsersBulkDeleteCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDEPProfilesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDEPDevicesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemGroupAPNSSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMProfilesSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemGroupAPNSUuidSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MDMDeviceCommandsCommand;
use \PhPeteur\AirwatchCommandLine\Commands\MAMAppsApplestoreSearchCommand;
use \PhPeteur\AirwatchCommandLine\Commands\SystemScimV2UsersCommand;
#//use \PhPeteur\Commands\MDMRelayServersSearchCommand;
//use \PhPeteur\AirwatchCommandLine\Commands\AirwatchProdAppsInstalled;


$configfile = __DIR__.'/config.yml';
if (!is_readable($configfile)) {
    die('Please copy config.yml.dist to config.yml'.PHP_EOL);
}
$justcfg = Yaml::parseFile($configfile);

$credentialsfile = __DIR__.'/credentials.yml';
if (!is_readable($credentialsfile)) {
    die('Please copy credentials.yml.dist to credentials.yml'.PHP_EOL);
}
$creds = Yaml::parseFile($credentialsfile);

$cfg = array_merge($justcfg, $creds);

//var_dump($cfg);
//exit;
$application = new Application("Airwatch command line tool");

$application->add(new SystemInfoCommand( $cfg ) );
$application->add(new SystemUsersSearchCommand( $cfg ) );
$application->add(new MAMAppsSearchCommand( $cfg ) );
$application->add(new MAMAppsPlaystoreSearchCommand( $cfg ) );
$application->add(new MDMDevicesSearchCommand( $cfg ) );
$application->add(new MDMSmartGroupsSearchCommand( $cfg ) );
$application->add(new MDMProductsSearchCommand( $cfg ) );
$application->add(new SystemAdminsSearchCommand( $cfg ));
$application->add(new MAMAppsGroupsSearchCommand( $cfg ));
$application->add(new SystemGroupsSearchCommand( $cfg ));
$application->add(new MAMAppsRemovalLogsSearchCommand( $cfg ));
$application->add(new MDMDeviceAppsSearchCommand( $cfg ));
$application->add(new MDMDeviceInformationsSearchCommand( $cfg ));
$application->add(new MDMDeviceComplianceDetailsSearchCommand( $cfg ));
$application->add(new MDMComplianceAttributesOGComplianceAttrSearchCommand( $cfg ));
$application->add(new MDMDevicesAppStatusSearchCommand( $cfg ));
$application->add(new MDMDeviceEventLogSearchCommand( $cfg ));
$application->add(new MDMDeviceGPSSearchCommand( $cfg ));
$application->add(new MDMDevicesBulkGPSSearchCommand( $cfg ));
$application->add(new MDMDevicesBulkDeviceSearchCommand( $cfg ));
$application->add(new MAMAppsGroupSearchCommand( $cfg ));
$application->add(new MDMSmartGroupSearchCommand( $cfg ));
$application->add(new MDMDeviceSmartGroupsSearchCommand( $cfg ));
$application->add(new MDMDeviceSecuritySearchCommand( $cfg ));
$application->add(new MDMDeviceEnrolledDevicesCountSearchCommand( $cfg ));
$application->add(new MDMDeviceUserSearchCommand( $cfg ));
$application->add(new MDMDeviceProfilesSearchCommand( $cfg ));
$application->add(new MDMDeviceNetworkSearchCommand( $cfg ));
$application->add(new SystemGroupSearchCommand( $cfg ));
$application->add(new SystemGroupChildrenSearchCommand( $cfg ));
$application->add(new SystemGroupAdminsSearchCommand( $cfg ));
$application->add(new SystemGroupUsersSearchCommand( $cfg ));
$application->add(new SystemGroupRolesSearchCommand( $cfg ));
$application->add(new MDMDeviceAdminAppsSearchCommand( $cfg ));
$application->add(new MDMDevicesBulkSettingsSearchCommand( $cfg ));
$application->add(new MDMDeviceCertificatesSearchCommand( $cfg ));
$application->add(new MDMDeviceNotesSearchCommand( $cfg ));
$application->add(new MDMDevicesExtensiveSearchCommand( $cfg ));
$application->add(new MDMDevicesLiteSearchCommand( $cfg ));
$application->add(new MDMDevicesSecurityInformationsSearchCommand( $cfg ));
$application->add(new MDMDevicesNetworkInformationsSearchCommand( $cfg ));
$application->add(new MDMCompliancePoliciesSearchCommand( $cfg ));
$application->add(new MDMDeviceCustomAttributesSearchCommand( $cfg ));
$application->add(new MDMDeviceCustomAttributesChangeReportSearchCommand( $cfg ));
$application->add(new SystemUsersEnrolledDevicesSearchCommand( $cfg ));
$application->add(new SystemUserSearchCommand( $cfg ));
$application->add(new MDMProductFailedSearchCommand( $cfg ));
$application->add(new MDMProductInProgressSearchCommand( $cfg ));
$application->add(new MDMProductAssignedSearchCommand( $cfg ));
$application->add(new MDMProductsReprocessActionCommand( $cfg ));
$application->add(new MDMProductSearchCommand( $cfg ));
$application->add(new MDMSmartGroupDevicesSearchCommand( $cfg ));
$application->add(new SystemUserDeleteCommand( $cfg ));
$application->add(new SystemUserChangeOGCommand( $cfg ));//!\ NOT WORKING
$application->add(new MDMDeviceDeleteCommand( $cfg ));
$application->add(new SystemUserUpdateCommand( $cfg ));
$application->add(new SystemUsersBulkDeleteCommand( $cfg ));
$application->add(new MDMDEPProfilesSearchCommand( $cfg ));
$application->add(new MDMDEPDevicesSearchCommand($cfg ));
$application->add(new SystemGroupAPNSSearchCommand($cfg ));
$application->add(new MDMProfilesSearchCommand( $cfg ));
$application->add(new SystemGroupAPNSUuidSearchCommand($cfg ));
$application->add(new MDMDeviceCommandsCommand( $cfg ));
$application->add(new MAMAppsApplestoreSearchCommand( $cfg ));
$application->add(new SystemScimV2UsersCommand( $cfg ));

//$application->add(new AirwatchProdAppsInstalled($cfg));
#//$application->add(new MDMRelayServersSearchCommand( $cfg ));

//MAMAppsPlaystoreSearchCommand
// ... register commands


$application->run();