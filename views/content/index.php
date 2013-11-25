<?php
/**
 * /**
 * @var $this ContentController
 * @var $model RActiveRecord
 * @var $module RActiveRecord
 */

echo CHtml::beginForm($this->createUrl('change', compact('url')));
?>
    <div class="clearfix"></div>
    <div class="row"><?=
        CHtml::tag('div', array('class' => 'elements'), strtr('<span class="count">{count}</span>', array(
            '{from}' => 0,
            '{to}' => 0,
            '{count}' => $model->getDataProvider()->getTotalItemCount(),
        )))
        ?><?
        $this->widget('zii.widgets.CMenu', array(
            'id' => 'breadcrumbs',
            'items' => array(
                array('label' => 'RA-panel', 'url' => array('module/index')),
                array('label' => $module->groupName),
                array('label' => $module->name, 'url' => array('content/index', 'url' => $module->url)),
            ),
        ))
        ?>
    </div>

    <div class="row justi">
        <div class="checkbox"><?=
            CHtml::label(CHtml::checkBox('checkAll'), '')
            ?></div>
        <div class="search"><?=
            CHtml::textField('search', $_GET['search'], array('placeholder' => 'введите фразу для поиска')).
            CHtml::htmlButton('поиск', array(
                    'type' => 'submit',
                    'title' => 'найти')
            );
            ?></div>
        <div class="buttons"><?=
            CHtml::htmlButton('Добавить', array(
                'onclick' => 'modalIFrame(this)',
                'href' => $this->createUrl('edit', compact('url')),
                'title' => 'добавить запись',
            ))
            ?></div>
        <div style="display: none" class="go-page"><?=
            CHtml::htmlButton('назад', array('name' => 'prev', 'onclick' => 'goPage("prev")', 'disabled' => 1, 'title' => 'предыдущяя страница')) .
            CHtml::htmlButton('вперед', array('name' => 'next', 'onclick' => 'goPage("next")', 'disabled' => 1, 'title' => 'следующая страница'));
            ?></div>
        <div class="settings"><?=
            CHtml::htmlButton('настройка', array(
                    'onclick' => 'modalIFrame(this)',
                    'href' => $this->createUrl('module/config', compact('url')),
                    'title' => 'настроить отображение')
            );
            ?></div>

    </div>

    <div class="grid"><?
        $this->widget('ext.RSlickGrid.RSlickGrid' /*'zii.widgets.grid.CGridView'*/, array(
            'id' => 'contentGrid',
            'dataProvider' => $model->getDataProvider(),
            'columns' => $model->getColumns(),
            'htmlOptions' => array(
                'data-url' => $this->createUrl('saveOrder', compact('url')),
            ),
        ))
        ?></div>

    <div class="gridActions row">
        <? if (method_exists($model, 'status'))
            echo CHtml::dropDownList('status_id', null, $model::status(), array('empty' => 'сменить статус', 'onchange' => 'fastChange(this)'));
        ?>
    </div>
    <div class="clearfix"></div>
<?
echo CHtml::endForm();