/**
 * Created by i on 2017/5/24.
 */
var site_id;
var site_post_id;
var post_id;
var encuss_comments;
var user_nickname;
var user_avatar;
var isLogin=false;
var encuss_sub_replybox;
var encuss_allreplys=new Object();
encussInit();
function encussInit()
{
    includeCss("http://localhost/encuss/css/embed.css");
    site_id=encussConfig.site_id;
    var encuss=document.getElementsByClassName("encuss-div")[0];
    site_post_id=encuss.dataset['postId'];
    var ajax=new XMLHttpRequest();
    ajax.open("GET","http://localhost/encuss/commentAPI/getPostReplys/site_id/" + site_id + "/site_post_id/" +site_post_id,true);
    ajax.onreadystatechange=function(){
        if (ajax.readyState==4 && ajax.status==200) {
            var comment = document.createElement("div");
            comment.className = "encuss-comments";
            var response = JSON.parse(ajax.responseText);
            for (var index in response['replys']) {
                encuss_allreplys[response['replys'][index]['comment_id']] = response['replys'][index];
            }
            for (var index in response['replys']) {
                comment.appendChild(createCommentNode(response['replys'][index]));
            }
            if (encussConfig.is_nest)//嵌套回复处理
            {
                for(var index in encuss_allreplys)
                {
                    if(encuss_allreplys[index].parent_id>0)
                    {
                        encuss_allreplys[index].element.style.marginLeft="20px";
                        encuss_allreplys[encuss_allreplys[index].parent_id].element.appendChild(encuss_allreplys[index].element);
                    }
                }
            }
            post_id=response.post_id;
            if(response.isLogin==true)
            {
                user_nickname=response.nickname;
                user_avatar=response.avatar;
                isLogin=true;
            }
            else
            {
                isLogin=false;
            }

            encuss.appendChild(comment);
            encuss.appendChild(createReplyBox(0));
            encuss_comments=document.getElementsByClassName("encuss-div")[0].getElementsByClassName("encuss-comments")[0];

            encuss_sub_replybox=createReplyBox(0);
            encuss_sub_replybox.style.marginLeft="20px";

        }
    };
    ajax.send();

}
function includeCss(url) {
    var link = document.createElement("link");
    link.rel = "stylesheet";
    link.type = "text/css";
    link.href = url;
    document.getElementsByTagName("head")[0].appendChild(link);
}
function createCommentNode(info)
{
    var ele=document.createElement("div");
    ele.className="encuss-comments-node";
    ele.innerHTML='<img class="encuss-avatar">\
        <span class="encuss-username"></span>\
    <span class="encuss-comment-content-reply"></span>\
    <span class="encuss-comment-content"></span>\
    <span class="encuss-comment-time"></span>\
<span class="encuss-comment-action">\
    <a href="javascript:void(0)" onclick="moveRelpyBox(this)">回复</a>\
    <a href="#">顶</a>\
    </span>';
    info.content_reply="";
    if(info.parent_id>0)
    {
        info.content_reply="回复 @" + encuss_allreplys[info.parent_id].user_nickname + ": "+encuss_allreplys[info.parent_id].content ;
    }
    ele.getElementsByClassName("encuss-avatar")[0].src=info.user_avatar;
    ele.getElementsByClassName("encuss-username")[0].innerText=info.user_nickname;
    if (!encussConfig.is_nest)
        ele.getElementsByClassName("encuss-comment-content-reply")[0].innerText=info.content_reply;
    ele.getElementsByClassName("encuss-comment-content")[0].innerText=info.content;
    ele.getElementsByClassName("encuss-comment-time")[0].innerText=info.time;
    ele.dataset.comment_id=info.comment_id;
    encuss_allreplys[info.comment_id].element=ele;
    return ele;
}
function createReplyBox(parent_id)
{
    var ele=document.createElement("div");
    ele.className="encuss-replybox";
    ele.innerHTML='    <img class="encuss-avatar">\
        <div class="encuss-replybox-area">\
    <label><textarea placeholder="说点什么吧......"></textarea></label>\
</div>\
<div class="encuss-replybox-action">\
    <button class="encuss-reply-button" onclick="submitComment(this)">发布</button>\
    </div>';
    ele.dataset.parent_id=parent_id;
    ele.getElementsByClassName("encuss-avatar")[0].src=user_avatar;
    return ele;
}
function submitComment(curObj)
{
    var replyboxNode=curObj.parentNode.parentNode;
    var parent_id=replyboxNode.dataset['parent_id'];
    var textarea=replyboxNode.getElementsByClassName("encuss-replybox-area")[0].getElementsByTagName("textarea")[0];
    if(postComment(parent_id,textarea.value,replyboxNode))
        textarea.value="";

}
function postComment(parent_id,content,replyboxNode)
{
    var ajax=new XMLHttpRequest();
    var json_obj=new Object();
    json_obj.post_id=post_id;
    json_obj.content=content;
    json_obj.parent_id=parent_id;
    ajax.open("POST","http://localhost/encuss/commentAPI/newReply",true);
    ajax.onreadystatechange=function(){
        if (ajax.readyState==4 && ajax.status==200)
        {
            var response=JSON.parse(ajax.responseText);
            if(response.status==0)
            {
                alert(decodeURI (response.message));
                return false;
            }
            else
            {
                var info=new Object();
                info.user_avatar=user_avatar;
                info.user_nickname=user_nickname;
                info.content=content;
                info.parent_id=parent_id;
                info.comment_id=response.comment_id;
                var d=new Date();
                info.time= d.toLocaleString();
                encuss_allreplys[response.comment_id]=info;
                var new_comment=createCommentNode(info);
                if(parent_id>0 && encussConfig.is_nest==true)
                {
                    new_comment.style.marginLeft="20px";
                    encuss_allreplys[parent_id].element.appendChild(new_comment);
                }
                else
                {
                    encuss_comments.appendChild(new_comment);
                    window.scrollTo(0,new_comment.offsetTop-50);
                }

                encuss_allreplys[response.comment_id].element=new_comment;

                if(replyboxNode!=null)
                {
                    var textarea=replyboxNode.getElementsByClassName("encuss-replybox-area")[0].getElementsByTagName("textarea")[0];
                    console.log(textarea);
                    textarea.value="";
                    if(parent_id>0)
                        replyboxNode.style.display="none";

                }
                return true;
            }
        }
    };
    ajax.send(JSON.stringify(json_obj));
}
function moveRelpyBox(curObj)
{
    var commentNode=curObj.parentNode.parentNode;
    encuss_sub_replybox.dataset['parent_id']=commentNode.dataset['comment_id'];
    encuss_sub_replybox.getElementsByClassName("encuss-replybox-area")[0].getElementsByTagName("textarea")[0].placeholder="回复 @" + encuss_allreplys[commentNode.dataset['comment_id']].user_nickname+":";
    if(commentNode.getElementsByClassName("encuss-comments-node").length==0)//子评论
        commentNode.appendChild(encuss_sub_replybox);
    else
        commentNode.insertBefore(encuss_sub_replybox,commentNode.getElementsByClassName("encuss-comments-node")[0])//保证刚好插在下面
    encuss_sub_replybox.style.display="block";
}
