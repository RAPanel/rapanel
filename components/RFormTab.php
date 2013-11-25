<?php

class RFormTab extends CForm {

	public $tabs = array();

	public function render() {
		die('ok');
		$tabs = array();
		foreach($this->tabs as $i => $tab) {
			$this->elements = $tab;
			$tabs[$i] = $this->renderElements();
		}
		$this->parent->widget('bootstrap.widgets.TbTabs', array(
			'id' => $this->getUniqueId(),
			'tabs' => $tabs,
		));
	}


}