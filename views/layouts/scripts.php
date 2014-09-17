<?php

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
	),
	'less' => array(
		'[rapanel]/style.less',
	)
));
Yii::app()->clientScript->forcePublishOnDebug = false;
Yii::app()->clientScript->minEnabled = true;
echo $content;

$this->endContent();
