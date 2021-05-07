<?php
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);

class archivi_services_ProdConsService extends metafad_jobmanager_service_JobService
{
    protected $arcProxy;

    public function run()
    {
        try {
            $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);
            $this->arcProxy = __ObjectFactory::createModel("archivi.models.proxy.ArchiviProxy");
            //$this->arcProxy->setQueueSize(1000);
            $this->getAllChildren($this->params['id'], $this->params['produttori'], $this->params['conservatore']);
            //@$this->arcProxy->commit();
            $this->arcProxy->reindexDescendants([$this->params['id']]);
            $this->arcProxy->commit();
            $this->arcProxy->reindexDescendants([$this->params['id']], true);
            $this->arcProxy->commitFe(__Config::get('metafad.solr.archive.url'));
            $this->finish('Task completato');
        } catch (Error $e) {
            $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
        }
    }

    private function changeProdCons($figlio, $produttori, $conservatore)
    {
        $data = $figlio->getRawData();
        $data->__id = $figlio->document_id;
        $data->__model = $data->document_type;
        $data->produttori = $produttori;
        $data->soggettoConservatore = $conservatore;
        $oldFlag = $this->arcProxy->getUpdateProdCons();
        $this->arcProxy->setUpdateProdCons(false);
        $this->arcProxy->save($data);
        $this->arcProxy->setUpdateProdCons($oldFlag);
        $this->getAllChildren($figlio->document_id, $produttori, $conservatore);
    }

    private function onlyReindex($figlio) {
        $this->getAllChildren($figlio->document_id);
    }

    private function getAllChildren($id, $produttori = '', $conservatore = '')
    {
        $it = __ObjectFactory::createModelIterator('archivi.models.Model')
            ->load('getParent', array(':parent' => $id, ':languageId' => __ObjectValues::get('pinax', 'languageId')));

        foreach ($it as $figlio) {
            if ($figlio->document_type != 'archivi.models.ComplessoArchivistico') {
                //$this->reindexUnita($figlio);
                //$this->onlyReindex($figlio);
                continue;
            }
            $this->changeProdCons($figlio, $produttori, $conservatore);
            $this->reindexUnita($figlio);
        }
    }

    private function reindexUnita($ar)
    {
        if ($this->params['root'] !== "false") {
            $data = $ar->getRawData();
            $data->__model = $data->document_type;
            $data->__id = $data->document_id;
            $data->instituteKey = $data->instituteKey;
            $this->arcProxy->appendDocumentToData($data);
            $this->arcProxy->sendDataToSolr($data, true);
        }
    }
}
