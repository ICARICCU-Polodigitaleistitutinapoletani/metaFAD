<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 60 * 60);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("../import_pinax.php");

include 'classes/PHPExcel/IOFactory.php';

$inputFileName = './input/ImportExcel.xlsx';

try {
    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
} catch(Exception $e) {
    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
}

$sheet = $objPHPExcel->getSheet();
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

for ($r = 2; $r <= $highestRow; $r++) {
    $rowData = $sheet->rangeToArray('A' . $r . ':' . $highestColumn . $r, NULL, TRUE, FALSE);

    $row = $rowData[0];

    $parent = $row[3];

    $idDizionario = 1;

    $model = pinax_ObjectFactory::createModel('metafad.gestioneDati.iccd.models.Details');
    $model->thesaurusdetails_FK_thesaurus_id = $idDizionario;
    $model->thesaurusdetails_key = $row[1];
    $model->thesaurusdetails_value = $row[0];
    $model->thesaurusdetails_level = $row[2];

    if($parent){
        $parent = 'Furto';
        $it = pinax_ObjectFactory::createModelIterator('metafad.gestioneDati.iccd.models.ThesaurusDetails');
        $it->where('thesaurusdetails_key', $parent)/*->where('thesaurus_id', $idDelDizionario);*/;
        foreach($it as $ar) {
            $parentId = $ar->getRawData()->thesaurusdetails_id;
        }
        $model->thesaurusdetails_parent = $parentId;
    }

    $model->save();
}
