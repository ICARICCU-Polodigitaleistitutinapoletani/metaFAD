<?php
require_once(__DIR__ .'/../vendor/autoload.php');

$application = new pinaxcms_core_application_AdminApplication('../application', '../vendor/icariccu/pinax-2/', '../application/');
__Paths::addClassSearchPath('../vendor/icariccu/metafad-sdk/src/');
$application->runSoft();
$application->executeCommand('pinaxcms.mediaArchive.controllers.Upload');

