<?php
/**
 * User: imyxz
 * Date: 2017/5/26
 * Time: 14:55
 * Github: https://github.com/imyxz/
 */
class commentAPI extends SlimvcController{
    function newReply(){
        $return=array();
        try{
            $json=$this->getRequestJson();
            $post_id=intval($json['post_id']);
            $content=$json['content'];

        }
        catch (Exception $e)
        {
            $return['status']=0;
            $return['message']=urlencode($e->getMessage());
            $this->outputJson($return);
        }



    }
}