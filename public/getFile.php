<?php
if (!defined('PNX_LOADED'))
{
    require_once(__DIR__ .'/../vendor/autoload.php');

    $application = new pinaxcms_core_application_Application('../application', '../vendor/icariccu/pinax-2/');
    $application->runStartup();
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : NULL;

pinaxcms_Pinaxcms::getMediaArchiveBridge()->serveMedia($id);


