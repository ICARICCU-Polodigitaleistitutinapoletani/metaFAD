<?php
class archivi_views_components_PageTitle extends pinax_components_PageTitle
{
    function process()
    {
        $this->_content = $this->getAttribute('value');
    }
}