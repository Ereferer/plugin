<?php

require_once '../../../wp-load.php';
require_once 'lib/titan-framework/titan-framework-embedder.php';

class Isubmission_Post_Endpoint {

	public function run() {

		if ( ! $this->is_bearer_token_valid() ) {

			wp_send_json( array(
				'status'  => false,
				'message' => __( 'Incorrect API key.', ISUBMISSION_ID_LANGUAGES )
			) );

			return;
		}

		$json = file_get_contents( 'php://input' );
		$data = json_decode( $json, true );

//		wp_send_json( $data );
//		return;

		if ( empty( $data['post_title'] ) || empty( $data['post_content'] ) ) {

			wp_send_json( array(
				'status'  => false,
				'message' => __( 'Post title and content can\'t be empty.', ISUBMISSION_ID_LANGUAGES )
			) );

			return;
		}

		$post_id = wp_insert_post( array(
			'post_title'   => $data['post_title'],
			'post_content' => $data['post_content'],
			'post_status'  => 'publish',
			//'post_author'  => 1,//get_current_user_id(),
			'post_category' => !empty($data['categories']) ? $data['categories']:[]
		) );

		if ( empty( $post_id ) || is_wp_error( $post_id ) ) {

			wp_send_json( array(
				'status'  => false,
				'message' => $post_id->get_error_message()
			) );

			return;
		}

		$this->insert_row( $post_id );

		$featured_image_result = $this->add_post_featured_image( $post_id, $data['front_image'] );

		if ( true !== $featured_image_result && is_string( $featured_image_result ) ) {

			wp_send_json( array(
				'status'  => false,
				'message' => $featured_image_result
			) );

			return;
		}

		wp_send_json( array(
			'status'  => true,
			'message' => __( 'Success', ISUBMISSION_ID_LANGUAGES )
		) );
	}

	private function is_bearer_token_valid() {

		$bearer_token = $this->get_bearer_token();

		$isubmission_options = TitanFramework::getInstance( 'isubmission' );
		$apy_key = $isubmission_options->getOption( 'isubmission_api_key' );

		return ( ! empty( $bearer_token ) && ! empty( $apy_key ) && $bearer_token === $apy_key );
	}

	private function insert_row( $post_id ) {

		global $wpdb, $plugin_table_isub;

		return $wpdb->insert(
			$wpdb->prefix . $plugin_table_isub,
			array(
				'post_id' => $post_id
			)
		);
	}

	/**
	 * Get hearder Authorization
	 * */
	private function get_authorization_header() {

		$headers = null;

		if ( isset( $_SERVER['Authorization'] ) ) {

			$headers = trim( $_SERVER["Authorization"] );
		} else if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) { //Nginx or fast CGI

			$headers = trim( $_SERVER["HTTP_AUTHORIZATION"] );
		} elseif ( function_exists( 'apache_request_headers' ) ) {

			$requestHeaders = apache_request_headers();

			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine( array_map( 'ucwords', array_keys( $requestHeaders ) ), array_values( $requestHeaders ) );

			//print_r($requestHeaders);
			if ( isset( $requestHeaders['Authorization'] ) ) {

				$headers = trim( $requestHeaders['Authorization'] );
			}
		}

		return $headers;
	}

	/**
	 * get access token from header
	 * */
	private function get_bearer_token() {

		$headers = $this->get_authorization_header();

		// HEADER: Get the access token from the header
		if ( ! empty( $headers ) ) {

			if ( preg_match( '/Bearer\s(\S+)/', $headers, $matches ) ) {

				return $matches[1];
			}
		}

		return null;
	}

	private function add_post_featured_image( $post_id, $img_url ) {

		if ( ! function_exists( 'media_handle_upload' ) ) {

			require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/media.php' );
		}

		$file_array = array();
		$tmp        = download_url( $img_url );
		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $img_url, $matches );
		$file_array['name']     = basename( $matches[0] );
		$file_array['tmp_name'] = $tmp;

		// upload file
		$id = media_handle_sideload( $file_array, $post_id );

		// errors
		if ( is_wp_error( $id ) ) {

			@unlink( $file_array['tmp_name'] );

			return $id->get_error_messages();
		}

		// remove temporary file
		@unlink( $file_array['tmp_name'] );

		return set_post_thumbnail( $post_id, $id );
	}
}

$endpoint = new Isubmission_Post_Endpoint();
$endpoint->run();
