<?php
require_once(__DIR__ .'/vendor/autoload.php');
chdir(dirname($_SERVER['PHP_SELF']));

$application = new pinaxcms_core_application_AdminApplication('application', 'vendor/icariccu/pinax-2/', 'application/');
$application->useXmlSiteMap = true;
$application->setLanguage('it');
__Paths::addClassSearchPath('vendor/icariccu/metafad-sdk/src/');
$application->runSoft();
$application->executeCommand('metafad.jobmanager.controllers.JobManager');
