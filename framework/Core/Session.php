<?php

namespace T4\Core;

class Session
{

    const SESSION_KEY = '__t4';

    public static function init()
    {
        session_start();
    }

    public static function set($key, $val)
    {
        if (!isset($_SESSION[self::SESSION_KEY]))
            $_SESSION[self::SESSION_KEY] = new Std();
        $_SESSION[self::SESSION_KEY]->{$key} = $val;
    }

    public static function get($key)
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !isset($_SESSION[self::SESSION_KEY]->{$key}))
            return null;
        return $_SESSION[self::SESSION_KEY]->{$key};
    }

    public static function clear($key)
    {
        unset($_SESSION[self::SESSION_KEY]->{$key});
    }

}