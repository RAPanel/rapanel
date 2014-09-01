<?php

class InstallController extends CController
{
    public $lt;
    public $update;

    private $int = 'int(11) unsigned NOT NULL';
    private $pk = 'int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY';
    private $binary = 'binary(8) NOT NULL';
    private $tinyint = 'tinyint(3) unsigned NOT NULL';
    private $smallint = 'smallint(6) unsigned NOT NULL';
    private $lang = 'char(2) NOT NULL';
    private $lastmod = 'timestamp NOT NULL';

    public function actionIndex()
    {
        $this->createTable('lang', array(
            'id' => $this->lang . ' PRIMARY KEY',
            'name' => 'varchar(50) NOT NULL',
            'tooltip' => 'varchar(255) NOT NULL',
        ), array(
            "'en','English','English'",
            "'ru','Russian','Русский'",
        ));

        $this->createTable('cache', array(
            'id' => 'char(128) NOT NULL PRIMARY KEY',
            'expire' => $this->int,
            'value' => 'longblob NOT NULL',
            'KEY `expire` (`expire`)',
        ));

        $this->createTable('config', array(
            'id' => $this->pk,
            'module_id' => $this->int,
            'category' => 'varchar(64) NOT NULL',
            'name' => 'varchar(128) NOT NULL',
            'value' => 'varchar(256) NOT NULL',
            'lastmod' => $this->lastmod,
        ));

        $this->createTable('session', array(
            'id' => 'char(32) NOT NULL PRIMARY KEY',
            'expire' => $this->int,
            'data' => 'longblob',
            'KEY `expire` (`expire`)',
        ));

        $this->createTable('message_source', array(
            'id' => $this->pk,
            'language' => $this->lang,
            'category' => 'varchar(32) DEFAULT NULL',
            'message' => 'text',
            'UNIQUE KEY `category_message` (`category`,`message`(255),`language`)',
            'KEY `language` (`language`)',
            'KEY `category` (`category`)',
            $this->fk('language', 'lang'),
        ));

        $this->createTable('message_translation', array(
            'id' => $this->int,
            'language' => $this->lang,
            'translation' => 'text',
            'PRIMARY KEY (`id`,`language`)',
            'KEY `id` (`id`)',
            'KEY `language` (`language`)',
            $this->fk('id', 'message_source'),
            $this->fk('language', 'lang'),
        ));

        $this->createTable('statistic', array(
            'id' => $this->pk,
            'time' => 'float(6,3) unsigned NOT NULL',
            'memory' => 'float(6,3) unsigned NOT NULL',
            'cpu' => 'float(6,3) unsigned NOT NULL',
            'referrer_id' => $this->int,
            'page_id' => $this->int,
            'session' => 'char(32) NOT NULL',
            'ip' => 'varchar(50) NOT NULL',
            'url' => 'varchar(500) NOT NULL',
            'url_referrer' => 'varchar(500) NOT NULL',
            'user_agent' => 'varchar(500) NOT NULL',
            'lastmod' => $this->lastmod,
            'KEY `session` (`session`)',
            'KEY `lastmod` (`lastmod`)',
        ));

        $this->createTable('module', array(
            'id' => $this->pk,
            'num' => $this->int,
            'status_id' => $this->tinyint,
            'type_id' => $this->tinyint,
            'name' => 'varchar(32) NOT NULL',
            'access' => 'varchar(64) NOT NULL',
            'url' => 'varchar(30) NOT NULL',
            'className' => 'varchar(255) NOT NULL',
            'groupName' => 'varchar(255) NOT NULL',
            'lang_id' => $this->lang,
            'lastmod' => $this->lastmod,
            'UNIQUE KEY `url` (`url`)',
            'KEY `num` (`num`)',
            'KEY `groupName` (`groupName`)',
        ), array(
            "null,0,1,0,'Основные страницы','administrator','page','Page','Страницы сайта','ru',null",
            "null,0,0,0,'Статьи','administrator','article','Page','Страницы сайта','ru',null",
            "null,0,0,0,'Слайдер','administrator','slider','Page','Страницы сайта','ru',null",
            "null,0,0,0,'Новости','administrator','news','Page','Страницы сайта','ru',null",
            "null,0,0,0,'Пользователи','administrator','users','User','Посетители','ru',null",
            "null,0,0,0,'Компании','administrator','company','Page','Посетители','ru',null",
            "null,0,0,0,'Подписка','administrator','subscribe','Subscription','Посетители','ru',null",
            "null,0,0,0,'Уведомления','administrator','notification','Notification','Посетители','ru',null",
            "null,0,0,0,'Опросы','administrator','interview','Page','Посетители','ru',null",
            "null,0,0,0,'Товары','administrator','catalog','Product','Магазин','ru',null",
            "null,0,0,0,'Заказы','administrator','order','Order','Магазин','ru',null",
            "null,0,0,0,'Брэнды','administrator','brand','Page','Магазин','ru',null",
            "null,0,0,0,'Акции','administrator','stock','Page','Магазин','ru',null",
            "null,0,1,0,'Характеристики','administrator','characteristics','Character','Настройки','ru',null",
            "null,0,0,0,'Сообщения и переводы','administrator','translater','MessageTranslate','Настройки','ru',null",
            "null,0,0,0,'Параметры','administrator','config','Config','Настройки','ru',null",
        ));

        $this->createTable('user', array(
            'id' => $this->pk,
            'username' => 'varchar(255) NOT NULL',
            'email' => 'varchar(255) NOT NULL',
            'password' => 'varchar(255) NOT NULL',
            'role' => 'varchar(32) NOT NULL',
            'lastmod' => $this->lastmod,
            'created' => $this->lastmod,
            'UNIQUE KEY `email` (`email`)',
            'KEY `username` (`username`)',
            'KEY `role` (`role`)',
        ), array(
            "null,'Семен Сушенцев','semyonchick@gmail.com','" . '$2a$13$/WoHDNmN87m1zVSg7a8Cpu1IfRCvZof0K9Z1FW7QraM06I.aJZ9c2' . "','root',null,null",
            "null,'Валерия Сушенцева','mail@rere-design.ru','" . '$2a$13$tUzrpV1b3OEWizFT1vfeQuc9VtaBvLf5KMyFTYnxjNh4e9PSdhvE.' . "','root',null,null",
            "null,'RUgaleFF','anatoly.rugalev@gmail.com','" . '$2a$10$tzpV5NBnTyv6dxxXJySys.64Xgy4OOJGwN34QfBX4YqwII4CYBGUq' . "','root',null,null",
            "null,'Денис','denart89@gmail.com','" . '$2a$13$SMxuEoXwpmTXmS5xXQmvIe0BETFquft84dbWXYrcmcl04.A4QWOli' . "','root',null,null",
            "null,'Valentina E','v.a.elokhova@gmail.com','" . '$2a$13$StKF31EpRWWLUcs3RbvCx.dN9mdCf9NXK3D4E/idLz0A/L1gDmeO6' . "','root',null,null",
        ));

        $this->createTable('page', array(
            'id' => $this->pk,
            'user_id' => $this->int,
            'status_id' => $this->tinyint,
            'module_id' => $this->int,
            'parent_id' => $this->int,
            'lft' => $this->int,
            'rgt' => $this->int,
            'level' => $this->tinyint,
            'is_category' => $this->tinyint,
            'lang_id' => $this->lang,
            'lastmod' => $this->lastmod,
            'created' => $this->lastmod,
            'KEY `id_user` (`id`,`user_id`)',
            'KEY `nested` (`lft`,`rgt`,`level`)',
            'KEY `user_id` (`user_id`)',
            'KEY `status_id` (`status_id`)',
            'KEY `module_id` (`module_id`)',
            'KEY `parent_id` (`parent_id`)',
            'KEY `lft` (`lft`)',
            'KEY `rgt` (`rgt`)',
            'KEY `level` (`level`)',
            'KEY `is_category` (`is_category`)',
            'KEY `lang_id` (`lang_id`)',
            'KEY `lastmod` (`lastmod`)',
            'KEY `created` (`created`)',
            $this->fk('user_id', 'user'),
            $this->fk('module_id', 'module'),
            $this->fk('lang_id', 'lang'),
        ));

        $this->createTable('character', array(
            'id' => $this->pk,
            'num' => $this->int,
            'url' => 'varchar(30) NOT NULL',
            'type' => 'varchar(30) NOT NULL',
            'name' => 'varchar(32) NOT NULL',
            'inputType' => 'varchar(50) NOT NULL',
            'position' => 'varchar(50) NOT NULL',
            'filter' => 'varchar(10) NOT NULL',
            'data' => 'text NOT NULL',
            'lang_id' => 'char(5) NOT NULL',
            'lastmod' => $this->lastmod,
            'KEY `num` (`num`)',
            'UNIQUE KEY `url` (`url`)',
        ), array(
            "null,null,'name','varchar','Наименование','text','main','','','ru',null",
            "null,null,'about','text','Краткое описание','textarea','main','','','ru',null",
            "null,null,'content','text','Основной текст','wysiwyg','main','','','ru',null",
            "null,null,'tags','tags','Тэги','tags','main','','','ru',null",
            "null,null,'title','varchar','Title','text','seo','','','ru',null",
            "null,null,'description','varchar','Description','textarea','seo','','','ru',null",
            "null,null,'keywords','varchar','Keywords','textarea','seo','','','ru',null",
        ));

        $this->createTable('character_int', array(
            'character_id' => $this->int,
            'page_id' => $this->int,
            'value' => 'int DEFAULT NULL',
            'PRIMARY KEY (`character_id`,`page_id`)',
            'KEY `character_id` (`character_id`)',
            'KEY `page_id` (`page_id`)',
            'KEY `value` (`value`)',
            $this->fk('character_id', 'character'),
            $this->fk('page_id', 'page'),
        ));

        $this->createTable('character_tags_values', array(
            'id' => $this->int . ' AUTO_INCREMENT',
            'lang_id' => $this->lang,
            'value' => 'varchar(255) DEFAULT NULL',
            'PRIMARY KEY (`id`,`lang_id`)',
            'UNIQUE KEY `unique` (`lang_id`, `value`)',
            'KEY `lang_id` (`lang_id`)',
            $this->fk('lang_id', 'lang'),
        ));

        $this->createTable('character_tags', array(
            'character_id' => $this->int,
            'page_id' => $this->int,
            'tag_id' => $this->int,
            'PRIMARY KEY (`character_id`,`page_id`,`tag_id`)',
            'KEY `character_id` (`character_id`)',
            'KEY `page_id` (`page_id`)',
            'KEY `tag_id` (`tag_id`)',
            $this->fk('character_id', 'character'),
            $this->fk('page_id', 'page'),
            $this->fk('tag_id', 'character_tags_values'),
        ));

        $this->createTable('character_text', array(
            'character_id' => $this->int,
            'page_id' => $this->int,
            'lang_id' => $this->lang,
            'value' => 'text DEFAULT NULL',
            'PRIMARY KEY (`character_id`,`page_id`,`lang_id`)',
            'KEY `character_id` (`character_id`)',
            'KEY `page_id` (`page_id`)',
            'KEY `lang_id` (`lang_id`)',
            $this->fk('character_id', 'character'),
            $this->fk('page_id', 'page'),
            $this->fk('lang_id', 'lang'),
        ));

        $this->createTable('character_varchar', array(
            'character_id' => $this->int,
            'page_id' => $this->int,
            'lang_id' => $this->lang,
            'value' => 'varchar(255) DEFAULT NULL',
            'PRIMARY KEY (`character_id`,`page_id`,`lang_id`)',
            'KEY `character_id` (`character_id`)',
            'KEY `page_id` (`page_id`)',
            'KEY `lang_id` (`lang_id`)',
            'KEY `value` (`value`)',
            $this->fk('character_id', 'character'),
            $this->fk('page_id', 'page'),
            $this->fk('lang_id', 'lang'),
        ));

        /*$this->createTable('exchange_1c', array(
            'external_id' => 'char(36) NOT NULL PRIMARY KEY',
            'type' => 'varchar(255)',
            'id' => 'int',
        ));*/

        $this->createTable('form', array(
            'id' => $this->pk,
            'type' => 'string',
            'info' => 'text',
            'lastmod' => $this->lastmod,
            'KEY `type` (`type`)',
        ));

        $this->createTable('module_config', array(
            'module_id' => $this->int,
            'num' => $this->int,
            'name' => 'varchar(30) NOT NULL',
            'value' => 'varchar(30) NOT NULL',
            'PRIMARY KEY (`module_id`,`num`,`name`)',
            $this->fk('module_id', 'module'),
        ));

        $this->createTable('order', array(
            'id' => $this->pk,
            'user_id' => $this->int,
            'status_id' => $this->tinyint,
            'pay_status' => $this->tinyint,
            'delivery_id' => $this->tinyint,
            'pay_id' => $this->tinyint,
            'total' => 'float(11,2) unsigned NOT NULL',
            'data' => 'longtext NOT NULL',
            'lastmod' => $this->lastmod,
            'created' => $this->lastmod,
        ));

        $this->createTable('user_photo', array(
            'id' => $this->pk,
            'num' => $this->int,
            'page_id' => $this->int,
            'user_id' => $this->int,
            'name' => 'varchar(255) NOT NULL',
            'width' => $this->int,
            'height' => $this->int,
            'about' => 'varchar(255) NOT NULL',
            'cropParams' => 'varchar(255) NOT NULL',
            'hash' => 'char(32) NOT NULL',
            'lastmod' => $this->lastmod,
            'created' => $this->lastmod,
            'KEY `num` (`num`)',
            'KEY `page_id` (`page_id`)',
            'KEY `user_id` (`user_id`)',
            'KEY `name` (`name`)',
            'KEY `hash` (`hash`)',
            $this->fk('page_id', 'page'),
            $this->fk('user_id', 'user'),
        ));

        $this->createTable('price_type', array(
            'id' => $this->pk,
            'name' => 'varchar(255) NOT NULL',
            'currency' => 'char(3) NOT NULL',
            'tax' => 'varchar(255) NOT NULL',
            'taxInclude' => 'boolean',
            'KEY `currency` (`currency`)',
        ));

        $this->createTable('price', array(
            'id' => $this->pk,
            'page_id' => $this->int,
            'type_id' => $this->int,
            'unit' => 'varchar(30) NOT NULL',
            'value' => 'float(11,2) unsigned NOT NULL',
            'count' => $this->int,
            'lastmod' => $this->lastmod,
            'KEY `page_id` (`page_id`)',
            'KEY `type_id` (`type_id`)',
            'KEY `count` (`count`)',
            'KEY `value` (`value`)',
            $this->fk('page_id', 'page'),
            $this->fk('type_id', 'price_type'),
        ));

        $this->createTable('price_characters', array(
            'price_id' => $this->int,
            'character_id' => $this->int,
            'value_id' => $this->int,
            'PRIMARY KEY (`price_id`,`character_id`,`value_id`)',
            'KEY `price_id` (`price_id`)',
            'KEY `character_id` (`character_id`)',
            'KEY `value_id` (`value_id`)',
            $this->fk('price_id', 'price'),
            $this->fk('character_id', 'character'),
        ));

        $this->createTable('subscription', array(
            'id' => $this->pk,
            'name' => 'varchar(150) NOT NULL',
            'email' => 'varchar(100) NOT NULL',
            'value' => $this->tinyint,
            'lastmod' => $this->lastmod,
        ));

        $this->createTable('url', array(
            'value' => 'varchar(50) NOT NULL PRIMARY KEY',
            'page_id' => $this->int,
            'current' => 'boolean',
            'KEY `page_id` (`page_id`)',
            'KEY `current` (`current`)',
            $this->fk('page_id', 'page'),
        ));

        $this->createTable('user_setting', array(
            'user_id' => $this->int,
            'name' => 'varchar(255) NOT NULL',
            'value' => 'varchar(255) NOT NULL',
            'PRIMARY KEY (`user_id`,`name`)',
            'KEY `user_id` (`user_id`)',
            'KEY `value` (`value`)',
            $this->fk('user_id', 'user'),
        ));

        $this->createTable('user_token', array(
            'user_id' => $this->int,
            'name' => 'varchar(32) NOT NULL',
            'value' => 'char(32) NOT NULL',
            'expiration' => $this->lastmod,
            'PRIMARY KEY (`user_id`,`name`)',
            'UNIQUE KEY `value` (`value`)',
            $this->fk('user_id', 'user'),
        ));

        $this->actionAnalytics();

        $dirs = array('app.runtime', 'assets', 'data', 'data._source', 'data._tmp');
        foreach ($dirs as $dir) {
            $path = Yii::getPathOfAlias('webroot.' . $dir);
            if (!file_exists($path)) mkdir($path);
        }
    }

