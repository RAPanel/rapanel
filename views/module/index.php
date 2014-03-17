<?php
/**
 * @var $this ModuleController
 * @var $model Module
 */

echo CHtml::htmlButton('Создать новый модуль', array(
    'onclick' => 'modalIFrame(this)',
    'data-update' => 'modulesGrid',
    'data-url' => $this->createUrl('edit'),
));

echo CHtml::beginForm(array('change'));

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'modulesGrid',
    'cssFile' => null,
    'dataProvider' => $model->getDataProvider(),
    'columns' => $model->getColumns(),
    'htmlOptions' => array(
        'data-url' => $this->createUrl('saveOrder'),
    ),
));

if (method_exists($model, 'status'))
    echo CHtml::dropDownList('status_id', null, $model::status(), array('empty' => 'сменить статус', 'onchange' => 'fastChange(this)'));

echo CHtml::endForm();