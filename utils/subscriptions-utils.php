<?php


require_once plugin_dir_path( dirname(__FILE__) ) . 'utils/class-subscriptions-messages.php';
require_once plugin_dir_path( dirname(__FILE__) ) . 'utils/class-subscriptions-session.php';

Subscriptions_Messages::init();
Subscriptions_Sessions::init();