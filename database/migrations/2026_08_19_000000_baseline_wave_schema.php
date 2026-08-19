<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Baseline del esquema que creaba el paquete Wave.
 *
 * Al retirar wave/ se fueron con él sus 25 migraciones, y con ellas la única
 * forma de reconstruir `users`, `roles`, `plans` o `subscriptions` desde cero:
 * el repo ya no podía levantar la base de datos en una máquina limpia. Así se
 * perdió el backend de api.yammbo.com, y no vuelve a pasar.
 *
 * Es una copia literal del `SHOW CREATE TABLE` de producción (sin
 * AUTO_INCREMENT), no una reinterpretación, para que lo que se cree sea
 * exactamente lo que hay hoy. Sobre bases ya migradas no hace nada: cada tabla
 * se salta si existe.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            DB::statement('CREATE TABLE `users` (   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,   `name` varchar(191) DEFAULT NULL,   `email` varchar(191) NOT NULL,   `avatar` varchar(191) NOT NULL DEFAULT \'demo/default.png\',   `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences`)),   `privacy_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`privacy_settings`)),   `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_links`)),   `email_verified_at` timestamp NULL DEFAULT NULL,   `password` varchar(191) DEFAULT NULL,   `two_factor_secret` text DEFAULT NULL,   `two_factor_recovery_codes` text DEFAULT NULL,   `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,   `remember_token` varchar(100) DEFAULT NULL,   `created_at` timestamp NULL DEFAULT NULL,   `updated_at` timestamp NULL DEFAULT NULL,   `deletion_scheduled_at` timestamp NULL DEFAULT NULL,   `username` varchar(191) NOT NULL,   `trial_ends_at` datetime DEFAULT NULL,   `verification_code` varchar(191) DEFAULT NULL,   `verified` tinyint(4) DEFAULT NULL,   `deleted_at` timestamp NULL DEFAULT NULL,   PRIMARY KEY (`id`),   UNIQUE KEY `users_email_unique` (`email`),   UNIQUE KEY `users_username_unique` (`username`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (! Schema::hasTable('roles')) {
            DB::statement('CREATE TABLE `roles` (   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,   `name` varchar(191) NOT NULL,   `guard_name` varchar(191) NOT NULL,   `created_at` timestamp NULL DEFAULT NULL,   `updated_at` timestamp NULL DEFAULT NULL,   `description` varchar(191) DEFAULT NULL,   PRIMARY KEY (`id`),   UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (! Schema::hasTable('permissions')) {
            DB::statement('CREATE TABLE `permissions` (   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,   `name` varchar(191) NOT NULL,   `guard_name` varchar(191) NOT NULL,   `created_at` timestamp NULL DEFAULT NULL,   `updated_at` timestamp NULL DEFAULT NULL,   PRIMARY KEY (`id`),   UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (! Schema::hasTable('model_has_permissions')) {
            DB::statement('CREATE TABLE `model_has_permissions` (   `permission_id` bigint(20) unsigned NOT NULL,   `model_type` varchar(191) NOT NULL,   `model_id` bigint(20) unsigned NOT NULL,   PRIMARY KEY (`permission_id`,`model_id`,`model_type`),   KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),   CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (! Schema::hasTable('model_has_roles')) {
            DB::statement('CREATE TABLE `model_has_roles` (   `role_id` bigint(20) unsigned NOT NULL,   `model_type` varchar(191) NOT NULL,   `model_id` bigint(20) unsigned NOT NULL,   PRIMARY KEY (`role_id`,`model_id`,`model_type`),   KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),   CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (! Schema::hasTable('role_has_permissions')) {
            DB::statement('CREATE TABLE `role_has_permissions` (   `permission_id` bigint(20) unsigned NOT NULL,   `role_id` bigint(20) unsigned NOT NULL,   PRIMARY KEY (`permission_id`,`role_id`),   KEY `role_has_permissions_role_id_foreign` (`role_id`),   CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,   CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (! Schema::hasTable('plans')) {
            DB::statement('CREATE TABLE `plans` (   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,   `name` varchar(191) NOT NULL,   `description` text DEFAULT NULL,   `features` varchar(191) NOT NULL,   `limits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`limits`)),   `monthly_price_id` varchar(191) DEFAULT NULL,   `yearly_price_id` varchar(191) DEFAULT NULL,   `onetime_price_id` varchar(191) DEFAULT NULL,   `active` tinyint(1) NOT NULL DEFAULT 1,   `role_id` bigint(20) unsigned NOT NULL,   `default` tinyint(1) NOT NULL DEFAULT 0,   `monthly_price` varchar(191) DEFAULT NULL,   `yearly_price` varchar(191) DEFAULT NULL,   `onetime_price` varchar(191) DEFAULT NULL,   `created_at` timestamp NULL DEFAULT NULL,   `updated_at` timestamp NULL DEFAULT NULL,   `sort_order` int(11) NOT NULL DEFAULT 0,   `currency` varchar(3) NOT NULL DEFAULT \'$\',   PRIMARY KEY (`id`),   KEY `plans_role_id_foreign` (`role_id`),   CONSTRAINT `plans_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (! Schema::hasTable('subscriptions')) {
            DB::statement('CREATE TABLE `subscriptions` (   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,   `billable_type` varchar(191) NOT NULL,   `billable_id` bigint(20) unsigned NOT NULL,   `plan_id` int(10) unsigned NOT NULL,   `vendor_slug` varchar(191) NOT NULL,   `vendor_product_id` varchar(191) DEFAULT NULL,   `vendor_transaction_id` varchar(191) DEFAULT NULL,   `vendor_customer_id` varchar(191) DEFAULT NULL,   `vendor_subscription_id` varchar(191) DEFAULT NULL,   `status` varchar(191) NOT NULL,   `cycle` enum(\'month\',\'year\',\'onetime\') NOT NULL DEFAULT \'month\',   `seats` int(11) NOT NULL DEFAULT 1,   `trial_ends_at` timestamp NULL DEFAULT NULL,   `ends_at` timestamp NULL DEFAULT NULL,   `created_at` timestamp NULL DEFAULT NULL,   `updated_at` timestamp NULL DEFAULT NULL,   PRIMARY KEY (`id`),   UNIQUE KEY `subscriptions_vendor_slug_vendor_subscription_id_unique` (`vendor_slug`,`vendor_subscription_id`),   KEY `subscriptions_billable_type_billable_id_index` (`billable_type`,`billable_id`),   KEY `subscriptions_billable_id_billable_type_plan_id_index` (`billable_id`,`billable_type`,`plan_id`),   KEY `subscriptions_plan_id_foreign` (`plan_id`),   CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (! Schema::hasTable('settings')) {
            DB::statement('CREATE TABLE `settings` (   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,   `key` varchar(191) NOT NULL,   `display_name` varchar(191) NOT NULL,   `value` text DEFAULT NULL,   `details` text DEFAULT NULL,   `type` varchar(191) NOT NULL,   `order` int(11) NOT NULL DEFAULT 1,   `group` varchar(191) DEFAULT NULL,   `created_at` timestamp NULL DEFAULT NULL,   `updated_at` timestamp NULL DEFAULT NULL,   PRIMARY KEY (`id`),   UNIQUE KEY `settings_key_unique` (`key`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        if (! Schema::hasTable('activity_logs')) {
            DB::statement('CREATE TABLE `activity_logs` (   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,   `user_id` bigint(20) unsigned NOT NULL,   `action` varchar(191) NOT NULL,   `description` text DEFAULT NULL,   `ip_address` varchar(45) DEFAULT NULL,   `user_agent` text DEFAULT NULL,   `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),   `created_at` timestamp NULL DEFAULT NULL,   `updated_at` timestamp NULL DEFAULT NULL,   PRIMARY KEY (`id`),   KEY `activity_logs_user_id_index` (`user_id`),   KEY `activity_logs_action_index` (`action`),   KEY `activity_logs_created_at_index` (`created_at`),   CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }
    }

    public function down(): void
    {
        // Sin down(): estas tablas guardan las cuentas y las suscripciones.
        // Una reversión automática sería una pérdida de datos silenciosa.
    }
};
