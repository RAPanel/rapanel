<?php
/**
 * Created by Anatoly Rugalev <anatoly.rugalev@gmail.com>
 */

class ContentController extends RAdminController
{
    public $returnActions = array('index');

    public function actions()
    {
        return array(
            'upload' => array(
                'class' => 'ext.RFileUpload.RFileUploadAction',
                'savePath' => 'data/_tmp',
            ),
        );
    }

    public function init()
    {
        if (isset($_GET['start'])) $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        parent::init();
    }

    public function actionSeo()
    {
        $criteria = new CDbCriteria();
        $criteria->limit = 10;
        $i = 0;
        $time = time();
        while ($data = Page::model()->findAll($criteria)) {
            $criteria->offset = $criteria->offset + $criteria->limit;
            /** @var $model PageBase */
            foreach ($data as $model) {
                $i++;
                $model->setSeo();
                $model->save();
            }
        }
        var_dump((time() - $time), $i);
    }

    public function actionAutocompete($tag = false, $term = false, $class = false, $attr = false, $sql = false, array $params = array())
    {
        if ($tag && is_numeric($tag)) {
            $sql = 'SELECT DISTINCT `value` FROM `character_tags_values` JOIN `character_tags` ON(`character_id`=:id) WHERE `value` LIKE :find';
            $params = array('id' => $tag);
        }
        if ($sql) $params['find'] = "%{$term}%";

        if ($sql && isset($params) && is_array($params)) {
            if ($tag) {
                echo json_encode(Yii::app()->db->createCommand($sql)->queryAll(1, $params));
            } else {
                $result = CHtml::listData(Yii::app()->db->createCommand($sql)->queryAll(1, $params), 'id', 'value');
                echo json_encode(array('q' => $term, 'results' => $result));
            }
            return true;
        }

        /*if ($class && $attr) {
            $criteria = new CDbCriteria();
            $criteria->select = 'id, ' . $attr;
            $criteria->addSearchCondition($attr, $term);
            $criteria->addCondition("{$attr}!=''");
            $criteria->limit = 50;
            $data = RActiveRecord::model($class)->findAll($criteria);
            $result = array();
            foreach ($data as $row) {
                $result[$row->id] = $row->$attr;
            }
            echo json_encode(array('q' => $term, 'results' => $result));
            return true;
        }*/

        return false;
    }

    /*public function beforeAction($action) {
        if($action->id == 'elfinder') {
            Yii::app()->log->routes['web']->enabled = false;
        }
        return parent::beforeAction($action);
    }*/

    /**
     * Список элементов модели
     *
     * param string $url URL модуля
     * param string $type ТИП показа
     */
    public function actionIndex($url = null, $type = null)
    {
        $module = Module::model()->findByAttributes(compact('url'));
        if (empty($module)) throw new CHttpException(404, 'Модуль не найден');
        $model = new $module->className('grid');
        /** @var $model ContentBehavior */
        $model->attachBehavior('column', 'ContentBehavior');
        $model->setModule($module);
        if ($type == 'categories') $model->setIsCategory(true);
        if (Yii::app()->request->isAjaxRequest) {
            Yii::import('ext.RSlickGrid.RSlickGrid');
            echo json_encode(array(
                'hits' => $model->getDataProvider()->getTotalItemCount(),
                'request' => array(
                    'start' => (int)$_GET['start'],
                ),
                'results' => CJavaScript::jsonDecode(RSlickGrid::getValues($model->getDataProvider(), $model->getColumns())),
            ));
        } else $this->render($this->action->id, compact('model', 'module', 'url'));
    }

    /**
     * Редактирование/создание элемента
     *
     * param string $url URL модуля
     * param string $id ID модели
     */
    public function actionEdit($url = null, $id = null)
    {
        $module = Module::model()->findByAttributes(compact('url'));
        if (empty($module)) throw new CHttpException(404, 'Модуль не найден');
        $model = RActiveRecord::model($module->className)->findByPk($id);
        if (empty($model)) $model = new $module->className('insert');
        else $model->scenario = 'edit';
        /** @var $model ContentBehavior */
        $model->attachBehavior('contentBehavior', 'ContentBehavior');
        $model->setModule($module);

        $this->performAjaxValidation($model);
        if (isset($_POST[get_class($model)])) {
            $model->attributes = $_POST[get_class($model)];
            if (isset($_POST[get_class($model)]['photos']))
                $model->photos = $_POST[get_class($model)]['photos'];
            if ($model->save()) {
                if ($_GET['iframe']) exit('<script>parent.$.modal().close();</script>');
                $this->flash('success content-edit', Yii::t('admin.result', 'Module successfully saved'));
                $this->redirect(Yii::app()->user->returnUrl);
            }
        }
        $form = new CForm($model->getForm(), $model);

        $this->pageTitle = $model->isNewRecord ? 'Создание элемента' : 'Редактирование элемента';
        Yii::app()->clientScript->registerScript('tabs', '$("#tabs").tabs();');
        /*$this->widget('ext.RChosen.RChosen', array(
            'id'=>'test',
            'query'=>'select',
            'select'=>false,
            'options'=>array(
                'disable_search_threshold'=>10,
            ),
        ));*/
        $this->renderText($form->render());
    }

    public function actionDelete($url, $id)
    {
        /** @var ModuleAdmin $module */
        $module = $this->recordExists('ModuleAdmin', array('url' => $url), 'Модуль не найден');
        /** @var SiteModule $model */
        $model = $module->getModel('Admin');
        $model->applyModule($module->url);
        if ($model = $model->findByPk($id)) {
            $model->delete();
            $this->flash('success content-delete', 'Object successfully deleted');
        } else {
            $this->flash('error content-delete', 'Object not found');
        }
        Yii::app()->history->back(false, array('/admin/index'));
    }

    public function actionSaveOrder($url = null)
    {
        $module = Module::model()->findByAttributes(compact('url'));
        if (empty($module)) throw new CHttpException(404, 'Модуль не найден');
        if (isset($_POST['id'])) {
            $data = array();
            foreach ($_POST['id'] as $key => $val) if ($val)
                $data[] = array(
                    'id' => $val,
                    'num' => $key,
                );
            $table = RActiveRecord::model($module->className)->tableName();
            SaveDAO::execute($table, $data, 'num');
        }
        if (!Yii::app()->request->isAjaxRequest)
            $this->redirect(Yii::app()->user->returnUrl);
        else
            echo Yii::t('admin.result', 'Modules move success');
    }

    public function actionPageSize($size)
    {
        Yii::app()->user->setState('AdminGridView.pageSize', $size);
        Yii::app()->history->back(false, array('/admin/index'));
    }

    public function actionView($id, $url)
    {
        $module = Module::model()->findByAttributes(compact('url'));
        if (empty($module)) throw new CHttpException(404, 'Модуль не найден');
        $model = RActiveRecord::model($module->className)->findByPk($id);
        if ($model->href) $this->redirect($model->href);
    }
}