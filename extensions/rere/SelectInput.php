<?php
/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */

class SelectInput extends CInputWidget {

	public $data;

	public function run() {
		echo CHtml::activeDropDownList($this->model, $this->attribute, $this->data);
	}

}