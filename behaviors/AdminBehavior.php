<?php

class AdminBehavior extends CActiveRecordBehavior
{

	private $_ownerColumns;

	private $_ownerFormAttributes;

	private $_ownerRules;

	public $module;

	public $formId;

	public $gridId;

	private $_photoData;

	public function attach($owner) {
		$this->attachPhotoBehaviors($owner);
		parent::attach($owner);
	}

	public function getColumns($order = null, $getHidden = true)
	{
		$columns = array();
		$attributes = array_keys($this->getOwnerColumns());
		if (!count($attributes))
			$attributes = $this->owner->attributeNames();
		$time = microtime(true);
		foreach ($attributes as $i => $attribute) {
			if ($column = $this->getColumn($attribute)) {
				$columns[$attributes[$i]] = $column;
			}
		}
		if($order === true) {
			$order = $this->getModule()->config['columns'];
		}

		if (is_array($order)) {
			$orderedColumns = array();
			foreach ($order as $columnId) {
				if (isset($columns[$columnId]))
					$orderedColumns[$columnId] = $columns[$columnId];
			}
			if ($getHidden) {
				$notIncluded = array_diff(array_keys($columns), array_keys($orderedColumns));
				foreach ($notIncluded as $columnId)
					if (isset($columns[$columnId]))
						$orderedColumns[$columnId] = $columns[$columnId];
			}
		} else {
			$orderedColumns = $columns;
		}

		return $orderedColumns;
	}

	public function getModule() {
		return $this->module;
	}

	public function getActionsColumn()
	{
		$module = $this->module;
		$gridId = $this->gridId;
		$formId = false;
		if ($gridId) {
			$grids = $this->owner->getGridNames($module);
			$formId = $grids[$gridId]['form'];
		}
		return array(
			'actions' => array(
				'header' => 'Действия',
				'class' => 'bootstrap.widgets.TbButtonColumn',
				'template' => '{view}{edit}{delete}',
				'buttons' => array(
					'view' => array(
						'label' => App::t('admin', 'View'),
						'icon' => 'eye-open',
						'url' => function ($data) {
							return $data->getHref();
						},
						'visible' => function ($row, $data) {
							if (!method_exists($data, 'getHref'))
								return false;
							return $data->getHref() !== false;
						},
						'options' => array(
							'target' => '_blank',
						),
					),
					'edit' => array(
						'label' => App::t('admin', 'Edit'),
						'icon' => 'pencil',
						'url' => function ($data) use ($module, $formId) {
							if ($formId)
								return array('content/edit', 'url' => $module->url, 'id' => $data->id, 'formId' => $formId);
							else
								return array('content/edit', 'url' => $module->url, 'id' => $data->id);
						},
						'options' => array(
							'class' => 'modal-trigger',
						),
					),
					'delete' => array(
						'label' => App::t('admin', 'Delete'),
						'icon' => 'trash',
						'url' => function ($data) use ($module) {
							return array('content/delete', 'url' => $module->url, 'id' => $data->id);
						},
					),
				)
			)
		);
	}

	public function getColumnType($attribute)
	{
		/** @var RActiveRecord $owner */
		$owner = $this->getOwner();
		$column = $owner->getTableSchema()->getColumn($attribute);
		if ($column === null) {
			$type = 'varchar';
		} else {
			$type = $column->dbType;
			preg_match('#^([a-z]+)#i', $type, $matches);
			$type = $matches[1];
		}
		return $type;
	}

	public function getRenderFunction($attribute, $type)
	{
		switch ($type) {
			case 'int':
				return function ($data) use ($attribute) {
					return CVarDumper::dumpAsString((int)$data->$attribute, 1, 1);
				};
			case 'date':
				return function ($data) use ($attribute) {
					return CVarDumper::dumpAsString((int)$data->$attribute, 1, 1);
				};
			case 'datetime':
			case 'timestamp':
				return function ($data) use ($attribute) {
					$timestamp = strtotime($data->$attribute);
					if ($timestamp == 0) {
						return Yii::t('site', 'Not Set');
					}
					return Yii::app()->dateFormatter->formatDatetime($timestamp);
				};
			case 'text':
			case 'varchar':
			case 'char':
			default:
				return function ($data) use ($attribute) {
					return $data->$attribute;
				};
		}
	}

