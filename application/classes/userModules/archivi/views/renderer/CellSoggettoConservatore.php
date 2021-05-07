<?php
class archivi_views_renderer_CellSoggettoConservatore extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row, $columnName)
    {
        $conservatore = __ObjectFactory::createModel('archivi.models.ComplessoArchivistico');
        $conservatore->load($value);
        $output = $conservatore->_denominazione;
        
        return $output;
    }
}


