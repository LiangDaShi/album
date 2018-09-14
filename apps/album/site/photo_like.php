<?php 
defined('IN_MWEB') or die('access denied');

checkLogin();

$id = intval(getGet('id'));

$act = getPost('a');
if($act == 'cancel'){
    $likerow = M('album_likes')->findRow('uid='.$_G['user']['id'].' and photo_id='.$id);
    if(!$likerow){
        alert('还未点赞过，无法取消点赞！');
    }
    if(time()-$likerow['addtime']<60){
        alert('操作过于频繁！');
    }
    M('album_likes')->deleteW('uid='.$_G['user']['id'].' and photo_id='.$id);
    //减少点赞次数
    M('album_photos')->update($id , array('like_num'=>array('exp','like_num-1')) );

    alert('取消点赞成功！',true);
}else{
    $m_photo = M('album_photos');
    $info = $m_photo->load($id);

    if($info['uid']==$_G['user']['id']){
        alert('把点赞的机会留给别人吧！');
    }
    if(M('album_likes')->findRow('uid='.$_G['user']['id'].' and photo_id='.$id)){
        alert('您已经点赞过了！');
    }

    if(M('album_likes')->insert(array('uid'=>$_G['user']['id'],'photo_id'=>$id,'addtime'=>CURRENT_TIME))){
        //增加点赞次数
        $m_photo->update($id , array('like_num'=>array('exp','like_num+1')) );
        
        alert('点赞成功！',true);
    }else{
        alert('点赞失败！');
    }
}