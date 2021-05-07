<?php
class archivi_models_proxy_StrumentiDiRicercaProxy extends archivi_models_ModelProxy
{

    public function findTerm($fieldName, $model, $query, $term, $proxyParams = null)
    {
        $result = $this->searchByTerms($model, $term, $proxyParams);

        $ret = $this->sortResultsByText($result);

        return $ret;
    }

    protected function searchByTerms($model, $term, $proxyParams = null)
    {
        $result = array();
        $id = $this->getId();
        $modelToLoad = $proxyParams->type === 'UA' ? 'archivi.models.UnitaArchivistica' : 'archivi.models.UnitaDocumentaria';

        $ar = __ObjectFactory::createModel($modelToLoad);
        $ar->load($id);
        $parentId = $this->getParentId($ar);
        $arComplesso = __ObjectFactory::createModel('archivi.models.ComplessoArchivistico');

        while ($parentId !== false) {
            $arComplesso->load($parentId);
            $parentId = $this->getParentId($arComplesso);
            if ($arComplesso->livelloDiDescrizione !== 'fondo') {
                continue;
            }
            $strumenti = $arComplesso->strumentiRicerca;
            if (is_array($strumenti)) {
                foreach ($strumenti as $s)
                    if (!is_null($s->linkStrumentiRicerca)) {
                        $result[] = get_object_vars($s->linkStrumentiRicerca);
                    }
            }
        }
        return $result;
    }

    private function getId()
    {
        $referer = $_SERVER['HTTP_REFERER'];
        $pos = strpos($referer, '/edit/');
        $idTemp = substr($referer, $pos + 6);
        $id = str_replace('/', '', $idTemp);
        return intval($id);
    }

    private function getParentId($ar)
    {
        $parent = $ar->parent;
        if (isset($parent['id'])) {
            return $parent['id'];
        }
        return false;
    }
}
