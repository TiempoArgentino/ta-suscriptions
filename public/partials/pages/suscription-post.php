<?php

/**
 * This template is basic and irrelevant, it is only a guide. You are free to do what you want....
 */
get_header() ?>
<div class="container">
    <div class="row">
        <?php
        // Start the loop.
        while (have_posts()) : the_post();
        ?>
            <div class="col-12 mt-3 mb-5">
                <h2><?php echo get_the_title() ?></h2>
            </div>
            <div class="col-md-6 col-12 text-center">
                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" class="img-fluid img-thumbnail rounded mx-auto d-block" />
            </div>
            <div class="col-md-6 col-12">
                <?php echo get_the_content() ?>
                <div class="d-block">
                    <h4><?php echo __('Price/s', 'subscriptions') ?></h4>
                    <!-- price -->
                    <div class="price-single">
                        <?php echo get_option('subscriptions_currency_symbol') ?> <?php echo get_post_meta(get_the_ID(), '_s_price', true) ?>
                    </div>
                    <!-- price loop -->
                    <?php
                    if (get_post_meta(get_the_ID(), '_prices_extra', true) && count(get_post_meta(get_the_ID(), '_prices_extra', true)) > 0) {
                        foreach (get_post_meta(get_the_ID(), '_prices_extra', true) as $key => $value) { ?>
                            <div class="price-single" data-id="<?php echo get_the_ID() ?>" data-price="<?php echo $value ?>"><?php echo get_option('subscriptions_currency_symbol') ?> <?php echo $value ?></div>
                    <?php    }
                    }
                    ?>
                    <!-- price custom -->
                    <?php
                    if (get_post_meta(get_the_ID(), '_price_custom', true)) { ?>
                        <div class="custom-price "><strong data-id="<?php echo get_the_ID() ?>" class="open-price btn btn-small btn-secondary"><?php echo __('I would like to contribute more money', 'subscriptions') ?></strong>
                            <div class="price-custom-field" id="input<?php echo get_the_ID() ?>"><input type="number" minlength="8" data-id="<?php echo get_the_ID() ?>" class="form-control custom-price-amount" name="custom-price" value="" placeholder="<?php echo get_option('subscriptions_currency_symbol', 'ARS') ?>" /></div>
                        </div>
                    <?php }
                    ?>
                    <p class="d-flex justify-content-between align-items-center buttons-container">
                        <button type="button" class="btn btn-primary btn-block text-uppercase font-weight-bold" disabled id="button<?php echo get_the_ID() ?>" data-id="<?php echo get_the_ID() ?>" data-price="" data-name="<?php echo get_the_title() ?>">
                            <?php echo __('associate', 'subscriptions') ?>
                        </button>
                    </p>
                </div>
            </div>
        <?php
        // End the loop.
        endwhile;
        ?>
    </div>
</div>
<?php get_footer() ?>