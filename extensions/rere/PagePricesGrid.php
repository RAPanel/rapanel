<?php
/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */

class PagePricesGrid extends CInputWidget {

	public function run() {

	}

	public function getInputName($attribute) {
		return CHtml::activeName($this->model, $attribute);
	}

}