<?php
set_time_limit(0);

class archivi_models_proxy_ArchiviProxy extends metafad_common_models_proxy_SolrQueueProxy
{
    protected static $sigle = null;
    protected $application = null;
    protected $profiling = false;
    protected $isImport = false;
    protected $countForQueue = 0;

    /**
     * Serve a riprovare il salvataggio per la bozza
     * @var bool
     */
    protected $retry = false;
    /**
     * Servono per forzare l'update della visibilità e del tematismo anche ai livelli sottostanti.
     * Messa a false per evitare chiamate ridondanti verso il basso.
     * @var bool
     */
    protected $updateVisibility = true;
    protected $updateTematismo = true;
    protected $updateProdCons = true;
    protected $updateRelations = true;
    protected $updateConsProdBidirectional = true;
    protected $updateRoot = true;
    protected $bench = array();
    /**
     * @var $arcFEHelper metafad_solr_helpers_ArchiveFEHelper
     */
    protected $arcFEHelper = null;
    /**
     * @var pinaxcms_contents_models_proxy_ModuleContentProxy
     */
    protected $proxy = null;
    private $stack = array();
    protected $hasImageHelper = null;

    /**
     * @return boolean
     */
    public function isRetry()
    {
        return $this->retry;
    }

    /**
     * Fluent setter
     * @param boolean $updateVisibility
     * @return $this
     */
    public function setUpdateVisibility($updateVisibility)
    {
        $this->updateVisibility = $updateVisibility;
        return $this;
    }

    public function setUpdateTematismo($updateTematismo)
    {
        $this->updateTematismo = $updateTematismo;
        return $this;
    }

    public function setUpdateProdCons($updateProdCons)
    {
        $this->updateProdCons = $updateProdCons;
        return $this;
    }

    public function setUpdateConsProdBidirectional($updateConsProdBidirectional)
    {
        $this->updateConsProdBidirectional = $updateConsProdBidirectional;
    }

    public function setUpdateRelations($updateRelations)
    {
        $this->updateRelations = $updateRelations;
    }

    public function setUpdateRoot($updateRoot)
    {
        $this->updateRoot = $updateRoot;
    }

    //Funzione per evitare lungaggini nei processi di importazione
    public function isImportProcess()
    {
        $this->isImport = true;
        $this->setUpdateConsProdBidirectional(false);
        $this->setUpdateTematismo(false);
        $this->setUpdateProdCons(false);
        $this->setUpdateRelations(false);
        $this->setUpdateRoot(false);
    }

    /**
     * @return bool
     */
    public function getUpdateVisibility()
    {
        return $this->updateVisibility;
    }

    public function getUpdateTematismo()
    {
        return $this->updateTematismo;
    }

    public function getUpdateProdCOns()
    {
        return $this->updateProdCons;
    }

    public function getUpdateRelations()
    {
        return $this->updateRelations;
    }

    /**
     * Fluent setter
     * @param $retry
     * @return $this
     */
    public function setRetryWithDraftOnInvalidate($retry)
    {
        $this->retry = $retry;
        return $this;
    }

    function __construct($profileSave = false)
    {
        $this->application = pinax_ObjectValues::get('pinax', 'application');
        $this->profiling = $profileSave === true;
        $this->initSigle();

        $this->arcFEHelper = $this->arcFEHelper ?: __ObjectFactory::createObject('metafad.solr.helpers.ArchiveFEHelper');
        $this->proxy = __ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
        $this->hasImageHelper = __ObjectFactory::createObject('metafad.solr.helpers.HasImageHelper');
    }

    public function initSigle()
    {
        if (!self::$sigle) {
            $prefix = "archivi.models.";
            self::$sigle = array(
                $prefix . "ComplessoArchivistico" => "CA",
                $prefix . "UnitaArchivistica" => "UA",
                $prefix . "UnitaDocumentaria" => "UD",
                $prefix . "FonteArchivistica" => "FA",
                $prefix . "ProduttoreConservatore" => "ENT",
                $prefix . "SchedaBibliografica" => "SB",
                $prefix . "SchedaStrumentoRicerca" => "SR",
                $prefix . "ProgettoDiDigitalizzazione" => "PD"
            );
        }
    }

    public function validate($data)
    {
        $this->buildProdConsField($data);

        $catExporter = __ObjectFactory::createObject('metafad.exporter.services.catexporter.CatExporter');
        $result = $catExporter->validate($data);
        if ($result === true) {
            $data->root = (!$data->parent) ? 'true' : 'false';
            $data->isValid = 1;
            $result = $this->proxy->saveContent($data, true);

            $this->appendDocumentToData($data);
            $this->sendDataToSolr($data, true);

            if ($result['__id']) {
                return array('set' => $result);
            } else {
                return array('errors' => $result);
            }
        } else {
            return array('errors' => array('Scheda non valida per CAT-SAN'));
        }
    }

    protected function createModel($id = null, $model)
    {
        $document = __ObjectFactory::createModel($model);
        if ($id) {
            $document->load($id);
        }
        return $document;
    }

    /**
     * Esegue il salvataggio (prima presente in archivi.controllers.ajax.Save)
     * @param $data StdClass
     * @param $invertRelation bool (Default TRUE) serve per chiamare il proxy di inversione delle relazioni
     * @return mixed Restituisce $result
     */
    public function save($data, $invertRelation = true)
    {
        $isDraft = false;
        $data->isValid = 0;
        $data->root = (!$data->parent) ? 'true' : 'false';

        return $this->saveProcedure($data, $invertRelation, $isDraft);
    }

