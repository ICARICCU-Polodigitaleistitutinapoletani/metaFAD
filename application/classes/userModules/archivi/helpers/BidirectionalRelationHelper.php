<?php
class archivi_helpers_BidirectionalRelationHelper
{
    private $oldIds;
    private $newIds;

    public function compareRepeaterValues($old, $new, $linkField)
    {
        $this->oldIds = [];
        $this->newIds = [];
        $equals = true;

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
            $equals = false;
        }
        $valuesOld = [];
        $valuesNew = [];
        foreach ($old as $o => $oldVal) {
            $valuesOld[] = trim($oldVal->{$linkField}->id);
        }
        foreach ($new as $n => $newVal) {
            $valuesNew[] = trim($newVal->{$linkField}->id);
        }
        foreach ($valuesOld as $t) {
            if (!in_array($t, $valuesNew)) {
                $this->oldIds[] = $t;
                $equals = false;
            }
        }
        foreach ($valuesNew as $t) {
            if (!in_array($t, $valuesOld)) {
                $this->newIds[] = $t;
                $equals = false;
            }
        }
        return $equals;
    }

    public function compareSimpleValue($old, $new)
    {
        $this->oldIds = [];
        $this->newIds = [];

        if (is_null($old) && is_null($new)) {
            return true;
        }

        if ($old == '' && $new == '') {
            return true;
        }

        if ($old->id !== $new->id) {
            if (is_object($old)) {
                $this->oldIds[] = $old->id;
            }
            if (is_object($new)) {
                $this->newIds[] = $new->id;
            }
            return false;
        }

        return true;
    }

    public function getNewIds()
    {
        return $this->newIds;
    }

    public function getOldIds()
    {
        return $this->oldIds;
    }
}
