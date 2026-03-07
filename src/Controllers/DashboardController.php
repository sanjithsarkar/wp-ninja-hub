<?php

namespace WPNinjaHub\Controllers;

use WPNinjaHub\Helpers\PluginChecker;
use WPNinjaHub\Models\PaymatticData;
use WPNinjaHub\Models\FluentFormData;
use WPNinjaHub\Models\FluentCrmData;
use WPNinjaHub\Models\FluentBoardData;
use WPNinjaHub\Models\FluentCommunityData;

class DashboardController
{
    public function register(): void
    {
        add_shortcode('wp_ninja_hub', [$this, 'renderShortcode']);
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function renderShortcode(): string
    {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please log in to view the dashboard.', 'wp-ninja-hub') . '</p>';
        }

        $this->enqueueAssets();

        ob_start();
        include WPNINJA_HUB_PATH . 'src/Views/front/dashboard-wrapper.php';
        return ob_get_clean();
    }

    public function registerRoutes(): void
    {
        register_rest_route('wp-ninja-hub/v1', '/menu', [
            'methods'             => 'GET',
            'callback'            => [$this, 'getMenu'],
            'permission_callback' => [$this, 'checkPermission'],
        ]);

        register_rest_route('wp-ninja-hub/v1', '/data/(?P<product>[a-z]+)', [
            'methods'             => 'GET',
            'callback'            => [$this, 'getProductData'],
            'permission_callback' => [$this, 'checkPermission'],
            'args'                => [
                'product' => [
                    'required'          => true,
                    'validate_callback' => function ($param) {
                        return in_array($param, ['paymattic', 'fluentform', 'fluentcrm', 'fluentboard', 'fluentcommunity'], true);
                    },
                ],
            ],
        ]);
    }

    public function checkPermission(): bool
    {
        return is_user_logged_in();
    }

    public function getMenu(): \WP_REST_Response
    {
        return new \WP_REST_Response(PluginChecker::getActivePlugins(), 200);
    }

    public function getProductData(\WP_REST_Request $request): \WP_REST_Response
    {
        $product = $request->get_param('product');
        $userId  = get_current_user_id();

        $models = [
            'paymattic'        => PaymatticData::class,
            'fluentform'       => FluentFormData::class,
            'fluentcrm'        => FluentCrmData::class,
            'fluentboard'      => FluentBoardData::class,
            'fluentcommunity'  => FluentCommunityData::class,
        ];

        if (!isset($models[$product])) {
            return new \WP_REST_Response(['error' => 'Unknown product'], 404);
        }

        $data = $models[$product]::getData($userId);

        return new \WP_REST_Response($data, 200);
    }

    private function isViteDevServer(): bool
    {
        if (defined('WPNINJA_HUB_DEV') && WPNINJA_HUB_DEV) {
            $response = @file_get_contents('http://localhost:5173/@vite/client');
            return $response !== false;
        }
        return false;
    }

    private function enqueueAssets(): void
    {
        $inlineData = 'window.wpNinjaHub = ' . wp_json_encode([
            'restUrl' => esc_url_raw(rest_url('wp-ninja-hub/v1/')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]) . ';';

        if ($this->isViteDevServer()) {
            // Dev mode: load from Vite dev server with HMR
            add_action('wp_head', function () {
                echo '<script type="module" src="http://localhost:5173/@vite/client"></script>';
            });

            wp_register_script('wpninja-hub-js', '', [], WPNINJA_HUB_VERSION, true);
            wp_enqueue_script('wpninja-hub-js');
            wp_add_inline_script('wpninja-hub-js', $inlineData, 'before');

            add_action('wp_footer', function () {
                echo '<script type="module" src="http://localhost:5173/resources/vue/src/main.js"></script>';
            });

            return;
        }

        // Production: load built file as ES module
        $distUrl = WPNINJA_HUB_URL . 'assets/dist/assets/';

        wp_enqueue_style('wpninja-hub-css', $distUrl . 'main.css', [], WPNINJA_HUB_VERSION);

        add_action('wp_head', function () use ($inlineData) {
            echo "<script>{$inlineData}</script>";
        });

        add_action('wp_footer', function () use ($distUrl) {
            echo '<script type="module" src="' . esc_url($distUrl . 'main.js') . '"></script>';
        });
    }
}
