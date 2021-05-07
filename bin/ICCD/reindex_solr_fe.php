<?php
set_time_limit (0);
require_once("../import_pinax.php");

$iccdProxy = __ObjectFactory::createObject('metafad.gestioneDati.boards.models.proxy.ICCDProxy');

$models = array(
    'AUT200.models.Model' => __Config::get('metafad.solr.iccdaut.url'),
    'AUT300.models.Model' => __Config::get('metafad.solr.iccdaut.url'),
    'BIB200.models.Model' => __Config::get('metafad.solr.iccdaut.url'),
    'BIB300.models.Model' => __Config::get('metafad.solr.iccdaut.url'),
    'SchedaOA300.models.Model' => __Config::get('metafad.solr.iccd.url'),
    'SchedaS300.models.Model' => __Config::get('metafad.solr.iccd.url'),
    'SchedaD300.models.Model' => __Config::get('metafad.solr.iccd.url'),
    'SchedaMI300.models.Model' => __Config::get('metafad.solr.iccd.url'),
    'SchedaF300.models.Model' => __Config::get('metafad.solr.iccd.url'),
    'SchedaVEAC301.models.Model' => __Config::get('metafad.solr.iccd.url'),
    'SchedaNU300.models.Model' => __Config::get('metafad.solr.iccd.url'),
    'SchedaRA300.models.Model' => __Config::get('metafad.solr.iccd.url'),
    'SchedaBDM200.models.Model' => __Config::get('metafad.solr.iccd.url'),
);

foreach ($models as $model => $url) {
    echo "reindex ".$model.PHP_EOL;
    $it = __ObjectFactory::createModelIterator($model);
    foreach($it as $ar)
    {
        $objData = $ar->getRawData();
        $objData->__id = $ar->document_id;
        $objData->__model = $model;
        $iccdProxy->saveFE($objData,true,false);
    }

    $iccdProxy->commitRemainingFE($url);
}