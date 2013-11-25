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
            'actions' => array('view', 'edit', 'delete'),
        );
    }

    public function getDataProvider()
    {
        if (!$this->_dataProvider) {
            $columns = $this->adminSettings['columns'];
            $criteria = new CDbCriteria();
            $criteria->addCondition('`t`.`id` > 0');

            if (method_exists($this->getOwner(), 'getCharacterNames')) {
                $characters = array_intersect((array)$this->adminSettings['columns'], $this->getOwner()->getCharacterNames());
                foreach ($characters as $character) $criteria->with[] = Characters::getRelationByUrl($character);
                $criteria->with['photo'] = array('select' => 'name');
                $criteria->with['currentUrl'] = array('select' => 'value');
                if (in_array('user_id', $columns)) $criteria->with['user'] = array('select' => 'username');
                $criteria->compare('`t`.`module_id`', $this->getModule()->id);
            }
            if ($_GET['limit']) {
                $_GET['myPage'] = floor($_GET['start'] / $_GET['limit']) + 1;
                $pagination = array(
                    'pageSize' => $_GET['limit'],
                    'pageVar' => 'myPage',
                );
            } else $pagination = false;

            $sort = false;

            if ($this->getModule()->type_id == Module::TYPE_SELF_NESTED || ($this->getModule()->type_id == Module::TYPE_NESTED && $this->getIsCategory() == true))
                $criteria->order = '`t`.`lft` ASC';
            if (in_array('num', array_keys($this->getOwner()->tableSchema->columns)))
                $criteria->order = '`t`.`num` ASC, `t`.`id`';
            else
                $sort = array('defaultOrder' => 't.id DESC');

            $criteria = $this->getSearchCriteria($criteria);
            $this->_dataProvider = new CActiveDataProvider($this->owner, compact('criteria', 'pagination', 'sort'));

            /*$count = Yii::app()->cache->get( $id = md5(serialize($criteria)));
            if($count === false){
                $count = $this->owner->getCommandBuilder()->createCountCommand($this->owner->getTableSchema(),$criteria,$this->owner->getTableAlias())->queryScalar();
                Yii::app()->cache->set($id, $count, 5*60);
            }
            $this->_dataProvider->setTotalItemCount($count);*/
        }

        return $this->_dataProvider;
    }

    public function getSearchCriteria($criteria)
    {
        if ($q = $_GET['q']) {
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
            if (get_class($this->getOwner()) == 'Page') {
                $criteria->join .= ' INNER JOIN `character_varchar` `cv` ON(cv.page_id=t.id)';
                $condition[] = "cv.value LIKE :textSearch";
                $criteria->params['textSearch'] = "%{$q}%";
            }
            if(count($criteria->with)) foreach (array('user', 'currentUrl') as $val) if (in_array($val, array_keys($criteria->with)) && !empty($criteria->with[$val]['select'])){
                $condition[] = "{$val}.{$criteria->with[$val]['select']} LIKE :textSearch";
                $criteria->with[$val]['together']=true;
                $criteria->params['textSearch'] = "%{$q}%";
            }
            $criteria->addCondition(implode(' OR ', $condition));
        }
//        CVarDumper::dump($criteria,10,1);

        return $criteria;
    }

    public function getColumns()
    {
        $default = array();
        $default['order'] = array(
            'name' => '#',
            'value' => '$data->id',
        );
        $default['checkbox'] = array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 9999,
        );
        foreach ((array)$this->adminSettings['columns'] as $column) {
            $default[$column] = array(
                'header' => $this->getOwner()->getAttributeLabel($column),
                'value' => '$data->' . $column . '',
            );
            if ($column == 'user_id') $default[$column]['value'] = '$data->user->username';
            if ($column == 'status_id') $default[$column]['value'] = '$data->status';
        }
        $default['buttons'] = array(
            'header' => 'Действия',
            'class' => 'CButtonColumn',
            'template' => '{' . implode('} {', $this->adminSettings['actions']) . '}',
            'buttons' => $this->getButtons(),
        );
        return $default;
    }

    public function getButtons()
    {
        $result = array();
        foreach ($this->adminSettings['actions'] as $button) {
            $result[$button] = array(
                'label' => Yii::t('admin.grid', ucfirst($button)),
                'url' => 'CHtml::normalizeUrl(array("' . $button . '", "url"=>"' . $this->getModule()->url . '", "id"=>$data->id))',
                'imageUrl' => false,
                'options' => array(
                    'class' => "button" . ucfirst($button),
                    'onclick' => 'modalIFrame(this);return false;',
                    'data-update' => 'contentGrid',
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
                    'label' => Yii::t('admin.form', $this->owner->isNewRecord ? 'Создать' : 'Сохранить'),
                ),
            ),
        );
    }

    public function getElements()
    {
        $result = array();
        $elements = method_exists($this->owner, 'getElements') ? $this->owner->getElements() : array();
        foreach ((array)$this->adminSettings['elements'] as $row)
            if (in_array($row, array_keys($this->getOwner()->tableSchema->columns))) {
                $result['main'][$row] = $elements[$row] ? $elements[$row] : array();
            }
        if (method_exists($this->getOwner(), 'getUrl'))
            $result['seo']['url'] = $this->getCharacterElement(array(
                'url' => 'url',
                'label' => 'url',
                'inputType' => 'text',
            ));
        if (method_exists($this->getOwner(), 'getCharacterNames'))
            foreach (Characters::getDataByUrls($this->getOwner()->getCharacterNames()) as $row)
                $result[$row['position']][$row['url']] = $this->getCharacterElement($row);
        if ($this->adminSettings['photos'] > 0) {
            $result['photos']['photo'] = Yii::app()->controller->widget('ext.RFileUpload.RFileUpload', array(
                'model' => $this->owner,
                'attribute' => 'photos',
                'options' => array(
                    'url' => array('content/upload'),
                ),
            ), 1);
        }

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
                    'query' => '.input-' . $row['inputType'],
                    'autoComplete' => array('autocompete', 'tag' => $row['id']),
                    'class' => 'input-' . $row['inputType'],
                );
            case 'wysiwyg':
                return $data + array(
                    'type' => 'ext.RTinyMCE.RTinyMCE',
                    'query' => '.input-' . $row['inputType'],
                    'attributes' => array('class' => 'input-' . $row['inputType']),
                );
            case 'autocomplete':
                return $data + array(
                    'type' => 'zii.widgets.jui.CJuiAutoComplete',
//                    'query' => '.input-' . $row['inputType'],
                    'sourceUrl' => array('autocompete', 'tag' => $row['id']),
                    'cssFile' => false,
                );
            case 'boolean':
                return $data + array(
                    'type' => 'checkbox',
                    'class' => 'input-' . $row['inputType'],
                );
            case 'fromlist':
                $items = array();
                if (strstr('SELECT', $row['data']) === false) {
                    foreach (explode(',', $row['data']) as $item) $items[trim($item)] = trim($item);
                }
                return $data + array(
                    'type' => 'ext.RChosen.RChosen',
                    'query' => '.input-' . $row['inputType'],
                    'data' => $items,
//                    'autoCompleteUrl' => array('autocompete'),
                    'class' => 'input-' . $row['inputType'],
                    'options' => array(
                        'disable_search_threshold' => 10,
                    ),
                    'cssFile' => false,
                );
            case 'numeric':
                return $data + array(
                    'type' => 'number',
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
            $this->owner->module_id = $data->id;
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
}