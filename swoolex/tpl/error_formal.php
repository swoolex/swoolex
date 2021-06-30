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
        body{color:#333;font:14px Verdana,"Helvetica Neue",helvetica,Arial,'Microsoft YaHei',sans-serif;margin:0;padding:0 20px 20px;word-break:break-word}h1{margin:10px 0 0;font-size:28px;font-weight:500;line-height:32px}h2{color:#4288ce;font-weight:400;padding:6px 0;margin:6px 0 0;font-size:18px;border-bottom:1px solid #eee}h3.subheading{color:#4288ce;margin:6px 0 0;font-weight:400}h3{margin:12px;font-size:16px;font-weight:bold}abbr{cursor:help;text-decoration:underline;text-decoration-style:dotted}a{color:#868686;cursor:pointer}a:hover{text-decoration:underline}.line-error{background:#f8cbcb}.echo table{width:100%}.echo pre{padding:16px;overflow:auto;font-size:85%;line-height:1.45;background-color:#f7f7f7;border:0;border-radius:3px;font-family:Consolas,"Liberation Mono",Menlo,Courier,monospace}.echo pre>pre{padding:0;margin:0}.col-md-3{width:25%}.col-md-9{width:75%}[class^="col-md-"]{float:left}.clearfix{clear:both}@media only screen and (min-device-width :375px) and (max-device-width :667px){.col-md-3,.col-md-9{width:100%}}.exception{margin-top:20px}.exception .message{padding:12px;border:1px solid #ddd;border-bottom:0 none;line-height:18px;font-size:16px;border-top-left-radius:4px;border-top-right-radius:4px;font-family:Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑"}.exception .code{float:left;text-align:center;color:#fff;margin-right:12px;padding:16px;border-radius:4px;background:#999}.exception .source-code{padding:6px;border:1px solid #ddd;background:#f9f9f9;overflow-x:auto}.exception .source-code pre{margin:0}.exception .source-code pre ol{margin:0;color:#4288ce;display:inline-block;min-width:100%;box-sizing:border-box;font-size:14px;font-family:"Century Gothic",Consolas,"Liberation Mono",Courier,Verdana;padding-left:48px}.exception .source-code pre li{border-left:1px solid #ddd;height:18px;line-height:18px}.exception .source-code pre code{color:#333;height:100%;display:inline-block;border-left:1px solid #fff;font-size:14px;font-family:Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑"}.exception .trace{padding:6px;border:1px solid #ddd;border-top:0 none;line-height:16px;font-size:14px;font-family:Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑"}.exception .trace ol{margin:12px}.exception .trace ol li{padding:2px 4px}.exception div:last-child{border-bottom-left-radius:4px;border-bottom-right-radius:4px}.exception-var table{width:100%;margin:12px 0;box-sizing:border-box;table-layout:fixed;word-wrap:break-word}.exception-var table caption{text-align:left;font-size:16px;font-weight:bold;padding:6px 0}.exception-var table caption small{font-weight:300;display:inline-block;margin-left:10px;color:#ccc}.exception-var table tbody{font-size:13px;font-family:Consolas,"Liberation Mono",Courier,"微软雅黑"}.exception-var table td{padding:0 6px;vertical-align:top;word-break:break-all}.exception-var table td:first-child{width:28%;font-weight:bold;white-space:nowrap}.exception-var table td pre{margin:0}.copyright{margin-top:24px;padding:12px 0;border-top:1px solid #eee}pre.prettyprint .pln{color:#000}pre.prettyprint .str{color:#080}pre.prettyprint .kwd{color:#008}pre.prettyprint .com{color:#800}pre.prettyprint .typ{color:#606}pre.prettyprint .lit{color:#066}pre.prettyprint .pun,pre.prettyprint .opn,pre.prettyprint .clo{color:#660}pre.prettyprint .tag{color:#008}pre.prettyprint .atn{color:#606}pre.prettyprint .atv{color:#080}pre.prettyprint .dec,pre.prettyprint .var{color:#606}pre.prettyprint .fun{color:red}
    </style>
</head>
<body>


<div class="exception">
    <div class="message">
        <div class="info">
            <div>
                <h2>ThrowableError in：<?php echo parse_file($e['file'], $e['line']); ?></h2>
            </div>
            <div>
                <h1>
                    原因：<?php echo nl2br(htmlentities($e['message'])); ?><br/>
                </h1>
            </div>
        </div>
    </div>
 </div>               
    


<div class="copyright">
    <a title="官方网站" href="https://www.sw-x.cn">SW-X</a> 
    <span><?php echo VERSION;?></span> 
    <span>{ SW-X，专注高性能便捷开发而生的PHP框架 }</span>
</div>

</body>
</html>
                