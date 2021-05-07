<?php
class archivi_views_renderer_CellShow extends metafad_common_views_renderer_CellEditDraftDelete
{
    private function getPageTypeFromModelName($modelname){
        $modelNameSplit = explode(".",$modelname);
        return strtolower("archivi-".end($modelNameSplit));
    }

    protected function renderShowButton($key, $row, $enabled = true)
    {
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output = __Link::makeLinkWithIcon(
                'actionsMVC',
                __Config::get('pinax.datagrid.action.showOnlyCssClass').($enabled ? '' : ' disabled'),
                array(
                    'pageId' => $this->getPageTypeFromModelName($row->className),
                    'title' => __T('PNX_RECORD_EDIT'),
                    'id' => $key,
                    'action' => 'edit',
                    'cssClass' => ($enabled ? '' : ' disabled-button')
                )
            );
        }

        return $output;
    }

    public function renderCell($key, $value, $row, $columnName)
    {
        $output = $this->renderShowButton($key, $row, true);

        return $output;
    }
}