    protected function handleVisibility($data)
    {
        $visibilityModels = array(
            "archivi.models.ComplessoArchivistico",
            "archivi.models.UnitaArchivistica",
            "archivi.models.UnitaDocumentaria"
        );

        if (!$data->__id || !$data->__model || !in_array($data->__model, $visibilityModels) || !$this->updateVisibility) { //TODO: L'ultimo serve ad evitare che si metta a fare il save di tutti i figli
            return;
        }

        $old = $this->createModel($data->__id, $data->__model);

        $visHelper = __ObjectFactory::createObject('metafad.common.helpers.VisibilityHelper');

        if (!$visHelper->compareVisibilities($old->visibility, $data->visibility)) {
            $jobFactory = __ObjectFactory::createObject('metafad.jobmanager.JobFactory');
            $jobFactory->createJob(
                'archivi_services_VisibilityService',
                array(
                    'id' => $data->__id,
                    'model' => $data->__model,
                    'visibility' => $data->visibility
                ),
                'Cambio visibilità',
                'BACKGROUND'
            );

            $data->visibility = $old->visibility;
        }
    }

    protected function propagateTematismo($data)
    {
        $tematismoModels = array(
            "archivi.models.ComplessoArchivistico",
            "archivi.models.UnitaArchivistica",
            "archivi.models.UnitaDocumentaria"
        );

        if (!$data->__id || !$data->__model || !in_array($data->__model, $tematismoModels) || !$this->updateTematismo) {
            return;
        }

        $old = $this->createModel($data->__id, $data->__model);
        if (!$old->identificativo) {
            return;
        }
        $temHelper = __ObjectFactory::createObject('metafad.common.helpers.TematismoHelper');
        if (!$temHelper->compareTematismi($old->tematismo, $data->tematismo)) {
            $jobFactory = __ObjectFactory::createObject('metafad.jobmanager.JobFactory');
            $jobFactory->createJob(
                'archivi_services_TematismoService',
                array(
                    'id' => $data->__id,
                    'model' => $data->__model,
                    'tematismo' => $data->tematismo
                ),
                'Cambio Tematismo',
                'BACKGROUND'
            );
        }
    }

    protected function propagateProdCons($data)
    {

        if (!$data->__id || !$data->__model || $data->__model != "archivi.models.ComplessoArchivistico" || !$this->updateProdCons) {
            return;
        }

        $old = $this->createModel($data->__id, $data->__model);
        if (!$old->identificativo) {
            return;
        }
        $prodConsHelper = __ObjectFactory::createObject('archivi.helpers.ProdConsHelper');
        if (
            !$prodConsHelper->compareProduttori($old->produttori, $data->produttori)
            || !$prodConsHelper->compareConservatore($old->soggettoConservatore, $data->soggettoConservatore)
        ) {
            $jobFactory = __ObjectFactory::createObject('metafad.jobmanager.JobFactory');
            $jobFactory->createJob(
                'archivi_services_ProdConsService',
                array(
                    'id' => $data->__id,
                    'model' => $data->__model,
                    'root' => $data->root,
                    'produttori' => $data->produttori,
                    'conservatore' => $data->soggettoConservatore
                ),
                'Cambio Produttori/Conservatori',
                'BACKGROUND'
            );
        }
    }

    protected function updateRelations($data)
    {
        if (!$data->__id || !$data->__model || !$this->updateRelations) {
            return;
        }
        $update = false;
        $rr = __Paths::get('ROOT');
        $ff = getcwd();
        $mapping = json_decode(file_get_contents(__Config::get('pathToAdmin') . 'classes/userModules/archivi/services/json/mappingUpdateRelations.json'));
        $model = $data->__model;
        $old = $this->createModel($data->__id, $model);
        $field = $mapping->$model->field;
        if (!$field) {
            return;
        }
        $oldData = $old->getRawData();
        foreach ($field as $f) {
            if (strpos($f, '.')) {
                $arr = explode('.', $f);
                if (!is_array(@$data->$arr[0]) || !is_array(@$oldData->$arr[0])) {
                    return;
                }
                $denData = $data->$arr[0];
                $denOldData = $oldData->$arr[0];
                if (@$denData[0]->$arr[1] != @$denOldData[0]->$arr[1]) {
                    $update = true;
                    break;
                }
            } elseif (@$data->$f != @$oldData->$f) {
                $update = true;
                break;
            }
        }
        if (!$update) {
            return;
        }
        if (!$mapping->$model->voceIndice) {
            $value = $this->extractTitleFromStdClass($data);
        } else {
            $value = $data->intestazione;
        }
        $jobFactory = __ObjectFactory::createObject('metafad.jobmanager.JobFactory');
        $jobFactory->createJob(
            'archivi_services_UpdateRelService',
            array(
                'instituteKey' => metafad_usersAndPermissions_Common::getInstituteKey(),
                'target' => $mapping->$model->target,
                'id' => $data->__id,
                'value' => $value
            ),
            'Cambio Denominazione schede collegate',
            'BACKGROUND'
        );
    }

    public function getRootAr($id)
    {
        $ar = __ObjectFactory::createModel('archivi.models.Model');
        $ar->load($id, 'PUBLISHED_DRAFT');

        if ($ar->root === 'true') {
            return $ar;
        } else {
            return $this->getRootAr($ar->parent['id']);
        }
    }

