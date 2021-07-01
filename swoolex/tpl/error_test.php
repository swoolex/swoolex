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
    h3.subheading{color:#4288ce;margin:6px 0 0;font-weight:400}
    
    .copyright{margin-top:24px;padding:12px 0;border-top:1px solid #e2e2e2;float: left;width: 100%;height: 30px;line-height: 30px;}
    .copyright a{color:#1E9FFF;margin-right: 0;}
    .tab{float: left;width: 100%;text-align: center; line-height: 40px; height: 40px;background: #2F4056;}
    .tab ul,.tab li{margin: 0;padding: 0;list-style: none;}
    .tab ul{display: flex;justify-content: center;}
    .tab li{color: #fff;width: 100px;cursor: pointer;}
    .tab li:hover{background: #465d7b;color: #fff;}

    ul .his{background: #e6e7e8;color: #465d7b;}
    .line-error{background:#cef8cb}
    .echo table{width:100%}
    .echo pre{padding:16px;overflow:auto;font-size:85%;line-height:1.45;background-color:#f7f7f7;border:0;border-radius:3px;font-family:Consolas,"Liberation Mono",Menlo,Courier,monospace}
    .echo pre>pre{padding:0;margin:0}
    .col-md-3{width:25%}
    .col-md-9{width:75%}
    [class^="col-md-"]{float:left}
    .clearfix{clear:both}
    .exception .code{float:left;text-align:center;color:#fff;margin-right:12px;padding:16px;border-radius:4px;background:#999}
    .exception .source-code{padding:6px;border:1px solid #ddd;background:#f9f9f9;overflow-x:auto}
    .exception .source-code pre{margin:0}
    .exception .source-code pre ol{margin:0;color:#4288ce;display:inline-block;min-width:100%;box-sizing:border-box;font-size:14px;font-family:"Century Gothic",Consolas,"Liberation Mono",Courier,Verdana;padding-left:48px}
    .exception .source-code pre li{border-left:1px solid #ddd;height:18px;line-height:18px}
    .exception .source-code pre code{color:#333;height:100%;display:inline-block;border-left:1px solid #fff;font-size:14px;font-family:Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑"}
    .exception .trace{padding:6px;border:1px solid #ddd;border-top:0 none;line-height:16px;font-size:14px;font-family:Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑"}
    .exception .trace ol{margin:12px}
    .exception .trace ol li{padding:2px 4px}
    .exception div:last-child{border-bottom-left-radius:4px;border-bottom-right-radius:4px}
    pre.prettyprint .pln{color:#2F4056}
    pre.prettyprint .str{color:#FF5722}
    pre.prettyprint .kwd{color:#008}
    pre.prettyprint .com{color:#009688}
    pre.prettyprint .typ{color:#FF5722}
    pre.prettyprint .lit{color:#5FB878}
    pre.prettyprint .pun,pre.prettyprint .opn,pre.prettyprint .clo{color:#660}
    pre.prettyprint .tag{color:#008}
    pre.prettyprint .atn{color:#FF5722}
    pre.prettyprint .atv{color:#009688}
    pre.prettyprint .dec,pre.prettyprint .var{color:#FF5722}
    pre.prettyprint .fun{color:#FFB800}
    .yes{display: block; float: left; width: 100%;}
    .no{display: none;}
    .tabs>div{border-bottom:1px solid #eee;float:left;width: calc(100% - 40px); padding: 10px 0px;}
    .lt{width: 150px;float: left;}
    .rt{width: calc(100% - 150px);float: left;}
    .tabs div pre{padding: 0;margin: 0;}
    .rt{color:#007d72}
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
                <?php echo '错误内容：'. nl2br(htmlentities($e['message'])); ?><br/>
                <?php echo '错误地址：'. $e['file']; ?><br/>
                <?php echo '错误行数：'. $e['line']; ?><br/>
            </h1>
        </div>
    </div>

    <div class="tab">
        <ul id="tab">
            <li data-id="TAB-1" class="li his" onclick="getData(0)">错误详情</li>
            <li data-id="TAB-2" class="li" onclick="getData(1)">请求详情</li>
            <li data-id="TAB-3" class="li" onclick="getData(2)">文件</li>
            <li data-id="TAB-4" class="li" onclick="getData(3)">SQL</li>
        </ul>
    </div>

    <!--错误详情-->
    <div id="TAB-1" class="tabs exception yes">
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

    <div id="TAB-2" class="tabs exception no">
        <div class="mr20"><div class="lt">Request URL：</div><div class="rt"><font><?php echo \x\Request::baseUrl(true);?></font></div></div>
        <div class="mr20"><div class="lt">Request Method：</div><div class="rt"><font><?php if (\x\Request::is_get()) {echo 'GET';}else{echo 'POST';}?></font></div></div>
        <div class="mr20"><div class="lt">AJAX：</div><div class="rt"><font><?php if (\x\Request::is_ajax()) {echo '是';}else{echo '否';}?></font></div></div>
        <div class="mr20"><div class="lt">Client-IP：</div><div class="rt"><font><?php echo \x\Request::ip();?></font></div></div>
        <div class="mr20"><div class="lt">HEADER：</div><div class="rt"><pre><code><?php echo dd(\x\Request::header());?></code></pre></div></div>
        <div class="mr20"><div class="lt">GET：</div><div class="rt"><pre><code><?php echo dd(\x\Request::get()?:[]);?></code></pre></div></div>
        <div class="mr20"><div class="lt">POST：</div><div class="rt"><pre><code><?php echo dd(\x\Request::post()?:[]);?></code></pre></div></div>
        <div class="mr20"><div class="lt">RAW：</div><div class="rt"><pre><code><?php echo dd(\x\Request::raw()?:[]);?></code></pre></div></div>
        <div class="mr20"><div class="lt">FILE：</div><div class="rt"><pre><code><?php echo dd(\x\Request::file()?:[]);?></code></pre></div></div>
    </div>
    
    <div id="TAB-3" class="tabs exception no">
        <?php 
        $list = array_reverse(get_included_files());
        foreach ($list as $val) {
            echo '<div class="mr20">'.str_replace(ROOT_PATH, '', $val).' ('.number_format(filesize($val)/1024, 3).'KB)</div>';
        }
        ?>
    </div>

    <div id="TAB-4" class="tabs exception no">
        <?php
        $array = \x\Container::get('http_sql_log');
        $html = '';
        if ($array) {
            foreach ($array as $val) {
                $html .= '<div class="mr20">';
                $html .= '<font style="font-weight: bold;">调用来源：</font><font color="#8c2a07">'.$val['file'].'</font><br/>';
                $html .= '<font style="font-weight: bold;">SQL：</font><font color="red">'.$val['sql'].'</font><br/>';
                $html .= '<font style="font-weight: bold;">耗时：</font><font color="#b800d8">'.$val['time'].'s</font>';
                $html .= '</div>';
            }
        }
        echo ($html ?: '<div class="mr20">无</div>');
        ?>
    </div>

    <div class="copyright">
        <a class="mr20" title="官方网站" href="https://www.sw-x.cn">SW-X</a> 
        <span>v2.0.10</span> 
        <span>{ SW-X，专注-高性能/灵活/便捷-开发而生的PHP-Swoole框架 }</span>
    </div>
    
    <script>
        function getData(e) {
        
            var lis = document.getElementsByClassName("li");
            var dataId =  lis[e].getAttribute("data-id");
            var content = document.getElementById(dataId);
            var tabContents = document.getElementsByClassName("exception");
            // 其他切换
            for(var i = 0; i < tabContents.length; i++){
                tabContents[i].className= "tabs exception no" 
            }  
            content.className= "tabs exception yes" 

            // 其他切换
            for(var i = 0; i < lis.length; i++){
                if (i == e) {
                    lis[i].className= "li his" 
                } else {
                    lis[i].className= "li" 
                }
            }  
         }
    </script>  
    <script>
        var LINE = 1049;
    
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
    
            $.getScript('https://cdn.bootcss.com/prettify/r298/prettify.min.js', function(){
                prettyPrint();
    
                if(window.navigator.userAgent.indexOf('Firefox') >= 0){
                    ol[0].innerHTML = ol[0].innerHTML;
                }
            });
    
        })();
    </script>
    <script src=""></script>
</body>
</html>