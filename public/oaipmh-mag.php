<?php
require_once(__DIR__ .'/../vendor/autoload.php');

$application = pinax_ObjectFactory::createObject('pinax.oaipmh.core.Application', '../application', '../vendor/icariccu/pinax-2/', '../application/');
$application->sitemapFactory(function($forceReload=false) use ($application) {
    $application->setAdapter('metafad.oaipmh.adapters.MagAdapter');

    $application->addMetadataFormat(pinax_oaipmh_models_VO_MetadataVO::create(
        'mag',
        'http://www.iccu.sbn.it/directories/metadigit201/metadigit.xsd',
        'http://www.iccu.sbn.it/metaAG1.pdf',
        'mag',
        'http://www.iccu.sbn.it/directories/metadigit201/metadigit.xsd'
    ));

    $application->addMetadataFormat(pinax_oaipmh_models_VO_MetadataVO::create(
        'mets',
        'http://www.loc.gov/standards/mets/profile_docs/mets.profile.v1-2.xsd',
        'http://www.loc.gov/METS_Profile/',
        'mag',
        'http://www.loc.gov/standards/mets/profile_docs/mets.profile.v1-2.xsd'
    ));

    $application->addSet('mag', 'metafad.oaipmh.sets.Mag');

    $application->createSiteMap($forceReload);
    return $application->siteMap;
});
__Paths::addClassSearchPath('../vendor/icariccu/metafad-sdk/src/');
$application->run();

