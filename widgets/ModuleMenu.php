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

    public function init()
    {
        $data = Module::model()->active()->order()->findAll();
        $this->items = $items = array();
        foreach ($data as $row)
            $items[$row->groupName?$row->groupName:'Прочее'][] = array('label' => $row->name, 'url' => array("/".Yii::app()->controller->module->id."/content/index", 'url' => $row->url));
        foreach ($items as $key => $val) {
            $this->items[] = array('label' => $key, 'items' => $val, 'itemOptions'=>array('class'=>'menu-' . Text::cyrillicToLatin($key)));
        }
        parent::init();
    }
} 