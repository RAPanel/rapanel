<?php

class ContentBehavior extends AdminBehavior
{
    private $_module;
    private $_isCategory;
    private $_dataProvider;

    protected function getAdminSettings()
    {
        $adminSettings = (array)$this->getModule()->config;
        return count($adminSettings) ? $adminSettings : array(
            'actions' => array('view', 'edit', 'clone', 'delete'),
        );
    }

    public function getDataProvider($criteria = array())
    {
        if (!$this->_dataProvider) {
            $columns = (array)$this->adminSettings['columns'];
            $criteria = new CDbCriteria($criteria);
            $criteria->mergeWith($this->owner->defaultScope());
            $criteria->addCondition('`t`.`id` > 0');

            if (isset($this->owner->module_id))
                $criteria->compare('`t`.`module_id`', $this->getModule()->id);

            if (method_exists($this->getOwner(), 'getCharacterNames')) {
                $characters = array_intersect((array)$this->adminSettings['columns'], $this->getOwner()->getCharacterNames());
                foreach ($characters as $character) $criteria->with[] = Characters::getRelationByUrl($character);
                if ((int)$this->adminSettings['photos'] > 0)
                    $criteria->with['photo'] = array('select' => 'name');
                $criteria->with['currentUrl'] = array('select' => 'value');
            }

            $relations = array_keys($this->owner->relations());
            if (in_array('user_id', $columns) && in_array('user', $relations))
                $criteria->with['user'] = array('select' => 'username');
            if (in_array('page_id', $columns) && in_array('page', $relations))
                $criteria->with['page'] = array('select' => false, 'with' => array('rName' => array('alias' => 'pName')));
            if (in_array('parent_id', $columns) && in_array('parent', $relations))
                $criteria->with['parent'] = array('select' => false, 'with' => array('rName' => array('alias' => 'pName')));

            foreach ((array)$criteria->with as $key => $with)
                if (is_array($with))
                    $criteria->with[$key] = array_merge($with, array('together' => false));
                else {
                    unset($criteria->with[$key]);
                    $criteria->with[$with] = array('together' => false);
                }

            $pagination = false;

            $sort = false;

            $columns = array_keys($this->getOwner()->tableSchema->columns);

            if ($this->getModule()->type_id == Module::TYPE_SELF_NESTED || $this->getModule()->type_id == Module::TYPE_NESTED) {
                $criteria->order = '`t`.`is_category` DESC, `t`.`lft` ASC, `t`.`created` DESC, `t`.`id` DESC';
                if (!isset($_GET['parent_id'])) $_GET['parent_id'] = $this->getModule()->config['parent_id'];
                if (!empty($_GET['q'])) {
                    $criteria->join .= ' JOIN page p1 ON(p1.id=:parent_id)';
                    $criteria->join .= ' JOIN page p2 ON(p2.lft BETWEEN p1.lft AND p1.rgt)';
                    $criteria->addCondition('t.parent_id=p2.id');
                    $criteria->addCondition('t.id!=:parent_id');
                } else $criteria->addCondition('t.parent_id=:parent_id');
                $criteria->addCondition('t.id!=:parent_id');
                $criteria->params['parent_id'] = $_GET['parent_id'];
            } elseif (in_array('num', $columns))
                $criteria->order = '`t`.`num` ASC, `t`.`id` ASC';
            elseif (in_array('lft', $columns))
                $criteria->order = '`t`.`lft` ASC, `t`.`id` DESC';
            else
                $sort = array('defaultOrder' => 't.id DESC');
            $criteria->group = '`t`.`id`';

            $page = isset($_GET['page']) ? $_GET['page'] * 1000 : 0;
            $start = isset($_GET['start']) ? $_GET['start'] : 0;
            $limit = isset($_GET['limit']) ? $_GET['limit'] : 50;
            $criteria->limit = $limit;
            $criteria->offset = $page + $start;

            $this->getSearchCriteria($criteria);

            $this->_dataProvider = new CActiveDataProvider($this->owner->cache('60*60', new CGlobalStateCacheDependency($this->getModule()->url))->resetScope(), compact('criteria', 'pagination', 'sort'));

            /* $count = Yii::app()->cache->get( $id = md5(serialize($criteria)));
             if($count === false){
                 if($_GET['q'])
                     else $count = $this->owner->getCommandBuilder()->createCountCommand($this->owner->getTableSchema(),$criteria,$this->owner->getTableAlias())->queryScalar();
                 Yii::app()->cache->set($id, $count, 5*60);
             }
             $this->_dataProvider->setTotalItemCount($count);*/
        }

        return $this->_dataProvider;
    }

