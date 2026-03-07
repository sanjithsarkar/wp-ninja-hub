<?php

namespace WPNinjaHub\Models;

use WPNinjaHub\Helpers\PluginChecker;

class FluentFormData
{
    public static function getData(int $userId): array
    {
        if (!PluginChecker::isActive('fluentform')) {
            return [];
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fluentform_submissions';

        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return [];
        }

        $entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, form_id, status, created_at, updated_at
                 FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT 50",
                $userId
            ),
            ARRAY_A
        );

        return [
            'entries' => $entries ?: [],
        ];
    }
}
