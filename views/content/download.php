<?php
/**
 * Created by PhpStorm.
 * User: semyonchick
 * Date: 27.01.2016
 * Time: 14:16
 */


// disable caching
$now = gmdate("D, d M Y H:i:s");
header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
header("Last-Modified: {$now} GMT");

// force download
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");

// disposition / encoding on response body
header("Content-Disposition: attachment;filename={$_GET['url']}_" . time() . '.csv');
header("Content-Transfer-Encoding: binary");

$data = new CDataProviderIterator($model->contentBehavior->getDataProvider(), 100);

$df = fopen("php://output", 'w');

$keys = $names = array();

foreach (array_merge_recursive(method_exists($model, 'getCharacters') ? array_keys($model->getCharacters()) : array(), array_keys($model->attributes)) as $key) {
    $names[] = iconv('utf8', 'cp1251', $model->getAttributeLabel($key));
    $keys[] = $key;
}

function arrayToString($model, $array)
{
    $data = [];
    foreach ($array as $key => $value)
        $data[] = $model->getAttributeLabel($key) . ': ' . (is_array($value) ? arrayToString($value) : $value);
    return implode("\n", $data);
}

fputcsv($df, $names, ';');
foreach ($data as $model) {
    $array = array();
    foreach ($keys as $key) {
        $array[] = iconv('utf8', 'cp1251', is_array($model->$key) ? arrayToString($model, $model->$key) : $model->$key);
    }
    fputcsv($df, $array, ';');
}
fclose($df);

die;
