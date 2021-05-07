<?php
class archivi_controllers_ajax_GetTree extends metafad_common_controllers_ajax_CommandAjax
{
    private $selectedId;
    protected $archiviProxy;
    private $iconClasses;

    public function execute($id = null, $getRoot = false)
    {
        $result = $this->checkPermissionForBackend('visible');
        if (is_array($result)) {
            return $result;
        }

        $this->iconClasses = (array) json_decode(__Config::get('metafad.archive.treeIcon'));

        $this->archiviProxy = __ObjectFactory::createObject('archivi.models.proxy.ArchiviProxy');

        $instituteKey = metafad_usersAndPermissions_Common::getInstituteKey();

        $this->selectedId = $id;
        $this->directOutput = true;
        $tree = array();
        if ($getRoot == 'true') {
            $trees = __ObjectFactory::createModelIterator('archivi.models.ComplessoArchivistico')
                ->setOptions(array('type' => 'PUBLISHED_DRAFT'))
                ->where('root', 'true')
                ->orderBy('document_id');

            if ($instituteKey != '*') {
                $trees->where('instituteKey', $instituteKey);
            }

            $rootId = $id ? $this->getRootId($id) : null;

            foreach ($trees as $t) {
                $allChildren = $rootId == $t->document_id;
                $tree[] = $this->loadTree($t->document_id, new stdClass(), $allChildren);
            }
        } else {
            if ($id) {
                $tree[] = $this->loadTree($id, new stdClass());
            } else {
                $node = new StdClass();
                $node->id = (int) $id;
                $node->active = true;
                $node->title = '(Non noto): id = ';
                $node->expanded = true;
                $node->children = array();

                $tree[] = $node;
            }
        }

        return $tree;
    }

    public function getRootId($id)
    {
        $ar = __ObjectFactory::createModel('archivi.models.Model');
        $ar->setType(null);

        $ar->load($id, 'PUBLISHED_DRAFT');
        if ($ar->parent && $ar->parent['id']) {
            return $this->getRootId($ar->parent['id']);
        } else {
            return $id;
        }
    }

    public function loadTree($id, $subTree, $allChildren = false)
    {
        $ar = __ObjectFactory::createModel('archivi.models.Model');
        $ar->setType(null);
        $ar->load($id, 'PUBLISHED_DRAFT');
        $tree = $this->createNodeFromAr($id, $ar);
        $tree->iconclass = ($ar->livelloDiDescrizione == 'documento-allegato') ? 'fa fa-paperclip' : $this->iconClasses[$ar->document_type];

        $options = array('fields' => 'id,document_type_t,livelloDiDescrizione_s,denominazione_s,cronologia_s,doc_store');
        $docs = $this->archiviProxy->getChildren($id, $options);

        foreach ($docs as $doc) {
            if ($doc->id == $subTree->id) {
                $child = $subTree;
            } else {
                $child = $this->createNodeFromDoc($doc, false);

                $options = array('fields' => 'id');
                $docs = $this->archiviProxy->getChildren($doc->id, $options);

                if (count($docs)) {
                    $child->folder = true;
                    $child->lazy = true;
                }

                if ($allChildren) {
                    return $this->loadTree($doc->id, $tree, $allChildren);
                }
            }

            $tree->children[] = $child;
        }

        $tree->folder = !empty($tree->children);

        if ($ar->parent && $ar->parent['id']) {
            return $this->loadTree($ar->parent['id'], $tree, $allChildren);
        }

        return $tree;
    }

