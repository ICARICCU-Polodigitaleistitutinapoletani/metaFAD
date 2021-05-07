<?php
set_time_limit (0);
require_once("../import_pinax.php");

$onlyBE = $argc > 1 ? $argv[1] : true;

$iccdProxy = __ObjectFactory::createObject('metafad.gestioneDati.boards.models.proxy.ICCDProxy');

$models = array(
    'AUT200.models.Model',
    'AUT300.models.Model',
    'BIB200.models.Model',
    'BIB300.models.Model',
    'SchedaOA200.models.Model',
    'SchedaOA300.models.Model',
    'SchedaS200.models.Model',
    'SchedaS300.models.Model',
    'SchedaD200.models.Model',
    'SchedaD300.models.Model',
    'SchedaMI200.models.Model',
    'SchedaMI300.models.Model',
    'SchedaF300.models.Model',
    'SchedaVEAC301.models.Model',
    'SchedaNU300.models.Model',
    'SchedaRA300.models.Model',
    'SchedaBDM200.models.Model'
);

foreach ($models as $model) {
    $moduleBuilder = pinax_ObjectFactory::createObject('metafad.iccd.services.ModuleBuilder', $model);
    echo "reindex ".$model.PHP_EOL;
    $moduleBuilder->solrReindex($model);

    $iccdProxy->commitRemainingFE(__Config::get('metafad.solr.url'));
}
