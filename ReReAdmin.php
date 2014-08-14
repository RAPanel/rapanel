<?php
/**
 * Class ReReAdmin
 */

class ReReAdmin extends CWebModule
{
    public $layout;
    public $assetsDir;
    public $defaultController = 'module';

    public function __construct($id, $parent, $config = null)
    {
        YiiBase::setPathOfAlias('admin', __DIR__);
        $configPath = YiiBase::getPathOfAlias('admin.config') . '/admin.php';
        $config = file_exists($configPath) ? require($configPath) : null;
        parent::__construct($id, $parent, $config);
    }

    public function init()
    {
        $this->publishAssets();
	    Yii::app()->errorHandler->errorAction = '/rapanel/module/error';
        parent::init();
    }

    public function publishAssets()
    {
        $this->assetsDir = Yii::app()->assetManager->publish(YiiBase::getPathOfAlias('admin.assets'), false, -1, YII_DEBUG);
    }
}