    public function getChildren($id, $options = null)
    {
        if (is_null($options)) {
            $options = array(
                'sort' => 'ordinamentoProvvisorio_i asc',
                'fields' => 'all'
            );
        }

        if (!isset($options['sort'])) {
            $options['sort'] = 'ordinamentoProvvisorio_i asc';
        }

        $query = array(
            'q=parent_i:' . $id,
            'sort=' . urlencode($options['sort']),
            'wt=json',
            'rows=2147483647'
        );

        if ($options['fields'] != 'all') {
            $query[] = 'fl=' . $options['fields'];
        }

        if (__Config::get('DEBUG')) {
            $query[] = 'indent=true';
        }

        $url = __Config::get('metafad.solr.url') . 'select?' . implode('&', $query);
        $content = json_decode(file_get_contents($url));

        return $content->response->docs;
    }

    // restituisce true se il nodo $id ha figli UA/UD, false altrimenti
    public function hasUnitChildren($id)
    {
        $q = array(
            'parent_i:' . $id,
            '(document_type_t:"archivi.models.UnitaArchivistica" OR document_type_t:"archivi.models.UnitaDocumentaria")'
        );

        $query = array(
            'q=' . urlencode(implode(' AND ', $q)),
            'fl=id',
            'wt=json',
        );

        if (__Config::get('DEBUG')) {
            $query[] = 'indent=true';
        }

        $url = __Config::get('metafad.solr.url') . 'select?' . implode('&', $query);
        $content = json_decode(file_get_contents($url));

        return $content->response->numFound > 0;
    }

    public function getNodesToReorder($id, $type)
    {
        $q = array(
            'parent_i:' . $id,
            '(document_type_t:"archivi.models.UnitaArchivistica" OR document_type_t:"archivi.models.UnitaDocumentaria")'
        );

        if ($type == 'data-discendente') {
            $sort = 'estremoRemoto_s desc';
        } elseif ($type == 'data-ascendente') {
            $sort = 'estremoRemoto_s asc';
        } elseif ($type == 'segnaturaAttuale') {
            $sort = 'segnaturaAttuale_s asc';
        } elseif ($type == 'segnaturaPrecedente') {
            $sort = 'segnaturaPrecedente_s asc';
        } elseif ($type == 'codiceDiClassificazione') {
            $sort = 'codiceDiClassificazione_s asc';
        }

        $query = array(
            'q=' . urlencode(implode(' AND ', $q)),
            'sort=' . urlencode($sort),
            'wt=json',
            'rows=2147483647'
        );

        if (__Config::get('DEBUG')) {
            $query[] = 'indent=true';
        }

        $url = __Config::get('metafad.solr.url') . 'select?' . implode('&', $query);
        $content = json_decode(file_get_contents($url));

        return $content->response->docs;
    }

    public function reorderNode($id, $model, $ordinamentoProvvisorio)
    {
        $ar = __ObjectFactory::createModel($model);
        $ar->load($id, 'PUBLISHED_DRAFT');
        $ar->ordinamentoProvvisorio = $ordinamentoProvvisorio;
        $ar->saveCurrentPublished();
        $this->reindexAr($ar);
    }

    /**
     * setta il numero di ordinamento provvisorio delle UA/UD figlie di primo livello del record con id $id 
     */
    public function setOrdinamentoProvvisorioChildren($id, $orderRemoved)
    {
        $it = __ObjectFactory::createModelIterator('archivi.models.Model')
            ->load('getParent', array(':parent' => $id, ':languageId' => __ObjectValues::get('pinax', 'languageId')));

        foreach ($it as $ar) {
            $arOrder = $ar->ordinamentoProvvisorio;
            $arId = $ar->getId();
            if ($arOrder > $orderRemoved) {
                $type = $ar->getType();
                $arType = __ObjectFactory::createModel($type);
                $arType->load($arId, 'PUBLISHED_DRAFT');
                $arType->ordinamentoProvvisorio = --$arType->ordinamentoProvvisorio;
                $arType->saveCurrentPublished();
                $this->reindexAr($arType);
            }
        }
    }

    /**
     * setta il numero di ordinamento provvisorio di tutti i livelli figli del record con id $id 
     */
    public function setOrdinamentoProvvisorioAllChildren($id)
    {
        $options = array('sort' => 'id asc');
        $docs = $this->getChildren($id, $options);

        $i = 1;

        foreach ($docs as $doc) {
            $this->setOrdinamentoProvvisorioAllChildren($doc->id);

            $ar = __ObjectFactory::createModel($doc->document_type_t[0]);
            $ar->load($doc->id, 'PUBLISHED_DRAFT');
            if ($ar->getType() == 'archivi.models.UnitaArchivistica' || $ar->getType() == 'archivi.models.UnitaDocumentaria') {
                $ar->ordinamentoProvvisorio = $i++;
            } else {
                $ar->ordinamentoProvvisorio = $doc->id;
            }
            $ar->saveCurrentPublished();
            $this->reindexAr($ar);
        }
    }

    /**
     * setta il numero di ordinamento provvisorio della UA/UD in $data
     */
    public function setOrdinamentoProvvisorio($data)
    {
        if ($data->root !== 'true') {
            $docs = $this->getChildren($data->parent->id);
            $ordinamentoProvvisorio = count($docs) + 1;
        } else {
            $ordinamentoProvvisorio = $data->__id;
        }

        $ar = __ObjectFactory::createModel($data->__model);
        $ar->load($data->__id, 'PUBLISHED_DRAFT');
        $data->ordinamentoProvvisorio = $ordinamentoProvvisorio;
        $ar->ordinamentoProvvisorio = $ordinamentoProvvisorio;
        $ar->saveCurrentPublished();
        $this->reindexAr($ar);
    }

