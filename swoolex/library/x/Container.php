<?php
// +----------------------------------------------------------------------
// | 请求级容器
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------
namespace x;

class Container
{
    /**
     * 容器对象实例
     * @var Container
     */
    protected static $instance;

    /**
     * 容器中的对象实例
     * @var array
     */
    protected $instances = [];
    
    /**
     * 容器绑定标识
     * @var array
     */
    protected $bind = [];

    /**
     * 容器标识别名
     * @var array
     */
    protected $name = [];

    /**
     * 获取当前容器的实例(单例)
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * 获取当前进程下的顶级协程ID
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param int $id 协程父ID
     * @return int
    */
    private function getCoroutineId($id = null) {
        if ($id === null) {
            $id = \Swoole\Coroutine::getCid();
        }
        $pid = \Swoole\Coroutine::getPcid($id);
        if ($pid < 0) {
            return $id;
        }
        return $this->getCoroutineId($pid);
    }

    /**
     * 获取容器中的对象实例
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string        $abstract       类名或者标识
     * @param  array|true    $vars           变量
     * @param  bool          $newInstance    是否每次创建新的实例
     * @return object
    */
    public static function get($abstract, $vars = [], $newInstance = false)
    {
        return static::getInstance()->make($abstract, $vars, $newInstance);
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string  $abstract    类标识、接口
     * @param  mixed   $concrete    要绑定的类、闭包或者实例
     * @return Container
     * @return void
    */
    public static function set($abstract, $concrete = null)
    {
        return static::getInstance()->bindTo($abstract, $concrete);
    }

    /**
     * 移除容器中的对象实例
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string  $abstract    类标识、接口
     * @return void
    */
    public static function remove($abstract)
    {
        return static::getInstance()->delete($abstract);
    }
    
    /**
     * 清除容器中的对象实例
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function clear()
    {
        return static::getInstance()->flush();
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @access public
     * @param  string|array  $abstract    类标识、接口
     * @param  mixed         $concrete    要绑定的类、闭包或者实例
     * @return $this
    */
    public function bindTo($abstract, $concrete = null)
    {   
        // 协程ID
        $CoroutineId = $this->getCoroutineId();
        // 批量绑定
        if (is_array($abstract)) {
            $this->bind[$CoroutineId] = array_merge($this->bind[$CoroutineId], $abstract);
        // 匿名或闭包类绑定
        } elseif ($concrete instanceof Closure) {
            $this->bind[$CoroutineId][$abstract] = $concrete;
        // class类绑定
        } elseif (is_object($concrete)) {
            if (isset($this->bind[$CoroutineId][$abstract])) {
                $abstract = $this->bind[$CoroutineId][$abstract];
            }
            $this->instances[$CoroutineId][$abstract] = $concrete;
        // 其他绑定
        } else {
            $this->bind[$CoroutineId][$abstract] = $concrete;
        }

        return $this;
    }
    
    /**
     * 绑定一个类实例到当前容器中
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string           $abstract    类名或者标识
     * @param  object|\Closure  $instance    类的实例
     * @return $this
    */
    public function instance($abstract, $instance)
    {
        // 协程ID
        $CoroutineId = $this->getCoroutineId();
        if ($instance instanceof \Closure) {
            $this->bind[$CoroutineId][$abstract] = $instance;
        } else {
            if (isset($this->bind[$CoroutineId][$abstract])) {
                $abstract = $this->bind[$CoroutineId][$abstract];
            }

            $this->instances[$CoroutineId][$abstract] = $instance;
        }

        return $this;
    }

    /**
     * 判断容器中是否存在对象实例
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string    $abstract    类名或者标识
     * @return void
    */
    public function exists($abstract)
    {
        // 协程ID
        $CoroutineId = $this->getCoroutineId();
        if (isset($this->bind[$CoroutineId][$abstract])) {
            $abstract = $this->bind[$CoroutineId][$abstract];
        }

        return isset($this->instances[$CoroutineId][$abstract]);
    }
    
    /**
     * 判断容器中是否存在类及标识
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string    $name    类名或者标识
     * @return bool
    */
    public function has($name)
    {
        // 协程ID
        $CoroutineId = $this->getCoroutineId();
        return isset($this->bind[$CoroutineId][$name]) || isset($this->instances[$CoroutineId][$name]);
    }

    /**
     * 创建类的实例
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string        $abstract       类名或者标识
     * @param  array|true    $vars           变量
     * @param  bool          $newInstance    是否每次创建新的实例
     * @return object
    */
    public function make($abstract, $vars = [], $newInstance = false)
    {
        if (true === $vars) {
            // 总是创建新的实例化对象
            $newInstance = true;
            $vars        = [];
        }

        // 协程ID
        $CoroutineId = $this->getCoroutineId();
        $abstract = isset($this->name[$CoroutineId][$abstract]) ? $this->name[$CoroutineId][$abstract] : $abstract;

        if (isset($this->instances[$CoroutineId][$abstract]) && !$newInstance) {
            return $this->instances[$CoroutineId][$abstract];
        }

        if (isset($this->bind[$CoroutineId][$abstract])) {
            $concrete = $this->bind[$CoroutineId][$abstract];

            if ($concrete instanceof Closure) {
                $object = $this->invokeFunction($concrete, $vars);
                return $object;
            } else {
                $this->name[$CoroutineId][$abstract] = $concrete;
                return $this->make($concrete, $vars, $newInstance);
            }
        } else {
            // 进程级容器查找
            if (\x\ContainerProcess::getInstance()->has($abstract)) {
                return \x\ContainerProcess::getInstance()->make($abstract, $vars, $newInstance);
            }
        }
        return false;
    }
    
    /**
     * 删除容器中的对象实例
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string|array    $abstract    类名或者标识
     * @return void
    */
    public function delete($abstract)
    {
        // 协程ID
        $CoroutineId = $this->getCoroutineId();
        foreach ((array) $abstract as $name) {
            $name = isset($this->name[$CoroutineId][$name]) ? $this->name[$CoroutineId][$name] : $name;

            if (isset($this->instances[$CoroutineId][$name])) {
                unset($this->instances[$CoroutineId][$name]);
            }
        }
    }
    
    /**
     * 获取容器中的全部对象实例
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    public function all()
    {
        // 协程ID
        $CoroutineId = $this->getCoroutineId();
        return $this->instances[$CoroutineId];
    }

    /**
     * 清除容器中的对象实例
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function flush()
    {
        // 协程ID
        $CoroutineId = $this->getCoroutineId();
        unset($this->instances[$CoroutineId]);
        unset($this->bind[$CoroutineId]);
        unset($this->name[$CoroutineId]);
    }
    
    /**
     * 获取容器中进程的对象长度
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return int
    */
    public function count()
    {
        // 协程ID
        $CoroutineId = $this->getCoroutineId();
        return count($this->instances[$CoroutineId]);
    }
    
    /**
     * 获取容器中的全部对象长度
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return int
    */
    public function sum()
    {
        $num = 0;
        foreach ($this->instances as $v) {
            $num += count($v);
        }
        return $num;
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  mixed  $function 函数或者闭包
     * @param  array  $vars     参数
     * @return mixed
    */
    public function invokeFunction($function, $vars = [])
    {
        try {
            $reflect = new \ReflectionFunction($function);

            $args = $this->bindParams($reflect, $vars);

            return call_user_func_array($function, $args);
        } catch (\ReflectionException $e) {
            throw new \Exception('function not exists: ' . $function . '()');
        }
    }

    
    /**
     * 调用反射执行类的方法 支持参数绑定
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  mixed   $method 方法
     * @param  array   $vars   参数
     * @return mixed
    */
    public function invokeMethod($method, $vars = [])
    {
        try {
            if (is_array($method)) {
                $class   = is_object($method[0]) ? $method[0] : $this->invokeClass($method[0]);
                $reflect = new \ReflectionMethod($class, $method[1]);
            } else {
                // 静态方法
                $reflect = new \ReflectionMethod($method);
            }

            $args = $this->bindParams($reflect, $vars);

            return $reflect->invokeArgs(isset($class) ? $class : null, $args);
        } catch (\ReflectionException $e) {
            if (is_array($method) && is_object($method[0])) {
                $method[0] = get_class($method[0]);
            }

            throw new \Exception('method not exists: ' . (is_array($method) ? $method[0] . '::' . $method[1] : $method) . '()');
        }
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  object  $instance 对象实例
     * @param  mixed   $reflect 反射类
     * @param  array   $vars   参数
     * @return mixed
    */
    public function invokeReflectMethod($instance, $reflect, $vars = [])
    {
        $args = $this->bindParams($reflect, $vars);

        return $reflect->invokeArgs($instance, $args);
    }

    /**
     * 调用反射执行callable 支持参数绑定
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  mixed $callable
     * @param  array $vars   参数
     * @return mixed
    */
    public function invoke($callable, $vars = [])
    {
        if ($callable instanceof Closure) {
            return $this->invokeFunction($callable, $vars);
        }

        return $this->invokeMethod($callable, $vars);
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string    $class 类名
     * @param  array     $vars  参数
     * @return mixed
    */
    public function invokeClass($class, $vars = [])
    {
        try {
            $reflect = new \ReflectionClass($class);

            if ($reflect->hasMethod('__make')) {
                $method = new \ReflectionMethod($class, '__make');

                if ($method->isPublic() && $method->isStatic()) {
                    $args = $this->bindParams($method, $vars);
                    return $method->invokeArgs(null, $args);
                }
            }

            $constructor = $reflect->getConstructor();

            $args = $constructor ? $this->bindParams($constructor, $vars) : [];

            return $reflect->newInstanceArgs($args);

        } catch (\ReflectionException $e) {
            throw new \Exception('class not exists: ' . $class, $class);
        }
    }

    /**
     * 绑定参数
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  \ReflectionMethod|\ReflectionFunction $reflect 反射类
     * @param  array                                 $vars    参数
     * @return array
    */
    protected function bindParams($reflect, $vars = [])
    {
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        // 判断数组类型 数字数组时按顺序绑定参数
        reset($vars);
        $type   = key($vars) === 0 ? 1 : 0;
        $params = $reflect->getParameters();

        foreach ($params as $param) {
            $name      = $param->getName();
            $lowerName = \x\Loader::parseName($name);
            $class     = $param->getClass();

            if ($class) {
                $args[] = $this->getObjectParam($class->getName(), $vars);
            } elseif (1 == $type && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif (0 == $type && isset($vars[$name])) {
                $args[] = $vars[$name];
            } elseif (0 == $type && isset($vars[$lowerName])) {
                $args[] = $vars[$lowerName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \Exception('method param miss:' . $name);
            }
        }

        return $args;
    }

    /**
     * 获取对象类型的参数值
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @param  string   $className  类名
     * @param  array    $vars       参数
     * @return mixed
    */
    protected function getObjectParam($className, &$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }

    public function __unset($name)
    {
        $this->delete($name);
    }

    public function offsetUnset($key)
    {
        $this->__unset($key);
    }
}