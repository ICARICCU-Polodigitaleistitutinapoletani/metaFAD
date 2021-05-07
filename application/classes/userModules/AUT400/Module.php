<?php
class AUT400_Module
{
    static function registerModule()
    {
        pinax_loadLocale('AUT400');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'AUT400';
        $moduleVO->name = __T('AUT400.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'AUT400';
        $moduleVO->pageType = 'AUT400.views.FrontEnd';
        $moduleVO->model = 'AUT400.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'AUT';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="AUT400" value="{i18n:AUT400.views.FrontEnd}" pageType="AUT400.views.Admin" parentId="gestione-dati/authority/iccd" adm:acl="*"/>
<pnx:Page id="AUT400_preview" pageType="AUT400.views.AdminPreview" parentId="" adm:acl="*" /><pnx:Page pageType="AUT400.views.AdminPopup" id="AUT400_popup" visible="true" parentId="" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = true;

        pinax_Modules::addModule( $moduleVO );
    }
}