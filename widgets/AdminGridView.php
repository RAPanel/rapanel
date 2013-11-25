<?php
/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */

YiiBase::import('bootstrap.widgets.TbGridView');
class AdminGridView extends TbGridView
{

	public $pageSizes = array(20, 30, 40, 60, 100, 500);

	public $defaultPageSize = 20;

	public $pageSizerCssClass = 'pageSizer';

	public function getCurrentPageSize()
	{
		$value = Yii::app()->user->getState("AdminGridView.pageSize");
		if ($value <= 0)
			$value = $this->defaultPageSize;
		return $value;
	}

	public function init()
	{
		if ($this->enablePagination)
			$this->dataProvider->getPagination()->pageSize = $this->getCurrentPageSize();
		$selector = ".{$this->pageSizerCssClass} li.size-changer a, a.update-grid";
		$this->updateSelector = $this->updateSelector ? $this->updateSelector . ', ' . $selector : $selector;
		$this->applyFiltering();
		$this->applyColumnClasses();
		parent::init();
	}

	public function applyFiltering()
	{
		foreach ($this->columns as &$column) {
			if (is_array($column['filter']) && isset($column['filter']['class'])) {
				$widgetParams = $column['filter'];
				$widgetParams['model'] = $this->filter;
				unset($widgetParams['class']);
				$column['filter'] = $this->widget($column['filter']['class'], $widgetParams, 1);
			}
		}
	}

	public function applyColumnClasses()
	{
		foreach ($this->columns as $id => $column) {
			if (!isset($column['htmlOptions']['class']))
				$column['htmlOptions']['class'] = 'col-' . $id;
			if (!isset($column['headerHtmlOptions']['class']))
				$column['headerHtmlOptions']['class'] = 'col-' . $id;
			unset($column['relation']);
			$this->columns[$id] = $column;
		}
	}

	public function renderPageSizer()
	{
		$value = $this->getCurrentPageSize();
		$sizes = array();
		foreach ($this->pageSizes as $size) {
			$sizes[] = array('url' => array('content/pageSize', 'size' => $size), 'label' => $size, 'active' => $size == $value, 'itemOptions' => array('class' => 'size-changer'));
		}
		$this->widget('bootstrap.widgets.TbMenu', array(
			'items' => array(
				array('label' => $value, 'template' => Yii::t('admin', 'Page size:') . ' {menu}', 'items' => $sizes),
			),
			'htmlOptions' => array(
				'class' => $this->pageSizerCssClass,
			),
		));
	}

}