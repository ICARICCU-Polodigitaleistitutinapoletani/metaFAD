<?php
class SchedaD200_Module
{
    static function registerModule()
    {
        pinax_loadLocale('SchedaD200');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'SchedaD200';
        $moduleVO->name = __T('SchedaD200.views.FrontEnd');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'SchedaD200';
        $moduleVO->pageType = 'SchedaD200.views.FrontEnd';
        $moduleVO->model = 'SchedaD200.models.Model';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->iccdModuleType = 'D';
        $moduleVO->siteMapAdmin = '
<pnx:Page id="SchedaD200" value="{i18n:SchedaD200.views.FrontEnd}" pageType="SchedaD200.views.Admin" parentId="gestione-dati/patrimonio" adm:acl="*"/>
<pnx:Page id="SchedaD200_preview" pageType="SchedaD200.views.AdminPreview" parentId="" adm:acl="*" />
<pnx:Page id="SchedaD200_export" value="{i18n:SchedaD200.views.FrontEnd}" pageType="SchedaD200.views.AdminExport" parentId="export/patrimonio" icon="fa fa-angle-double-right" adm:acl="*" />';
        $moduleVO->canDuplicated = false;
        $moduleVO->isICCDModule = true;
        $moduleVO->isAuthority = false;

        pinax_Modules::addModule( $moduleVO );
    }
}