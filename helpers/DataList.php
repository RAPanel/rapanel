<?php
/**
 * Created by ReRe-Design.
 * User: Semyonchick
 * MailTo: webmaster@rere-design.ru
 */

class DataList
{
    static $remember;

    static function users($condition = null)
    {
        return self::getter(__METHOD__ . $condition, 'User', 'id', 'username', $condition);
    }

    static function modules($condition = null)
    {
        return self::getter(__METHOD__ . $condition, 'Module', 'id', 'name', $condition);
    }

    static function parentsTree($module_id)
    {
        $result = $parents = array();
        $data = Page::model()->findAll(array('with' => array('rName'), 'order' => 't.lft', 'condition' => 't.module_id=:module_id', 'params' => compact('module_id')));
        foreach ($data as $row) {
            $parents[$row->id] = $row->name;
            if (empty($parents[$row->parent_id])) $result[$row->id] = $row->name;
            else $result[$parents[$row->parent_id]][$row->id] = $row->name;
        }
        return $result;
    }

    static function getter($id, $object, $key, $value, $condition)
    {
        if (empty($remember[$id])) {
            $model = RActiveRecord::model($object);
            $sql = 'SELECT ' . $key . ', ' . $value . ' FROM `' . $model->tableName() . '`';
            if ($condition) $sql .= ' WHERE ' . $condition;
            $remember[$id] = CHtml::listData($model->findAllBySql($sql), $key, $value);
        }
        return $remember[$id];
    }

} 