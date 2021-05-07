<?php
class BIB200_Module
{
    static function registerModule()
    {
        pinax_loadLocale('BIB200');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'BIB200';
        $moduleVO->name = __T('BIB200.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'BIB200';
        $moduleVO->pageType = 'BIB200.views.FrontEnd';
        $moduleVO->model = 'BIB200.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'BIB';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="BIB200" value="{i18n:BIB200.views.FrontEnd}" pageType="BIB200.views.Admin" parentId="gestione-dati/authority/iccd" adm:acl="*"/>
<pnx:Page id="BIB200_preview" pageType="BIB200.views.AdminPreview" parentId="" adm:acl="*" /><pnx:Page pageType="BIB200.views.AdminPopup" id="BIB200_popup" visible="true" parentId="" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = true;

        pinax_Modules::addModule( $moduleVO );
    }
}