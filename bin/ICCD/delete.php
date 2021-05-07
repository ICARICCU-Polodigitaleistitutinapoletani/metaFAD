<?php

set_time_limit (0);
require_once("../import_pinax.php");

$type = $argc > 1 ? $argv[1] : 'SchedaOA200';
$instituteKey = $argc > 2 ? $argv[2] : '*';

$model = $type.'.models.Model';

$it = pinax_ObjectFactory::createModelIterator($model)
    ->setOptions(array('type' => 'PUBLISHED_DRAFT'))
    ->where('instituteKey', $instituteKey);

echo "deleting $type $instituteKey";

$iccdProxy = __ObjectFactory::createObject('metafad.gestioneDati.boards.models.proxy.ICCDProxy');
foreach ($it as $ar) {
    echo 'deleting record id:'.$ar->getId().'</br>'.PHP_EOL;
    $iccdProxy->delete($ar->getId(), $model);
}
