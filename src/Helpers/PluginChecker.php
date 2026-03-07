<?php

namespace WPNinjaDashboard\Helpers;

class PluginChecker
{
    private static $plugins = [
        'paymattic' => [
            'slug'  => 'wp-payment-form/wp-payment-form.php',
            'label' => 'Paymattic',
            'class' => 'WPPayForm\\App\\App',
        ],
        'fluentform' => [
            'slug'  => 'fluentform/fluentform.php',
            'label' => 'Fluent Form',
            'class' => 'FluentForm\\App\\App',
        ],
        'fluentcrm' => [
            'slug'  => 'fluent-crm/fluent-crm.php',
            'label' => 'Fluent CRM',
            'class' => 'FluentCrm\\App\\App',
        ],
        'fluentboard' => [
            'slug'  => 'fluent-boards/fluent-boards.php',
            'label' => 'Fluent Board',
            'class' => 'FluentBoards\\App\\App',
        ],
        'fluentcommunity' => [
            'slug'  => 'fluent-community/fluent-community.php',
            'label' => 'Fluent Community',
            'class' => 'FluentCommunity\\App\\App',
        ],
    ];

    public static function isActive(string $key): bool
    {
        if (!isset(self::$plugins[$key])) {
            return false;
        }

        $plugin = self::$plugins[$key];

        if (class_exists($plugin['class'])) {
            return true;
        }

        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active($plugin['slug']);
    }

    public static function getActivePlugins(): array
    {
        $active = [];

        foreach (self::$plugins as $key => $info) {
            if (self::isActive($key)) {
                $active[] = [
                    'slug'  => $key,
                    'label' => $info['label'],
                ];
            }
        }

        return $active;
    }
}
