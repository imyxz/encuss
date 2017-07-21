/**
 * Created by i on 2017/5/24.
 */
var site_id;
var site_post_id,site_title,site_url,site_img;
var post_id;
var encuss_comments;
var user_nickname;
var user_avatar;
var isLogin=false;
var encuss_sub_replybox;
var encuss_allreplys=new Object();
var encuss_smiles=new Object();
var encuss_sso_login="";
var encuss_sso_logout="";
var encuss_basic_url=(document.location.protocol == 'https:' ? 'https:' : 'http:') + "//localhost/encuss/";
var encuss_smiles_selector;
var encuss_message_notice;
var encuss_message_viewer;
encussInit();
function encussInit()
{
    includeCss(encuss_basic_url+"css/embed.css?20170721");
    site_id=encussConfig.site_id;
    var encuss=document.getElementsByClassName("encuss-div")[0];
    site_post_id=encuss.dataset['postId'];
    site_url=encuss.dataset['url']!=null?encuss.dataset['url']:'';
    site_img=encuss.dataset['image']!=null?encuss.dataset['image']:'';
    site_title=encuss.dataset['title']!=null?encuss.dataset['title']:'';
    if(encussConfig.sso!=null && encussConfig.sso.login!=null)
    {
        encuss_sso_login="viewing/" + encodeURIComponent(encussConfig.sso.login) +"/";
    }

    if(encussConfig.sso!=null && encussConfig.sso.logout!=null)
    {
        encuss_sso_logout="viewing/" + encodeURIComponent(encussConfig.sso.logout) +"/";
    }
    loadSmiles();
    var ajax=new XMLHttpRequest();
    ajax.open("GET",encuss_basic_url+"commentAPI/getPostReplys/site_id/" + site_id + "/site_post_id/" +site_post_id
        +"/title/"+encodeURIComponent(site_title)
        +"/url/"+encodeURIComponent(site_url)
        +"/image/"+encodeURIComponent(site_img),true);
    ajax.withCredentials=true;
    ajax.onreadystatechange=function(){
        if (ajax.readyState==4 && ajax.status==200) {
            var comment = document.createElement("div");
            comment.className = "encuss-comments";
            var response = JSON.parse(ajax.responseText);
            response['replys']=obj2Array(response['replys']);//将object转换成array才能排序
            response['replys'].sort(function(a,b)
            {
                return b.time_stamp - a.time_stamp;
            });//排序一下
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
                    if(encuss_allreplys[index].parent_id>0 && encuss_allreplys[encuss_allreplys[index].parent_id]!=null)
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
                initMessage();
            }
            else
            {
                isLogin=false;
                user_avatar=encuss_basic_url+"img/default_avatar.png";
            }
            encuss.appendChild(createReplyBox(0));
            encuss.appendChild(comment);
            encuss_comments=document.getElementsByClassName("encuss-div")[0].getElementsByClassName("encuss-comments")[0];
            encuss_sub_replybox=createReplyBox(0);
            encuss_sub_replybox.style.marginLeft="20px";
        }
    };
    ajax.send();





}
function loadSmiles()
{
    var ajax=new XMLHttpRequest();
    ajax.open("GET",encuss_basic_url+"resource/smiles/smiles.json",false);
    ajax.withCredentials=true;
    ajax.onreadystatechange=function(){
        if (ajax.readyState==4 && ajax.status==200) {
            var response = JSON.parse(ajax.responseText);
            for(var index1 in response['bundle'])
            {
                for(var index2 in response['bundle'][index1]['smiles'])
                {
                    encuss_smiles[index1 + '_' + index2]=encuss_basic_url + response['bundle'][index1]['basic_path'] + response['bundle'][index1]['smiles'][index2];
                }
            }

        }
    };
    ajax.send();
}
function processSmiles(innerHtml) {
    var i=0;
    var tmp;
    while(i<innerHtml.length)
    {
        if(tmp=getSubStr(innerHtml,"{:",":}",i))
        {
            if(encuss_smiles.hasOwnProperty(tmp.str))
            {
                var addon_string="<img src='"+ encuss_smiles[tmp.str] +"' class='encuss-smiles-icon'/>";
                innerHtml=innerHtml.substring(0,tmp.start-2) + addon_string +innerHtml.substr(tmp.end+3);
                i=tmp.start-4 + addon_string.length;
            }
        }
        i++;
    }
    return innerHtml;
}
function getSubStr(text,needle1,needle2,start)
{
    var pos1=text.indexOf(needle1,start);
    if(pos1<0)
        return false;
    var pos2=text.indexOf(needle2,pos1+1);
    if(pos2<0)
        return false;
    var ret=new Object();
    ret.start=pos1+needle1.length;
    ret.end=pos2-1;
    ret.str=text.substring(ret.start,ret.end+1);
    return ret;
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
    ele.innerHTML='<img class="encuss-avatar" height="50px" width="50px">\
        <span class="encuss-username"></span>\
    <span class="encuss-comment-content-reply"></span>\
    <span class="encuss-comment-content"></span>\
    <span class="encuss-comment-time"></span>\
<span class="encuss-comment-action">\
    <a href="javascript:void(0)" onclick="moveRelpyBox(this)">回复</a>\
    <a href="#"></a>\
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
    //处理表情
    ele.getElementsByClassName("encuss-comment-content")[0].innerHTML=processSmiles(ele.getElementsByClassName("encuss-comment-content")[0].innerHTML);
    ele.getElementsByClassName("encuss-comment-content-reply")[0].innerHTML=processSmiles(ele.getElementsByClassName("encuss-comment-content-reply")[0].innerHTML);
    //完毕
    ele.dataset.comment_id=info.comment_id;
    encuss_allreplys[info.comment_id].element=ele;
    return ele;
}
function createReplyBox(parent_id)
{
    var ele=document.createElement("div");
    ele.className="encuss-replybox";
    ele.innerHTML='    <img class="encuss-avatar" height="50px" width="50px">\
        <div class="encuss-replybox-area">\
    <label><textarea placeholder="说点什么吧......"></textarea></label>\
</div>\
<div class="encuss-replybox-action">\
    <div class="encuss-reply-smiles-button"></div>\
    <button class="encuss-reply-button" onclick="submitComment(this)">发布</button>\
    <img class="encuss-reply-login-byqq"/>\
    </div>';
    ele.dataset.parent_id=parent_id;
    ele.getElementsByClassName("encuss-reply-smiles-button")[0].addEventListener("click",moveSmilesSelector);
    ele.getElementsByClassName("encuss-avatar")[0].src=user_avatar;
    var img=ele.getElementsByClassName("encuss-reply-login-byqq")[0];
    img.src=encuss_basic_url + "img/qq_login.png";
    img.addEventListener("click",function(e){
        e.stopPropagation();
        window.location=encuss_basic_url+ "userAPI/loginFromQQ/site_id/" + site_id +"/" + encuss_sso_login;
    });
    if(isLogin)
        img.style.display="none";
    else
        img.style.display="block";
    return ele;
}
function createSmilesSelector()
{
    var ele=document.createElement("div");
    ele.className="encuss-smiles-selector";
    for(var index in encuss_smiles)
    {
        var smiles=document.createElement("img");
        smiles.src=encuss_smiles[index];
        smiles.dataset.smiles_name=index;
        smiles.className="encuss-smiles-icon";
        smiles.addEventListener("click",addSmiles);
        ele.appendChild(smiles);
    }
    ele.style.display="none";
    document.addEventListener("click",function(){
        ele.style.display="none";
    });
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
    ajax.open("POST",encuss_basic_url+"commentAPI/newReply",true);
    ajax.withCredentials=true;
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
                    encuss_comments.insertBefore(new_comment,encuss_comments.firstChild);
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
        commentNode.insertBefore(encuss_sub_replybox,commentNode.getElementsByClassName("encuss-comments-node")[0]);//保证刚好插在下面
    encuss_sub_replybox.style.display="block";
}
function moveSmilesSelector(e)
{
    if(encuss_smiles_selector==null)
        encuss_smiles_selector=createSmilesSelector();//在这里载入时减少不必要的流量损耗
    encuss_smiles_selector.style.display="block";
    this.parentNode.appendChild(encuss_smiles_selector);
    e.stopPropagation();
}
function addSmiles(e)
{
    var comment_box=this.parentNode.parentNode.parentNode.getElementsByClassName("encuss-replybox-area")[0].getElementsByTagName("textarea")[0];
    comment_box.value=comment_box.value.substr(0,comment_box.selectionStart) + "{:" + this.dataset.smiles_name +":}" + comment_box.value.substring(comment_box.selectionStart);
}
function obj2Array(obj)
{
    var arr=new Array();
    for(var x in obj)
    {
        arr.push(obj[x]);
    }
    return arr;
}
function initMessage()
{
    var ajax=new XMLHttpRequest();
    ajax.open("GET",encuss_basic_url+"messageAPI/getUnreadMessage/",true);
    ajax.withCredentials=true;
    ajax.onreadystatechange=function(){
        if (ajax.readyState==4 && ajax.status==200) {
            var response=JSON.parse(ajax.responseText);
            if(response.status==1 && response.unread>0)
            {
                encuss_message_notice=document.createElement("div");
                encuss_message_notice.className="encuss-message-notice";
                encuss_message_notice.innerHTML="<span></span>";
                encuss_message_notice.firstChild.innerText="您有 "+response.unread +" 条未读消息";
                encuss_message_notice.firstChild.addEventListener("click",onClickMessageNoticer);
                encuss_comments.appendChild(encuss_message_notice);
            }
        }
    };
    ajax.send();
}
function onClickMessageNoticer(element)
{
    if(encuss_message_viewer==null)
    {
        var ajax=new XMLHttpRequest();
        ajax.open("GET",encuss_basic_url+"messageAPI/getMessageDetail/",false);
        ajax.withCredentials=true;
        ajax.onreadystatechange=function(){
            if (ajax.readyState==4 && ajax.status==200) {
                var response=JSON.parse(ajax.responseText);
                if(response.status==1)
                {
                    encuss_message_viewer=createMessageViewer();
                    for(var i in response.message)
                    {
                        encuss_message_viewer.firstChild.appendChild(createMessageLink(response.message[i]));
                    }
                    console.log(encuss_message_viewer);
                    encuss_message_viewer.style.display="block";
                    encuss_comments.appendChild(encuss_message_viewer);

                }
            }
        };
        ajax.send();
    }
    else
    {
        encuss_message_viewer.style.display="block";

    }


}
function createMessageViewer()
{
    var ele=document.createElement("div");
    ele.className="encuss-message-viewer-div";
    ele.innerHTML='<div class="encuss-message-viewer">\
    <span class="encuss-message-viewer-closer">x</span>\
    </div>';
    ele.getElementsByClassName("encuss-message-viewer-closer")[0].addEventListener("click",onClickMessageViewerCloser);
    ele.style.display="none";
    return ele;
}
function onClickMessage(element)
{
    var ajax=new XMLHttpRequest();
    var json_obj=new Object();
    json_obj.message_id=element.target.dataset['message_id'];
    ajax.open("POST",encuss_basic_url+"messageAPI/onMessageClicked/",false);
    ajax.withCredentials=true;
    ajax.onreadystatechange=function(){
    };
    ajax.send(JSON.stringify(json_obj));
}
function onClickMessageViewerCloser(e)
{
    encuss_message_viewer.style.display="none";
    e.stopPropagation();
}
function createMessageLink(message_obj)
{
    var ele=document.createElement("a");
    ele.href=message_obj.url;
    ele.target="_blank";
    ele.innerText=message_obj.content;
    ele.dataset['message_id']=message_obj.id;
    ele.addEventListener("click",onClickMessage);
    return ele;
}