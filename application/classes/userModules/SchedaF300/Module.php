<?php
class SchedaF300_Module
{
    static function registerModule()
    {
        pinax_loadLocale('SchedaF300');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'SchedaF300';
        $moduleVO->name = __T('SchedaF300.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'SchedaF300';
        $moduleVO->pageType = 'SchedaF300.views.FrontEnd';
        $moduleVO->model = 'SchedaF300.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'F';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="SchedaF300" value="{i18n:SchedaF300.views.FrontEnd}" pageType="SchedaF300.views.Admin" parentId="gestione-dati/patrimonio" adm:acl="*"/>
<pnx:Page id="SchedaF300_preview" pageType="SchedaF300.views.AdminPreview" parentId="" adm:acl="*" />
<pnx:Page id="SchedaF300_export" value="{i18n:SchedaF300.views.FrontEnd}" pageType="SchedaF300.views.AdminExport" parentId="export/patrimonio" icon="fa fa-angle-double-right" adm:acl="*" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}