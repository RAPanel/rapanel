<?php

class ModuleBehavior extends AdminBehavior
{
    public function getClassList()
    {
	    if(Yii::app()->hasComponent('moduleMapper'))
	        return Yii::app()->moduleMapper->getModuleClasses();
        $result = array();
        $list = array(
            Yii::getPathOfAlias('application.models') . DIRECTORY_SEPARATOR,
            Yii::getPathOfAlias('ext._rere.models') . DIRECTORY_SEPARATOR,
        );
        foreach ($list as $dir) foreach (scandir($dir) as $file)
            if (is_file($dir . $file)) {
                $class = current(explode('.', $file));
                $result[$class] = $class;
            }
        return $result;
    }

    public function getDataProvider()
    {
        $criteria = new CDbCriteria();

        if (!empty($_REQUEST['sSearch'])) {
            $criteria->addSearchCondition('name', $_REQUEST['sSearch']);
        }

        $criteria->addCondition('`t`.`id` > 0');
        $criteria->order = '`t`.`num` ASC';

        if (($q = $_GET['q']) && $q != '*') {
            $condition = array();
            foreach ($this->getOwner()->tableSchema->columns as $row) {
                if (in_array(current(explode('(', $row->dbType)), array('varchar', 'text'))) {
                    $condition[] = "t.{$row->name} LIKE :textSearch";
                    $criteria->params['textSearch'] = "%{$q}%";
                } elseif (in_array(current(explode('(', $row->dbType)), array('timestamp'))) {
                    continue;
                } else {
                    if (!is_numeric($q)) continue;
                    $condition[] = "t.{$row->name}=:intSearch";
                    $criteria->params['intSearch'] = $q;
                }
            }
            $criteria->addCondition(implode(' OR ', $condition));
        }

        return new CActiveDataProvider($this->owner, array(
            'criteria' => $criteria,
            'pagination' => false,
            'sort' => false,
        ));
    }

    public function getColumns()
    {
        return array(
            'order' => array(
                'name' => '#',
                'value' => '$data->num',
                'type' => 'order',
                'cssClassExpression' => '"sorterHandler"',
            ),
            'checkbox'=>array(
                'class' => 'CCheckBoxColumn',
                'selectableRows' => 99999,
            ),
            'id' => array(
                'name' => 'id',
                'type' => $this->getTypeFromList('id'),
            ),
            'status_id' => array(
                'name' => 'status_id',
                'value' => '$data->status',
                'type' => $this->getTypeFromList('status_id'),
            ),
            'type_id' => array(
                'name' => 'type_id',
                'value' => '$data->type',
                'type' => $this->getTypeFromList('type_id'),
            ),
            'url' => array(
                'name' => 'url',
                'type' => $this->getTypeFromList('url'),
            ),
            'groupName' => array(
                'name' => 'groupName',
                'type' => $this->getTypeFromList('groupName'),
            ),
            'name' => array(
                'name' => 'name',
                'type' => $this->getTypeFromList('name'),
            ),
            'className' => array(
                'name' => 'className',
                'type' => $this->getTypeFromList('className'),
            ),
            'lastmod' => array(
                'name' => 'lastmod',
                'type' => $this->getTypeFromList('lastmod'),
            ),
            'buttons'=>array(
                'header' => 'Действия',
                'class' => 'CButtonColumn',
                'template' => '{edit} {config}',
                'buttons' => array(
                    'edit' => array(
                        'label' => 'Edit',
                        'url' => 'CHtml::normalizeUrl(array("edit", "url"=>$data->url))',
                        'options' => array(
                            'onclick' => 'modalIFrame(this);return false;',
                            'data-update' => 'modulesGrid',
                        ),
                    ),
                    'config' => array(
                        'label' => 'Config',
                        'url' => 'CHtml::normalizeUrl(array("config", "url"=>$data->url))',
                        'options' => array(
                            'onclick' => 'modalIFrame(this);return false;',
                            'data-update' => 'modulesGrid',
                        ),
                    ),
                )
            )
        );
    }

