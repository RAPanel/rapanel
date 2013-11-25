<?php
/**
 * Class TranslationMessageSource
 *
 * @property int $id
 * @property string $category
 * @property string $message
 */

class TranslationMessageSource extends RActiveRecord {

	public function tableName() {
		return 'message_source';
	}

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function relations() {
		return array(
			'translations' => array(self::HAS_MANY, 'TranslationMessage', 'id'),
			'translation' => array(self::HAS_ONE, 'TranslationMessage', 'id', 'on' => 'translation.language = :language'),
		);
	}

}
