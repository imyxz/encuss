<?php
/**
 * User: imyxz
 * Date: 2017/5/26
 * Time: 14:42
 * Github: https://github.com/imyxz/
 */
class post_model extends SlimvcModel{
    function getPostInfoBySiteInfo($site_id,$site_post_id)
    {
        return $this->queryStmt("select * from post_info WHERE site_id=? and site_post_id=? limit 1",
            "ii",
            $site_id,
            $site_post_id)->row();
    }
    function newSitePost($site_id,$site_post_id,$post_user_id,$post_status)
    {
        if(!$this->queryStmt("insert into post_info set site_id=?,site_post_id=?,post_user_id=?,post_content='',post_title='',post_image='',post_url='',post_time=now(),post_status=?",
            "iiii",
            $site_id,
            $site_post_id,
            $post_user_id,
            $post_status))
            return false;
        return $this->InsertId;
    }
    function updatePostContent($post_id,$content)
    {
        return $this->queryStmt("update post_info set post_content=? where post_id=?",
            "si",
            $content,
            $post_id);
    }
    function getPostInfoByPostID($post_id)
    {
        return $this->queryStmt("select * from post_info where post_id=? limit 1",
            "i",
            $post_id)->row();
    }
    function updatePostInfo($post_id,$title,$url,$image)
    {
        return $this->queryStmt("update post_info set post_title=?,post_url=?,post_image=? where post_id=?",
            "sssi",
            $title,
            $url,
            $image,
            $post_id);
    }
}