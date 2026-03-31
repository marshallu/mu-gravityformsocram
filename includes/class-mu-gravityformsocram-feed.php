<?php
/**
 * Gravity Forms Feed Add-On for Ocram.
 *
 * @package MU_GRAVITYFORMSOCRAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GFFeedAddOn subclass that posts form submissions to Ocram as board cards.
 */
class MU_GravityFormsOcram_Feed extends GFFeedAddOn {

	const OCRAM_API_BASE = 'https://www.ocram.io/api/webhooks/boards/';

	// phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore -- GFFeedAddOn requires underscore-prefixed properties.

	/**
	 * Add-on version.
	 *
	 * @var string
	 */
	protected $_version = '1.0.0';

	/**
	 * Minimum Gravity Forms version required.
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '2.5';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $_slug = 'mu-gravityformsocram';

	/**
	 * Plugin path relative to the plugins directory.
	 *
	 * @var string
	 */
	protected $_path = 'mu-gravityformsocram/mu-gravityformsocram.php';

	/**
	 * Full path to the main plugin file.
	 *
	 * @var string
	 */
	protected $_full_path = __FILE__;

	/**
	 * Full add-on title.
	 *
	 * @var string
	 */
	protected $_title = 'Gravity Forms Ocram';

	/**
	 * Short add-on title.
	 *
	 * @var string
	 */
	protected $_short_title = 'Ocram';

	/**
	 * Single instance of the class.
	 *
	 * @var MU_GravityFormsOcram_Feed
	 */
	private static $_instance = null;

	// phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Returns the single instance of MU_GravityFormsOcram_Feed.
	 *
	 * @return MU_GravityFormsOcram_Feed
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Returns the add-on menu icon as an inline SVG string.
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 254.23 402.46">'
			. '<path fill="#00AC3E" d="M0 248.3 177.37 70.93V0L0 177.39v70.91z'
			. 'M0 402.46l177.37-177.39v-57.31L0 345.13v57.33z'
			. 'M76.84 248.3 254.23 70.93v-59.6L76.84 188.72v59.58z'
			. 'M254.23 165.49 76.84 342.86v59.6l177.39-177.39v-59.58z"/>'
			. '</svg>';
	}

	/**
	 * Returns the feed settings fields.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Ocram Feed Settings', 'mu-gravityformsocram' ),
				'fields' => array(
					array(
						'name'     => 'feedName',
						'label'    => esc_html__( 'Feed Name', 'mu-gravityformsocram' ),
						'type'     => 'text',
						'required' => true,
						'class'    => 'medium',
					),
					array(
						'name'     => 'board_token',
						'label'    => esc_html__( 'Board Webhook Token', 'mu-gravityformsocram' ),
						'type'     => 'text',
						'required' => true,
						'class'    => 'medium',
						'tooltip'  => esc_html__( 'The webhook token from the Ocram board settings page.', 'mu-gravityformsocram' ),
					),
					array(
						'name'     => 'title_field',
						'label'    => esc_html__( 'Card Title', 'mu-gravityformsocram' ),
						'type'     => 'field_select',
						'required' => true,
						'tooltip'  => esc_html__( 'Select the form field whose value will become the card title.', 'mu-gravityformsocram' ),
					),
					array(
						'name'    => 'description_source',
						'label'   => esc_html__( 'Card Description', 'mu-gravityformsocram' ),
						'type'    => 'select',
						'choices' => array(
							array(
								'label' => esc_html__( 'All form fields', 'mu-gravityformsocram' ),
								'value' => 'all_fields',
							),
							array(
								'label' => esc_html__( 'Specific field', 'mu-gravityformsocram' ),
								'value' => 'specific_field',
							),
							array(
								'label' => esc_html__( 'None', 'mu-gravityformsocram' ),
								'value' => 'none',
							),
						),
						'tooltip' => esc_html__( '"All form fields" builds an HTML summary of every submitted field. "Specific field" maps a single field value. "None" omits the description entirely.', 'mu-gravityformsocram' ),
					),
					array(
						'name'       => 'description_field',
						'label'      => '',
						'type'       => 'field_select',
						'dependency' => array(
							'field'  => 'description_source',
							'values' => array( 'specific_field' ),
						),
					),
				),
			),
		);
	}

	/**
	 * Defines the columns shown in the feed list.
	 *
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'feedName'    => esc_html__( 'Name', 'mu-gravityformsocram' ),
			'board_token' => esc_html__( 'Board Token', 'mu-gravityformsocram' ),
		);
	}

	/**
	 * Masks the board token in the feed list for readability.
	 *
	 * @param array $feed Feed object.
	 * @return string
	 */
	public function get_column_value_board_token( $feed ) {
		$token = rgars( $feed, 'meta/board_token' );
		if ( empty( $token ) ) {
			return esc_html__( '(not set)', 'mu-gravityformsocram' );
		}
		return esc_html( substr( $token, 0, 8 ) ) . '&hellip;';
	}

	/**
	 * Processes a feed: builds the Ocram payload and posts it to the board webhook.
	 *
	 * Returns true on success or false on failure so GF can record feed status on the entry.
	 *
	 * @param array $feed  Feed config from the database.
	 * @param array $entry Gravity Forms entry.
	 * @param array $form  Gravity Forms form definition.
	 * @return bool
	 */
	public function process_feed( $feed, $entry, $form ) {
		$token = rgars( $feed, 'meta/board_token' );

		if ( empty( $token ) ) {
			$this->log_error( __METHOD__ . '(): Board webhook token is not configured.' );
			return false;
		}

		$title_field_id = rgars( $feed, 'meta/title_field' );
		$title          = $this->get_field_value( $form, $entry, $title_field_id );

		if ( '' === $title ) {
			$this->log_error( __METHOD__ . '(): Title field is empty — skipping.' );
			return false;
		}

		$body = array( 'title' => $title );

		$description_source = rgars( $feed, 'meta/description_source' );

		if ( 'all_fields' === $description_source ) {
			$body['description'] = $this->build_all_fields_description( $form, $entry );
		} elseif ( 'specific_field' === $description_source ) {
			$description_field_id = rgars( $feed, 'meta/description_field' );
			if ( ! empty( $description_field_id ) ) {
				$body['description'] = $this->get_field_value( $form, $entry, $description_field_id );
			}
		}

		$url      = self::OCRAM_API_BASE . rawurlencode( $token ) . '/cards';
		$response = wp_remote_post(
			$url,
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( $body ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->log_error( __METHOD__ . '(): Request failed — ' . $response->get_error_message() );
			return false;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( 201 !== $code ) {
			$this->log_error(
				__METHOD__ . '(): Unexpected response ' . $code . ' — ' . wp_remote_retrieve_body( $response )
			);
			return false;
		}

		$this->log_debug( __METHOD__ . '(): Card created successfully.' );
		return true;
	}

	/**
	 * Builds an HTML description from all non-hidden form field values.
	 *
	 * @param array $form  Gravity Forms form definition.
	 * @param array $entry Gravity Forms entry.
	 * @return string HTML string.
	 */
	private function build_all_fields_description( array $form, array $entry ): string {
		$html = '';

		foreach ( $form['fields'] as $field ) {
			if ( $field->displayOnly ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- GF framework property.
				continue;
			}

			if ( GFFormsModel::is_field_hidden( $form, $field, array(), $entry ) ) {
				continue;
			}

			$label  = esc_html( $field->label );
			$inputs = $field->get_entry_inputs();

			if ( is_array( $inputs ) ) {
				$parts = array();
				foreach ( $inputs as $input ) {
					$val = rgar( $entry, (string) $input['id'] );
					if ( '' !== $val ) {
						$parts[] = esc_html( $val );
					}
				}
				$value = implode( ', ', $parts );
			} else {
				$value = esc_html( rgar( $entry, (string) $field->id ) );
			}

			if ( '' === $value ) {
				continue;
			}

			$html .= '<p><strong>' . $label . ':</strong> ' . nl2br( $value ) . '</p>';
		}

		return $html;
	}
}
