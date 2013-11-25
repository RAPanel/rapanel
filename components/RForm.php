<?php

/**
 * Class RForm
 *
 * @property SiteModule $model
 */
class RForm extends CForm
{

	public $inputElementClass = 'RFormInputElement';

	public $activeForm = array('class' => 'RActiveForm');

	private $tabsRegexp = '#--\[([^\]]+)\]--#';

    public $showErrorSummary = true;

	/**
	 * @param SiteModule $model
	 * @param CBaseController $parent
	 * @param array $config
	 */
	public function __construct($model, $parent = null, $config = null)
	{
		$this->setModel($model);
		if ($config == null)
			$config = $this->buildConfig();
		parent::__construct($config, $model, $parent);
	}

	public function buildConfig()
	{
		if ($this->model instanceof SiteModule) {
			$attributes = $this->model->getFormAttributes(true);
		} else {
			$attributes = array();
		}
		$config = array(
			'showErrorSummary' => true,
		);
		$config['elements'] = $this->buildConfigElements($attributes);
		$config['buttons'] = array(
			'save' => array(
				'type' => 'submit',
				'label' => App::t('admin', 'Сохранить', 'ru'),
				'class' => 'btn btn-primary',
			),
			'saveClose' => array(
				'type' => 'submit',
				'label' => Yii::t('admin', 'Save & close'),
				'class' => 'btn btn-info',
			),
			'saveView' => array(
				'type' => 'submit',
				'label' => Yii::t('admin', 'Save & view'),
				'class' => 'btn btn-warning',
			),
			'saveCreate' => array(
				'type' => 'submit',
				'label' => Yii::t('admin', 'Save & create'),
				'class' => 'btn btn-success',
			),
		);
		return $config;
	}

	public function buildConfigElements($attributes) {
		$elements = array();
		foreach ($attributes as $attribute => $value) {
			if(is_array($value)) {
				$input = $value['input'];
				if (!$input) {
					$input = 'hidden';
				}
				if (is_array($input))
					$elements[$attribute] = $input;
				else
					$elements[$attribute]['type'] = $input;
			} else {
				if(preg_match($this->tabsRegexp, $value)) {
					$elements[] = $value;
				}
			}
		}
		return $elements;
	}

	public function render() {
		$form = parent::render();
		//Если имеются вкладки
		if(preg_match_all($this->tabsRegexp, $form, $matches)) {
			$tabNames = $matches[1];
			preg_match('#(<form\s[^>]+>)(.*)(<div class="control-group">.*</form>)#s', $form, $matches);
			$formParts = array($matches[1], $matches[2], $matches[3]);
			$tabsContent = preg_split($this->tabsRegexp, $formParts[1]);
			$tabs = array();
			foreach($tabNames as $i => $tabName) {
				$tabs[] = array(
					'label' => $tabName,
					'content' => $tabsContent[$i + 1],
					'active' => $i == 0,
				);
			}
			$tabsWidget = $this->parent->widget('bootstrap.widgets.TbTabs', array(
				'tabs' => $tabs,
			), 1);
			$form = $formParts[0] . $tabsContent[0] . $tabsWidget . $formParts[2];
		}
		return $form;
	}

	public function renderElements($elements = null)
	{
		if ($elements === null)
			$elements = $this->getElements();
		$output = '';
		foreach ($elements as $element) {
			$output .= $this->renderElement($element);
		}
		return $output;
	}

	public function renderElement($element)
	{
		if (is_string($element)) {
			if (($e = $this[$element]) === null && ($e = $this->getButtons()->itemAt($element)) === null)
				return $element;
			else
				$element = $e;
		}
		if ($element->getVisible()) {
			if ($element instanceof CFormInputElement) {
				if ($element->type === 'hidden')
					return "<div style=\"visibility:hidden\">\n" . $element->render() . "</div>\n";
				else
					return "<div class=\"control-group field_{$element->name}\">\n" . $element->render() . "</div>\n";
			} elseif ($element instanceof CFormButtonElement)
				return $element->render() . "\n"; else
				return $element->render();
		}
		return '';
	}

	public function renderButtons()
	{
		$output = '';
		foreach ($this->getButtons() as $button)
			$output .= $this->renderElement($button);
		return $output !== '' ? "<div class=\"control-group\"><div class=\"controls\">" . $output . "</div></div>\n" : '';
	}

}