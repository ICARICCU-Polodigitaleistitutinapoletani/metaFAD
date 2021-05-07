<?php
class SchedaOA200_Module
{
    static function registerModule()
    {
        pinax_loadLocale('SchedaOA200');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'SchedaOA200';
        $moduleVO->name = __T('SchedaOA200.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'SchedaOA200';
        $moduleVO->pageType = 'SchedaOA200.views.FrontEnd';
        $moduleVO->model = 'SchedaOA200.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'OA2.00';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="SchedaOA200" value="{i18n:SchedaOA200.views.FrontEnd}" pageType="SchedaOA200.views.Admin" parentId="gestione-dati/patrimonio" adm:acl="*"/>
<pnx:Page id="SchedaOA200_preview" pageType="SchedaOA200.views.AdminPreview" parentId="" adm:acl="*" />
<pnx:Page id="SchedaOA200_export" value="{i18n:SchedaOA200.views.FrontEnd}" pageType="SchedaOA200.views.AdminExport" parentId="export/patrimonio" icon="fa fa-angle-double-right" adm:acl="*" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}