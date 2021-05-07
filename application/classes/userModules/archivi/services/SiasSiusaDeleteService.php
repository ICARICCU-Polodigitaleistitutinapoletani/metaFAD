<?php
class archivi_services_SiasSiusaDeleteService extends metafad_jobmanager_service_JobService
{
    public function run()
    {
        try {
            $this->updateStatus(metafad_jobmanager_JobStatus::RUNNING);
            $ids = $this->params['ids'];
            $this->params['logFile'];
            //$this->createLogFolder();
            $this->releaseRecords($ids);
            $this->finish('Task completato');
        } catch (Error $e) {
            $this->updateStatus(metafad_jobmanager_JobStatus::ERROR);
        }
    }

    private function releaseRecords($ids)
    {
        $arrDeleted = [];
        $errors = [];
        $proxy = __ObjectFactory::createModel("archivi.models.proxy.ArchiviProxy");
        $proxy->isImportProcess();
        foreach ($ids as $id) {
            $ar = __ObjectFactory::createModel('archivi.models.Model');
            if ($ar->load($id)) {
                $data = $ar->getRawData();
                $oai =  $this->extractIdentifier($data->oaiUrl);
                $data->__model = $data->document_type;
                $data->__id = $data->document_id;
                $data->readOnly = "false";
                $data->oaiUrl = '';
                $data->externalID = '';
                $res = $proxy->save($data);
                if ($res) {
                    $arrDeleted[$data->identificativo] = $oai;
                } else {
                    $errors[$data->identificativo] = $oai;
                }
            }
        }
        //$this->createReport($arrDeleted, $errors);
    }

    private function extractIdentifier($url)
    {
        $startPos = strpos($url, 'identifier=') + 11;
        $identifier = substr($url, $startPos, strpos($url, '&metadataPrefix=') - $startPos);
        return $identifier;
    }

    private function createReport($arrDeleted, $errors)
    {
        $logFile = "/opt/data/export/icar-import_log_folder/" . $this->params['logFile'];
        $report = fopen($logFile, "w") or die("Unable to open file!");
        fwrite($report, "ID METAFAD\tID OAI\tTIPO\tMESSAGGIO\n");
        foreach ($arrDeleted as $id => $oai) {
            fwrite($report, "$id\t$oai\tINFO\tRecord scollegato correttamente\n");
        }
        foreach ($errors as $id => $oai) {
            fwrite($report, "$id\t$oai\ERROR\tErrore imprevisto. Record non scollegato\n");
        }
        fclose($report);
    }

    private function createLogFolder()
    {
        if (!file_exists('/opt/data/export/icar-import_log_folder')) {
            mkdir('/opt/data/export/icar-import_log_folder', 0777);
        }
    }
}
