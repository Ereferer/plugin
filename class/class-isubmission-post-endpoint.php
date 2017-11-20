<?php

class Isubmission_Post_Endpoint {

	private $endpoint = 'isubmission';

	public function __construct() {

		add_action( 'init', array( $this, 'add_rewrite_endpoint' ) );
		add_filter( 'query_vars', array( $this, 'add_query_var' ) );
		add_action( 'template_include', array( $this, 'template_include' ) );

	}

	public function add_rewrite_endpoint() {

		add_rewrite_endpoint( $this->endpoint, EP_ROOT );
	}

	public function add_query_var( $vars ) {

		$vars[] = $this->endpoint;

		return $vars;
	}

	public function template_include( $template ) {

		if ( false === get_query_var( $this->endpoint, false ) ) {

			return $template;

			exit;
		}

		$json = file_get_contents( 'php://input' );
		$data = json_decode( $json, true );

		$bearer_token = $this->get_bearer_token();

		$isubmission_options = TitanFramework::getInstance( 'isubmission' );
		$apy_key = $isubmission_options->getOption( 'isubmission_api_key' );

		if ( empty( $bearer_token ) || empty( $apy_key ) || $bearer_token !== $apy_key ) {

			wp_send_json( array(
				'status'  => false,
				'message' => __( 'Incorrect API key.', ISUBMISSION_ID_LANGUAGES )
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

		$post_id = wp_insert_post( array(
			'post_title'   => $data['post_title'],
			'post_content' => $data['post_content'],
			'post_status'  => 'publish',
			//'post_author'  => 1,//get_current_user_id(),
			'post_category' => !empty($data['categories']) ? $data['categories']:[],
		) );

		if ( empty( $post_id ) || is_wp_error( $post_id ) ) {

			wp_send_json( array(
				'status'  => false,
				'message' => $post_id->get_error_message()
			) );

			return;
		}

		$this->insert_row( $post_id );

		wp_send_json( array(
			'status'  => true,
			'message' => __( 'Success', ISUBMISSION_ID_LANGUAGES )
		) );
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
}

new Isubmission_Post_Endpoint();
