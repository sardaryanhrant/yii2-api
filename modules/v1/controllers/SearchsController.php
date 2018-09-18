<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\AudioResource;
use app\modules\v1\models\PostResource;
use app\modules\v1\models\UserResource;
use app\modules\v1\models\GroupResource;
use app\modules\v1\models\VideoResource;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use Yii;


class SearchsController extends BaseController {

    public $modelClass  = '';
    public $postType    = 'post';
    public $fieldType   = 'posttype';
    public $field1      = '';
    public $field2      = '';
    protected $excludedFields = ['user_password'];

    public function actionSearch()
    {
        $params = Yii::$app->request->queryParams;
        switch ($params['type']) {
            case 'user':
                $this->modelClass   = new UserResource();
                $this->postType     = '';
                $this->field1       = 'user_name';
                $this->field2       = 'user_last_name';
                $q                  = $params['q'];
                $relation           = [];
                break;
            case 'post':
                $this->modelClass   = new PostResource();
                $this->field1       = 'post_content';
                $q                  = $params['q'];
                $relation           = ['user','comments','likes'];
                break;
            case 'note':
                $this->modelClass   = new PostResource();
                $this->postType     = 'note';
                $relation           = ['user','comments','likes'];
                break;
            case 'video':
                $this->modelClass   = new VideoResource();
                $relation           = [];
                $this->postType     = '';
                $q                  = '';
                break;
            case 'audio':
                $this->modelClass   = new AudioResource();
                $this->postType     = '';
                $relation           = [];
                $q                  = '';
                break;
            case 'group':
                $this->modelClass   = new GroupResource();
                $this->field1       = 'group_name';
                $q                  = $params['q'];
                $this->postType     = '';
                $relation           = [];
                break;
        }


        $query = $this->modelClass::find();



       if($q !=''){
           if($this->field1){
               $query->andFilterWhere(['ilike', $this->field1, $q ])->with($relation)->asArray();
           }

           if($this->field2){
               $query->orFilterWhere(['ilike', $this->field2, $q])->with($relation)->asArray();
           }
       }

       if($this->postType){$query->andFilterWhere([$this->fieldType => $this->postType ])->with($relation)->asArray();}

        $dataProvider = new ActiveDataProvider(['query' => $query,]);

        return $dataProvider;

    }

}

