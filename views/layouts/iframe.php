<?php
$this->beginContent('/layouts/scripts');
Yii::app()->clientScript->registerScript("iFrame", 'iFrameAutoResize();');
?>

    <div class="iframe-container" id="iframe">
        <?= $content ?>
    </div>

<?php $this->endContent() ?>