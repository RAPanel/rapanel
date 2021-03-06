<?php

return CMap::mergeArray(require(__DIR__ . '/main.php'), array(
	'modules' => array(
		'rapanel' => array(
			'class' => 'rapanel.RapanelModule',
			'preload' => array('log'),
			'import' => array(
				'rapanel.components.*',
				'rapanel.models.*',
				'rapanel.controllers.*',
				'rapanel.behaviors.*',
				'rapanel.helpers.*',
                'rapanel.extensions.edatatables.*',
			),
			'components' => array(
				'widgetFactory' => array(
					'widgets' => array(
						'RTagsInput' => array(
							'class' => 'ext.RTagsInput.RTagsInput',
							'options' => array(
								'defaultText' => 'добавить слово',
								'minInputWidth' => '100',
							),
						),
						'CBreadcrumbs' => array(),
						'CListView' => array(
							'cssFile' => null,
							'template' => '{sorter}{pager}{items}{pager}',
							'enableHistory' => true,
							'beforeAjaxUpdate' => 'beforeListViewUpdate',
						),
						'CGridView' => array(
							'cssFile' => null,
							'enableHistory' => true,
							'beforeAjaxUpdate' => 'beforeListViewUpdate',
						),
						'CJuiAutoComplete' => array(
							'cssFile' => false,
						),
						'CLinkPager' => array(
							'maxButtonCount' => 15,
							'header' => false,
							'cssFile' => null,
							'firstPageLabel' => '&lt;&lt;',
							'prevPageLabel' => '&lt;',
							'nextPageLabel' => '&gt;',
							'lastPageLabel' => '&gt;&gt;',
						),
						'CActiveForm' => array(
							'id' => 'edit-form',
							'enableAjaxValidation' => true,
							'enableClientValidation' => true,
							'clientOptions' => array(
								'validateOnSubmit' => true,
								'validateOnChange' => true,
							),
						),
						'CCaptcha' => array(
							'showRefreshButton' => true,
							'clickableImage' => true,
							'imageOptions' => array(
								'title' => 'Получить новый код',
							),
						),
						'CPagination' => array(
							'pageVar' => 'page',
						),
					),
				),
			),
		),
	),
));
