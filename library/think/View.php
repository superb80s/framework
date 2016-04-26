<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

class View
{
    // 视图实例
    protected static $instance = null;
    // 模板引擎实例
    public $engine = null;
    // 模板变量
    protected $data = [];
    // 视图输出替换
    protected $replace = [];

    /**
     * 架构函数
     * @access public
     * @param array $engine  模板引擎参数
     * @param array $replace  字符串替换参数
     */
    public function __construct($engine = [], $replace = [])
    {
        // 初始化模板引擎
        $this->engine((array) $engine);
        $this->replace = $replace;
    }

    /**
     * 初始化视图
     * @access public
     * @param array $engine  模板引擎参数
     * @return object
     */
    public static function instance($engine = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($engine);
        }
        return self::$instance;
    }

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name  变量名
     * @param mixed $value 变量值
     * @return $this
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
            return $this;
        } else {
            $this->data[$name] = $value;
        }
        return $this;
    }

    /**
     * 设置当前模板解析的引擎
     * @access public
     * @param array $options 引擎参数
     * @return $this
     */
    public function engine($options = [])
    {
        $type  = !empty($options['type']) ? $options['type'] : 'Think';
        $class = (!empty($options['namespace']) ? $options['namespace'] : '\\think\\view\\driver\\') . ucfirst($type);
        unset($options['type']);
        $this->engine = new $class($options);
        return $this;
    }

    /**
     * 解析和获取模板内容 用于输出
     * @param string $template 模板文件名或者内容
     * @param array  $vars     模板输出变量
     * @param array  $config     模板参数
     * @param bool   $renderContent 是否渲染内容
     * @return string
     * @throws Exception
     */
    public function fetch($template = '', $vars = [], $config = [], $renderContent = false)
    {
        // 模板变量
        $vars = array_merge($this->data, $vars);

        // 页面缓存
        ob_start();
        ob_implicit_flush(0);

        // 渲染输出
        $method = $renderContent ? 'display' : 'fetch';
        $this->engine->$method($template, $vars, $config);

        // 获取并清空缓存
        $content = ob_get_clean();
        // 内容过滤标签
        APP_HOOK && Hook::listen('view_filter', $content);
        // 允许用户自定义模板的字符串替换
        if (!empty($this->replace)) {
            $content = str_replace(array_keys($this->replace), array_values($this->replace), $content);
        }
        return $content;
    }

    /**
     * 视图内容替换
     * @access public
     * @param string|array $content 被替换内容（支持批量替换）
     * @param string  $replace    替换内容
     * @return $this
     */
    public function replace($content, $replace = '')
    {
        if (is_array($content)) {
            $this->replace = array_merge($this->replace, $content);
        } else {
            $this->replace[$content] = $replace;
        }
        return $this;
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array  $vars    模板输出变量
     * @param array  $config     模板参数
     * @return mixed
     */
    public function display($content, $vars = [], $config = [])
    {
        return $this->fetch($content, $vars, $config, true);
    }

    /**
     * 模板变量赋值
     * @access public
     * @param string $name  变量名
     * @param mixed $value 变量值
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 取得模板显示变量的值
     * @access protected
     * @param string $name 模板变量
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * 检测模板变量是否设置
     * @access public
     * @param string $name 模板变量名
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
}