    public function queueReorderGlobal($id)
    {
        //TODO ora commentata perché il job crea problemi
        //va analizzato in maniera approfondita il funzionamento

        // $jobFactory = pinax_ObjectFactory::createObject('metafad.jobmanager.JobFactory');
        // $jobFactory->createJob(
        //     'archivi_services_ReorderService',
        //     array(
        //         'id' => $id,
        //     ),
        //     'Cambio ordinamento globale',
        //     'SYSTEM'
        // );
    }

    /**
     * setta il numero di ordinamento globale delle UA/UD
     */
    public function setOrdinamentoGlobale($ar, $ordinamentoGlobale, $options = null)
    {
        if ($ar->getType() == 'archivi.models.UnitaArchivistica' || $ar->getType() == 'archivi.models.UnitaDocumentaria') {
            $ar->ordinamentoGlobale = ++$ordinamentoGlobale;
            $ar->saveCurrentPublished();
            $this->reindexAr($ar);
        }

        $docs = $this->getChildren($ar->getId(), $options);

        foreach ($docs as $doc) {
            $arChild = __ObjectFactory::createModel($doc->document_type_t[0]);
            $arChild->load($doc->id, 'PUBLISHED_DRAFT');
            $ordinamentoGlobale = $this->setOrdinamentoGlobale($arChild, $ordinamentoGlobale, $options);
        }

        return $ordinamentoGlobale;
    }

    /**
     * @param $data
     * @param $invertRelation
     * @param $isDraft
     * @return array
     */
    protected function saveProcedure($data, $invertRelation, $isDraft)
    {
        $isNew = !$data->__id;

        if ($this->updateRoot && !$isNew && $data->root == "false" && !$isDraft) {
            $this->benchStart('saveRoot');
            $rootAr = $this->getRootAr($data->__id);
            $rootData = $rootAr->getRawData();
            $rootData->__id = $rootAr->getId();
            $rootData->__model = 'archivi.models.ComplessoArchivistico';
            $rootData->pageId = 'archivi-complessoarchivistico';
            $this->saveObject($rootData, $isDraft === true);
            $this->appendDocumentToData($rootData);
            $this->sendDataToSolr($rootData, true);
            $this->benchEnd('saveRoot');
        }

        if ($this->updateConsProdBidirectional) {
            if ($data->__model == "archivi.models.ComplessoArchivistico" || $data->__model == "archivi.models.ProduttoreConservatore") {
                $old = $this->createModel($data->__id, $data->__model);
            }
        }

        $this->handleVisibility($data);
        $this->propagateTematismo($data);
        //$this->propagateProdCons($data);
        $this->updateRelations($data);

        $this->benchStart('saveObject');
        $this->buildProdConsField($data);
        $res = $this->saveObject($data, $isDraft === true);
        $this->benchEnd('saveObject');

        if (key_exists("errors", $res) && $this->isRetry()) {
            $isDraft = true;
            $this->benchStart('saveObject');
            $res = $this->saveObject($data, $isDraft === true);
            $this->benchEnd('saveObject');
        }

        $this->benchStart('updateId');
        $res = $this->updateIdentificativi($data, $res, $isDraft === true);
        $this->benchEnd('updateId');

        if ($this->updateConsProdBidirectional) {
            $this->benchStart('updateConsProdBidirectional');
            $this->updateConsProdBidirectional($data, $isNew, $old);
            $this->benchEnd('updateConsProdBidirectional');
        }

        $this->appendDocumentToData($data);

        if ($invertRelation) {
            //$invRelProxy = new archivi_models_proxy_InverseRelationProxy();
            //$invRelProxy->insertInverseRelation($data);
        }

        $this->benchStart('solr');
        // mapFE è false se il record è nuovo (__id null), true altrimenti
        //$mapFE = !$isNew && !$isDraft;

        if ($isNew) {
            $this->setOrdinamentoProvvisorio($data);
        }

        $mapFE = !$isDraft;
        $this->sendDataToSolr($data, $mapFE);
        $this->benchEnd('solr');

        $this->queueReorderGlobal($data->__id);

        if ($this->profiling === true) {
            foreach ($this->bench as $k => $v) {
                echo "'$k' took {$v['time']}ms\n<br>\n";
            }
        }
        $this->bench = array();

        return $res;
    }

    public function benchStart($str)
    {
        if ($this->profiling === true) {
            $this->bench[$str]['start'] = microtime(true);
        }
    }

    // POLODEBUG-219 - ereditarietà del primo soggetto produttore
    // protected function inheritProducer($id, $produttori)
    // {
    //     $it = pinax_ObjectFactory::createModelIterator('archivi.models.ComplessoArchivistico')
    //         ->where('parent', $id);

    //     foreach ($it as $ar) {
    //         if (!$ar->produttori) {
    //             $childProduttori = array();
    //         } else {
    //             $childProduttori = $ar->produttori;
    //         }
    //         $childProduttori[0] = $produttori[0];
    //         $ar->produttori = $childProduttori;
    //         $ar->saveCurrentPublished();

    //         // ricorsione sulle CA figlie
    //         $this->inheritProducer($ar->getId(), $produttori);
    //     }
    // }

