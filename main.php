<?php
/**
* Plugin Name: Feed Send
* Description: FeedSend is designed to send articles and slideshows to our servers, facilitating the creation of a dynamic feed. This allows your audience to view your content on multiple sites supporting RSS, such as Microsoft Start.
* Version: 1.2
* Author: PluginPioneers
* Author URI: https://www.pluginpioneers.tech
*/

add_action('add_meta_boxes', 'my_custom_meta_box');

function get_featured_image_url($post_id) {
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        $image_url = wp_get_attachment_image_src($thumbnail_id, 'full');
        return $image_url[0];
    } else {
        return false;
    }
}

function my_custom_meta_box() {
    add_meta_box(
        'my_meta_box_id',          // ID of the meta box
        'MSN Options',             // Title of the meta box
        'my_meta_box_callback',    // Callback function
        'post',                    // Post type where the meta box will appear
        'side',                    // Context where the box will appear ('normal', 'side', 'advanced')
        'default'                  // Priority within the context where the box will show
    );
}


function get_featured_image_caption($post_id) {
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        $caption = wp_get_attachment_caption($thumbnail_id);
        return $caption;
    } else {
        return false;
    }
}


function send_request($post_id, $type, $delete = false) {
    $webhook_url = 'https://api.pluginpioneers.tech/req';
    $post_title = get_the_title($post_id);
    $post_link = get_permalink($post_id);
    $post_content = get_post_field('post_content', $post_id);
    $post = get_post($post_id); // Fetch the post object
    $featured_image = get_featured_image_url($post_id);
    $featured_image_credit = get_featured_image_caption($post_id);
    $author_id = get_post_field('post_author', $post_id); // Get the author ID
    $author_name = get_the_author_meta('display_name', $author_id); // Get the author's display name

	if ($post !== null) {
		if ($post->post_status == 'future') {
			// Fetch the scheduled date in GMT if the post status is 'future'
			$scheduled_date_gmt = get_post_datetime($post_id, 'date', 'gmt');
			if ($scheduled_date_gmt !== false) {
				$gmt_timezone = new DateTimeZone('GMT');
				$scheduled_date_gmt = $scheduled_date_gmt->setTimezone($gmt_timezone);
				$scheduled_date = $scheduled_date_gmt->format('Y-m-d H:i:s T');
			} else {
				$scheduled_date = 'N/a';
			}
		} else {
			// Set scheduled date to 'N/a' if post status is not 'future'
			$scheduled_date = 'N/a';
		}
	} else {
		// Handle cases where the post is not found
		$scheduled_date = 'N/a';
	}

	
    $data = json_encode([
        'content' => [
            'featured_image' => $featured_image,
            'featured_image_credit' => $featured_image_credit,
            'post_title' => $post_title,
            'post_link' => $post_link,
            'post_content' => $post_content,
            'author_name' => $author_name,
            'post_date' => $scheduled_date,
            'type' => $type,
            'GUID' => $post_id,
            'delete' => $delete
        ]
    ]);

    // Set up cURL options
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        // Log error or handle accordingly
        error_log('cURL error: ' . curl_error($ch));
    }

    // Close cURL session
    curl_close($ch);
}

function my_meta_box_callback($post) {
    // Add a nonce field so we can check for it later.
    wp_nonce_field('my_custom_meta_box_nonce', 'my_custom_meta_box_nonce_field');

    // Retrieve previous value from database and check if it's checked.
    $msn_slideshow = get_post_meta($post->ID, '_msn_slideshow', true);
    $msn_basic = get_post_meta($post->ID, '_msn_basic', true);

    // HTML for the meta box
    echo '<label for="msn_slideshow">MSN Slideshow</label> ';
    echo '<input type="checkbox" id="msn_slideshow" name="msn_slideshow" value="yes"' . checked($msn_slideshow, 'yes', false) . '/>';
    echo '<br/>';
    echo '<label for="msn_basic">MSN Basic</label> ';
    echo '<input type="checkbox" id="msn_basic" name="msn_basic" value="yes"' . checked($msn_basic, 'yes', false) . '/>';
}

function save_my_meta_box_data($post_id) {
    // Check if our nonce is set.
    if (!isset($_POST['my_custom_meta_box_nonce_field'])) {
        return;
    }

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['my_custom_meta_box_nonce_field'], 'my_custom_meta_box_nonce')) {
        return;
    }

    // If this is an autosave, our form has not been submitted,
    // so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Update the post meta for MSN Slideshow checkbox
    $msn_slideshow = isset($_POST['msn_slideshow']) ? 'yes' : 'no';
    update_post_meta($post_id, '_msn_slideshow', $msn_slideshow);

    // Update the post meta for MSN Basic checkbox
    $msn_basic = isset($_POST['msn_basic']) ? 'yes' : 'no';
    update_post_meta($post_id, '_msn_basic', $msn_basic);

    if ($msn_slideshow === 'yes') {
        send_request($post_id, 'slideshow');
    }

    if ($msn_slideshow === 'no') {
        send_request($post_id, 'slideshow', true);
    }   

}

add_action('save_post', 'save_my_meta_box_data');
?>
