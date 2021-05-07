<?php
class archivi_controllers_ajax_OaiPmhImport extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($data)
    {
        $result = $this->checkPermissionForBackend('publish');
        if (is_array($result)) {
            return $result;
        }
        $data = json_decode($data);
        $identifier = $data->oaiIdentifier;
        if (!$identifier) {
            $this->directOutput = true;
            $msg['errors'] = ["Inserire un identificativo OAI"];
            return $msg;
        }

        if (strpos($identifier, 'sias.archivi.beniculturali')) {
            $curlUrl = __Config::get('OAI_SIAS');
            $type = 'SIAS';
        } else {
            $curlUrl = __Config::get('OAI_SIUSA');
            $type = 'SIUSA';
        }

        //Determinino il tipo di record e verifico se corrisponde a quello della scheda (ca, sc, sp, sdc)
        $arr = explode(':', $identifier);
        $recordType = $arr[2];
        if ($this->verifyRecordType($recordType, $data->recordType)) {
            $this->directOutput = true;
            $msg['errors'] = ["Il tipo di record $recordType che si sta tentando di importare non corrisponde al tipo di scheda metaFAD"];
            return $msg;
        }

        //Determino il metadata prefix del record richiesto
        $prefix = $this->detectMetadataPrefix($recordType);

        //Costruisco l'url per la cURL
        $curlUrl .= "?verb=GetRecord&identifier=$identifier&metadataPrefix=$prefix";

        //Effettuo la chiamata cURL
        $curlSES = curl_init();
        curl_setopt($curlSES, CURLOPT_URL, $curlUrl);
        curl_setopt($curlSES, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlSES, CURLOPT_HEADER, false);
        $curlResult = curl_exec($curlSES);
        curl_close($curlSES);

        $dom = new DOMDocument();
        $dom->loadXML($curlResult);

        //Controlla la presenza di errori nella risposta
        $error = $dom->getElementsByTagName('error');
        if ($error->length) {
            $this->directOutput = true;
            $msg['errors'] = ["Il servizio ha restituto il seguente errore per l'identificativo '$identifier': " . $error[0]->nodeValue];
            return $msg;
        }

        //Salva le relazioni in un array se ca, altrimenti salva l'identificativo della scheda
        $arrRel = $this->detectRelationsIds($dom, $prefix, $type);

        //Crea il job
        $this->createJob($curlUrl, $arrRel, $identifier, $type, $data->recordId);
        
        //Redirect alla tabella dei jobs
        $this->directOutput = true;
        $url = pinax_helpers_Link::makeUrl('link', array('pageId' => 'metafad.jobsReport'));
        return array('url' => $url);
    }


    private function verifyRecordType($recordTypeOai, $recordTypeMetafad)
    {
        if ($recordTypeMetafad == 'pc') {
            if ($recordTypeOai == 'sp' || $recordTypeOai == 'sc') {
                return false;
            }
        }
        if ($recordTypeOai == $recordTypeMetafad) {
            return false;
        }
        return true;
    }
    //TODO Gli strumenti di ricerca?
    private function detectMetadataPrefix($recordType)
    {
        switch ($recordType) {
            case 'ca':
                return 'ead_icar';
            case 'sp':
                return 'eac_icar';
            case 'sc':
                return 'scons_icar';
            default:
                return 'undefined';
        }
    }

    private function detectRelationsIds($dom, $prefix, $type)
    {
        $arrIds = [];
        $xpath = new DOMXPath($dom);
        //$xpath->registerNamespace('OAI-PMH', 'http://www.openarchives.org/OAI/2.0/');
        $xpath->registerNamespace($prefix, $this->detectSchemaLocation($prefix));

        if ($prefix == 'ead_icar') {
            //Soggetti produttori
            $arrIds['eac_icar'] = $this->readIds($xpath, '//ead_icar:origination/ead_icar:corpname/@identifier', $type, 'eac_icar');

            //Soggetti conservatori
            $arrIds['scons_icar'] = $this->readIds($xpath, '//ead_icar:repository/ead_icar:corpname/@identifier', $type, 'scons_icar');

            $arrIds['ead_icar'] = $this->readIds($xpath, '//ead_icar:otherfindaid/ead_icar:archref', $type, 'ead_icar');


            //TODO strumenti di ricerca?
        } elseif ($prefix == 'eac_icar') {
            $arrIds['eac_icar'] = __Config::get('OAI_' . $type) . "?verb=GetRecord&identifier=" . 'oai:' . strtolower($type) . '.archivi.beniculturali.it:' . str_replace('-', ':', trim($xpath->query('//eac_icar:recordId')[0]->nodeValue)) . '&metadataPrefix=' . $prefix;;
        } elseif ($prefix == 'scons_icar') {
            $arrIds['scons_icar'] = __Config::get('OAI_' . $type) . "?verb=GetRecord&identifier=" .'oai:' . strtolower($type) . '.archivi.beniculturali.it:' . str_replace('-', ':', trim($xpath->query("//scons_icar:identificativo[@tipo='$type']")[0]->nodeValue)) . '&metadataPrefix=' . $prefix;
        }

        return $arrIds;
    }

    private function readIds($xpath, $path, $type, $prefix)
    {
        $url = __Config::get('OAI_' . $type) . "?verb=GetRecord&identifier=";
        $arr = [];
        $nodeList = $xpath->query($path);
        foreach ($nodeList as $node) {
            $arr[] = $url . 'oai:' . strtolower($type) . '.archivi.beniculturali.it:' . str_replace('-', ':', trim($node->nodeValue)) . '&metadataPrefix=' . $prefix;
        }
        return implode('##', $arr);
    }

    private function detectSchemaLocation($prefix)
    {
        switch ($prefix) {
            case 'ead_icar':
                return 'http://ead3.archivists.org/schema/';
            case 'eac_icar':
                return 'urn:isbn:1-931666-33-4';
            case 'scons_icar':
                return 'http://www.san.beniculturali.it/scons2';
        }
    }

    private function createJob($url, $arrRel, $identifier, $type, $recordId = null, $configFile = '')
    {
        $params = [
            'url' => $url,
            'idsRelations' => $arrRel,
            'logFile' => uniqid(rand()) . uniqid() . '.log',
            'instance' => metafad_usersAndPermissions_Common::getInstituteKey(),
            'type' => $type,
            'configFile' => $configFile,
            'recordId' => $recordId        ];

        if ($params['instance']) {
            //Creazione del job di importazione
            $jobFactory = pinax_ObjectFactory::createObject('metafad.jobmanager.JobFactory');
            $jobFactory->createJob(
                'archivi_services_SiasSiusaImportService',
                $params,
                "Importazione $identifier SIAS/SIUSA",
                'BACKGROUND'
            );
            if (!$recordId) {
                $this->directOutput = true;
                $url = pinax_helpers_Link::makeUrl('link', array('pageId' => 'metafad.jobsReport'));
                return array('url' => $url);
            }
        } else {
            return array('errors' => array('error' => 'La selezione dell\'istituto è mancante. Potrebbe essere scaduta la sessione. Contattare l\'amministratore di sistema.'));
        }
    }
}
