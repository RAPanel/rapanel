<?php

class InstallController extends RController
{
    public $lt;

    public function actionIndex()
    {
        $int = 'int unsigned NOT NULL';
        $id = $int . ' AUTO_INCREMENT PRIMARY KEY';
        $tinyint = 'tinyint(3) unsigned NOT NULL';
        $lang = 'char(2) NOT NULL';
        $lastmod = 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';

        $this->createTable('lang', array(
            'id' => $lang . ' PRIMARY KEY',
            'name' => 'varchar(50) NOT NULL',
            'tooltip' => 'varchar(255) NOT NULL',
        ), array(
            "'en','English','English'",
            "'ru','Russian','Русский'",
        ));

        $this->createTable('cache', array(
            'id' => 'char(128) NOT NULL PRIMARY KEY',
            'expire' => $int,
            'value' => 'longblob NOT NULL',
            'KEY `expire` (`expire`)',
        ));

        $this->createTable('session', array(
            'id' => 'char(32) NOT NULL PRIMARY KEY',
            'expire' => $int,
            'data' => 'longblob',
            'KEY `expire` (`expire`)',
        ));

        $this->createTable('message_source', array(
            'id' => $id,
            'language' => $lang,
            'category' => 'varchar(32) DEFAULT NULL',
            'message' => 'text',
            'UNIQUE KEY `category_message` (`category`,`message`(255),`language`)',
            'KEY `language` (`language`)',
            'KEY `category` (`category`)',
            $this->fk('language', 'lang'),
        ));

        $this->createTable('message_translation', array(
            'id' => $int,
            'language' => $lang,
            'translation' => 'text',
            'PRIMARY KEY (`id`,`language`)',
            'KEY `id` (`id`)',
            'KEY `language` (`language`)',
            $this->fk('id', 'message_source'),
            $this->fk('language', 'lang'),
        ));

        $this->createTable('statistic', array(
            'time' => 'float(6,3) unsigned NOT NULL',
            'memory' => 'float(6,3) unsigned NOT NULL',
            'cpu' => 'float(6,3) unsigned NOT NULL',
            'referrer_id' => $int,
            'page_id' => $int,
            'session' => 'char(32) NOT NULL',
            'ip' => 'varchar(50) NOT NULL',
            'url' => 'varchar(500) NOT NULL',
            'url_referrer' => 'varchar(500) NOT NULL',
            'user_agent' => 'varchar(500) NOT NULL',
            'lastmod' => $lastmod,
        ));

        $this->createTable('module', array(
            'id' => $id,
            'num' => $int,
            'status_id' => $tinyint,
            'type_id' => $tinyint,
            'name' => 'varchar(32) NOT NULL',
            'access' => 'varchar(64) NOT NULL',
            'url' => 'varchar(30) NOT NULL',
            'className' => 'varchar(255) NOT NULL',
            'groupName' => 'varchar(255) NOT NULL',
            'lang_id' => $lang,
            'lastmod' => $lastmod,
            'UNIQUE KEY `url` (`url`)',
            'KEY `num` (`num`)',
            'KEY `groupName` (`groupName`)',
        ), array(
            "null,0,0,0,'Основные страницы','administrator','page','Page','Страницы сайта','ru',null",
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
            "null,0,0,0,'Характеристики','administrator','characteristics','Character','Настройки','ru',null",
            "null,0,0,0,'Сообщения и переводы','administrator','translater','MessageTranslate','Настройки','ru',null",
        ));

        $this->createTable('user', array(
            'id' => $id,
            'username' => 'varchar(255) NOT NULL',
            'email' => 'varchar(255) NOT NULL',
            'password' => 'varchar(255) NOT NULL',
            'role' => 'varchar(32) NOT NULL',
            'lastmod' => $lastmod,
            'created' => 'timestamp',
            'UNIQUE KEY `email` (`email`)',
            'KEY `username` (`username`)',
            'KEY `role` (`role`)',
        ), array(
            "null,'Семен Сушенцев','semyonchick@gmail.com','" . '$2a$13$/WoHDNmN87m1zVSg7a8Cpu1IfRCvZof0K9Z1FW7QraM06I.aJZ9c2' . "','root',null,null",
            "null,'Валерия Сушенцева','mail@rere-design.ru','" . '$2a$13$tUzrpV1b3OEWizFT1vfeQuc9VtaBvLf5KMyFTYnxjNh4e9PSdhvE.' . "','root',null,null",
            "null,'RUgaleFF','anatoly.rugalev@gmail.com','" . '$2a$13$BWLTAkr.pruzgl6nPp2LeOZJfm6vXXQ8INZdtRKrPyNtzLi.MZZIm' . "','root',null,null",
            "null,'Денис','denart89@gmail.com','" . '$2a$13$SMxuEoXwpmTXmS5xXQmvIe0BETFquft84dbWXYrcmcl04.A4QWOli' . "','root',null,null",
            "null,'Valentina E','v.a.elokhova@gmail.com','" . '$2a$13$StKF31EpRWWLUcs3RbvCx.dN9mdCf9NXK3D4E/idLz0A/L1gDmeO6' . "','root',null,null",
        ));

        $this->createTable('page', array(
            'id' => $id,
            'user_id' => $int,
            'status_id' => $tinyint,
            'module_id' => $int,
            'parent_id' => $int,
            'lft' => $int,
            'rgt' => $int,
            'level' => $tinyint,
            'is_category' => $tinyint,
            'lang_id' => $lang,
            'lastmod' => $lastmod,
            'created' => 'timestamp',
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
            'id' => $id,
            'num' => $int,
            'url' => 'varchar(30) NOT NULL',
            'type' => 'varchar(30) NOT NULL',
            'name' => 'varchar(32) NOT NULL',
            'inputType' => 'varchar(50) NOT NULL',
            'position' => 'varchar(50) NOT NULL',
            'filter' => 'varchar(10) NOT NULL',
            'data' => 'text NOT NULL',
            'lang_id' => 'char(5) NOT NULL',
            'lastmod' => $lastmod,
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
            'character_id' => $int,
            'page_id' => $int,
            'value' => 'int DEFAULT NULL',
            'PRIMARY KEY (`character_id`,`page_id`)',
            'KEY `character_id` (`character_id`)',
            'KEY `page_id` (`page_id`)',
            'KEY `value` (`value`)',
            $this->fk('character_id', 'character'),
            $this->fk('page_id', 'page'),
        ));

        $this->createTable('character_tags_values', array(
            'id' => $int . ' AUTO_INCREMENT',
            'lang_id' => $lang,
            'value' => 'varchar(255) DEFAULT NULL',
            'PRIMARY KEY (`id`,`lang_id`)',
            'UNIQUE KEY `unique` (`lang_id`, `value`)',
            'KEY `lang_id` (`lang_id`)',
            $this->fk('lang_id', 'lang'),
        ));

        $this->createTable('character_tags', array(
            'character_id' => $int,
            'page_id' => $int,
            'tag_id' => $int,
            'PRIMARY KEY (`character_id`,`page_id`,`tag_id`)',
            'KEY `character_id` (`character_id`)',
            'KEY `page_id` (`page_id`)',
            'KEY `tag_id` (`tag_id`)',
            $this->fk('character_id', 'character'),
            $this->fk('page_id', 'page'),
            $this->fk('tag_id', 'character_tags_values'),
        ));

        $this->createTable('character_text', array(
            'character_id' => $int,
            'page_id' => $int,
            'lang_id' => $lang,
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
            'character_id' => $int,
            'page_id' => $int,
            'lang_id' => $lang,
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
            'id' => $id,
            'type' => 'string',
            'info' => 'text',
            'lastmod' => $lastmod,
            'KEY `type` (`type`)',
        ));

        $this->createTable('module_config', array(
            'module_id' => $int,
            'num' => $int,
            'name' => 'varchar(30) NOT NULL',
            'value' => 'varchar(30) NOT NULL',
            'PRIMARY KEY (`module_id`,`num`,`name`)',
            $this->fk('module_id', 'module'),
        ));

        $this->createTable('order', array(
            'id' => $id,
            'user_id' => $int,
            'status_id' => $tinyint,
            'pay_status' => $tinyint,
            'delivery_id' => $tinyint,
            'pay_id' => $tinyint,
            'total' => 'float(11,2) unsigned NOT NULL',
            'data' => 'longtext NOT NULL',
            'lastmod' => $lastmod,
            'created' => 'timestamp',
        ));

        $this->createTable('user_photo', array(
            'id' => $id,
            'num' => $int,
            'page_id' => $int,
            'user_id' => $int,
            'name' => 'varchar(255) NOT NULL',
            'width' => $int,
            'height' => $int,
            'about' => 'varchar(255) NOT NULL',
            'cropParams' => 'varchar(255) NOT NULL',
            'hash' => 'char(32) NOT NULL',
            'lastmod' => $lastmod,
            'created' => 'timestamp',
            'KEY `num` (`num`)',
            'KEY `page_id` (`page_id`)',
            'KEY `user_id` (`user_id`)',
            'KEY `name` (`name`)',
            'KEY `hash` (`hash`)',
            $this->fk('page_id', 'page'),
            $this->fk('user_id', 'user'),
        ));

        $this->createTable('price_type', array(
            'id' => $id,
            'name' => 'varchar(255) NOT NULL',
            'currency' => 'char(3) NOT NULL',
            'tax' => 'varchar(255) NOT NULL',
            'taxInclude' => 'boolean',
            'KEY `currency` (`currency`)',
        ));

        $this->createTable('price', array(
            'id' => $id,
            'page_id' => $int,
            'type_id' => $int,
            'unit' => 'varchar(30) NOT NULL',
            'value' => 'float(11,2) unsigned NOT NULL',
            'count' => $int,
            'lastmod' => $lastmod,
            'KEY `page_id` (`page_id`)',
            'KEY `type_id` (`type_id`)',
            'KEY `count` (`count`)',
            'KEY `value` (`value`)',
            $this->fk('page_id', 'page'),
            $this->fk('type_id', 'price_type'),
        ));

        $this->createTable('price_characters', array(
            'price_id' => $int,
            'character_id' => $int,
            'value_id' => $int,
            'PRIMARY KEY (`price_id`,`character_id`,`value_id`)',
            'KEY `price_id` (`price_id`)',
            'KEY `character_id` (`character_id`)',
            'KEY `value_id` (`value_id`)',
            $this->fk('price_id', 'price'),
            $this->fk('character_id', 'character'),
        ));

        $this->createTable('subscription', array(
            'id' => $id,
            'name' => 'varchar(150) NOT NULL',
            'email' => 'varchar(100) NOT NULL',
            'value' => $tinyint,
            'lastmod' => $lastmod,
        ));

        $this->createTable('url', array(
            'value' => 'varchar(50) NOT NULL PRIMARY KEY',
            'page_id' => $int,
            'current' => 'boolean',
            'KEY `page_id` (`page_id`)',
            'KEY `current` (`current`)',
            $this->fk('page_id', 'page'),
        ));

        $this->createTable('user_setting', array(
            'user_id' => $int,
            'name' => 'varchar(255) NOT NULL',
            'value' => 'varchar(255) NOT NULL',
            'PRIMARY KEY (`user_id`,`name`)',
            'KEY `user_id` (`user_id`)',
            'KEY `value` (`value`)',
            $this->fk('user_id', 'user'),
        ));

        $this->createTable('user_token', array(
            'user_id' => $int,
            'name' => 'varchar(32) NOT NULL',
            'value' => 'char(32) NOT NULL',
            'expiration' => $lastmod,
            'PRIMARY KEY (`user_id`,`name`)',
            'UNIQUE KEY `value` (`value`)',
            $this->fk('user_id', 'user'),
        ));

        $dirs = array('app.runtime', 'assets', 'data', 'data._source', 'data._tmp');
        foreach ($dirs as $dir) {
            $path = Yii::getPathOfAlias('webroot.' . $dir);
            if (!file_exists($path)) mkdir($path);
        }
    }

