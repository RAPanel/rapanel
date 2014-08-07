<?php

/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */
class AdminToolbar extends CWidget
{
    public $accessible;
    public $debug;
    public $module = 'rapanel';

    public function init()
    {
        if ($this->accessible = Yii::app()->user->checkAccess('moderator'))
            $this->registerAssets();
        $this->debug = YII_DEBUG;
    }

    public function run()
    {
        if ($this->accessible ) {
            echo CHtml::openTag('nav', array('id' => 'adminToolbar', 'class' => 'userMenu' . (isset(Yii::app()->request->cookies['userMenu']) && Yii::app()->request->cookies['userMenu']->value ? ' active' : '')));
            $this->widget('zii.widgets.CMenu', array(
                'htmlOptions' => array('class' => 'f-left'),
                'encodeLabel' => false,
                'items' => $this->menuLeft,
            ));
            $this->widget('zii.widgets.CMenu', array(
                'htmlOptions' => array('class' => 'f-right'),
                'items' => $this->menuRight,
            ));
            echo CHtml::closeTag('nav');
            echo CHtml::link('RA-panel', '#open', array('class' => 'ra-panel'));
        }
    }

    public function registerAssets()
    {
        /** @var CAssetManager $assetManager */
        $assetManager = Yii::app()->assetManager;
        $path = $assetManager->publish(__DIR__ . '/assets', 0, -1, YII_DEBUG);

        /** @var CClientScript $clientScript */
        $clientScript = Yii::app()->clientScript;
        $clientScript->registerScriptFile($path . '/theModal.js');
        $clientScript->registerCssFile($path . '/theModal.css');
        $clientScript->registerScriptFile($path . '/userMenu.js');
        $clientScript->registerCssFile($path . '/userMenu.css');
        $clientScript->registerScript(__CLASS__, 'adminToolbar();');
        $clientScript->registerCoreScript('cookie');
    }

    public function getMenuLeft()
    {
        require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ModuleMenu.php');
        return array(
            array('label' => 'меню', 'url' => array('/' . $this->module . '/module/index'), 'itemOptions' => array('class' => 'menu'), 'items' => ModuleMenu::items($this->module)),
            array('label' => 'сбросить кэш', 'url' => array('/' . $this->module . '/clear/cache'), 'itemOptions' => array('class' => 'reset')),
            array('label' => 'режим правки ' . CHtml::tag('span', array(), $this->debug ? '(включен)' : '(выключен)'), 'url' => array('/' . $this->module . '/options/debug'), 'itemOptions' => array('class' => $this->debug ? 'editMode active' : 'editMode'), 'visible' => Yii::app()->user->checkAccess('root')),
            array('label' => 'свернуть', 'url' => '#hide', 'itemOptions' => array('class' => 'turn')),
        );
    }

    public function getMenuRight()
    {
        return array(
            array('label' => 'редактировать', 'url' => array('/' . $this->module . '/content/edit', 'id' => Yii::app()->params['page_id']), 'itemOptions' => array('class' => 'editSite'), 'visible' => Yii::app()->params['page_id']),
            array('label' => Yii::app()->user->name, 'url' => array('/' . $this->module . '/content/edit', 'url' => 'user', 'id' => Yii::app()->user->id), 'itemOptions' => array('class' => 'username')),
            array('label' => 'выйти', 'url' => array('/' . $this->module . '/auth/logout'), 'itemOptions' => array('class' => 'exit')),
        );
    }
}