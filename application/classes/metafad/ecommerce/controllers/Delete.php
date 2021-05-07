<?php
class metafad_ecommerce_controllers_Delete extends metafad_common_controllers_Command
{
    public function execute($id, $model)
    {
        if ($id) {
            $proxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ActiveRecordProxy');
            $data = $proxy->load($id, $model);

            $this->checkPermissionAndInstitute('delete', $data['instituteKey']);

            $proxy->delete($id, $model);

            pinax_helpers_Navigation::goHere();
        }
    }
}
