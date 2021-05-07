<?php
class archivi_Module
{
    static function registerModule()
    {
        pinax_loadLocale('archivi');

        $moduleVO = pinax_Modules::getModuleVO();
        $moduleVO->id = 'archivi';
        $moduleVO->name = 'Archivi';
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'archivi';
        $moduleVO->pageType = '';
        $moduleVO->author = 'ICAR - ICCU - Polo Digitale degli istituti culturali di Napoli';
        $moduleVO->authorUrl = '';
        $moduleVO->pluginUrl = '';
        $moduleVO->siteMapAdmin = '
<pnx:Page parentId="gestione-dati" id="gestione-dati/archivi" pageType="" value="{i18n:Archivi}" icon="fa fa-angle-double-right" adm:acl="*" />
<pnx:Page parentId="gestione-dati/archivi" id="archivi-ComplessoArchivistico" pageType="archivi.views.AdminComplessoArchivistico" value="{i18n:Complessi}" adm:acl="*"/>
<glz:Page id="archivi-Complessi_popup" parentId="" pageType="archivi.views.PopupSearchComplessi"/>
<glz:Page id="archivi-ProdCons_popup" parentId="" pageType="archivi.views.PopupSearchProdCons"/>
<pnx:Page parentId="gestione-dati/archivi" id="archivi-SchedaStrumentoRicerca" pageType="archivi.views.AdminSchedaStrumentoRicerca" value="{i18n:Strumenti}" adm:acl="*"/>
<pnx:Page parentId="gestione-dati/archivi" id="archivi-FonteArchivistica" pageType="archivi.views.AdminFonteArchivistica" value="{i18n:Fonti Archivistiche}" adm:acl="*"/>
<pnx:Page parentId="gestione-dati/archivi" id="archivi-UnitaArchivistica" pageType="archivi.views.AdminUnitaArchivistica" value="{i18n:Unità archivistica}" adm:acl="*" hide="true"/>
<pnx:Page parentId="gestione-dati/archivi" id="archivi-UnitaDocumentaria" pageType="archivi.views.AdminUnitaDocumentaria" value="{i18n:Unità documentaria}" adm:acl="*" hide="true"/>
<pnx:Page parentId="gestione-dati/authority/archivi" id="archivi-ProduttoreConservatore" pageType="archivi.views.AdminProduttoreConservatore" value="{i18n:Entità}" adm:acl="*"/>
<glz:Page parentId="gestione-dati/authority/archivi" id="archivi-ProgettoDiDigitalizzazione" pageType="archivi.views.AdminProgettoDiDigitalizzazione" value="{i18n:Progetto Digitalizzazione}" adm:acl="*"/>
<glz:Page parentId="gestione-dati/authority/archivi" id="archivi-TestValidazione" pageType="archivi.views.AdminTestValidazione" value="{i18n:Test Validazione}" adm:acl="*"/>
<pnx:Page parentId="gestione-dati/authority/archivi" id="archivi-SchedaBibliografica" pageType="archivi.views.AdminSchedaBibliografica" value="{i18n:Fonti Bibliografiche}" adm:acl="*"/>
<pnx:Page parentId="gestione-dati/authority/archivi/voci-indice" id="archivi-Antroponimi" pageType="archivi.views.AdminAntroponimi" value="{i18n:Antroponimi}" adm:acl="*"/>
<pnx:Page id="archivi-Antroponimi_popup" parentId="" pageType="archivi.views.AdminAntroponimi_popup"/>
<pnx:Page parentId="gestione-dati/authority/archivi/voci-indice" id="archivi-Enti" pageType="archivi.views.AdminEnti" value="{i18n:Enti}" adm:acl="*"/>
<pnx:Page id="archivi-Enti_popup" parentId="" pageType="archivi.views.AdminEnti_popup"/>
<pnx:Page parentId="gestione-dati/authority/archivi/voci-indice" id="archivi-Toponimi" pageType="archivi.views.AdminToponimi" value="{i18n:Toponimi}" adm:acl="*"/>
<pnx:Page id="archivi-Toponimi_popup" parentId="" pageType="archivi.views.AdminToponimi_popup"/>
<glz:Page id="archivi-Import_popup" parentId="" pageType="archivi.views.AdminImport_popup"/>
<glz:Page id="archivi-Export_popup" parentId="" pageType="archivi.views.AdminExport_popup"/>

<pnx:Page parentId="gestione-dati/authority/archivi" id="gestione-dati/authority/archivi/voci-indice" pageType="" value="{i18n:Voci d\'Indice}" icon="fa fa-angle-double-right" adm:acl="*" />
';
        $moduleVO->canDuplicated = false;
        //Formato: archivi-<IdSezioneMinuscolo>@<NomeModello>
        $moduleVO->subPageTypes = array('archivi-complessoarchivistico@Complessi', 'archivi-testvalidazione@Test Validazione', 'archivi-schedastrumentoricerca@Strumenti', 'archivi-fontearchivistica@Fonti Archivistiche', 'archivi-unitaarchivistica@Unità archivistica', 'archivi-unitadocumentaria@Unità documentaria', 'archivi-produttoreconservatore@Entità', 'archivi-schedabibliografica@Fonti Bibliografiche', 'archivi-antroponimi@Antroponimi', 'archivi-enti@Enti', 'archivi-progettodidigitalizzazione@Progetto di Digitalizzazione', 'archivi-toponimi@Toponimi');
        $moduleVO->modelList = array("archivi.models.ComplessoArchivistico" , "archivi.models.TestValidazione" ,"archivi.models.SchedaStrumentoRicerca" ,"archivi.models.FonteArchivistica" ,"archivi.models.UnitaArchivistica" ,"archivi.models.UnitaDocumentaria" ,"archivi.models.ProduttoreConservatore", "archivi.models.SchedaBibliografica" ,"archivi.models.Antroponimi" ,"archivi.models.Enti" , "archivi.models.ProgettoDiDigitalizzazione", "archivi.models.Toponimi" ,);
        pinax_Modules::addModule( $moduleVO );
    }
}
