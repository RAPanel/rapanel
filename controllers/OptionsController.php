<?php
/**
 * @author ReRe Design studio
 * @email webmaster@rere-design.ru
 */

class OptionsController extends RAdminController {

	public function actionLanguage($id) {
		if(Yii::app()->hasComponent('languages')) {
			Yii::app()->languages->setCurrentLanguage($id);
		}
		Yii::app()->history->back(false, array('/admin/index'));
	}

}