<?php
defined('IN_MWEB') or die('access denied');

require_once('_submenu.php');

class UserIndex extends Adminbase{
    function indexAct(){
        $page = getGet('page',1);

        $search['keyword'] = trim(getRequest('keyword'));

        $m_user = M('users');

        $where = '1';
        if( $search['keyword'] ){
            if( is_numeric($search['keyword']) ){
                $where .= ' and id ='.intval($search['keyword']);
            }else{
                $keyword = trim($search['keyword'],'*');
                $where .= " and (username like '%".$m_user->escape($keyword,false)."%' or email like '%".$m_user->escape($keyword,false)."%' or nickname like '%".$m_user->escape($keyword,false)."%')";
            }
        }

        $totalCount = $m_user->count($where);
        $pageurl = U('user','index','keyword='.$search['keyword'].'&page=%page%');

        $pager = new Pager($page,C('pageset.admin',15),$totalCount,$pageurl);
        $pager->config(C('adminpage'));
        $limit = $pager->getLimit();
        $this->view->assign('pagestr',$pager->html());

        $rows = $m_user->findAll(array(
                    'where' => $where,
                    'start' => $limit['start'],
                    'limit' => $limit['limit'],
                    'order' => 'id desc'
                ));

        $this->view->assign('rows',$rows);
        $this->view->assign('search',$search);
        $this->view->display('index.php');
    }

    function editAct(){
        $id = intval(getGet('id'));
        $m_user = M('users');

        if(isPost()){
            $data['username'] = safestr(trim(getPost('username')));
            $data['nickname'] = trim(getPost('nickname'));
            $data['mobile'] = trim(getPost('mobile'));
            $data['email'] = trim(getPost('email'));
            $data['level'] = intval(getPost('level'));
            $data['gender'] = getPost('gender');
            $data['description'] = safestr(trim(getPost('description')));
            
            if(!$data['username']){
                alert('用户名不能为空！');
            }
            if(!$data['nickname']){
                alert('昵称不能为空！');
            }
            if(!$data['email']){
                alert('Email不能为空！');
            }
            if(!isEmail($data['email'])){
                alert('Email格式错误！');
            }
            if(!isMobile($data['mobile'])){
                alert('手机号格式错误！');
            }
            //检查用户名是否重复
            if(0 < $m_user->count("username=".$m_user->escape($data['username']).' and id<>'.$id) ){
                alert('用户名重复！');
            }
            if(0 < $m_user->count("email=".$m_user->escape($data['email']).' and id<>'.$id) ){
                alert('Email重复！');
            }
            if(0 < $m_user->count("mobile=".$m_user->escape($data['mobile']).' and id<>'.$id) ){
                alert('手机号重复！');
            }



            $userpass = getPost('userpass');
            if($userpass && $userpass!=getPost('userpass2')){
                alert('两次密码输入不一致！');
            }
            if($userpass){
                $data['salt'] = substr(uniqid(rand()), -6);
                $data['userpass'] = md5(md5($userpass).$data['salt']);
            }
            

            if($m_user->update($id,$data)){
                //更新用户相关信息
                $fields = app('base')->getSetting('user_fields',true);
                $infodata = array();
                foreach($fields as $k=>$v){
                    $infodata[$k] = trim(getPost($k));
                }
                $uiinfo = M('users_info')->load($id,'*','uid');
                if($uiinfo){
                    M('users_info')->updateW('uid='.$id,$infodata);
                }else{
                    $infodata['uid'] = $id;
                    M('users_info')->insert($infodata);
                }

                alert('修改用户成功！',true,U('user','index'));
            }else{
                alert('修改用户失败！');
            }
        }

        $info = $m_user->load($id);
        $iinfo = M('users_info')->load($id,'*','uid');
        if(!$iinfo) $iinfo = M('users_info')->loadDefault();

        $fields = app('base')->getSetting('user_fields',true);

        $this->view->assign('fields',$fields);
        $this->view->assign('info',$info);
        $this->view->assign('iinfo',$iinfo);
        $this->view->display('index_edit.php');
    }

    function addAct(){
        $m_user = M('users');
        if(isPost()){
            $data['username'] = safestr(trim(getPost('username')));
            $data['nickname'] = trim(getPost('nickname'));
            $data['email'] = trim(getPost('email'));
            $data['mobile'] = trim(getPost('mobile'));
            $data['level'] = intval(getPost('level'));
            $data['gender'] = getPost('gender');
            $data['description'] = safestr(trim(getPost('description')));

            if(!$data['username']){
                alert('用户名不能为空！');
            }
            if(!$data['nickname']){
                alert('昵称不能为空！');
            }
            if(!$data['email']){
                alert('Email不能为空！');
            }
            if(!isEmail($data['email'])){
                alert('Email格式错误！');
            }
            if(!isMobile($data['mobile'])){
                alert('手机号格式错误！');
            }
            $userpass = getPost('userpass');
            if(!$userpass){
                alert('请输入密码！');
            }
            if($userpass!=getPost('userpass2')){
                alert('两次密码输入不一致！');
            }

            //检查用户名是否重复
            if(0 < $m_user->count("username=".$m_user->escape($data['username'])) ){
                alert('用户名重复！');
            }
            if(0 < $m_user->count("email=".$m_user->escape($data['email'])) ){
                alert('Email重复！');
            }
            if(0 < $m_user->count("mobile=".$m_user->escape($data['mobile'])) ){
                alert('手机号重复！');
            }

            $data['salt'] = substr(uniqid(rand()), -6);
            $data['userpass'] = md5(md5($userpass).$data['salt']);
            $data['regtime'] = time();
            $data['regip'] = getClientIp();

            if($m_user->insert($data)){
                $uid = $m_user->insertId();
                //额外字段信息
                $fields = app('base')->getSetting('user_fields',true);
                $infodata = array( 'uid' => $uid,'addtime'=>time());
                foreach($fields as $k=>$v){
                    $infodata[$k] = trim(getPost($k));
                }
                M('users_info')->insert($infodata);

                alert('添加用户成功！',true,U('user','index'));
            }else{
                alert('添加用户失败！');
            }
        }

        
        $info = $m_user->loadDefault();

        $fields = app('base')->getSetting('user_fields',true);

        $this->view->assign('fields',$fields);
        $this->view->assign('info',$info);
        $this->view->assign('iinfo',M('users_info')->loadDefault());
        $this->view->display('index_edit.php');
    }

    function delAct(){
        $id = intval(getGet('id'));
        if($id == $this->_G['user']['id']){
            alert('你不能删除自己！');
        }

        if(M('users')->delete($id)){
            //删除额外信息
            M('users_info')->delete($id,'uid');

            alert('删除用户成功！',true,U('user','index'));
        }else{
            alert('删除用户失败！');
        }
    }
}