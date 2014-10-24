<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 29.08.14
 * Time: 9:58
 */

$this->beginContent('/layouts/assets');

?>

    <div class="auth">
        <header class="main">
            <div class="logo"><?= CHtml::link('RA-panel ' . Yii::app()->name, array('module/index')) ?></div>
            <div class="clearfix"></div>
        </header>

        <section class="main">
            <div class="wrapper">
                <?= $content ?>
            </div>
        </section>
    </div>

<?php

Yii::app()->clientScript->registerCss('body-fix', <<<CSS
html {
height: 100%;
}
body {
    overflow: visible;
    min-height: 100%;
}
CSS
);

$this->endContent();