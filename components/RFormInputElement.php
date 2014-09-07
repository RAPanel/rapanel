<?php

class RFormInputElement extends CFormInputElement
{

	public static function getAdvancedTypes()
	{
		return array(
			'wysiwyg' => array(
				'class' => 'rere.tinymce.TinyMce',
				'fileManager' => array(
					'class' => 'rere.ElFinder.TinyMceElFinder',
					'connectorRoute' => 'admin/content/elfinder',
				),
			),
			'photo' => array(
				'class' => 'rere.RFineImageUploader.RFineImageUploader',
				'uploaderSkin' => 'single-image',
				'skin' => 'images',
				'viewFile' => '_image',
			),
			'tags' => array(
				'class' => 'rere.RTagIt.RTagIt',
				'autoCompleteUrl' => array('/site/autocomplete', 'source' => 'tags'),
				'options' => array(
					'placeholderText' => 'press enter to add',
					'autocomplete' => array(
						'delay' => 100,
					),
					'allowSpaces' => true,
				),
			),
			'boolean' => array(
				'class' => 'rapanel.extensions.rere.BooleanInput',
			),
			'checkboxlist' => array(
				'class' => 'rapanel.extensions.rere.RCheckboxList',
			),
			'autocomplete' => array(
				'class' => 'rere.RAutoComplete.RAutoCompleteWidget',
			),
			'select' => array(
				'class' => 'rapanel.extensions.rere.SelectInput',
			),
			'date' => array(
				'class' => 'rapanel.extensions.rere.DateField',
			),
			'price' => array(
				'class' => 'rapanel.extensions.rere.PriceField',
			),
		);
	}

	public function renderInput()
	{
		$advancedTypes = self::getAdvancedTypes();
		if (isset($advancedTypes[$this->type])) {
			$config = $advancedTypes[$this->type];
			$this->type = $config['class'];
			unset($config['class']);
			if (count($this->items)) {
				$this->attributes['items'] = $this->items;
			}
			$this->attributes = CMap::mergeArray($this->attributes, $config);
		}
		$input = parent::renderInput();
		return CHtml::tag('div', array('class' => 'controls'), $input);
	}

	public function renderLabel()
	{
		$options = array(
			'label' => $this->getLabel(),
			'required' => $this->getRequired()
		);

		if (!empty($this->attributes['id'])) {
			$options['for'] = $this->attributes['id'];
		}
		$options['class'] = $options['class'] ? $options['class'] . ' control-label' : 'control-label';

		return CHtml::activeLabel($this->getParent()->getModel(), $this->name, $options);
	}

}