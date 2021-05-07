<?php

class metafad_metaindicePEB_services_PebProxy extends PinaxObject
{
    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $ontologies = $this->getOntologies();
        $result = [];
        $it = __ObjectFactory::createModelIterator('metafad.metaindicePEB.models.OntologyManager');
        if(!$proxyParams){
            $it->where('ontology_type','entity')
                ->where('ontology_name','%'.$term .'%','ILIKE')
                ->orderBy('ontology_name');
            foreach($it as $ar){
                $name = json_decode($ar->ontology_name)->it;
                if($name){
                    $result[] = ['id' => $ar->ontology_uri, 
                            'text' => json_decode($ar->ontology_name)->it,
                            'path' => $ontologies[$ar->ontology_group]];
                }
            }
        }
        else{
            if(__Config::get('peb.url')){
                $pebUrl = __Config::get('peb.url') . '/' . __Config::get('peb.entity.properties') . urlencode($proxyParams->entity);
            }
            else{
                $pebUrl = 'http://peb/admin/' . __Config::get('peb.entity.properties') . urlencode($proxyParams->entity);
            }    
            $response = json_decode(file_get_contents($pebUrl));
            if($response->properties){
                foreach($response->properties as $property){
                    $result[] = ['id' => $property->id, 'text' => $property->name->it];
                }
            }
        }

        return $result;
    }

    private function getOntologies()
    {
        $ontologies = [];
        $it = __ObjectFactory::createModelIterator('metafad.metaindicePEB.models.OntologyManager')
                ->where('ontology_type','ontology');
        foreach($it as $ar){
            $ontologies[$ar->ontology_uri] = json_decode($ar->ontology_name)->it;
        }
        return $ontologies;
    }
}