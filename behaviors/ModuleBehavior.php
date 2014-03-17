<?php

class ModuleBehavior extends AdminBehavior
{
    public function getClassList()
    {
        $result = array();
        $dir = Yii::getPathOfAlias('application.models') . DIRECTORY_SEPARATOR ;
        foreach (scandir($dir) as $file)
            if (is_file($dir . $file)) {
                $class = current(explode('.', $file));
                $result[$class] = $class;
            }
        return $result;
    }

    public function getDataProvider()
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('`t`.`id` > 0');
        $criteria->order = '`t`.`num` ASC';

        return new CActiveDataProvider($this->owner, array(
            'criteria' => $criteria,
            'pagination' => false,
            'sort' => false,
        ));
    }

    public function getColumns()
    {
        return array(
            array(
                'class' => 'CCheckBoxColumn',
                'selectableRows' => 9999,
            ),
            'id',
            array(
                'name' => 'status_id',
                'value' => '$data->status',
            ),
            array(
                'name' => 'type_id',
                'value' => '$data->type',
            ),
            'url',
            'groupName',
            'name',
            'className',
            array(
                'header' => 'Действия',
                'class' => 'CButtonColumn',
                'template' => '{edit} {config}',
                'buttons' => array(
                    'edit' => array(
                        'label' => 'edit',
                        'url' => 'CHtml::normalizeUrl(array("edit", "url"=>$data->url))',
                        'options' => array(
                            'onclick' => 'modalIFrame(this);return false;',
                            'data-update' => 'modulesGrid',
                        ),
                    ),
                    'config' => array(
                        'label' => 'config',
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

        if (method_exists((new $this->owner->className), 'getCharacterNames'))
            $lines['characters'] = $model->getCharacterNames(true, true);
        $lines['elements'] = array_keys($model->tableSchema->columns);
        $lines['columns'] = isset($lines['characters']) ? array_merge($lines['elements'], $lines['characters']) : $lines['elements'];

        foreach ($lines as $label => $array) {
            $columns = array_intersect((array)$this->owner->config[$label], $array);
            foreach (array_merge($columns, $array) as $val) {
                $data[$label][$val] = $model->getAttributeLabel($val);
            }
        }

        if ($data['columns'])
            $elements['config[columns]'] = array(
                'label' => 'Колонки',
                'type' => 'ext.jmultiselect2side.Jmultiselect2side',
                'autoSort' => false,
                'labeldx' => 'Доступно',
                'labelsx' => 'Выбрано',
                'search' => 'Поиск ',
                'list' => $data['columns'],
            );
        if ($data['elements'])
            $elements['config[elements]'] = array(
                'label' => 'Основные эллементы',
                'type' => 'ext.jmultiselect2side.Jmultiselect2side',
                'autoSort' => false,
                'labeldx' => 'Доступно',
                'labelsx' => 'Выбрано',
                'search' => 'Поиск ',
                'list' => $data['elements'],
            );
        if ($data['characters'])
            $elements['config[characters]'] = array(
                'label' => 'Характеристики',
                'type' => 'ext.jmultiselect2side.Jmultiselect2side',
                'autoSort' => false,
                'labeldx' => 'Доступно',
                'labelsx' => 'Выбрано',
                'search' => 'Поиск ',
                'list' => $data['characters'],
            );
        $elements['config[actions]'] = array(
            'label' => 'Доступные действия',
            'type' => 'checkboxlist',
            'items' => array(
                'view' => 'Просмотр',
                'edit' => 'Редактирование',
                'delete' => 'Удаление',
            ),
        );
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