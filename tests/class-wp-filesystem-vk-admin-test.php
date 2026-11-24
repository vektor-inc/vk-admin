<?php
/**
 * テスト用のファイルシステム.
 *
 * @package vk-admin
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- WP_Filesystem() がクラス名を「WP_Filesystem_ + 方式名」で決めるため.

/**
 * 外部ファイルの代わりに、テストで用意した内容を返すファイルシステム.
 *
 * このファイルを読み込んだうえで filesystem_method フィルターで方式名 Vk_Admin_Test を返すと、WP_Filesystem() がこのクラスを使う.
 */
class WP_Filesystem_Vk_Admin_Test extends WP_Filesystem_Base {

	/**
	 * 読み込むファイルのパス・URL と、返す内容の対応.
	 *
	 * @var array<string, string>
	 */
	public static $contents = array();

	/**
	 * テストで用意した内容を返す.
	 *
	 * @param string $file 読み込むファイルのパス・URL.
	 * @return string|false 用意した内容。用意していないときは false.
	 */
	public function get_contents( $file ) {
		return isset( self::$contents[ $file ] ) ? self::$contents[ $file ] : false;
	}
}
// phpcs:enable
