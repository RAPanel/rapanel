<?php

/**
 * Created by Anatoly Rugalev <anatoly.rugalev@gmail.com>
 */
class ClearController extends RAdminController
{

    public function actionIndex($back = true)
    {
        $this->actionAssets(false);
        $this->actionImages(false);
        $this->actionAssets(false);
        $this->actionState(false);

        if ($back) $this->back();
    }

    public function actionState($back = true)
    {
        $this->deleteFilesRecursive(YiiBase::getPathOfAlias('application.runtime.state') . '.bin');

        if ($back) $this->back();
    }

    public function actionAssets($back = true)
    {
        $this->deleteFilesRecursive(YiiBase::getPathOfAlias('webroot.assets'));

        if ($back) $this->back();
    }

    public function actionImages($back = true)
    {
        foreach (array_keys(Yii::app()->imageConverter->formats) as $val)
            if ($val[0] != '_')
                $this->deleteFilesRecursive(YiiBase::getPathOfAlias('webroot.data.' . $val));

        if ($back) $this->back();
    }

    public function actionCache($back = true)
    {
        $this->actionAssets(false);
        
        /** @var $component CCache */
        foreach (Yii::app()->getComponents(false) as $component)
            if (is_subclass_of($component, 'CCache'))
                $component->flush();
        $this->deleteFilesRecursive(YiiBase::getPathOfAlias('webroot.app.runtime.cache'));

        if ($back) $this->back();
    }

    public function deleteFilesRecursive($path)
    {
        if (file_exists($path)) CFileHelper::removeDirectory($path);
        mkdir($path);
    }

}