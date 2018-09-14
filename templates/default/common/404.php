<!DOCTYPE html>
<html> 
    <head>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0" />
        <title>404！页面不存在 - <?php echo getSetting('site_title');?> - <?php echo getSetting('site_sub_title');?></title>
        <style type="text/css">
        *{margin:0;padding:0;}
        h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:400;}
        body{background:#f1f1f1;color:#1c2837;font:normal 15px arial, verdana, tahoma, sans-serif;position:relative;}
        a{color:#284b72;}
        a:hover{color:#528f6c;text-decoration:underline;}
        .error-box{max-width: 400px;margin:80px auto 0;}
        .error-box .mod-body{padding:30px 30px 30px 100px;background-image:url(<?php echo ST('images/i-info.png')?>);background-size:65px 65px;background-repeat:no-repeat;background-position:15px center;background-color: #ebebeb;border-radius: 10px;}
        .error-box .icon-bubble{float: left;width: 80px;}
        .error-box .icon-bubble img{width: 60px;}
        @media (max-width:640px) {
            .error-box{padding:0 20px;}
            .error-box .mod-body{padding:20px 20px 20px 60px;background-size:40px 40px;background-position: 10px center;}
        }

        </style>
        </head>
    <body>
        <div class="error-box">
            <div class="mod-body">
                <strong>404 页面不存在</strong>
                <br />
                <br />
                <span class='desc'>(<a href="<?php echo U('base','index'); ?>">返回首页</a>)</span>
            </div>
        </div>
    </body>
</html>