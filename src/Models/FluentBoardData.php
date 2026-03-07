<?php

namespace WPNinjaDashboard\Models;

use WPNinjaDashboard\Helpers\PluginChecker;

class FluentBoardData
{
    public static function getData(int $userId): array
    {
        if (!PluginChecker::isActive('fluentboard')) {
            return [];
        }

        global $wpdb;

        $boardsTable = $wpdb->prefix . 'fbs_boards';
        $tasksTable  = $wpdb->prefix . 'fbs_tasks';

        if ($wpdb->get_var("SHOW TABLES LIKE '{$tasksTable}'") !== $tasksTable) {
            return [];
        }

        $tasks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, board_id, title, status, priority, created_at
                 FROM {$tasksTable} WHERE assigned_to = %d ORDER BY created_at DESC LIMIT 50",
                $userId
            ),
            ARRAY_A
        );

        $boards = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '{$boardsTable}'") === $boardsTable) {
            $boards = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, title, created_at
                     FROM {$boardsTable} WHERE created_by = %d ORDER BY created_at DESC LIMIT 20",
                    $userId
                ),
                ARRAY_A
            ) ?: [];
        }

        return [
            'boards' => $boards,
            'tasks'  => $tasks ?: [],
        ];
    }
}
