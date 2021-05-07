<?php
class archivi_controllers_ajax_CreateNodeFromParent extends archivi_controllers_ajax_SaveDraft
{
    public function execute($datta)
    {
        // crea un nodo figlio di $parentId di tipo $typeId
        $parentId = __Request::get('parentId');
        $typeId = __Request::get('typeId');

        $archiviProxy = pinax_ObjectFactory::createObject('archivi.models.proxy.ArchiviProxy');

        $arParent = pinax_ObjectFactory::createModel('archivi.models.Model');
        $arParent->setType(null);
        $arParent->load($parentId, 'PUBLISHED_DRAFT');

        $ar = pinax_ObjectFactory::createModel('archivi.models.ArchiveType');
        $ar->find(array('archive_type_key' => $typeId));

        $data = array(
            '__id' => '',
            '__model' => $ar->archive_type_model,
            'pageId' => $ar->archive_type_pageId,
            'livelloDiDescrizione' => $typeId,
            'parent' => array(
                'id' => $parentId,
                'text' => $arParent->_denominazione
            ),
            '_denominazione' => $archiviProxy->extractTitleFromStdClass(new stdClass()),
            'tematismo' => $arParent->tematismo,
            'produttori' => $arParent->produttori
        );

        $rawData = $arParent->getRawData();
        if(property_exists($rawData, 'soggettoConservatore')) {
            $data['soggettoConservatore'] = $rawData->soggettoConservatore;
        }

        $result = parent::execute(json_encode($data));

        if ($result['errors']) {
            return $result;
        }

        $routing = __Routing::makeUrl('archiviMVC', array(
            'id' => $result['set']['__id'],
            'pageId' =>  $ar->archive_type_pageId,
            'sectionType' => $typeId,
            'action' => 'editDraft'
        ));

        return $routing;
    }
}
