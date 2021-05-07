<?php
require_once('../vendor/autoload.php');

$application = new pinaxcms_core_application_AdminApplication('../application', '../vendor/icariccu/pinax-2/', '../application/');
$application->useXmlSiteMap = true;
$application->setLanguage('it');
__Paths::addClassSearchPath('../vendor/icariccu/metafad-sdk/src/');
$application->login();
$user = $application->getCurrentUser();
if (!$user->isLogged()) {
   header("HTTP/1.0 403 Forbidden");
   exit;
}
$application->runAjax();
