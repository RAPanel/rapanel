<?php
/**
 * Created by Anatoly Rugalev <anatoly.rugalev@gmail.com>
 */

class ClearController extends RAdminController
{

	public function actionIndex()
	{
		throw new CHttpException(404);
	}

	public function actionAssets()
	{
		$this->deleteFilesRecursive(YiiBase::getPathOfAlias('webroot.assets'));
		Yii::app()->history->back(false, array('/admin/index'));
	}

	public function deleteFilesRecursive($path)
	{
		$files = scandir($path);
		foreach ($files as $file) {
			if (stripos($file, '.') === 0) continue;
			$fullPath = $path . '/' . $file;
			if(is_dir($fullPath)) {
				$this->deleteFilesRecursive($fullPath);
				@rmdir($fullPath);
			} else {
				@unlink($fullPath);
			}
		}
	}

	public function actionImages()
	{
		$path = YiiBase::getPathOfAlias('webroot.data');
		$types = scandir($path);
		foreach ($types as $type) {
			if (!is_dir($path . '/' . $type)) continue;
			if (stripos($type, '.') === 0) continue;
			if (stripos($type, '_') === 0) continue;
			foreach (CFileHelper::findFiles(YiiBase::getPathOfAlias("webroot.data.{$type}")) as $file) {
				@unlink($file);
			}
		}
		Yii::app()->history->back(false, array('/admin/index'));
	}

	public function actionCache()
	{
		/** @var $component CCache */
		foreach (Yii::app()->getComponents(false) as $component) {
			if (is_subclass_of($component, 'CCache')) {
				$component->flush();
			}
		}
		Yii::app()->history->back(false, array('/admin/index'));
	}

}