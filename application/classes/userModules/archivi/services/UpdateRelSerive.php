<?php
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);

class archivi_services_UpdateRelService extends metafad_jobmanager_service_JobService
{
    protected $arcProxy;
    protected $root;
    protected $ids;

    public function run()
    {
        try {
            $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);
            $this->arcProxy = __ObjectFactory::createModel("archivi.models.proxy.ArchiviProxy");
            $this->arcProxy->isImportProcess();
            $this->updateRecords();
            $this->finish('Task completato');
            if ($this->root) {
                $jobFactory = __ObjectFactory::createObject('metafad.jobmanager.JobFactory');
                $jobFactory->createJob(
                    'archivi.services.DenominazioneService',
                    array(
                        'id' => $this->ids
                    ),
                    'Cambio denominazione',
                    'BACKGROUND'
                );
            }
        } catch (Error $e) {
            $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
        }
    }

    private function updateRecords()
    {
        $target = $this->params['target'];
        $institute = $this->params['instituteKey'];
        $id = $this->params['id'];
        $value = $this->params['value'];
        foreach ($target as $t) {
            $it = __ObjectFactory::createModelIterator('archivi.models.' . $t->model)->where('instituteKey', $institute);
            foreach ($it as $ar) {
                $update = false;
                $data = $ar->getRawData();
                $fields = $t->fields;
                $fieldNoRep = @$t->fieldNoRep;
                foreach ($fields as $k => $v) {
                    $ob = $data->$k;
                    if (is_array($ob)) {
                        foreach ($ob as $key => $o) {
                            if ($o->$v->id == $id) {
                                $ob[$key]->$v->text = $value;
                                $update = true;
                            }
                        }
                    }
                    $data->$k = $ob;
                }

                if ($fieldNoRep) {
                    $singleOb = @$data->$fieldNoRep;
                    if ($singleOb && $singleOb->id == $id) {
                        $singleOb->text = $value;
                        $data->$fieldNoRep = $singleOb;
                        $update = true;
                    }
                }

                if (!$update) {
                    continue;
                }
                if ($t->model == 'ComplessoArchivistico' && @$data->root == "true") {
                    $this->root = true;
                    $this->ids[] = $ar->getId();
                }
                $data->__id = $ar->getId();
                $data->__model = 'archivi.models.' . $t->model;
                $data->pageId = $t->pageId;
                $status = $ar->getStatus();
                if ($status == 'PUBLISHED') {
                    $this->arcProxy->save($data);
                } else {
                    $this->arcProxy->saveDraft($data);
                }
            }
        }
    }
}
