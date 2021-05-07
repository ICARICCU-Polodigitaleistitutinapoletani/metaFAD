
<?php
class archivi_controllers_ajax_GetArchiveType extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute()
    {

        $it = __ObjectFactory::createModelIterator('archivi.models.ArchiveType')
            ->load('allTypes');
        $arr = [];
        foreach ($it as $ar) {
            $arr[$ar->archive_type_key] = $ar->archive_type_order;
        }
        return $arr;
    }
}