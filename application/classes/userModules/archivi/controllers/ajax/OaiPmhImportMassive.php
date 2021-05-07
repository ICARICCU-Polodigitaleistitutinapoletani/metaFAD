<?php
class archivi_controllers_ajax_OaiPmhImportMassive extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($data)
    {
        $result = $this->checkPermissionForBackend('publish');
        if (is_array($result)) {
            return $result;
        }
        $caUrls = [];
        $caIt = __ObjectFactory::createModelIterator('archivi.models.ComplessoArchivistico')->where('oaiUrl', '', '!=');
        foreach ($caIt as $caAr) {
            $caUrls[] = $caAr->oaiUrl;
        }
        $urls = implode('##', $caUrls);

        //TEMPORANEO PER DEMO
        //$tmpURL = $caUrls[0];

        $arrRel = [];
        $spIdArr = [];
        $scIdArr = [];
        $sdcArr = [];

        $pcIt = __ObjectFactory::createModelIterator('archivi.models.ProduttoreConservatore')->where('oaiUrl', '', '!=');
        foreach ($pcIt as $pcAr) {
            $oaiID = $pcAr->oaiUrl;
            if (strpos($oaiID, ':sp:') !== false) {
                $spIdArr[] = $oaiID;
            } elseif (strpos($oaiID, ':sc:') !== false) {
                $scIdArr[] = $oaiID;
            }
        }

        $sdcIt = __ObjectFactory::createModelIterator('archivi.models.SchedaStrumentoRicerca')->where('oaiUrl', '', '!=');
        foreach ($sdcIt as $sdcAr) {
            foreach ($sdcIt as $sdcAr) {
                $sdcArr[] = $sdcAr->oaiUrl;
            }
        }

        $arrRel['eac_icar'] = implode('##', $spIdArr);
        $arrRel['scons_icar'] = implode('##', $scIdArr);
        $arrRel['ead_icar'] = implode('##', $sdcArr);

        //TEMPORANEO
        if($urls == '' && $arrRel['eac_icar'] == '' && $arrRel['scons_icar'] == '') {
            $this->directOutput = true;
            $msg['errors'] = ["Non ci sono record SIAS/SIUS nel database"];
            return $msg;
        }

        //Crea il job
        $this->createJob($urls, $arrRel, 'massive');

        //Redirect alla tabella dei jobs
        $this->directOutput = true;
        $url = __Link::makeUrl('link', array('pageId' => 'metafad.jobsReport'));
        return array('url' => $url);
    }


    private function createJob($url, $arrRel, $type, $configFile = '')
    {
       
        $params = [
            'url' => $url,
            'idsRelations' => $arrRel,
            'logFile' => uniqid(rand()) . uniqid() . '.log',
            'instance' => metafad_usersAndPermissions_Common::getInstituteKey(),
            'type' => $type,
            'configFile' => $configFile,
               ];

        if ($params['instance']) {
            //Creazione del job di sincronizzazione
            $jobFactory = __ObjectFactory::createObject('metafad.jobmanager.JobFactory');
            $jobFactory->createJob(
                'archivi_services_SiasSiusaImportService',
                $params,
                "Sincronizzazione SIAS/SIUSA",
                'BACKGROUND'
            );
            
            $this->directOutput = true;
            $url = __Link::makeUrl('link', array('pageId' => 'metafad.jobsReport'));
            return array('url' => $url);        
        } else {
            return array('errors' => array('error' => 'La selezione dell\'istituto è mancante. Potrebbe essere scaduta la sessione. Contattare l\'amministratore di sistema.'));
        }
    }
}