	public function getOwnerColumns()
	{
		if (isset($this->_ownerColumns)) {
			return $this->_ownerColumns;
		}
		if (method_exists($this->getOwner(), 'columns')) {
			/** @noinspection PhpUndefinedMethodInspection */
			$this->_ownerColumns = $this->getOwner()->columns();
		} else {
			$this->_ownerColumns = array();
		}
		return $this->_ownerColumns;
	}

	public function hasOrderingColumn()
	{
		if ($this->getOwner() instanceof ISortableModule)
			return $this->getOwner()->getOrderingKey();
		else
			return false;
	}

	public function getOrderingColumn($attributeName)
	{
		$sorted = true;
		$module = $this->module;
		if ($sorted) {
			return array(
				'name' => $attributeName,
				'filter' => false,
				'type' => 'raw',
				'value' => function ($data) use ($module) {
					$level = method_exists($data, 'getNestingLevel') ? $data->getNestingLevel() : 0;
					$arrows =
						CHtml::link(
							CHtml::tag('i', array('class' => 'icon-arrow-up'), ''),
							array('content/move', 'url' => $module->url, 'id' => $data->id, 'direction' => 'up'),
							array('class' => 'update-grid')
						) . ' ' .
						CHtml::link(
							CHtml::tag('i', array('class' => 'icon-arrow-down'), ''),
							array('content/move', 'url' => $module->url, 'id' => $data->id, 'direction' => 'down'),
							array('class' => 'update-grid')
						);
					if($level > 0) {
						$arrows = CHtml::tag('span', array('class' => 'nesting-level-' . $level), $arrows);
					}
					return $arrows;
				},
			);
		} else {
			return false;
		}
	}

	public function setPhotoData($value) {
		$this->_photoData = $value;
	}

	public function getPhotoData() {
		 return $this->_photoData;
	}

	public function hasPhoto($owner = null) {
		if($owner === null)
			$owner = $this->getOwner();
		return isset($owner->photoLimit) && $owner->photoLimit >= 0;
	}

	public function attachPhotoBehaviors($owner) {
		if($this->hasPhoto($owner)) {
			$behaviors =  array(
				'photoSave' => array(
					'class' => 'core.behaviors.PhotoSaveBehavior',
					'relationName' => 'photos',
					'attribute' => 'photoData',
					'relationType' => CActiveRecord::HAS_MANY,
					'limit' => $owner->photoLimit,
				),
			);
			$owner->attachBehaviors($behaviors);
		}
	}

	public function hasUrl() {
		return $this->getOwner() instanceof PageModule && isset($this->getOwner()->allowUrl) && $this->getOwner()->allowUrl;
	}

	public function getColumn($attribute)
	{
		$columns = $this->getOwnerColumns();
		if (count($columns)) {
			if (isset($columns[$attribute])) {
				$sortAttribute = $this->hasOrderingColumn();
				$column = false;
				if ($attribute == $sortAttribute) {
					$column = $this->getOrderingColumn($attribute);
				}
				if (!$column)
					$column = $columns[$attribute];
				return $column;
			} else
				return false;
		}
		$config = array();
		$config['header'] = $this->owner->getAttributeLabel($attribute);
		$type = $this->getColumnType($attribute);
		$config['value'] = $this->getRenderFunction($attribute, $type);
		$config['type'] = 'raw';

		return $config;
	}


	/**
	 *
	 * @return string
	 * @deprecated (Use RForm)
	 */
	public function buildForm()
	{
		$formFile = get_class($this->getOwner());
		$formPath = YiiBase::getPathOfAlias('admin.views.forms') . $formFile . '.php';
		if (file_exists($formPath)) {
			return Yii::app()->controller->renderPartial($formPath, array('model' => $this->getOwner()), true);
		} else {
			$attributes = $this->getFormAttributes();
			$elements = array();
			foreach ($attributes as $attribute => $config) {
				$input = $this->getFormInput($config['input']);
				$elements[$attribute] = $input;
			}
			$form = new CForm(array(
				'elements' => $elements,
			), $this->getOwner());
			return $form->render();
		}
	}

	public function buildRules()
	{
		$attributes = $this->getFormAttributes(false);
		$rules = array();
		foreach ($attributes as $attribute => $config) {
			if (is_array($config)) {
				if (isset($config['rules']))
					foreach ($config['rules'] as $rule)
						$rules[] = CMap::mergeArray(array($attribute), $this->getFormRule($rule));
				else
					$rules[] = array($attribute, 'safe');
			}
		}
		$this->_ownerRules = $rules;
	}

