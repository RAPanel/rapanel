<?php
/** @var RController $this */
$this->beginContent('/layouts/scripts');

/** @var CClientScript $cs */
$cs = Yii::app()->clientScript;
$cs->registerScript('fixedHeight', 'fixedHeight("aside.main, section.main", "header.main")');
?>

    <header class="main">
        <div class="logo"><?= CHtml::link('RA-panel '.Yii::app()->name, array('module/index')) ?></div>
        <nav class="menu">
            <? $this->widget('zii.widgets.CMenu', array(
                'id' => 'topMenu',
                'items' => array(
//                    array('label' => 'eng', 'url' => '#'),
                    array('label' => 'на сайт', 'url' => Yii::app()->homeUrl),
                    array('label' => 'сервис', 'url' => array('clear/index'), 'items' => array(
                        array('label' => 'очистить assets', 'url' => array('clear/assets'), 'visible' => Yii::app()->user->checkAccess('root')),
                        array('label' => 'очистить images', 'url' => array('clear/images'), 'visible' => Yii::app()->user->checkAccess('root')),
                        array('label' => 'очистить cache', 'url' => array('clear/cache')),
                    ), 'visible' => Yii::app()->user->checkAccess('administrator')),
                    array('label' => 'выход', 'url' => array('auth/logout'), 'visible' => !Yii::app()->user->isGuest),
                ),
            ))?>
        </nav>
        <div class="clearfix"></div>
    </header>

<? if (Yii::app()->user->checkAccess('moderator')): ?>
    <aside class="main">
        <div class="wrapper">
            <? $this->widget('rapanel.widgets.ModuleMenu') ?>
        </div>
        <div class="actions">
            <span class="hide"></span>
            <span class="resize"></span>
        </div>
    </aside>
<? endif ?>

    <section class="main">
        <div class="wrapper">
            <?= $content ?>
        </div>
    </section>

    <div class="contentLoading"></div>

<?
$this->widget('rapanel.widgets.FlashWidget');

$this->endContent();