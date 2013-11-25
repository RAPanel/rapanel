<?php

YiiBase::import('bootstrap.widgets.TbMenu');
/**
 * Class ModulesMenu
 *
 * @property RAdminController $controller
 */
class ModulesMenu extends TbMenu
{

	public $encodeLabel = false;

	public function init()
	{
		$this->id = 'modules-menu';
		$modules = Module::model()->findAll(array('order' => '`t`.`num` ASC'));
		$items = array();
		$label = Yii::t('modules', 'Modules');
		$groups = array();
		$noGroup = array();
		foreach ($modules as $module) {
			if ($module->groupName)
				$groups[$module->groupName][] = $module;
			else
				$noGroup[] = $module;
		}
		foreach ($groups as $groupName => $groupModules) {
			$groupItems = array();
			$lastLang = 'en';
			foreach ($groupModules as $module) {
				$item = $this->formatMenuItem($module);
				if ($item['active']) {
					$label = $this->getModuleLabel($module);
				}
				$groupItems[] = $item;
				$lastLang = $module->lang_id;
			}
			$items[] = array('label' => App::t('modules', $groupName, $lastLang), 'items' => $groupItems);
		}
		if(count($items)) {
			$items[] = '---';
		}
		foreach ($noGroup as $module) {
			$item = $this->formatMenuItem($module);
			if ($item['active']) {
				$label = $this->getModuleLabel($module);
			}
			$items[] = $item;
		}
		if (count($this->controller->module->personalMenu)) {
			if(count($items)) {
				$items[] = '---';
			}
			foreach ($this->controller->module->personalMenu as $controllerId => $title) {
				$active = ($this->controller->id == $controllerId);
				$items[] = array('label' => Yii::t('modules', $title), 'url' => array('/admin/' . $controllerId), 'active' => $active);
				if ($active) {
					$label = Yii::t('modules', $title);
				}
			}
		}
		$this->items = array(array('label' => $label, 'items' => $items));
		parent::init();
	}

	public function formatMenuItem($module)
	{
		$visible = Yii::app()->user->checkAccess($module->access);
		$active = ($module->url == $_GET['url']) && ($this->controller->id == 'content');
		return array('label' => $this->getModuleLabel($module), 'url' => array('/admin/content', 'url' => $module->url), 'active' => $active, 'visible' => $visible);
	}

	public function getModuleLabel($module) {
		return $module->getLabel();
	}

}