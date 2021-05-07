<?php
class archivi_views_renderer_CellNOP extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row, $columnName)
    {
        return ($row->className == 'archivi.models.ComplessoArchivistico') ? '' : $value;
    }
}
