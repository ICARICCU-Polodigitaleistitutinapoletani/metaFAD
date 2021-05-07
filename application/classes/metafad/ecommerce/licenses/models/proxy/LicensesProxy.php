<?php

class metafad_ecommerce_licenses_models_proxy_LicensesProxy extends PinaxObject
{
    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
      $result = array();
      $it = pinax_ObjectFactory::createModelIterator('metafad.ecommerce.licenses.models.Model');
      if($term)
      {
        $it->where('license_title','%'.$term.'%','ILIKE');
      }

      foreach ($it as $ar) {
        $result[] = array(
          'id' => $ar->license_id,
          'text' => $ar->license_title
        );
      }

      return $result;
    }

    public function getDetailFromId($id)
    {
      $ar = pinax_ObjectFactory::createModel('metafad.ecommerce.licenses.models.Model');
      $ar->load($id);

      return array('id'=>$id,
                   'title'=>$ar->license_title,
                   'description'=>$ar->license_description,
                   'price'=>$ar->license_price
      );
    }
}
