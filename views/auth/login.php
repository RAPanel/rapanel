<div class="loginPage">
	<div class="alert alert-info">
		<h3><?=Yii::t('admin', 'Site managment system') ?></h3>

		<p><?=Yii::t('admin', 'Enter your authentication data') ?></p>
	</div>
	<?php
	/**
	 * @var AuthController $this
	 * @var TbActiveForm $form
	 */
	$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
		'id' => 'login-form',
		'type' => 'horizontal',
		'htmlOptions' => array(
			'class' => 'well'
		)
	)) ?>
	<div class="row">
		<div class="control-group">
			<div class="input-prepend">
				<span class="add-on"><i class="icon-envelope"></i></span>
				<?php echo $form->textField($model, 'email', array('class' => 'enter-submit')) ?>
			</div>
			<?php echo $form->error($model, 'email') ?>
		</div>
		<div class="control-group">
			<div class="input-prepend">
				<span class="add-on"><i class="icon-barcode"></i></span>
				<?php echo $form->passwordField($model, 'password', array('class' => 'enter-submit')) ?>
			</div>
			<?php echo $form->error($model, 'password') ?>
		</div>
		<div class="control-group">
			<?php echo $form->hiddenField($model, 'rememberMe') ?>
			<div class="btn-group">
				<button tabindex="-1" class="btn btn-info <?=$model->rememberMe ? 'active' : ''?>" data-toggle="button" onclick="$('#LoginForm_rememberMe').val($(this).hasClass('active') ? '0' : '1'); return false;"><?php echo $form->labelEx($model, 'rememberMe') ?></button>
				<?php $this->widget('bootstrap.widgets.TbButton', array(
					'buttonType' => 'submit',
					'type' => 'success',
					'label' => App::t('admin', 'Login'),
					'icon' => 'icon-play',
				)); ?>
			</div>
		</div>
	</div>
	<?php $this->endWidget() ?>
</div>