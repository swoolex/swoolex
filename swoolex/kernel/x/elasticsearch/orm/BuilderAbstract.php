<?php
/**
 * +----------------------------------------------------------------------
 * ORM构造器基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\elasticsearch\orm;

class BuilderAbstract
{
    /**
     * 查询条件解析器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-09
     * @deprecated 暂不启用
     * @global 无
     * @param array $where 条件
     * @return array
    */
    protected function parserWhere($where) {
        if (empty($where)) return [];

        $common_logic = [
            '=' => 'match',
            '>' => 'gt',
            '<' => 'lt',
            '>=' => 'gte',
            '<=' => 'lte',
        ];

        $ret = [];
        foreach ($where as $k => $v) {
            // 常用逻辑符替换
            foreach ($common_logic as $key=>$val) {
                if ($v[1] == $key) {
                    $v[1] = $val;
                    break;
                }
            }
            // 拼接表达式
            switch ($v[1]) {
                case 'gt':
                case 'lt':
                case 'gte':
                case 'lte':
                    $ret['range'][$v[0]][$v[1]] = [$v[2], $v[3]];
                break;
                default:
                    $ret[$v[1]][$v[0]][] = [$v[2], $v[3]];
                break;
            }
        }
        // 转换and条件
        $vif_list = ['terms', 'range'];
        $array = [];
        foreach ($ret as $key=>$v) {
            $key = strtolower($key);
            if (in_array($key, $vif_list) === false) {
                foreach ($v as $field => $val) {
                    foreach ($val as $value) {
                        $array['bool'][$value[1]][] = [
                            $key => [
                                $field => $value[0]
                            ]
                        ];
                    }
                }
            } else {
                $k = key($v);
                $data = isset($v[$k][0]) ? $v[$k][0] : $v[$k];
                if ($key == 'terms') {
                    $lable = $data[1];
                    $data = $data[0];
                    $array['bool'][$lable][] = [
                        $key => [
                            $k => $data
                        ]
                    ];
                } else {
                    $quque = [];
                    foreach ($data as $exp => $vv) {
                        $quque[$vv[1]][$key][$k][$exp] = $vv[0]; 
                    }
                    foreach ($quque as $lable => $vv) {
                        $array['bool'][$lable][] = $vv;
                    }
                }
            }
        }
        
        return $array;
    }
}