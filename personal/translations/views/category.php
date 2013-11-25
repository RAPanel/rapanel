<?php
$this->beginContent('layout');

$language = $this->language;
$this->widget('bootstrap.widgets.TbGridView', array(
	'id' => 'translations-grid',
	'template' => '{pager}{items}{pager}',
	'dataProvider' => $dataProvider,
	'columns' => array(
		'id' => array(
			'name' => 'id',
			'headerHtmlOptions' => array(
				'class' => 'translationId',
			),
		),
		'source' => array(
			'name' => 'message',
			'headerHtmlOptions' => array(
				'class' => 'translationSource',
			),
		),
		'translation' => array(
			'name' => 'translation.translation',
			'type' => 'raw',
			'value' => function ($data) use($language) {
				return CHtml::link(CHtml::tag('span', array(), $data->translation->translation) . ' ' . CHtml::tag('i', array('class' => 'icon-pencil'), ''), array('/admin/translations/translate', 'id' => $data->id, 'language' => $language), array('class' => 'translate'));

			},
			'headerHtmlOptions' => array(
				'class' => 'translationTranslation',
			),
		),
	),
));

$this->endContent();