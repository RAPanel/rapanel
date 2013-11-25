<?php

class WebstatController extends RAdminController {

	public function actionIndex($view = 'index') {
		include_once("webstat/" . $view . ".html");
	}

}