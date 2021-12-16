<?php

get_header();

do_action('subscriptions_payment_page_header');

?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <?php do_action('subscriptions_payment_page_message_before'); ?>

            <?php if (!Subscriptions_Sessions::get_session('subscriptions_add_session') || !is_user_logged_in()) : ?>
                <?php
                echo '<div class="col-md-6 mx-auto col-12 text-center alert alert-info">';
                echo __('You have to select a subscription and be logged in to make a payment. Please ', 'subscriptions') . '<a href="' . get_permalink(get_option('subscriptions_loop_page')) . '">' . __('click here', 'subscriptions') . '.</a>';
                echo '</div>';
                return;
                ?>
            <?php else : ?>
                    <?php 
                        if (Subscriptions_Sessions::get_session('subscriptions_add_session')['suscription_address'] === '1') : 
                            $address = get_user_meta(get_current_user_id(),'_user_address',false);
                    ?>
                    <div class="col-md-6 col-12 mx-md-auto text-center" id="address-container">
                        <h3><?php echo sprintf(__('Hi %s we need your address.', 'subscriptions'), wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name) ?></h3>
                        <div id="msg-ok"></div>
                        <form method="post" id="address-form" class="text-left">
                            <div class="form-group">
                                <label><?php echo __('State', 'subscriptions') ?></label>
                                <input type="text" class="form-control" name="state" id="state" value="<?php echo $address[0]['state'] !== null ? $address[0]['state'] : ''; ?>" required />
                            </div>
                            <div class="form-group">
                                <label><?php echo __('City', 'subscriptions') ?></label>
                                <input type="text" class="form-control" name="city" id="city" value="<?php echo $address[0]['city'] !== null ? $address[0]['city'] : ''; ?>" required />
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 col-12">
                                    <label><?php echo __('Address', 'subscriptions') ?></label>
                                    <input type="text" class="form-control" name="address" id="address" value="<?php echo $address[0]['address'] !== null ? $address[0]['address'] : ''; ?>" required />
                                    <p class="help small"><?php echo __('Street address, P.O. box, company name, c/o', 'subscriptions'); ?></p>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label><?php echo __('Street Nº', 'subscriptions') ?></label>
                                    <input type="text" class="form-control" name="number" id="number" value="<?php echo $address[0]['number'] !== null ? $address[0]['number'] : ''; ?>" required />
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Floor', 'subscriptions') ?></label>
                                <input type="text" class="form-control" name="floor" id="floor" value="<?php echo $address[0]['floor'] !== null ? $address[0]['floor'] : ''; ?>" />
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Apt', 'subscriptions') ?></label>
                                <input type="text" class="form-control" name="apt" id="apt" value="<?php echo $address[0]['apt'] !== null ? $address[0]['apt'] : ''; ?>" />
                            </div>
                            <div class="form-group">
                                <label><?php echo __('ZIP', 'subscriptions') ?></label>
                                <input type="text" class="form-control" name="zip" id="zip" value="<?php echo $address[0]['zip'] !== null ? $address[0]['zip'] : ''; ?>" required />
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Between streets', 'subscriptions') ?></label>
                                <input type="text" class="form-control" name="bstreet" id="bstreet" value="<?php echo $address[0]['bstreet'] !== null ? $address[0]['bstreet'] : ''; ?>" />
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Observations', 'subscriptions') ?></label>
                                <textarea name="observations" class="form-control" id="observations" cols="30" rows="4"><?php echo $address[0]['observations'] !== null ? $address[0]['observations'] : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <button type="button" name="address-button" id="address-button" class="btn btn-primary btn-lg btn-block"><?php echo __('Add Address', 'subscriptions') ?></button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <?php do_action('subscriptions_payment_page_before_methods') ?>
                <div class="col-12 text-center" <?php echo Subscriptions_Sessions::get_session('subscriptions_add_session')['suscription_address'] === '1' ? 'style="display:none"' : '' ?> id="payment-container">
                    <h2 class="text-center mb-3"><?php echo __('Payment', 'subscriptions') ?></h2>
                    <?php do_action('payment_messages'); ?>
                    <h3><?php echo sprintf(__('Thanks %s for suporting ', 'subscriptions'), wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name) ?> <?php echo get_bloginfo('name'); ?></h3>
                    <h4><?php echo __('To continue the process select a payment method', 'subscriptions'); ?></h4>
                    <div class=" mx-auto col-md-6 col-12 mt-3 mb-3">
                        <ul class="payment-methods">
                            <?php do_action('payment_getways') ?>
                        </ul>
                    </div>
                    <?php do_action('subscriptions_payment_page_after_methods'); ?>
                <?php endif; ?>
                <?php do_action('subscriptions_payment_page_message_after'); ?>
                </div>
        </div>
    </div>

    <?php

    do_action('subscriptions_payment_page_footer');

    get_footer();
    ?>