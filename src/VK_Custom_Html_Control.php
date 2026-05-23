<?php
/**
 * VK Custom HTML Control loader.
 *
 * カスタマイザーで任意の HTML（説明文・補助情報など）を出力するための独自 control。
 * Previously each plugin (Lightning G3 Pro Unit, ExUnit, etc.) defined this class
 * locally with a `class_exists()` guard. This file consolidates that definition
 * inside the shared vk-admin package so plugins can rely on a single source of
 * truth instead of copy/pasting the class.
 *
 * Composer の `files` autoloader はプラグイン起動直後（`require_once vendor/autoload.php`）に
 * 即ファイルを require する。この時点では WordPress コアの `WP_Customize_Control` がまだ
 * 読み込まれていないため、即時に `class_exists( 'WP_Customize_Control' )` でガードしても
 * クラス宣言ブロックがスキップされてしまい、後から `new VK_Custom_Html_Control(...)` を
 * 呼んだ際に Fatal error が発生する。
 *
 * これを避けるため、`customize_register` アクション内で `WP_Customize_Control` が
 * 読み込まれた後にクラス宣言を行う「遅延宣言」方式を採用している。
 *
 * @package vektor-inc/vk-admin
 * @license GPL-2.0+
 */

// グローバル名前空間（namespace 宣言なし）。
// 既存プラグインが `new VK_Custom_Html_Control(...)` で利用しているため、
// クラス名はグローバルのまま維持する必要がある。

// 優先度 0 で customize_register に登録し、他の add_control 呼び出しより先に
// クラス宣言が完了している状態を作る。
// WordPress 環境外（phpcs などの静的解析 CLI から composer autoload された場合）でも
// fatal にしないよう、`add_action()` の存在チェックでガードする。
if ( function_exists( 'add_action' ) ) {
	add_action( 'customize_register', 'vk_admin_register_custom_html_control', 0 );
}

if ( ! function_exists( 'vk_admin_register_custom_html_control' ) ) {
	/**
	 * VK_Custom_Html_Control を遅延宣言する。
	 *
	 * `customize_register` アクションのタイミングでは `WP_Customize_Control` が
	 * 読み込まれているため、ここで初めてクラス宣言を行う。`class_exists()` には
	 * 第 2 引数 `false` を渡し、autoload の発火による無限ループを防ぐ。
	 *
	 * @return void
	 */
	function vk_admin_register_custom_html_control() {
		// WP_Customize_Control が未読込、または既にクラス宣言済みなら何もしない。
		// 第 2 引数 false で autoload を発火させない。
		if ( ! class_exists( 'WP_Customize_Control', false ) || class_exists( 'VK_Custom_Html_Control', false ) ) {
			return;
		}

		/**
		 * VK_Custom_Html_Control
		 *
		 * WP_Customize_Control を継承し、ラベル・サブタイトル・任意 HTML をまとめて
		 * 出力するための control。設定値そのものではなく説明文や注意書きを
		 * カスタマイザーのセクション内に表示する用途で利用する。
		 *
		 * 公開プロパティ:
		 *  - $type             : control の type 識別子（'customtext'）。
		 *  - $label_tag        : label を囲む見出しタグ（'h2' / 'h3' / 'h4' / 'h5' / 'h6'、デフォルト 'h2'）。
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
		 *
		 * label_tag の利用例（親 h2 配下の子セクション見出しとして h3 を使う）:
		 *  $wp_customize->add_control(
		 *      new VK_Custom_Html_Control(
		 *          $wp_customize,
		 *          'vk_admin_sample_subsection',
		 *          array(
		 *              'section'     => 'vk_admin_sample_section',
		 *              'label'       => __( 'Image size', 'your-textdomain' ),
		 *              'label_tag'   => 'h3',
		 *              'custom_html' => '<p>' . esc_html__( '説明文', 'your-textdomain' ) . '</p>',
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
			 * Heading tag used to wrap the main label.
			 *
			 * label を囲む見出しタグ。情報階層の逆転を避けるため、親セクション（h2）の
			 * 配下に置く子セクションで 'h3' などを指定して見出しレベルを下げる用途。
			 *
			 * 許容値: 'h2' / 'h3' / 'h4' / 'h5' / 'h6'
			 * 上記以外が渡された場合は安全のため 'h2' にフォールバックする。
			 *
			 * @var string
			 */
			public $label_tag = 'h2';

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
			 * label は $label_tag で指定された見出しタグ（デフォルト h2）で出力する。
			 * 許容外の値が指定された場合は安全のため h2 にフォールバックする。
			 *
			 * @return void
			 */
			public function render_content() {
				// ラベルが設定されていれば $label_tag で指定された見出しタグとして出力する。
				if ( $this->label ) {
					// 許容する見出しタグのリスト。h1 はカスタマイザーパネルのタイトル等と衝突しうるため含めない。
					$allowed_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6' );
					// 入力を小文字化し、許容リストに含まれない場合は 'h2' にフォールバックする。
					$label_tag = in_array( strtolower( (string) $this->label_tag ), $allowed_tags, true ) ? strtolower( (string) $this->label_tag ) : 'h2';
					// CSS クラスはタグに対応する名前にする（'admin-custom-h2' / 'admin-custom-h3' / ...）。
					// 'admin-custom-h2' は従来から存在するクラス名のため、デフォルト挙動の後方互換が維持される。
					$label_class = 'admin-custom-' . $label_tag;
					printf(
						'<%1$s class="%2$s">%3$s</%1$s>',
						esc_attr( $label_tag ),
						esc_attr( $label_class ),
						wp_kses_post( $this->label )
					);
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
}
