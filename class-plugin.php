<?php

namespace Bloom_UX\WP_Async_Img_Load;

class Plugin {
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_image_attributes'), 9999, 3 );
	}
	public function enqueue_scripts() {
		wp_enqueue_script( 'lazysizes', plugins_url( 'node_modules/lazysizes/lazysizes.min.js', __FILE__ ), array(), '5.1.0', false );
	}
	public function filter_image_attributes( array $attr, \WP_Post $attachment, $size ) : array {
		if ( empty( $attr['class'] ) ) {
			$attr['class'] = 'lazyload';
		} else {
			$attr['class'] .= ' lazyload';
		}
		$attr['data-src'] = $attr['src'];
		if ( ! empty( $attr['srcset'] ) ) {
			$attr['data-srcset'] = $attr['srcset'];
			unset( $attr['srcset'] );
		}
		$attr['src'] = plugins_url( 'blank.png', __FILE__ );
		return $attr;
	}
}
