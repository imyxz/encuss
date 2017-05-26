<?php
/**
 * User: imyxz
 * Date: 2017/5/26
 * Time: 16:28
 * Github: https://github.com/imyxz/
 */
class session_helper extends SlimvcHelper
{
    private $session_info=array();
    private $session_id;
    private $seesion_key;
    const SESSION_MINUTE=60*24*30;//Ò»¸öÔÂ
    function __construct()
    {
        if(!($tmp=$this->getSession()))
        {
            $this->delAllSessionCookie();
            $this->session_info['user_id']=0;
            $this->seesion_key=$this->getRandMd5();
            $this->session_id=$this->model("session_model")->newSession($this->seesion_key,self::SESSION_MINUTE);
        }
    }
    function updateSessionInfo()
    {
        $this->model("session_model")->updateSessionInfo($this->session_id,json_encode($this->session_info),self::SESSION_MINUTE);
    }
    private function delAllSessionCookie()
    {
        foreach($_COOKIE as $key => &$value)
        {
            if(substr($key,0,15)=='encuss_session_')
            {
                setcookie($key,0,1);
            }
        }
    }
    private function getSession()
    {
        foreach($_COOKIE as $key => &$value)
        {
            if(substr($key,0,15)=='encuss_session_')
            {
                return array("id"=>intval(substr($key,9)),
                    "key"=>$_COOKIE['encuss_session_' . $key]);
            }
        }
        return false;
    }
    private function getRandMd5()
    {
        return md5((time()/2940*time()/rand(1024,2325333)) . time() . "awoefpewofiajwepoisdnvsiejfwwaeifhpwhaaerghwrifpspdvnw" . rand(100000,1000000));
    }

}