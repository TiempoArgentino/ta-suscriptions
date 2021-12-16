<?php

/**
 * Login 
 * page default
 */
get_header();

do_action('user_register_actions');

$can_register = get_option('users_can_register');
?>
<div class="container">
    <div class="row">
    <?php if ($can_register !== '0') : ?>
            <div class="col-12 col-md-6" id="form-register-container">
                <?php do_action('subscriptions_before_register_form') ?>
                <h3><?php echo __('Become a member', 'subscriptions') ?></h3>
                <form method="post" class="suscription-register-form">
                    <div class="form-group">
                        <label><?php echo __('Firstname *', 'subscriptions') ?></label>
                        <input type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : '' ?>" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label><?php echo __('Lastname *', 'subscriptions') ?></label>
                        <input type="text" name="last_name" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : '' ?>" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label><?php echo __('Email *', 'subscriptions') ?></label>
                        <input type="email" autocomplete="off" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : '' ?>" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label><?php echo __('Password *', 'subscriptions') ?></label>
                        <input type="password" autocomplete="off" name="password" id="passwd" value="" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label><?php echo __('Repeat Password *', 'subscriptions') ?></label>
                        <input type="password" autocomplete="off" name="password2" id="passwd2" value="" class="form-control" />
                    </div>
                    <input type="hidden" name="register_redirect" value="<?php echo wp_get_referer() && wp_get_referer() !== get_permalink(get_option('subscriptions_register_page')) ? wp_get_referer() : get_permalink(get_option('subscriptions_profile')) ?>">
                    <div class="form-group">
                        <input type="submit" name="submit-register" value="<?php echo __('Sign up', 'subscriptions') ?>" class="btn btn-block btn-primary" />
                    </div>
                </form>
                <?php do_action('subscriptions_after_register_form') ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer()?>