    /**
     * @param $data
     * @param $isDraft
     * @return array
     */
    protected function saveObject($data, $isDraft = false)
    {
        $voceIndice = array(
            "archivi.models.antroponimi",
            "archivi.models.toponimi",
            "archivi.models.enti"
        );

        if ($data->__id && !in_array(strtolower($data->__model), $voceIndice)) {
            $data->identificativo = $data->acronimoSistema . " " . self::$sigle[$data->__model] . " " . $data->__id;
            $data->codiceIdentificativoSistema = $data->__id;

            // POLODEBUG-219 - ereditarietà del primo soggetto produttore
            // if (!empty($data->produttori[0])) {
            //     $this->inheritProducer($data->__id, $data->produttori);
            // }
        }

        $data->_denominazione = $this->extractTitleFromStdClass($data);
        $data->instituteKey = $data->instituteKey ?: metafad_usersAndPermissions_Common::getInstituteKey();

        $result = array();
        if (in_array(strtolower($data->__model), $voceIndice)) {
            if (!$this->isImport) {
                $ar = __ObjectFactory::createModelIterator($data->__model)
                    ->setOptions(array('type' => 'PUBLISHED_DRAFT'))
                    ->where("intestazione", $data->intestazione)
                    ->first();
            } else {
                $ar = __ObjectFactory::createModelIterator($data->__model)
                    ->where("intestazione", $data->intestazione)
                    ->first();
            }
            if ($ar) {
                $result['__id'] = $ar->getId();
            } else {
                $result = $this->proxy->saveContent($data, __Config::get('pinaxcms.content.history'), $isDraft === true);
            }
        } else {
            if ($data->__id) {
                $ar = __ObjectFactory::createModel('archivi.models.Model');
                $ar->load($data->__id, $isDraft ? 'DRAFT' : 'PUBLISHED');
                if ($data->denominazione != $ar->denominazione) {

                    $it = pinax_ObjectFactory::createModelIterator('archivi.models.Model')
                        ->load('getParent', array(':parent' => $data->__id, ':languageId' => pinax_ObjectValues::get('pinax', 'languageId')));

                    // se ci sono discendenti
                    if ($it->count() > 0) {
                        // crea un job per aggiornare i nodi discendenti perchè la denominazion è cambiata
                        $jobFactory = pinax_ObjectFactory::createObject('metafad.jobmanager.JobFactory');
                        $jobFactory->createJob(
                            'archivi.services.DenominazioneService',
                            array(
                                'id' => [$data->__id]
                            ),
                            'Cambio denominazione',
                            'BACKGROUND'
                        );
                    }
                }
            }

            $result = $this->proxy->saveContent($data, __Config::get('pinaxcms.content.history'), $isDraft === true);
        }

        if ($result['__id']) {
            return array('set' => $result);
        } else {
            return array('errors' => $result);
        }
    }

    public function benchEnd($str)
    {
        if ($this->profiling === true) {
            $x = $this->bench[$str]['start'];
            $this->bench[$str]['time'] = $x ? round((microtime(true) - $x) * 1000, 3) : 0;
        }
    }

    /**
     * @param $data
     * @param $res
     * @param $isDraft
     * @return array
     */
    protected function updateIdentificativi($data, $res, $isDraft)
    {
        $data->__id = $res['set']['__id'];

        if (!$data->identificativo || !$data->codiceIdentificativoSistema) {
            $data->identificativo = $data->acronimoSistema . " " . self::$sigle[$data->__model] . " " . $data->__id;
            $data->codiceIdentificativoSistema = $data->__id;
            $res = $this->saveObject($data, $isDraft === true);
        }

        return $res;
    }

    public function getParentsArray($parent, &$parentsPath)
    {
        if ($parent->id) {
            $ar = __ObjectFactory::createModel('archivi.models.Model');
            $ar->load($parent->id, 'PUBLISHED_DRAFT');
            $this->getParentsArray((object) $ar->parent, $parentsPath);
            $parentsPath[$ar->getId()] = $ar->denominazione;
        }
    }

    /**
     * @param $data stdClass
     * @param bool $mapFE
     */
    public function sendDataToSolr($data, $mapFE = false, $onlyFE = false)
    {
        // presenza del digitale o meno
        $data->_hasDigital = $this->hasImageHelper->hasImage($data, 'archive') ? 1 : 0;

        if ($data->parent) {
            $parentsPath = array();
            $this->getParentsArray($data->parent, $parentsPath);
            $data->_parentsIds = array_keys($parentsPath);
            $data->_parents = array_values($parentsPath);
            $data->_complessoAppartenenza = $data->_parents[0];
        }

        if (!$onlyFE)
            parent::sendDataToSolr($data);

        if ($mapFE && __Config::get('metafad.be.hasFE') == 'true') {
            $options = $this->getSolrOption();
            $this->arcFEHelper->mappingFE($data, $options['queue'] ? 'queue' : 'commit');
        }
    }

    public function reindexDescendants($id, $mapFE = false)
    {
        $this->setQueueSize(1000);
        foreach ($id as $i) {
            $it = pinax_ObjectFactory::createModelIterator('archivi.models.Model')
                ->load('getParent', array(':parent' => $i, ':languageId' => pinax_ObjectValues::get('pinax', 'languageId')));

            foreach ($it as $ar) {
                $this->countForQueue++;
                $this->reindexArQueue($ar, $mapFE);
                $this->reindexDescendants([$ar->getId()], $mapFE);
            }
        }
    }

    public function reindexAr($ar, $mapFE = false)
    {
        $data = $ar->getRawData();
        $data->__model = $data->document_type;
        $data->__id = $data->document_id;
        $data->instituteKey = $ar->instituteKey;
        $this->appendDocumentToData($data);
        $this->sendDataToSolr($data, $mapFE);
    }

    public function reindexArQueue($ar, $mapFE = false)
    {
        $data = $ar->getRawData();
        $data->__model = $data->document_type;
        $data->__id = $data->document_id;
        $data->instituteKey = $ar->instituteKey;
        $this->appendDocumentToData($data);
        if (!$mapFE) {
            $this->sendDataToSolr($data);
        } else {
            $this->sendDataToSolr($data, true, true);
        }
        if ($this->countForQueue % 1000 == 0) {
            if (!$mapFE) {
                @$this->commit();
            } else {
                @$this->commitFE(__Config::get('metafad.solr.archive.url'));
            }
        }
    }