    public function getForm()
    {
        if ($this->owner->isNewRecord) {
            $this->owner->status_id = 1;
            $this->owner->num = Yii::app()->db->createCommand('SELECT MAX(`num`) FROM `module`')->queryScalar() + 1;
        }
        return array(
            'elements' => array(
                'num' => array(
                    'type' => 'hidden',
                ),
                'status_id' => array(
                    'type' => 'dropdownlist',
                    'items' => Module::status(),
                ),
                'name' => array(
                    'type' => 'text'
                ),
                'groupName' => array(
                    'type' => 'text'
                ),
                'url' => array(
                    'type' => 'text'
                ),
                'access' => array(
                    'type' => 'dropdownlist',
                    'empty' => 'Выберите права...',
                    'items' => UserBase::roles(),
                ),
                'className' => array(
                    'type' => 'dropdownlist',
                    'empty' => 'Выберите модуль...',
                    'items' => $this->owner->classList,
                ),
                'type_id' => array(
                    'type' => 'dropdownlist',
                    'items' => Module::type(),
                    'hint' => 'Действует только для Моделей произаодных от Page',
                ),
            ),
            'buttons' => array(
                'send' => array(
                    'type' => 'submit',
                    'label' => Yii::t('admin', $this->owner->isNewRecord ? 'Create' : 'Update'),
                ),
            ),
        );
    }

    public function getConfigForm()
    {
        /** @var $model RActiveRecord|PageBase */
        $model = new $this->owner->className();
        $lines = $data = array();

        if (method_exists($model, 'getCharacterNames'))
            $lines['characters'] = array_values($model->getCharacterNames(true, true));
        $lines['elements'] = $model->attributeNames();
        $lines['columns'] = isset($this->owner->config['characters']) ? CMap::mergeArray($lines['elements'], (array)$this->owner->config['characters']) : $lines['elements'];

        foreach ($lines as $label => $array) {
            $columns = array_intersect((array)$this->owner->config[$label], $array);
            foreach (CMap::mergeArray($columns, $array) as $val) {
                $data[$label][$val] = $model->getAttributeLabel($val) . ' / ' . $val;
            }
        }

        if ($data['columns'])
            $elements['config[columns]'] = array(
                'label' => 'Колонки',
                'type' => 'ext.jmultiselect2side.Jmultiselect2side',
                'autoSort' => false,
                'labeldx' => 'Выбрано',
                'labelsx' => 'Доступно',
                'search' => 'Поиск ',
                'list' => $data['columns'],
            );
        if ($data['elements'])
            $elements['config[elements]'] = array(
                'label' => 'Основные эллементы',
                'type' => 'ext.jmultiselect2side.Jmultiselect2side',
                'autoSort' => false,
                'labeldx' => 'Выбрано',
                'labelsx' => 'Доступно',
                'search' => 'Поиск ',
                'list' => $data['elements'],
            );
        if ($data['characters'])
            $elements['config[characters]'] = array(
                'label' => 'Характеристики',
                'type' => 'ext.jmultiselect2side.Jmultiselect2side',
                'autoSort' => false,
                'labeldx' => 'Выбрано',
                'labelsx' => 'Доступно',
                'search' => 'Поиск ',
                'list' => $data['characters'],
            );
        $elements['config[actions]'] = array(
            'label' => 'Доступные действия',
            'type' => 'checkboxlist',
            'items' => array(
                'view' => 'Просмотр',
                'edit' => 'Редактирование',
                'clone' => 'Клонирование',
                'delete' => 'Удаление',
                'note' => 'Заметки',
            ),
        );
        if (in_array('currentUrl', array_keys($model->relations()))) {
            $elements['config[noUrl]'] = array(
                'label' => 'Не генерировать url',
                'layout' => '{input}{label}{hint}{error}',
                'type' => 'checkbox',
            );
        }
        if (in_array('photos', array_keys($model->relations()))) {
            $elements['config[photos]'] = array(
                'label' => 'Максимальное количество фотографий',
                'type' => 'number',
                'attributes' => array(
                    'min' => 0,
                    'max' => 99,
                ),
            );

            $elements['config[crop]'] = array(
                'label' => 'Коэфициент обрезки',
                'hint' => 'Расчитывается делением `width` на `height`',
                'type' => 'number',
                'attributes' => array(
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.01,
                ),
            );
        }
        if (in_array('files', array_keys($model->relations()))) {
            $elements['config[files]'] = array(
                'label' => 'Максимальное количество файлов',
                'type' => 'number',
                'attributes' => array(
                    'min' => 0,
                    'max' => 99,
                ),
            );
        }
        $elements['config[parent_id]'] = array(
            'label' => 'Номер родителя по умолчанию',
            'type' => 'number',
        );

        $elements[] = '<div class="clearfix"></div>';
        return array(
            'elements' => $elements,
            'buttons' => array(
                'send' => array(
                    'type' => 'submit',
                    'label' => Yii::t('admin', $this->owner->isNewRecord ? 'Create' : 'Update'),
                ),
            ),
        );
    }

}