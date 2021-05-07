<?php
class metafad_api_search_SolrIndexDetail extends pinax_rest_core_CommandRest
{
    use metafad_api_search_SolrTrait;

    public $url;

    public function __construct()
    {
        $this->url = __Config::get('metafad.solr.'.__Request::get('type').'.url');
    }

    public function execute()
    {
        $id = urldecode(__Request::get('id'));

        if(!$this->isValidSearchType(__Request::get('type'))){
            return $this->errorMessage('Indice SOLR non disponibile');
        }

        if(!$id){
            return $this->errorMessage('Fornire identificativo valido');
        }

        if(!$this->url){
            return $this->errorMessage('Indice SOLR non disponibile');
        }
        try{
            $postBody = $this->buildJsonQuery($id);
            $response = $this->doRequest($this->url, $postBody);
            return $this->createSearchResult($response);
        }
        catch(Exception $e){
            return $this->errorMessage('ATTENZIONE: Verificare che i dati inseriti siano corretti');
        }
    }

    private function buildJsonQuery($id)
    {
        $postBody = [];
        $postBody['query'] = 'id:"'.$id.'"';
        return $postBody;
    }

    private function createSearchResult($response)
    {
        $results = $response->response;

        $toReturn = [];
        $toReturn['doc'] = current($results->docs);

        return $toReturn;
    }
}