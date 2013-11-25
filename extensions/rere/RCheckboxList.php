<?php
/**
 * Created by Anatoly Rugalev <anatoly.rugalev@gmail.com>
 */

class RCheckboxList extends CInputWidget
{

	public $items = array();

	public function run()
	{
		echo self::activeCheckBoxList($this->model, $this->attribute, $this->items, $this->htmlOptions);
	}

	public static function activeCheckBoxList($model, $attribute, $data, $htmlOptions = array())
	{
		$htmlOptions['template'] = "{input}\n{label}\n";
		$htmlOptions['separator'] = "\n";
		$htmlOptions['labelOptions'] = $htmlOptions['labelOptions'] ? $htmlOptions['labelOptions'] : array('class' => 'checkbox');
		$container = isset($htmlOptions['container']) ? $htmlOptions['container'] : 'div';
		unset($htmlOptions['container']);

		$checkboxList = CHtml::activeCheckBoxList($model, $attribute, $data, $htmlOptions);
		unset($htmlOptions['container'], $htmlOptions['template'], $htmlOptions['separator'], $htmlOptions['labelOptions'], $htmlOptions['baseID']);
		$output = CHtml::openTag($container, $htmlOptions);
		if (preg_match_all("#(<input [^>]+>)(:?\s(<label [^>]+>[^<]+</label>))?#", $checkboxList, $matches)) {
			foreach ($matches[2] as $i => $label) {
				$input = $matches[1][$i];
				if ($i == 0) { //hidden input
					$output .= $input;
					continue;
				}
				$labeledInput = preg_replace("#^\s<label([^>]+)>#", "<label $1>{$input}", $label);
				$output .= $labeledInput;
			}
		}
		$output .= CHtml::closeTag($container);
		return $output;
	}


}