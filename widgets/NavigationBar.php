<?php

YiiBase::import('bootstrap.widgets.TbNavbar');

class NavigationBar extends TbNavbar {

	public $type = self::TYPE_INVERSE;
	public $brandUrl = array('/admin//');
	public $collapse = false;
	public $fluid = true;


	public function init() {
		$this->brand = Yii::app()->name;
		$status = YII_DEBUG ?
			CHtml::tag('span', array('class' => 'label label-important status-label pull-right'), Yii::t('admin', "Development mode")) :
			CHtml::tag('span', array('class' => 'label label-success status-label pull-right'), Yii::t('admin', "Production mode"));
		$languagesMenu = '';
		if(Yii::app()->hasComponent('languages')) {
			$menu = array();
			if(Yii::app()->hasComponent('translator')) {
				$languages = Yii::app()->translator->getListData(Yii::app()->language, '{name}');
				foreach($languages as $code => $name) {
					$menu[] = array('label' => $name, 'url' => array('options/language', 'id' => $code), 'active' => $code == Yii::app()->language);
				}
			} else {
				foreach(Yii::app()->languages->allowed as $code) {
					$menu[] = array('label' => $code, 'url' => array('options/language', 'id' => $code), 'active' => $code == Yii::app()->language);
				}
			}
			if(count($menu) > 1) {
				$languagesMenu = array(
					'class' => 'bootstrap.widgets.TbMenu',
					'items' => array(array('label' => Yii::t('admin', 'Language'), 'items' => $menu)),
				);
			}
		}
		$this->items = array(
			array(
				'class' => 'admin.widgets.ModulesMenu',
			),
			$languagesMenu,
			array(
				'class' => 'bootstrap.widgets.TbMenu',
				'htmlOptions' => array('class' => 'pull-right'),
				'items' => array(
					array('label' => Yii::t('admin', 'Go to site'), 'url' => Yii::app()->homeUrl),
					array('label' => Yii::t('admin', 'Clear'), 'visible' => Yii::app()->user->checkAccess(User::ROLE_ROOT), 'items' => array(
						array('label' => Yii::t('admin', 'Assets'), 'url' => array('/admin/clear/assets')),
						array('label' => Yii::t('admin', 'Cached images'), 'url' => array('/admin/clear/images')),
						array('label' => Yii::t('admin', 'Cache'), 'url' => array('/admin/clear/cache')),
					)),
					array('label' => Yii::t('admin', 'Statistics'), 'url' => array('/admin/webstat/index'), 'visible' => file_exists(YiiBase::getPathOfAlias('webroot.webstat'))),
					array('label' => Yii::t('admin', 'Logout'), 'url' => array('/admin/auth/logout')),
				),
			),
			$status,
		);
		parent::init();
	}
}