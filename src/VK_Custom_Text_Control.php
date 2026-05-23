<?php
/**
 * VK Custom Text Control loader.
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
 * Composer の `files` autoloader はプラグイン起動直後（`require_once vendor/autoload.php`）に
 * 即ファイルを require する。この時点では WordPress コアの `WP_Customize_Control` がまだ
 * 読み込まれていないため、即時に `class_exists( 'WP_Customize_Control' )` でガードしても
 * クラス宣言ブロックがスキップされてしまい、後から `new VK_Custom_Text_Control(...)` を
 * 呼んだ際に Fatal error が発生する。
 *
 * これを避けるため、`customize_register` アクション内で `WP_Customize_Control` が
 * 読み込まれた後にクラス宣言を行う「遅延宣言」方式を採用している。
 *
 * @package vektor-inc/vk-admin
 * @license GPL-2.0+
 */

// グローバル名前空間（namespace 宣言なし）。
// 既存プラグインが `new VK_Custom_Text_Control(...)` で利用しているため、
// クラス名はグローバルのまま維持する必要がある。

// 優先度 0 で customize_register に登録し、他の add_control 呼び出しより先に
// クラス宣言が完了している状態を作る。
// WordPress 環境外（phpcs などの静的解析 CLI から composer autoload された場合）でも
// fatal にしないよう、`add_action()` の存在チェックでガードする。
if ( function_exists( 'add_action' ) ) {
	add_action( 'customize_register', 'vk_admin_register_custom_text_control', 0 );
}

if ( ! function_exists( 'vk_admin_register_custom_text_control' ) ) {
	/**
	 * VK_Custom_Text_Control を遅延宣言する。
	 *
	 * `customize_register` アクションのタイミングでは `WP_Customize_Control` が
	 * 読み込まれているため、ここで初めてクラス宣言を行う。`class_exists()` には
	 * 第 2 引数 `false` を渡し、autoload の発火による無限ループを防ぐ。
	 *
	 * @return void
	 */
	function vk_admin_register_custom_text_control() {
		// WP_Customize_Control が未読込、または既にクラス宣言済みなら何もしない。
		// 第 2 引数 false で autoload を発火させない。
		if ( ! class_exists( 'WP_Customize_Control', false ) || class_exists( 'VK_Custom_Text_Control', false ) ) {
			return;
		}

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
		 *  - $input_type   : input 要素の type 属性（例: 'text', 'number', 'email' など）。
		 *  - $input_attrs  : input 要素に出力する任意属性の連想配列（例: array( 'min' => 1, 'max' => 500 )）。
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
		 *              'input_type'  => 'number',
		 *              'input_after' => 'px',
		 *              'input_attrs' => array(
		 *                  'min'       => 1,
		 *                  'max'       => 500,
		 *                  'step'      => 1,
		 *                  'inputmode' => 'numeric',
		 *              ),
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
			 * Input type attribute.
			 *
			 * input 要素の type 属性に出力する文字列。
			 * 例: 'text', 'number', 'email', 'url', 'tel', 'password' など。
			 * 空文字や非文字列が指定された場合は render 時に 'text' へフォールバックする。
			 *
			 * @var string
			 */
			public $input_type = 'text';

			/**
			 * Extra input attributes.
			 *
			 * input 要素に追加で出力する任意属性の連想配列。
			 * 例: array( 'min' => 1, 'max' => 500, 'step' => 1, 'inputmode' => 'numeric', 'pattern' => '\\d+' )
			 * 属性名・属性値ともに esc_attr() でエスケープして出力するため、
			 * data-* / aria-* 等もそのまま渡すことができる。
			 *
			 * セキュリティおよび既存プロパティとの衝突回避のため、render_content() 内で
			 * 以下の属性は自動的に除外される:
			 * - 'type' / 'value' / 'style'（既存プロパティと衝突、または XSS リスク）
			 * - 'on*'（onclick / onload 等のイベントハンドラ）
			 * また、属性値は scalar のみ許可（配列・オブジェクト・null はスキップ）。
			 * bool 値は HTML5 boolean attribute として扱われる（true なら属性名のみ出力）。
			 *
			 * @var array
			 */
			public $input_attrs = array();

			/**
			 * Render the control's content.
			 *
			 * ラベル・input_before・input 本体・input_after・description の順で出力する。
			 * input_before / input_after が設定されている場合は input の幅を 50% に抑え、
			 * 補助文字列との並びが破綻しないようにする。
			 *
			 * input_type / input_attrs を併用することで、type=number 等の他の input タイプや
			 * min / max / step / inputmode などの任意属性を出力できる。
			 *
			 * @return void
			 */
			public function render_content() {
				// input_before / input_after があるときは input の幅を 50% に抑え、
				// 補助文字列との並びを保つ。
				$input_style = ( $this->input_before || $this->input_after ) ? ' style="width:50%"' : '';

				// input_type が空文字や非文字列の場合は 'text' にフォールバックする防御。
				// ホワイトリスト的なチェックは行わないが、esc_attr で XSS は防止される。
				$input_type = ( is_string( $this->input_type ) && '' !== $this->input_type ) ? $this->input_type : 'text';

				// input_attrs が配列でない場合に foreach で fatal を起こさないよう配列にフォールバック。
				$input_attrs = is_array( $this->input_attrs ) ? $this->input_attrs : array();
				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<div>
						<?php echo wp_kses_post( $this->input_before ); ?>
						<input
							type="<?php echo esc_attr( $input_type ); ?>"
							value="<?php echo esc_attr( $this->value() ); ?>"
							<?php
							// 任意属性（min / max / step / inputmode / data-* / aria-* など）。
							// 属性名・属性値のバリデーションを行い、不正な値や危険な属性は出力しない。
							// - 属性名: 文字列かつ非空のもののみ許可。
							// - 'on*'（onclick / onload 等のイベントハンドラ）はスキップ。
							// - 予約属性（type / value / style）はクラスの既存プロパティと衝突するためスキップ。
							//   さらに style は esc_attr() だけでは XSS を防ぎきれないので一律除外。
							// - 属性値: スカラーのみ許可。bool true は HTML5 boolean attribute として
							//   属性名のみを出力、bool false は省略。null・配列・オブジェクトは出力しない。
							$reserved_attrs = array( 'type', 'value', 'style' );
							foreach ( $input_attrs as $attr_name => $attr_value ) :
								// 属性名が文字列でない、または空文字ならスキップ。
								if ( ! is_string( $attr_name ) || '' === $attr_name ) {
									continue;
								}
								// 比較は小文字で行う（HTML 属性名は大文字小文字を区別しないため）。
								$attr_name_lc = strtolower( $attr_name );
								// イベントハンドラ系（on*）と予約属性は出力しない。
								if ( 0 === strpos( $attr_name_lc, 'on' ) || in_array( $attr_name_lc, $reserved_attrs, true ) ) {
									continue;
								}
								// bool 値は HTML5 boolean attribute として扱う（true なら属性名のみ、false なら省略）。
								if ( is_bool( $attr_value ) ) {
									if ( $attr_value ) {
										echo esc_attr( $attr_name );
									}
									continue;
								}
								// null / 配列 / オブジェクトはそのまま esc_attr に渡せないのでスキップ。
								if ( null === $attr_value || ! is_scalar( $attr_value ) ) {
									continue;
								}
								?>
								<?php echo esc_attr( $attr_name ); ?>="<?php echo esc_attr( (string) $attr_value ); ?>"
							<?php endforeach; ?>
							<?php echo $input_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 内部で固定値のみを設定。 ?>
							<?php $this->link(); ?>
						/>
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
}
