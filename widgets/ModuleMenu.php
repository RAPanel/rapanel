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
        parent::init();
    }
} 