<?php
class metafad_metaindicePEB_controllers_Index extends pinax_mvc_core_Command
{
    public function execute()
    {
        $c = $this->view->getComponentById('__model');
        $model = $c->getAttribute('value');

        $record = __ObjectFactory::createModelIterator($model)->first();
        if($record){
            $id = $record->document_id;
        }
        else{
            $record = __ObjectFactory::createModel($model);
            $id = $record->save(null,false,'PUBLISHED');
        }
        $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
        $data = $contentproxy->loadContent($id, $model);
        $data['__id'] = $id;
        $this->view->setData($data);
    }
}