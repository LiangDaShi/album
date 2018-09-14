<?php $this->display('user/login_head.php'); ?>
<div class="register-box">
    <div class="mod-head">
        <a href="/"><img src="<?php echo ST('images/login_logo.png');?>" alt="<?php echo getSetting('site_title');?>"></a>
        <h1>找回密码 - 第二步</h1>
    </div>
    <div class="mod-body">
            <form class="session-form" action="<?php echo U('user','findpass','a=step2');?>" method="post" onsubmit="return postFindForm(this);">
                <ul>
                <li class="alert alert-danger error_message" style="display: none;">
                    
                </li>
                <li>
                    <input type="text" readonly="" value="<?php echo $username; ?>" name="username" class="form-control" />
                </li>
                <li>
                    <input type="text" required="" placeholder="请输入收到的验证码" name="captcha" class="form-control" />
                </li>
                <li>
                    <input type="password" required="" value="" placeholder="请输入新密码" name="newpass" class="form-control" />
                </li>
                <li class="form-action">
                    <input type="hidden" name="isajax" value="1" />
                    <input type="submit" class="btn btn-xl" value="修改密码" />
                </li>
                </ul>
            </form>
    </div>
</div>
<script type="text/javascript" src="<?php echo S('sms','js/sms.js') ?>"></script>
<script type="text/javascript">
function postFindForm(f){
    $('.error_message').hide();
    $.post($(f).attr('action'),$(f).serializeArray(),function(data){
        if(data.ret){
            $('.error_message').removeClass('alert-danger').html(data.msg).show();
            $(f).find('input.btn-xl').prop('disabled',true);
            setTimeout(function(){
                window.location.href=data.redirect;
            },1500);
        }else{
            $('.error_message').html(data.msg).show();
            if(data.redirect){
                setTimeout(function(){
                    window.location.href = data.redirect;
                },1000);
            }
        }
    },'json');
    return false;
}
$(function(){
    $('input, textarea').placeholder();
});
</script>
<?php $this->display('user/login_foot.php'); ?>