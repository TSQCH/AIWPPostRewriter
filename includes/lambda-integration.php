<?php

function gpt_wp_post_rewriter_lambda_function($payload) {
    $url = 'https://uhpcfachegzui2dszfn4unhmva0jxsjn.lambda-url.us-east-1.on.aws/';
    $args = [
        'body' => wp_json_encode($payload),
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'method' => 'POST',
        'timeout' => 900, // Set timeout to 60 seconds or as needed
    ];

    $response = wp_remote_post($url, $args);
    // echo "Actual res ==== <pre>"; print_r($response); echo "</pre>";

    if (is_wp_error($response)) {
        error_log('Lambda API Error: ' . $response->get_error_message());
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}


// Add Meta Box for Old Post Content
function gpt_wp_post_rewriter_add_old_post_content_meta_box() {
    add_meta_box(
        'old_post_content_meta_box', // Meta box ID
        'Old Post Content',          // Title of the meta box
        'gpt_wp_post_rewriter_render_old_post_content_meta_box', // Callback function
        'post',                      // Post type where the meta box will appear
        'normal',                    // Context (normal, side, or advanced)
        'default'                    // Priority
    );
}
add_action('add_meta_boxes', 'gpt_wp_post_rewriter_add_old_post_content_meta_box');

// Render the Meta Box Content
function gpt_wp_post_rewriter_render_old_post_content_meta_box($post) {
    // Get the old_post_content meta value
    $old_post_content = get_post_meta($post->ID, 'old_post_content', true);

    if ($old_post_content) {
        // Remove HTML tags
        $sanitized_content = wp_strip_all_tags($old_post_content);

        // Display the sanitized content in a read-only textarea
        echo '<textarea style="width: 100%; height: 150px;" readonly>' . esc_textarea($sanitized_content) . '</textarea>';
    } else {
        echo '<p>No old content available.</p>';
    }
}

add_action('init', function() {
    if (isset($_GET['run_my_cron']) && $_GET['run_my_cron'] === '1') {
        do_action('gpt_wp_post_rewriter_cron_job');
        echo "Cron job manually triggered.";
        exit;
    }

    if (isset($_GET['list_my_cron']) && $_GET['list_my_cron'] === '1') {
        // The hook name of your cron job
        $hook_name = 'gpt_wp_post_rewriter_cron_job';

        // Get the next scheduled timestamp
        $next_run = wp_next_scheduled($hook_name);

        if ($next_run) {
            echo "The next scheduled run for '$hook_name' is: " . date('Y-m-d H:i:s', $next_run);
        } else {
            echo "No scheduled event found for '$hook_name'.";
        }
        exit;
    }
});


function add_post_id_column($columns) {
    $columns['post_id'] = 'Post ID';
    return $columns;
}
add_filter('manage_posts_columns', 'add_post_id_column');

function show_post_id_in_column($column, $post_id) {
    if ($column == 'post_id') {
        echo $post_id;
    }
}
add_action('manage_posts_custom_column', 'show_post_id_in_column', 10, 2);