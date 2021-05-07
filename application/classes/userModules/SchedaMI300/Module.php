<?php
class SchedaMI300_Module
{
    static function registerModule()
    {
        pinax_loadLocale('SchedaMI300');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'SchedaMI300';
        $moduleVO->name = __T('SchedaMI300.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'SchedaMI300';
        $moduleVO->pageType = 'SchedaMI300.views.FrontEnd';
        $moduleVO->model = 'SchedaMI300.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'MI';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="SchedaMI300" value="{i18n:SchedaMI300.views.FrontEnd}" pageType="SchedaMI300.views.Admin" parentId="gestione-dati/patrimonio" adm:acl="*"/>
<pnx:Page id="SchedaMI300_preview" pageType="SchedaMI300.views.AdminPreview" parentId="" adm:acl="*" />
<pnx:Page id="SchedaMI300_export" value="{i18n:SchedaMI300.views.FrontEnd}" pageType="SchedaMI300.views.AdminExport" parentId="export/patrimonio" icon="fa fa-angle-double-right" adm:acl="*" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}