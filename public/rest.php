<?php
require_once(__DIR__ .'/../vendor/autoload.php');

$application = new pinax_rest_core_Application('../application', '../vendor/icariccu/pinax-2/');
$application->useXmlSiteMap = true;
$application->setLanguage('it');
__Paths::addClassSearchPath('../vendor/icariccu/metafad-sdk/src/');
$application->run();
