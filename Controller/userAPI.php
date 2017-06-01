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
            $this->helper("user_helper")->updateUserAvatar($user_id,$avatar);
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
        $site_id=intval($_GET['site_id']);
        $site_info=$this->model("site_model")->getSiteInfoByID($site_id);
        if(!$site_info)
        {
            echo "site_id 参数错误！";
            return;
        }
        $this->helper("session_helper")->set("qq_login_site_id",$site_id);
        $this->helper("session_helper")->set("qq_login_state",$state);
        if(isset($_GET['viewing']))
            $jump=$_GET['viewing'];
        else
            $jump=urlencode($_SERVER['HTTP_REFERER']);
        $jump=substr($jump,0,250);
        $this->helper("session_helper")->set("qq_login_jump",$jump);
        global $Config;
        $client_id=$Config['QQ_connect_client_id'];

        header('Cache-control: private, must-revalidate');
        header("Location: " ."https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=$client_id&redirect_uri=$redirect_uri&state=$state");
    }
    function loginFromQQAuth()
    {
        try{
            global $Config;
            $state=$_GET['state'];
            $code=$_GET['code'];
            $client_id=$Config['QQ_connect_client_id'];
            $client_secure=$Config['QQ_connect_client_secret'];
            $session_state=$this->helper("session_helper")->get("qq_login_state");
            $session_jump=$this->helper("session_helper")->get("qq_login_jump");
            $site_id=$this->helper("session_helper")->get("qq_login_site_id");
            if(empty($state) || $state!=$session_state)
            {
                throw new Exception("检测到CSRF攻击行为！");
            }
            $this->helper("session_helper")->del("qq_login_state");
            $this->helper("session_helper")->del("qq_login_jump");
            $this->helper("session_helper")->del("qq_login_site_id");
            $redirect_uri="http://encuss.yxz.me/userAPI/loginFromQQAuth/";
            $redirect_uri=urlencode($redirect_uri);
            $response=@file_get_contents("https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=$client_id&client_secret=$client_secure&code=$code&redirect_uri=$redirect_uri");
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
                throw new Exception( "授权登录错误! ") ;
            }
            $access_token=$ret['access_token'];
            $expires_in=$ret['expires_in'];
            $refresh_token=$ret['refresh_token'];
            $response=@file_get_contents("https://graph.qq.com/oauth2.0/me?access_token=$access_token");


            $response=substr($response,strlen("callback( "),strlen($response)-strlen("callback( ")-3);
            $ret=json_decode($response,true);

            if(!$ret || empty($ret['openid']))
            {
                throw new Exception("拉取oepnid失败！");
            }

            $openid=$ret['openid'];

            $response=@file_get_contents("https://graph.qq.com/user/get_user_info?access_token=$access_token&oauth_consumer_key=$client_id&openid=$openid");
            $response=json_decode($response,true);
            if($response['ret']!=0)
            {
                throw new Exception("读取用户信息失败！");
            }
            if(!$this->helper("user_helper")->loginByQQ($openid,$access_token,$expires_in,$refresh_token,$response))
                throw new Exception("系统错误，登录失败！");
            $user_info=$this->helper("user_helper")->getUserInfo();
            $token_info=$this->model("sso_model")->generateToken($site_id,$user_info['user_id'],60*24*30);
            if(!$token_info)
                throw new Exception("系统错误，生成token失败！");
            header("Location: " . urldecode($session_jump) . "?token_id=" . $token_info['token_id'] . "&token_key=" .$token_info['token_key']);
        }catch (Exception $e)
        {
            $return['status']=0;
            $return['message']=urlencode($e->getMessage());
            $this->outputJson($return);
        }

    }
}