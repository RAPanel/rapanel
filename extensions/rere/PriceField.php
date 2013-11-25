<?php
/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */

class PriceField extends CInputWidget {

	public function run() {
		$value = $this->model->{$this->attribute};
		$this->htmlOptions['value'] = round($value / 100, 2);
		$this->htmlOptions['step'] = 0.01;
		echo CHtml::activeNumberField($this->model, $this->attribute, $this->htmlOptions);
	}

}