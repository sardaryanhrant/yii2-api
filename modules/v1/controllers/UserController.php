<?php

namespace app\modules\v1\controllers;

use app\common\components\Jwt;
use app\modules\v1\models\BlockUserResource;
use app\modules\v1\models\FollowerResource;
use app\modules\v1\models\PrivacyResource;
use app\modules\v1\models\TokenResource;
use app\modules\v1\models\UserResource;
use app\modules\v1\models\FriendResource;
use Lcobucci\JWT\Token;
use Yii;
use yii\di\Instance;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use Lcobucci\JWT\Parser;

class UserController extends BaseController
{
    public $excludedFields = ['id','author_id','user_email'];
    public $excludeSearchFields = ['user_password'];
    public $modelClass = 'app\modules\v1\models\UserResource';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'][] = 'token';
        $behaviors['authenticator']['except'][] = 'forgotpassword';
        $behaviors['authenticator']['except'][] = 'create';

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'index'  => ['GET', 'HEAD'],
            'view'   => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);

        return $actions;
    }

    public function actionIndex(){
        if($this->checkauthuser()) {
            $blocked = BlockUserResource::find()->where( ['block_user' => $this->checkauthuser()] )->all();
            $blockedIds = array();
            foreach ($blocked as $b) {
                $blockedIds[] = $b['author_id'];
            }
            $users = UserResource::find()->where( ['not in', 'id', $blockedIds] )->all();
            return $users;
        }
    }


    /**
     * @return bool|mixed
     */
    public function checkauthuser()
    {

        $headers =  Yii::$app->request->headers;
        $token = explode(' ',$headers['authorization'])[1];

        $auth = (new Parser())->parse((string) $token);

        $id = Yii::$app->user->identity->id;
        $authID = $auth->getClaim('uid');

        if($authID == $id){
            return $authID;
        }else{
            return false;
        }
    }


    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionToken()
    {
        $request = Yii::$app->getRequest();
        $data = $request->getBodyParams();

        /**
         * Input data validation
         */
        if (empty($data)) {
            throw new BadRequestHttpException();
        }

        if (empty($data['user_name'])) {
            throw new BadRequestHttpException();
        }

        if (empty($data['user_password'])) {
            throw new BadRequestHttpException();
        }

        $hash =  \Yii::$app->getSecurity()->generatePasswordHash($data['user_password']);

        $user = UserResource::findOne(['user_email' => $data['user_name'] ]);
        if (empty($user)) {
            throw new UnauthorizedHttpException();
        }

        if (!Yii::$app->getSecurity()->validatePassword($data['user_password'], $user->user_password)) {
            throw new UnauthorizedHttpException();
        }

        /**
         * @var Jwt $jwt
         */
        $jwt = Instance::ensure('jwt', Jwt::class);
        /**
         * @var Token $token
         */
        $token = $jwt->createToken($user);

        $resource = new TokenResource();
        $resource->token = (string)$token;
        $resource->expired = $token->getClaim('exp', 0);
        $resource->setResourceRelationship('user', $user);
        unset($user['user_password']);

        return ['user'=>$user, 'auth'=>$resource];
    }


    /**
     * @param $id
     * @return UserResource|null
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $blocked = BlockUserResource::find()->where(['block_user'=>$this->checkauthuser()])->all();
        $blockedIds = array();
        foreach ($blocked as $b){
            $blockedIds[] = $b['author_id'];
        }

        if (!empty($id)) {
            $friends = FriendResource::find()->where(['user_id'=>$id])->orWhere(['friend_id'=>$id])->all();

            $friendIds = array();
            foreach ($friends as $friend){
                if($friend->user_id == $id){$friendIds[] =  $friend->friend_id;}else{$friendIds[] =  $friend->user_id;}
            }

            $follow_to = FollowerResource::find()->where(['user_id'=>$this->checkauthuser(), 'follow_to'=>$id, 'to'=>'user'])->one();
            $myfriends = UserResource::find()->where(['in', 'id', $friendIds])
                ->select('id,user_name,user_last_name,user_email,user_photo,user_status,user_date_of_birth,user_marital_status,user_gender,user_country,user_city')
                ->all();

            $resource = UserResource::find()
                        ->where(['users.id' => $id])
                        ->andWhere(['not in', 'id', $blockedIds])
                        ->with(['groups','privacy'])->asArray()
                        ->one();



            if (!empty($resource)) {
                unset($resource['user_password']);

                $basicInfoPrivacy = $resource['privacy']['basic_info'];
                $resource['friends'] = $myfriends;

                if($basicInfoPrivacy == 3 && $id != $this->checkauthuser()){
                    unset($resource['friends']);
                }
                if($basicInfoPrivacy == 2 && $id != $this->checkauthuser()){
                    $isFriend = FriendResource::find()->where(['user_id'=>$this->checkauthuser(), 'friend_id'=>$id, 'subscription'=>1])
                        ->orWhere(['user_id'=>$id, 'friend_id'=>$this->checkauthuser(), 'subscription'=>1])->one();
                    if(empty($isFriend)){
                        $resource['friends'] = array();
                    }

                }
                if(!empty($follow_to)){
                    $resource['follow'] = true;
                }else{
                    $resource['follow'] = false;
                }
                return $resource;
            }else{
                throw new BadRequestHttpException("User not found or you are blocked by him/her");
            }
        }
    }


    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {

        $request = Yii::$app->getRequest();
        $data = $request->getBodyParams();
      
        if (empty($data['user_email'])) {
            throw new BadRequestHttpException();
        }

        /*Check if user exist with this email*/
        $userExist = UserResource::find()->where(['user_email'=>$data['user_email']])->one();
        if(!empty($userExist)){
            throw new BadRequestHttpException("User with email=".$data['user_email'].' already exist');
        }

        $hash =  \Yii::$app->getSecurity()->generatePasswordHash($data['user_password']);        

        $user = new UserResource();
        $user->user_name = $data['user_first_name'];
        $user->user_email = $data['user_email'];
        $user->user_last_name = $data['user_last_name'];
        $user->user_password = $hash;
        $user->save();
        $user->author_id = $user->id;
        $user->update();

        /*Create Record in privacy for new user*/
        $newPrivacy = new PrivacyResource();
        $newPrivacy->author_id = $user->id;
        $newPrivacy->save();

        $newUser = UserResource::findOne($user->id);  


        if (!Yii::$app->getSecurity()->validatePassword($data['user_password'], $user->user_password)) {
            throw new UnauthorizedHttpException();
        }

        /**
         * @var Jwt $jwt
         */
        $jwt = Instance::ensure('jwt', Jwt::class);
        /**
         * @var Token $token
         */
        $token = $jwt->createToken($user);

        $resource = new TokenResource();
        $resource->token = (string)$token;
        $resource->expired = $token->getClaim('exp', 0);
        $resource->setResourceRelationship('user', $user);

        unset($newUser['user_password']);

        return ['user'=>$newUser, 'auth'=>$resource, 'privacy'=>$newPrivacy];
    }


    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionGetfriends($id)
    {
        $friends  = FriendResource::find()->where(['user_id'=>$id])->all();
        return $friends;
    }


    /**
     * @return array|null|\yii\db\ActiveRecord
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdateuser()
    {
        $acceptableFields = ['user_name','user_last_name', 'user_photo','user_first_name',
                            'user_location', 'user_date_of_birth','user_gender','author_id',
                            'user_marital_status', 'user_password', 'user_country','user_city', 'user_active', 'user_speed'
                            ];

        if($this->checkauthuser()){
            $authUser = UserResource::find()->where(['id'=> $this->checkauthuser()])->one();
            $request = Yii::$app->getRequest();
            $data = $request->getBodyParams();

            foreach ($data as $key=>$value) {

                if($authUser->hasProperty($key) && in_array($key, $acceptableFields)){
                    if($key == 'user_password'){
                        if (Yii::$app->getSecurity()->validatePassword($data['user_password'], $authUser->user_password)) {
                            $authUser->user_password = \Yii::$app->getSecurity()->generatePasswordHash($data['new_password']);
                            $authUser->update();
                            return ['status'=>'OK', 'message'=>'Password Changed'];
                        }
                    }else{
                        $authUser->$key = $value;
                    }
                }
            }
            $authUser->update();
            return $authUser;
        }else{
            throw new BadRequestHttpException("You hav not permission to change user account with id=".$id);
        }
    }



    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionAddchatlist($id)
    {
        $me = UserResource::findOne($this->checkauthuser());
        $user = UserResource::findOne($id);
        if($id != $this->checkauthuser() && !empty($user)){
            $chatList = $me->user_chat_list;

            if(!in_array($id, $chatList)){
                $chatList[] = $id;
                $me->user_chat_list = $chatList;
                $me->update();
                return ['status'=>'OK', 'message'=>'User added in your chat list'];
            }else{
                return ['message'=>'User is in your chatlist'];
            }
        }elseif(empty($user)){
            throw new BadRequestHttpException("User with id=".$id." does not exist in our DB");
        }else{
            throw new BadRequestHttpException("You cannot add yourself in your chatlist");
        }
    }


    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetchatlist()
    {
        if($this->checkauthuser()){
            $me = UserResource::findOne($this->checkauthuser());
            $chatlist = $me->user_chat_list;

            if(!empty($chatlist)){
                $userChatList = UserResource::find()
                ->where(['id'=>$chatlist['chatList']])
                ->select('id, user_name, user_last_name, user_photo')
                ->all();

                $userNewChatList = array();
                foreach ($userChatList as $value) {
                    $userNewChatList[] =['id' => $value->id, 'displayName' => $value->user_name . ' ' . $value->user_last_name, 'avatar' => $value->user_photo, 'status'=>0];
                }
                return $userNewChatList;
            }else{
                return [];
            }
        }else{
           throw new BadRequestHttpException("Permission defined to other chatlist"); 
        }
    }



    public function actionRemoveuserfromchatlist()
    {
        if($this->checkauthuser()){
            $request = Yii::$app->getRequest();
            $data = $request->getBodyParams();

            $me = UserResource::findOne($this->checkauthuser());

            $chatlist = $me->user_chat_list;

            if (($key = array_search($data['id'], $chatlist['chatList'])) !== false) {
                array_splice($chatlist['chatList'], $key, 1);
            }  

            $me->user_chat_list =  $chatlist; 
            $me->update();
            return $chatlist;
        }else{
           throw new BadRequestHttpException("Permission defined to other chatlist"); 
        }
    }


    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionAdduserblacklist()
   {
        $request = Yii::$app->getRequest();
        $data = $request->getBodyParams();

        $user_id = $data['user_id'];

        if($this->checkauthuser()){
            $me = UserResource::findOne($this->checkauthuser());

            $user = UserResource::findOne($user_id);
            $blacklist = $me->user_blacklist;
            if(!in_array($user_id, $blacklist) && !empty($user) && $user_id != $this->checkauthuser()){
                $blacklist[] = $user_id;
                $me->user_blacklist = $blacklist;
                $me->update();
                return ['status'=>'OK', 'message'=>'User added in you blacklist'];
            }elseif(empty($user)){
                throw new BadRequestHttpException("User with id=".$user_id." does not exist in our DB");
            }elseif($user_id == $this->checkauthuser()){
                return ['message'=>'You cannot add yourself to your blacklist :)'];                
            }elseif(in_array($user_id, $blacklist)){
                return ['message'=>'User is in your blacklist'];
            }
        }else{
           throw new BadRequestHttpException("Permission defined to other chatlist"); 
        }
    }


    /**
     * @return mixed
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdatecontact()
    {
        if($this->checkauthuser()){
            $request = Yii::$app->getRequest();
            $data = $request->getBodyParams();

            $acceptableFields = ['user_mobile', 'user_twitter', 'user_facebook', 'user_website', 'user_skype'];

            $me = UserResource::findOne($this->checkauthuser());

            $userContact = $me->user_contact;
            
            foreach ($data as $key => $value) {
                if(in_array($key, $acceptableFields)){
                    $userContact[$key] = $value;
                }
            }
            $me->user_contact = $userContact;
            $me->update();
            return $me->user_contact;
        }
    }


    /**
     * @return mixed
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdatepersonalinfo()
    {
        if($this->checkauthuser()){
            $request = Yii::$app->getRequest();
            $data = $request->getBodyParams();

            $acceptableFields = ['activities', 'interests', 'favorite_munshids', 'favorite_preachers', 'favorite_books', 'favorite_sports', 'favorite_quotes', 'about_me'];

            $me = UserResource::findOne($this->checkauthuser());
            $userInterests = $me->user_interests;            
            foreach ($data as $key => $value) {
                if(in_array($key, $acceptableFields)){
                    $userInterests[$key] = $value;
                }
            }
            $me->user_interests = $userInterests;
            $me->update();
            return $userInterests;
        }
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionForgotpassword(){
        $request = Yii::$app->getRequest();
        $data = $request->getBodyParams();

        $user = UserResource::find()->where(['user_email'=>$data['user_email']])->one();
        if(empty($user)){
            throw new NotFoundHttpException("User does not exist");
        }else{
            $hash = md5($data['user_email']);
            Yii::$app->mailer->compose()
                ->setFrom('admin@globstage.com')
                ->setTo($data['user_email'])
                ->setSubject('Forgot Globstage Password')
                ->setTextBody('Plain text content')
                ->setHtmlBody('<p>
                    You can change your password from <a href="http://globstage.com/?hash='.$hash.'">here</a>
                </p> ')
                ->send();
            $user->password_hash = md5($data['user_email']);
            $user->update();
            return ['status'=>'OK', 'message'=>'Please check your email'];
        }
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionChangepassword(){
        $request = Yii::$app->getRequest();
        $data = $request->getBodyParams();
        $password = $data['new_password'];
        $cpassword = $data['confirm_password'];
        $hash = $data['hash'];

        $user = UserResource::find()->where(['password_hash'=>$hash])->one();
        if(!empty($user) && $password == $cpassword){
            $userPassword = \Yii::$app->getSecurity()->generatePasswordHash($password);
            $user->user_password = $userPassword;
            $user->update();
            return ['status'=>'OK', 'message'=>'Password changed'];
        }else{
            throw new NotFoundHttpException("User does not exist");
        }
    }

}