    /**
     * Esegue quel che avrebbe eseguito la archivi_controllers_ajax_SaveDraft::execute()
     * @param $data stdClass
     * @param bool $invertRelation (Default TRUE) serve per chiamare il proxy di inversione delle relazioni
     * @return array|null
     */
    public function saveDraft($data, $invertRelation = true)
    {
        $isDraft = true;
        $data->isValid = 0;
        $data->root = (!$data->parent) ? 'true' : 'false';

        return $this->saveProcedure($data, $invertRelation, $isDraft);
    }

    public function delete($id, $recurse = false, $control = false, $feOnly = false, $massive = false)
    {
        $this->stack[] = $id;
        $ret = array($id);
        $it =
            $recurse ?
            pinax_ObjectFactory::createModelIterator('archivi.models.Model')
            ->load('getParent', array(':parent' => $id, ':languageId' => pinax_ObjectValues::get('pinax', 'languageId')))
            :
            array();

        foreach ($it as $ar) {
            if (!in_array($ar->getId(), $this->stack)) {
                $ret = array_merge($ret, $this->delete($ar->getId(), $recurse, false, $feOnly));
            }
        }

        if (count($this->stack) == 1) {
            $ar = __ObjectFactory::createModel('archivi.models.Model');
            $ar->load($id, 'PUBLISHED_DRAFT');
            $type = $ar->getType();
            $orderNumber = $ar->ordinamentoProvvisorio;
            if ($ar->parent) {
                $parentId = $ar->parent['id'];
            }
            if (!$this->isImport) {
                if ($type == 'archivi.models.ComplessoArchivistico' || $type = 'archivi.models.ProduttoreConservatore') {
                    $deleteHelper = __ObjectFactory::createObject('archivi.helpers.DeleteLinkedHelper');
                    $deleteHelper->deleteLinked($ar, $type, $id);
                }
            }
        }

        $this->deleteItem($id, $control, $feOnly);

        array_pop($this->stack);

        if (empty($this->stack) && $parentId && !$massive & !$feOnly) {
            //TODO rivedere
            //Il riordinamento globale in queste funzioni crea dei 
            //disallineamenti evidenti a livello di dati

            $this->setOrdinamentoProvvisorioChildren($parentId, $orderNumber);

            //$this->queueReorderGlobal($parentId);

        }

        return $ret;
    }

    private function deleteItem($id, $control = false, $feOnly)
    {
        if ($control === true && $this->archiviControl($id)) {
            return false;
        } else {
            if (!$feOnly) {
                $evt = array('type' => 'deleteRecord', 'data' => $id);
                $this->dispatchEvent($evt);
            }
            $evt2 = array('type' => 'deleteRecord', 'data' => array('id' => $id, 'option' => array('url' => __Config::get('metafad.solr.metaindice.url'))));
            $this->dispatchEvent($evt2);

            $evt3 = array('type' => 'deleteRecord', 'data' => array('id' => $id, 'option' => array('url' => __Config::get('metafad.solr.archive.url'))));
            $this->dispatchEvent($evt3);

            $evt4 = array('type' => 'deleteRecord', 'data' => array('id' => $id, 'option' => array('url' => __Config::get('metafad.solr.metaindiceaut.url'))));
            $this->dispatchEvent($evt4);

            $evt5 = array('type' => 'deleteRecord', 'data' => array('id' => $id, 'option' => array('url' => __Config::get('metafad.solr.archiveaut.url'))));
            $this->dispatchEvent($evt5);

            if (!$feOnly) {
                $this->proxy->delete($id, 'archivi.models.Model');
            }

            return true;
        }
    }

    public function archiviControl($archiviId)
    {
        $it = __ObjectFactory::createModelIterator('archivi.models.Model');

        $found = false;
        foreach ($it as $ar) {
            $parentId = $ar->getRawData()->parentId;
            if ($parentId && $parentId == $archiviId) {
                $found = true;
            }
        }

        return $found;
    }

    public function getLinkObjectsByExternalId($extid, $model = 'archivi.models.Model', $first = true)
    {
        if (!$extid) {
            return array();
        }
        $it = __ObjectFactory::createModelIterator($model)
            ->load('getByIndexedText', array(':textName' => "externalID", ":textVal" => $extid, ':languageId' => pinax_ObjectValues::get('pinax', 'languageId')));

        $ret = array();
        /**
         * @var $ar pinax_dataAccessDoctrine_ActiveRecord
         */
        foreach ($it as $ar) {
            $ret[] = (object) array("id" => $ar->getId(), "text" => $ar->_denominazione);
            if ($first) break;
        }

        return $ret;
    }

    /**
     * Tenta un accesso alla proprietà desiderata. L'array indica una cosa come "obj->ar[0]->ar[1]->...->ar[n]"
     * @param $obj
     * @param $properties
     * @param null $default
     * @return mixed|null
     */
    private function softAccess($obj, $properties, $default = null)
    {
        $obj = json_decode(json_encode($obj));
        if (!is_array($properties) || !is_object($obj)) {
            return null;
        }

        $i = -1;
        $n = count($properties);
        $got = $obj;
        while ($got !== null && ++$i < $n) {
            $propname = $properties[$i];
            if (!is_string($propname) || (!is_object($got) && !is_array($got))) {
                $got = null;
            } else if (is_object($got) && property_exists($got, $propname)) {
                $got = $got->$propname;
            } else if (is_array($got) && count($got) && is_object($got[0]) && property_exists($got[0], $propname)) {
                //TODO uno solo?
                $got = $got[0]->$propname;
            } else {
                $got = null;
            }
        }

        return $got === null ? $default : $got;
    }

