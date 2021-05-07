<?php
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);

class archivi_services_TematismoService extends metafad_jobmanager_service_JobService
{
    protected $arcProxy;

    public function run()
    {
        try {
            $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);
            $this->arcProxy = __ObjectFactory::createModel("archivi.models.proxy.ArchiviProxy");
            $this->getAllChildren($this->params['id'], $this->params['tematismo']);
            $this->finish('Task completato');
        } catch (Error $e) {
            $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
        }
    }

    private function changeTematismo($figlio, $tematismo)
    {
        $data = $figlio->getRawData();
        $data->__id = $figlio->document_id;
        $data->__model = $data->document_type;
        $data->tematismo = $tematismo;
        $oldFlag = $this->arcProxy->getUpdateTematismo();
        $this->arcProxy->setUpdateTematismo(false);
        $this->arcProxy->save($data);
        $this->arcProxy->setUpdateTematismo($oldFlag);
        $this->getAllChildren($figlio->document_id, $tematismo);
    }

    private function getAllChildren($id, $tematismo)
    {
        $it = __ObjectFactory::createModelIterator('archivi.models.Model')
            ->load('getParent', array(':parent' => $id, ':languageId' => __ObjectValues::get('pinax', 'languageId')));

        foreach ($it as $figlio) {
            $this->changeTematismo($figlio, $tematismo);
        }
    }
}
