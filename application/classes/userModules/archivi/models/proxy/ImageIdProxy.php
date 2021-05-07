<?php
class archivi_models_proxy_ImageIdProxy extends archivi_models_ModelProxy
{
    public function findTerm($fieldName, $model, $query, $term, $proxyParams = null)
    {
        $result = $this->searchByTerms($model, $term, $proxyParams);

        $ret = $this->sortResultsByText($result);

        return $ret;
    }

    protected function searchByTerms($model, $term, $proxyParams = null)
    {
        $strumagId = $proxyParams->metadato;
        $result = [];
        if (!$strumagId) {
            return $result;
        }
        $strumag = __ObjectFactory::createModel('metafad.strumag.models.Model');
        $strumag->load($strumagId);
        $physicalSTRU = json_decode($strumag->physicalSTRU);
        $images = $physicalSTRU->image;
        $i = 0;
        $max = min(count($images), 100);
        foreach ($images as $img) {
            $label = $img->label;
            if (!$term) {
                $result[] = ['id' => $img->id, 'text' => $label];
            } elseif (stripos($label, $term) !== false) {
                $result[] = ['id' => $img->id, 'text' => $label];
            }
            ++$i;
            if ($i == $max) {
                break;
            }
        }
        return $result;
    }
}
