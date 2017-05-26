<?php
class indexs extends SlimvcController{
    function IndexAction()
    {
        $this->outputJson($this->model("index_model")->showTable("servos"));
    }
    function insertComment()
    {
        echo $this->model("comment_model")->insertComment(1,1,1,"awfwfawefew",1);

    }
    function getComment()
    {
        $this->outputJson($this->model("comment_model")->getPostComments(1,1,0,10));
    }
    function insertPost()
    {
        echo $this->model("post_model")->newSitePost(1,1,1,1);
    }
    function testHelper()
    {
        $this->helper("user_helper")->test();
        $this->helper("user_helper")->test();
        $this->helper("user_helper")->test();
        $this->helper("user_helper")->test();
        $this->helper("user_helper")->test();
    }
}