<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 60 * 60);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("../import_pinax.php");

include 'classes/PHPExcel/IOFactory.php';

$inputFileName = './input/POLONAP - PMM - scheda archivistica.xlsx';

try {
    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
} catch(Exception $e) {
    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
}

$sheet = $objPHPExcel->getSheet(4);
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

/*
$it = pinax_ObjectFactory::createModelIterator('archivioDeputazione.models.Model')
      ->where('type', 'fascicolo');

foreach ($it as $ar) {
    $ar->delete();
}
*/

$fieldMap = array (
    3 => 'title',
    4 => 'numerazioneLivello',
    5 => 'dataInizio',
    6 => 'dataFine',
    7 => 'dataTopica',
    8 => 'numeroFogli',
    10 => 'oggetto',
    11 => 'numeroAllegato',
    12 => 'descrizioneFisica',
    13 => 'numeroCorda',
    14 => 'luogo',
    15 => 'statoConservazione',
    16 => 'descrizioneStatoConservazione',
    17 => 'collocazione',
    18 => 'note',
    19 => 'segnalazioni',
    20 => 'inserimentoPersona',
    21 => 'inserimentoData'
);

for ($r = 3; $r <= $highestRow; $r++) {
    $rowData = $sheet->rangeToArray('A' . $r . ':' . $highestColumn . $r, NULL, TRUE, FALSE);
    $row = $rowData[0];

    $model = pinax_ObjectFactory::createModel('metafad.gestioneDati.archivi.models.Model');
    $model->type = 'fascicolo';
    $model->numeroProgressivoGenerale = getProgressiveCode($r - 2);
    $model->identificativoSchedatore = 'Amministratore';

    foreach ($fieldMap as $i => $fieldName) {
        $model->$fieldName = $row[$i];
    }

    $parentModel = pinax_ObjectFactory::createModel('metafad.gestioneDati.archivi.models.Model');
    $parentModel->load($row[2]);
    $parent = new StdClass();
    $parent->id = $row[2];
    $parent->text = $parentModel->title;
    $model->parent = $parent;

    $model->publish();
}

function getLevel($code) {
    $arr = explode('/', $code);

    return $arr[0];
}

function getProgressiveCode($number) {
    return sprintf("NPG%08d", $number);
}