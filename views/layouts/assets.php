<?php
/**
 * @var $content string
 */

$this->beginContent('/layouts/head');
Yii::app()->clientScript->assetDirs['rapanel'] = 'rapanel.assets';
Yii::app()->clientScript->addGlobal(array(
	'js' => array(
		'core:jquery',
		'core:jquery.ui',
		'[rapanel]/vendor/jquery.the-modal.js',
		'[rapanel]/sidebar.js',
		'[rapanel]/application.js',
		'[rapanel]/plugins.js',
		'[rapanel]/dropdown.js',
		'[rapanel]/jquery.colorbox-min.js',
	),
    //TODO перенести в less файл!
	'css' => array(
		'[rapanel]/style.css',
	),
	/*'less' => array(
		'[rapanel]/style.less',
	),*/
));
Yii::app()->clientScript->forcePublishOnDebug = true;
Yii::app()->clientScript->minEnabled = false;
echo $content;

$this->endContent();
