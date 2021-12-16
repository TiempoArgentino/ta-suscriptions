<?php do_action('before_subscriptions_profile_page') ?>
<?php do_action('subscriptions_edit_actions') ?>
<div class="content-panel" id="subscription">
    <div class="row mt-3" id="membership-data">
        <?php if (membership()->get_membership(wp_get_current_user()->ID)['id'] === null || isset(membership()->get_membership(wp_get_current_user()->ID)['status']) === 'cancel') : ?>
            <div class="col-12 text-center">
                <h2 class="mt-3 mb-5"><?php echo __('You are not a member', 'subscriptions') ?></h2>
                <a href="<?php echo get_permalink(get_option('subscriptions_loop_page')) ?>" class="btn btn-primary text-white text-uppercase"><?php echo __('become a member', 'subscriptions') ?></a>
            </div>
        <?php else : ?>
            <div class="col-12 text-center">
                <?php if (membership()->get_membership(wp_get_current_user()->ID)['type'] === 'subscription') : ?>
                    <h2 class="mt-3 mb-5"><?php echo __('You are subscribe to:', 'subscriptions') ?></h2>
                    <img src="<?php echo membership()->get_membership(wp_get_current_user()->ID)['image'] ?>" />
                    <h3><?php echo membership()->get_membership(wp_get_current_user()->ID)['title'] ?></h3>
                <?php else : ?>
                    <h2 class="mt-3 mb-5"><?php echo __('Your contribution:', 'subscriptions') ?></h2>
                <?php endif; ?>
                <div class="period mt-5 mb-5"><strong><?php echo __('Your billing period: ', 'subscriptions') ?> <?php echo membership()->get_membership(wp_get_current_user()->ID)['period'] ?></strong><br />
                    <strong><?php echo __('and you pay: ', 'subscriptions') ?><?php echo get_option('subscriptions_currency_symbol') ?><?php echo membership()->get_membership(wp_get_current_user()->ID)['price'] ?></strong>
                </div>
                <div class="buttons">
                    <button type="button" name="edit_subscription" id="edit_subscription" class="btn btn-primary"><?php echo __('Edit', 'subscriptions') ?></button>
                    <?php do_action('edit_membership_user_panel') ?>
                    <form method="post" id="cancel-form-membership">
                        <input type="hidden" name="membership_id" value="<?php echo membership()->get_membership(wp_get_current_user()->ID)['id'] ?>">
                        <input type="hidden" name="payment_method_id" value="<?php echo membership()->get_membership(wp_get_current_user()->ID)['payment'] ?>">
                        <input type="hidden" name="action" value="cancel_membership" />
                        <input type="hidden" name="user_id" value="<?php echo wp_get_current_user()->ID ?>">
                        <button type="submit" name="cancel_subscription" class="btn btn-danger"><?php echo __('Cancel membership', 'subscriptions') ?></button>
                    </form>
                </div>
                <div id="become-a-member">
                    <?php if (membership()->get_membership(wp_get_current_user()->ID)['type'] === 'donation') : ?>
                        <h3 class="mt-5 mb-3"><?php echo __('Convert your contribution in a subscription', 'subscriptions') ?></h3>
                        <a href="<?php echo get_permalink(get_option('subscriptions_loop_page')) ?>" class="btn btn-primary text-white text-uppercase"><?php echo __('become a member', 'subscriptions') ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-12">
                <div class="row mt-3" id="membership-edit-subscriptions">
                    <div class="col-12 col-md-6 mx-auto text-center">

                        <?php do_action('subscriptions_edit_actions') ?>

                        <h3><?php echo __('Edit your subscription', 'subscriptions') ?></h3>
                        <p><?php echo __('Remember, the minimun for this transaction is: ', 'subscriptions') ?>$ <span id="min-price-show"><?php echo membership()->get_minimun() ?></span></p>
                        <div id="msg-form"></div>
                        <form method="post" id="edit-payment-subscriptions" class="subscritpions-form">
                        <?php if (membership()->get_membership(wp_get_current_user()->ID)['type'] === 'subscription') : ?>
                            <div class="form-group">
                                <label><?php echo __('Change my membership', 'subscriptions') ?></label>
                                <?php membership()->get_subscriptions_names('form-control') ?>
                            </div>
                            <div class="form-group" id="physical">
                                <label for=""><?php echo __('Add / Quit paper', 'subscriptions') ?></label>
                                <input type="checkbox" name="paper" id="paper" value="<?php echo membership()->get_paper_value() ?>" <?php echo checked('1', membership()->get_membership(wp_get_current_user()->ID)['physical'], false) ?> />
                            </div>
                            <?php endif;?>
                            <div class="from-groups prices mb-3">
                                <?php echo __('Select a price: ','subscriptions')?>
                                <?php if(membership()->get_price()):?>
                                    <span class="price-select"><?php echo membership()->get_price()?></span>
                                <?php endif;?>
                                <?php foreach(membership()->extra_price() as $key => $val):?>
                                    <span class="price-select"><?php echo $val?></span>
                                <?php endforeach;?>
                            </div>
                            <p><?php echo __('or','subscriptions')?></p>
                            <div class="form-group">
                                <label for=""><?php echo __('Add your price', 'subscriptions') ?></label>
                                <input type="number" min="<?php echo membership()->get_minimun() ?>" name="amount" id="amount-subscription" placeholder="<?php echo __('Price', 'subscriptions') ?>" class="form-control" value="<?php echo membership()->get_minimun() ?>" />
                            </div>
                            <?php do_action('edit_form_extra')?>
                            <input type="hidden" name="payment_method_id" value="<?php echo membership()->get_membership(wp_get_current_user()->ID)['payment'] ?>">
                            <input type="hidden" name="payment_min" id="payment_min_s" value="<?php echo membership()->get_minimun() ?>" />
                            <input type="hidden" name="membership_id" value="<?php echo membership()->get_membership(wp_get_current_user()->ID)['id'] ?>" />
                            <input type="hidden" name="user_id" value="<?php echo wp_get_current_user()->ID ?>">
                            <input type="hidden" name="subscription_name" value="" id="subscription_name">
                            <input type="hidden" name="action" value="edit_membership" />
                            <input type="hidden" id="user_edit_email" name="user_edit_email" class="form-control" value="<?php echo wp_get_current_user()->user_email?>" />
                            <button type="submit" name="send-edit-subscription" id="send-edit-subscription" class="btn btn-primary"><?php echo __('Edit membership', 'subscriptions') ?></button>
                            <button type="button" id="cancel-edit-subscription" class="btn btn-info"><?php echo __('Cancel', 'subscriptions') ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php do_action('subscriptions_profile_extra_content') ?>
    </div>
</div>