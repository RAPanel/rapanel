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
	public $module;

	static function items($module_id = false)
	{
		$module = new self();
		$module->module = $module_id;
		$module->init();
		return $module->items;
	}

	public function init()
	{
		if (empty($this->module)) $this->module = Yii::app()->controller->module->id;
		$data = Module::model()->cache(60 * 60 * 24, new CGlobalStateCacheDependency('settings'))->active()->order()->findAll();
		$this->items = $items = $urlList = array();
		foreach ($data as $row) {
			$urlList[] = $row->url;
			$items[$row->groupName ? $row->groupName : 'Прочее'][] = array(
				'label' => $row->name,
				'url' => array("/" . $this->module . "/content/index", 'url' => $row->url),
				'visible' => Yii::app()->user->checkAccess($row->access),
			);
		}
		if (in_array('banner', $urlList))
			$items['Статистика'][] = array(
				'label' => 'Баннерная сетка',
				'url' => array("/" . $this->module . "/content/banner", 'url' => 'banner'),
				'visible' => Yii::app()->user->checkAccess('moderator'),
			);
		if (Yii::app()->hasComponent('analytics') && Yii::app()->getComponent('analytics')->enabled) {
			$items['Статистика'][] = array('label' => 'Статистика посещений', 'url' => array('/' . $this->module . '/analytics/graph', 'dataSources' => 'hits,visits,visitors'));
			$items['Статистика'][] = array('label' => 'Производительность', 'url' => array('/' . $this->module . '/analytics/graph', 'dataSources' => 'ram,cpu,execTime'));
			$items['Статистика'][] = array('label' => 'Страницы', 'url' => array('/' . $this->module . '/analytics/pages'));
			$items['Статистика'][] = array('label' => 'Продолжительность визита', 'url' => array('/' . $this->module . '/analytics/graph', 'dataSources' => ''));
			$items['Статистика'][] = array('label' => 'Браузеры', 'url' => array('/' . $this->module . '/analytics/graph', 'dataSources' => ''));
		}
		foreach ($items as $key => $val) {
			$this->items[] = array('label' => $key, 'items' => $val, 'itemOptions' => array('class' => 'menu-' . Text::cyrillicToLatin($key)));
		}
		parent::init();
	}
} 