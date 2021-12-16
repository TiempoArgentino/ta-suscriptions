<?php

class Subscriptions_Sessions
{

    static private $init = false;

    static public $messages = [];

    public static function init()
    {
        if (self::$init)
            return false;
        self::$init = true;

        
        
        add_action('init',[self::class,'sessions'],1);
        
        add_action('wp_loaded',[self::class,'set_session'],10,2);

        if(isset($_SESSION['flash_messages'])) {
            self::$messages = $_SESSION['flash_messages'];

            $_SESSION['flash_messages'] = [];
        }
       
    }
    /**
     * https://www.php.net/manual/es/function.setcookie.php
     */
    public static function set_cookie($name, $value, $expire = 0, $secure = false, $httponly = false)
    {
       if(!headers_sent()){
           setcookie($name, $value, $expire, $secure, $httponly);
       } else {
           headers_sent($file,$line);
           trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
       }  
    }
    /**
     * Initialize the sessions
     */
    public static function sessions() 
    {
        if(!headers_sent()) {
            if(!session_id()) {
                session_start();
            }
        }
    }
    /**
     * Session set
     */
    public static function set_session($session_name,$data = [])
    {
        if(!isset($_SESSION[$session_name])) {
            $_SESSION[$session_name] = $data;
        }
    }
    /**
     * Session get
     */
    public static function get_session($session_name)
    {
        if(isset($_SESSION[$session_name])) {
            return $_SESSION[$session_name];
        } else {
            return;
        }
    }
    /**
     * Session update
     */
    public static function update_session($session_name,$key,$val)
    {
        if(isset($_SESSION[$session_name])) {
            return $_SESSION[$session_name][$key] = $val;
        } else {
            return;
        }
    }
    /**
     * Session destroy
     */
    public static function destroy_session($session_name)
    {
        unset($_SESSION[$session_name]);
    }
    /**
     * Flash
     */
    public static function set_flash_session($class,$msg)
    {
       /**
         * Init sessions if not
         */
        if (!session_id()) {
            session_start();
        }
        /**
         * Create session if not exist
         */
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = ["hola"];
        } else {
            $_SESSION['flash_messages'] = [];
        }

        $_SESSION['flash_messages'] = [
            'name' => $class,
            'msg' => $msg
        ];

        return $_SESSION['flash_messages'];
    }
   
}