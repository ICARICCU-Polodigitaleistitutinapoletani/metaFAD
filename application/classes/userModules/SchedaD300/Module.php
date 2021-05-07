<?php
class SchedaD300_Module
{
    static function registerModule()
    {
        pinax_loadLocale('SchedaD300');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'SchedaD300';
        $moduleVO->name = __T('SchedaD300.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'SchedaD300';
        $moduleVO->pageType = 'SchedaD300.views.FrontEnd';
        $moduleVO->model = 'SchedaD300.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'D';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="SchedaD300" value="{i18n:SchedaD300.views.FrontEnd}" pageType="SchedaD300.views.Admin" parentId="gestione-dati/patrimonio" adm:acl="*"/>
<pnx:Page id="SchedaD300_preview" pageType="SchedaD300.views.AdminPreview" parentId="" adm:acl="*" />
<pnx:Page id="SchedaD300_export" value="{i18n:SchedaD300.views.FrontEnd}" pageType="SchedaD300.views.AdminExport" parentId="export/patrimonio" icon="fa fa-angle-double-right" adm:acl="*" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}