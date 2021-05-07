<?php
class metafad_api_search_SolrIndexSearch extends pinax_rest_core_CommandRest
{
    use metafad_api_search_SolrTrait;

    public $search;
    public $filters;
    public $facets;
    public $page;
    public $limit;
    public $sort;
    public $fields;
    public $url;

    public function __construct()
    {
        $this->search = __Request::get('search','*');
        $this->filters = __Request::get('filters', []);
        $this->facets = __Request::get('facets', []);
        $this->page = __Request::get('page', 1);
        $this->limit = __Request::get('limit', 10);
        $this->sort = __Request::get('sort', []);
        $this->fields = __Request::get('fields',[]);
        $this->url = __Config::get('metafad.solr.'.__Request::get('type').'.url');
    }

    public function execute()
    {
        if(!$this->isValidSearchType(__Request::get('type'))){
            return $this->errorMessage('Indice SOLR non disponibile');
        }

        if(!$this->url){
            return $this->errorMessage('Indice SOLR non disponibile');
        }
        try{
            $postBody = $this->buildJsonQuery();
            $response = $this->doRequest($this->url, $postBody);
            return $this->createSearchResult($response);
        }
        catch(Exception $e){
            return $this->errorMessage('ATTENZIONE: Verificare che i dati inseriti siano corretti');
        }
    }

    private function buildJsonQuery()
    {
        $postBody = [];
        $postBody['query'] = $this->search;
        $postBody['offset'] = ($this->page - 1) * $this->limit;
        $postBody['limit'] = $this->limit;
        $postBody['fields'] = $this->fields;

        if(!empty($this->filters)){
            foreach($this->filters as $field => $value){
                $postBody['filter'][] = "$field:$value";
            }
        }

        if(!empty($this->facets)){
            $facets = new stdClass();
            foreach($this->facets as $field){
                $facets->$field = new stdClass();
                $facets->$field->field = $field; 
            }
            $postBody['facet'] = $facets;
        }

        if(!empty($this->sort)){
            $sorting = [];
            foreach($this->sort as $sort){
                $sorting[] = $sort->field . ' ' . $sort->direction;
            }
            $postBody['sort'] = implode(',',$sorting);
        }

        return $postBody;
    }

    private function createSearchResult($response)
    {
        $results = $response->response;
        $facets = $response->facets;

        $toReturn = [];
        $toReturn['numFound'] = $results->numFound;
        $toReturn['docs'] = $results->docs;
        $toReturn['facets'] = $facets;

        return $toReturn;
    }
}