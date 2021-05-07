<?php

use League\Flysystem\Config;

class archivi_controllers_ajax_ModifyTree extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($sourceNodeId, $targetNodeId, $hitMode)
    {
        
        $result = $this->checkPermissionForBackend('visible');
        if (is_array($result)) {
            return $result;
        }

        $this->directOutput = true;

        if (!$sourceNodeId || !$targetNodeId) {
            return array('status' => false);
        }

        if ($hitMode == 'after' || $hitMode == 'before') {
            $sourceAr = pinax_ObjectFactory::createModel('archivi.models.Model');
            $sourceAr->load($sourceNodeId, 'PUBLISHED_DRAFT');
            $targerAr = pinax_ObjectFactory::createModel('archivi.models.Model');
            $targerAr->load($targetNodeId);
            $this->updateSiblingId($sourceAr, $targerAr, $hitMode);
        } elseif ($hitMode == 'over') {
            $sourceAr = pinax_ObjectFactory::createModel('archivi.models.Model');
            $sourceAr->load($sourceNodeId, 'PUBLISHED_DRAFT');
            if(!$this->dropInsideNode($sourceAr, $targetNodeId)) {
                return array('status' => false);
            }
            
        }

        $routing = __Routing::makeUrl('archiviMVC', array(
            'id' => $sourceNodeId,
            'pageId' =>  $sourceAr->pageId,
            'sectionType' => $sourceAr->livelloDiDescrizione,
            'action' => 'editDraft'
        ));

        return array('status' => true, 'routing' => $routing);
    }

    function updateSiblingId($sourceAr, $targerAr, $hitMode)
    {
        $archiviProxy = __ObjectFactory::createObject('archivi.models.proxy.ArchiviProxy');
        $ordinamentoTarget = $targerAr->ordinamentoProvvisorio;
        $ordinamentoSource = $sourceAr->ordinamentoProvvisorio;
        if ($hitMode == 'before' && $ordinamentoSource < $ordinamentoTarget) {
            $hitMode = 'after';
            --$ordinamentoTarget;
        }
        if ($hitMode == 'after' && $ordinamentoSource > $ordinamentoTarget) {
            $hitMode = 'before';
            ++$ordinamentoTarget;
        }
        $sourceArType = __ObjectFactory::createModel($sourceAr->getType());
        $sourceArType->load($sourceAr->getId(), 'PUBLISHED_DRAFT');
        $sourceArType->ordinamentoProvvisorio = $ordinamentoTarget;
        $sourceArType->saveCurrentPublished();
        //TODO GESTIONE FE quando e se ci sarà
        $archiviProxy->reindexAr($sourceArType, false);
        $parentId = $sourceAr->parent['id'];
        $it = __ObjectFactory::createModelIterator('archivi.models.Model')->load('getParent', array(':parent' => $parentId, ':languageId' => __ObjectValues::get('pinax', 'languageId')));
        if ($hitMode == 'before') {
            foreach ($it as $ar) {
                if ($ar->ordinamentoProvvisorio >= $ordinamentoTarget && $ar->ordinamentoProvvisorio <= $ordinamentoSource && $ar->getId() != $sourceAr->getid()) {
                    $arType = __ObjectFactory::createModel($ar->getType());
                    $arType->load($ar->getId(), 'PUBLISHED_DRAFT');
                    $arType->ordinamentoProvvisorio = ++$arType->ordinamentoProvvisorio;
                    $arType->saveCurrentPublished();
                    //TODO GESTIONE FE quando e se ci sarà
                    $archiviProxy->reindexAr($arType, false);
                }
            }
        } elseif ($hitMode == 'after') {
            foreach ($it as $ar) {
                if ($ar->ordinamentoProvvisorio >= $ordinamentoSource && $ar->ordinamentoProvvisorio <= $ordinamentoTarget && $ar->getId() != $sourceAr->getid()) {
                    $arType = __ObjectFactory::createModel($ar->getType());
                    $arType->load($ar->getId(), 'PUBLISHED_DRAFT');
                    $arType->ordinamentoProvvisorio = --$arType->ordinamentoProvvisorio;
                    $arType->saveCurrentPublished();
                    //TODO GESTIONE FE quando e se ci sarà
                    $archiviProxy->reindexAr($arType, false);
                }
            }
        }
    }

    function dropInsideNode($arChild, $parentId)
    {
        //$arChild = pinax_ObjectFactory::createModel('archivi.models.Model');
        //$arChild->load($id, 'PUBLISHED_DRAFT');
        $data = $arChild->getRawData();
        $data->__id = $arChild->document_id;
        $data->__model = $arChild->document_type;

        $arParent = pinax_ObjectFactory::createModel('archivi.models.Model');
        $arParent->load($parentId, 'PUBLISHED_DRAFT');

        if ($arChild->livelloDiDescrizione == 'documento-allegato' || $arChild->livelloDiDescrizione == 'unita-documentaria') {
            $constraintOK = false;
        } else {
            // verifica se il nodo con id:$id e type:$arChild->livelloDiDescrizione
            // diventando figlio del nodo con id:$parentId e type:$arParent->livelloDiDescrizione
            // non violi vincoli di parentela, altrimenti restituisce errore
            $ar = pinax_ObjectFactory::createModelIterator('archivi.models.ArchiveType')
                ->load('checkLevel', array(':childType' => $arChild->livelloDiDescrizione, ':parentType' => $arParent->livelloDiDescrizione))->first();

            $constraintOK = $ar->constraintOK;
        }

        if (!$constraintOK) {
            return false;
        }

        $oldParentId = $data->parent->id;

        $data->parent = (object)array(
            'id' => $parentId,
            'text' => $arParent->_denominazione
        );

        $data->root = !$data->parent;

        $archiviProxy = __ObjectFactory::createObject('archivi.models.proxy.ArchiviProxy');
        $childrenNumber = count($archiviProxy->getChildren($parentId));
        $orderRemoved = $data->ordinamentoProvvisorio;
        $data->ordinamentoProvvisorio = ++$childrenNumber;

        if ($arChild->getStatus() == 'PUBLISHED') {
            $archiviProxy->save($data, true);
        } else {
            $archiviProxy->saveDraft($data, true);
        }

        // ricalcola gli ordinamenti provvisori dei nodi figli del padre da cui si era partiti (drag)
        // e verso quello in cui si arriva (drop)
        $archiviProxy->setOrdinamentoProvvisorioChildren($oldParentId, $orderRemoved);
        //$archiviProxy->setOrdinamentoProvvisorioChildren($parentId);
        return true;
    }
}
