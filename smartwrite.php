<?php
/**
 * Plugin Name: SmartWrite AI
 * Description: SmartWrite AI is a powerful plugin designed to simplify content creation directly from your WordPress dashboard. It allows you to quickly generate blog posts and drafts using AI-generated content, perfect for writers and bloggers looking to speed up their workflow. With an intuitive React-based interface, SmartWrite AI integrates seamlessly into the WordPress admin, enabling you to generate ideas, create posts, and publish them with ease.
 * Version: 1.0
 * Author: Himanshu Negi
 */

// Enqueue React build files in the admin area
function enqueue_admin_react_app($hook_suffix) {
    // Check if the current admin page is the SmartWrite AI admin page
    if ($hook_suffix !== 'toplevel_page_smartwrite-ai') {
        return;
    }

    // Enqueue the main React app JavaScript file
    wp_enqueue_script(
        'smartwrite-react-app-js',
        plugins_url('build/assets/index.js', __FILE__), 
        array(), 
        null, 
        true 
    );

    // Enqueue the main React app CSS file
    wp_enqueue_style(
        'smartwrite-react-app-css',
        plugins_url('build/assets/index.css', __FILE__), 
        array(),
        null 
    );

    // Localize script to pass the REST API URL and nonce to the React app
    wp_localize_script(
        'smartwrite-react-app-js', 
        'smartwrite_ai_vars', 
        array(
            'rest_url' => esc_url_raw(rest_url()),  
            'nonce'    => wp_create_nonce('wp_rest'), 
        )
    );
}

// Add a custom menu to the WordPress admin sidebar for SmartWrite AI
function smartwrite_admin_menu() {
    add_menu_page(
        'SmartWrite AI',            
        'SmartWrite AI',            
        'manage_options',           
        'smartwrite-ai',            
        'smartwrite_admin_page',   
        'dashicons-welcome-write-blog', 
        6                           
    );
}

// Function to render the React app in the custom admin page
function smartwrite_admin_page() {   
    echo '<div id="root"></div>';
}

// Register the custom REST API endpoint for creating posts
function smartwrite_register_rest_route() {
    register_rest_route('smartwrite-ai/v1', '/create-post', array(
        'methods' => 'POST',
        'callback' => 'smartwrite_create_post',
        'permission_callback' => function () {
            return current_user_can('edit_posts'); 
        },
    ));
}

// Callback function for the REST API to create a new post
function smartwrite_create_post(WP_REST_Request $request) {
    // Get the post data from the request
    $params = $request->get_json_params();
    $title = sanitize_text_field($params['title']);
    $content = wp_kses_post($params['content']);

    // Validate title and content
    if (empty($title) || empty($content)) {
        return new WP_Error('invalid_data', 'Title and content are required.', array('status' => 400));
    }

    // Insert the new post into the WordPress database
    $post_id = wp_insert_post(array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'draft',  
        'post_author'  => get_current_user_id(),
    ));

    if (is_wp_error($post_id)) {
        return new WP_Error('post_creation_failed', 'Failed to create post.', array('status' => 500));
    }

    // Return a response with the new post ID
    return rest_ensure_response(array(
        'post_id' => $post_id,
        'message' => 'Post created successfully!',
    ));
}

// Hook to register the REST API route when the REST API is initialized
add_action('rest_api_init', 'smartwrite_register_rest_route');

// Hook to enqueue the React app scripts and styles
add_action('admin_enqueue_scripts', 'enqueue_admin_react_app');

// Hook to add the custom SmartWrite AI menu
add_action('admin_menu', 'smartwrite_admin_menu');
