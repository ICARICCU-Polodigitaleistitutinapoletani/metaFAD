<?php
class SchedaS200_Module
{
    static function registerModule()
    {
        pinax_loadLocale('SchedaS200');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'SchedaS200';
        $moduleVO->name = __T('SchedaS200.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'SchedaS200';
        $moduleVO->pageType = 'SchedaS200.views.FrontEnd';
        $moduleVO->model = 'SchedaS200.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'S';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="SchedaS200" value="{i18n:SchedaS200.views.FrontEnd}" pageType="SchedaS200.views.Admin" parentId="gestione-dati/patrimonio" adm:acl="*"/>
<pnx:Page id="SchedaS200_preview" pageType="SchedaS200.views.AdminPreview" parentId="" adm:acl="*" />
<pnx:Page id="SchedaS200_export" value="{i18n:SchedaS200.views.FrontEnd}" pageType="SchedaS200.views.AdminExport" parentId="export/patrimonio" icon="fa fa-angle-double-right" adm:acl="*" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}