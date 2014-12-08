<?php
/**
 * Created by ReRe-Design.
 * User: Semyonchick
 * MailTo: webmaster@rere-design.ru
 */

YiiBase::import('zii.widgets.CMenu');

class ModuleMenu extends CMenu
{
	public $id = 'modules-menu';
	public $activateParents = true;
	public $activeCssClass = 'active selected';
	public $moduleId;

	static function items($moduleId = false)
	{
		$moduleMenu = new self();
		$moduleMenu->moduleId = $moduleId;
		$moduleMenu->init();
		return $moduleMenu->items;
	}

	public function init() {
		$this->items = Yii::app()->moduleMapper->getAdminMenuItems($this->controller->module->id);
		parent::init();
	}
} 