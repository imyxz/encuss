<?php
/**
 * User: imyxz
 * Date: 2017/5/26
 * Time: 15:32
 * Github: https://github.com/imyxz/
 */
class user_helper extends SlimvcHelper
{
    private $user_id;
    private $user_info;
    private $is_login;
    function __construct()
    {
        $this->user_id=$this->helper("session_helper")->get("user_id");
        if(empty($this->user_id))
        {
            $this->user_id=0;
            $this->is_login=false;
            $this->user_info=array();
        }
        else
        {
            $this->is_login=true;
            $this->user_info=$this->model("user_model")->getUserInfo($this->user_id);
        }
    }
    function getUserInfo()
    {
        return $this->user_info;
    }
    function isLogin()
    {
        return $this->is_login;
    }
    function newUser($username,$password,$email,$nickname,$reg_ip)
    {
        return $this->model("user_model")->newUser($username,$password,$email,$nickname,$reg_ip);
    }
    function checkUserPassword($username,$password)
    {
        return $this->model("user_model")->checkUserPassword($username,$password);
    }
    function updateUserNickname($user_id=0,$nickname)
    {
        if($user_id==0)
            $user_id=$this->user_id;
        return $this->model("user_model")->updateUserNickname($user_id,$nickname);
    }
    function updateUserAvatar($user_id=0,$avatar)
    {
        if($user_id==0)
            $user_id=$this->user_id;
        return $this->model("user_model")->updateUserAvatar($user_id,$avatar);
    }

    function loginUser($userid)
    {
        $this->helper("session_helper")->set("user_id",$userid);
        $this->is_login=true;
        $this->user_id=$userid;
        $this->user_info=$this->model("user_model")->getUserInfo($userid);
    }
    function loginByQQ($open_id,$access_token,$expires_in,$refresh_token,$info)
    {
        $nickname=$info['nickname'];
        if(isset($info['figureurl_qq_2']))
            $avatar=$info['figureurl_qq_2'];
        else
            $avatar=$info['figureurl_qq_1'];

        if(!$this->is_login)
        {
            $open_id_info=$this->model("qq_connect_model")->getInfoByOpenID($open_id);
            if($open_id_info)//˵�����û�ͨ��qq��������
            {
                $user_id=$open_id_info['user_id'];
                $this->model("qq_connect_model")->updateAllInfoByOpenID($open_id,$user_id,$access_token,$refresh_token,$expires_in,$nickname,json_encode($info));

                $this->updateUserAvatar($user_id,$avatar);
                $this->updateUserNickname($user_id,$nickname);
                $this->loginUser($user_id);

            }
            else//˵�����û�ͨ��qq��������
            {
                $user_id=$this->newUser("_qqconnect_" . $open_id,"QQCONNECT","NULL",$nickname,$_SERVER['REMOTE_ADDR']);
                if($user_id<1)
                    return false;
                $this->model("qq_connect_model")->newQQConnect($open_id,$user_id,$access_token,$refresh_token,$expires_in,$nickname,json_encode($info));
                $this->updateUserAvatar($user_id,$avatar);
                $this->loginUser($user_id);
            }
        }
        else
        {
            $open_id_info_by_userid=$this->model("qq_connect_model")->getInfoByUserID($this->user_id);
            $open_id_info_by_openid=$this->model("qq_connect_model")->getInfoByOpenID($open_id);
            if($open_id_info_by_openid)//˵���û����Ե�¼��һ�˺�
            {
                $this->loginUser($open_id_info_by_openid['user_id']);
            }
            else
            {
                if(!$open_id_info_by_userid)//˵���û��ڰ�QQ����
                {
                    $this->model("qq_connect_model")->newQQConnect($open_id,$this->user_id,$access_token,$refresh_token,$expires_in,$nickname,json_encode($info));
                    $this->updateUserAvatar(0,$avatar);
                    $this->updateUserNickname(0,$nickname);
                }
                else//˵���û��л��˰󶨵�QQ�����˺�
                {
                    //��Ϊ��ʱ��ֹ
                    return true;
                }
            }

        }
        return true;
    }

    function logoutUser($userid=0)
    {

        if($userid==0)
            $userid=$this->user_id;
        if($this->is_login=true && $userid>0)
        {
            $this->helper("session_helper")->destroySession();
            $this->user_id=0;
            $this->is_login=false;
            $this->user_info=array();
        }
    }
}