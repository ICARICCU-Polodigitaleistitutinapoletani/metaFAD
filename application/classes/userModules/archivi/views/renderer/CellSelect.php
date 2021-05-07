<?php
class archivi_views_renderer_CellSelect extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row, $columnName)
    {   
        $output = '<i data-id="'.$key.'" class="js-send-to-peb btn btn-info btn-flat fa fa-share" aria-hidden="true"></i>';
        return $output;
    }
}
