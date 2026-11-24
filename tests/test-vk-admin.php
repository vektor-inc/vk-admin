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
