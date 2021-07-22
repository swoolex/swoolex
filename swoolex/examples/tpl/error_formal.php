<?php 
/**
 * +----------------------------------------------------------------------
 * HTTP服务 - 简洁错误显示界面 - DE_BUG=false
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

if(!function_exists('parse_file')){
    function parse_file($file, $line)
    {
        return '<a class="toggle" title="'."$file line $line".'">'.basename($file)." line $line".'</a>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System Error</title>
    <meta name="robots" content="noindex,nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <style>
    body{color:#2F4056;font:14px Verdana,"Helvetica Neue",helvetica,Arial,'Microsoft YaHei',sans-serif;margin:0;padding:0;word-break:break-word;}
    .mr20{margin: 0 20px;}
    .head{width: 100%;float: left;min-height: 40px;line-height: 40px;border-bottom: 1px solid #e2e2e2;text-indent: 24px;font-size: 16px;background: #fffdfd;}
    .head font{color:#1E9FFF;font-weight: 500;margin-right: 20px;font-size: 20px;}
    .head .type{color:#FF5722}
    .head .word{float: right;margin-right: 20px;}
    .head .word a{color: #009688;text-decoration: none;font-size: 14px;}
    .head .word a:hover{color: #5FB878;text-decoration:revert;}
    .info{float: left;width: 100%;font-size: 14px;border-bottom: 1px solid #eee;padding-bottom: 5px;}
    .toggle{color: #393D49;}
    h1{margin:10px 0 0;font-size:24px;font-weight:500;line-height:40px}
    h2{color:#009688;font-weight:400;padding:6px 0;margin:6px 0 0;font-size:18px;border-bottom:1px solid #eee}
    .copyright{margin-top:24px;padding:12px 0;border-top:1px solid #e2e2e2;float: left;width: 100%;height: 30px;line-height: 30px;position: fixed;bottom: 0;}
    .copyright a{color:#1E9FFF;margin-right: 0;}
    </style>
</head>
<body>
    <div class="head">
        <font>SW-X</font>
        <span class="type">
            <?php if (\x\Request::is_get()) {echo 'GET';}else{echo 'POST';}?>
        </span>
        -
        <span><?php echo \x\Request::baseUrl(true);?></span>
        <div class="word">
            <a href="https://www.sw-x.cn/word.php" target="_blank" title="SW-X 官方文档">官方文档</a>
        </div>
    </div>

    <div class="info">
        <div class="mr20">
            <h2>ThrowableError in：<?php echo parse_file($e['file'], $e['line']); ?></h2>
        </div>
        <div class="mr20">
            <h1>
                原因：<?php echo nl2br(htmlentities($e['message'])); ?><br/>
            </h1>
        </div>
    </div>

    <div class="copyright">
        <a class="mr20" title="官方网站" href="https://www.sw-x.cn">SW-X</a> 
        <span><?php echo VERSION;?></span> 
        <span>{ SW-X，专注-高性能/灵活/便捷-开发而生的PHP-Swoole框架 }</span>
    </div>
</body>
</html>