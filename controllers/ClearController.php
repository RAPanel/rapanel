<?php

/**
 * Created by Anatoly Rugalev <anatoly.rugalev@gmail.com>
 */
class ClearController extends RAdminController
{

    public function actionIndex()
    {
        $this->actionAssets(false);
        $this->actionImages(false);
        $this->actionAssets(false);

        $this->back();
    }

    public function actionAssets($back = true)
    {
        $this->deleteFilesRecursive(YiiBase::getPathOfAlias('webroot.assets'));

        if ($back) $this->back();
    }

    public function actionImages($back = true)
    {
        foreach (array_keys(Yii::app()->imageConverter->formats) as $val)
            $this->deleteFilesRecursive(YiiBase::getPathOfAlias('webroot.data.' . $val));

        if ($back) $this->back();
    }

    public function actionCache($back = true)
    {
        /** @var $component CCache */
        foreach (Yii::app()->getComponents(false) as $component)
            if (is_subclass_of($component, 'CCache'))
                $component->flush();
        $this->deleteFilesRecursive(YiiBase::getPathOfAlias('webroot.app.runtime.cache'));

        if ($back) $this->back();
    }

    public function deleteFilesRecursive($path)
    {
        $files = scandir($path);
        foreach ($files as $file) {
            if (stripos($file, '.') === 0) continue;
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullPath))
                CFileHelper::removeDirectory($fullPath);
            elseif (is_file($fullPath))
                @unlink($fullPath);
        }
    }

}