<?php
/**
 * User: imyxz
 * Date: 2017/6/4
 * Time: 14:16
 * Github: https://github.com/imyxz/
 */
class messageAPI extends SlimvcController
{
    function getUnreadMessage()
    {
        try{
            if($this->helper("user_helper")->isLogin()==false)
                throw new Exception("还未登录");
            $user_info=$this->helper("user_helper")->getUserInfo();
            $message_count=$this->model("message_model")->countUserMessage($user_info['user_id']);
            $return['unread']=$message_count;
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
    function getMessageDetail()
    {
        $return=array();
        try{
            if($this->helper("user_helper")->isLogin()==false)
                throw new Exception("还未登录");
            $user_info=$this->helper("user_helper")->getUserInfo();
            $user_id=$user_info['user_id'];
            $messages=$this->model("message_model")->getUserMessage($user_id,0,100);
            $min=-1;
            $max=-1;
            foreach($messages as &$one)
            {
                $return['message'][]=array(
                    "id"=>$one['message_id'],
                    "content"=>$one['message_content'],
                    "url"=>$one['message_url']);
                if($one['message_id']<$min || $min==-1)
                    $min=$one['message_id'];
                if($one['message_id']>$max || $max==-1)
                    $max=$one['message_id'];
            }
            $this->model("message_model")->setUserRangeMessageStatus($user_id,$min,$max,1);
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