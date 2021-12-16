<?php

class Subscriptions_Order_Handler
{

    public function __construct()
    {
        
    }
    /**
     * Subscription ID
     */
    public function get_subscription()
    {
        $user_id = wp_get_current_user()->ID;
        $subscription = get_user_meta($user_id, 'suscription', true);
        return $subscription;
    }
    /**
     * Membership
     */
    public function get_membership($id)
    {

        $args = [
            'post_type' => 'memberships',
            'meta_key' => '_member_user_id',
            'meta_value' => $id,
            'fields' => 'ids'
        ];

        $id = get_posts($args);
        /**
         * Membership data
         */
        $period = get_post_meta($id[0], '_member_suscription_period')[0] === 'Months' ? 'Monthly' : 'daily';
        $price = get_post_meta($id[0], '_member_suscription_cost', true);
        $type = get_post_meta($id[0], 'payment_type', true);
        $payment = get_post_meta($id[0],'_member_payment_method',true);
        $status = get_post_meta($id[0],'_member_order_status',true);

        /**
         * Subscription data
         */
        $image = get_the_post_thumbnail_url($this->get_subscription());
        $title = get_the_title($this->get_subscription());
        $physical = get_post_meta($this->get_subscription(), '_physical', true);
        /**
         * Array
         */
        $membership = [
            'id' => $id[0],
            'period' => $period,
            'price' => $price,
            'image' => $image,
            'title' => $title,
            'type' => $type,
            'physical' => $physical,
            'payment' => $payment,
            'status' => $status
        ];
        if($status !== 'cancel') {
            return $membership;
        } else {
            return null;
        }
       
    }

    public function get_price()
    {
        return get_post_meta($this->get_subscription(), '_s_price', true);
    }

    public function extra_price()
    {
        return get_post_meta($this->get_subscription(), '_prices_extra', true);
    }

    public function get_minimun()
    {
        $price_main = get_post_meta($this->get_subscription(), '_s_price', true);
        $prices_extra = get_post_meta($this->get_subscription(), '_prices_extra', true);
        if ($prices_extra) {
            array_push($prices_extra, $price_main);
        }
        $price_min = !$prices_extra ? $price_main : min($prices_extra);

        return $price_min;
    }

    public function get_subscriptions_names($class = null)
    {
        $args = array(
            'post_type' => 'subscriptions',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    [
                        'key' => '_is_donation',
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key' => '_is_donation',
                        'value' => ['1'],
                        'compare' => 'NOT IN'
                    ],
                ],
                [
                    'relation' => 'OR',
                    [
                        'key' => '_is_special',
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key' => '_is_special',
                        'value' => ['1'],
                        'compare' => 'NOT IN'
                    ],
                ]

            ]
        );
        $query = new WP_Query($args);

        $form = '<select id="subs_change" name="subs_change" class="' . $class . '">';
        $form .= '<option value="">' . __('-- select a option --', 'subscriptions') . '</option>';
        if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();

                $physical = get_post_meta(get_the_ID(),'_physical', true);

                $price_main = get_post_meta(get_the_ID(), '_s_price', true);
                $prices_extra = get_post_meta(get_the_ID(), '_prices_extra', true);
                if ($prices_extra) {
                    array_push($prices_extra, $price_main);
                }
                $price_min = !$prices_extra ? $price_main : min($prices_extra);

                $form .= '<option value="' . get_the_ID() . '" data-physical="'.$physical.'" data-min="' . $price_min . '" ' . selected($this->get_subscription(), get_the_ID(), false) . '>' . get_the_title() . '</option>';

            endwhile;
            wp_reset_postdata();
        endif;

        $form .= '</select>';

        echo $form;
    }

    public function get_paper_value()
    {
        $args = array(
            'post_type' => 'subscriptions',
            'posts_per_page' => '1',

            'meta_query' => [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    [
                        'key' => '_is_donation',
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key' => '_is_donation',
                        'value' => ['1'],
                        'compare' => 'NOT IN'
                    ],
                ],
                [
                    'relation' => 'AND',
                    [
                        'key' => '_is_special',
                        'compare' => 'EXISTS'
                    ],
                    [
                        'key' => '_is_special',
                        'value' => ['1'],
                        'compare' => 'IN'
                    ],
                ]
                
            ]
        );
        $query = new WP_Query($args);

        $id_paper = $query->{'posts'}[0]->{'ID'};
        $price_main = get_post_meta($id_paper, '_s_price', true);
        return $price_main;
    }

}

function membership()
{
    return new Subscriptions_Order_Handler();
}

membership();
