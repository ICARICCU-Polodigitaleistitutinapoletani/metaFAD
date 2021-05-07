<?php
class metafad_oaipmh_sets_Mag implements pinax_oaipmh_core_SetInterface
{
    /**
     * @return array
     */
    public function getSetInfo()
    {
        $info = array();
        $info[ 'setSpec' ] = 'metafad.mag.models.Model';
        $info[ 'setName' ] = 'MAG';
        $info[ 'setDescription' ] = 'MAG';
        $info[ 'setCreator' ] = 'Meta srl';
        $info[ 'model' ] = 'metafad.mag.models.Model';
        return $info;
    }


    /**
     * @return string
     */
    function getModelName()
	{
		$info = $this->getSetInfo();
		return $info['model'];
	}

    /**
     * @param pinax_oaipmh_models_VO_RecordVO $recordVO
     * @return string
     */
    public function getRecord(pinax_oaipmh_models_VO_RecordVO $recordVO)
    {
        return str_replace('<?xml version="1.0" encoding="UTF-8" standalone="no"?>', '', $recordVO->document->xml_only_store);
    }
}
