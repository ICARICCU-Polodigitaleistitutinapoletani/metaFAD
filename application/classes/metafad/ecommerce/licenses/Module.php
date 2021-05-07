<?php
class metafad_ecommerce_licenses_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.ecommerce.licenses';
        $moduleVO->name = 'Licenses';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.ecommerce.licenses';
        $moduleVO->model = 'metafad.ecommerce.licenses.models.Model';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page pageType="metafad.ecommerce.licenses.views.Admin" icon="fa fa-angle-double-right no-rot" parentId="ecommerce" id="ecommerce-licences" value="{i18n:Licenze}" adm:acl="*"/>';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );

    }
}
