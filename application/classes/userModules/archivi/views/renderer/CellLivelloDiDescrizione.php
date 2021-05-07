<?php
class archivi_views_renderer_CellLivelloDiDescrizione extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    public function renderCell($key, $value, $row, $columnName)
    {
        return __T($value);
    }
}