<?php
/**
 * +----------------------------------------------------------------------
 * ES-API地址
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\elasticsearch\tool;

class AbstractClient
{
    // ---------------------HTTP动词---------------------
    const GET = 'GET';
    const POST = 'POST';
    const HEAD = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const TRACE = 'TRACE';
    const CONNECT = 'CONNECT';
}
