<?php

/**
 * This template is the loop for all subscriptions, it also has the login and registration and the payment getways
 */
get_header(); 

do_action('header_thankyou_page');

?>
<div class="container">
    <div class="row">
        <?php if (!Subscriptions_Sessions::get_session('subscriptions_add_session') || !is_user_logged_in()) : ?>
                <?php
                echo '<div class="col-md-6 mx-auto col-12 text-center alert alert-info">';
                echo __('You have to select a subscription and be logged in to make a payment. Please ', 'subscriptions') . '<a href="' . get_permalink(get_option('subscriptions_loop_page')) . '">' . __('click here', 'subscriptions') . '.</a>';
                echo '</div>';
                return;
                ?>
            <?php else : ?>
                <div class="col-12 text-center p-5">
                    <?php do_action('messages_thankyou'); ?>

                    <h1 class="mt-5 mb-5"><?php echo __('Thank you for you purchase mate...','subscriptions')?></h1>

                    <h3><?php echo sprintf(__('Thank you %s for chose us','subscriptions'), wp_get_current_user()->first_name); ?></h3>
                    <h3><?php echo __('A self-managed environment is possible thanks to people like you!','subscriptions'); ?></h3>

                    <p><?php echo __('You can continue browsing the site','subscriptions')?></p>
                    <a href="<?php echo home_url()?>" class="btn btn-warning"><?php echo __('Go to site','subscriptions')?></a> <a href="<?php echo get_permalink(get_option('subscriptions_profile'))?>" class="btn btn-primary"><?php echo __('Go to your profile','subscriptions')?></a>
                    
                    <?php do_action('body_thankyou_page') ?>
                </div>
                   
                </div>
            <?php endif; ?>
    </div>
</div>
<?php get_footer() ?>