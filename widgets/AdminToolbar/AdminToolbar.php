<?php
/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */

class AdminToolbar extends CWidget
{

    public $accessable;

    public function init()
    {
        $this->accessable = Yii::app()->user->checkAccess(User::ROLE_MODER);
        $this->registerAssets();
    }

    public function run()
    {
        if ($this->accessable && $this->beginCache(__CLASS__ . YII_DEBUG, array('varyByRoute'=>0, 'duration' => 60 * 60 * 24))) {
            echo CHtml::openTag('div', array('id' => 'admin-toolbar', 'class' => 'admin-toolbar'));
            echo CHtml::link(Yii::t('admin', 'admin'), '#', array('class' => 'toolbar-toggler'));
            $this->widget('zii.widgets.CMenu', array(
                'id' => 'admin-menu',
                'encodeLabel' => false,
                'items' => self::getMenuItems(),
            ));
            echo CHtml::closeTag('div');
            $this->endCache();
        }
    }

    public function registerAssets()
    {
        /** @var CAssetManager $assetManager */
        $assetManager = Yii::app()->assetManager;
        $path = $assetManager->publish(dirname(__FILE__) . '/assets');

        /** @var CClientScript $clientScript */
        $clientScript = Yii::app()->clientScript;
        $clientScript->registerScriptFile($path . '/toolbar.js');
        $clientScript->registerCssFile($path . '/toolbar.css');
        $clientScript->registerScript('admin_toolbar', <<<JAVASCRIPT
$('#admin-toolbar').adminToolbar();
JAVASCRIPT
        );
    }

    public function getMenuItems()
    {
        $status = YII_DEBUG ?
            CHtml::tag('span', array('class' => 'status-development'), Yii::t('admin', "Development mode")) :
            CHtml::tag('span', array('class' => 'status-production'), Yii::t('admin', "Production mode"));
        return array(
            array('label' => Yii::app()->name, 'url' => array('/site/index'), 'itemOptions' => array('class' => 'title')),
            array('label' => Yii::t('auth', 'Logout'), 'url' => array('/auth/logout'), 'itemOptions' => array('class' => 'right')),
            array('label' => Yii::t('admin', 'Open admin panel'), 'url' => array('/admin//'), 'itemOptions' => array('class' => 'right')),
            array('label' => $status, 'itemOptions' => array('class' => 'right')),
        );
    }

}