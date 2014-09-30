<?php
$this->beginContent('/layouts/assets');
Yii::app()->clientScript->registerScript("iFrame", 'iFrameAutoResize();');
?>

    <div class="iframe-container" id="iframe">
        <?= $content ?>
    </div>

<?php $this->endContent() ?>