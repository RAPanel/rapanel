<?php
/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */

class BooleanInput extends CInputWidget {

	public function run() {
		echo CHtml::activeDropDownList($this->model, $this->attribute, array(
			0 => Yii::t('general', 'No'),
			1 => Yii::t('general', 'Yes'),
		));
	}

}