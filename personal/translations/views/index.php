<?php
$this->beginContent('layout');
?>

<div class="container">
	<div class="row">
		<div class="span12">
			<?php
			$this->widget('bootstrap.widgets.TbListView', array(
				'id' => 'categories-list',
				'itemView' => '_category',
				'dataProvider' => $dataProvider,
			));
			?>
		</div>
	</div>
</div>

<?php
$this->endContent();