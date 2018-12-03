<?php

require_once '../../../wp-load.php';
require_once 'lib/titan-framework/titan-framework-embedder.php';
require_once 'class/class-isubmission-import-external-images.php';

class Isubmission_Post_Endpoint {

	private $isubmission_options;

	public function __construct() {

		$this->isubmission_options = TitanFramework::getInstance( 'isubmission' );
	}

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

		if ( ! empty( $data['test_connection'] ) ) {

			wp_send_json( array(
				'status'      => true,
				'plugin_url'  => $this->isubmission_options->getOption( 'isubmission_endpoint' ),
				'message'     => __( 'Success connection', ISUBMISSION_ID_LANGUAGES ),
			) );
		} else {

			if ( empty( $data['id'] ) ) {

				wp_send_json( array(
					'status'  => false,
					'message' => __( 'Post id can\'t be empty.', ISUBMISSION_ID_LANGUAGES )
				) );

				return;
			}

			if ( empty( $data['post_title'] ) || empty( $data['post_content'] ) ) {

				wp_send_json( array(
					'status'  => false,
					'message' => __( 'Post title and content can\'t be empty.', ISUBMISSION_ID_LANGUAGES )
				) );

				return;
			}

			$post_status = $this->isubmission_options->getOption( 'isubmission_post_status' );
			$post_author = $this->isubmission_options->getOption( 'isubmission_post_author' );

			$post_data = array(
				'post_title'    => $data['post_title'],
				'post_content'  => $data['post_content'],
				'post_status'   => empty( $post_status ) ? 'publish' : $post_status,
				'post_author'  => empty( $post_author ) ? 1 : $post_author,
				'post_category' => ! empty( $data['categories'] ) ? $data['categories'] : []
			);

			$internal_post_id = $this->get_post_id_by_place_post_id( $data['id'] );

			if ( $internal_post_id ) {

				if ( 'yes' !== $this->isubmission_options->getOption( 'isubmission_is_posts_editable' ) && empty( $data['force'] ) ) {

					wp_send_json( array(
						'status'  => false,
						'message' => __( 'Posts are not editable.', ISUBMISSION_ID_LANGUAGES )
					) );

					return;
				}

				$post_data['ID'] = $internal_post_id;

				$post_id = wp_update_post( $post_data, true );
			} else {

				$post_id = wp_insert_post( $post_data, true );
			}

			if ( empty( $post_id ) || is_wp_error( $post_id ) ) {

				wp_send_json( array(
					'status'  => false,
					'message' => $post_id->get_error_message()
				) );

				return;
			}

			$import_external_images = new Isubmission_Import_External_Images();
			$import_result          = $import_external_images->import_content_images( $post_id );

			if ( is_string( $import_result ) ) {

				wp_send_json( array(
					'status'  => false,
					'message' => $import_result
				) );

				return;
			}

			if ( ! empty( $data['front_image'] ) ) {

				$featured_image_result = $import_external_images->sideload( $post_id, $data['front_image'] );

				if ( is_string( $featured_image_result ) ) {

					wp_send_json( array(
						'status'  => false,
						'message' => $featured_image_result
					) );

					return;
				}

				set_post_thumbnail( $post_id, $featured_image_result );
			}

			$is_yoast_active = self::is_yoast_active();

			if ( ! empty( $data['meta_title'] ) ) {

				add_post_meta( $post_id, '_isubmission_meta_title', $data['meta_title'] );

				if ( $is_yoast_active ) {

					update_post_meta( $post_id, '_yoast_wpseo_title', $data['meta_title'] );
				}
			}

			if ( ! empty( $data['meta_description'] ) ) {

				add_post_meta( $post_id, '_isubmission_meta_description', $data['meta_description'] );

				if ( $is_yoast_active ) {

					update_post_meta( $post_id, '_yoast_wpseo_metadesc', $data['meta_description'] );
				}
			}

			if ( ! empty( $data['custom_field'] ) ) {
				add_post_meta( $post_id, 'isubmission_image_source', $data['custom_field'] );
			}

			$this->insert_row( $post_id, $data['id'] );

			wp_send_json( array(
				'status'      => true,
				'message'     => __( 'Success', ISUBMISSION_ID_LANGUAGES ),
				'article_url' => get_permalink( $post_id )
			) );
		}
	}

	public function is_yoast_active() {

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {

			return true;
		}

		return false;
	}

	private function is_bearer_token_valid() {

		$bearer_token = $this->get_bearer_token();

		$apy_key = $this->isubmission_options->getOption( 'isubmission_api_key' );

		return ( ! empty( $bearer_token ) && ! empty( $apy_key ) && $bearer_token === $apy_key );
	}

	private function insert_row( $post_id, $place_post_id ) {

		global $wpdb, $plugin_table_isub;

		return $wpdb->insert(
			$wpdb->prefix . $plugin_table_isub,
			array(
				'post_id'       => $post_id,
				'place_post_id' => $place_post_id
			)
		);
	}

	private function get_post_id_by_place_post_id( $place_post_id ) {

		global $wpdb, $plugin_table_isub;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT post_id
			FROM {$wpdb->prefix}$plugin_table_isub
			WHERE place_post_id = %d
		", $place_post_id ) );
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
}

$endpoint = new Isubmission_Post_Endpoint();
$endpoint->run();
