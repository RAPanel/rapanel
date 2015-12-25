<?php
/**
 * @var $this ModuleController
 * @var $model Module
 */

echo CHtml::beginForm(array('change'));
?>
    <div class="clearfix"></div>

    <div class="row justi">
        <div class="leftBlock">
            <div class="checkbox"><?=
                CHtml::checkBox('checkAll', 0, array('id' => 'checkAll1')) . CHtml::label('', 'checkAll1')
                ?></div>
        </div>
        <div class="rightBlock">
            <div class="buttons"><?=
                CHtml::htmlButton('Создать новый модуль', array(
                    'onclick' => 'return modalIFrame(this)',
                    'href' => $this->createUrl('edit'),
                )); ?>
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
<? echo CHtml::hiddenField('search', ''); ?>

    <div class="grid"><?= $widget->run(); ?></div>

    <div class="gridActions row justi">
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