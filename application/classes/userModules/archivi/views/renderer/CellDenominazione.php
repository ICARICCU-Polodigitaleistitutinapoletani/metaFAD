<?php
class archivi_views_renderer_CellDenominazione extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    private $documents;
    
    public function renderCell($key, $value, $row, $columnName)
    {
        $output = $value;

        if ($this->documents->parents_ss) {
            $output .= '</br><span class="small">'.implode('/', $this->documents->parents_ss).'</span>';
        }

        return $output;
    }
}



