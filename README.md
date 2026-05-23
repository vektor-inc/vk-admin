# VK Admin

## 概要



## 使い方

Composer の require に登録
```
composer require vektor-inc/vk-admin
```

autoload.php を読み込み
```
require_once dirname( __FILE__ ) . '/vendor/autoload.php';
```

本体を読み込んで実行

```
use VektorInc\VK_Admin\VkAdmin;
VkAdmin::init();
```

## カスタマイザー用 独自 control

このパッケージには、カスタマイザーで使える独自 control クラスが同梱されています。
`composer require vektor-inc/vk-admin` した上で `vendor/autoload.php` を読み込めば、
グローバル名前空間にクラスが定義されているため、そのまま `new` して利用できます。

### VK_Custom_Html_Control

ラベル・サブタイトル・任意 HTML をまとめて出力するための control。
カスタマイザーのセクション内に説明文や注意書きを表示したいときに利用します。

```php
$wp_customize->add_control(
    new VK_Custom_Html_Control(
        $wp_customize,
        'your_setting_description',
        array(
            'section'          => 'your_section',
            'label'            => __( 'Section Title', 'your-textdomain' ),
            'custom_title_sub' => __( 'Sub Title', 'your-textdomain' ),
            'custom_html'      => '<p>' . esc_html__( '説明文', 'your-textdomain' ) . '</p>',
        )
    )
);
```

### VK_Custom_Text_Control

text 入力欄の前後に補助文字列（例: 単位ラベル）を、入力欄の下に共通 description を
表示するための control。同じセクションで `width` / `height` のように複数の control に
同じ説明文を持たせたい場合の集約用にも使えます。

```php
$wp_customize->add_control(
    new VK_Custom_Text_Control(
        $wp_customize,
        'your_setting_width',
        array(
            'section'     => 'your_section',
            'label'       => __( 'Width', 'your-textdomain' ),
            'description' => __( '幅と高さを揃えたい場合は両方指定してください。', 'your-textdomain' ),
            'input_after' => 'px',
        )
    )
);
```

#### input_type / input_attrs（type=number 等への対応）

`input_type` で input の type 属性を、`input_attrs` で任意の属性（`min` / `max` / `step` / `inputmode` / `data-*` / `aria-*` 等）を指定できます。
画像サイズ入力など、数値専用の入力欄を作りたいときに利用してください。

```php
$wp_customize->add_control(
    new VK_Custom_Text_Control(
        $wp_customize,
        'your_setting_image_width',
        array(
            'section'     => 'your_section',
            'label'       => __( 'Image width', 'your-textdomain' ),
            'input_type'  => 'number',
            'input_after' => 'px',
            'input_attrs' => array(
                'min'       => 1,
                'max'       => 500,
                'step'      => 1,
                'inputmode' => 'numeric',
            ),
        )
    )
);
```

`input_type` を省略した場合は従来どおり `text` として動作します（後方互換）。


---

## Change log

== 0.7.0 ==
[ Feature Add ] Add `input_type` and `input_attrs` properties to `VK_Custom_Text_Control` so it can render `type=number` and other input types with arbitrary attributes (`min` / `max` / `step` / `inputmode` etc). Existing usage keeps working unchanged because both properties default to the previous behavior (`input_type='text'`, `input_attrs=array()`).

== 0.6.1 ==
[ Bug Fix ] Defer VK_Custom_Html_Control / VK_Custom_Text_Control class declaration to the `customize_register` action so that the classes do not fail to load when `WP_Customize_Control` is not yet available at composer autoload time.

== 0.6.0 ==
[ Feature Add ] Add VK_Custom_Html_Control and VK_Custom_Text_Control as shared customizer controls for reuse across plugins.

== 0.5.1 ==
[ Bug Fix ] Fixed broken vk_admin.js / CSS URL on environments where site_url and home_url differ (WordPress installed in its own directory) or wp-content is relocated.
[ Bug Fix ] Skip enqueuing widget screen CSS when not in admin context.
[ Other ]  Fix CSS load hook.

== 0.5.0 ==
[ Other ]  Add article list of Vektor Pattern Library

== 0.4.1 ==
[ Bug Fix ] Fixed problem of filepath on Windows local environment.

== 0.4.0 ==
[ Other ] Cope with English news
[ Bug Fix ] Fix Widget Edit UI
