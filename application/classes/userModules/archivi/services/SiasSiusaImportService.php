<?php
class archivi_services_SiasSiusaImportService extends metafad_importer_helpers_AbstractBatch
{
    protected $importationsNumber = 0;
    protected $currentNumber = 0;

    public function mainRunnerProgress($evt)
    {
        if (property_exists($evt, "data") && key_exists("progress", $evt->data)) {
            $p = $evt->data['progress'];
            $t = $this->importationsNumber;
            $c = $this->currentNumber;
            $evt->data['progress'] = ($t ? ($c * 100 + $p) / $t : 100);
        }

        parent::mainRunnerProgress($evt);
    }

    public function run()
    {
        parent::run();
        $log = pinax_log_LogFactory::create('DB', array(), 255, '*');
        $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);
        $params = $this->params;
        $url = $params['url'];
        $instance = $params['instance'];
        $idsRelations = $params['idsRelations'];
        $logFile = $params['logFile'];
        $specificType = $params['type'];
        $configFile = $params['configFile'];
        $recordId = '';
        if(isset($params['recordId'])) {
            $recordId = $params['recordId'];
        }
        $this->importationsNumber = 1;
        metafad_importer_services_xmlArchiveEADEAC_Importers::importSIASSIUSA($url, $instance, $log, $specificType, $idsRelations, $configFile, $logFile, $recordId);
        $this->finish();
        $this->setJobMessage($logFile);
        $this->save();
    }

    private function setJobMessage($logFile = '')
    {
        $path = __Paths::get('ROOT') . 'export/icar-import_log_folder/';
        if (file_exists($path . $logFile)  || file_exists($path . md5($logFile) . '_logError.log')) {
            $this->setMessage('Importazione NON eseguita: validazione fallita');
        } else {
            $this->setMessage('Importazione eseguita');
        }
    }
}
