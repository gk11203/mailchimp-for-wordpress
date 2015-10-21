<?php

/**
 * Class MC4WP_Integration_Manager
 */
class MC4WP_Integration_Manager {

	/**
	 * @var array Array holding all integration instances
	 */
	private $integrations = array();


	/**
	 * @var array
	 */
	public $default_integrations = array(
		'comment_form' => 'Comment_Form',
		'registration_form' => 'Registration_Form',
		'buddypress'  => 'BuddyPress',
		'woocommerce'  => 'WooCommerce',
		'easy_digital_downloads'  => 'Easy_Digital_Downloads',
		'contact_form_7'  => 'Contact_Form_7',
		'events_manager'  => 'Events_Manager',
		'custom'  => 'Custom',
	);

	/**
	 * @var array
	 */
	public $registered_integrations = array();

	/**
	 * @var array Array of checkbox options
	 */
	private $options;

	/**
	 * @var MC4WP_Integration_Manager
	 */
	public static $instance;

	/**
	* Constructor
	*/
	public function __construct() {
		$this->options = mc4wp_get_options( 'integrations' );
		$this->registered_integrations = $this->get_registered_integrations();
	}

	/**
	 * @return array
	 */
	protected function get_registered_integrations() {
		$integrations = array();

		// convert classnames of default integrations
		foreach( $this->default_integrations as $key => $classname ) {
			$integrations[ $key ] = sprintf( 'MC4WP_%s_Integration', $classname );
		}

		return (array) apply_filters( 'mc4wp_integrations', $integrations );
	}

	/**
	 * @param $integration
	 *
	 * @return bool
	 */
	public function is_enabled( $integration ) {
		return ( ! empty( $this->options[ $integration ]['enabled'] ) );
	}

	/**
	 * @return MC4WP_Integration_Manager
	 */
	public static function instance() {

		if( self::$instance instanceof self ) {
			return self::$instance;
		}

		self::$instance = new self;
		return self::$instance;
	}


	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'template_redirect', array( $this, 'init_asset_manager' ) );

		/** @var MC4WP_Integration $integration */
		foreach( $this->integrations as $integration ) {
			$integration->add_hooks();
		}
	}

	/**
	 * Initialize the Asset Manager class
	 *
	 * @hooked `template_redirect`
	 */
	public function init_asset_manager() {
		$asset_manager = new MC4WP_Integrations_Asset_Manager( $this->options['general'] );
		$asset_manager->add_hooks();
	}


	/**
	 * Get an integration instance
	 *
	 * @param string $slug
	 * @return MC4WP_Integration
	 * @throws Exception
	 */
	public function integration( $slug ) {

		if( ! array_key_exists( $slug, $this->registered_integrations ) ) {
			throw new Exception( sprintf( "No integration with slug %s has been registered.", $slug ) );
		}

		// find instance of integration
		if( isset( $this->integrations[ $slug ] ) ) {
			return $this->integrations[ $slug ];
		}

		// create new instance
		$classname = $this->registered_integrations[ $slug ];
		$this->integrations[ $slug ] = $instance = new $classname;

		return $instance;
	}
}