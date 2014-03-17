<?php

class ModuleController extends RAdminController
{
    public $returnActions = array('index');

    /**
     * Список модулей сайта
     */
    public function actionIndex()
    {
        if (!Yii::app()->user->checkAccess('root')) {
            $this->redirect(array('tutorial'));
        }
        $model = new Module('grid');
        $model->attachBehavior('column', 'ModuleBehavior');

        $this->pageTitle = 'Управление модулями';
        $this->render($this->action->id, compact('model'));
    }

    /**
     * Редактирование/создание модуля
     *
     * @param string $url URL модуля
     */
    public function actionEdit($url = null)
    {
        if ($model = Module::model()->findByAttributes(compact('url'))) {
            $model->scenario = 'edit';
        } else {
            $model = new Module('insert');
        }
        /** @var $model ModuleBehavior */
        $model->attachBehavior('edit', 'ModuleBehavior');

        $this->performAjaxValidation($model);
        if (isset($_POST[get_class($model)])) {
            $model->attributes = $_POST[get_class($model)];
            if ($model->save()) {
                if ($_GET['iframe']) exit('<script>parent.$.modal().close();</script>');
                $this->flash('success module-edit', Yii::t('admin', 'Module successfully saved'));
                $this->redirect(Yii::app()->user->returnUrl);
            }
        }
        $form = new CForm($model->getForm(), $model);

        $this->pageTitle = $model->isNewRecord ? 'Создание модуля' : 'Редактирование модуля';
        $this->renderText($form);
    }

    public function actionConfig($url)
    {
        $model = Module::model()->findByAttributes(compact('url'));
        $model->scenario = 'config';
        /** @var $model ModuleBehavior */
        $model->attachBehavior('config', 'ModuleBehavior');

        $this->performAjaxValidation($model);
        if (isset($_POST[get_class($model)])) {
            $model->attributes = $_POST[get_class($model)];
            if ($model->save()) {
                if ($_GET['iframe']) exit('<script>parent.$.modal().close();</script>');
                $this->flash('success module-config', Yii::t('admin', 'Module config successfully update'));
                $this->redirect(Yii::app()->user->returnUrl);
            } else {
                $this->flash('error module-config', 'Module config save error');
            }
        }

        $form = new CForm($model->getConfigForm(), $model);

        $this->pageTitle = 'Настройка модуля';
        $this->renderText($form);
    }

    public function actionSaveOrder()
    {
        if (isset($_POST['id'])) {
            CVarDumper::dump($_POST['id']);
            $data = array();
            foreach ($_POST['id'] as $key => $val) if ($val)
                $data[] = array(
                    'id' => $val,
                    'num' => $key,
                );
            SaveDAO::execute('module', $data, 'num');
        }
        if (!Yii::app()->request->isAjaxRequest)
            $this->redirect(Yii::app()->user->returnUrl);
        else
            echo Yii::t('admin', 'Modules move success');
    }

    public function actionTutorial()
    {
        $content = Yii::app()->cache->get(__METHOD__);
        if ($content === false) {
            $content = file_get_contents('http://www.rere-design.ru/rere-cms-tutorial.html?ajax=true');
            Yii::app()->cache->set(__METHOD__, $content);
        }

        $this->pageTitle = 'Рекомендации по CMS';
        $this->renderText($content);
    }

    public function actionChange()
    {
        $this->redirect(Yii::app()->user->returnUrl);

        $id=$model=$attr=$value=false;
        foreach ($_POST as $key => $val)
            if ($val == end($_POST)) {
                $attr = $key;
                $value = $val;
            } else {
                $model = ucfirst(current(explode('sGrid_c', $key)));
                $id = $val;
            }
        if($id) echo RActiveRecord::model($model)->updateByPk($id, array($attr => $value));
    }


}