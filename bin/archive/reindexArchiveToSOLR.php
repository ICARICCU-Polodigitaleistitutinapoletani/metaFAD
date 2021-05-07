<?php
set_time_limit(0);
require_once("../import_pinax.php");

$modelName = $argc > 1 ? $argv[1] : '*';
$mapFE = $argc > 2 ? $argv[2] : false;
$instituteKey = $argc > 3 ? $argv[3] : '*';
$deleteAll = $argc > 4 ? $argv[4] : false;

metafad_usersAndPermissions_Common::setInstituteKey($instituteKey);

$allModels = array(
    "ComplessoArchivistico",
    "UnitaArchivistica",
    "Toponimi",
    "Antroponimi",
    "FonteArchivistica",
    "SchedaBibliografica",
    "Enti",
    "SchedaStrumentoRicerca",
    "ProduttoreConservatore",
    "UnitaDocumentaria",
    "ProgettoDiDigitalizzazione"
);


/**
 * @var $arcProxy archivi_models_proxy_ArchiviProxy
 */
$arcProxy = pinax_ObjectFactory::createObject("archivi.models.proxy.ArchiviProxy");
$arcProxy->setQueueSize(1000);
$arcProxy->setRetryWithDraftOnInvalidate(true);

if($deleteAll)
{
    // cancella prima i vecchi record su SOLR
    $query = 'document_type_t:archivi.models.'.implode(' OR document_type_t:archivi.models.', $allModels);

    $evt = array(
        'type' => 'deleteAll',
        'data' => array(
            'option' => array(
                'query' => $query
            )
        )
    );

    $arcProxy->dispatchEvent($evt);
}

$models = $modelName !== "*" ? array($modelName) : $allModels;
$instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();

foreach ($models as $model){
    echo "Now executing for model $model.$eol";
    try{
        $it = pinax_ObjectFactory::createModelIterator("archivi.models.$model");

        if ($instituteKey != '*') {
            $it->where('instituteKey', $instituteKey);
        }

        foreach ($it as $ar){
            $data = $ar->getRawData();
            $data->__model = $data->document_type;
            $data->__id = $data->document_id;
            $data->instituteKey = $data->instituteKey ?: $instituteKey;
            try{
                $t = microtime(true) * 1000;
                $arcProxy->appendDocumentToData($data);
                $arcProxy->sendDataToSolrAndFE($data, $mapFE);
                $t1 = microtime(true) * 1000;
                echo round($t - $t1, 3) . "ms. Record {$data->_denominazione} (ID {$data->__id})";
                echo "Record (ID {$data->__id}) successfully reindexed on SOLR".PHP_EOL;
            } catch (Exception $ex) {
                @$arcProxy->commit();
                echo "During {$data->__model}.{$data->__id} refresh, there was some error: {$ex->getMessage()}$eol";
            }

        }
    } catch (Exception $ex) {
        @$arcProxy->commit();
        echo "During $model refresh, there was some error: {$ex->getMessage()}$eol";
    }
    @$arcProxy->commit();
}