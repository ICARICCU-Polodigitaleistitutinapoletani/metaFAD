<?php
class metafad_ecommerce_rest_controllers_GetLicense extends pinax_rest_core_CommandRest
{
  function execute($search)
  {
    $proxy = pinax_ObjectFactory::createObject('metafad.ecommerce.licenses.models.proxy.LicensesProxy');
    $result[] = $proxy->findTerm('','','',$search,'');
    return $result;
  }
}