    /** @var $criteria CDbCriteria */
    public function getSearchCriteria($criteria)
    {
        /*preg_match_all('&(([a-z]+):(\(([^\)]+)\)|([^\s]+))\s)&', $_GET['q'], $data);
        if($_GET['q']) {
            var_dump($data);die;
        }*/

        if (($query = $_GET['q']) && $query != '*')
            foreach (explode(' ', $query) as $q) if ($q) {
                list($attr, $value) = explode(':', $q);
                if ($value && ($this->owner->hasAttribute($attr))) {
                    $criteria->compare('t.' . $attr, $value, !is_numeric($value));
                } elseif ($value && ($this->owner instanceof PageBase && $this->owner->hasCharacter($attr))) {
                    $criteria->compare(Characters::getRelationByUrl($attr) . '.value', $value, !is_numeric($value));
                } else {
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
                    if ($this->owner instanceof PageBase) {
                        if (stripos($criteria->join, ' `cv`') === false)
                            $criteria->join .= ' INNER JOIN `character_varchar` `cv` ON(cv.page_id=t.id)';
                        $condition[] = "cv.value LIKE :textSearch";
                        $criteria->params['textSearch'] = "%{$q}%";
                    }
                    if (count($criteria->with)) foreach (array('user', 'currentUrl') as $val) if (isset($criteria->with[$val]) && !empty($criteria->with[$val]['select'])) {
                        $condition[] = "{$val}.{$criteria->with[$val]['select']} LIKE :textSearch";
                        $criteria->with[$val]['together'] = true;
                        $criteria->params['textSearch'] = "%{$q}%";
                    }
                    if (count($criteria->with)) foreach (array('page', 'parent') as $val) if (isset($criteria->with[$val]) && $criteria->with[$val]['with']['rName']) {
                        $condition[] = "pName.value LIKE :textSearch";
                        $criteria->with[$val]['together'] = true;
                        $criteria->params['textSearch'] = "%{$q}%";
                    }
                    $criteria->addCondition(implode(' OR ', $condition));
                }
            }
    }

