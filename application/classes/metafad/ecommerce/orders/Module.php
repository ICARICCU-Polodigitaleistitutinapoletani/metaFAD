<?php
class metafad_ecommerce_orders_Module
{
    static function registerModule()
    {
        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'metafad.ecommerce.orders';
        $moduleVO->name = 'Orders';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'metafad.ecommerce.orders';
        $moduleVO->model = 'metafad.ecommerce.orders.models.Model';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '<pnx:Page pageType="metafad.ecommerce.orders.views.Admin" icon="fa fa-angle-double-right no-rot" parentId="ecommerce" id="ecommerce-orders" value="{i18n:Ordini}" adm:acl="*"/>';
        $moduleVO->canDuplicated = false;

        pinax_Modules::addModule( $moduleVO );

    }
}
