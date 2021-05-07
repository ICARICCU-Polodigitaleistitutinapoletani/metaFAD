<?php
class metafad_ecommerce_helpers_Ecommerce extends PinaxObject
{

  static function countReq()
  {
    $iterator= pinax_ObjectFactory::createModelIterator ('metafad.ecommerce.requests.models.Model', 'getCurrentUserRequests');
    return $iterator->count();
  }

}
