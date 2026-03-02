<?php
/*
Plugin Name: OpenTT Smart Redirect
Description: A simple way to enable redirect mode and send all users (except admins) to a selected page.
Version: 1.3
Author: Aleksa Dimitrijević
*/

if ( !defined( 'ABSPATH' ) ) exit;

define('OPENTT_SMART_REDIRECT_MODE_OPTION', 'opentt_smart_redirect_mode');
define('OPENTT_SMART_REDIRECT_PAGE_OPTION', 'opentt_smart_redirect_page_id');
define('OPENTT_SMART_REDIRECT_SUBPAGES_OPTION', 'opentt_smart_redirect_allow_subpages');

// Add admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'OpenTT Smart Redirect',
        'OpenTT Smart Redirect',
        'manage_options',
        'opentt-smart-redirect',
        'opentt_smart_redirect_settings_page',
        'dashicons-admin-generic'
    );
});

// Create /maintenance page if it doesn't exist
register_activation_hook(__FILE__, function() {
    $maintenance_page = get_page_by_path('maintenance');

    if (!$maintenance_page) {
        $page_id = wp_insert_post([
            'post_title'   => 'Maintenance',
            'post_name'    => 'maintenance',
            'post_content' => '<h2>The website is currently under maintenance</h2><p>Please check back soon.</p>',
            'post_status'  => 'publish',
            'post_type'    => 'page'
        ]);

        if (!is_wp_error($page_id) && $page_id) {
            update_option(OPENTT_SMART_REDIRECT_PAGE_OPTION, (int) $page_id);
        }
    } else {
        update_option(OPENTT_SMART_REDIRECT_PAGE_OPTION, (int) $maintenance_page->ID);
    }

    if (get_option(OPENTT_SMART_REDIRECT_SUBPAGES_OPTION, null) === null) {
        add_option(OPENTT_SMART_REDIRECT_SUBPAGES_OPTION, 0);
    }
});

// Render admin page
function opentt_smart_redirect_settings_page() {
    if (isset($_POST['opentt_smart_toggle']) && check_admin_referer('opentt_smart_toggle_action')) {
        $enabled = get_option(OPENTT_SMART_REDIRECT_MODE_OPTION) ? 0 : 1;
        update_option(OPENTT_SMART_REDIRECT_MODE_OPTION, $enabled);
    }

    if (isset($_POST['opentt_smart_save_page']) && check_admin_referer('opentt_smart_save_page_action')) {
        $page_id = isset($_POST['opentt_smart_redirect_page_id']) ? absint($_POST['opentt_smart_redirect_page_id']) : 0;
        $allow_subpages = isset($_POST['opentt_smart_allow_subpages']) ? 1 : 0;
        update_option(OPENTT_SMART_REDIRECT_PAGE_OPTION, $page_id);
        update_option(OPENTT_SMART_REDIRECT_SUBPAGES_OPTION, $allow_subpages);
    }

    $is_enabled       = (int) get_option(OPENTT_SMART_REDIRECT_MODE_OPTION, 0);
    $selected_page_id = (int) get_option(OPENTT_SMART_REDIRECT_PAGE_OPTION, 0);
    $allow_subpages   = (int) get_option(OPENTT_SMART_REDIRECT_SUBPAGES_OPTION, 0);
    $pages            = get_pages([
        'sort_column' => 'post_title',
        'sort_order'  => 'asc',
    ]);
    ?>
    <div class="wrap">
        <h1>OpenTT Smart Redirect</h1>
        <p>Status: <strong><?php echo $is_enabled ? 'ENABLED' : 'DISABLED'; ?></strong></p>
        <form method="post">
            <?php wp_nonce_field('opentt_smart_toggle_action'); ?>
            <input type="submit" name="opentt_smart_toggle" class="button button-primary" value="<?php echo $is_enabled ? 'Disable' : 'Enable'; ?> Redirect Mode">
        </form>

        <hr>

        <h2>Target Redirect Page</h2>
        <form method="post">
            <?php wp_nonce_field('opentt_smart_save_page_action'); ?>
            <select name="opentt_smart_redirect_page_id" style="min-width: 280px;">
                <option value="0">-- Select Page --</option>
                <?php foreach ($pages as $page): ?>
                    <option value="<?php echo (int) $page->ID; ?>" <?php selected($selected_page_id, (int) $page->ID); ?>>
                        <?php echo esc_html($page->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p style="margin: 12px 0;">
                <label style="display: inline-flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="opentt_smart_allow_subpages" value="1" <?php checked($allow_subpages, 1); ?>>
                    Allow subpages of the selected parent page
                </label>
            </p>
            <input type="submit" name="opentt_smart_save_page" class="button button-secondary" value="Save Settings">
        </form>
    </div>
    <?php
}

// Redirect all users except admins
add_action('template_redirect', function() {
    if (is_user_logged_in() && current_user_can('manage_options')) return;

    if (get_option(OPENTT_SMART_REDIRECT_MODE_OPTION)) {
        $redirect_page_id = (int) get_option(OPENTT_SMART_REDIRECT_PAGE_OPTION, 0);
        $allow_subpages   = (int) get_option(OPENTT_SMART_REDIRECT_SUBPAGES_OPTION, 0);

        if (!$redirect_page_id) {
            $maintenance_page = get_page_by_path('maintenance');
            if ($maintenance_page) {
                $redirect_page_id = (int) $maintenance_page->ID;
            }
        }

        $redirect_url = $redirect_page_id ? get_permalink($redirect_page_id) : '';
        $is_allowed_target = false;

        if ($redirect_page_id) {
            $is_allowed_target = is_page($redirect_page_id);

            if (!$is_allowed_target && $allow_subpages && is_page()) {
                $current_page_id = (int) get_queried_object_id();
                if ($current_page_id) {
                    $ancestors = get_post_ancestors($current_page_id);
                    $is_allowed_target = in_array($redirect_page_id, $ancestors, true);
                }
            }
        }

        if ($redirect_page_id && $redirect_url && !$is_allowed_target) {
            wp_redirect($redirect_url);
            exit;
        }
    }
});
