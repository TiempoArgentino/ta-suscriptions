<?php

/**
 * This template is the loop for all subscriptions, it also has the login and registration and the payment getways
 */
get_header();
?>
<div class="container">
    <div class="row">
        <div class="col-12 text-center p-5">
            <h1><?php echo __('Support us', 'subscriptions') ?></h1>
        </div>
        <hr />
    </div>
    <div class="row" id="subscriptions-loop">
        <?php
        $args = array(
            'post_type' => 'subscriptions',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_is_donation',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => '_is_donation',
                    'value' => ['1'],
                    'compare' => 'IN'
                ],
            ]
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
        ?>
                <div class="text-center col-12 donation-container">
                    <?php
                    echo '<h4><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h4>';
                    ?>
                    <?php echo get_the_content() ?>
                    <div class="prices-list text-center">
                        <!-- prices -->
                        <?php
                        $price_main = get_post_meta(get_the_ID(), '_s_price', true);
                        $prices_extra = get_post_meta(get_the_ID(), '_prices_extra', true);
                        if ($prices_extra) {
                            array_push($prices_extra, $price_main);
                        }
                        $price_min = !$prices_extra ? $price_main : min($prices_extra);
                        ?>
                        <!-- prices -->
                        <strong><?php echo __('Prices: ', 'subscriptions') ?></strong><br />
                        <span class="price" data-id="<?php echo get_the_ID() ?>" data-price="<?php echo get_post_meta(get_the_ID(), '_s_price', true) ?>"><?php echo get_option('subscriptions_currency_symbol', 'ARS') . ' ' . get_post_meta(get_the_ID(), '_s_price', true) ?></span>
                        <?php
                        if (get_post_meta(get_the_ID(), '_prices_extra', true) && count(get_post_meta(get_the_ID(), '_prices_extra', true)) > 0) {
                            foreach (get_post_meta(get_the_ID(), '_prices_extra', true) as $key => $value) {
                                echo '<div class="price" data-id="' . get_the_ID() . '" data-price="' . $value . '">' . get_option('subscriptions_currency_symbol', 'ARS') . ' ' . $value . '</div>';
                            }
                        }
                        ?>
                        <?php
                        if (get_post_meta(get_the_ID(), '_price_custom', true)) {
                            echo '<div class="custom-price-button  text-center"><strong data-id="' . get_the_ID() . '" data-min="' . $price_min . '" data-title="' . get_the_title() . '" class="open-price btn btn-small btn-secondary">' . __('I would like to contribute more money', 'subscriptions') . '</strong></div>';
                        }
                        ?>
                    </div>
                    <p class="d-flex justify-content-between align-items-center buttons-container">
                        <button type="button" class="btn btn-primary btn-block text-uppercase font-weight-bold button-suscribe" data-type="donation" disabled id="button<?php echo get_the_ID() ?>" data-id="<?php echo get_the_ID() ?>" data-price="" data-name="<?php echo get_the_title() ?>">
                            <?php echo __('associate', 'subscriptions') ?>
                        </button>
                    </p>
                    <?php  if(get_post_meta(get_the_ID(),'_discount',true) !== ''):?>
                    <p>
                        <button type="button" id="discount-button" class="btn btn-primary"><?php echo __('I need a discount','subscriptions')?></button>
                    </p>
                    <?php endif;?>
                </div>
                <div class="col-12 text-center" id="discount-data">
                <hr>
                     <h2><?php echo sprintf(__('From %s we want to offer you the possibility of supporting our environment by offering you a %s discount on your contribution','subscriptions'),bloginfo('name'),get_post_meta(get_the_ID(),'_discount',true).'%')?></h2>
                     <h3 class=" mb-5">
                        <p class="price-before"><?php echo __('Before: ','subscriptions')?> <span><?php echo get_option('subscriptions_currency_symbol', 'ARS')?><?php echo $price_min?></span> </p>
                     </h3>
                     <h2 class="mt-5 mb-5 now">
                        <p><?php $new_price = ($price_min * 20) / 100; echo __('Now: ','subscriptions')?> <?php echo get_option('subscriptions_currency_symbol', 'ARS')?><?php echo $new_price?></span></p>
                        <input type="hidden" id="new_price" data-type="donation" data-name="<?php echo get_the_title() ?>" data-id="<?php echo get_the_ID() ?>" value="<?php echo $new_price?>" />
                        <p><button type="button"  data-payment_page="<?php echo get_permalink(get_option('subscriptions_payment_page')); ?>" class="btn btn-primary" id="next-discount"><?php echo __('Next','subscriptions')?></button></p>
                     </h2>
                     <div class="mt-5 pt-5 pb-5">
                        <?php echo __('Si necesitás un descuento mayor','subscriptions')?><br />
                        <button type="button" class="btn btn-info mt-3" id="next-contact"><?php echo __('Click here','subscriptions')?></button>
                     </div>
                </div>
            <?php
            endwhile;
            wp_reset_postdata();
            ?>
            <div id="contact-form" class="col-12 mt-5 text-center">
                <h3><?php echo __('We want to know your situation:','subscriptions')?></h3>
                <h4><?php echo __('Tell us your name, your e-mail and why you think you need this discount:','subscriptions')?></h4>
                <form method="post" class="col-md-6 mx-auto col-12 mt-5 pb-5">
                    <div class="form-group">
                        <label class="text-left"><?php echo __('Name and Lastname','subscriptions')?></label>
                        <input type="text" name="name" id="name_support_us" value="" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label class="text-left"><?php echo __('Email', 'subscriptions')?></label>
                        <input type="email" name="email" id="email_support_us" value="" class="form-control" required />
                    </div>
                    <h5><?php echo __('Write us a message:','subscriptions')?></h5>
                    <div class="form-group">
                        <label class="text-left"><?php echo __('Message','subscriptions')?></label>
                        <textarea name="msg" id="msg_support_us" class="form-control" required></textarea>
                    </div>
                    <button type="button" id="send-contact" class="btn btn-primary"><?php echo __('Send','subscriptions')?></button>
                </form>
            </div>
            <div id="contact-thankyou" class="col-12 mt-5 text-center">
               <h3><?php echo __('Thank you','subscriptions')?> <span id="name-thanks"></span></h3>
                <h5> <?php echo __('Very soon someone from our site will write to you by e-mail. In the meantime you can continue reading our site.','subscriptions')?></h5>
                <p>
                    <a href="<?php echo home_url()?>" class="btn btn-primary text-uppercase mt-5 mb-5 text-white"><?php echo __('go to site','subscriptions')?></a>
                </p>
            </div>
    </div>
    <!-- custom price -->
    <div class="row" id="custom-price-row">
        <div class="col-12 text-center">
            <h3><?php echo __('How much do you want to contribute?', 'subscriptions'); ?></h3>
            <h5><?php echo __('Enter the amount you want.', 'subscriptions'); ?> <?php echo __('Min amount for this subscriptions is: ', 'subscriptions') ?> <?php echo get_option('subscriptions_currency_symbol', 'ARS') ?> <span id="price-min-span"></span></h5>
            <div class="form-group">
                <input type="number" name="custom_price" id="custom-price-input" class="form-control form-control-lg mt-3 mb-3">
                <div class="invalid-feedback">
                    <?php echo __('The minimum amount cannot be less than ', 'subscriptions') ?> <span id="minimum"></span>
                </div>
            </div>
            <button class="btn btn-primary btn-block btn-lg" id="custom-next" data-type="donation"><?php echo __('Next', 'subscriptions') ?></button>
            <p class="cancel-custom-price mt-2 mb-5 btn btn-small btn-warning"><?php echo __('Change selection', 'subscriptions') ?></p>
        </div>
    </div>
    <!-- login register -->

    <div class="row mt-5" id="login-register-loop">
        <?php if (!is_user_logged_in()) : ?>
            <div class="col-12 text-center form-title">
                <h5><?php echo __('Login to your account or create one to finish associating with', 'subscriptions') ?> <?php echo bloginfo('name') ?></h5>
                <button type="button" class="btn btn-warning text-uppercase" id="login-title-loop"><?php echo __('Sign in', 'subscriptions') ?></button>
            </div>
            <!-- form -->
            <div class="col-md-6 col-12 mx-auto" id="login-form-loop">
                <div id="message-login-response"></div>
                <form method="post" class="mb-5 login-form-loop">
                    <div class="form-group">
                        <label for=""><?php echo __('Email', 'subscriptions') ?></label>
                        <input type="email" autocomplete="off" name="username" id="username" class="form-control form-control-lg" value="<?php echo isset($_POST['username']) ? sanitize_email($_POST['username']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for=""><?php echo __('Password', 'subscriptions') ?></label>
                        <input type="password" name="password" id="password" class="form-control form-control-lg" value="" required>
                    </div>
                    <input type="hidden" name="redirect_to" id="redirect_to" value="<?php echo get_permalink(get_option('subscriptions_payment_page')); ?>">
                    <button type="button" id="send-login" class="btn btn-primary btn-block btn-lg" name="send-login"><?php echo __('Sign in', 'subscriptions') ?></button>
                    <p class="cancel-login mt-2 mb-5 text-center"><span class="btn btn-small btn-warning"><?php echo __('Change selection', 'subscriptions') ?></span></p>
                </form>
            </div>
            <!-- register --->
            <?php if (get_option('users_can_register') !== '0') : ?>
                <div class="col-12 text-center mt-5 form-title">
                    <h5><?php echo __('Don\'t have an account?', 'subscriptions') ?></h5>
                    <button type="button" class="btn btn-info text-uppercase" id="register-title-loop"><?php echo __('Sign up', 'subscriptions') ?></button>
                </div>
                <!-- form -->
                <div class="col-md-6 col-12 mx-auto" id="register-form-loop">
                    <div id="message-register-response"></div>
                    <form method="post" class="mb-5 register-form-loop">
                        <div class="form-group">
                            <label><?php echo __('Firstname *', 'subscriptions') ?></label>
                            <input type="text" name="first_name" id="first_name" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : '' ?>" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label><?php echo __('Lastname *', 'subscriptions') ?></label>
                            <input type="text" name="last_name" id="last_name" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : '' ?>" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label><?php echo __('Email *', 'subscriptions') ?></label>
                            <input type="email" autocomplete="off" name="email" id="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : '' ?>" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label><?php echo __('Password *', 'subscriptions') ?></label>
                            <input type="password" name="password" id="passwd" value="" class="form-control" />
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="register_redirect" id="register_redirect" value="<?php echo get_permalink(get_option('subscriptions_payment_page')); ?>">
                            <button type="button" name="submit-register" id="submit-register" class="btn btn-block btn-primary"><?php echo __('Sign up', 'subscriptions') ?></button>
                            <p class="cancel-login mt-2 mb-5 text-center"><span class="btn btn-small btn-warning"><?php echo __('Change selection', 'subscriptions') ?></span></p>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            <!-- register --->
        <?php endif; ?>
        <?php if (is_user_logged_in()) : ?>
            <div class="col-12 user-logged text-center">
                <h4><?php echo __('Welcome again', 'subscriptions'); ?> <?php echo wp_get_current_user()->first_name; ?></h4>
                <button class="btn btn-primary btn-block btn-lg mt-5" id="continue-next" data-payment_page="<?php echo get_permalink(get_option('subscriptions_payment_page')); ?>"><?php echo __('Continue to payment page', 'subscriptions') ?></button>
            </div>
        <?php endif; ?>
    </div>
<?php
        else :
            echo '<div class="col-12 text-center"> ' . __('Sorry, no subscriptions matched your criteria.', 'subscriptions') . '</div>';
        endif;
?>
<?php do_action('subscriptions_loop_footer') ?>
</div>
<?php get_footer() ?>