    public function fk($from, $to, $type = 'CASCADE', $column = 'id')
    {
        $id = '';
        foreach (explode('_', $from) as $row) $id .= $row[0];
        return "CONSTRAINT `{$id}_{$to}_fk` FOREIGN KEY (`{$from}`) REFERENCES `{$to}` (`{$column}`) ON DELETE {$type} ON UPDATE {$type}";
    }

    public function createTable($name, $data, $values = array())
    {
        $id = '';
        foreach (explode('_', $name) as $row) $id .= $row[0] . $row[1];
        foreach ($data as $key => $val) $data[$key] = str_replace('_fk` F', '_' . $id . '_fk` F', $val);
        if (!Yii::app()->db->createCommand('SHOW TABLES LIKE "' . $name . '"')->queryScalar())
            Yii::app()->db->createCommand()->createTable($name, $data, 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        if (count($values)) {
            $list = array();
            foreach (array_keys($data) as $key) if (!is_numeric($key)) $list[] = $key;
            $sql = 'insert ignore into `' . $name . '`(`' . implode('`,`', $list) . '`) values (' . implode('),(', $values) . ');';
            Yii::app()->db->createCommand($sql)->execute();
        }
    }

    public function insertTable($table, $keys, $values)
    {
        $sql = 'insert  into `' . $table . '`(' . $keys . ') values (' . implode('),(', $values) . ');';
        return Yii::app()->db->createCommand($sql)->execute();
    }
}