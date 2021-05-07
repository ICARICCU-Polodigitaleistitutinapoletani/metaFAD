<?php
setlocale(LC_TIME, "it_IT", "it", "it_IT.utf8");
pinaxcms_Pinaxcms::init();
metafad_Metafad::init();

metafad_sbn_modules_authoritySBN_Module::registerModule();
metafad_sbn_modules_sbnunimarc_Module::registerModule();
metafad_dam_Module::registerModule();
metafad_strumag_Module::registerModule();
metafad_exporter_Module::registerModule();
metafad_mag_Module::registerModule();
metafad_mets_Module::registerModule();
metafad_opac_Module::registerModule();
metafad_gestioneDati_massiveEdit_Module::registerModule();
metafad_workflow_activities_Module::registerModule();
metafad_workflow_processes_Module::registerModule();

metafad_thesaurus_Module::registerModule();
metafad_importer_Module::registerModule();
metafad_uploader_Module::registerModule();
metafad_jobsReport_Module::registerModule();
metafad_gestioneDati_schedeSemplificate_Module::registerModule();

metafad_ecommerce_licenses_Module::registerModule();
metafad_ecommerce_orders_Module::registerModule();
metafad_ecommerce_requests_Module::registerModule();

metafad_tei_Module::registerModule();
metafad_mods_Module::registerModule();

ICARICCU\PEB\OntologyManagerModule::registerModule();

metafad_usersAndPermissions_users_Module::registerModule();
metafad_usersAndPermissions_institutes_Module::registerModule();
metafad_usersAndPermissions_roles_Module::registerModule();

pinax_log_LogFactory::create('DB', array(), 255, 'audiction' );
pinax_log_LogFactory::create('DB', array(), 255, 'thesaurus' );
pinax_log_LogFactory::create('DB', array(), 255, 'internal' );

pinax_ObjectFactory::remapClass('pinax.components.Fieldset', 'metafad.common.views.components.Fieldset');

pinax_ObjectFactory::createObject('metafad.usersAndPermissions.IstituteCheckListener');
pinax_ObjectFactory::createObject('metafad.solr.Listener');

__Paths::set('CACHE_JS', 'cache/');

$application = pinax_ObjectValues::get('org.pinax', 'application' );
if ($application) {
    __Paths::addClassSearchPath( __Paths::get( 'APPLICATION_CLASSES' ).'userModules/' );
}

AUT200_Module::registerModule();
AUT300_Module::registerModule();
AUT400_Module::registerModule();
BIB200_Module::registerModule();
BIB300_Module::registerModule();
BIB400_Module::registerModule();
SchedaOA200_Module::registerModule();
SchedaS200_Module::registerModule();
SchedaD200_Module::registerModule();
SchedaMI200_Module::registerModule();
SchedaOA300_Module::registerModule();
SchedaS300_Module::registerModule();
SchedaD300_Module::registerModule();
SchedaMI300_Module::registerModule();
SchedaF400_Module::registerModule();
archivi_Module::registerModule();
