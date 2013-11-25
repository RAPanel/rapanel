<?php
Yii::app()->clientScript->registerScriptFile($this->assetsDir . '/translations.js');
Yii::app()->clientScript->registerCssFile($this->assetsDir . '/translations.css');
?>
	<div class="container">
		<h2><?= Yii::t('translations', 'Translations') ?></h2>

		<div class="row">
			<div class="span6">
				<?php
				$link = array('/admin/translations/auto', 'language' => $this->language);
				if(isset($this->category)) {
					$link['category'] = $this->category;
				}
				?>
				<?= CHtml::label(Yii::t('site', 'Language'), 'language'); ?>
				<?= CHtml::link(Yii::t('site', 'Auto translation'), $link, array('class' => 'btn btn-info pull-right')) ?>
				<?= CHtml::dropDownList('language', $this->language, Yii::app()->translator->getListData($this->language)); ?>
			</div>
		</div>

		<div class="row">
			<div class="span12">
				<?php
				echo $content;
				?>
			</div>
		</div>
	</div>