    public function getLinkObjectById($id, $model = 'archivi.models.Model')
    {
        $ar = __ObjectFactory::createModelIterator($model)->where("document_id", $id)->first();

        if ($ar) {
            return (object) array("id" => $id, "text" => $ar->_denominazione);
        } else {
            return null;
        }
    }

    /**
     * Viene restituito un link (array con id e text) della mini-authority trovata/salvata
     * @param $data
     * @param $model
     * @param string $firstField
     * @param string $secondField
     * @return mixed
     */
    public function addOrGetMiniAuthorityLink($data, $model, $firstField = 'intestazione', $secondField = 'externalID')
    {
        $model = $model ?: $data->__model;
        $firstField = $firstField ?: 'intestazione';
        $secondField = $secondField ?: 'externalID';
        $it = __ObjectFactory::createModelIterator($model);
        $result = array();

        if ($data->$firstField) {
            $it->load('getByIndexedText', array(':textName' => $firstField, ":textVal" => $data->$firstField, ':languageId' => pinax_ObjectValues::get('pinax', 'languageId')));
        }

        if ((!$data->$firstField || $it->count() == 0) && $data->$secondField) {
            unset($it);
            $it = __ObjectFactory::createModelIterator($model)
                ->load('getByIndexedText', array(':textName' => $secondField, ":textVal" => $data->$secondField, ':languageId' => pinax_ObjectValues::get('pinax', 'languageId')));
        }

        foreach ($it as $ar) {
            $result[] = array(
                'id' => $ar->getId(),
                'text' => $ar->intestazione
            );
            break;
        }

        if (count($result) == 0) {
            $data->__model = $model;
            $data->externalID = $data->externalID ?: "";
            $ret = $this->save($data);
            $result = array(array('id' => $ret['set']['__id'], 'text' => $data->intestazione));
        }

        return current($result);
    }

    /**
     * @param $data
     * @return mixed|null|string
     */
    private function extractCronologiaFromStdClass($data)
    {
        $chronos = "";
        $attempts = array(
            array("cronologia", "estremoCronologicoTestuale"),
            array("cronologiaEnte", "estremoCronologicoTestuale"),
            array("cronologiaPersona", "estremoCronologicoTestuale"),
            array("cronologiaFamiglia", "estremoCronologicoTestuale"),
            array("cronologiaRedazione", "estremoCronologicoTestuale"),
            array("annoDiEdizione")
        );

        foreach ($attempts as $attempt) {
            $chronos = $this->softAccess($data, $attempt, $chronos) ?: $chronos;
        }

        return $chronos;
    }

    /**
     * @param $data
     * @return string
     */
    private function extractDenominazioneFromStdClass($data)
    {
        $name = "";
        $surname = "";
        $attempts = array(
            array("denominazione"),
            array("titoloAttribuito"),
            array("titolo"),
            array("ente_famiglia_denominazione", "entitaDenominazione"),
            array("persona_denominazione", "entitaDenominazione"),
            array("titoloNormalizzato"),
            array("titoloLibroORivista")
        );
        foreach ($attempts as $attempt) {
            $surname = $this->softAccess($data, $attempt, $surname) ?: $surname;
        }

        $attempts = array(
            array("persona_denominazione", "persona_nome"),
        );
        foreach ($attempts as $attempt) {
            $name = $this->softAccess($data, $attempt, $name) ?: $name;
        }

        return implode(", ", array_filter(array($surname, $name)));
    }

    /**
     * @param $data
     * @return mixed|null
     */
    private function extractIdentificativoFromStdClass($data)
    {
        $title = "";
        return $this->softAccess($data, array("identificativo"), $title);
    }

    /**
     * @param $data
     * @return string
     */
    public function extractTitleFromStdClass($data)
    {
        $title = array();
        $title[] = $this->extractIdentificativoFromStdClass($data);
        $title[] = $this->extractDenominazioneFromStdClass($data);
        $title[] = $this->extractCronologiaFromStdClass($data);

        return count(array_filter($title)) > 0 ? implode(' || ', array_map(function ($a) {
            return $a ?: " - ";
        }, $title)) : ($this->softAccess($data, array("intestazione")) . "");
    }

    private function isConservatore($entity)
    {
        $conservatoreFields = array(
            "cenniStoriciIstituzionali",
            "sog_cons_patrimonio",
            "sog_cons_note",
            "sog_cons_mail",
            "sog_cons_pec",
            "sog_cons_url",
            "sog_cons_telefono",
            "sog_cons_fax",
            "sog_cons_sedi",
            "condizioniAccesso",
            "complessiArchivisticiConservatore",
            "riferimentiBibliograficiConvervatore",
            "fontiArchivisticheConvervatore",
            "riferimentiWebConvervatore",
            "linguaDescrizioneRecordConvervatore",
            "compilazioneConvervatore",
            "osservazioniConvervatore"
        );

        return $this->hasAnyNotEmptyField($entity, $conservatoreFields);
    }

    private function isProduttore($entity)
    {
        $produttoreFields = array(
            "storiaBiografiaStrutturaAmministrativa",
            "complessiArchivisticiProduttore",
            "soggettiProduttori",
            "riferimentiBibliograficiProduttore",
            "fontiArchivisticheProduttore",
            "riferimentiWebProduttore"
        );

        return $this->hasAnyNotEmptyField($entity, $produttoreFields);
    }

