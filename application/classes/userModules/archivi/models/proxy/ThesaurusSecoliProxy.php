<?php
class archivi_models_proxy_ThesaurusSecoliProxy extends metafad_thesaurus_models_proxy_ThesaurusProxy
{

    private $secoliId = [
        'I' => 0, 'II' => 1, 'III' => 2, 'IV' => 3, 'V' => 4, 'VI' => 5, 'VII' => 6, 'VIII' => 7, 'IX' => 8, 'X' => 9, 'XI' => 10, 'XII' => 11,
        'XIII' => 12, 'XIV' => 13, 'XV' => 14, 'XVI' => 15, 'XVII' => 16, 'XVIII' => 17, 'XIX' => 18, 'XX' => 19, 'XXI' => 20
    ];

    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $it = __ObjectFactory::createModelIterator('metafad.thesaurus.models.ThesaurusForms')
            ->load('findTerm', array('moduleId' => $proxyParams->module, 'fieldName' => ($proxyParams->fieldName) ?: $fieldName, 'level' => $proxyParams->level));
        if ($term != '') {
            $it->where('thesaurusdetails_value', '%' . $term . '%', 'ILIKE')
                ->limit(0, 250);
        } else {
            $it->limit(0, 250);
        }

        $result = array();

        foreach ($it as $ar) {
            if ($ar->thesaurusdetails_key == $ar->thesaurusdetails_value) {
                $text = $ar->thesaurusdetails_key;
            } else {
                $text = $ar->thesaurusdetails_key . ' - ' . $ar->thesaurusdetails_value;
            }

            $key = $this->secoliId[$ar->thesaurusdetails_key];

            $result[$key] = array(
                'id' => $ar->thesaurusdetails_key,
                'text' => $text
            );
        }
        
        ksort($result);

        return $result;
    }
}
