<?php
/**
 * VK Custom HTML Control
 *
 * カスタマイザーで任意の HTML（説明文・補助情報など）を出力するための独自 control。
 * Previously each plugin (Lightning G3 Pro Unit, ExUnit, etc.) defined this class
 * locally with a `class_exists()` guard. This file consolidates that definition
 * inside the shared vk-admin package so plugins can rely on a single source of
 * truth instead of copy/pasting the class.
 *
 * @package vektor-inc/vk-admin
 * @license GPL-2.0+
 */

// グローバル名前空間（namespace 宣言なし）。
// 既存プラグインが `new VK_Custom_Html_Control(...)` で利用しているため、
// クラス名はグローバルのまま維持する必要がある。

// WP_Customize_Control が存在しない環境（フロント側など）では本クラスを宣言しない。
if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'VK_Custom_Html_Control' ) ) {

	/**
	 * VK_Custom_Html_Control
	 *
	 * WP_Customize_Control を継承し、ラベル・サブタイトル・任意 HTML をまとめて
	 * 出力するための control。設定値そのものではなく説明文や注意書きを
	 * カスタマイザーのセクション内に表示する用途で利用する。
	 *
	 * 公開プロパティ:
	 *  - $type             : control の type 識別子（'customtext'）。
	 *  - $custom_title_sub : ラベルの直下に表示するサブタイトル（h3）。
	 *  - $custom_html      : サブタイトルの下に表示する任意 HTML（wp_kses_post でエスケープ）。
	 *
	 * 利用例:
	 *  $wp_customize->add_control(
	 *      new VK_Custom_Html_Control(
	 *          $wp_customize,
	 *          'vk_admin_sample_description',
	 *          array(
	 *              'section'          => 'vk_admin_sample_section',
	 *              'label'            => __( 'Section Title', 'your-textdomain' ),
	 *              'custom_title_sub' => __( 'Sub Title', 'your-textdomain' ),
	 *              'custom_html'      => '<p>' . esc_html__( '説明文', 'your-textdomain' ) . '</p>',
	 *          )
	 *      )
	 *  );
	 */
	class VK_Custom_Html_Control extends WP_Customize_Control {

		/**
		 * Control type.
		 *
		 * WP_Customize_Control が内部で参照する type 識別子。
		 *
		 * @var string
		 */
		public $type = 'customtext';

		/**
		 * Sub title shown under the main label.
		 *
		 * label の直下に h3 として表示するサブタイトル。
		 *
		 * @var string
		 */
		public $custom_title_sub = '';

		/**
		 * Arbitrary HTML displayed below the sub title.
		 *
		 * 任意 HTML。wp_kses_post() で出力時にサニタイズされる。
		 *
		 * @var string
		 */
		public $custom_html = '';

		/**
		 * Render the control's content.
		 *
		 * ラベル → サブタイトル → 任意 HTML の順で出力する。
		 * いずれも未設定の場合は何も出力しない。
		 *
		 * @return void
		 */
		public function render_content() {
			// ラベルが設定されていれば h2 として出力する。
			if ( $this->label ) {
				echo '<h2 class="admin-custom-h2">' . wp_kses_post( $this->label ) . '</h2>';
			}
			// サブタイトルが設定されていれば h3 として出力する。
			if ( $this->custom_title_sub ) {
				echo '<h3 class="admin-custom-h3">' . wp_kses_post( $this->custom_title_sub ) . '</h3>';
			}
			// 任意 HTML が設定されていれば div でラップして出力する。
			if ( $this->custom_html ) {
				echo '<div>' . wp_kses_post( $this->custom_html ) . '</div>';
			}
		}
	}
}
