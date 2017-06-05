<?php
/**
 * User: imyxz
 * Date: 2017/6/4
 * Time: 14:07
 * Github: https://github.com/imyxz/
 */
class message_model extends SlimvcModel
{
    function insertMessage($user_id,$content,$url,$status)
    {
        if(!$this->queryStmt("insert into user_message set user_id=?,message_content=?,message_url=?,message_time=now(),message_status=?",
            "issi",
            $user_id,
            $content,
            $url,
            $status))
            return false;
        return $this->InsertId;
    }
    function getUserMessage($user_id,$start,$count)
    {
        return $this->queryStmt("select *,unix_timestamp(message_time) as message_timestamp from user_message where user_id=? and message_status=0 order by message_id desc limit ?,?",
            "iii",
            $user_id,
            $start,
            $count)->all();
    }
    function countUserMessage($user_id)
    {
        return $this->queryStmt("select message_id from user_message where user_id=?",
            "i",
            $user_id)->sum();
    }
    function setUserMessageStatus($message_id,$user_id,$status)
    {
        return $this->queryStmt("update user_message set message_status=? where message_id=? and user_id=? limit 1",
            "iii",
            $status,
            $message_id,
            $user_id);

    }
    function setUserRangeMessageStatus($user_id,$start,$end,$status)
    {
        return $this->queryStmt("update user_message set message_status=? where user_id=? and message_id>=? and message_id<=?",
            "iiii",
            $status,
            $user_id,
            $start,
            $end);
    }
}