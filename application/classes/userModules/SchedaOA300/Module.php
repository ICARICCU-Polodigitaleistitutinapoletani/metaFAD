<?php
class SchedaOA300_Module
{
    static function registerModule()
    {
        pinax_loadLocale('SchedaOA300');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'SchedaOA300';
        $moduleVO->name = __T('SchedaOA300.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'SchedaOA300';
        $moduleVO->pageType = 'SchedaOA300.views.FrontEnd';
        $moduleVO->model = 'SchedaOA300.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'OA';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="SchedaOA300" value="{i18n:SchedaOA300.views.FrontEnd}" pageType="SchedaOA300.views.Admin" parentId="gestione-dati/patrimonio" adm:acl="*"/>
<pnx:Page id="SchedaOA300_preview" pageType="SchedaOA300.views.AdminPreview" parentId="" adm:acl="*" />
<pnx:Page id="SchedaOA300_export" value="{i18n:SchedaOA300.views.FrontEnd}" pageType="SchedaOA300.views.AdminExport" parentId="export/patrimonio" icon="fa fa-angle-double-right" adm:acl="*" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}