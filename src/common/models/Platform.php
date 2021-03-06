<?php

namespace common\models;

use common\definitions\Status;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "platform".
 *
 * @property integer $id
 * @property string  $name
 * @property string  $abbreviation
 * @property integer $status
 * @property string  $created_at
 * @property integer $created_by
 * @property string  $updated_at
 * @property integer $updated_by
 */
class Platform extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'platform';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'abbreviation'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'abbreviation' => 'Abbreviation',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d H:i:s')
            ],
            [
                'class' => BlameableBehavior::class,
                'value' =>  function(){
                    if (isset(\Yii::$app->user)){
                        return \Yii::$app->user->id ?? 0;
                    } else {
                        return 0;
                    }
                },
                'createdByAttribute' => false,
            ]
        ];
    }

    public function newData($data)
    {
        $model = new self;
        $model->name = $data->name ?? $data->platform;
        $model->abbreviation = $data->platform;
        $model->status = Status::ACTIVE;
        if ($model->save()) {
            return $model->id;
        } else {
            var_dump($model->errors);
        }

        return null;
    }

    public function storeData($data)
    {
        $have = self::getPlatform($data->platform);

        if (!$have) {
            return $this->newData($data);
        }

        return null;
    }

    public static function getPlatform($platform)
    {
        $result = self::find()
            ->where('abbreviation =:a', [':a' => $platform])
            ->andWhere(['status' => Status::ACTIVE])
            ->one();

        return $result;
    }

    public static function platformDropDownData()
    {
        $result = self::find()
            ->select('name')
            ->where(['status' => Status::ACTIVE])
            ->indexBy('id')
            ->column();

        return $result ?: ['empty' => '暂无平台'];
    }

    public static function platformDropDownDataOrderByPay()
    {
        $result = self::find()
            ->alias('p')
            ->select(['name', 'p.id pid', 'SUM(pay_money) pm'])
            ->leftJoin('arrange a', 'a.platform_id = p.id')
            ->groupBy('p.id')
            ->indexBy('pid')
            ->orderBy('pm DESC')
            ->column();

        return $result ?: ['empty' => '暂无平台'];
    }
}