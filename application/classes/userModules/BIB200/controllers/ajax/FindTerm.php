<?php
class BIB200_controllers_ajax_FindTerm extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($fieldName, $model, $term, $id)
    {
        $result = $this->checkPermissionForBackend('visible');
        if (is_array($result)) {
            return $result;
        }

        $it = pinax_ObjectFactory::createModelIterator('BIB200.models.Model');

        if ($id) {
          $it->where('document_id',$id);
        }

        if ($term != '') {
            $it->where('BIBA', '%'.$term.'%', 'ILIKE');
        }

        $result = array();

        foreach($it as $ar) {
            $result[] = array(
                'id' => $ar->getId(),
                'text' => $ar->BIBA.' - '.$ar->BIBH,
                'values' => $ar->getValuesAsArray(false, false, false, false)
            );
        }

        $this->directOutput = false;
        return $result;
    }
}