	public function setDefaults()
	{
		$attributes = $this->getFormAttributes(false);
		foreach ($attributes as $attribute => $config) {
			if (isset($config['default']) && $this->getOwner()->hasProperty($attribute)) {
				$this->getOwner()->$attribute = $config['default'];
			}
		}
	}

	protected function getRules()
	{
		$this->setDefaults();
		$this->buildRules();
		return $this->_ownerRules;
	}

	/**
	 * @param array $value
	 * @return array
	 */
	public function getArrayResult($value)
	{
		if (!$value)
			return false;
		// Если это функция, то вызываем её
		if (is_array($value))
			return $value;
		return $this->evaluateExpression($value);
	}

	public function getFormInput($input)
	{
		return $input;
	}

	/**
	 * Возвращает правило для метода модели rules()
	 *
	 * Есть несколько предустановленных правил и шаблонов для компактности описания в модели
	 *
	 * Так же можно определять регулярные выражения в разделителях "#". Если поставить "!" перед перовой "#", то
	 * будут валидироваться только несовпадения
	 *
	 * Если строка в формате "a|b|c", то будет применено range правило
	 *
	 * Если строка в формате "{1,5}" или "{25}", то будет применено length правило
	 *
	 * @param $rule
	 * @return array
	 */
	public function getFormRule($rule)
	{
		if (is_string($rule)) {
			switch ($rule) {
				case 'required':
					return array('required');
				case 'safe':
					return array('safe');
				case 'unique':
					return array('unique');
				case 'email':
					return array('email');
				case 'int':
					return array('numerical', 'integerOnly' => true);
			}
			if (preg_match('#^.*\|.*$#', $rule)) {
				$parts = explode('|', $rule);
				return array('in', 'range' => $parts);
			}
			if (preg_match('#^\#.*\#$#', $rule)) {
				return array('match', 'pattern' => $rule);
			}
			if (preg_match('#^!\#.*\#$#', $rule)) {
				$pattern = preg_replace('#^!#', '', $rule);
				return array('match', 'pattern' => $pattern, 'not' => true);
			}
			if (preg_match('#^\{(:?(\d+)?,\s?(\d+)?)|(\d+)\}$#', $rule, $matches)) {
				$min = (int)$matches[2];
				$max = is_numeric($matches[3]) ? (int)$matches[3] : false;
				$full = is_numeric($matches[4]) ? (int)$matches[4] : false;
				$rule = array('length');
				if ($min)
					$rule['min'] = $min;
				if ($max !== false)
					$rule['max'] = $max;
				if ($full !== false)
					$rule['max'] = $rule['min'] = $full;
				return $rule;
			}
			return array('safe');
		}
		return $rule;
	}

	public function getFormAttributes($fetchData = true)
	{
		$this->_ownerFormAttributes = array();
		if ($this->formId === null) {
			if (method_exists($this->getOwner(), 'formAttributes')) {
				/** @noinspection PhpUndefinedMethodInspection */
				$this->_ownerFormAttributes = $this->getOwner()->formAttributes(array(), $fetchData);
				if($this->hasUrl() && !isset($attribute['urlString'])) {
					$this->_ownerFormAttributes['urlString'] = array(
						'input' => 'text',
						'rules' => array(
							'safe',
						),
					);
				}
				if($this->hasPhoto() && !isset($attribute['photoData'])) {
					$this->_ownerFormAttributes[] = '--[Photo]--';
					$this->_ownerFormAttributes['photoData'] = array(
						'input' => array(
							'type' => 'photo',
							'relationName' => 'photos',
							'limit' => $this->getOwner()->photoLimit,
						),
						'rules' => array('safe'),
					);
				}
			}
		} else {
			$forms = $this->getAdditionalForms($fetchData);
			if (isset($forms[$this->formId]['attributes']))
				$this->_ownerFormAttributes = $forms[$this->formId]['attributes'];
		}
		return $this->_ownerFormAttributes;
	}

	public function getAdditionalForms($fetchData = true)
	{
		if (method_exists($this->getOwner(), 'additionalForms')) {
			/** @noinspection PhpUndefinedMethodInspection */
			$forms = $this->getOwner()->additionalForms($this->getModule(), $fetchData);
		} else {
			$forms = array();
		}
		return $forms;
	}

}