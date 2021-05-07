<?php
class archivi_controllers_ajax_GetConservatore extends metafad_common_controllers_ajax_CommandAjax
{
    function execute($fieldName, $model, $query, $term, $proxy, $proxyParams, $getId)
    {
        $it = __ObjectFactory::createModelIterator($model);
        $complesso = $it->where('_denominazione', $term, '=')->first();
        $conservatore = $complesso->soggettoConservatore;
        $id = $conservatore->id;

        $ar = __ObjectFactory::createModel('archivi.models.ProduttoreConservatore');
        if ($ar->load($id)) {
            $conservatoreData = new StdClass();
            $conservatoreData->id = $id;
            $conservatoreData->text = $ar->_denominazione;
            return $conservatoreData;
        }

        return $conservatore;
    }
}
