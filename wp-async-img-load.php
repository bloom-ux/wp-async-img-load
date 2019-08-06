<?php
/**
 * Plugin Name: Async Image loading
 * Plugin URI: https://github.com/bloom-ux/wp-async-img-load
 * Description: Enable lazy-loading for images
 * Version: 0.1.0
 * Author: Bloom User Experience
 * Author URI: https://www.bloom-ux.com
 */

namespace Bloom_UX\WP_Async_Img_Load;

require_once __DIR__ .'/class-plugin.php';
require_once __DIR__ .'/functions.php';

$plugin = Plugin::get_instance();
$plugin->init();
