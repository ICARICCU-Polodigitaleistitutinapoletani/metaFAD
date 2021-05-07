<?php
class metafad_oaipmh_adapters_MagRecordIterator extends pinax_oaipmh_core_AbstractRecordIterator implements pinax_oaipmh_core_RecordIteratorInterface
{
    private $docs;

    /**
     * @param StdClass[] $docs
     */
    public function __construct($docs)
    {
        $this->docs = $docs;
    }


    /**
     * @return pinax_oaipmh_models_VO_RecordVO
     */
    public function current()
    {
        $temp = $this->docs[$this->position];
        return pinax_oaipmh_models_VO_RecordVO::create($temp->id, $temp->update_at_s, 'metafad.mag.models.Model', $temp);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return isset($this->docs[$this->position]);
    }
}
