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

		$images_urls = $this->get_images_urls( $post->post_content );

		if ( ! $images_urls ) {
			return;
		}

		foreach ( $images_urls as $img_url ) {

			$new_img_id = $this->sideload( $post_id, $img_url );

			if ( is_string( $new_img_id ) ) {
				continue;
				//return $new_img_id;
			}

			$new_img_url = wp_get_attachment_url( $new_img_id );

			if ( ! empty( $new_img_url ) ) {

				$post->post_content = str_replace( $img_url, $new_img_url, $post->post_content );
			}
		}

		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $post->post_content
		) );

		return true;
	}

	private function get_images_urls( $content ) {

		$home_url = home_url();

		$images_urls = array();

		preg_match_all( '/<img[^>]* src=[\'"]?([^>\'" ]+)/', $content, $matches );
		preg_match_all( '/<a[^>]* href=[\'"]?([^>\'" ]+)/', $content, $matches2 );

		$urls = array_merge( $matches[1], $matches2[1] );

		foreach ( $urls as $url ) {

			if ( $home_url === substr( $url, 0, strlen( $home_url ) ) ) {
				continue;
			}

			$images_urls[] = $url;
		}

		$images_urls = array_unique( $images_urls );

		return $images_urls;
	}

	public function sideload( $post_id, $img_url ) {

		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $img_url, $matches );

		if ( empty( $matches[0] ) ) {

			return __( 'Pas d\'image', ISUBMISSION_ID_LANGUAGES );
		}

		$download_url = download_url( $img_url );

		if ( is_wp_error( $download_url ) ) {

			return $download_url->get_error_messages();
		}

		$file_array = array(
			'name'     => basename( $matches[0] ),
			'tmp_name' => $download_url
		);

		$id = media_handle_sideload( $file_array, $post_id );

		// errors
		if ( is_wp_error( $id ) ) {

			@unlink( $file_array['tmp_name'] );

			return $id->get_error_messages();
		}

		// remove temporary file
		@unlink( $file_array['tmp_name'] );

		return $id;
	}
}
