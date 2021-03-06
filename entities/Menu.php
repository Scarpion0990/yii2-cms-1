<?php

namespace afzalroq\cms\entities;

use creocoder\nestedsets\NestedSetsBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "menu".
 *
 * @property int $id
 * @property string $title_0
 * @property string $title_1
 * @property string $title_2
 * @property string $title_3
 * @property int $type
 * @property string $type_helper
 * @property int $menu_type_id
 * @property int $created_at
 * @property int $updated_at
 * @property MenuType $menuType
 */
class Menu extends ActiveRecord
{
    #region Constants
    const TYPE_EMPTY = 1;
    const TYPE_ACTION = 2;
    const TYPE_LINK = 3;
    const TYPE_OPTION = 4;
    const TYPE_ITEM = 5;
    const TYPE_COLLECTION = 6;
    const TYPE_ENTITY = 7;
    const TYPE_ENTITY_ITEM = 10;
    #endregion

    #region Extra Attributes

    public $types;
    public $types_helper;
    public $option_id;

    public $action;
    public $link;
    public $treeAttribute = 'tree';
    private $CMSModule;

    #endregion

    #region Overrides

    public function __construct($config = [])
    {
        $this->CMSModule = Yii::$app->getModule('cms');
        parent::__construct($config);
    }

    public static function tableName()
    {
        return 'cms_menu';
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            'tree' => [
                'class' => NestedSetsBehavior::className(),
                'treeAttribute' => $this->treeAttribute,
                // 'leftAttribute' => 'lft',
                // 'rightAttribute' => 'rgt',
                // 'depthAttribute' => 'depth',
            ]
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert))
            return false;

        switch ($this->type) {
            case self::TYPE_ITEM:
                $this->type_helper = explode('_', $this->types)[1] . ',' . $this->type_helper;
                break;
        }

        return true;
    }

    public function afterFind()
    {
        $options = '';
        switch ($this->type) {
            case self::TYPE_EMPTY:
                $this->types = 'type_' . $this->type;
                break;

            case self::TYPE_ACTION:
                $this->types = 'action_' . $this->type_helper;
                break;

            case self::TYPE_LINK:
                $this->types = 'type_' . $this->type;
                $this->link = $this->type_helper;
                break;

            case self::TYPE_OPTION:
                if ($dependOption = Options::findOne($this->type_helper))
                    foreach (Options::findAll(['collection_id' => $dependOption->collection_id]) as $option)
                        $options .= '<option ' . (($this->type_helper == $option->id) ? 'selected' : '') . ' value=' . $option->id . '>' . $option->name_0 . '</option>';

                $this->types = 'collection_' . $dependOption->collection->id;
                $this->types_helper = $options;
                break;

            case self::TYPE_ITEM:
                $option_id = explode(',', $this->type_helper)[0];
                $item_id = explode(',', $this->type_helper)[1];
                foreach (OaI::findAll(['option_id' => $option_id]) as $oai)
                    $options .= '<option ' . (($oai->item_id == $item_id) ? 'selected' : '') . ' value=' . $oai->item_id . '>' . $oai->item->text_1_0 . '</option>';

                $this->types = 'option_' . $option_id;
                $this->types_helper = $options;
                $this->option_id = $option_id;
                $this->type_helper = $item_id;
                break;

            case self::TYPE_ENTITY_ITEM:
                if ($dependItem = Items::findOne($this->type_helper)) {
                    $entity_id = $dependItem->entity->id;
                    $this->types = 'entity_' . $entity_id;
                    foreach (Items::findAll(['entity_id' => $entity_id]) as $item)
                        $options .= '<option ' . (($item->id == $this->type_helper) ? 'selected' : '') . ' value=' . $item->id . '>' . $item->text_1_0 . '</option>';
                }

                $this->types_helper = $options;
                break;

            case self::TYPE_COLLECTION:
            case self::TYPE_ENTITY:

            default:
                $this->type_helper = '';
        }
        parent::afterFind();
    }

    public function rules()
    {
        return [
            ['title_0', 'required', 'when' => function () {
                return in_array(0, Yii::$app->params['cms']['languageIds']);
            }],
            ['title_1', 'required', 'when' => function () {
                return in_array(1, Yii::$app->params['cms']['languageIds']);
            }],
            ['title_2', 'required', 'when' => function () {
                return in_array(2, Yii::$app->params['cms']['languageIds']);
            }],
            ['title_3', 'required', 'when' => function () {
                return in_array(3, Yii::$app->params['cms']['languageIds']);
            }],
            [['title_0', 'title_1', 'title_2', 'title_3'], 'string', 'max' => 255],

            [['type', 'menu_type_id'], 'required'],
            [['type', 'menu_type_id'], 'integer'],
            ['types', 'string'],
            ['type_helper', 'string'],
            ['types_helper', 'string'],

            ['action', 'in', 'range' => self::actionsList(true), 'allowArray' => true, 'when' => function ($model) {
                return $model->type == self::TYPE_ACTION;
            }, 'enableClientValidation' => false],

            ['link', 'required', 'when' => function ($model) {
                return $model->type == self::TYPE_LINK;
            }, 'enableClientValidation' => false],
            ['link', 'string', 'when' => function ($model) {
                return $model->type == self::TYPE_LINK;
            }, 'enableClientValidation' => false],

        ];
    }

    public static function find()
    {
        return new MenuQuery(get_called_class());
    }

    public function attributeLabels()
    {
        $language0 = isset(Yii::$app->params['cms']['languages'][0]) ? Yii::$app->params['cms']['languages'][0] : '';
        $language1 = isset(Yii::$app->params['cms']['languages'][1]) ? Yii::$app->params['cms']['languages'][1] : '';
        $language2 = isset(Yii::$app->params['cms']['languages'][2]) ? Yii::$app->params['cms']['languages'][2] : '';
        $language3 = isset(Yii::$app->params['cms']['languages'][3]) ? Yii::$app->params['cms']['languages'][3] : '';

        return [
            'id' => Yii::t('cms', 'ID'),
            'title_0' => Yii::t('cms', 'Title') . '(' . $language0 . ')',
            'title_1' => Yii::t('cms', 'Title') . '(' . $language1 . ')',
            'title_2' => Yii::t('cms', 'Title') . '(' . $language2 . ')',
            'title_3' => Yii::t('cms', 'Title') . '(' . $language3 . ')',
            'type' => Yii::t('cms', 'Type'),
            'types' => Yii::t('cms', 'Type'),
            'type_helper' => Yii::t('cms', 'Type Helper'),
            'action' => Yii::t('cms', 'Module action'),
            'link' => Yii::t('cms', 'link'),
            'page_id' => Yii::t('cms', 'Page'),
            'articles_category_id' => Yii::t('cms', 'Articles'),
            'created_at' => Yii::t('cms', 'Created At'),
            'updated_at' => Yii::t('cms', 'Updated At'),
        ];
    }

    #endregion

    #region Extra Methods

    public function getMenuType()
    {
        $this->hasOne(MenuType::class, ['id' => 'menu_typ_id']);
    }

    public function actionsList($flip = false)
    {
        $array = [];
        if (is_string($flip) && array_key_exists($flip, $this->CMSModule->menuActions))
            return $array[$flip];
        elseif ($flip)
            return array_flip($this->CMSModule->menuActions);
        return $this->CMSModule->menuActions;
    }

    public function typesList($key = null)
    {
        $a = [];
        $a[self::TYPE_EMPTY] = 'Элемент меню для вложения подменю';
        $a[self::TYPE_LINK] = 'Ссылка';
        return ($key && isset($a[$key])) ? $a[$key] : $a;
    }

    public function typesLists($key = null)
    {
        $a = [];
        $a[self::TYPE_EMPTY] = 'Элемент меню для вложения подменю';
        $a[self::TYPE_LINK] = 'Ссылка';
        $a[self::TYPE_ACTION] = 'Action';
        $a[self::TYPE_OPTION] = 'Option';
        $a[self::TYPE_ITEM] = 'Item';
        $a[self::TYPE_ENTITY_ITEM] = 'Entity Item';

        if ($key && isset($a[$key])) {
            return $a[$key];
        } else {
            return $a;
        }
    }

    public function getTypeValue()
    {
        switch ($this->type) {
            case self::TYPE_ACTION:
                return $this->CMSModule->menuActions[$this->type_helper];
            case self::TYPE_OPTION:
                return Options::findOne($this->type_helper)->name_0;
            case self::TYPE_ITEM:
                return Options::findOne($this->option_id)->name_0 . ', ' . Items::findOne($this->type_helper)->text_1_0;
            case self::TYPE_ENTITY_ITEM:
                $item = Items::findOne($this->type_helper);
                return Html::a($item->text_1_0, Url::to(['items/view', 'id' => $this->type_helper, 'slug' => $item->entity->slug]));
            default:
                return $this->type_helper;
        }
    }

    //    public function getMaxSort()
    //    {
    //        return ((new ActiveQuery(self::class))
    //                ->from(self::tableName())
    //                ->max('sort')) + 1;
    //    }

    #endregion
}
