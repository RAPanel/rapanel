rapanel
=======


Вывод административной панели управления
=======

Добавить вывод виджета в app/views/layouts/head.php
-------
```php
<? $this->widget('application.modules.rapanel.widgets.AdminToolbar.AdminToolbar') ?>
```

Рекомендуемый конфиг
-------
app/phpconfig.php

```php
<?php
// Показ ошибок
if ($_REQUEST['editMode']) {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
    error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
    ini_set('display_errors', 1);
    ini_set('html_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('html_errors', 0);
}
```

app/run.php

```php
<?php
date_default_timezone_set('Asia/Yekaterinburg');

include_once(dirname(__FILE__) . '/phpconfig.php');

$yii = '/opt/yii/latest/yii.php';

require_once($yii);
```

rapanel
