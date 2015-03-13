<?php

/**
 * Created by Anatoly Rugalev <anatoly.rugalev@gmail.com>
 */
class ClearController extends RAdminController
{

    public function actionIndex($back = true)
    {
        $this->actionState(false);
        $this->actionAssets(false);
//        $this->actionImages(false);
        $this->actionCache(false);

        if ($back) $this->back();
    }

    public function actionFast($back = true)
    {
        $this->actionState(false);
        $this->actionAssets(false);
        $this->actionCache(false);

        if ($back) $this->back();
    }

    public function actionState($back = true)
    {
        unlink(YiiBase::getPathOfAlias('application.runtime.state') . '.bin');

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