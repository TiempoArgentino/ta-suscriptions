<?php
/**
 * This is simple: https://developer.wordpress.org/plugins/
 */
class Subscriptions_Metaboxes {

	public function __construct()
	{
		add_action('add_meta_boxes', [$this, 'add_metabox_suscription']);
		add_action('save_post', [$this, 'save_subscriptions_meta_box_data']);

        
	}


	public function add_metabox_suscription()
    {
        $protected_content = get_option('subscriptions_options_option_name'); //if function is active or not
       
        if(isset($protected_content['use_the_private_content_funtion_0'])) {
            $screens = get_post_types();
            $post_types = array_key_exists('protected_post_type_1',$protected_content) ? $protected_content['protected_post_type_1'] : ['post','page'];
            if (post_type_exists('subscriptions')) {
                if (get_post_type(get_the_ID()) != 'subscriptions') {
                    add_meta_box(
                        'private-post',
                        __('Subscriber content', 'subscriptions'),
                        [$this, 'subscriptions_meta_box_callback'],
                        $post_types,
                        'side',
                        'high'
                    );
                }
            }
        }   
    }
    
	
	public function subscriptions_meta_box_callback($post)
    {

        wp_nonce_field('subscriptions_nonce', 'subscriptions_nonce');

        $values = get_post_custom($post->ID);
        
        $suscription = maybe_unserialize(get_post_meta($post->ID, '_suscription', true));

        $args = [
            'post_type' => 'subscriptions'
        ];

        $allsuscription = query_posts($args);

        if($allsuscription) {
            $private = isset($values['suscription_private']) ? esc_attr($values['suscription_private'][0]) : '';
            $field = '<label> ' . __('Is it content for subscribers only?', 'sucriptions') . ' <input type="checkbox" id="suscription_private" value="on" name="suscription_private" ' . checked($private, 'on', false) . ' /></label>';

            $field .= '<p>' . __('Authorized subscribers', 'subscriptions') . '</p>';
            
            while (have_posts()) : the_post();
                $slug = sanitize_title(get_the_title());
                $id = get_the_ID();

                if (is_array($suscription) && in_array($id, $suscription)) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = null;
                }
                $field .= '<label><input type="checkbox" value="' . $id . '" name="suscription[]" ' . $checked . '  /> ' . get_the_title() . '</label><br />';
            endwhile;
            echo $field;
        } else {
            echo '<label><strong style="color:red">' . __('You must create a subscription first', 'sucriptions') . '</strong></label>';
        }
    }

	public function save_subscriptions_meta_box_data($post_id)
    {

        // Check if our nonce is set.
        if (!isset($_POST['subscriptions_nonce'])) {
            return $post_id;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['subscriptions_nonce'], 'subscriptions_nonce')) {
            return $post_id;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $check = isset($_POST['suscription_private']) && $_POST['suscription_private'] ? 'on' : 'off';
        $suscription = $_POST['suscription'];

        update_post_meta($post_id, 'suscription_private', $check);

        if (!empty($suscription)) {
            update_post_meta($post_id, '_suscription', $_POST['suscription']);
        } else {
            delete_post_meta($post_id, '_suscription');
        }
	}
	
	public function subscriptions_meta_form_new()
    {
        wp_nonce_field('category_termmeta_nonce', 'category_termmeta_nonce');

        //private content
        $field = '<tr class="form-field">';
        $field .= '<th valign="top" scope="row">';
        $field .= ' <label for="catpic">' . _e('Is it content for subscribers only?', 'subscriptions') . '</label>';
        $field .= '</th><td>';
        $field = '<input type="checkbox" id="suscription_private" value="on" name="suscription_private"  />';
        $field .= '</td></tr>';

        //suscription
        $field .= '<p>' . __('Authorized subscribers', 'subscriptions') . '</p>';
        $args = [
            'post_type' => 'subscriptions'
        ];
        query_posts($args);
        while (have_posts()) : the_post();
            $slug = sanitize_title(get_the_title());
            echo '<label><input type="checkbox" value="' . $slug . '" name="suscription[]"  /> ' . the_title() . '</label><br />';
        endwhile;

        echo $field;
    }

	public function subscriptions_meta_form_edit($term)
    {
        $private = get_term_meta($term->term_id, 'suscription_private', true);
        $subscriptions = maybe_unserialize(get_term_meta($term->term_id, 'suscription', true));
        wp_nonce_field('category_termmeta_nonce', 'category_termmeta_nonce');

        $field = '<tr class="form-field">';
        $field .= '<th valign="top" scope="row">';
        $field .= ' <label for="catpic">' . _e('Is it content for subscribers only?', 'subscriptions') . '</label>';
        $field .= '</th><td>';

        $field = '<input type="checkbox" id="suscription_private" value="on" name="suscription_private" ' . checked($private, 'on', false) . ' />';
        $field .= '</td></tr>';

        //suscription
        $field .= '<p>' . __('Authorized subscribers', 'subscriptions') . '</p>';
        $args = [
            'post_type' => 'subscriptions'
        ];
        query_posts($args);
        while (have_posts()) : the_post();
            $slug = sanitize_title(get_the_title());

            if (is_array($subscriptions) && in_array($slug, $subscriptions)) {
                $checked = 'checked="checked"';
            } else {
                $checked = null;
            }

            $field .= '<label><input type="checkbox" value="' . $slug . '" name="suscription[]" ' . $checked . '  /> ' . get_the_title() . '</label><br />';
        endwhile;
        echo $field;
	}
	
	public function subscriptions_meta_save($term_id)
    {
        if (!isset($_POST['category_termmeta_nonce'])) {
            return $term_id;
        }
        $nonce = $_POST['category_termmeta_nonce'];

        $private = get_term_meta($term_id, 'suscription_private', true);
        $subscriptions = maybe_unserialize(get_term_meta($term_id, 'suscription', true));

        $private_save = $_POST['suscription_private'];
        $suscriciones_save = $_POST['suscription'];


        update_term_meta($term_id, 'suscription_private', $private_save, $private);


        if (!empty($suscriciones_save)) {
            update_term_meta($term_id, 'suscription', $suscriciones_save, $subscriptions);
        } else {
            delete_term_meta($term_id, 'suscription');
        }
    }
}

$subscriptions_metaboxes = new Subscriptions_Metaboxes();


