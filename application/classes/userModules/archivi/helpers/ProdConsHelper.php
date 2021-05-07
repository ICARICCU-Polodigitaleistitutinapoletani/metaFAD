<?php
class archivi_helpers_ProdConsHelper
{
    public function compareProduttori($old, $new)
    {
        if ($old == '') {
            $old = [];
        }
        if (is_null($old) && is_null($new)) {
            return true;
        }
        if (count($old) == 0 && is_null($new)) {
            return true;
        }
        if (count($old) !== count($new)) {
            return false;
        }
        $produttoriOld = [];
        $produttoriNew = [];
        foreach ($old as $o => $oldVal) {
            $produttoriOld[] = trim($oldVal->soggettoProduttore->id);
        }
        foreach ($new as $n => $newVal) {
            $produttoriNew[] = trim($newVal->soggettoProduttore->id);
        }
        foreach ($produttoriOld as $t) {
            if (!in_array($t, $produttoriNew)) {
                return false;
            }
        }
        foreach ($produttoriNew as $t) {
            if (!in_array($t, $produttoriOld)) {
                return false;
            }
        }
        return true;
    }

    public function compareConservatore($old, $new)
    {
        if (is_null($old) && is_null($new)) {
            return true;
        }

        if ($old == '' && $new == '') {
            return true;
        }

        if ($old->id !== $new->id) {
            return false;
        }

        return true;
    }
}
