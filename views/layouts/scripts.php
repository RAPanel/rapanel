<?php

$this->beginContent('/layouts/head');

$dir = $this->module->assetsDir;

/** @var CClientScript $cs */
$cs = Yii::app()->clientScript;
$cs->registerCoreScript('jquery');
$cs->registerCoreScript('jquery.ui');
$cs->registerCssFile($dir . '/style.css');
//$cs->registerLinkTag('stylesheet/less', 'text/css', $dir . '/style.less');
//$cs->registerScriptFile($dir . '/vendor/less-1.3.3.min.js', CClientScript::POS_HEAD);
//$cs->registerScript('lessWatch', 'less.env = "development";less.watch();', CClientScript::POS_HEAD);
$cs->registerScriptFile($dir . '/vendor/jquery.the-modal.js');
$cs->registerScriptFile($dir . '/application.js');
$cs->registerScriptFile($dir . '/plugins.js');

echo $content;

$this->endContent();
