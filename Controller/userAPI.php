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
}