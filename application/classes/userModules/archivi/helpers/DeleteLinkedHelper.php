<?php
class archivi_helpers_DeleteLinkedHelper
{

    public function deleteLinked($ar, $type, $id)
    {
        $archiviProxy = __ObjectFactory::createObject('archivi.models.proxy.ArchiviProxy');
        if ($type == 'archivi.models.ComplessoArchivistico') {
            $data = $ar->getRawData();
            $cons = @$data->soggettoConservatore;
            $prod = @$data->produttori;
            if ($cons && is_object($cons)) {
                $idCons = $cons->id;
                if ($idCons) {
                    $arCons = __ObjectFactory::createModel('archivi.models.ProduttoreConservatore');
                    if ($arCons->load($idCons, 'PUBLISHED_DRAFT')) {
                        $complCons = $arCons->complessiArchivisticiConservatore;
                        if (is_array($complCons)) {
                            foreach ($complCons as $key => $compl) {
                                if (!$compl->linkComplessiArchivistici || !is_object($compl->linkComplessiArchivistici)) {
                                    continue;
                                }
                                if ($compl->linkComplessiArchivistici->id == $id) {
                                    unset($complCons[$key]);
                                    $arCons->complessiArchivisticiConservatore = array_values($complCons);
                                    $arCons->saveCurrentPublished();
                                    $archiviProxy->reindexAr($arCons);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            if (is_array($prod) && !empty($prod)) {
                $prodsId = [];
                foreach ($prod as $p) {
                    $sp = $p->soggettoProduttore;
                    if (is_object($sp)) {
                        $prodsId[] = $sp->id;
                    }
                }
                if ($prodsId) {
                    foreach ($prodsId as $pId) {
                        $arProd = __ObjectFactory::createModel('archivi.models.ProduttoreConservatore');
                        if ($arProd->load($pId, 'PUBLISHED_DRAFT')) {
                            $complProd = $arProd->complessiArchivisticiProduttore;
                            if (is_array($complProd)) {
                                foreach ($complProd as $key => $compl) {
                                    if (!$compl->inputComplessiArchivistici || !is_object($compl->inputComplessiArchivistici)) {
                                        continue;
                                    }
                                    if ($compl->inputComplessiArchivistici->id == $id) {
                                        unset($complProd[$key]);
                                        $arProd->complessiArchivisticiProduttore = array_values($complProd);
                                        $arProd->saveCurrentPublished();
                                        $archiviProxy->reindexAr($arProd);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($type == 'archivi.models.ProduttoreConservatore') {
            $data = $ar->getRawData();
            $consCompl = @$data->complessiArchivisticiConservatore;
            $prodCompl = @$data->complessiArchivisticiProduttore;
            if (is_array($consCompl) && !empty($consCompl)) {
                $complsConsId = [];
                foreach ($consCompl as $c) {
                    $complesso = $c->linkComplessiArchivistici;
                    if (is_object($complesso)) {
                        $complsConsId[] = $complesso->id;
                    }
                }
                if ($complsConsId) {
                    foreach ($complsConsId as $pC) {
                        $arCompl = __ObjectFactory::createModel('archivi.models.ComplessoArchivistico');
                        if ($arCompl->load($pC, 'PUBLISHED_DRAFT')) {
                            $consData = $arCompl->getRawData();
                            $cons = @$consData->soggettoConservatore;
                            if ($cons && is_object($cons)) {
                                if ($cons->id == $id) {
                                    $arCompl->soggettoConservatore = null;
                                    $arCompl->saveCurrentPublished();
                                    $archiviProxy->reindexAr($arCompl);
                                }
                            }
                        }
                    }
                }
            }
            if (is_array($prodCompl) && !empty($prodCompl)) {
                $complsProdId = [];
                foreach ($prodCompl as $c) {
                    $complessoProd = $c->inputComplessiArchivistici;
                    if (is_object($complessoProd)) {
                        $complsProdId[] = $complessoProd->id;
                    }
                }
                if ($complsProdId) {
                    foreach ($complsProdId as $pC) {
                        $arComplP = __ObjectFactory::createModel('archivi.models.ComplessoArchivistico');
                        if ($arComplP->load($pC, 'PUBLISHED_DRAFT')) {
                            $prodCompls = $arComplP->produttori;
                            if (is_array($prodCompls)) {
                                foreach ($prodCompls as $key => $prod) {
                                    if (!$prod->soggettoProduttore || !is_object($prod->soggettoProduttore)) {
                                        continue;
                                    }
                                    if ($prod->soggettoProduttore->id == $id) {
                                        unset($prodCompls[$key]);
                                        $arComplP->produttori = array_values($prodCompls);
                                        $arComplP->saveCurrentPublished();
                                        $archiviProxy->reindexAr($arComplP);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
