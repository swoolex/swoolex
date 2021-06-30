<?php
/**
 * +----------------------------------------------------------------------
 * HTTP服务 - 详细错误显示界面 - DE_BUG=true
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

    if(!function_exists('parse_padding')){
        function parse_padding($source)
        {
            $length  = strlen(strval(count($source['source']) + $source['first']));
            return 40 + ($length - 1) * 8;
        }
    }

    if(!function_exists('parse_class')){
        function parse_class($name)
        {
            $names = explode('\\', $name);
            return '<abbr title="'.$name.'">'.end($names).'</abbr>';
        }
    }

    if(!function_exists('parse_file')){
        function parse_file($file, $line)
        {
            return '<a class="toggle" title="'."$file line $line".'">'.basename($file)." line $line".'</a>';
        }
    }

    if(!function_exists('parse_args')){
        function parse_args($args)
        {
            $result = [];

            foreach ($args as $key => $item) {
                switch (true) {
                    case is_object($item):
                        $value = sprintf('<em>object</em>(%s)', parse_class(get_class($item)));
                        break;
                    case is_array($item):
                        if(count($item) > 3){
                            $value = sprintf('[%s, ...]', parse_args(array_slice($item, 0, 3)));
                        } else {
                            $value = sprintf('[%s]', parse_args($item));
                        }
                        break;
                    case is_string($item):
                        if(strlen($item) > 20){
                            $value = sprintf(
                                '\'<a class="toggle" title="%s">%s...</a>\'',
                                htmlentities($item),
                                htmlentities(substr($item, 0, 20))
                            );
                        } else {
                            $value = sprintf("'%s'", htmlentities($item));
                        }
                        break;
                    case is_int($item):
                    case is_float($item):
                        $value = $item;
                        break;
                    case is_null($item):
                        $value = '<em>null</em>';
                        break;
                    case is_bool($item):
                        $value = '<em>' . ($item ? 'true' : 'false') . '</em>';
                        break;
                    case is_resource($item):
                        $value = '<em>resource</em>';
                        break;
                    default:
                        $value = htmlentities(str_replace("\n", '', var_export(strval($item), true)));
                        break;
                }

                $result[] = is_int($key) ? $value : "'$key' => $value";
            }

            return implode(', ', $result);
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
                        <?php echo '错误内容：'. nl2br(htmlentities($e['message'])); ?><br/>
                        <?php echo '错误地址：'. $e['file']; ?><br/>
                        <?php echo '错误行数：'. $e['line']; ?><br/>
                    </h1>
                </div>
            </div>
        </div>

        <div class="source-code">
            <pre class="prettyprint lang-php"><ol start="<?php echo $source['first']; ?>"><?php foreach ((array) $source['source'] as $key => $value) { ?><li class="line-<?php echo $key + $source['first']; ?>"><code><?php echo htmlentities($value); ?></code></li><?php } ?></ol></pre>
        </div>
        
        <div class="trace">
            <h2>Call Stack</h2>
            <ol>
                <li><?php echo sprintf('in %s', parse_file($e['file'], $e['line'])); ?></li>
                <?php foreach ((array) $e['trace'] as $value) { ?>
                <li>
                <?php 
                    // Show Function
                    if($value['function']){
                        echo sprintf(
                            'at %s%s%s(%s)', 
                            isset($value['class']) ? parse_class($value['class']) : '',
                            isset($value['type'])  ? $value['type'] : '', 
                            $value['function'], 
                            isset($value['args'])?parse_args($value['args']):''
                        );
                    }

                    // Show line
                    if (isset($value['file']) && isset($value['line'])) {
                        echo sprintf(' in %s', parse_file($value['file'], $value['line']));
                    }
                ?>
                </li>
                <?php } ?>
            </ol>
        </div>


    </div>

    <div class="copyright">
        <a title="官方网站" href="https://www.sw-x.cn">SW-X</a> 
        <span><?php echo VERSION;?></span> 
        <span>{ SW-X，专注高性能便捷开发而生的PHP框架 }</span>
    </div>

    <script>
        var LINE = <?php echo $e['line']; ?>;

        function $(selector, node){
            var elements;

            node = node || document;
            if(document.querySelectorAll){
                elements = node.querySelectorAll(selector);
            } else {
                switch(selector.substr(0, 1)){
                    case '#':
                        elements = [node.getElementById(selector.substr(1))];
                        break;
                    case '.':
                        if(document.getElementsByClassName){
                            elements = node.getElementsByClassName(selector.substr(1));
                        } else {
                            elements = get_elements_by_class(selector.substr(1), node);
                        }
                        break;
                    default:
                        elements = node.getElementsByTagName();
                }
            }
            return elements;

            function get_elements_by_class(search_class, node, tag) {
                var elements = [], eles, 
                    pattern  = new RegExp('(^|\\s)' + search_class + '(\\s|$)');

                node = node || document;
                tag  = tag  || '*';

                eles = node.getElementsByTagName(tag);
                for(var i = 0; i < eles.length; i++) {
                    if(pattern.test(eles[i].className)) {
                        elements.push(eles[i])
                    }
                }

                return elements;
            }
        }

        $.getScript = function(src, func){
            var script = document.createElement('script');
            
            script.async  = 'async';
            script.src    = src;
            script.onload = func || function(){};
            
            $('head')[0].appendChild(script);
        }

        ;(function(){
            var files = $('.toggle');
            var ol    = $('ol', $('.prettyprint')[0]);
            var li    = $('li', ol[0]);   

            // 短路径和长路径变换
            for(var i = 0; i < files.length; i++){
                files[i].ondblclick = function(){
                    var title = this.title;

                    this.title = this.innerHTML;
                    this.innerHTML = title;
                }
            }

            // 设置出错行
            var err_line = $('.line-' + LINE, ol[0])[0];
            err_line.className = err_line.className + ' line-error';

            $.getScript('//cdn.bootcss.com/prettify/r298/prettify.min.js', function(){
                prettyPrint();

                // 解决Firefox浏览器一个很诡异的问题
                // 当代码高亮后，ol的行号莫名其妙的错位
                // 但是只要刷新li里面的html重新渲染就没有问题了
                if(window.navigator.userAgent.indexOf('Firefox') >= 0){
                    ol[0].innerHTML = ol[0].innerHTML;
                }
            });

        })();
    </script>
</body>
</html>

                