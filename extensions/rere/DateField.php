<?php
/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */

class DateField extends CInputWidget {

	public function run() {
		$timestamp = $this->model->{$this->attribute};
		if(!$timestamp)
			$timestamp = time();
		$formattedDate = date('Y-m-d', $timestamp);
		echo CHtml::activeDateField($this->model, $this->attribute, array('value' => $formattedDate));
	}

}