<?php
/**
 * Login 
 * page default
 */
get_header();

do_action('user_login_actions');

$can_register = get_option('users_can_register');
?>
<div class="container">
    <div class="row">
        <div class="col-12" id="form-login-container">
            <?php do_action('subscriptions_before_login_form') ?>
            <h3><?php echo __('Login in your account', 'subscriptions') ?></h3>
            <form method="post" class="suscription-login-form">
                <div class="form-group">
                    <label for=""><?php echo __('Your email', 'subscriptions') ?></label>
                    <input type="email" name="username" id="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : '' ?>" placeholder="<?php echo __('Your email', 'subscriptions') ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label for=""><?php echo __('Your password', 'subscriptions') ?></label>
                    <input type="password" name="password" id="password" value="" placeholder="<?php echo __('Your password', 'subscriptions') ?>" class="form-control">
                </div>
                <input type="hidden" name="redirect_to" value="<?php echo wp_get_referer() && wp_get_referer() !== get_permalink(get_option('subscriptions_login_register_page')) ? wp_get_referer() : get_permalink(get_option('subscriptions_profile')) ?>">
                <button type="submit" name="login" class="btn btn-block btn-primary"><?php echo __('Login', 'subscriptions') ?></button>
            </form>
            <?php do_action('subscriptions_after_login_form') ?>
        </div>
    </div>
</div>

<?php get_footer() ?>