<?php

/**
 * Class TranslationMessageSource
 *
 * @property int $id
 * @property string $language
 * @property string $translation
 */

class TranslationMessage extends RActiveRecord {

	public function tableName() {
		return 'message_translation';
	}

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function relations() {
		return array(
			'source' => array(self::HAS_ONE, 'TranslationMessageSource', 'id'),
		);
	}

	public function rules() {
		return array(
			array('id, translation, language', 'safe'),
		);
	}

}