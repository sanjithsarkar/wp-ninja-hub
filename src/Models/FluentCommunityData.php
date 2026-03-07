<?php

namespace WPNinjaDashboard\Models;

use WPNinjaDashboard\Helpers\PluginChecker;

class FluentCommunityData
{
    public static function getData(int $userId): array
    {
        if (!PluginChecker::isActive('fluentcommunity')) {
            return [];
        }

        global $wpdb;

        $postsTable = $wpdb->prefix . 'fcom_posts';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$postsTable}'") !== $postsTable) {
            return [];
        }

        $posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, title, message_rendered, status, created_at
                 FROM {$postsTable} WHERE user_id = %d ORDER BY created_at DESC LIMIT 50",
                $userId
            ),
            ARRAY_A
        );

        $commentsTable = $wpdb->prefix . 'fcom_comments';
        $comments = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '{$commentsTable}'") === $commentsTable) {
            $comments = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, post_id, message_rendered, created_at
                     FROM {$commentsTable} WHERE user_id = %d ORDER BY created_at DESC LIMIT 50",
                    $userId
                ),
                ARRAY_A
            ) ?: [];
        }

        return [
            'posts'    => $posts ?: [],
            'comments' => $comments,
        ];
    }
}
