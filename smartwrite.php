 <?php
/**
 * Plugin Name: SmartWrite AI
 * Description: SmartWrite AI is a powerful plugin designed to simplify content creation directly from your WordPress dashboard. It also allows automatic internal linking based on user-defined keywords and URLs.
 * Version: 2.0
 * Author: Himanshu Negi
 */

// Enqueue React build files in the admin area
function enqueue_admin_react_app($hook_suffix) {
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
add_action('admin_enqueue_scripts', 'enqueue_admin_react_app');

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
    
    // Add submenu for Internal Linking
    add_submenu_page(
        'smartwrite-ai',             
        'Internal Linking Settings', // Page title
        'Internal Linking',          // Menu title
        'manage_options',            // Capability
        'smartwrite_internal_linking',// Menu slug
        'smartwrite_internal_linking_page' // Callback function
    );
}
add_action('admin_menu', 'smartwrite_admin_menu');

// Function to render the React app in the custom admin page
function smartwrite_admin_page() {   
    echo '<div id="root"></div>';
}

// Internal Linking Settings Page
function smartwrite_internal_linking_page() {
    ?>
    <div class="wrap">
        <h1>Internal Linking Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('smartwrite_linking_settings');
            $keywords_urls = get_option('smartwrite_keywords_urls', array());

            if (!empty($keywords_urls)) {
                foreach ($keywords_urls as $index => $pair) {
                    ?>
                    <p>
                        <input type="text" name="smartwrite_keywords_urls[<?php echo $index; ?>][keyword]" value="<?php echo esc_attr($pair['keyword']); ?>" placeholder="Keyword" />
                        <input type="url" name="smartwrite_keywords_urls[<?php echo $index; ?>][url]" value="<?php echo esc_url($pair['url']); ?>" placeholder="URL" />
                        <a href="#" class="remove-pair">Remove</a>
                    </p>
                    <?php
                }
            } else {
                ?>
                <p>
                    <input type="text" name="smartwrite_keywords_urls[0][keyword]" placeholder="Keyword" />
                    <input type="url" name="smartwrite_keywords_urls[0][url]" placeholder="URL" />
                </p>
                <?php
            }
            ?>
            <div id="extra-pairs"></div>
            <button type="button" id="add-pair">Add another pair</button>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        document.getElementById('add-pair').addEventListener('click', function(e) {
            e.preventDefault();
            var index = document.querySelectorAll('input[name^="smartwrite_keywords_urls"]').length / 2;
            var div = document.createElement('div');
            div.innerHTML = `
                <p>
                    <input type="text" name="smartwrite_keywords_urls[` + index + `][keyword]" placeholder="Keyword" />
                    <input type="url" name="smartwrite_keywords_urls[` + index + `][url]" placeholder="URL" />
                    <a href="#" class="remove-pair">Remove</a>
                </p>
            `;
            document.getElementById('extra-pairs').appendChild(div);
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-pair')) {
                e.preventDefault();
                e.target.parentElement.remove();
            }
        });
    </script>
    <?php
}

// Register settings for Internal Linking
function smartwrite_register_settings() {
    register_setting('smartwrite_linking_settings', 'smartwrite_keywords_urls');
}
add_action('admin_init', 'smartwrite_register_settings');

// Apply internal linking to the content
function smartwrite_apply_internal_linking($content) {
    $keywords_urls = get_option('smartwrite_keywords_urls', array());

    if (!empty($keywords_urls)) {
        foreach ($keywords_urls as $pair) {
            $keyword = esc_html($pair['keyword']);
            $url = esc_url($pair['url']);

            if (!empty($keyword) && !empty($url)) {
                // Replace the first occurrence of the keyword with a link
                $content = preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<a href="' . $url . '">$1</a>', $content, 1);
            }
        }
    }

    return $content;
}
add_filter('the_content', 'smartwrite_apply_internal_linking');

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
add_action('rest_api_init', 'smartwrite_register_rest_route');

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
