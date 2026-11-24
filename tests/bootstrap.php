<?php
/**
 * PHPUnit のブートストラップ.
 *
 * @package vk-admin
 */

// Composer の自動読み込み（VkAdmin クラスと PHPUnit Polyfills）を読み込む.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// WordPress のテストライブラリの場所を決める（wp-env の tests-cli では WP_TESTS_DIR が設定されている）.
$vk_admin_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $vk_admin_tests_dir ) {
	$vk_admin_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
}

// WordPress のテスト環境を起動する.
require $vk_admin_tests_dir . '/includes/bootstrap.php';
