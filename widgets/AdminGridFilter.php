<?php
/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */

YiiBase::import('rere.RAutoComplete.RAutoCompleteAction');

class AdminGridFilter extends CInputWidget {

	public function run() {
		if($this->model instanceof PageModule) {
			$characters = $this->model->characters;
			if($characters->contains($this->attribute)) {
				$id = Character::idMap($this->attribute);
				$type = Character::typeMap($id);
				$inputType = Character::inputTypeMap($id);
				if($inputType == 'boolean') {
					echo CHtml::activeDropDownList($this->model, $this->attribute, array('' => '', '0' => App::t('general', 'No'), '1' => App::t('general', 'Yes')));
					return;
				} elseif($type == 'varchar' || $type == 'int') {
					$source = "character[type={$this->attribute}]";
					$this->widget('rere.RAutoComplete.RAutoCompleteWidget', array(
						'model' => $this->model,
						'attribute' => $this->attribute,
						'source' => $source,
					));
					return;
				}
			}
		}
		echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
	}



}