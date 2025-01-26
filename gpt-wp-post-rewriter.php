<?php
/**
 * Plugin Name: GPT WP Post Rewriter
 * Description: Calls an AWS Lambda function to update WordPress posts with AI-generated content.
 * Version: 1.0
 * Author: TSQ
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define constants.
define('GPT_WP_POST_REWRITER_PATH', plugin_dir_path(__FILE__));
define('GPT_WP_POST_REWRITER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files.
require_once GPT_WP_POST_REWRITER_PATH . 'admin/admin-page.php';
require_once GPT_WP_POST_REWRITER_PATH . 'includes/cron-jobs.php';
require_once GPT_WP_POST_REWRITER_PATH . 'includes/lambda-integration.php';

// Activation and deactivation hooks.
register_activation_hook(__FILE__, 'gpt_wp_post_rewriter_activate');
register_deactivation_hook(__FILE__, 'gpt_wp_post_rewriter_deactivate');

function gpt_wp_post_rewriter_activate() {
    if (!wp_next_scheduled('gpt_wp_post_rewriter_cron_job')) {
        wp_schedule_event(time(), 'hourly', 'gpt_wp_post_rewriter_cron_job');
    }
}

function gpt_wp_post_rewriter_deactivate() {
    wp_clear_scheduled_hook('gpt_wp_post_rewriter_cron_job');
}
