<?php
// +----------------------------------------------------------------------
// | 测试的数据库模型
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace app\model;
use \x\Model;

class UserModel  extends Model{

    public function run() {
        $list = $this->where('id', '<>',1)->where('')->order('id DESC')->limit(100)->select(false);
        return $list;
    }

}