    public function actionAnalytics()
    {
        $this->createTable('log_action', array(
            'id' => $this->pk,
            'name' => 'text',
            'hash' => $this->int,
            'type' => $this->tinyint,
            'KEY `hash` (`hash`)',
        ));

        $this->createTable('log_visit', array(
            'id' => $this->pk,
            'visitor_id' => $this->binary,
            'last_action_time' => $this->lastmod,
            'first_action_time' => $this->lastmod,
            'location_ip' => 'varbinary(16) NOT NULL',
            'action_id_ref' => $this->int,
            'total_time' => $this->smallint,
            'total_actions' => $this->smallint,
            'os' => 'char(3) NOT NULL',
            'browser' => 'varchar(10) NOT NULL',
            'browser_version' => 'varchar(20) NOT NULL',
        ));

        $this->createTable('log_hit', array(
            'id' => $this->pk,
            'visitor_id' => $this->binary,
            'visit_id' => $this->int,
            'action_id_name' => $this->int,
            'action_id_url' => $this->int,
            'action_id_event' => $this->int,
            'time_cpu' => $this->smallint,
            'time_exec' => $this->smallint,
            'ram' => $this->smallint,
            'created' => $this->lastmod,
            $this->fk('visit_id', 'log_visit'),
            $this->fk('action_id_url', 'log_action'),
        ));
    }

