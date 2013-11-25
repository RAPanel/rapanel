<?php
$this->beginContent('layout');

$dataProvider = new CArrayDataProvider($messages, array(
	'pagination' => false,
));

$this->widget('bootstrap.widgets.TbGridView', array(
	'dataProvider' => $dataProvider,
	'columns' => array(
		'source' => array(
			'header' => Yii::t('translations', 'Source'),
			'value' => function($data) {
				return $data['message'];
			}
		),
		'category' => array(
			'header' => Yii::t('translations', 'Category'),
			'value' => function($data) {
				return $data['category'];
			}
		),
		'translation' => array(
			'header' => Yii::t('translations', 'Translation'),
			'value' => function($data) use ($language) {
				return Yii::t($data['category'], $data['message'], array(), null, $language);
			}
		),
	),
));

$this->endContent();