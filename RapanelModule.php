<?php

class RapanelModule extends CWebModule
{
	public $layout;
	public $assetsDir;
	public $defaultController = 'module';
	private $_oldErrorAction;

	public function beforeControllerAction($controller, $action)
	{
		$this->_oldErrorAction = Yii::app()->errorHandler->errorAction;
		Yii::app()->errorHandler->errorAction = '/rapanel/module/error';
		return parent::beforeControllerAction($controller, $action);
	}

	public function afterControllerAction($controller, $action)
	{
		Yii::app()->errorHandler->errorAction = $this->_oldErrorAction;
		parent::afterControllerAction($controller, $action);
	}

}