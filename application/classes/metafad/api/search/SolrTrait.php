<?php
trait metafad_api_search_SolrTrait
{
    public function isValidSearchType($type)
    {
        $validSearch = json_decode(__Config::get('solr.validSearch'));
        return in_array($type, $validSearch);
    }

    public function errorMessage($message)
    {
        return ['http-status' => 500, 'error' => $message];
    }

    public function doRequest($url, $postBody)
    {
        $request = \__ObjectFactory::createObject('pinax.rest.core.RestRequest', $url . 'query' , 'POST', json_encode($postBody), 'application/json');
        $request->setAcceptType('application/json');
        $request->setTimeout(1000);
        $request->execute();

        return json_decode($request->getResponseBody());
    }
}