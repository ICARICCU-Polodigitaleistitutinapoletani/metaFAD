<?php
ini_set('memory_limit','2048M');
ini_set('max_execution_time', 0);

class archivi_services_DenominazioneService extends metafad_jobmanager_service_JobService
{
    public function run()
    {
        try {
            $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);
            $proxy = pinax_ObjectFactory::createModel("archivi.models.proxy.ArchiviProxy");
            $proxy->reindexDescendants($this->params['id']);
            @$proxy->commit();
            $proxy->reindexDescendants($this->params['id'], true);
            @$proxy->commitFe(__Config::get('metafad.solr.archive.url'));
            $this->finish('Task completato');
        } catch (Error $e) {
            $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
        }
    }
}
