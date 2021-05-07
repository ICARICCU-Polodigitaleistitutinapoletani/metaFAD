<?php
/**
 *  ripristina i record che avevano il titolo attribuito, se questo non è presente nell'ultima versione
 *  input: l'id dell'utente
 */
set_time_limit (0);
require_once("../import_pinax.php");

$doRollback = $argc > 1 ? $argv[1] : false;

$userId = 36; // Rosa Granato

$it = pinax_ObjectFactory::createModelIterator('archivi.models.Model')
    ->where('document_detail_FK_user_id', $userId)
    ->allTypes();

foreach ($it as $ar) {
    $id = $ar->getId();
    $model = $ar->getType();
    $status = $ar->getStatus();

    if (strpos($model, 'archivi.models.') === false) {
        continue;
    }

    if (!$ar->keyInDataExists('denominazione') && !$ar->keyInDataExists('titoloAttribuito')) {
        $vid = checkRollback($id, $model);
        if ($vid) {
            echo "processing id:$id vid:$vid".PHP_EOL;
            if ($doRollback) {
                echo "rollback id:$id vid:$vid".PHP_EOL;
                rollback($model, $id, $vid, $status);
            }
        }
    }
}

function checkRollback($id, $model)
{
    $it = pinax_ObjectFactory::createModelIterator($model)
        ->where('document_detail_FK_document_id', $id)
        ->whereStatusIs('OLD')
        ->orderBy('document_detail_modificationDate', 'desc');

    if ($it->count() == 0) {
        return false;
    }

    foreach ($it as $ar) {
        if ($ar->keyInDataExists('titoloAttribuito')) {
            return $ar->getDetailId();
        }
    }

    return false;
}

function rollback($model, $id, $vid, $status)
{
    $it = pinax_ObjectFactory::createModelIterator($model);
    $it->where("document_detail_id", $vid)
        ->allStatuses();
    $ar = $it->first();

    $data = json_decode($ar->document_detail_object);
    if (!is_object($data)) {
        return false;
    }

    $data->__id = $id;
    $data->__model = $model;

    $archiviProxy= pinax_ObjectFactory::createObject('archivi.models.proxy.ArchiviProxy');

    metafad_usersAndPermissions_Common::setInstituteKey($data->instituteKey);

    if ($status == 'PUBLISHED') {
        return $archiviProxy->save($data);
    } else {
        return $archiviProxy->saveDraft($data);
    }
}