<?php

class AdminBehavior extends CActiveRecordBehavior
{

    public function getTypeFromList($name)
    {
//        'raw, text, ntext, html, date, time, datetime, boolean, number, email, image, url';
        switch($name):
            case 'status_id':
                return 'status';
            case 'user_id':
            case 'page_id':
            case 'type_id':
            case 'parent_id':
                return 'text';
        endswitch;

        if($this->owner->tableSchema->columns[$name]){
            $data = trim(current(explode('(', $this->owner->tableSchema->columns[$name]->dbType)));
        } elseif($this->owner->hasAttribute('module_id')){
            $map = Characters::map($this->owner->module_id, 'url', 'inputType');
            $data = $map[$name];
        }
        switch($data):
            case 'int':
            case 'smallint':
            case 'tinyint':
                return 'ntext';
            case 'textarea':
                return 'html';
            case 'varchar':
                return 'text';
            case 'timestamp':
                return 'date';
        endswitch;
        return $data;
    }

}