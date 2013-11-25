<?php
/**
 * Created by Anatoly Rugalev <anatoly.rugalev@gmail.com>
 */

abstract class RPersonalController extends RAdminController {

	public $enabled = false;

	/**
	 * Функция должна возваращать название элемента в меню
	 * @return string
	 */
	public abstract function getMenuTitle();

	public function getViewPath() {
		return $this->module->getBasePath() . '/personal/' . $this->id . '/views';
	}

}