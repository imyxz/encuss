<?php
class user_model extends SlimvcModel
{

    function isUserExist($user_name)
    {

        return $this->queryStmt("select user_id from user_info WHERE user_name=? limit 1",
            "s",
            $user_name)->sum() >=1 ;
    }


    function checkUserPassword($username,$password)
    {
        $row=$this->queryStmt("select user_id from user_info WHERE user_name=? and user_password=? limit 1",
            "ss",
            $username,
            $password)->row();
        if($row)
            return $row['user_id'];
        else
            return false;
    }


    function getUserId($username)
    {
        $result=$this->queryStmt("select user_id from user_info where user_name=? limit 1",
            "s",
            $username)->row();
        if(!$result)    return false;
        return $result['user_id'];
    }


    function getUserInfo($userid)
    {
        return $this->queryStmt("select * from user_info where user_id=? limit 1",
            "i",
            $userid)->row();
    }

    function newUser($username,$password,$email,$nickname,$reg_ip)
    {
        if(!$this->queryStmt("insert into user_info set user_name=?,user_password=?,user_nickname=?,user_email=?,reg_time=now(),login_time=now(),reg_ip=?,user_avatar=''",
            "sssss",
            $username,
            $password,
            $nickname,
            $email,
            $reg_ip))
            return false;
        return $this->InsertId;
    }





}