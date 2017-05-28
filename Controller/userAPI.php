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
                throw new Exception("�Ѿ���¼��");
            $json=$this->getRequestJson();
            $user_name=$json['user_name'];
            $password=md5($json['user_password']);
            $user_id=$this->helper("user_helper")->checkUserPassword($user_name,$password);
            if(!$user_id) throw new Exception("�˺Ż�������д����");
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
                throw new Exception("�Ѿ���¼��");
            $json=$this->getRequestJson();
            $user_name=trim($json['user_name']);
            $password=md5(trim($json['user_password']));
            $nickname=trim($json['nickname']);
            $email=trim($json['email']);
            if(empty($user_name) || empty($password)|| empty($nickname)|| empty($email))
                throw new Exception("��Ϣ��д������");
            if(strlen($user_name)>60)   throw new Exception("�û������60���ַ�");
            if(strlen($nickname)>60)   throw new Exception("�ǳ����60���ַ�");
            if(strlen($email)>60)   throw new Exception("�ʼ���ַ���60���ַ�");
            if($this->model("user_model")->isUserExist($user_name)) throw new Exception("�û����Ѵ��ڣ�");
            if(!$this->helper("user_helper")->newUser($user_name,$password,$email,$nickname,$_SERVER['REMOTE_ADDR']))
                throw new Exception("ע��ʧ�ܣ�");
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
}