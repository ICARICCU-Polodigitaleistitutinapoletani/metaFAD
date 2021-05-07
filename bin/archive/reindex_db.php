<?php

set_time_limit (0);
require_once("../import_pinax.php");

$models = array(
    'archivi.models.UnitaArchivistica',
    'archivi.models.UnitaDocumentaria',
    'metafad.sbn.modules.sbnunimarc.model.Model'
);

echo "re-indexing archive modules...".PHP_EOL;

foreach ($models as $model) {
    reindexModule($model);
}

echo "finished";


function reindexModule($model)
{
    $it = pinax_ObjectFactory::createModelIterator($model)
        ->setOptions(array('type' => 'PUBLISHED_DRAFT'));

    foreach ($it as $ar) {
        echo 're-indexing '.$model.' id:'.$ar->getId().PHP_EOL;
        $ar->reIndex(false);
        $data = $ar->getValuesAsArray();
        $ar->fulltext = pinaxcms_core_helpers_Fulltext::make($data, $ar);
        $ar->forceModified('fulltext');
        $ar->saveCurrentPublished();
    }
}
