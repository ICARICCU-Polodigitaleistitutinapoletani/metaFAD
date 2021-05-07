<?php

set_time_limit (0);
require_once("../import_pinax.php");

$modules = pinax_Modules::getModules();

echo "Indexing iccd modules...</br>";

foreach ($modules as $module) {
    if ($module->isICCDModule) {
        reindexModule($module);
    }
}

echo "finished";


function reindexModule($module)
{
    echo 'indexing '.$module->name.'...</br>';
    flush();
    $it = pinax_ObjectFactory::createModelIterator($module->model)
        ->setOptions(array('type' => 'PUBLISHED_DRAFT'));
            
    foreach ($it as $ar) {
        $ar->reIndex(false);
        $data = $ar->getValuesAsArray();
        $ar->fulltext = pinaxcms_core_helpers_Fulltext::make($data, $ar);
        $ar->forceModified('fulltext');
        $ar->saveCurrentPublished();
    }
}
