<?php

/**
 * Created by Anatoly Rugalev <anatoly.rugalev@gmail.com>
 */
class ContentController extends RAdminController
{
	public $returnActions = array('index', 'banner');

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
				echo json_encode(Yii::app()->db->createCommand($sql)->limit(100)->queryAll(1, $params));
			} else {
				$result = CHtml::listData(Yii::app()->db->createCommand($sql)->limit(100)->queryAll(1, $params), 'id', 'value');
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
		/** @var $model RActiveRecord */
		$model = new $module->className($module->id);
		$model->resetScope();
		/** @var $model RActiveRecord|ContentBehavior */
		$model->attachBehavior('contentBehavior', 'ContentBehavior');
		$model->setModule($module);

		$widget = $this->createWidget('ext.RSlickGrid.RSlickGrid', array(
			'id' => 'gridItems',
			'dataProvider' => $model->contentBehavior->getDataProvider(),
			'columns' => $model->contentBehavior->getColumns(),
//            'ajaxUrl'       => array($this->action->id, 'url'=>$url, 'ajax'=>1),
			'orderUrl' => array('saveOrder', 'url' => $url),
		), 1);

		if (isset($_GET['json'])) {
			echo json_encode($widget->formattedData);
			Yii::app()->end();
		}

		$this->render($this->action->id, compact('widget', 'model', 'module', 'url'));
	}

	/**
	 * Список элементов модели
	 *
	 * param string $url URL модуля
	 */
	public function actionBanner($url = null)
	{
		$module = Module::model()->findByAttributes(compact('url'));
		if (empty($module)) throw new CHttpException(404, 'Модуль не найден');
		$model = new $module->className('grid');
		/** @var $model ContentBehavior */
		$model->attachBehavior('contentBehavior', 'ContentBehavior');
		$model->setModule($module);
		$this->render($this->action->id, compact('model', 'module', 'url'));
	}

	/**
	 * Редактирование/создание элемента
	 *
	 * param string $url URL модуля
	 * param string $id ID модели
	 */
	public function actionEdit($url = null, $id = null, $type = null, $clone = false)
	{
		if (empty($url) && $id)
			$url = Module::get(Page::model()->resetScope()->findByPk($id)->module_id);
		/** @var $module Module */
		$module = Module::model()->findByAttributes(compact('url'));
		if (empty($module)) throw new CHttpException(404, 'Модуль не найден');
		/** @var $model RActiveRecord */
		$model = RActiveRecord::model($module->className)->resetScope();
		if (is_array($model->tableSchema->primaryKey)) {
			$parts = explode("--", $id);
			$pk = array();
			foreach ($model->tableSchema->primaryKey as $name) {
				$pk[$name] = array_shift($parts);
			}
		} else
			$pk = $id;
		$model = $model->findByPk($pk);
		if (empty($model)) $model = new $module->className('insert');
		else $model->scenario = 'edit';
		/** @var $model RActiveRecord|ContentBehavior */
		$model->attachBehavior('contentBehavior', 'ContentBehavior');
		$model->setModule($module);
		if ($type == 'category') $model->is_category = 1;
		$this->performAjaxValidation($model);
		if (isset($_POST[get_class($model)])) {
			if ($clone !== false && property_exists($model, 'clone')) {
				$model->clone = $model->id;
				$model->isNewRecord = true;
				$model->id = null;
				$model->scenario = 'insert';
			}
			$model->attributes = $_POST[get_class($model)];
			if ($model->save()) {
				if ($_GET['iframe']) exit('<script>parent.$.modal().close();</script>');
				$this->flash('success content-edit', Yii::t('admin', 'Module successfully saved'));
				$this->redirect(Yii::app()->user->returnUrl);
			}
		}
		$form = new CForm($model->getForm(), $model);

		$this->pageTitle = $model->isNewRecord ? 'Создание элемента' : 'Редактирование элемента';
		Yii::app()->clientScript->registerScript('tabs', '$("#tabs").tabs();');
		if (is_null($id)) Yii::app()->clientScript->registerScript('parentId', 'if(parent.parentId) $("form div.row.field_parent_id select").val(parent.parentId)');

		$this->renderText($form->render());
	}

	public function actionClone($url = null, $id = null, $type = null)
	{
		$this->actionEdit($url, $id, $type, true);
	}

	public function actionDelete($url, $id)
	{
		/** @var $module Module */
		$module = Module::model()->findByAttributes(compact('url'));
		if (empty($module)) throw new CHttpException(404, 'Модуль не найден');

		/** @var $model RActiveRecord */
		$model = RActiveRecord::model($module->className)->resetScope()->findByPk($id);
		if (empty($model)) throw new CHttpException(404, 'Запись не найдена');

		$func = isset($model->nestedSetBehavior) ? 'deleteNode' : 'delete';

		if ($model->{$func}()) {
			$this->flash('success content-delete', 'Object successfully deleted');
		} else {
			$this->flash('error content-delete', 'Object not found');
		}

		if ($_GET['iframe']) Yii::app()->end('<script>parent.$.modal().close();</script>');

		$this->redirect(Yii::app()->user->returnUrl);
	}

	public function actionSaveOrder($id, $prev = null, $next = null, $href = null)
	{
		$result = 0;

		preg_match('|url=([^&]+)|', $href ? $href : Yii::app()->request->urlReferrer, $url);
		$class = Module::model()->findByPk(Module::getIdByUrl($url[1]))->className;
		if (!$class) $class = 'Module';
		$base = RActiveRecord::model($class)->resetScope();

		$move = $base->findByPk($id);
		if ($move->hasAttribute('lft')) {
			if ($move->lft > 0 && $move->rgt > 0 || $move->level > 0) {
				/** @var $move NestedSetBehavior */
				if ($before = Page::model()->findByPk($prev)) {
					if ($move->parent_id == $before->id || $before->level > $move->level)
						$result = $move->moveAsFirst($before);
					elseif ($move->id != $before->parent_id)
						$result = $move->moveAfter($before);
				} elseif ($after = Page::model()->findByPk($next)) {
					$result = $move->moveBefore($after);
				}
			} else {
				$criteria = new CDbCriteria();
				$criteria->compare('level', $move->level);
				$criteria->compare('parent_id', $move->parent_id);
				$criteria->compare('module_id', $move->module_id);
				$criteria->compare('is_category', 0);
				$before = $base->findByPk($prev, $criteria);
				$after = $base->findByPk($next, $criteria);
				if (!$before) {
					$base->updateCounters(array('lft' => 1), $criteria);
					$move->lft = 0;
					$result = $move->save(false, array('lft'));
				} elseif (!$after) {
					$move->lft = $before->lft + 1;
					$result = $move->save(false, array('lft'));
				} else {
					if ($after->lft - $before->lft < 2) {
						$count = -($after->lft - $before->lft - 2);
						$criteria->addCondition('(lft>' . $before->lft . ') OR (lft=' . $before->lft . ' AND id<' . $before->id . ')');
						$criteria->order = 'lft, id DESC';
						$base->updateCounters(array('lft' => $count), $criteria);
					}
					$move->lft = $before->lft + 1;
					$result = $move->save(false, array('lft'));
				}
			}
		} elseif ($move->hasAttribute('num')) {
			$before = $base->findByPk($prev);
			$after = $base->findByPk($next);
			if (!$before) {
				$base->updateCounters(array('num' => 1));
				$move->num = 0;
				$result = $move->save(false, array('num'));
			} elseif (!$after) {
				$move->num = $before->num + 1;
				$result = $move->save(false, array('num'));
			} else {
				if ($after->num - $before->num < 2) {
					$count = -($after->num - $before->num - 2);
					$criteria = new CDbCriteria(array('order' => 'num, id'));
					$criteria->addCondition('(num>' . $before->num . ') OR (num=' . $before->num . ' AND id>' . $before->id . ')');
					$base->updateCounters(array('num' => $count), $criteria);
				}
				$move->num = $before->num + 1;
				$result = $move->save(false, array('num'));
			}
		}
		echo $result;
	}

	public function actionView($id, $url)
	{
		$module = Module::model()->findByAttributes(compact('url'));
		if (empty($module)) throw new CHttpException(404, 'Модуль не найден');
		$model = RActiveRecord::model($module->className)->resetScope()->findByPk($id);
        if(get_class($model) == 'User'){
            Yii::app()->user->setState('adminView', $model->id);
            $this->redirect('/');
        }
		elseif ($model->href) $this->redirect($model->href);
	}

	public function actionFix($id = false, $url = false)
	{
		if (!$id) $id = Module::getIdByUrl($url);
		$this->fixPage($id);
	}

	public function actionUpdate($id, $href = null)
	{
		preg_match('|url=([^&]+)|', $href ? $href : Yii::app()->request->urlReferrer, $url);
		$class = Module::model()->findByPk(Module::getIdByUrl($url[1]))->className;
		if (!$class) $class = 'Module';
		$base = RActiveRecord::model($class)->resetScope()->resetScope();

		$model = $base->findByPk($id);
		$model->status_id = (int)$model->status_id != 1;
		$model->save(false, array('status_id'));
		echo $model->status;
	}

	public function fixPage($module_id, $is_category = null)
	{
		$data = Page::model()->resetScope()->findAllByAttributes(compact('module_id'), array('select' => 'id, parent_id, lft, rgt, level', 'order' => 'lft, id DESC', 'condition' => $is_category ? 'is_category>0' : ''));
		$items = array();
		foreach ($data as $row) {
			$items[$row->parent_id][] = $row;
		}
		Yii::app()->db->autoCommit = false;
		$this->addIndex(0, $items);
		Yii::app()->db->autoCommit = true;
		$this->back();
	}

	public function addIndex($parent_id, $items, $lft = 1)
	{
		set_time_limit(10);
		if (is_array($items[$parent_id])) foreach ($items[$parent_id] as $row) if ($row->level || $row->rgt) {
			$row->lft = $lft++;
			$lft = $this->addIndex($row->id, $items, $lft);
			$row->rgt = $lft++;
			$row->saveNode(false, array('lft', 'rgt'));
		}
		return $lft;
	}

	public function actionMany(array $id, $type, $url = null)
	{
		if (is_null($url)) {
			preg_match('|url=([^&]+)|', Yii::app()->request->urlReferrer, $url);
			$url = $url[1];
		}
		$module = Module::model()->findByPk(Module::get($url));
		$class = $module->className;
		if (is_null($class)) $class = 'Module';
		$base = RActiveRecord::model($class)->resetScope();
		$list = $base->findAllByPk($id);

		$result = array();
		foreach ($list as $row)
			switch ($type):
				case 'show':
					$row->status_id = 1;
					$result[] = $row->save(false, array('status_id'));
					break;
				case 'hide':
					$row->status_id = 0;
					$result[] = $row->save(false, array('status_id'));
					break;
				case 'delete':
					$result[] = $row->delete();
					break;
				case 'edit':
					$listData[] = $row->characters;
					break;
				case 'export':
					$result[] = $row->getAttributes();
					break;
			endswitch;

		if (!empty($listData)) {
			$data = array();
			foreach (Characters::getDataByUrls(array_keys($row->characters)) as $key => $val)
//                if ($val['position'] == 'main')
				$data[$val['url']] = array(
					'header' => $val['name'],
					'type' => $val['inputType'],
					'formatter' => 'edit',
				);

			$widget = $this->createWidget('ext.RSlickGrid.RSlickGrid', array(
				'id' => 'gridEditItems',
				'dataProvider' => new CArrayDataProvider($listData),
				'columns' => $data,
				'orderUrl' => array('saveOrder', 'url' => $url),
			), 1);

			if (isset($_GET['json'])) {
				echo json_encode($widget->formattedData);
				Yii::app()->end();
			}
			$this->renderActive('editAll', compact('widget'), 0, 1);
		} else {
			if ($type == 'export') {
				Yii::app()->log->routes['web']->enabled = false;
				header("Content-Type: application/octet-stream");
				header('Content-Disposition: attachment; filename=' . "Экспорт данных из модуля «{$module->name}» " . date('Y-m-d H-i-s') . ".csv");
				$isFirst = true;
				foreach ($result as $row) {
					if ($isFirst) {
						$rowData = array();
						foreach ($row as $attributeName => $value)
							$rowData[] = iconv('utf8', 'cp1251', $base->getAttributeLabel($attributeName));
						echo implode(";", $rowData) . "\n";
						$isFirst = false;
					}
					$rowData = array();
					foreach ($row as $attributeName => $value) {
						if (!is_scalar($value))
							$value = gettype($value) . ':' . base64_encode(serialize($value));
						if ($attributeName == 'lastmod' || $attributeName == 'created')
							$value = date('Y-m-d H:i:s', $value);
						$rowData[] = iconv('utf8', 'cp1251', $value);
					}
					echo implode(";", $rowData) . "\n";
				}
				exit;
			}
			var_dump($result);

		}
	}

	public function actionNote($id, $url)
	{
		if ($_POST['text']) echo Yii::app()->db->createCommand()->insert('admin_note', array(
			'user_id' => Yii::app()->user->id,
			'from_id' => $id,
			'from_url' => $url,
			'text' => $_POST['text'],
		));
	}

	public function actionDownload($url)
	{
		$this->layout = 'head';
		$this->actionIndex($url);
	}
}