    public static function buildProdConsArray($isProd = false, $isCons = false)
    {
        $inferred = array();

        if ($isProd) {
            $inferred[] = "Produttore";
        }

        if ($isCons) {
            $inferred[] = "Conservatore";
        }

        return $inferred;
    }

    private function buildProdConsField($data)
    {
        if ($data->__model != 'archivi.models.ProduttoreConservatore') {
            return;
        }

        $forced = $data->isForceProdCons;
        $forced = is_array($forced) ? $forced : array();

        $inferred = self::buildProdConsArray(
            $this->isProduttore($data) || in_array("SoggettoProduttore", $forced),
            $this->isConservatore($data) || in_array("SoggettoConservatore", $forced)
        );

        $data->isProdCons = $inferred;
    }

    /**
     * @param $entity
     * @param $conservatoreFields
     * @return bool
     */
    private function hasAnyNotEmptyField($entity, $conservatoreFields)
    {
        foreach ($conservatoreFields as $field) {
            if (metafad_common_helpers_ImporterCommons::purgeEmpties($entity->$field)) {
                return true;
            }
        }

        return false;
    }

    private function updateConsProdBidirectional($data, $isNew, $old)
    {
        if ($data->__model != "archivi.models.ComplessoArchivistico" && $data->__model != "archivi.models.ProduttoreConservatore") {
            return;
        }

        if ($data->__model == "archivi.models.ComplessoArchivistico" && !$data->denominazione) {
            return;
        }
        $prodConsHelper = __ObjectFactory::createObject('archivi.helpers.BidirectionalRelationHelper');
        if ($data->__model == 'archivi.models.ComplessoArchivistico') {
            if (!$prodConsHelper->compareSimpleValue($old->soggettoConservatore, $data->soggettoConservatore)) {
                $this->bidirectionalSaveProcedure($prodConsHelper, "complessiArchivisticiConservatore", "linkComplessiArchivistici", $data, "archivi.models.ProduttoreConservatore");
            }
            if (!$prodConsHelper->compareRepeaterValues($old->produttori, $data->produttori, 'soggettoProduttore')) {
                $this->bidirectionalSaveProcedure($prodConsHelper, "complessiArchivisticiProduttore", "inputComplessiArchivistici", $data, "archivi.models.ProduttoreConservatore");
            }
        } else {
            $this->updateProdCons = false;
            if (!$prodConsHelper->compareRepeaterValues($old->complessiArchivisticiProduttore, $data->complessiArchivisticiProduttore, 'inputComplessiArchivistici')) {
                $this->bidirectionalSaveProcedure($prodConsHelper, "produttori", "soggettoProduttore", $data, "archivi.models.ComplessoArchivistico");
            }
            if (!$prodConsHelper->compareRepeaterValues($old->complessiArchivisticiConservatore, $data->complessiArchivisticiConservatore, 'linkComplessiArchivistici')) {
                $this->bidirectionalSaveProcedure($prodConsHelper, "soggettoConservatore", "soggettoConservatore", $data, "archivi.models.ComplessoArchivistico", false);
            }
        }
    }

    private function bidirectionalSaveProcedure($prodConsHelper, $fieldValue, $obValue, $data, $model, $isRepeater = true)
    {
        $idsNew = $prodConsHelper->getNewIds();
        $idsOld = $prodConsHelper->getOldIds();
        foreach ($idsNew as $id) {
            $this->saveBidirectionalRelation($id, $fieldValue, $obValue, $data, $model, $isRepeater);
            $this->updateConsProdBidirectional = true;
        }
        foreach ($idsOld as $id) {
            $this->deleteBidirectionalRelation($id, $fieldValue, $obValue, $data, $model, $isRepeater);
            $this->updateConsProdBidirectional = true;
        }
    }

    private function saveBidirectionalRelation($id, $fieldValue, $obValue, $data, $model, $isRepeater = true)
    {
        $ar = __ObjectFactory::createModel($model);
        if ($ar->load($id)) {
            $dataNew = $ar->getRawData();
            if ($dataNew->instituteKey != metafad_usersAndPermissions_Common::getInstituteKey()) {
                return;
            }
            $stdClass = new StdClass();
            $stdClass->{$obValue} = new Stdclass();
            $stdClass->{$obValue}->id = $data->__id;
            $stdClass->{$obValue}->text = $this->extractTitleFromStdClass($data);
            $dataNew->__id = $id;
            $dataNew->__model = $ar->_className;
            if ($isRepeater) {
                $dataNew->{$fieldValue}[] = $stdClass;
            } else {
                $dataNew->{$fieldValue} = $stdClass->soggettoConservatore;
            }
            $this->updateConsProdBidirectional = false;
            $this->save($dataNew);
        }
    }

    private function deleteBidirectionalRelation($id, $fieldValue, $obValue, $data, $model, $isRepeater = true)
    {
        $ar = __ObjectFactory::createModel($model);
        if ($ar->load($id)) {
            $dataNew = $ar->getRawData();
            if ($dataNew->instituteKey != metafad_usersAndPermissions_Common::getInstituteKey()) {
                return;
            }
            $dataNew->__id = $id;
            $dataNew->__model = $ar->_className;
            if ($isRepeater) {
                $blockValues = $dataNew->{$fieldValue};
                for ($i = 0; $i < count($blockValues); ++$i) {
                    if ($blockValues[$i]->{$obValue}->id == $data->__id) {
                        unset($blockValues[$i]);
                    }
                }
                $blockValues = array_values($blockValues);
                $dataNew->{$fieldValue} = $blockValues;
            } else {
                $dataNew->{$fieldValue} = null;
            }
            $this->updateConsProdBidirectional = false;
            $this->save($dataNew);
        }
    }
}
