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

## Change log

[ Spec Change ] Remove `VK_Custom_Html_Control` and `VK_Custom_Text_Control` because the same classes are now provided by [vektor-inc/vk-helpers](https://github.com/vektor-inc/vk-helpers) 0.3.0+. Projects that pulled these classes from vk-admin must switch to `vektor-inc/vk-helpers ^0.3.0` (this is a breaking change).

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
