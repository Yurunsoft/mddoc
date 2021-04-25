<?php

namespace Yurun\MdDoc;

class Args
{
    private static $cliArgs;

    public static function init()
    {
        static::$cliArgs = [];
        $keyName = null;
        for ($i = 1; $i < $_SERVER['argc']; ++$i)
        {
            if (isset($_SERVER['argv'][$i][0]) && '-' === $_SERVER['argv'][$i][0])
            {
                $keyName = substr($_SERVER['argv'][$i], 1);
                static::$cliArgs[$keyName] = true;
            }
            else
            {
                if (null === $keyName)
                {
                    static::$cliArgs[$_SERVER['argv'][$i]] = true;
                }
                else
                {
                    static::$cliArgs[$keyName] = $_SERVER['argv'][$i];
                    $keyName = null;
                }
            }
        }
    }

    /**
     * 指定数据是否存在.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function exists($name)
    {
        if (\is_int($name))
        {
            return isset($_SERVER['argv'][$name]);
        }
        else
        {
            return isset(static::$cliArgs[$name]);
        }
    }

    /**
     * 获取值
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get($name = '', $default = null)
    {
        if (\is_int($name))
        {
            $data = $_SERVER['argv'];
        }
        else
        {
            $data = &static::$cliArgs;
        }
        if ('' === $name)
        {
            // 全部的值
            return $data;
        }
        // 判断指定的值是否存在
        elseif (isset($data[$name]))
        {
            return $data[$name];
        }
        else
        {
            // 返回默认值
            return $default;
        }
    }
}
