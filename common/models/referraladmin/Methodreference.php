<?php

namespace common\models\referraladmin;

use Yii;

/**
 * This is the model class for table "tbl_methodreference".
 *
 * @property int $methodreference_id
 * @property string $method
 * @property string $reference
 * @property double $fee
 * @property string $create_time
 * @property string $update_time
 *
 * @property Analysis[] $analyses
 * @property TestnameMethod[] $testnameMethods
 */
class Methodreference extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_methodreference';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['method', 'reference', 'fee', 'create_time'], 'required'],
            [['fee'], 'number'],
            [['create_time', 'update_time'], 'safe'],
            [['method', 'reference'], 'string', 'max' => 200],
            [['method', 'reference', 'fee'], 'unique', 'targetAttribute' => ['method', 'reference', 'fee']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'methodreference_id' => 'Methodreference ID',
            'method' => 'Method',
            'reference' => 'Reference',
            'fee' => 'Fee',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnalyses()
    {
        return $this->hasMany(Analysis::className(), ['methodreference_id' => 'methodreference_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTestnameMethods()
    {
        return $this->hasMany(TestnameMethod::className(), ['methodreference_id' => 'methodreference_id']);
    }
}