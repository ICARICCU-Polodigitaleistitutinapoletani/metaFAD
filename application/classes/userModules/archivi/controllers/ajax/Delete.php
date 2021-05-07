<?php
class archivi_controllers_ajax_Delete extends metafad_common_controllers_ajax_CommandAjax
{
    public function execute($id)
    {
        $result = $this->checkPermissionForBackend('delete');
        if (is_array($result)) {
            return $result;
        }

        // Nodo da eliminare
        $ar = pinax_ObjectFactory::createModel('archivi.models.Model');
        $ar->setType(null);
        $ar->load($id, 'PUBLISHED_DRAFT');

        $url = $this->getRedirectFromAR($ar);

        if (!$this->hasChildrenFromID($id)){
            if ($this->hasParentFromAR($ar)) {
                $ar->load($ar->parent['id'], 'PUBLISHED_DRAFT');
                $url = $this->getRedirectFromAR($ar);
            } else {
                $url = __Routing::makeURL('link', array('pageId' => 'archivi-complessoarchivistico'));
            }

            $archiveProxy = pinax_ObjectFactory::createObject('archivi.models.proxy.ArchiviProxy');
            $archiveProxy->delete($id);
        }

        $this->directOutput = true;
        return array('url' => $url);
    }

    private function getRedirectFromAR($ar){
        return __Routing::makeURL('archiviMVC', array(
            'id' => $ar->getId(),
            'pageId' => $ar->pageId ?: "archivi-".strtolower(str_replace("archivi.models.", "", $ar->document_type)),
            'sectionType' => $ar->livelloDiDescrizione,
            'action' => 'edit'.($ar->getStatus() == 'DRAFT' ? "Draft" : "")
        ));
    }

    /**
     * @param $ar
     * @return bool
     */
    private function hasParentFromAR($ar)
    {
        return $ar->root == 'false';
    }

    private function hasChildrenFromID($id){
        return pinax_ObjectFactory::createModelIterator('archivi.models.Model')
            ->load('getParent', array(':parent' => $id, ':languageId' => pinax_ObjectValues::get('org.pinax', 'languageId')))
            ->count() > 0;
    }
}
