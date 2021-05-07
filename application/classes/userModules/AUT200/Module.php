<?php
class AUT200_Module
{
    static function registerModule()
    {
        pinax_loadLocale('AUT200');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'AUT200';
        $moduleVO->name = __T('AUT200.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'AUT200';
        $moduleVO->pageType = 'AUT200.views.FrontEnd';
        $moduleVO->model = 'AUT200.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'AUT';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="AUT200" value="{i18n:AUT200.views.FrontEnd}" pageType="AUT200.views.Admin" parentId="gestione-dati/authority/iccd" adm:acl="*"/>
<pnx:Page id="AUT200_preview" pageType="AUT200.views.AdminPreview" parentId="" adm:acl="*" /><pnx:Page pageType="AUT200.views.AdminPopup" id="AUT200_popup" visible="true" parentId="" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = true;

        pinax_Modules::addModule( $moduleVO );
    }
}