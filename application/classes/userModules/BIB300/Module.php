<?php
class BIB300_Module
{
    static function registerModule()
    {
        pinax_loadLocale('BIB300');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'BIB300';
        $moduleVO->name = __T('BIB300.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'BIB300';
        $moduleVO->pageType = 'BIB300.views.FrontEnd';
        $moduleVO->model = 'BIB300.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'BIB';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="BIB300" value="{i18n:BIB300.views.FrontEnd}" pageType="BIB300.views.Admin" parentId="gestione-dati/authority/iccd" adm:acl="*"/>
<pnx:Page id="BIB300_preview" pageType="BIB300.views.AdminPreview" parentId="" adm:acl="*" /><pnx:Page pageType="BIB300.views.AdminPopup" id="BIB300_popup" visible="true" parentId="" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = true;

        pinax_Modules::addModule( $moduleVO );
    }
}