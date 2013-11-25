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

    static function getter($id, $object, $key, $value, $condition)
    {
        if (empty($remember[$id])) {
            $user = RActiveRecord::model($object);
            $sql = 'SELECT ' . $key . ', ' . $value . ' FROM `' . $user->tableName() . '`';
            if ($condition) $sql .= ' WHERE ' . $condition;
            $remember[$id] = CHtml::listData($user->findAllBySql($sql), $key, $value);
        }
        return $remember[$id];
    }

} 