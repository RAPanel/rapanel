<?php

/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */
class AdminToolbar extends CWidget
{
    public $accessible;
    public $debug;
    public $moduleId = 'rapanel';

    public function init()
    {
        if ($this->accessible = Yii::app()->user->checkAccess('moderator'))
            $this->registerAssets();
        $this->debug = YII_DEBUG;
    }

    public function run()
    {
        if ($this->accessible) {
            echo CHtml::openTag('nav', array('id' => 'adminToolbar', 'class' => 'rp_userMenu' . (isset(Yii::app()->request->cookies['userMenu']) && Yii::app()->request->cookies['userMenu']->value ? ' active' : '')));
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
            echo CHtml::link('RA-panel', '#openRAPanel', array('class' => 'ra-panel'));
        }
    }

    public function registerAssets()
    {
        /** @var RClientScript $clientScript */
        $clientScript = Yii::app()->clientScript;
        $clientScript->assetDirs['adminToolbar'] = __DIR__ . '/assets';
        $clientScript->addGlobal(array(
            'js' => array(
                '[adminToolbar]/theModal.js',
                '[adminToolbar]/userMenu.js',
                'core:cookie',
            ),
            'css' => array(
                '[adminToolbar]/userMenu.css',
            ),
            /*'less' => array(
                '[adminToolbar]/theModal.less',
                '[adminToolbar]/userMenu.less',
            ),*/
        ));
        $clientScript->registerScript(__CLASS__, 'adminToolbar();');
    }

    public function getMenuLeft()
    {
        return array(
            array('label' => 'меню', 'url' => array('/' . $this->moduleId . '/module/index'), 'itemOptions' => array('class' => 'rp_menu'), 'items' => Yii::app()->moduleMapper->getAdminMenuItems($this->moduleId)),
            array('label' => 'сбросить кэш', 'url' => array('/' . $this->moduleId . '/clear/fast'), 'itemOptions' => array('class' => 'rp_reset')),
            array('label' => 'режим правки ' . CHtml::tag('span', array(), $this->debug ? '(включен)' : '(выключен)'), 'url' => array('/' . $this->moduleId . '/options/debug'), 'itemOptions' => array('class' => $this->debug ? 'rp_editMode active' : 'rp_editMode'), 'visible' => Yii::app()->user->checkAccess('root')),
            array('label' => 'свернуть', 'url' => '#hide', 'itemOptions' => array('class' => 'rp_turn')),
        );
    }

    public function getMenuRight()
    {
        return array(
            array('label' => 'редактировать', 'url' => array('/' . $this->moduleId . '/content/edit', 'id' => Yii::app()->params['page_id']), 'itemOptions' => array('class' => 'rp_editSite'), 'visible' => Yii::app()->params['page_id']),
            array('label' => Yii::app()->user->name, 'url' => array('/' . $this->moduleId . '/content/edit', 'url' => 'user', 'id' => Yii::app()->user->id), 'itemOptions' => array('class' => 'rp_username')),
            array('label' => 'выйти', 'url' => array('/' . $this->moduleId . '/auth/logout'), 'itemOptions' => array('class' => 'rp_exit')),
        );
    }
}