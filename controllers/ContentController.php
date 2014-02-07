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
                'class' => 'ext.RUpload.RFileUploadAction',
                'model' => $_GET['type'] ? $_GET['type'] : 'Photo',
                'savePath' => 'data/' . ($_GET['type'] == 'UserFiles' ? '_files' : '_tmp'),
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
        $model->attachBehavior('contentBehavior', 'ContentBehavior');
        $model->setModule($module);
        if ($type == 'categories') $model->setIsCategory(true);
        if (Yii::app()->request->isAjaxRequest) {
            $provider = $model->contentBehavior->getDataProvider();
            Yii::import('ext.RSlickGrid.RSlickGrid');
            echo json_encode(array(
                'hits' => $provider->getTotalItemCount(),
                'request' => array(
                    'start' => (int)$_GET['start'],
                ),
                'results' => CJavaScript::jsonDecode(RSlickGrid::getValues($provider, $model->contentBehavior->getColumns())),
            ));
        } else $this->render($this->action->id, compact('model', 'module', 'url'));
    }

    /**
     * Редактирование/создание элемента
     *
     * param string $url URL модуля
     * param string $id ID модели
     */
    public function actionEdit($url = null, $id = null, $type = null)
    {
        /** @var $module Module */
        $module = Module::model()->findByAttributes(compact('url'));
        if (empty($module)) throw new CHttpException(404, 'Модуль не найден');
        /** @var $model RActiveRecord */
        $model = RActiveRecord::model($module->className)->findByPk($id);
        if (empty($model)) $model = new $module->className('insert');
        else $model->scenario = 'edit';
        /** @var $model RActiveRecord|ContentBehavior */
        $model->attachBehavior('contentBehavior', 'ContentBehavior');
        $model->setModule($module);
        if ($type == 'category') {
            $model->is_category = 1;
        }

        $this->performAjaxValidation($model);
        if (isset($_POST[get_class($model)])) {
            $model->attributes = $_POST[get_class($model)];
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
        /** @var $module Module */
        $module = Module::model()->findByAttributes(compact('url'));
        if (empty($module)) throw new CHttpException(404, 'Модуль не найден');

        /** @var $model RActiveRecord */
        $model = RActiveRecord::model($module->className)->findByPk($id);
        if (empty($model)) throw new CHttpException(404, 'Запись не найдена');

        $func = isset($model->NestedSetBehavior) ? 'deleteNode' : 'delete';

        if ($model->{$func}()) {
            $this->flash('success content-delete', 'Object successfully deleted');
        } else {
            $this->flash('error content-delete', 'Object not found');
        }

        if ($_GET['iframe']) Yii::app()->end('<script>parent.$.modal().close();</script>');

        $this->redirect(Yii::app()->user->returnUrl);
    }

    public function actionSaveOrder($id, $prev = null, $next = null)
    {
        $result = 0;
        $move = Page::model()->findByPk($id);
        if ($before = Page::model()->findByPk($prev)) {
            if ($move->parent_id == $before->id || $before->level > $move->level)
                $result = $move->moveAsFirst($before);
            elseif ($move->id != $before->parent_id)
                $result = $move->moveAfter($before);
        } elseif ($after = Page::model()->findByPk($next)) {
            $result = $move->moveBefore($after);
        }
        echo $result;
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

    public function actionFix($id = false, $url = false)
    {
        if (!$id) $id = Module::getIdByUrl($url);
        $this->fixPage($id);
    }

    public function fixPage($module_id)
    {
        $data = Page::model()->findAllByAttributes(compact('module_id'), array('select' => 'id, parent_id, lft, rgt', 'order' => 'lft'));
        $items = array();
        foreach ($data as $row) {
            $items[$row->parent_id][] = $row;
        }
        $this->addIndex(0, $items);
    }

    public function addIndex($parent_id, $items, $lft = 1)
    {
        if (is_array($items[$parent_id])) foreach ($items[$parent_id] as $row) {

            $row->lft = $lft++;
            $lft = $this->addIndex($row->id, $items, $lft);
            $row->rgt = $lft++;
            $row->saveNode(false, array('lft', 'rgt'));
        }
        return $lft;
    }
}