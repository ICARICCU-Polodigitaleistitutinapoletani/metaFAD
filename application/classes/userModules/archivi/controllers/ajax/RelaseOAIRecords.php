<?php
class archivi_controllers_ajax_RelaseOAIRecords extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($data)
    {
        $result = $this->checkPermissionForBackend('publish');
        if (is_array($result)) {
            return $result;
        }
        
        $data = json_decode($data);
        $ids = trim($data->ids, ',');
        $idsArr = explode(',', $ids);
        if (!$ids) {
            $this->directOutput = true;
            $msg['errors'] = ["Selezionare almeno un record"];
            return $msg;
        }

        //Crea il job
        $this->createJob($idsArr);

        //Redirect alla tabella dei jobs
        $this->directOutput = true;
        $url = pinax_helpers_Link::makeUrl('link', array('pageId' => 'metafad.jobsReport'));
        return array('url' => $url);
    }


    private function createJob($idsArr)
    {
        $params = [
            'ids' => $idsArr,
            'logFile' => uniqid(rand()) . uniqid() . '.log',
            'instance' => metafad_usersAndPermissions_Common::getInstituteKey()
        ];

        if ($params['instance']) {
            //Creazione del job di cancellazione
            $jobFactory = pinax_ObjectFactory::createObject('metafad.jobmanager.JobFactory');
            $jobFactory->createJob(
                'archivi_services_SiasSiusaDeleteService',
                $params,
                "Cancellazione record SIAS/SIUSA",
                'BACKGROUND'
            );
        } else {
            return array('errors' => array('error' => 'La selezione dell\'istituto è mancante. Potrebbe essere scaduta la sessione. Contattare l\'amministratore di sistema.'));
        }
    }
}
