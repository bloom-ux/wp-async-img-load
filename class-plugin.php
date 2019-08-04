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
	public function filter_image_attributes( array $attr, ?\WP_Post $attachment, $size ) : array {
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

		$registered_sizes = wp_get_additional_image_sizes();
		$attachment_meta  = get_post_meta( $attachment->ID, '_wp_attachment_metadata', true );

		if ( ! isset( $registered_sizes[ $size ] ) ) {
			$attr['src'] = plugins_url( 'blank.png', __FILE__ );
			return $attr;
		}

		$initial_size = $size;
		$current_size = $registered_sizes[ $size ];

		$aspect_ratio = round( (int) $registered_sizes[ $size ]['height'] / (int) $registered_sizes[ $size ]['width'], 2 );
		foreach ( $attachment_meta['sizes'] as $_size => $_atts ) {
			$this_ratio = round( (int) $_atts['height'] / (int) $_atts['width'], 2 );
			if ( $aspect_ratio === $this_ratio && $_atts['height'] < $current_size['height'] ) {
				$current_size = $_atts;
				$size = $_size;
			}
		}

		if ( $size === $initial_size ) {
			$attr['src'] = plugins_url( 'blank.png', __FILE__ );
		}

		// reemplazar src por la versión más pequeña en la misma proporción de aspecto
		$attr['src'] = wp_get_attachment_image_url( $attachment->ID, $size );
		return $attr;
	}
}