    public function fk($from, $to, $type = 'CASCADE', $column = 'id')
    {
        $id = '';
        foreach (explode('_', $from) as $row) $id .= $row[0];
        return "CONSTRAINT `{$id}_{$to}_fk` FOREIGN KEY (`{$from}`) REFERENCES `{$to}` (`{$column}`) ON DELETE {$type} ON UPDATE {$type}";
    }

    public function createTable($name, $data, $values = array(), $charset = 'utf8')
    {
        $id = '';
        foreach (explode('_', $name) as $row) $id .= $row[0] . $row[1];
        foreach ($data as $key => $val) $data[$key] = str_replace('_fk` F', '_' . $id . '_fk` F', $val);
        if (!Yii::app()->db->createCommand('SHOW TABLES LIKE "' . $name . '"')->queryScalar())
            Yii::app()->db->createCommand()->createTable($name, $data, 'ENGINE=InnoDB DEFAULT CHARSET=' . $charset);
        elseif ($this->update) {
            $last = false;
            $list = array();
            foreach (Yii::app()->db->schema->tables[$name]->columns as $key => $val) {
                $list[$key] = $val->dbType;
                if (!$val->allowNull) $list[$key] .= ' NOT NULL';
                if ($val->autoIncrement) $list[$key] .= ' AUTO_INCREMENT';
                if ($val->isPrimaryKey) $list[$key] .= ' PRIMARY KEY';
                if ($data[$key] != $list[$key]) {
                    $list[$key] .= $last ? " AFTER `{$last}`" : ' FIRST';
                    Yii::app()->db->createCommand()->alterColumn($name, $key, $list[$key]);
                }
                $last = $key;
            }
        }
        if (count($values)) {
            $list = array();
            foreach (array_keys($data) as $key) if (!is_numeric($key)) $list[] = $key;
            $sql = 'insert ignore into `' . $name . '`(`' . implode('`,`', $list) . '`) values (' . implode('),(', $values) . ');';
            Yii::app()->db->createCommand($sql)->execute();
        }
    }
}