    protected function createNodeFromDoc($doc, $expanded = true)
    {
        $id = $doc->id;
        $documentType = $doc->document_type_t[0];
        $livelloDiDescrizione = $doc->livelloDiDescrizione_s;

        $denominazione = $doc->denominazione_s;
        //$denominazione = truncateDenominazioneDoc($denominazione);

        $cron = $doc->cronologia_s;
        //$cron = truncateCronologiaDoc($cron);

        $title = $denominazione . ' || ' . $cron;
        $pageId = "archivi-" . strtolower(str_replace("archivi.models.", "", $documentType));
        $doc_store = json_decode($doc->doc_store[0]);

        $tree = new stdClass();
        $tree->id = (int) $id;
        $tree->active = $this->selectedId == $id;
        $tree->title = $title; //POLODEBUG-481 BE, Punto 2
        $tree->type = $livelloDiDescrizione;
        $tree->expanded = $expanded === true;
        $tree->children = array();
        $tree->canAdd = $livelloDiDescrizione != 'documento-allegato' && $livelloDiDescrizione != 'unita-documentaria';
        $tree->canEdit = $doc_store->hasPublishedVersion;
        $tree->canEditDraft = $doc_store->hasDraftVersion;
        $tree->canImport = $doc->document_type_t[0] == 'archivi.models.ComplessoArchivistico' ? true : false;
        $tree->canExport = $doc->document_type_t[0] == 'archivi.models.ComplessoArchivistico' ? true : false;

        $tree->iconclass = ($livelloDiDescrizione == 'documento-allegato') ? 'fa fa-paperclip' : $this->iconClasses[$documentType];

        $tree->routingEdit = __Routing::makeUrl('archiviMVC', array(
            'id' => $id,
            'pageId' => $pageId,
            'sectionType' => $livelloDiDescrizione,
            'action' => 'edit'
        ));

        $tree->routingEditDraft = __Routing::makeUrl('archiviMVC', array(
            'id' => $id,
            'pageId' => $pageId,
            'sectionType' => $livelloDiDescrizione,
            'action' => 'editDraft'
        ));

        return $tree;
    }

    protected function createNodeFromAr($id, $ar, $expanded = true)
    {
        $pageId = $ar->pageId ?: "archivi-" . strtolower(str_replace("archivi.models.", "", $ar->document_type));

        $tree = new stdClass();
        $tree->id = (int) $id;
        $tree->active = $this->selectedId == $id;

        $_denominazione = $ar->_denominazione;
        $denominazione = $ar->denominazione;
        if ($denominazione === '') {
            $denominazione = $ar->titoloAttribuito;
        }
        //$_denominazione = truncateDenominazioneAr($denominazione, $_denominazione);

        $cronologia =  $ar->cronologia;
        //$_denominazione = truncateCronologiaAr($cronologia, $_denominazione);

        $tree->title = implode(" || ", array_map("trim", array_slice(explode(" || ", $_denominazione), 1))); //POLODEBUG-481 BE, Punto 2
        $tree->type = $ar->livelloDiDescrizione;
        $tree->expanded = $expanded === true;
        $tree->children = array();
        $tree->canAdd = $ar->livelloDiDescrizione != 'documento-allegato' && $ar->livelloDiDescrizione != 'unita-documentaria';
        $tree->canEdit = $ar->hasPublishedVersion();
        $tree->canEditDraft = $ar->hasDraftVersion();
        $tree->canImport = $ar->document_type == 'archivi.models.ComplessoArchivistico' ? true : false;
        $tree->canExport = $ar->document_type == 'archivi.models.ComplessoArchivistico' ? true : false;

        $tree->iconclass = ($ar->livelloDiDescrizione == 'documento-allegato') ? 'fa fa-paperclip' : $this->iconClasses[$ar->document_type];

        $tree->routingEdit = __Routing::makeUrl('archiviMVC', array(
            'id' => $id,
            'pageId' => $pageId,
            'sectionType' => $ar->livelloDiDescrizione,
            'action' => 'edit'
        ));

        $tree->routingEditDraft = __Routing::makeUrl('archiviMVC', array(
            'id' => $id,
            'pageId' => $pageId,
            'sectionType' => $ar->livelloDiDescrizione,
            'action' => 'editDraft'
        ));

        return $tree;
    }
}
