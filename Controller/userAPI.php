<?php
/**
 * User: imyxz
 * Date: 2017/5/28
 * Time: 0:21
 * Github: https://github.com/imyxz/
 */
class userAPI extends SlimvcController
{
    function login()
    {
        try{
            if($this->helper("user_helper")->isLogin()==true)
                throw new Exception("已经登录了");
            $json=$this->getRequestJson();
            $user_name=$json['user_name'];
            $password=md5($json['user_password']);
            $user_id=$this->helper("user_helper")->checkUserPassword($user_name,$password);
            if(!$user_id) throw new Exception("账号或密码填写错误");
            $this->helper("user_helper")->loginUser($user_id);
            $user_info=$this->helper("user_helper")->getUserInfo();
            $return['avatar']=$user_info['user_avatar'];
            $return['nickname']=$user_info['user_nickname'];
            $return['status']=1;
            $this->outputJson($return);

        }
        catch (Exception $e)
        {
            $return['status']=0;
            $return['message']=urlencode($e->getMessage());
            $this->outputJson($return);
        }
    }
    function register()
    {
        try{
            if($this->helper("user_helper")->isLogin()==true)
                throw new Exception("已经登录了");
            $json=$this->getRequestJson();
            $user_name=trim($json['user_name']);
            $password=md5(trim($json['user_password']));
            $nickname=trim($json['nickname']);
            $avatar=trim($json['avatar']);
            $email=trim($json['email']);
            if(empty($user_name) || empty($password)|| empty($nickname)|| empty($email))
                throw new Exception("信息填写不完整");
            if(strlen($user_name)>60)   throw new Exception("用户名最多60个字符");
            if(strlen($nickname)>60)   throw new Exception("昵称最多60个字符");
            if(strlen($email)>60)   throw new Exception("邮件地址最多60个字符");
            if(strlen($avatar)>300) throw new Exception("头像网址不得多于300个字符");
            if($this->model("user_model")->isUserExist($user_name)) throw new Exception("用户名已存在！");
            if(!$user_id=$this->helper("user_helper")->newUser($user_name,$password,$email,$nickname,$_SERVER['REMOTE_ADDR']))
                throw new Exception("注册失败！");
            $this->model("user_model")->updateUserAvatar($user_id,$avatar);
            $return['status']=1;
            $this->outputJson($return);

        }
        catch (Exception $e)
        {
            $return['status']=0;
            $return['message']=urlencode($e->getMessage());
            $this->outputJson($return);
        }
    }
    function logout()
    {
        $this->helper("user_helper")->logoutUser();
    }
    function loginFromQQ()
    {
        $redirect_uri="http://encuss.yxz.me/userAPI/loginFromQQAuth/";
        $redirect_uri=urlencode($redirect_uri);
        $state=rand(1000,9999);
        $this->helper("session_helper")->set("qq_login_state",$state);
        $this->helper("session_helper")->set("qq_login_jump",substr(trim($_GET['viewing']),300));
        header('Cache-control: private, must-revalidate');
        header("Location: " ."https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101405718&redirect_uri=$redirect_uri&state=$state");
    }
    function loginFromQQAuth()
    {
        $state=$_GET['state'];
        $code=$_GET['code'];
        if(empty($state) || $state!=$this->helper("session_helper")->get("qq_login_state"))
        {
            echo "检测到CSRF攻击行为！";
            return;
        }
        $redirect_uri="http://encuss.yxz.me/userAPI/loginFromQQAuth/";
        $redirect_uri=urlencode($redirect_uri);
        $response=@file_get_contents("https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=101405718&client_secret=9f61edddc96c7aee943555ed8333b8b7&code=$code&redirect_uri=$redirect_uri");
        $response=explode("&",$response);
        $ret=array();
        foreach($response as $one)
        {
            $two=explode("=",$one);
            if(count($two)==2)
            {
                $ret[$two[0]]=$two[1];
            }
        }
        if(!isset($ret['access_token']))
        {
            echo "授权登录错误! " ;
            var_dump($response);
            return;
        }
        $access_token=$ret['access_token'];
        $expires_in=$ret['expires_in'];
        $refresh_token=$ret['refresh_token'];
        $response=@file_get_contents("https://graph.qq.com/oauth2.0/me?access_token=$access_token");


        $response=substr($response,strlen("callback( "),strlen($response)-strlen("callback( ")-3);
        $ret=json_decode($response,true);
        $client_id=$ret['client_id'];
        $openid=$ret['openid'];

        $response=@file_get_contents("https://graph.qq.com/user/get_user_info?access_token=$access_token&oauth_consumer_key=101405718&openid=$openid");
        $response=json_decode($response,true);
        if($response['ret']!=0)
        {
            echo "读取用户信息失败！";
            return;
        }
    }
}