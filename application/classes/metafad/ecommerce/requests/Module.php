<?php
class metafad_ecommerce_requests_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.ecommerce.requests';
        $moduleVO->name = 'Licenses';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.ecommerce.requests';
        $moduleVO->model = 'metafad.ecommerce.requests.models.Model';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page pageType="metafad.ecommerce.requests.views.Admin" icon="fa fa-angle-double-right no-rot" parentId="ecommerce" id="ecommerce-requests" value="{i18n:Richieste}" adm:acl="*"/>';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );

    }
}
