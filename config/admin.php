<?php
return array(
	'preload' => array('log'),
	'import' => array(
		'admin.components.*',
		'admin.models.*',
		'admin.controllers.*',
		'admin.behaviors.*',
		'admin.helpers.*',
	),
	'controllerMap' => array(
		'statistic' => array(
			'class' => 'ext._rere.components.RStatisticManager.RStatisticController',
		),
	),
	'components' => array(
		'errorHandler' => array(
			'errorAction' => 'rapanel/module/error',
		),
		'urlManager' => array(
			'urlFormat' => 'path',
			'showScriptName' => false,
			'urlSuffix' => '',
			'rules' => array(
				'' => 'site/index',
				'<m_:(rapanel)>' => '<m_>/module/index',
				'<m_>/<c_>' => '<m_>/<c_>/index',
				'<m_>/<c_>/<a_>' => '<m_>/<c_>/<a_>',
			),
		),
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
);