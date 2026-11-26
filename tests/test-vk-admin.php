<?php
/**
 * VkAdmin のテスト.
 *
 * @package vk-admin
 */

use VektorInc\VK_Admin\VkAdmin;

/**
 * VkAdmin のテストクラス.
 */
class VkAdminTest extends WP_UnitTestCase {

	/**
	 * テスト前のファイルシステム（WP_Filesystem() が設定するグローバル変数 $wp_filesystem）.
	 *
	 * @var WP_Filesystem_Base|null
	 */
	private $wp_filesystem_backup;

	/**
	 * テスト用のファイルシステムに差し替える前の $wp_filesystem を退避する.
	 */
	public function set_up() {
		parent::set_up();
		$this->wp_filesystem_backup = isset( $GLOBALS['wp_filesystem'] ) ? $GLOBALS['wp_filesystem'] : null;
	}

	/**
	 * 退避した $wp_filesystem を戻し、テスト用のファイルシステムに用意した内容を消す.
	 */
	public function tear_down() {
		$GLOBALS['wp_filesystem'] = $this->wp_filesystem_backup;
		if ( class_exists( 'WP_Filesystem_Vk_Admin_Test', false ) ) {
			WP_Filesystem_Vk_Admin_Test::$contents = array();
		}
		parent::tear_down();
	}

