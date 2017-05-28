<?php
/**
 * User: imyxz
 * Date: 2017/5/25
 * Time: 18:17
 * Github: https://github.com/imyxz/
 */
class comment_model extends SlimvcModel
{
    function getPostComments($post_id,$start,$count)
    {
        return $this->queryStmt("select comment_info.*,user_info.user_id,user_info.user_nickname,user_info.user_avatar from comment_info,user_info where comment_info.post_id=? and user_info.user_id=comment_info.comment_user_id  order by comment_info.comment_id desc limit ?,?",
            "iii",
            $post_id,
            $start,
            $count)->all();
    }
    function insertComment($post_id,$user_id,$parent_id,$content,$status=0)
    {
        if(!$this->queryStmt("insert into comment_info set post_id=?,comment_user_id=?,comment_content=?,comment_time=now(),comment_status=?,comment_parent_id=?",
            "iisii",
            $post_id,
            $user_id,
            $content,
            $status,
            $parent_id))
            return false;
        return $this->InsertId;
    }
    function getCommentInfo($comment_id)
    {
        return $this->queryStmt("select * from comment_info where comment_id=?",
            "i",
            $comment_id)->row();
    }
    function updateCommentStatus($comment_id,$status)
    {
        return $this->queryStmt("update comment_info set comment_status=? where comment_id=?",
            "ii",
            $status,
            $comment_id);
    }
}