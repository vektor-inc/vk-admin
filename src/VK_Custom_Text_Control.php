<?php
/**
 * VK Custom Text Control
 *
 * カスタマイザーの text 入力欄に、前後の補助文字列（input_before / input_after）と
 * 説明文（description）を 1 つの control として表示するための独自 control。
 * 単位ラベル付きの数値入力欄や、入力欄直下に注釈を表示したい場合に用いる。
 *
 * Previously each plugin (Lightning G3 Pro Unit, ExUnit, etc.) defined this class
 * locally with a `class_exists()` guard. This file consolidates that definition
 * inside the shared vk-admin package so plugins can rely on a single source of
 * truth instead of copy/pasting the class.
 *
 * @package vektor-inc/vk-admin
 * @license GPL-2.0+
 */

// グローバル名前空間（namespace 宣言なし）。
// 既存プラグインが `new VK_Custom_Text_Control(...)` で利用しているため、
// クラス名はグローバルのまま維持する必要がある。

// WP_Customize_Control が存在しない環境（フロント側など）では本クラスを宣言しない。
if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'VK_Custom_Text_Control' ) ) {

	/**
	 * VK_Custom_Text_Control
	 *
	 * WP_Customize_Control を継承し、テキスト入力欄の前後に補助文字列を、
	 * 入力欄の下に description を出力できるようにした control。
	 *
	 * 公開プロパティ:
	 *  - $type         : control の type 識別子（'customtext'）。
	 *  - $input_before : input の左側に表示する補助文字列（例: '$' などの単位記号）。
	 *  - $input_after  : input の右側に表示する補助文字列（例: 'px' などの単位ラベル）。
	 *
	 * $description は親クラス WP_Customize_Control が提供するプロパティをそのまま利用する。
	 *
	 * 利用例:
	 *  $wp_customize->add_control(
	 *      new VK_Custom_Text_Control(
	 *          $wp_customize,
	 *          'vk_admin_sample_width',
	 *          array(
	 *              'section'     => 'vk_admin_sample_section',
	 *              'label'       => __( 'Width', 'your-textdomain' ),
	 *              'description' => __( '幅と高さを揃えたい場合は両方指定してください。', 'your-textdomain' ),
	 *              'input_after' => 'px',
	 *          )
	 *      )
	 *  );
	 */
	class VK_Custom_Text_Control extends WP_Customize_Control {

		/**
		 * Control type.
		 *
		 * WP_Customize_Control が内部で参照する type 識別子。
		 *
		 * @var string
		 */
		public $type = 'customtext';

		/**
		 * String displayed before the input.
		 *
		 * 入力欄の左側に表示する補助文字列。wp_kses_post() でエスケープされる。
		 *
		 * @var string
		 */
		public $input_before = '';

		/**
		 * String displayed after the input.
		 *
		 * 入力欄の右側に表示する補助文字列。wp_kses_post() でエスケープされる。
		 *
		 * @var string
		 */
		public $input_after = '';

		/**
		 * Render the control's content.
		 *
		 * ラベル・input_before・input 本体・input_after・description の順で出力する。
		 * input_before / input_after が設定されている場合は input の幅を 50% に抑え、
		 * 補助文字列との並びが破綻しないようにする。
		 *
		 * @return void
		 */
		public function render_content() {
			// input_before / input_after があるときは input の幅を 50% に抑え、
			// 補助文字列との並びを保つ。
			$input_style = ( $this->input_before || $this->input_after ) ? ' style="width:50%"' : '';
			?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<div>
					<?php echo wp_kses_post( $this->input_before ); ?>
					<input type="text" value="<?php echo esc_attr( $this->value() ); ?>"<?php echo $input_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 内部で固定値のみを設定。 ?> <?php $this->link(); ?> />
					<?php echo wp_kses_post( $this->input_after ); ?>
				</div>
				<?php if ( $this->description ) : ?>
					<div class="description"><?php echo wp_kses_post( $this->description ); ?></div>
				<?php endif; ?>
			</label>
			<?php
		}
	}
}
