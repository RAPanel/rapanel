<?php

class RapanelModule extends CWebModule
{
    public $layout;
    public $assetsDir;
    public $defaultController = 'module';

    public function init()
    {
        $this->publishAssets();
	    Yii::app()->errorHandler->errorAction = '/rapanel/module/error';
        parent::init();
    }

    public function publishAssets()
    {
        $this->assetsDir = Yii::app()->assetManager->publish(YiiBase::getPathOfAlias('rapanel.assets'), false, -1, YII_DEBUG);
    }
}