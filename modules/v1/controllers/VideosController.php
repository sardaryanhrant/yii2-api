<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\VideoResource;

class VideosController extends BaseController {

    public $modelClass = 'app\modules\v1\models\VideoResource';
    public $excludedFields = ['id','author_id'];
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view']);

        return $actions;
    }

    public function actionView($id){
        $videos = VideoResource::find()->where(['author_id'=>$id])->all();
        return $videos;
    }

}

