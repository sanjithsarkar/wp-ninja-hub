<?php

namespace WPNinjaHub\Models;

use WPNinjaHub\Helpers\PluginChecker;

class PaymatticData
{
    public static function getData(int $userId): array
    {
        if (!PluginChecker::isActive('paymattic')) {
            return [];
        }

        global $wpdb;
        $user = get_userdata($userId);
        if (!$user) {
            return [];
        }

        $email = $user->user_email;
        $table = $wpdb->prefix . 'wpf_submissions';

        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return [];
        }

        $submissions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, form_id, customer_name, customer_email, payment_total, payment_status,
                        payment_method, currency, payment_mode, created_at
                 FROM {$table} WHERE customer_email = %s ORDER BY created_at DESC LIMIT 50",
                $email
            ),
            ARRAY_A
        );

        $submissionIds = array_column($submissions, 'id');

        $transactions = [];
        $txnTable = $wpdb->prefix . 'wpf_order_transactions';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$txnTable}'") === $txnTable && !empty($submissionIds)) {
            $placeholders = implode(',', array_fill(0, count($submissionIds), '%d'));
            $transactions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, form_id, submission_id, subscription_id, transaction_type,
                            payment_method, card_brand, charge_id, payment_total,
                            status, currency, payment_mode, created_at
                     FROM {$txnTable} WHERE submission_id IN ({$placeholders}) ORDER BY created_at DESC",
                    ...$submissionIds
                ),
                ARRAY_A
            ) ?: [];
        }

        $subscriptions = [];
        $subTable = $wpdb->prefix . 'wpf_subscriptions';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$subTable}'") === $subTable && !empty($submissionIds)) {
            $placeholders = implode(',', array_fill(0, count($submissionIds), '%d'));
            $subscriptions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, submission_id, plan_name, item_name, payment_total,
                            recurring_amount, billing_interval, status, created_at
                     FROM {$subTable} WHERE submission_id IN ({$placeholders}) ORDER BY created_at DESC",
                    ...$submissionIds
                ),
                ARRAY_A
            ) ?: [];
        }

        // Collect unique form IDs and fetch form names
        $formIds = array_unique(array_merge(
            array_column($submissions, 'form_id'),
            array_column($transactions, 'form_id')
        ));
        $formIds = array_filter($formIds);
        $formNames = [];
        if (!empty($formIds)) {
            $placeholders = implode(',', array_fill(0, count($formIds), '%d'));
            $forms = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'wp_payform' AND ID IN ({$placeholders})",
                    ...array_values($formIds)
                ),
                ARRAY_A
            ) ?: [];
            foreach ($forms as $f) {
                $formNames[$f['ID']] = $f['post_title'];
            }
        }

        return [
            'submissions'   => $submissions ?: [],
            'transactions'  => $transactions,
            'subscriptions' => $subscriptions,
            'formNames'     => $formNames,
        ];
    }
}
