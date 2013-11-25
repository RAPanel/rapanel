<?php

class AuthController extends RAdminController
{
    public function actionLogin()
    {
        $model = new User('login');
        $this->performAjaxValidation($model);
        if (isset($_POST[get_class($model)])) {
            $model->attributes = $_POST[get_class($model)];
            if ($model->validate() && $model->login()) {
                $this->redirect(array("/{$this->module->id}//"));
            }
            $this->flash('You can`t log in now!');
        }
        $form = new CForm($this->getForm(), $model);

        $this->pageTitle = 'Вход в админ панель';
        $this->renderText($form);
    }

    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }

    public function getForm()
    {
        return array(
            'elements' => array(
                'email' => array(
                    'type' => 'text'
                ),
                'password' => array(
                    'type' => 'password'
                ),
                'rememberMe' => array(
                    'type' => 'checkbox',
                ),
            ),
            'buttons' => array(
                'send' => array(
                    'type' => 'submit',
                    'label' => Yii::t('base.auth', 'Log in'),
                ),
            ),
        );
    }

}