<?php
class BIB400_Module
{
    static function registerModule()
    {
        pinax_loadLocale('BIB400');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'BIB400';
        $moduleVO->name = __T('BIB400.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'BIB400';
        $moduleVO->pageType = 'BIB400.views.FrontEnd';
        $moduleVO->model = 'BIB400.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'BIB';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="BIB400" value="{i18n:BIB400.views.FrontEnd}" pageType="BIB400.views.Admin" parentId="gestione-dati/authority/iccd" adm:acl="*"/>
<pnx:Page id="BIB400_preview" pageType="BIB400.views.AdminPreview" parentId="" adm:acl="*" /><pnx:Page pageType="BIB400.views.AdminPopup" id="BIB400_popup" visible="true" parentId="" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = true;

        pinax_Modules::addModule( $moduleVO );
    }
}