	/**
	 * バナー get_admin_banner() が、外部の JSON から取得したリンク先・画像の URL・代替テキストをエスケープして出力するかと、末尾のロゴが 2026 年版の画像を指すかを確かめる.
	 */
	public function test_get_admin_banner() {
		// 外部の JSON の代わりに、テストで用意した内容を返すファイルシステムを使う.
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once __DIR__ . '/class-wp-filesystem-vk-admin-test.php';
		add_filter(
			'filesystem_method',
			/**
			 * テスト用のファイルシステムの方式名を返す.
			 *
			 * @return string 方式名.
			 */
			function () {
				return 'Vk_Admin_Test';
			}
		);

		$admin_url = admin_url();

		$test_cases = array(
			array(
				'test_condition_name' => '英語のサイトで、外部サイトへのリンクのプラグインのバナー => そのまま出力される',
				'conditions'          => array(
					'locale'  => 'en_US',
					'product' => array(
						'type'       => 'plugin',
						'slug'       => 'vk-admin-test-plugin/vk-admin-test-plugin.php',
						'image_file' => 'ad_banner-vk-filter-search-pro-en.jpg',
						'link_url'   => 'https://vk-filter-search.com/?rel=vkadmin',
						'alt'        => 'VK Filter Search Pro Global Edition',
						'language'   => 'en',
						'admin_url'  => false,
					),
				),
				'expected'            => '<a href="https://vk-filter-search.com/?rel=vkadmin" target="_blank" class="admin_banner"><img src="https://raw.githubusercontent.com/vektor-inc/vk-admin-banners/main/images/ad_banner-vk-filter-search-pro-en.jpg" alt="VK Filter Search Pro Global Edition" /></a>',
			),
			array(
				'test_condition_name' => '英語のサイトで、外部サイトへのリンクのテーマのバナー => そのまま出力される',
				'conditions'          => array(
					'locale'  => 'en_US',
					'product' => array(
						'type'       => 'theme',
						'slug'       => 'vk-admin-test-theme/style.css',
						'image_file' => 'lightning_bnr_en.jpg',
						'link_url'   => 'https://lightning.nagoya/?rel=vkadmin',
						'alt'        => 'Lightning',
						'language'   => 'en',
						'admin_url'  => false,
					),
				),
				'expected'            => '<a href="https://lightning.nagoya/?rel=vkadmin" target="_blank" class="admin_banner"><img src="https://raw.githubusercontent.com/vektor-inc/vk-admin-banners/main/images/lightning_bnr_en.jpg" alt="Lightning" /></a>',
			),
			array(
				'test_condition_name' => '日本語のサイトで、代替テキストが日本語のプラグインのバナー => そのまま出力される',
				'conditions'          => array(
					'locale'  => 'ja',
					'product' => array(
						'type'       => 'plugin',
						'slug'       => 'vk-admin-test-plugin/vk-admin-test-plugin.php',
						'image_file' => 'ad_banner-vektor-passport-ja.jpg',
						'link_url'   => 'https://vws.vektor-inc.co.jp/vektor-passport?rel=vkadmin',
						'alt'        => 'ベクトルパスポート',
						'language'   => 'ja',
						'admin_url'  => false,
					),
				),
				'expected'            => '<a href="https://vws.vektor-inc.co.jp/vektor-passport?rel=vkadmin" target="_blank" class="admin_banner"><img src="https://raw.githubusercontent.com/vektor-inc/vk-admin-banners/main/images/ad_banner-vektor-passport-ja.jpg" alt="ベクトルパスポート" /></a>',
			),
			array(
				'test_condition_name' => '管理画面のプラグイン検索へのリンク（& で複数のパラメーターをつなぐ） => & が文字参照 &#038; で出力される',
				'conditions'          => array(
					'locale'  => 'en_US',
					'product' => array(
						'type'       => 'plugin',
						'slug'       => 'vk-admin-test-plugin/vk-admin-test-plugin.php',
						'image_file' => 'vk-block-patterns_bnr_en.jpg',
						'link_url'   => 'plugin-install.php?s=vk+block+patterns&tab=search&type=term',
						'alt'        => 'VK Block Patterns',
						'language'   => 'en',
						'admin_url'  => true,
					),
				),
				'expected'            => '<a href="' . $admin_url . 'plugin-install.php?s=vk+block+patterns&#038;tab=search&#038;type=term" target="_blank" class="admin_banner"><img src="https://raw.githubusercontent.com/vektor-inc/vk-admin-banners/main/images/vk-block-patterns_bnr_en.jpg" alt="VK Block Patterns" /></a>',
			),
			array(
				'test_condition_name' => '代替テキストに & を含む => & が文字参照 &amp; で出力される',
				'conditions'          => array(
					'locale'  => 'en_US',
					'product' => array(
						'type'       => 'plugin',
						'slug'       => 'vk-admin-test-plugin/vk-admin-test-plugin.php',
						'image_file' => 'vk-blocks_bnr_en.jpg',
						'link_url'   => 'https://vk-blocks.com/?rel=vkadmin',
						'alt'        => 'VK Blocks & VK Blocks Pro',
						'language'   => 'en',
						'admin_url'  => false,
					),
				),
				'expected'            => '<a href="https://vk-blocks.com/?rel=vkadmin" target="_blank" class="admin_banner"><img src="https://raw.githubusercontent.com/vektor-inc/vk-admin-banners/main/images/vk-blocks_bnr_en.jpg" alt="VK Blocks &amp; VK Blocks Pro" /></a>',
			),
			array(
				'test_condition_name' => 'javascript: のリンク・引用符を含む画像ファイル名・タグを含む代替テキスト => リンクは空になり、画像の URL と代替テキストは属性の外に出ない',
				'conditions'          => array(
					'locale'  => 'en_US',
					'product' => array(
						'type'       => 'plugin',
						'slug'       => 'vk-admin-test-plugin/vk-admin-test-plugin.php',
						'image_file' => '" onerror="alert(2)',
						'link_url'   => 'javascript:alert(1)',
						'alt'        => '"><script>alert(3)</script>',
						'language'   => 'en',
						'admin_url'  => false,
					),
				),
				'expected'            => '<a href="" target="_blank" class="admin_banner"><img src="https://raw.githubusercontent.com/vektor-inc/vk-admin-banners/main/images/%20onerror=alert(2)" alt="&quot;&gt;&lt;script&gt;alert(3)&lt;/script&gt;" /></a>',
			),
			array(
				'test_condition_name' => 'テーマのバナーで、リンク先が文字列でない（配列） => 読み飛ばされ、何も出力されない',
				'conditions'          => array(
					'locale'  => 'en_US',
					'product' => array(
						'type'       => 'theme',
						'slug'       => 'vk-admin-test-theme/style.css',
						'image_file' => 'lightning_bnr_en.jpg',
						'link_url'   => array( 'https://lightning.nagoya/?rel=vkadmin' ),
						'alt'        => 'Lightning',
						'language'   => 'en',
						'admin_url'  => false,
					),
				),
				'expected'            => '',
			),
			array(
				'test_condition_name' => 'プラグインのバナーで、画像ファイル名が文字列でない（null） => 読み飛ばされ、何も出力されない',
				'conditions'          => array(
					'locale'  => 'en_US',
					'product' => array(
						'type'       => 'plugin',
						'slug'       => 'vk-admin-test-plugin/vk-admin-test-plugin.php',
						'image_file' => null,
						'link_url'   => 'https://vk-filter-search.com/?rel=vkadmin',
						'alt'        => 'VK Filter Search Pro Global Edition',
						'language'   => 'en',
						'admin_url'  => false,
					),
				),
				'expected'            => '',
			),
			array(
				'test_condition_name' => 'プラグインのバナーで、代替テキストが文字列でない（数値） => 読み飛ばされ、何も出力されない',
				'conditions'          => array(
					'locale'  => 'en_US',
					'product' => array(
						'type'       => 'plugin',
						'slug'       => 'vk-admin-test-plugin/vk-admin-test-plugin.php',
						'image_file' => 'ad_banner-vk-filter-search-pro-en.jpg',
						'link_url'   => 'https://vk-filter-search.com/?rel=vkadmin',
						'alt'        => 123,
						'language'   => 'en',
						'admin_url'  => false,
					),
				),
				'expected'            => '',
			),
		);

		// 外部の JSON の内容にかかわらず、バナーの末尾に出すロゴは 2026 年版の画像を指す.
		$expected_logo = '<img src="https://raw.githubusercontent.com/vektor-inc/vk-admin-banners/main/images/vektor_logo-2026.png" alt="Vektor,Inc." />';

		foreach ( $test_cases as $case ) {
			// サイトの言語を設定する.
			/**
			 * このケースのサイトの言語を返す.
			 *
			 * @return string サイトの言語.
			 */
			$locale_filter = function () use ( $case ) {
				return $case['conditions']['locale'];
			};
			add_filter( 'locale', $locale_filter );

			// 外部の JSON の内容を、バナー 1 件だけの一覧にする.
			WP_Filesystem_Vk_Admin_Test::$contents = array(
				'https://raw.githubusercontent.com/vektor-inc/vk-admin-banners/main/vk-admin-banners.json' => wp_json_encode( array( $case['conditions']['product'] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
			);

			$banner_html = VkAdmin::get_admin_banner();

			// バナーの HTML から、バナーを並べる部分を取り出す.
			preg_match( '#<div class="vk-admin-banner-grid">(.*?)</div>#s', $banner_html, $matches );
			$actual = isset( $matches[1] ) ? $matches[1] : null;

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );

			// バナーの HTML から、末尾のロゴの画像を取り出す.
			preg_match( '#<a [^>]*class="vektor_logo"[^>]*>(<img [^>]*/>)</a>#', $banner_html, $logo_matches );
			$actual_logo = isset( $logo_matches[1] ) ? $logo_matches[1] : null;

			$this->assertSame( $expected_logo, $actual_logo, '[ロゴ] ' . $case['test_condition_name'] );

			remove_filter( 'locale', $locale_filter );
		}
	}

	/**
	 * 記事一覧の JavaScript get_posts_from_rest_api_js() が、記事を取得する URL と追加先のリストのセレクターを JavaScript の文字列として出力するかを確かめる.
	 */
	public function test_get_posts_from_rest_api_js() {
		$test_cases = array(
			array(
				'test_condition_name' => 'お知らせの REST API の URL => 同じ URL とセレクターが JavaScript の文字列として出力される',
				'conditions'          => array(
					'url'       => 'https://vektor-inc.co.jp/wp-json/wp/v2/info/?per_page=3',
					'target_id' => 'vk-wp-info',
				),
				'expected'            => array(
					'url'      => '"https:\/\/vektor-inc.co.jp\/wp-json\/wp\/v2\/info\/?per_page=3"',
					'selector' => '"ul#vk-wp-info"',
				),
			),
			array(
				'test_condition_name' => '& で複数のパラメーターをつなぐブログの REST API の URL => & のまま出力される',
				'conditions'          => array(
					'url'       => 'https://www.vektor-inc.co.jp/wp-json/wp/v2/posts/?categories=55&per_page=3',
					'target_id' => 'vk-wp-blog',
				),
				'expected'            => array(
					'url'      => '"https:\/\/www.vektor-inc.co.jp\/wp-json\/wp\/v2\/posts\/?categories=55&per_page=3"',
					'selector' => '"ul#vk-wp-blog"',
				),
			),
			array(
				'test_condition_name' => '引用符と script の終了タグを含む URL => 引用符・山括弧が除かれ、文字列の外に出ない',
				'conditions'          => array(
					'url'       => 'https://example.com/?a=");alert(1);//</script>',
					'target_id' => 'vk-wp-info',
				),
				'expected'            => array(
					'url'      => '"https:\/\/example.com\/?a=);alert(1):\/\/\/script"',
					'selector' => '"ul#vk-wp-info"',
				),
			),
			array(
				'test_condition_name' => 'javascript: の URL => 空の文字列が出力される',
				'conditions'          => array(
					'url'       => 'javascript:alert(2)',
					'target_id' => 'vk-wp-info',
				),
				'expected'            => array(
					'url'      => '""',
					'selector' => '"ul#vk-wp-info"',
				),
			),
			array(
				'test_condition_name' => '引用符と script の終了タグを含むリストの id => 引用符と / がエスケープされ、文字列の外に出ない',
				'conditions'          => array(
					'url'       => 'https://vektor-inc.co.jp/wp-json/wp/v2/info/?per_page=3',
					'target_id' => 'vk-wp-info");alert(3);</script>',
				),
				'expected'            => array(
					'url'      => '"https:\/\/vektor-inc.co.jp\/wp-json\/wp\/v2\/info\/?per_page=3"',
					'selector' => '"ul#vk-wp-info\");alert(3);<\/script>"',
				),
			),
		);

		foreach ( $test_cases as $case ) {
			// JavaScript を出力して取り込む.
			ob_start();
			VkAdmin::get_posts_from_rest_api_js( $case['conditions']['url'], $case['conditions']['target_id'] );
			$js = ob_get_clean();

			// $.getJSON() に渡している URL の部分と、記事を追加するリストのセレクターの部分を取り出す.
			preg_match( '#\$\.getJSON\(\s*(.*?),\s*\n#', $js, $url_matches );
			preg_match( '#\$\((.*?)\)\.append\( \$listItem \);#', $js, $selector_matches );
			$actual = array(
				'url'      => isset( $url_matches[1] ) ? $url_matches[1] : null,
				'selector' => isset( $selector_matches[1] ) ? $selector_matches[1] : null,
			);

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}

	/**
	 * 共通フレーム admin_page_frame() が、ロゴ・左メニューの正当なマークアップを残し、危険なタグ・属性を除いて出力するかを確かめる.
	 */
	public function test_admin_page_frame() {
		$test_cases = array(
			array(
				'test_condition_name' => 'column_3 で ExUnit のロゴ（div・img）と VK Blocks Pro のメニュー（li・a）を渡す => そのまま出力される',
				'conditions'          => array(
					'logo_html' => '<div class="logo_exUnit"><img src="https://example.com/wp-content/plugins/vk-all-in-one-expansion-unit/assets/images/head_logo_ExUnit.png" alt="VK ExUnit" /></div>',
					'menu_html' => '<li><a href="#license-setting">License Key</a></li><li><a href="#balloon-setting">Balloon Block Setting</a></li>',
					'layout'    => 'column_3',
				),
				'expected'            => array(
					'logo' => '<div class="logo_exUnit"><img src="https://example.com/wp-content/plugins/vk-all-in-one-expansion-unit/assets/images/head_logo_ExUnit.png" alt="VK ExUnit" /></div>',
					'menu' => '<li><a href="#license-setting">License Key</a></li><li><a href="#balloon-setting">Balloon Block Setting</a></li>',
				),
			),
			array(
				'test_condition_name' => 'column_3 で img だけのロゴと、target 付きのリンク・id と class 付きの li のメニューを渡す => そのまま出力される',
				'conditions'          => array(
					'logo_html' => '<img src="https://example.com/wp-content/plugins/vk-blocks-pro/inc/vk-blocks/admin//images/vk-blocks-logo_ol.svg" alt="VK Blocks" />',
					'menu_html' => '<li><a href="https://example.com/wp-admin/profile.php" target="_blank">Edit your profile</a></li><li id="btn_vkExUnit_ga_options" class="vkExUnit_ga_options"><a href="#vkExUnit_ga_options">Google Analytics Settings</a></li>',
					'layout'    => 'column_3',
				),
				'expected'            => array(
					'logo' => '<img src="https://example.com/wp-content/plugins/vk-blocks-pro/inc/vk-blocks/admin//images/vk-blocks-logo_ol.svg" alt="VK Blocks" />',
					'menu' => '<li><a href="https://example.com/wp-admin/profile.php" target="_blank">Edit your profile</a></li><li id="btn_vkExUnit_ga_options" class="vkExUnit_ga_options"><a href="#vkExUnit_ga_options">Google Analytics Settings</a></li>',
				),
			),
			array(
				'test_condition_name' => 'column_2 でロゴとメニューを渡す => ロゴはそのまま出力され、メニューは出力されない',
				'conditions'          => array(
					'logo_html' => '<img src="https://example.com/wp-content/themes/smaveksive/admin/build/admin.svg" alt="SmaVeksive" />',
					'menu_html' => '<li><a href="#smaveksive-license-key">SmaVeksive License Key</a></li>',
					'layout'    => 'column_2',
				),
				'expected'            => array(
					'logo' => '<img src="https://example.com/wp-content/themes/smaveksive/admin/build/admin.svg" alt="SmaVeksive" />',
					'menu' => null,
				),
			),
			array(
				'test_condition_name' => 'column_3 でロゴ・メニューが空文字 => 空のまま出力される',
				'conditions'          => array(
					'logo_html' => '',
					'menu_html' => '',
					'layout'    => 'column_3',
				),
				'expected'            => array(
					'logo' => '',
					'menu' => '',
				),
			),
			array(
				'test_condition_name' => 'ExUnit の id 属性の引用符が閉じ誤りの li を渡す => id と class は残り、不正な属性だけ除かれる',
				'conditions'          => array(
					'logo_html' => '',
					'menu_html' => '<li id="btn_"vkExUnit_ga_options" class="vkExUnit_ga_options"><a href="#vkExUnit_ga_options">Google Analytics Settings</a></li>',
					'layout'    => 'column_3',
				),
				'expected'            => array(
					'logo' => '',
					'menu' => '<li id="btn_" class="vkExUnit_ga_options"><a href="#vkExUnit_ga_options">Google Analytics Settings</a></li>',
				),
			),
			array(
				'test_condition_name' => 'column_3 で onclick 属性・javascript: のリンク・script タグを含むロゴとメニューを渡す => それらが除かれる',
				'conditions'          => array(
					'logo_html' => '<div class="logo_exUnit" onclick="alert(1)"><img src="https://example.com/logo.png" alt="VK ExUnit" /></div>',
					'menu_html' => '<li><a href="#faq-setting" onclick="alert(2)">FAQ Setting</a></li><li><a href="javascript:alert(3)">Link</a></li><script>alert(4)</script>',
					'layout'    => 'column_3',
				),
				'expected'            => array(
					'logo' => '<div class="logo_exUnit"><img src="https://example.com/logo.png" alt="VK ExUnit" /></div>',
					'menu' => '<li><a href="#faq-setting">FAQ Setting</a></li><li><a href="alert(3)">Link</a></li>alert(4)',
				),
			),
			array(
				'test_condition_name' => 'column_2 で onerror 属性と script タグを含むロゴを渡す => それらが除かれる',
				'conditions'          => array(
					'logo_html' => '<img src="https://example.com/logo.svg" alt="Logo" onerror="alert(5)" /><script>alert(6)</script>',
					'menu_html' => '',
					'layout'    => 'column_2',
				),
				'expected'            => array(
					'logo' => '<img src="https://example.com/logo.svg" alt="Logo" />alert(6)',
					'menu' => null,
				),
			),
		);

		foreach ( $test_cases as $case ) {
			// 共通フレームを出力して取り込む（タイトルは空にして見出しを出さない）.
			ob_start();
			VkAdmin::admin_page_frame( '', '__return_null', $case['conditions']['logo_html'], $case['conditions']['menu_html'], $case['conditions']['layout'] );
			$html = ob_get_clean();

			// ロゴ部分と左メニュー部分を取り出す（出力されていない場合は null）.
			preg_match( '#<div class="pageLogo">(.*?)</div>\s*<(?:div class="vk_option_nav"|hr class="wp-header-end")#s', $html, $logo_matches );
			preg_match( '#<div class="vk_option_nav">\s*<ul>\s*(.*?)\s*</ul>#s', $html, $menu_matches );
			$actual = array(
				'logo' => isset( $logo_matches[1] ) ? $logo_matches[1] : null,
				'menu' => isset( $menu_matches[1] ) ? $menu_matches[1] : null,
			);

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}
}
