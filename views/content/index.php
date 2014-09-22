<?php
/**
 * /**
 * @var $this ContentController
 * @var $model RActiveRecord
 * @var $module RActiveRecord
 */
echo CHtml::beginForm($this->createUrl('index', compact('url') + array('type' => 'tree')));
$total = $model->contentBehavior->getDataProvider()->getTotalItemCount();
?>
    <div class="clearfix"></div>
    <div class="row"><?=
        CHtml::tag('div', array('class' => 'elements'), strtr('<span class="count">{count}</span> из <span class="total">{count}</span>', array(
            '{count}' => $model->contentBehavior->getDataProvider()->getTotalItemCount(),
        )))
        ?><?
        $additional = array();
        if ($_GET['parent_id']) {
            $parents = $model->findAll(array(
                'join' => 'JOIN page p ON(p.lft BETWEEN t.lft AND t.rgt AND p.id=:id)',
                'condition' => 't.lft>0 AND t.parent_id>0 AND t.module_id=:module_id',
                'order' => 't.lft',
                'params' => array('id' => $_GET['parent_id'], 'module_id' => $module->id),
            ));
            foreach ($parents as $row) $additional[] = array('label' => $row->name, 'url' => array('content/index', 'url' => $module->url, 'type' => $_GET['type'], 'parent_id' => $row->id));
        }
        $this->widget('zii.widgets.CMenu', array(
            'id' => 'breadcrumbs',
            'items' => CMap::mergeArray(array(
                    array('label' => 'RA-panel', 'url' => array('module/index')),
//                array('label' => $module->groupName, 'url' => array('module/index')),
                    array('label' => $module->name, 'url' => array('content/index', 'url' => $module->url)),
                ), $additional),
        ))
        ?>
    </div>

    <div class="row justi">
        <div class="leftBlock">
            <div class="checkbox"><?=
                CHtml::checkBox('checkAll', 0, array('id' => 'checkAll1')) . CHtml::label('', 'checkAll1')
                ?></div>
        </div>
        <div class="rightBlock">
            <div class="buttons"><?=
                CHtml::htmlButton('Добавить', array(
                    'onclick' => 'modalIFrame(this)',
                    'href' => $this->createUrl('edit', compact('url')),
                    'title' => 'добавить запись',
                ));
                $items = array(
                    array('label' => 'добавить запись', 'url' => array('edit') + compact('url'), 'linkOptions' => array('onclick' => 'modalIFrame(this);return false;')),
                    array('label' => 'добавить категорию', 'visible' => $module->type_id == Module::TYPE_NESTED, 'url' => array('edit', 'type' => 'category') + compact('url'), 'linkOptions' => array('onclick' => 'modalIFrame(this);return false;')),
                );
                $i = 0;
                foreach ($items as $row) if (!isset($row['visible']) || $row['visible'] == 1) $i++;
                if ($i > 1) $this->widget('zii.widgets.CMenu', array(
                    'htmlOptions' => array('class' => 'listAction'),
                    'items' => array(array('itemOptions' => array('class' => 'dropdown', 'data-toggle' => 'dropdown'), 'items' => $items)),
                ));?>
            </div>

            <? if (in_array($module->type_id, array(Module::TYPE_SELF_NESTED, Module::TYPE_NESTED)) && 0): ?>
                <div class="grid-type">
                    <?= CHtml::htmlButton('папки', array('class' => 'gridType', 'title' => 'Отображение по папкам', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' => $this->createUrl('', compact('url') + array('type' => 'folder')))) ?>
                    <? if ($module->type_id == Module::TYPE_NESTED) echo CHtml::htmlButton('записи', array('class' => 'gridType', 'title' => 'Отображение по записям', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' => $this->createUrl('', compact('url') + array('type' => 'items')))) ?>
                    <? if ($module->type_id == Module::TYPE_NESTED) echo CHtml::htmlButton('категории', array('class' => 'gridType', 'title' => 'Отображение по категориям', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' => $this->createUrl('', compact('url') + array('type' => 'cats')))) ?>
                    <? //=CHtml::htmlButton('все', array('class' => 'gridType', 'title' => 'Отображение деревом', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' =>  $this->createUrl('', compact('url') + array('type'=>'tree'))))?>
                </div>
            <? endif ?>

            <? if ($total > 1000): ?>
                <div class="go-page"><?=
                    CHtml::htmlButton('назад', array('class' => 'prev', 'name' => 'prev', 'onclick' => 'goPage("prev")', 'disabled' => $_GET['page'] < 2, 'title' => 'предыдущяя страница')) .
                    CHtml::htmlButton('вперед', array('class' => 'next', 'name' => 'next', 'onclick' => 'goPage("next")', 'disabled' => $_GET['page'] == ceil($total / 1000), 'title' => 'следующая страница'));
                    ?></div>
            <? endif ?>

            <div class="settings"><?=
                CHtml::htmlButton('настройка', array(
                        'onclick' => 'modalIFrame(this)',
                        'href' => $this->createUrl('module/config', compact('url')),
                        'title' => 'настроить отображение')
                );
                $items = array(
                    array('label' => 'настроить отображение', 'url' => array('module/config') + compact('url'), 'linkOptions' => array('onclick' => 'modalIFrame(this);return false;')),
                    array('label' => 'исправить индексы', 'visible' => in_array($module->type_id, array(Module::TYPE_SELF_NESTED, Module::TYPE_NESTED)), 'url' => array('fix', 'id' => $module->id, 'is_category' => $module->type_id == Module::TYPE_NESTED)),
                    array('label' => 'редактировать root', 'visible' => in_array($module->type_id, array(Module::TYPE_SELF_NESTED, Module::TYPE_NESTED)), 'url' => array('edit', 'url' => $url, 'id' => $module->config['parent_id']), 'linkOptions' => array('onclick' => 'modalIFrame(this);return false;')),
                );
                $i = 0;
                foreach ($items as $row) if (!isset($row['visible']) || $row['visible'] == 1) $i++;
                if ($i > 1) $this->widget('zii.widgets.CMenu', array(
                    'htmlOptions' => array('class' => 'listAction'),
                    'items' => array(array('itemOptions' => array('class' => 'dropdown', 'data-toggle' => 'dropdown'), 'items' => $items)),
                ));?>
            </div>
        </div>
        <div class="centerBlock">
            <div class="search"><?=
                CHtml::textField('search', $_GET['search'], array('placeholder' => 'введите фразу для поиска')) .
                CHtml::htmlButton('поиск', array(
                        'type' => 'submit',
                        'title' => 'найти')
                );
                ?></div>
        </div>
    </div>
    <div class="grid"><? $widget->run(); ?></div>

    <div class="gridActions row justi">
        <?
        //        if (method_exists($model, 'status'))
        //            echo CHtml::dropDownList('status_id', null, $model::status(), array('empty' => 'сменить статус', 'onchange' => 'fastChange(this)'));
        ?>
        <div class="actions">
            <div class="checkAll"><?=
                CHtml::checkBox('checkAll', 0, array('id' => 'checkAll2')) . CHtml::label('Для всех', 'checkAll2')
                ?>
            </div>
            <div class="edit-delete">
                <button type="button" class="edit"></button>
                <button type="button" class="delete"></button>
            </div>
            <div class="actionList">
                <ul>
                    <li>
                        <button type="button" class="hide">скрыть</button>
                    </li>
                    <li>
                        <button type="button" class="show">активировать</button>
                    </li>
                    <li>
                        <button type="button" class="edit">редактировать</button>
                    </li>
                    <li>
                        <button type="button" class="delete">удалить</button>
                    </li>
                </ul>
                <button type="button" class="action dropdown" data-toggle="dropdown">действия</button>
            </div>
        </div>
        <!--<div class="pagination">
            <button class="up"></button>
        </div>-->
    </div>
    <div class="clearfix"></div>
<?
echo CHtml::endForm();