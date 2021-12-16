<?php


class Subscriptions_Messages
{
    static private $init = false;

    public static function init()
    {
        if (self::$init)
            return false;
        self::$init = true;
    }

    public static function messages($class, $message)
    {
        echo '<div class="alert alert-' . $class . '" role="alert">
        ' . $message . '
        </div>';
    }
}


