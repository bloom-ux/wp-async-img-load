<?php

namespace Bloom_UX\WP_Async_Img_Load;

class Plugin {
	private static $instance = null;
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_image_attributes'), 9999, 3 );
	}
	public static function get_instance() {
		if ( ! static::$instance ) {
			$called_class = get_called_class();
			static::$instance = new $called_class;
		}
		return static::$instance;
	}
	public function enqueue_scripts() {
		wp_enqueue_script( 'lazysizes', plugins_url( 'node_modules/lazysizes/lazysizes.min.js', __FILE__ ), array(), '5.1.0', false );
	}
	public function filter_image_attributes( array $attr, ?\WP_Post $attachment, $size ) : array {
		if ( ! $attachment ) {
			return $attr;
		}

		if ( isset( $attr['data-load'] ) && $attr['data-load'] === 'eager' ) {
			return $attr;
		}

		$attachment_meta  = get_post_meta( $attachment->ID, '_wp_attachment_metadata', true );

		if ( $size !== 'full' && empty( $attachment_meta['sizes'] ) ) {
			return $attr;
		}

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

		if ( $size !== 'full' && ! isset( $registered_sizes[ $size ] ) ) {
			$attr['src'] = plugins_url( 'blank.png', __FILE__ );
			return $attr;
		}

		$initial_size = $size;
		$current_size = $size === 'full' ? [
			'file' => $attachment_meta['file'],
			'width' => $attachment_meta['width'],
			'height' => $attachment_meta['height']
		] : $registered_sizes[ $size ];

		$height = $size === 'full' ? (int) $attachment_meta['height'] : $registered_sizes[ $size ]['height'];
		$width  = $size === 'full' ? (int) $attachment_meta['width']  : $registered_sizes[ $size ]['width'];

		$aspect_ratio = round( $height / $width, 2 );
		if ( ! empty( $attachment_meta['sizes'] ) ) {
			foreach ( $attachment_meta['sizes'] as $_size => $_atts ) {
				$this_ratio = round( (int) $_atts['height'] / (int) $_atts['width'], 2 );
				if ( $aspect_ratio === $this_ratio && $_atts['height'] < $current_size['height'] ) {
					$current_size = $_atts;
					$size = $_size;
				}
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
