<?php
class SchedaS300_Module
{
    static function registerModule()
    {
        pinax_loadLocale('SchedaS300');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'SchedaS300';
        $moduleVO->name = __T('SchedaS300.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'SchedaS300';
        $moduleVO->pageType = 'SchedaS300.views.FrontEnd';
        $moduleVO->model = 'SchedaS300.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'S';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="SchedaS300" value="{i18n:SchedaS300.views.FrontEnd}" pageType="SchedaS300.views.Admin" parentId="gestione-dati/patrimonio" adm:acl="*"/>
<pnx:Page id="SchedaS300_preview" pageType="SchedaS300.views.AdminPreview" parentId="" adm:acl="*" />
<pnx:Page id="SchedaS300_export" value="{i18n:SchedaS300.views.FrontEnd}" pageType="SchedaS300.views.AdminExport" parentId="export/patrimonio" icon="fa fa-angle-double-right" adm:acl="*" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}