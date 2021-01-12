<?php
// +----------------------------------------------------------------------
// | 微服务-通用助手类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed (http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\rpc;

class Currency
{ 
    /**
     * AES加密方法
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $data 要加密的数据 
     * @return void
    */
    public function aes_encrypt($data) {  
        $config = \x\Config::run()->get('rpc');
        return openssl_encrypt($data, $config['aes_method'], $config['aes_key'], 0, $config['aes_iv']);  
    }  
  
    /**
     * AES解密方法
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $data 要解密的数据 
     * @return void
    */  
    public function aes_decrypt($data) {  
        $config = \x\Config::run()->get('rpc');
        return openssl_decrypt($data, $config['aes_method'], $config['aes_key'], 0, $config['aes_iv']);  
    } 
}