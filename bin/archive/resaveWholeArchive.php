<?php
/**
 * TODO: refactor. Quasi identico a reindexArchiveToSolr.php...
 */

set_time_limit(0);
require_once("../import_pinax.php");

metafad_usersAndPermissions_Common::setInstituteKey("*");

$modelName = $argc > 1 ? $argv[1] : '*';
$mapFE = $argc > 2 ? $argv[2] : false;

$allModels = array(
    "ComplessoArchivistico",
    "UnitaArchivistica",
    "UnitaDocumentaria",
    "ProduttoreConservatore",
    "Toponimi",
    "Antroponimi",
    "FonteArchivistica",
    "SchedaBibliografica",
    "Enti",
    "SchedaStrumentoRicerca"
);


/**
 * @var $arcProxy archivi_models_proxy_ArchiviProxy
 */
$arcProxy = pinax_ObjectFactory::createObject("archivi.models.proxy.ArchiviProxy");
$arcProxy->setQueueSize(1000);
$arcProxy->setRetryWithDraftOnInvalidate(true);

$models = $modelName !== "*" ? array($modelName) : $allModels;

$instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();
foreach ($models as $model){
    $eol = "\r\n<br>\r\n";
    echo "Now executing for model $model.$eol";
    try{
        $it = pinax_ObjectFactory::createModelIterator("archivi.models.$model");
        foreach ($it as $ar){
            $data = $ar->getRawData();
            $data->__model = $data->document_type;
            $data->__id = $data->document_id;
            $data->instituteKey = $data->instituteKey ?: $instituteKey;
            try{
                $t = microtime(true) * 1000;
                $ret = $arcProxy->save($data);
                $t1 = microtime(true) * 1000;
                echo round($t - $t1, 3) . "ms. Record {$data->_denominazione} (ID {$data->__id})";
                if (key_exists("errors", $ret)){
                    echo " unsuccessfully resaved: ".json_encode($ret['errors'])."$eol";
                } else {
                    echo " successfully resaved$eol";
                }
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