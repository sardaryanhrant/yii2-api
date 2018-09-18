<?php

namespace app\modules\v1\controllers;
use app\modules\v1\models\AudioResource;


class AudiosController extends BaseController {

    public $modelClass = 'app\modules\v1\models\AudioResource';
    public $excludedFields = ['id','author_id'];

     public function actions()
    {
        $actions = parent::actions();
        unset($actions['view']);

        return $actions;
    }

    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionView($id){
        $audios = AudioResource::find()->where(['author_id'=>$id])->orderBy('id ASC')->all();
        return $audios;
    }





}

