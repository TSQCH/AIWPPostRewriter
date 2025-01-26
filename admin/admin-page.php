<?php

// Add admin menu.
add_action('admin_menu', 'gpt_wp_post_rewriter_add_admin_menu');
function gpt_wp_post_rewriter_add_admin_menu() {
    add_menu_page(
        'GPT WP Post Rewriter Settings',
        'GPT WP Post Rewriter',
        'manage_options',
        'gpt-wp-post-rewriter',
        'gpt_wp_post_rewriter_admin_page'
    );
}

// Admin page content.
function gpt_wp_post_rewriter_admin_page() {
    if ($_POST['gpt_wp_post_rewriter_settings']) {
        $post_range = sanitize_text_field($_POST['post_range']);
        if (preg_match('/^(\d+)-(\d+)$/', $post_range, $matches)) {
            $left = (int)$matches[1];
            $right = (int)$matches[2];

            if ($left > $right) {
                $error_message = "The left value must be smaller than or equal to the right value.";
                // Handle the error (e.g., display a message or prevent saving).
            } else {
                // Validation passed, proceed with saving the value.
            }
        } else {
            $error_message = "Please enter a valid range in the format '1-100'.";
            // Handle the error.
        }

        update_option('gpt_wp_post_rewriter_post_range', sanitize_text_field($_POST['post_range']));
        update_option('gpt_wp_post_rewriter_post_tag', sanitize_text_field($_POST['post_tag']));
        update_option('gpt_wp_post_rewriter_post_type', sanitize_text_field($_POST['post_type']));
        update_option('gpt_wp_post_rewriter_post_category', sanitize_text_field($_POST['post_category']));
        update_option('gpt_wp_post_rewriter_frequency', sanitize_text_field($_POST['frequency']));
        
        echo '<div class="updated"><p>Settings saved!</p></div>';
    }

    $allTags = get_tags();
    $postTags = wp_list_pluck($allTags, 'name', 'term_id');
    $postTypes = get_post_types();
    $categories = get_categories();
    $postCategories = wp_list_pluck($categories, 'name', 'term_id');    
  
    $post_range = get_option('gpt_wp_post_rewriter_post_range', '1-100');
    $post_tag = get_option('gpt_wp_post_rewriter_post_tag', '');
    $post_type = get_option('gpt_wp_post_rewriter_post_type', 'post');
    $post_category = get_option('gpt_wp_post_rewriter_post_category', '');
    $frequency = get_option('gpt_wp_post_rewriter_frequency', 'hourly');

    ?>
    <div class="wrap">
        <h1>GPT WP Post Rewriter Settings</h1>
        <form method="POST">
            <table class="form-table">
                <tr>
                    <th><label for="post_range">Post ID Range</label></th>
                    <td>
                        <input type="text" name="post_range" id="post_range" value="<?php echo esc_attr($post_range); ?>" class="regular-text" 
                            pattern="^\d+-\d+$" title="Enter a valid range in the format '1-100'">
                        <span id="error-message" style="color: red; display: none;">The left value must be smaller than the right value.</span>
                    </td>
                </tr>

                <tr>
                    <th><label for="post_tag">Post Tag</label></th>
                    <td>
                        <select name="post_tag" id="post_tag">
                            <option value="">Please Select</option>
                            <?php foreach($postTags as $id => $pTag): ?>
                                <option value="<?php echo $id ?>" <?php selected($id, $post_tag); ?>> <?php echo $pTag ?> </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><label for="post_type">Post Type</label></th>
                    <td>
                        <select name="post_type" id="post_type">
                            <option value="">Please Select</option>
                            <?php foreach($postTypes as $pType): ?>
                                <option value="<?php echo $pType ?>" <?php selected($pType, $post_type); ?>> <?php echo $pType ?> </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><label for="post_category">Post Category</label></th>
                    <td>
                        <select name="post_category" id="post_category">
                            <option value="">Please Select</option>
                            <?php foreach($postCategories as $id => $pCategory): ?>
                                <option value="<?php echo $id ?>" <?php selected($id, $post_category); ?>> <?php echo $pCategory ?> </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><label for="frequency">Frequency</label></th>
                    <td>
                        <select name="frequency" id="frequency">
                            <option value="hourly" <?php selected($frequency, 'hourly'); ?>>Hourly</option>
                            <option value="twicedaily" <?php selected($frequency, 'twicedaily'); ?>>Twice Daily</option>
                            <option value="daily" <?php selected($frequency, 'daily'); ?>>Daily</option>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="gpt_wp_post_rewriter_settings" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
    <?php
}


function gpt_wp_post_rewriter_enqueue_scripts($hook) {
    // Only load the script on specific admin pages if needed
    // echo $hook."<br>"; exit;
    if (!str_contains($hook, 'gpt-wp-post-rewriter')) {
        return;
    }

    // Register and enqueue the JavaScript file
    wp_enqueue_script(
        'gpt-wp-post-rewriter-validation', // Handle
        plugin_dir_url(__FILE__) . '../js/validation.js', // File URL
        array(), // Dependencies
        '1.0.0', // Version
        true // Load in footer
    );
}
add_action('admin_enqueue_scripts', 'gpt_wp_post_rewriter_enqueue_scripts');

?>