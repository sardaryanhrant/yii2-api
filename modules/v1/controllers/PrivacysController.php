<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\PrivacyResource;

class PrivacysController extends BaseController {

    public $modelClass = 'app\modules\v1\models\PrivacyResource';
    public $excludedFields = ['id','author_id'];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        return $actions;
    }

    public function actionIndex(){
        $p = PrivacyResource::find()->where(['author_id'=>$this->checkauthuser()])->one();
        return $p;
    }

    public function actionView($id){
        return [];
    }

}

