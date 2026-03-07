<?php

namespace WPNinjaHub\Models;

use WPNinjaHub\Helpers\PluginChecker;

class FluentCrmData
{
    public static function getData(int $userId): array
    {
        if (!PluginChecker::isActive('fluentcrm')) {
            return [];
        }

        global $wpdb;
        $user = get_userdata($userId);
        if (!$user) {
            return [];
        }

        $email = $user->user_email;

        $subscriberTable = $wpdb->prefix . 'fc_subscribers';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$subscriberTable}'") !== $subscriberTable) {
            return [];
        }

        $contact = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, first_name, last_name, email, status, created_at
                 FROM {$subscriberTable} WHERE email = %s LIMIT 1",
                $email
            ),
            ARRAY_A
        );

        if (!$contact) {
            return ['contact' => null, 'activities' => []];
        }

        $activities = [];
        $activityTable = $wpdb->prefix . 'fc_subscriber_notes';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$activityTable}'") === $activityTable) {
            $activities = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, title, description, type, created_at
                     FROM {$activityTable} WHERE subscriber_id = %d ORDER BY created_at DESC LIMIT 50",
                    $contact['id']
                ),
                ARRAY_A
            ) ?: [];
        }

        return [
            'contact'    => $contact,
            'activities' => $activities,
        ];
    }
}
