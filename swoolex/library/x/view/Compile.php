<?php
// +----------------------------------------------------------------------
// | 视图层标签解析核心类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\view;

class Compile {
    /**
     * 模板内容
     */
	private $content;
    /**
     * 配置
     */
    private $config;
    /**
     * 右侧结束标签
     */
    private $tpl_end;

	/**
     * 构造函数，初始化模板内容
     * @param string $tpl_file 模板文件路径
    */
	public function __construct($tpl_file) {
        $this->config = \x\Config::run()->get('app.template');
        $this->tpl_begin =  $this->config['tpl_begin'];
        $this->tpl_end   = $this->config['tpl_end'];
        $this->content   = file_get_contents($tpl_file);
	}

    /**
     * 解析普通变量，如把{$name}解析成$name;
    */ 
	private function parseVar() {
		$pattern = '/\\'.$this->tpl_begin.'\$([\w\d]+)\\'.$this->tpl_end.'/';
		if (preg_match($pattern, $this->content)) {
			$this->content = preg_replace($pattern, '<?php echo $$1; ?>', $this->content);
		}
	}

    /**
     * 解析视图文件包含标签 
     * @param bool $type 是否检测生成
    */
	private function parseInclude($type) {
		$arr = explode("\n",$this->content);
		$content = '';
		//在模板文件中匹配模式,如果匹配成功,则替换成相应的php语言中的include包含语句 
		foreach ($arr as $val){
			$mode = '/'.$this->tpl_begin.'include file=\"(.+)\"\\'.$this->tpl_end.'/';//普通文件包含标签模式
			if(preg_match($mode,$val,$file)){ 
                # 模板文件地址
                $tpl_file  = ROOT_PATH.'/app/view/'.$file[1].$this->config['view_suffix'];
                if (!file_exists($tpl_file)) {
                    \x\StartEo::run("View：Include Imported file does not exist~", 'error');
                }
                # 缓存文件地址
                $parse_file = ROOT_PATH.'/runtime/view/'.str_replace('/', '_', $file[1]).'.php';
                # 缓存文件不存在，先生成
                if ((!file_exists($parse_file) && $type==true) || \x\Config::run()->get('app.de_bug')) {
                    $obj = new \x\view\Compile($tpl_file);
                    $obj->parse($parse_file, false);
                }

				# 替换成相应的php语言中的include包含语句 
				$content .= preg_replace($mode, "<?php include '$parse_file';?>", $val);
			}else{
				$content .= $val; 
			}
		}
		$this->content = $content;
	}

    /**
     * 解析原生PHP语句
    */
	private function parsePhp() {
		$pattern = '/'.$this->tpl_begin.'php'.$this->tpl_end.'(.*?)'.$this->tpl_begin.'\/php'.$this->tpl_end.'/is';
		if (preg_match($pattern, $this->content)) {
			$this->content = preg_replace($pattern, '<?php $1 ?>', $this->content);
		}
	}

    /** 
     * 解析框架常量
    */
	private function parseDefData() {
		$pattern = '/'.$this->tpl_begin.':(.*?)'.$this->tpl_end.'/is';
		if (preg_match($pattern, $this->content)) {
			$this->content = preg_replace($pattern, '<?php echo $1;?>', $this->content);
		}	
	}

    /**
     * 解析二维数组
    */
	private function parseArray(){
		$pattern = '/\\'.$this->tpl_begin.'\$([\w]+)\.([\w]+)\\'.$this->tpl_end.'/';
		if (preg_match($pattern,$this->content)) {
			$this->content = preg_replace($pattern, "<?php echo \$$1['$2']?>", $this->content);
		}
	}

    /**
     * 模板编译，解析所有标签
     * @param string $parse_file 缓存文件存放地址
     * @param bool $type 特殊标签是否跳过局部检测
    */
	public function parse($parse_file, $type=true){
        # 调用普通变量解析器
		$this->parseArray();
		$this->parseVar();
		$this->parsePhp();
		$this->parseInclude($type);
        $this->parseDefData();
		# 这里可以调用其他解析器
		
		# 编译完成后，生成编译文件
		if (!file_put_contents($parse_file, $this->content)) {
            \x\StartEo::run("View：File compilation error~", 'error');
		}
    }
}
