<?php

class Isubmission_Import_External_Images {

	public function __construct() {

		if ( ! function_exists( 'media_handle_upload' ) ) {

			require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/media.php' );
		}
	}

	public function import_content_images( $post_id ) {

		$post = get_post( $post_id );

		$post_images = $this->get_post_images( $post->post_content );

		if ( ! $post_images ) {

			return;
		}

		foreach ( $post_images as $post_image ) {

			$new_img_id = $this->sideload( $post_id, $post_image );

			if ( is_string( $new_img_id ) ) {

				continue;
				//return $new_img_id;
			}

			$new_img_url = wp_get_attachment_url( $new_img_id );

			if ( ! empty( $new_img_url ) ) {

				$post->post_content = str_replace( $post_image['src'], $new_img_url, $post->post_content );
			}
		}

		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $post->post_content
		) );

		return true;
	}

	private function get_post_images( $content ) {

		$post_images = array();

		$doc = new DOMDocument();
		@$doc->loadHTML( $content );

		$img_tags = $doc->getElementsByTagName( 'img' );
		$a_tags   = $doc->getElementsByTagName( 'a' );

		foreach ( $img_tags as $img_tag ) {

			$post_images[] = array(
				'src'    => $img_tag->getAttribute( 'src' ),
				'width'  => $img_tag->getAttribute( 'width' ),
				'height' => $img_tag->getAttribute( 'height' )
			);
		}

		foreach ( $a_tags as $a_tag ) {

			$post_images[] = array(
				'src'    => $a_tag->getAttribute( 'src' ),
				'width'  => null,
				'height' => null
			);
		}

		$post_images = array_unique( $post_images, SORT_REGULAR );

		return $post_images;
	}

	public function sideload( $post_id, $post_image ) {

		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $post_image['src'], $matches );

		if ( empty( $matches[0] ) ) {

			return __( 'Pas d\'image', ISUBMISSION_ID_LANGUAGES );
		}

		$temp_file = download_url( $post_image['src'] );

		if ( is_wp_error( $temp_file ) ) {

			return $temp_file->get_error_messages();
		}

		$mime_type = mime_content_type( $temp_file );

		$file_array = array(
			'name'     => basename( $matches[0] ),
			'type'     => $mime_type,
			'tmp_name' => $temp_file
		);

		$overrides = array(
			'test_form' => false
		);

		$file = wp_handle_sideload( $file_array, $overrides );

		// remove temporary file
		@unlink( $temp_file );

		if ( isset( $file['error'] ) ) {

			return new WP_Error( 'upload_error', $file['error'] );
		}

		if ( ! empty( $post_image['width'] ) || ! empty( $post_image['height'] ) ) {

			$resized = $this->resize( $file['file'], $post_image['width'], $post_image['height'] );

			if ( is_wp_error( $resized ) ) {

				return $resized->get_error_messages();
			}
		}

		$attachment_id = $this->insert_attachment( $post_id, $file['file'], $mime_type );

		if ( is_wp_error( $attachment_id ) ) {

			return $attachment_id->get_error_messages();
		}

		return $attachment_id;
	}

	private function insert_attachment( $post_id, $file_path, $mime_type ) {

		$wp_upload_dir = wp_upload_dir();

		$attachment_data = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $file_path ),
			'post_mime_type' => $mime_type,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		return wp_insert_attachment( $attachment_data, $file_path, $post_id );
	}

	private function resize( $file_path, $width, $height ) {

		$image = wp_get_image_editor( $file_path );

		if ( ! is_wp_error( $image ) ) {

			$image->resize( $width, $height, true );

			$resized = $image->save( $file_path );

			if ( is_wp_error( $resized ) ) {

				return new WP_Error( 'image_resize_error', $resized );
			}
		} else {

			return new WP_Error( 'image_editor_load_error', $image );
		}
	}
}
