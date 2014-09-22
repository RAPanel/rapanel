<?php

Yii::import('rapanel.personal.translations.models.*');
class TranslationsController extends RPersonalController {

	public $assetsDir;
	public $language;
	public $category;
	public $enabled = false;

	public function getMenuTitle() {
		return 'Translations';
	}

	public function beforeAction($action) {
		$assetsDir = YiiBase::getPathOfAlias('rapanel.personal.translations.assets');
		/** @var CAssetManager $manager */
		$manager = Yii::app()->assetManager;
		$this->assetsDir = $manager->publish($assetsDir);
		return parent::beforeRender($action);
	}

	public function actionIndex($language = null) {
		if($language === null)
			$this->language = Yii::app()->language;
		else
			$this->language = $language;
		$criteria = new CDbCriteria();
		$criteria->group = 't.category';
		$dataProvider = new CActiveDataProvider('TranslationMessageSource', array(
			'criteria' => $criteria,
			'pagination' => false,
		));
		$this->render('index', compact('dataProvider'));
	}

	public function actionCategory($category = null, $language = null) {
		if($language === null)
			$this->language = Yii::app()->language;
		else
			$this->language = $language;
		$criteria = new CDbCriteria();
		if($category !== null) {
			$this->category = $category;
			$criteria->compare('t.category', $category);
		}
		$criteria->with = array('translation' => array('params' => array(':language' => $this->language)));
		$dataProvider = new CActiveDataProvider('TranslationMessageSource', array(
			'criteria' => $criteria,
		));
		$this->render('category', compact('dataProvider', 'category'));
	}

	public function actionTranslate($id, $language) {
		/** @var TranslationMessageSource $source */
		$source = TranslationMessageSource::model()->findByPk($id);
		if($source === null) {
			throw new CException(404);
		}
		/** @var TranslationMessage $translation */
		$translation = $source->getRelated('translation', true, array('params' => array(':language' => $language)));
		if($translation === null) {
			$translation = new TranslationMessage();
			$translation->id = $source->id;
			$translation->language = $language;
			$translation->translation = Yii::app()->translator->translate($source->message, $language, Yii::app()->sourceLanguage);
		}
		if(isset($_POST['translation'])) {
			$_POST['TranslationMessage']['translation'] = $_POST['translation'];
			unset($_POST['translation']);
		}
		if(isset($_POST['TranslationMessage'])) {
			$translation->attributes = $_POST['TranslationMessage'];
			$translation->save();
		}
		echo $translation->translation;
		if(!Yii::app()->request->isAjaxRequest)
			die('only POST allowed for now');
	}

	public function actionAuto($language, $category = null) {
		$command = Yii::app()->db->commandBuilder->createSqlCommand("SELECT `message`, `category` FROM `message_source`");
		if($category !== null) {
			$command->text .= " WHERE `category` = :category";
			$command->bindValue(':category', $category);
			$this->category = $category;
		}
		$this->language = $language;
		$messages = $command->queryAll();
		$this->render('auto', compact('messages', 'language', 'category'));
	}

}