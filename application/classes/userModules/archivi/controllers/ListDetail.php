<?php
class archivi_controllers_listDetail extends metafad_common_controllers_Command
{
    public function execute($id)
    {
        $this->checkPermissionForBackend('visible');

        if ($id) {
            $archiviProxy = __ObjectFactory::createObject('archivi.models.proxy.ArchiviProxy');
            $found = false;

            $it = pinax_ObjectFactory::createModelIterator('archivi.models.UnitaArchivistica')
                ->where('parent', $id);

            if ($it->count() > 0) {
                $found = true;
            }

            if (!$found) {
                $it = pinax_ObjectFactory::createModelIterator('archivi.models.UnitaDocumentaria')
                ->where('parent', $id);

                if ($it->count() > 0) {
                   $found = true;
                }
            }

            if ($found) {
                $cmp = $this->view->getComponentById( 'dataGridDetail' );
                $cmp->setColumnAttribute(0, 'headerText','Identificativo per Unità');
            }

            $parent = array();
            $record = pinax_ObjectFactory::createModel('archivi.models.Model');
            $record->setType(null);
            $title = $id;
            if ($record->load($id, 'PUBLISHED_DRAFT')) {
                $this->checkInstitute($record->instituteKey);

                $titleLinks = array();

                if ($record->parent) {
                    $parentsPath = array();
                    $archiviProxy->getParentsArray((object)$record->parent, $parentsPath);
                    foreach ($parentsPath as $k => $v) {
                        $url = __Link::makeLink('archiviMVC', array(
                            'action' => 'listDetail',
                            'id' => $k,
                            'label' => $v
                        ));
                        $titleLinks []= $url;
                    }
                }

                $titleLinks[] = $record->denominazione;

                $title = implode(' > ', $titleLinks);
            }

            $c = $this->view->getComponentById('fondoTitle');
            $c->setAttribute('value', $title);
            $c->process();

            $hasUnitChildren = $archiviProxy->hasUnitChildren($id);
            $c = $this->view->getComponentById('reorderButton');
            $c->setAttribute('visible', $hasUnitChildren);
        }
    }
}