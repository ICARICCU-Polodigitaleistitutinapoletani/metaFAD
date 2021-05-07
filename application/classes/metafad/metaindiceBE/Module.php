<?php
class metafad_metaindiceBE_Module
{
    static function registerModule()
    {
        pinax_loadLocale('metaindiceBE');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metaindiceBE';
        $moduleVO->name = 'Metaindice BE';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.metaindiceBE';
        $moduleVO->pageType = 'metafad.metaindiceBE.views.FrontEnd';
        $moduleVO->model = 'metafad.metaindiceBE.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="metaindiceBE" value="Metaindice BE" pageType="metafad.metaindiceBE.views.Admin" parentId="home" adm:acl="*" icon="fa fa-search"/>';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}