    public function getColumns()
    {
        $owner = $this->getOwner();

        $default = array();
        if ($owner->hasAttribute('lft') || $owner->hasAttribute('num'))
            $default['order'] = array(
                'name' => '#',
                'value' => $owner->hasAttribute('lft') ? '$data->lft' : '$data->num',
                'type' => 'order',
                'cssClassExpression' => '"sorterHandler"',
            );
        else$default['step'] = array(
            'name' => '#',
            'value' => '',
            'type' => 'order',
        );
        $default['checkbox'] = array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 9999,
        );
        if ($this->owner->hasAttribute('is_category')) $default['is_category'] = array(
            'value' => '$data->is_category?$data->is_category:$data->hasNestedChild',
        );
        foreach ((array)$this->adminSettings['columns'] as $column) if (empty($default[$column])) {
            $default[$column] = array(
                'header' => $owner->getAttributeLabel($column),
                'value' => $this->columnValue($column),
                'type' => $this->getTypeFromList($column),
            );
            if (method_exists($owner, 'serializationAttributes') && in_array($column, $owner->serializationAttributes())){
                $default[$column]['type'] = 'arrayMode';
                $default[$column]['value'] = '$data->inLineData';
            }
        }
        $default['buttons'] = array(
            'header' => 'Действия',
            'class' => 'CButtonColumn',
            'template' => '{' . implode('} {', (array)$this->adminSettings['actions']) . '}',
            'buttons' => $this->getButtons(),
        );
        return $default;
    }


    public function columnValue($column)
    {
        switch ($column):
            case 'user_id':
                return '$data->user->username';
            case 'page_id':
                return '$data->page->name';
            case 'parent_id':
                return '$data->parent->name';
//            case 'status_id':
//                return '$data->status';
            default:
                return '$data->' . $column;
        endswitch;
    }

    public function getButtons()
    {
        $result = array();
        $module = $this->getModule();
        foreach ((array)$this->adminSettings['actions'] as $action) {
            $result[$action] = array(
                'label' => ucfirst($action),
                'url' => function ($data) use ($action, $module) {
                    return CHtml::normalizeUrl(array($action, "url" => $module->url, "id" => implode('--', (array)$data->getPrimaryKey())));
                },
                'imageUrl' => false,
                'options' => array(
                    'class' => "button" . ucfirst($action),
                ),
            );
        }
        return $result;
    }

    public function getForm()
    {
        return array(
            'showErrorSummary' => true,
            'elements' => $this->getElements(),
            'buttons' => array(
                'send' => array(
                    'type' => 'submit',
                    'label' => Yii::t('admin', $this->owner->isNewRecord ? 'Create' : 'Save'),
                ),
            ),
        );
    }

    public function applyDefaultElementConfig($elementConfig) {
        if($elementConfig['type'] == 'checkbox')
            $elementConfig['layout'] = '<div class="checkbox-single">{input}{label}</div>{hint}{error}';
        return $elementConfig;
    }

    /**
     * @return array|mixed
     */
    public function getElements()
    {
        $result = array();
        $elements = method_exists($this->owner, 'getElements') ? $this->owner->getElements() : array();
        foreach($elements as $elementId => $config) {
            $elements[$elementId] = $this->applyDefaultElementConfig($config);
        }
        foreach ((array)$this->adminSettings['elements'] as $row)
            if (in_array($row, array_keys($this->getOwner()->tableSchema->columns))) {
                $result['main'][$row] = $elements[$row] ? $elements[$row] : array();
            }
        if (method_exists($this->getOwner(), 'getCharacterNames'))
            foreach (Characters::getDataByUrls($this->getOwner()->getCharacterNames(true)) as $row)
                $result[$row['position']][$row['url']] = $this->applyDefaultElementConfig($this->getCharacterElement($row));
        if (method_exists($this->getOwner(), 'settingNames'))
            foreach ($this->getOwner()->settingNames() as $row)
                $result['additional'][$row] = array(
                    'name' => $row,
                    'type' => 'text',
                );
        if ($this->adminSettings['photos'] > 0) {
            $result['photos']['photo'] = Yii::app()->controller->widget('ext.RUpload.RPhotoUpload', array(
                'model' => $this->owner,
                'attribute' => 'photos',
                'crop' => $this->adminSettings['crop'],
                'max' => $this->adminSettings['photos'],
                'options' => array(
                    'url' => array('content/upload', 'model' => 'Photo'),
                ),
            ), 1);
        }
        if ($this->adminSettings['files'] > 0) {
            $result['files']['file'] = Yii::app()->controller->widget('ext.RUpload.RFileUpload', array(
                'model' => $this->owner,
                'attribute' => 'files',
                'max' => $this->adminSettings['files'],
                'options' => array(
                    'url' => array('content/upload', 'type' => 'UserFiles'),
                ),
            ), 1);
        }
        if (method_exists($this->getOwner(), 'getUrl') && isset($result['seo']))
            $result['seo']['url'] = $this->getCharacterElement(array(
                'url' => 'url',
                'label' => 'url',
                'inputType' => 'text',
            ));

        if (!empty($this->getOwner()->is_category)) unset($result['additional']);
        if (count($keys = array_keys($result)) == 1)
            return current($result);
        $data = array();
        $data[] = CHtml::openTag('div', array('id' => 'tabs'));
        $data[] = CHtml::openTag('ul');
        foreach ($keys as $key)
            $data[] = CHtml::tag('li', array(), CHtml::link($key, "#{$key}"));
        $data[] = CHtml::closeTag('ul');
        foreach ($result as $key => $row) {
            $data[] = CHtml::openTag('div', array('id' => $key));
            foreach ($row as $element => $value)
                $data[$element] = $value;
            $data[] = CHtml::closeTag('div');
        }
        $data[] = CHtml::closeTag('div');

        return $data;
    }

    public function getCharacterElement($row)
    {
        $data = array(
            'name' => $row['url'],
            'label' => $row['name'],
        );
        switch ($row['inputType']):
            case 'tags':
                return $data + array(
                    'type' => 'ext.RTagsInput.RTagsInput',
                    'cssFile' => false,
                    'query' => '.input-' . $row['inputType'],
                    'autoComplete' => array('autocompete', 'tag' => $row['id']),
                    'class' => 'input-' . $row['inputType'],
                );
            case 'wysiwyg':
                return $data + array(
                    'type' => 'ext.RTinyMCE.RTinyMCE',
                    'query' => '.input-' . $row['inputType'],
                    'class' => 'input-' . $row['inputType'],
                );
            case 'autocomplete':
                return $data + array(
                    'type' => 'zii.widgets.jui.CJuiAutoComplete',
                    'sourceUrl' => array('autocompete', 'tag' => $row['id']),
                    'cssFile' => false,
                );
            case 'date':
                return $data + array(
                    'type' => 'zii.widgets.jui.CJuiDatePicker',
                    'language' => Yii::app()->language,
                    'options' => array(
                        'dateFormat' => 'dd.mm.yy',
                        'changeMonth' => 'true',
                        'changeYear' => 'true',
                        'firstDay' => '1',
                    ),
                    'htmlOptions' => array('class' => 'datePicker'),
                    'layout' => '{label}{input}{hint}{error}',
                    'cssFile' => false,
                );
            case 'boolean':
                return $data + array(
                    'type' => 'checkbox',
                    'class' => 'input-' . $row['inputType'],
                );
            case 'fromlist':
                return $data + $this->getDataList($row['data']) + array(
                    'type' => 'ext.RChosen.RChosen',
                    'query' => '.input-' . $row['inputType'],
                    'options' => array(
                        'disable_search_threshold' => 10,
                    ),
                    'class' => 'input-' . $row['inputType'],
                    'cssFile' => false,
                );
            case 'tagsList':
                return $data + $this->getDataList($row['data']) + array(
                    'type' => 'ext.RChosen.RChosen',
                    'empty' => '',
                    'multiple' => true,
                    'query' => '.input-' . $row['inputType'],
                    'class' => 'input-' . $row['inputType'],
                    'cssFile' => false,
                );
            case 'numeric':
                return $data + array(
                    'type' => 'number',
                    'class' => 'input-' . $row['inputType'],
                );
            case 'price':
                return $data + array(
                    'type' => 'number',
                    'step' => '0.01',
                    'class' => 'input-' . $row['inputType'],
                );
            case 'widget':
                return $data + array(
                    'type' => $row['data'],
                    'class' => 'input-' . $row['inputType'],
                );
            default:
                return $data + array(
                    'type' => $row['inputType'],
                    'class' => 'input-' . $row['inputType'],
                );
        endswitch;
    }

    public function setModule($data)
    {
        $this->_module = $data;
        if (in_array('module_id', array_keys($this->owner->tableSchema->columns)))
            $this->owner->setAttribute('module_id', $data->id);
    }

    public function getModule()
    {
        return $this->_module;
    }

    public function setIsCategory($data)
    {
        $this->_isCategory = $data;
    }

    public function getIsCategory()
    {
        return $this->_isCategory;
    }

    /**
     * @return RActiveRecord|PageBase.
     */
    public function getOwner()
    {
        return parent::getOwner();
    }

    public function getDataList($data)
    {
        $count = substr_count(mb_strtoupper($data), 'SELECT');
        $items = array();
        if ($count == 2) {
            $items['autoCompleteUrl'][] = 'autocompete';
            list($items['autoCompleteUrl']['sql'], $items['sql']) = preg_split('/\n|\r\n?/', trim($data));
        } elseif ($count == 1) {
            $items['data'] = CHtml::listData(Yii::app()->db->createCommand($data)->queryAll(), 'key', 'value');
        } else {
            foreach (explode(',', $data) as $item) $items['data'][trim($item)] = trim($item);
        }
        return $items;
    }
}