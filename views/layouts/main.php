<?php
/** @var RController $this */
$this->beginContent('/layouts/scripts');

/** @var CClientScript $cs */
$cs = Yii::app()->clientScript;
$cs->registerScript('fixedHeight', 'fixedHeight("aside.main, section.main", "header.main")');
?>

    <header class="main">
        <div class="logo"><?= CHtml::link('RA-panel.sitename', array('module/index')) ?></div>
        <nav class="menu">
            <? $this->widget('zii.widgets.CMenu', array(
                'id' => 'topMenu',
                'items' => array(
                    array('label' => 'eng', 'url' => '#'),
                    array('label' => 'на сайт', 'url' => Yii::app()->homeUrl),
                    array('label' => 'сервис', 'url' => '#'),
                    array('label' => 'выход', 'url' => '#'),
                ),
            ))?>
        </nav>
        <div class="clearfix"></div>
    </header>

    <aside class="main">
        <div class="wrapper">
            <? $this->widget('admin.widgets.ModuleMenu') ?>
        </div>
        <div class="actions">
            <span class="hide"></span>
            <span class="resize"></span>
        </div>
    </aside>

    <section class="main">
        <div class="wrapper">
            <?= $content ?>
        </div>
    </section>

<div class="contentLoading"></div>

<?
$this->widget('admin.widgets.FlashWidget');

$this->endContent();