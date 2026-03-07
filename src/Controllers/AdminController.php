<?php

namespace WPNinjaDashboard\Controllers;

class AdminController
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu']);
    }

    public function addMenu(): void
    {
        $hook = add_menu_page(
            'WP Ninja Hub',
            'WP Ninja Hub',
            'manage_options',
            'wp-ninja-hub',
            [$this, 'renderPage'],
            'dashicons-layout',
            30
        );

        add_action("admin_enqueue_scripts", function ($pageHook) use ($hook) {
            if ($pageHook !== $hook) {
                return;
            }
            $this->enqueueAdminAssets();
        });
    }

    public function renderPage(): void
    {
        include WPNINJA_HUB_PATH . 'src/Views/admin/shortcode-page.php';
    }

    private function isViteDevServer(): bool
    {
        if (defined('WPNINJA_HUB_DEV') && WPNINJA_HUB_DEV) {
            $response = @file_get_contents('http://localhost:5173/@vite/client');
            return $response !== false;
        }
        return false;
    }

    private function enqueueAdminAssets(): void
    {
        $dashboardUrl = '';
        $page = get_pages([
            'post_status' => 'publish',
            'meta_key'    => '',
            'number'      => 100,
        ]);

        foreach ($page as $p) {
            if (has_shortcode($p->post_content, 'wp_ninja_hub')) {
                $dashboardUrl = get_permalink($p->ID);
                break;
            }
        }

        $inlineData = 'window.wpNinjaHub = ' . wp_json_encode([
            'restUrl'      => esc_url_raw(rest_url('wp-ninja-hub/v1/')),
            'nonce'        => wp_create_nonce('wp_rest'),
            'dashboardUrl' => $dashboardUrl,
        ]) . ';';

        if ($this->isViteDevServer()) {
            add_action('admin_head', function () {
                echo '<script type="module" src="http://localhost:5173/@vite/client"></script>';
            });

            wp_register_script('wpninja-hub-admin-js', '', [], WPNINJA_HUB_VERSION, true);
            wp_enqueue_script('wpninja-hub-admin-js');
            wp_add_inline_script('wpninja-hub-admin-js', $inlineData, 'before');

            add_action('admin_footer', function () {
                echo '<script type="module" src="http://localhost:5173/resources/vue/src/admin/main.js"></script>';
            });

            return;
        }

        $distUrl = WPNINJA_HUB_URL . 'assets/dist/assets/';

        wp_enqueue_style('wpninja-hub-admin-css', $distUrl . 'admin.css', [], WPNINJA_HUB_VERSION);

        add_action('admin_head', function () use ($inlineData) {
            echo "<script>{$inlineData}</script>";
        });

        add_action('admin_footer', function () use ($distUrl) {
            echo '<script type="module" src="' . esc_url($distUrl . 'admin.js') . '"></script>';
        });
    }
}
