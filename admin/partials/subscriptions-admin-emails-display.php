<?php do_action('email_actions')?>
<div class="wrap">
    <h1><?php echo __('Subscriptions emails and status settings', 'subscriptions'); ?></h1>
    <hr>
    <div class="status">
        <h3><?php echo __('Status and email', 'subscriptions') ?></h3>
        <?php
            $default_status = ['completed','on-hold','error','cancel','renewal'];
        ?>
        <?php do_action('messages_status_emails') ?>
        <?php foreach(SE()->get_all_status() as $status) :?>
        <div class="edit-status">
            <h4 class="open-form-status" data-id="<?php echo $status->ID?>"><?php echo $status->status_name?> <span><?php echo __('Edit','subscriptions')?></span></h4>
            <form method="post" id="form-edit-status-<?php echo $status->ID?>" class="form-edit-status">
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label><?php echo __('Status Name', 'subscriptions') ?> *</label></th>
                            <td>
                                <input type="text" name="edit_status_name" class="regular-text" value="<?php echo $status->status_name?>" placeholder="<?php echo __('Status Name', 'subscriptions') ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php echo __('Status Color', 'subscriptions') ?> *</label></th>
                            <td>
                                <input type="color" name="edit_status_color" value="<?php echo $status->status_color?>" placeholder="<?php echo __('Status Color', 'subscriptions') ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php echo __('Email Subject', 'subscriptions') ?></label></th>
                            <td>
                                <input type="text" name="edit_email_subject" class="regular-text" value="<?php echo $status->email_subject?>" placeholder="<?php echo __('Email Subject', 'subscriptions') ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php echo __('Email Body', 'subscriptions') ?></label></th>
                            <td>
                                <textarea name="edit_email_body" class="large-text" placeholder="<?php echo __('Email Body', 'subscriptions') ?>"><?php echo $status->email_body?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit">
                <input type="hidden" name="status_slug" value="<?php echo $status->status_slug?>">
                <input type="hidden" name="ID" value="<?php echo $status->ID?>">
                    <input type="submit" value="<?php echo __('Edit Status', 'subscriptions') ?>" class="button button-primary" name="edit_status">
                    <?php if(!in_array($status->status_slug,$default_status)) :?>
                        <input type="submit" value="<?php echo __('DELETE', 'subscriptions') ?>" class="button button-secondary" name="delete_status">
                    <?php endif;?>
                </p>
            </form>
        </div>
        <?php endforeach;?>
    </div>
    <h3><?php echo __('New Status and email', 'subscriptions') ?></h3>
    <form method="post">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label><?php echo __('Status Name', 'subscriptions') ?> *</label></th>
                    <td>
                        <input type="text" name="status_name" class="regular-text" value="" placeholder="<?php echo __('Status Name', 'subscriptions') ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php echo __('Status Color', 'subscriptions') ?> *</label></th>
                    <td>
                        <input type="color" name="status_color" value="" placeholder="<?php echo __('Status Color', 'subscriptions') ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php echo __('Email Subject', 'subscriptions') ?></label></th>
                    <td>
                        <input type="text" name="email_subject" class="regular-text" value="" placeholder="<?php echo __('Email Subject', 'subscriptions') ?>">
                        <?php echo '<p class="tip">
                            '.__('Tags that you can use','subscriptions').'<br />
                            '.__('Name','subscriptions').': {{first_name}} <br />
                            '.__('Last Name','subscriptions').': {{last_name}} <br />
                            '.__('Email','subscriptions').': {{email}} <br />
                            '.__('Name of subscription','subscriptions').': {{subscription_name}}<br />
                        </p>';?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php echo __('Email Body', 'subscriptions') ?></label></th>
                    <td>
                        <textarea name="email_body" class="large-text" placeholder="<?php echo __('Email Body', 'subscriptions') ?>"></textarea>
                        <?php echo '<p class="tip">
                            '.__('Tags that you can use','subscriptions').'<br />
                            '.__('Name','subscriptions').': {{first_name}} <br />
                            '.__('Last Name','subscriptions').': {{last_name}} <br />
                            '.__('Email','subscriptions').': {{email}} <br />
                            '.__('Name of subscription','subscriptions').': {{subscription_name}}<br />
                        </p>';?>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" value="<?php echo __('Add Status', 'subscriptions') ?>" class="button button-primary" name="add_status">
        </p>
    </form>
</div>