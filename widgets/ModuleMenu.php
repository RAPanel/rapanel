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

    public function init()
    {
        $data = Module::model()->cache(60 * 60 * 24, new CGlobalStateCacheDependency('settings'))->active()->order()->findAll();
        $this->items = $items = $urlList = array();
        foreach ($data as $row) {
            $urlList[] = $row->url;
            $items[$row->groupName ? $row->groupName : 'Прочее'][] = array(
                'label' => $row->name,
                'url' => array("/" . Yii::app()->controller->module->id . "/content/index", 'url' => $row->url),
                'visible' => Yii::app()->user->checkAccess($row->access),
            );
        }
        if (in_array('banner', $urlList))
            $items['Статистика'][] = array(
                'label' => 'баннеры',
                'url' => array("/" . Yii::app()->controller->module->id . "/content/banner", 'url' => 'banner'),
                'visible' => Yii::app()->user->checkAccess('moderator'),
            );
        foreach ($items as $key => $val) {
            $this->items[] = array('label' => $key, 'items' => $val, 'itemOptions' => array('class' => 'menu-' . Text::cyrillicToLatin($key)));
        }
	    if(Yii::app()->hasComponent('statisticManager') && Yii::app()->statisticManager->enabled) {
		    $this->items[] = array(
			    'label' => 'Статистика', 'items' => array(
				    array('label' => 'Статистика посещений', 'url' => array("/" . Yii::app()->controller->module->id . '/statistic/global', 'zoom' => 'day')),
				    array('label' => 'Производительность', 'url' => array("/" . Yii::app()->controller->module->id . '/statistic/performance')),
				    array('label' => 'Страницы', 'url' => array("/" . Yii::app()->controller->module->id . '/statistic/pages')),
				    array('label' => 'Точки входа', 'url' => array("/" . Yii::app()->controller->module->id . '/statistic/enters')),
				    array('label' => 'Точки выхода', 'url' => array("/" . Yii::app()->controller->module->id . '/statistic/exits')),
				    array('label' => 'Продолжительность визита', 'url' => array("/" . Yii::app()->controller->module->id . '/statistic/visits')),
				    array('label' => 'Браузеры', 'url' => array("/" . Yii::app()->controller->module->id . '/statistic/browsers')),
			    ),
			    'itemOptions' => array('class' => 'menu-statistic'),
			    'visible' => Yii::app()->user->checkAccess('moderator'),
		    );
	    }
        parent::init();
    }
} 