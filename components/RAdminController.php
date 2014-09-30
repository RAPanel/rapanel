<?php

abstract class RAdminController extends RController
{
    public $layout = 'rapanel.views.layouts.main';

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function init()
    {
        if (isset($_GET['iframe'])) $this->layout = 'iframe';
        parent::init();
    }

    /*public function createWidget($className, $properties = array())
    {
        $widget = $this->module->getComponent('widgetFactory')->createWidget($this, $className, $properties);
        $widget->init();
        return $widget;
    }*/

    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array('login'),
                'users' => array('*'),
            ),
            array('allow',
                'roles' => array('moderator'),
            ),
            array('deny',
                'roles' => array('user'),
                'deniedCallback' => function () {
                    Yii::app()->user->setFlash('error access-denied', 'Доступ запрещён');
                    Yii::app()->controller->redirect(array('auth/login', 'returnTo' => Yii::app()->request->requestUri));
                }
            ),
            array('deny',
                'deniedCallback' => function () {
                    Yii::app()->user->setFlash('error access-guest', 'Вы не авторизованы');
                    Yii::app()->controller->redirect(array('auth/login', 'returnTo' => Yii::app()->request->requestUri));
                }
            )
        );
    }
}