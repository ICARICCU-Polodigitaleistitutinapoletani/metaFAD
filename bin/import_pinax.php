<?php
require_once("../vendor/autoload.php");

$application = pinax_ObjectFactory::createObject('pinaxcms.core.application.AdminApplication', '../application', '../', '../application');
$application->useXmlSiteMap = true;
$application->setLanguage('it');
$application->runSoft();
