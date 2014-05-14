<?php
/**
 * /**
 * @var $this ContentController
 * @var $model RActiveRecord
 * @var $module RActiveRecord
 */
echo CHtml::beginForm($this->createUrl('index', compact('url')+array('type'=>'tree')));
?>
    <div class="clearfix"></div>
    <div class="row"><?=
        CHtml::tag('div', array('class' => 'elements'), strtr('<span class="count">{count}</span>', array(
            '{from}' => 0,
            '{to}' => 0,
            '{count}' => $model->contentBehavior->getDataProvider()->getTotalItemCount(),
        )))
        ?><?
        $additional = array();
        if($_GET['parent_id']){
            $parents = $model->findAll(array(
                'join'=>'JOIN page p ON(p.lft BETWEEN t.lft AND t.rgt AND p.id=:id)',
                'condition'=>'t.lft>0 AND t.parent_id>0 AND t.module_id=:module_id',
                'order'=>'t.lft',
                'params'=>array('id'=>$_GET['parent_id'], 'module_id'=>$module->id),
            ));
            foreach($parents as $row) $additional[] = array('label' => $row->name, 'url' => array('content/index', 'url' => $module->url, 'type' => $_GET['type'], 'parent_id'=>$row->id));
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
        <div class="checkbox"><?=
            CHtml::checkBox('checkAll', 0, array('id' => 'checkAll1')) . CHtml::label('', 'checkAll1')
            ?></div>
        <div class="search"><?=
            CHtml::textField('search', $_GET['search'], array('placeholder' => 'введите фразу для поиска')) .
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
            ));
            if ($module->type_id == Module::TYPE_NESTED) echo CHtml::htmlButton('+ категория', array(
                'onclick' => 'modalIFrame(this)',
                'href' => $this->createUrl('edit', compact('url') + array('type' => 'category')),
                'title' => 'добавить категорию',
            ));
            ?></div>
        <?if(in_array($module->type_id, array(Module::TYPE_SELF_NESTED, Module::TYPE_NESTED)) && 0):?>
        <div class="grid-type">
            <?= CHtml::htmlButton('папки', array('class' => 'gridType', 'title' => 'Отображение по папкам', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' =>  $this->createUrl('', compact('url') + array('type'=>'folder')))) ?>
            <?if($module->type_id==Module::TYPE_NESTED) echo CHtml::htmlButton('записи', array('class' => 'gridType', 'title' => 'Отображение по записям', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' =>  $this->createUrl('', compact('url') + array('type'=>'items'))))?>
            <?if($module->type_id==Module::TYPE_NESTED) echo CHtml::htmlButton('категории', array('class' => 'gridType', 'title' => 'Отображение по категориям', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' =>  $this->createUrl('', compact('url') + array('type'=>'cats'))))?>
            <?//=CHtml::htmlButton('все', array('class' => 'gridType', 'title' => 'Отображение деревом', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' =>  $this->createUrl('', compact('url') + array('type'=>'tree'))))?>
            </div>
    <?endif?>
        <div style="display: none" class="go-page"><?=
            CHtml::htmlButton('назад', array('class' => 'prev', 'name' => 'prev', 'onclick' => 'goPage("prev")', 'disabled' => 1, 'title' => 'предыдущяя страница')) .
            CHtml::htmlButton('вперед', array('class' => 'next', 'name' => 'next', 'onclick' => 'goPage("next")', 'disabled' => 1, 'title' => 'следующая страница'));
            ?></div>
        <div class="settings"><?=
            CHtml::htmlButton('настройка', array(
                    'onclick' => 'modalIFrame(this)',
                    'href' => $this->createUrl('module/config', compact('url')),
                    'title' => 'настроить отображение')
            );
            ?>
            <?if(in_array($module->type_id, array(Module::TYPE_SELF_NESTED, Module::TYPE_NESTED))) echo CHtml::htmlButton('fix', array('class' => 'gridType', 'title' => 'Исправить индексы', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' =>  $this->createUrl('fix', array('id'=>$module->id, 'is_category'=>$module->type_id == Module::TYPE_NESTED)))) ?>
            <?if(in_array($module->type_id, array(Module::TYPE_SELF_NESTED, Module::TYPE_NESTED))) echo CHtml::htmlButton('edit', array('class' => 'gridType', 'title' => 'редактировать root', 'onclick' => 'location.href=$(this).attr("data-href")', 'data-href' =>  $this->createUrl('edit', array('url'=>$url, 'id'=>$module->config['parent_id'])))) ?>

        </div>

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

    <div class="gridActions row justi" style="display: none">
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
                <button class="edit"></button>
                <button class="delete"></button>
            </div>
            <div class="actionList">
                <ul>
                    <li><a href="#">Создать</a></li>
                    <li><a href="#">Редактировать</a></li>
                    <li><a href="#">Удалить</a></li>
                </ul>
                <button class="action">действия</button>
            </div>
        </div>
        <div class="pagination" style="display: none">
            <div class="pager">
                <ul>
                    <li><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#">4</a></li>
                    <li><a href="#">5</a></li>
                    <li><a href="#">6</a></li>
                    <li><a href="#">7</a></li>
                    <li><a href="#">...</a></li>
                    <li><a href="#">22</a></li>
                </ul>
            </div>
            <button class="up"></button>
        </div>
    </div>
    <div class="clearfix"></div>
<?
echo CHtml::endForm();