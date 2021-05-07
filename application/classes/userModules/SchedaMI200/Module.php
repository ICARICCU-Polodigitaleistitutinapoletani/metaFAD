<?php
class SchedaMI200_Module
{
    static function registerModule()
    {
        pinax_loadLocale('SchedaMI200');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'SchedaMI200';
        $moduleVO->name = __T('SchedaMI200.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'SchedaMI200';
        $moduleVO->pageType = 'SchedaMI200.views.FrontEnd';
        $moduleVO->model = 'SchedaMI200.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'MI';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="SchedaMI200" value="{i18n:SchedaMI200.views.FrontEnd}" pageType="SchedaMI200.views.Admin" parentId="gestione-dati/patrimonio" adm:acl="*"/>
<pnx:Page id="SchedaMI200_preview" pageType="SchedaMI200.views.AdminPreview" parentId="" adm:acl="*" />
<pnx:Page id="SchedaMI200_export" value="{i18n:SchedaMI200.views.FrontEnd}" pageType="SchedaMI200.views.AdminExport" parentId="export/patrimonio" icon="fa fa-angle-double-right" adm:acl="*" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}