<?php
/**
 * /**
 * @var $this ContentController
 * @var $model RActiveRecord
 * @var $module RActiveRecord
 */

Yii::app()->clientScript->registerScript('bannerCreate', 'bannerCreate()');

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$month = date('m') + $page - 1;
$t = strtotime(date('Y') . '-' . $month . '-01');
$time[0] = strtotime("first day last month", $t);
$time[1] = strtotime("first day next month", $t);
$days = array();
foreach (array($time[0], $t, $time[1]) as $row) {
    for ($i = 1; $i <= date('t', $row); $i++) {
        $days[] = $day = date('d-m-Y', $row + $i * 60 * 60 * 24);
        $items[$row][] = array('label' => $i, 'active' => $day == date('d-m-Y', time() + 60*60*24));
    }
    $items[0][] = array('label' => Yii::app()->dateFormatter->format('LLLL, yyyy', $row), 'items' => $items[$row]);
}

echo CHtml::beginForm($this->createUrl('change', compact('url')));
?>
<div class="clearfix"></div>

<div class="row justi">
    <div class="changeBlock">
        <?= CHtml::dropDownList('city', 'city', CHtml::listData(Yii::app()->db->createCommand('SELECT `id_region` `key`, `region_name_ru` `value` FROM `geo_region` WHERE `id_country`=1')->queryAll(), 'key', 'value'), array('class' => 'chosen-container', 'empty'=>'выберите регион')) ?>
        <?= CHtml::dropDownList('type', 'type', array_map('trim', explode(',', Yii::app()->db->createCommand('SELECT `data` FROM `character` WHERE id=54')->queryScalar())), array('class' => 'chosen-container', 'empty'=>'выберите тип')) ?>
    </div>
    <div class="dates">
        <div class="dateBlock">
            <? /*$this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name' => 'from',
                'language' => Yii::app()->language,
                'options' => array(
                    'dateFormat' => 'dd.mm.yy',
                    'changeMonth' => 'true',
                    'changeYear' => 'true',
                    'firstDay' => '1',
                ),
                'htmlOptions' => array('class' => 'datePicker'),
                'cssFile' => false))*/ ?>
        </div>
        <div class="dateBlock">
            <? /*$this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name' => 'to',
                'language' => Yii::app()->language,
                'options' => array(
                    'dateFormat' => 'dd.mm.yy',
                    'changeMonth' => 'true',
                    'changeYear' => 'true',
                    'firstDay' => '1',
                ),
                'htmlOptions' => array('class' => 'datePicker'),
                'cssFile' => false))*/ ?>
        </div>
    </div>
    <div class="action">
        <div class="buttons"><?=
            CHtml::htmlButton('Добавить', array(
                'onclick' => 'modalIFrame(this)',
                'href' => $this->createUrl('edit', compact('url')),
                'title' => 'добавить запись',
            ));
            if ($module->type_id == 1) echo CHtml::htmlButton('+ категория', array(
                'onclick' => 'modalIFrame(this)',
                'href' => $this->createUrl('edit', compact('url') + array('type' => 'category')),
                'title' => 'добавить категорию',
            ));
            ?></div>
        <div class="go-page"><?=
            CHtml::htmlButton('назад', array('class' => 'prev', 'name' => 'prev', 'data-href' => CHtml::normalizeUrl(array($this->action->id, 'url' => $url, 'page' => $page - 1)), 'onclick' => 'location.href=$(this).attr("data-href")', 'disabled' => $page < 2, 'title' => 'предыдущяя страница')) .
            CHtml::htmlButton('вперед', array('class' => 'next', 'name' => 'next', 'data-href' => CHtml::normalizeUrl(array($this->action->id, 'url' => $url, 'page' => $page + 1)), 'onclick' => 'location.href=$(this).attr("data-href")', 'title' => 'следующая страница'));
            ?></div>
        <div class="settings"><?=
            CHtml::htmlButton('настройка', array(
                    'onclick' => 'modalIFrame(this)',
                    'href' => $this->createUrl('module/config', compact('url')),
                    'title' => 'настроить отображение')
            );
            ?></div>
    </div>
</div>


<div class="gridBanners">
    <div class="mounth-block">
        <?
        $this->widget('zii.widgets.CMenu', array('items' => $items[0]))
        ?>
    </div>
    <?
    $data = $model->contentBehavior->getDataProvider(array(
        'condition' => '(rStartDate.value>:start AND rStartDate.value<:end) OR (rEndDate.value>:start AND rEndDate.value<:end)',
        'with' => array('rStartDate', 'rEndDate', 'rBannerPlace', 'rName'),
        'order' => 'rBannerPlace.value, rStartDate.value',
        'params' => array(
            'start' => strtotime("last day of last month", $time[0]),
            'end' => strtotime("first day of next month", $time[1]),
        ),
    ))->data;
    $list = array();
    foreach ($data as $row)
        $list /*[$row->bannerPlace]*/
        [] = array(
            'from' => array_search(date('d-m-Y', $row->startDate), $days),
            'to' => array_search(date('d-m-Y', $row->endDate), $days),
            'total' => count($days),
            'id' => $row->id,
            'name' => $row->name,
            'ico' => $row->getIco('micro', 'link'),
            'color' => sprintf('#%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255)),
        );

    Yii::app()->clientScript->registerScript('jsonData', 'var bannerData=' . CJavaScript::jsonEncode($list), CClientScript::POS_END);
    //        CVarDumper::dump($list, 10, 1);
    ?>
    <? foreach ($list as $key => $val): ?>

    <? endforeach ?>
</div>

<div class="clearfix"></div>
<?
echo CHtml::endForm();

/*<div class="line">
                <div class="item">
                    <div class="ico"></div>
                    <div class="title"><?= $val['name']*/?><!--</div>
</div>
</div>-->