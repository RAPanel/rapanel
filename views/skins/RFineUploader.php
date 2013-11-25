<?php
return array(
	'default' => array(
		'label' => 'Выберите файлы',
		'skin' => 'bootstrap',
		'cssFile' => false,
	),
	'images' => array(
		'label' => 'Выберите изображения',
		'skin' => 'bootstrapImages',
		'cssFile' => false,
		'options' => array(
			'retry' => array(
				'showButton' => false,
			),
		),
		'callbacks' => array(
			'complete' => 'uploadComplete',
		),
	),
);