<?php
class archivi_views_components_DeleteButton extends pinax_components_HtmlButton
{
	public function render($outputMode=NULL, $skipChilds=false)
	{
		$this->applyOutputFilters('pre', $this->_content);
		$output = '';

		$attributes 				= array();
		$attributes['id'] 			= $this->getId();
		// $attributes['name'] 		= $this->getAttribute('name') != '' ? $this->getAttribute('name') : $this->getOriginalId();
		$attributes['name'] 		= $this->getOriginalId();
		$attributes['disabled'] 	= $this->getAttribute('disabled') ? 'disabled' : '';
		$attributes['class'] 		= $this->getAttribute('cssClass');
		$attributes['type'] 		= $this->getAttribute('type');
		$attributes['onclick'] 		= $this->getAttribute('onclick');

        $id = intval(preg_replace('/[^0-9]+/', '', $_SERVER['REQUEST_URI']), 10);
        if(strpos($_SERVER['REQUEST_URI'], 'parentId') || strpos($_SERVER['REQUEST_URI'], '/edit/0/')){
            return;
        }

		$output = '';

            $output .= __Link::makeLinkWithIcon( 'archiviMVCDelete',
                                                            $attributes['class'],
                                                            array(
                                                                'title' => __T('PNX_RECORD_DELETE'),
                                                                'id' => $id,
                                                                'model' => 'archivi.models.Model',
                                                                'action' => 'delete'  ) );


		$this->addOutputCode($output);
	}
}