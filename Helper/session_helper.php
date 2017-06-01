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
    private $session_key;
    const SESSION_MINUTE=60*24*30;
    function __construct()
    {
        if (!($tmp = $this->getSession())) {
            $this->delAllSessionCookie();
            $this->session_info=array();
            $this->session_key = $this->getRandMd5();
            $this->session_id = $this->model("session_model")->newSession($this->session_key, self::SESSION_MINUTE);
            $this->updateSessionInfo();
        }
        else
        {
            $this->session_info=json_decode($tmp['session_info'],true);
            $this->session_id=$tmp['session_id'];
            $this->session_key=$tmp['session_pass'];
        }
        $this->updateSessionCookieTime();
        header('P3P: "DSP IDC CUR ADM DELi STP NAV COM UNI INT PHY DEM"');

    }
    function __destruct()
    {
        if(isset($this->session_id) && $this->session_id>0)
            $this->updateSessionInfo();
    }
    function updateSessionInfo()
    {
        $this->model("session_model")->updateSessionInfo($this->session_id,json_encode($this->session_info),self::SESSION_MINUTE);
    }
    function destroySession()
    {
        $this->delAllSessionCookie();
        $this->model("session_model")->delSession($this->session_id);
        $this->session_info=array();
        $this->session_id=0;
    }
    function get($key)
    {
        if(isset($this->session_info[$key]))    return $this->session_info[$key];
        else    return false;
    }
    function set($key,$value)
    {
        $this->session_info[$key]=$value;
    }
    function del($key)
    {
        unset($this->session_info[$key]);
    }
    function &getSessionInfo()
    {
        return $this->session_info;
    }
    private function delAllSessionCookie()
    {
        foreach($_COOKIE as $key => &$value)
        {
            if(substr($key,0,15)=='encuss_session_')
            {
                setcookie($key,'',1,'/');
            }
        }
    }
    private function updateSessionCookieTime()
    {
        setcookie('encuss_session_' . $this->session_id,$this->session_key,time()+60*self::SESSION_MINUTE,'/');
        $this->model("session_model")->renewSessionTime($this->session_id,self::SESSION_MINUTE);
    }
    private function getSession()
    {
        $session=$this->getCookieSession();
        if(!$session)
            return false;
        $session_info=$this->model("session_model")->getSessionInfo(intval($session['id']));
        if(!$session_info || $session_info['session_pass']!=$session['key'])
            return false;
        return $session_info;
    }
    private function getCookieSession()
    {
        foreach($_COOKIE as $key => &$value)
        {
            if(substr($key,0,15)=='encuss_session_')
            {
                return array("id"=>intval(substr($key,15)),
                    "key"=>$_COOKIE[$key]);
            }
        }
        return false;
    }
    private function getRandMd5()
    {
        return md5((time()/2940*time()/rand(1024,2325333)) . time() . "awoefpewofiajwepoisdnvsiejfwwaeifhpwhaaerghwrifpspdvnw" . rand(100000,1000000));
    }

}