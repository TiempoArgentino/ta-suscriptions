<?php get_header()?>

<?php do_action('subscriptions_password_lost_before')?>
<div class="container">
    <?php if(!is_user_logged_in()): ?>
    <div class="row">
        <div class="col-12 p-5 text-center">
            <h3><?php echo __('Lost password Form', 'subscriptions') ?></h3>
            <?php echo __('Enter your email address and we\'ll send you a link you can use to pick a new password.','subscriptions') ?>
        </div>
        <div class="col-md-6 mx-auto">
            <?php do_action('subscriptions_password_lost_errors')?>
            <form method="post" id="lost-password-form" action="<?php echo wp_lostpassword_url(); ?>">
                <div class="form-group">
                    <label><?php echo __('Enter your email','subscriptions')?></label>
                    <input type="email" name="user_login" value="" id="user_login" class="form-control" required />
                </div>
                <input type="submit" name="send-password" class="btn btn-lg btn-block btn-primary" value="<?php echo __( 'Reset Password', 'personalize-login' ); ?>" />
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="row">
    <div class="col-12 p-5 text-center">
            <h3><?php echo sprintf(__('Hi %s, you are already logged in.', 'subscriptions'),wp_get_current_user()->first_name) ?></h3>
        </div>
    </div>
<?php endif; ?>
</div>
<?php do_action('subscriptions_password_lost_after')?>

<?php get_footer()?>