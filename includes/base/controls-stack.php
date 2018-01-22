<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Controls Stack.
 *
 * A base abstract class that provides the needed properties and methods to
 * manage and handle controls in the editor panel to inheriting classes.
 *
 * @since 1.4.0
 * @abstract
 */
abstract class Controls_Stack {

	/**
	 * Responsive 'desktop' device name.
	 */
	const RESPONSIVE_DESKTOP = 'desktop';

	/**
	 * Responsive 'tablet' device name.
	 */
	const RESPONSIVE_TABLET = 'tablet';

	/**
	 * Responsive 'mobile' device name.
	 */
	const RESPONSIVE_MOBILE = 'mobile';

	/**
	 * Generic ID.
	 *
	 * Holds the unique ID.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $_id;

	/**
	 * Parsed Settings.
	 *
	 * Holds the settings, which is the data entered by the user and processed
	 * by elementor.
	 *
	 * @access private
	 *
	 * @var null|array
	 */
	private $_settings;

	/**
	 * Raw Data.
	 *
	 * Holds all the raw data including the element type, the child elements,
	 * the user data.
	 *
	 * @access private
	 *
	 * @var null|array
	 */
	private $_data;

	/**
	 * The configuration.
	 *
	 * Holds the configuration used to generate the Elementor editor. It includes
	 * the element name, icon, categories ect...
	 *
	 * @access private
	 *
	 * @var null|array
	 */
	private $_config;

	/**
	 * Current section.
	 *
	 * Holds the current section while inserting a set of controls sections.
	 *
	 * @access private
	 *
	 * @var null|array
	 */
	private $_current_section;

	/**
	 * Current tab.
	 *
	 * Holds the current tab while inserting a set of controls tabs.
	 *
	 * @access private
	 *
	 * @var null|array
	 */
	private $_current_tab;

	/**
	 * Current popover.
	 *
	 * Holds the current popover while inserting a set of controls.
	 *
	 * @access private
	 *
	 * @var null|array
	 */
	private $current_popover;

	/**
	 * Injection point.
	 *
	 * Holds the injection point in the stack where the control will be inserted.
	 *
	 * @access private
	 *
	 * @var null|array
	 */
	private $injection_point;

	/**
	 * Get element name.
	 *
	 * Retrieve the element name.
	 *
	 * @since 1.4.0
	 * @access public
	 * @abstract
	 *
	 * @return string The name.
	 */
	abstract public function get_name();

	/**
	 * Get unique name.
	 *
	 * Some classes need to use unique names, this method allows you to create
	 * them. By default it retrieves the regular name.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return string Unique name.
	 */
	public function get_unique_name() {
		return $this->get_name();
	}

	/**
	 * Get element ID.
	 *
	 * Retrieve the element generic ID.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @return string The ID.
	 */
	public function get_id() {
		return $this->_id;
	}

	/**
	 * Get element ID.
	 *
	 * Retrieve the element generic ID as integer.
	 *
	 * @since 1.8.0
	 * @access public
	 *
	 * @return string The converted ID.
	 */
	public function get_id_int() {
		return hexdec( $this->_id );
	}

	/**
	 * Get the type.
	 *
	 * Retrieve the type, e.g. 'stack', 'element', 'widget' etc.
	 *
	 * @since 1.4.0
	 * @access public
	 * @static
	 *
	 * @return string The type.
	 */
	public static function get_type() {
		return 'stack';
	}

	/**
	 * Retrieve items.
	 *
	 * Utility method that receives an array with a needle and returns all the
	 * items that match the needle. If needle is not defined the entire haystack
	 * will be returned.
	 *
	 * @since 1.4.0
	 * @access private
	 * @static
	 *
	 * @param array  $haystack An array of items.
	 * @param string $needle   Optional. Needle. Default is null.
	 *
	 * @return mixed The whole haystack or the needle from the haystack when requested.
	 */
	private static function _get_items( array $haystack, $needle = null ) {
		if ( $needle ) {
			return isset( $haystack[ $needle ] ) ? $haystack[ $needle ] : null;
		}

		return $haystack;
	}

	/**
	 * Get current section.
	 *
	 * When inserting new controls, this method will retrieve the current section.
	 *
	 * @since 1.7.1
	 * @access public
	 *
	 * @return null|array Current section.
	 */
	public function get_current_section() {
		return $this->_current_section;
	}

	/**
	 * Get current tab.
	 *
	 * When inserting new controls, this method will retrieve the current tab.
	 *
	 * @since 1.7.1
	 * @access public
	 *
	 * @return null|array Current tab.
	 */
	public function get_current_tab() {
		return $this->_current_tab;
	}

	/**
	 * Get controls.
	 *
	 * Retrieve all the controls or, when requested, a specific control.
 	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $control_id The ID of the requested control. Optional field,
	 *                           when set it will return a specific control.
	 *                           Default is null.
	 *
	 * @return mixed Controls list.
	 */
	public function get_controls( $control_id = null ) {
		return self::_get_items( $this->get_stack()['controls'], $control_id );
	}

	/**
	 * Get active controls.
	 *
	 * Retrieve an array of all the active controls that meet the condition field.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @return array Active controls.
	 */
	public function get_active_controls() {
		$controls = $this->get_controls();

		$settings = $this->get_controls_settings();

		$active_controls = array_reduce(
			array_keys( $controls ), function( $active_controls, $control_key ) use ( $controls, $settings ) {
				$control = $controls[ $control_key ];

				if ( $this->is_control_visible( $control, $settings ) ) {
					$active_controls[ $control_key ] = $control;
				}

				return $active_controls;
			}, []
		);

		return $active_controls;
	}

	/**
	 * Get controls settings.
	 *
	 * Retrieve the settings for all the controls that represent them.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return array Controls settings.
	 */
	public function get_controls_settings() {
		return array_intersect_key( $this->get_settings(), $this->get_controls() );
	}

	/**
	 * Add new control to stack.
	 *
	 * Register a single control to allow the user to set/update data.
	 *
	 * This method should be used inside `_register_controls()`.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $id      Control ID.
	 * @param array  $args    Control arguments.
	 * @param array  $options Optional. Control options. Default is an empty array.
	 *
	 * @return bool True if control added, False otherwise.
	 */
	public function add_control( $id, array $args, $options = [] ) {
		$default_options = [
			'overwrite' => false,
			'position' => null,
		];

		$options = array_merge( $default_options, $options );

		if ( $options['position'] ) {
			$this->start_injection( $options['position'] );
		}

		if ( $this->injection_point ) {
			$options['index'] = $this->injection_point['index']++;
		}

		if ( empty( $args['type'] ) || ! in_array( $args['type'], [ Controls_Manager::SECTION, Controls_Manager::WP_WIDGET ] ) ) {
			$target_section_args = $this->_current_section;

			$target_tab = $this->_current_tab;

			if ( $this->injection_point ) {
				$target_section_args = $this->injection_point['section'];

				if ( ! empty( $this->injection_point['tab'] ) ) {
					$target_tab = $this->injection_point['tab'];
				}
			}

			if ( null !== $target_section_args ) {
				if ( ! empty( $args['section'] ) || ! empty( $args['tab'] ) ) {
					_doing_it_wrong( get_called_class() . '::' . __FUNCTION__, 'Cannot redeclare control with `tab` or `section` args inside section. - ' . esc_html( $id ), '1.0.0' );
				}

				$args = array_replace_recursive( $target_section_args, $args );

				if ( null !== $target_tab ) {
					$args = array_merge( $args, $target_tab );
				}
			} elseif ( empty( $args['section'] ) && ( ! $options['overwrite'] || is_wp_error( Plugin::$instance->controls_manager->get_control_from_stack( $this->get_unique_name(), $id ) ) ) ) {
				wp_die( sprintf( '%1$s::%2$s: Cannot add a control outside of a section (use `start_controls_section`).', get_called_class(), __FUNCTION__ ) );
			}
		}

		if ( $options['position'] ) {
			$this->end_injection();
		}

		unset( $options['position'] );

		if ( $this->current_popover && ! $this->current_popover['initialized'] ) {
			$args['popover'] = [
				'start' => true,
			];

			$this->current_popover['initialized'] = true;
		}

		return Plugin::$instance->controls_manager->add_control_to_stack( $this, $id, $args, $options );
	}

	/**
	 * Remove control from stack.
	 *
	 * Unregister an existing control and remove it from the stack.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $control_id Control ID.
	 *
	 * @return bool|\WP_Error
	 */
	public function remove_control( $control_id ) {
		return Plugin::$instance->controls_manager->remove_control_from_stack( $this->get_unique_name(), $control_id );
	}

	/**
	 * Update control in stack.
	 *
	 * Change the value of an existing control in the stack. When you add new
	 * control you set the `$args` parameter, this method allows you to update
	 * the arguments by passing new data.
	 *
	 * @since 1.4.0
	 * @since 1.8.1 New `$options` parameter added.
	 *
	 * @access public
	 *
	 * @param string $control_id Control ID.
	 * @param array  $args       Control arguments. Only the new fields you want
	 *                           to update.
	 * @param array  $options    Optional. Some additional options. Default is
	 *                           an empty array.
	 *
	 * @return bool
	 */
	public function update_control( $control_id, array $args, array $options = [] ) {
		$is_updated = Plugin::$instance->controls_manager->update_control_in_stack( $this, $control_id, $args, $options );

		if ( ! $is_updated ) {
			return false;
		}

		$control = $this->get_controls( $control_id );

		if ( Controls_Manager::SECTION === $control['type'] ) {
			$section_args = $this->get_section_args( $control_id );

			$section_controls = $this->get_section_controls( $control_id );

			foreach ( $section_controls as $section_control_id => $section_control ) {
				$this->update_control( $section_control_id, $section_args );
			}
		}

		return true;
	}

	/**
	 * Get stack.
	 *
	 * Retrieve the stack of controls.
	 *
	 * @since 1.9.2
	 * @access public
	 *
	 * @return array Stack of controls.
	 */
	public function get_stack() {
		$stack = Plugin::$instance->controls_manager->get_element_stack( $this );

		if ( null === $stack ) {
			$this->init_controls();

			return $this->get_stack();
		}

		return $stack;
	}

	/**
	 * Get position information.
	 *
	 * Retrieve the position while injecting data, based on the element type.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param array $position {
	 *     The injection position.
	 *
	 *     @type string $type Injection type, either `control` or `section`.
	 *                        Default is `control`.
	 *     @type string $at   Where to inject. If `$type` is `control` accepts
	 *                        `before` and `after`. If `$type` is `section`
	 *                        accepts `start` and `end`. Default values based on
	 *                        the `type`.
	 *     @type string $of   Control/Section ID.
	 * }
	 *
	 * @return bool|array Position info.
	 */
	final public function get_position_info( array $position ) {
		$default_position = [
			'type' => 'control',
			'at' => 'after',
		];

		if ( ! empty( $position['type'] ) && 'section' === $position['type'] ) {
			$default_position['at'] = 'end';
		}

		$position = array_merge( $default_position, $position );

		if (
			'control' === $position['type'] && in_array( $position['at'], [ 'start', 'end' ] ) ||
			'section' === $position['type'] && in_array( $position['at'], [ 'before', 'after' ] )
		) {
			_doing_it_wrong( get_called_class() . '::' . __FUNCTION__, 'Invalid position arguments. Use `before` / `after` for control or `start` / `end` for section.', '1.7.0' );

			return false;
		}

		$target_control_index = $this->get_control_index( $position['of'] );

		if ( false === $target_control_index ) {
			return false;
		}

		$target_section_index = $target_control_index;

		$registered_controls = $this->get_controls();

		$controls_keys = array_keys( $registered_controls );

		while ( Controls_Manager::SECTION !== $registered_controls[ $controls_keys[ $target_section_index ] ]['type'] ) {
			$target_section_index--;
		}

		if ( 'section' === $position['type'] ) {
			$target_control_index++;

			if ( 'end' === $position['at'] ) {
				while ( Controls_Manager::SECTION !== $registered_controls[ $controls_keys[ $target_control_index ] ]['type'] ) {
					if ( ++$target_control_index >= count( $registered_controls ) ) {
						break;
					}
				}
			}
		}

		$target_control = $registered_controls[ $controls_keys[ $target_control_index ] ];

		if ( 'after' === $position['at'] ) {
			$target_control_index++;
		}

		$section_id = $registered_controls[ $controls_keys[ $target_section_index ] ]['name'];

		$position_info = [
			'index' => $target_control_index,
			'section' => $this->get_section_args( $section_id ),
		];

		if ( ! empty( $target_control['tabs_wrapper'] ) ) {
			$position_info['tab'] = [
				'tabs_wrapper' => $target_control['tabs_wrapper'],
				'inner_tab' => $target_control['inner_tab'],
			];
		}

		return $position_info;
	}

	/**
	 * Get control key.
	 *
	 * Retrieve the key of the control based on a given index of the control.
	 *
	 * @since 1.9.2
	 * @access public
	 *
	 * @param string $control_index Control index.
	 *
	 * @return int Control key.
	 */
	final public function get_control_key( $control_index ) {
		$registered_controls = $this->get_controls();

		$controls_keys = array_keys( $registered_controls );

		return $controls_keys[ $control_index ];
	}

	/**
	 * Get control index.
	 *
	 * Retrieve the index of the control based on a given key of the control.
	 *
	 * @since 1.7.6
	 * @access public
	 *
	 * @param string $control_key Control key.
	 *
	 * @return false|int Control index.
	 */
	final public function get_control_index( $control_key ) {
		$controls = $this->get_controls();

		$controls_keys = array_keys( $controls );

		return array_search( $control_key, $controls_keys );
	}

	/**
	 * Get section controls.
	 *
	 * Retrieve all controls under a specific section.
	 *
	 * @since 1.7.6
	 * @access public
	 *
	 * @param string $section_id Section ID.
	 *
	 * @return array Section controls
	 */
	final public function get_section_controls( $section_id ) {
		$section_index = $this->get_control_index( $section_id );

		$section_controls = [];

		$registered_controls = $this->get_controls();

		$controls_keys = array_keys( $registered_controls );

		while ( true ) {
			$section_index++;

			if ( ! isset( $controls_keys[ $section_index ] ) ) {
				break;
			}

			$control_key = $controls_keys[ $section_index ];

			if ( Controls_Manager::SECTION === $registered_controls[ $control_key ]['type'] ) {
				break;
			}

			$section_controls[ $control_key ] = $registered_controls[ $control_key ];
		};

		return $section_controls;
	}

	/**
	 * Add new group control to stack.
	 *
	 * Register a set of related controls grouped together as a single unified
	 * control. For example grouping together like typography controls into a
	 * single, easy-to-use control.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $group_name Group control name.
	 * @param array  $args       {
	 *     Group control arguments. Default is an empty array.
	 *
	 *     @type string $name      Base Control name.
	 *     @type string $selector  CSS Selector
	 *     @type string $scheme    Globel scheme to be used.
	 *     @type array  $condition Display control based on predefined conditional
	 *                             logic.
	 * }
	 * @param array  $options    Optional. Group control options. Default is an
	 *                           empty array.
	 */
	final public function add_group_control( $group_name, array $args = [], array $options = [] ) {
		$group = Plugin::$instance->controls_manager->get_control_groups( $group_name );

		if ( ! $group ) {
			wp_die( sprintf( '%1$s::%2$s: Group `%3$s` not found.', get_called_class(), __FUNCTION__, $group_name ) );
		}

		$group->add_controls( $this, $args, $options );
	}

	/**
	 * Get scheme controls.
	 *
	 * Retrieve all the controls that use schemes.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @return array Scheme controls.
	 */
	final public function get_scheme_controls() {
		$enabled_schemes = Schemes_Manager::get_enabled_schemes();

		return array_filter(
			$this->get_controls(), function( $control ) use ( $enabled_schemes ) {
				return ( ! empty( $control['scheme'] ) && in_array( $control['scheme']['type'], $enabled_schemes ) );
			}
		);
	}

	/**
	 * Get style controls.
	 *
	 * Retrieve style controls for all active controls or, when requested, from
	 * a specific set of controls.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param array $controls Optional. Controls list. Default is null.
	 *
	 * @return array Style controls.
	 */
	final public function get_style_controls( $controls = null ) {
		if ( null === $controls ) {
			$controls = $this->get_active_controls();
		}

		$style_controls = [];

		foreach ( $controls as $control_name => $control ) {
			$control_obj = Plugin::$instance->controls_manager->get_control( $control['type'] );

			if ( ! $control_obj instanceof Base_Data_Control ) {
				continue;
			}

			$control = array_merge( $control_obj->get_settings(), $control );

			if ( Controls_Manager::REPEATER === $control['type'] ) {
				$control['style_fields'] = $this->get_style_controls( $control['fields'] );
			}

			if ( ! empty( $control['selectors'] ) || ! empty( $control['dynamic'] ) || ! empty( $control['style_fields'] ) ) {
				$style_controls[ $control_name ] = $control;
			}
		}

		return $style_controls;
	}

	/**
	 * Get class controls.
	 *
	 * Retrieve the controls that use the same prefix class from all the active
	 * controls
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @return array Class controls.
	 */
	final public function get_class_controls() {
		return array_filter(
			$this->get_active_controls(), function( $control ) {
				return ( isset( $control['prefix_class'] ) );
			}
		);
	}

	/**
	 * Get tabs controls.
	 *
	 * Retrieve all the tabs assigned to the control.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @return array Tabs controls.
	 */
	final public function get_tabs_controls() {
		return $this->get_stack()['tabs'];
	}

	/**
	 * Add new responsive control to stack.
	 *
	 * Register a set of controls to allow editing based on user screen size.
	 * This method registers three screen sizes: Desktop, Tablet and Mobile.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $id      Responsive control ID.
	 * @param array  $args    Responsive control arguments.
	 * @param array  $options Optional. Responsive control options. Default is
	 *                        an empty array.
	 */
	final public function add_responsive_control( $id, array $args, $options = [] ) {
		$args['responsive'] = [];

		$devices = [
			self::RESPONSIVE_DESKTOP,
			self::RESPONSIVE_TABLET,
			self::RESPONSIVE_MOBILE,
		];

		if ( isset( $args['devices'] ) ) {
			$devices = array_intersect( $devices, $args['devices'] );

			$args['responsive']['devices'] = $devices;

			unset( $args['devices'] );
		}

		if ( isset( $args['default'] ) ) {
			$args['desktop_default'] = $args['default'];

			unset( $args['default'] );
		}

		foreach ( $devices as $device_name ) {
			$control_args = $args;

			if ( isset( $control_args['device_args'] ) ) {
				if ( ! empty( $control_args['device_args'][ $device_name ] ) ) {
					$control_args = array_merge( $control_args, $control_args['device_args'][ $device_name ] );
				}

				unset( $control_args['device_args'] );
			}

			if ( ! empty( $args['prefix_class'] ) ) {
				$device_to_replace = self::RESPONSIVE_DESKTOP === $device_name ? '' : '-' . $device_name;

				$control_args['prefix_class'] = sprintf( $args['prefix_class'], $device_to_replace );
			}

			$control_args['responsive']['max'] = $device_name;

			if ( isset( $control_args['min_affected_device'] ) ) {
				if ( ! empty( $control_args['min_affected_device'][ $device_name ] ) ) {
					$control_args['responsive']['min'] = $control_args['min_affected_device'][ $device_name ];
				}

				unset( $control_args['min_affected_device'] );
			}

			if ( isset( $control_args[ $device_name . '_default' ] ) ) {
				$control_args['default'] = $control_args[ $device_name . '_default' ];
			}

			unset( $control_args['desktop_default'] );
			unset( $control_args['tablet_default'] );
			unset( $control_args['mobile_default'] );

			$id_suffix = self::RESPONSIVE_DESKTOP === $device_name ? '' : '_' . $device_name;

			if ( ! empty( $options['overwrite'] ) ) {
				$this->update_control( $id . $id_suffix, $control_args, [ 'recursive' => ! empty( $options['recursive'] ) ] );
			} else {
				$this->add_control( $id . $id_suffix, $control_args, $options );
			}
		}
	}

	/**
	 * Update responsive control in stack.
	 *
	 * Change the value of an existing responsive control in the stack. When you
	 * add new control you set the `$args` parameter, this method allows you to
	 * update the arguments by passing new data.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $id      Responsive control ID.
	 * @param array  $args    Responsive control arguments.
	 * @param array  $options Optional. Additional options.
	 */
	final public function update_responsive_control( $id, array $args, array $options = [] ) {
		$this->add_responsive_control( $id, $args, [ 'overwrite' => true, 'recursive' => ! empty( $options['recursive'] ) ] );
	}

	/**
	 * Remove responsive control from stack.
	 *
	 * Unregister an existing responsive control and remove it from the stack.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $id Responsive control ID.
	 */
	final public function remove_responsive_control( $id ) {
		$devices = [
			self::RESPONSIVE_DESKTOP,
			self::RESPONSIVE_TABLET,
			self::RESPONSIVE_MOBILE,
		];

		foreach ( $devices as $device_name ) {
			$id_suffix = self::RESPONSIVE_DESKTOP === $device_name ? '' : '_' . $device_name;

			$this->remove_control( $id . $id_suffix );
		}
	}

	/**
	 * Get class name.
	 *
	 * Retrieve the name of the current class.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @return string Class name.
	 */
	final public function get_class_name() {
		return get_called_class();
	}

	/**
	 * Get the config.
	 *
	 * Retrieve the config or, if non set, use the initial config.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @return array|null The config.
	 */
	final public function get_config() {
		if ( null === $this->_config ) {
			$this->_config = $this->_get_initial_config();
		}

		return $this->_config;
	}

	/**
	 * Get frontend settings keys.
	 *
	 * Retrieve settings keys for all frontend controls.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return array Settings keys for each control.
	 */
	final public function get_frontend_settings_keys() {
		$controls = [];

		foreach ( $this->get_controls() as $control ) {
			if ( ! empty( $control['frontend_available'] ) ) {
				$controls[] = $control['name'];
			}
		}

		return $controls;
	}

	/**
	 * Get controls pointer index.
	 *
	 * Retrieve pointer index where the next control should be added.
	 *
	 * While using injection point, it will return the injection point index.
	 * Otherwise index of the last control plus one.
	 *
	 * @since 1.9.2
	 * @access public
	 *
	 * @return int Controls pointer index.
	 */
	public function get_pointer_index() {
		if ( null !== $this->injection_point ) {
			return $this->injection_point['index'];
		}

		return count( $this->get_controls() );
	}

	/**
	 * Get the raw data.
	 *
	 * Retrieve all the items or, when requested, a specific item.
 	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $item Optional. The requested item. Default is null.
	 *
	 * @return mixed The raw data.
	 */
	public function get_data( $item = null ) {
		return self::_get_items( $this->_data, $item );
	}

	/**
	 * Get the settings.
	 *
	 * Retrieve all the settings or, when requested, a specific setting.
 	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $setting Optional. The requested setting. Default is null.
	 *
	 * @return mixed The settings.
	 */
	public function get_settings( $setting = null ) {
		return self::_get_items( $this->_settings, $setting );
	}

	/**
	 * Get active settings.
	 *
	 * Retrieve the settings from all the active controls.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @return array Active settings.
	 */
	public function get_active_settings() {
		$settings = $this->get_settings();

		$active_settings = array_intersect_key( $settings, $this->get_active_controls() );

		$settings_mask = array_fill_keys( array_keys( $settings ), null );

		return array_merge( $settings_mask, $active_settings );
	}

	public function get_settings_for_display( $setting_key = null ) {
		if ( $setting_key ) {
			$settings = [ $setting_key => $this->get_settings( $setting_key ) ];
		} else {
			$settings = $this->get_active_settings();
		}

		$parsed_settings = $this->parse_dynamic_settings( $settings );

		if ( $setting_key ) {
			return $parsed_settings[ $setting_key ];
		}

		return $parsed_settings;
	}

	public function parse_dynamic_settings( $settings, $controls = null, $all_settings = null ) {
		if ( null === $all_settings ) {
			$all_settings = $this->get_settings();
		}

		if ( null === $controls ) {
			$controls = $this->get_controls();
		}

		foreach ( $controls as $control ) {
			if ( ! isset( $settings[ $control['name'] ] ) || null === $settings[ $control['name'] ] ) {
				continue;
			}

			$control_obj = Plugin::$instance->controls_manager->get_control( $control['type'] );

			if ( ! $control_obj instanceof Base_Data_Control ) {
				continue;
			}

			if ( 'repeater' === $control_obj->get_type() ) {
				foreach ( $settings[ $control['name'] ] as & $field ) {
					$field = $this->parse_dynamic_settings( $field, $control['fields'], $field );
				}

				continue;
			}

			$control = array_merge( $control_obj->get_settings(), $control );

			if ( ! empty( $control['dynamic'] ) && ! empty( $all_settings[ 'dynamic_' . $control['name'] ] ) ) {
				$settings[ $control['name'] ] = $control_obj->parse_tags( $settings[ $control['name'] ], $control['dynamic'] );
			}
		}

		return $settings;
	}

	/**
	 * Get frontend settings.
	 *
	 * Retrieve the settings for all frontend controls.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return array Frontend settings.
	 */
	public function get_frontend_settings() {
		$frontend_settings = array_intersect_key( $this->get_active_settings(), array_flip( $this->get_frontend_settings_keys() ) );

		foreach ( $frontend_settings as $key => $setting ) {
			if ( in_array( $setting, [ null, '' ], true ) ) {
				unset( $frontend_settings[ $key ] );
			}
		}

		return $frontend_settings;
	}

	/**
	 * Filter controls settings.
	 *
	 * Receives controls, settings and a callback function to filter the settings by
	 * and returns filtered settings.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param callable $callback The callback function.
	 * @param array    $settings Optional. Control settings. Default is an empty
	 *                           array.
	 * @param array    $controls Optional. Controls list. Default is an empty
	 *                           array.
	 *
	 * @return array Filtered settings.
	 */
	public function filter_controls_settings( callable $callback, array $settings = [], array $controls = [] ) {
		if ( ! $settings ) {
			$settings = $this->get_settings();
		}

		if ( ! $controls ) {
			$controls = $this->get_controls();
		}

		return array_reduce(
			array_keys( $settings ), function( $filtered_settings, $setting_key ) use ( $controls, $settings, $callback ) {
				if ( isset( $controls[ $setting_key ] ) ) {
					$result = $callback( $settings[ $setting_key ], $controls[ $setting_key ] );

					if ( null !== $result ) {
						$filtered_settings[ $setting_key ] = $result;
					}
				}

				return $filtered_settings;
			}, []
		);
	}

	/**
	 * Whether the control is visible or not.
	 *
	 * Used to determine whether the control is visible or not.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param array $control The control.
	 * @param array $values  Optional. Condition values. Default is null.
	 *
	 * @return bool Whether the control is visible.
	 */
	public function is_control_visible( $control, $values = null ) {
		if ( null === $values ) {
			$values = $this->get_settings();
		}

		// Repeater fields
		if ( ! empty( $control['conditions'] ) ) {
			return Conditions::check( $control['conditions'], $values );
		}

		if ( empty( $control['condition'] ) ) {
			return true;
		}

		foreach ( $control['condition'] as $condition_key => $condition_value ) {
			preg_match( '/([a-z_0-9]+)(?:\[([a-z_]+)])?(!?)$/i', $condition_key, $condition_key_parts );

			$pure_condition_key = $condition_key_parts[1];
			$condition_sub_key = $condition_key_parts[2];
			$is_negative_condition = ! ! $condition_key_parts[3];

			if ( ! isset( $values[ $pure_condition_key ] ) || null === $values[ $pure_condition_key ] ) {
				return false;
			}

			$instance_value = $values[ $pure_condition_key ];

			if ( $condition_sub_key ) {
				if ( ! isset( $instance_value[ $condition_sub_key ] ) ) {
					return false;
				}

				$instance_value = $instance_value[ $condition_sub_key ];
			}

			/**
			 * If the $condition_value is a non empty array - check if the $condition_value contains the $instance_value,
			 * If the $instance_value is a non empty array - check if the $instance_value contains the $condition_value
			 * otherwise check if they are equal. ( and give the ability to check if the value is an empty array )
			 */
			if ( is_array( $condition_value ) && ! empty( $condition_value ) ) {
				$is_contains = in_array( $instance_value, $condition_value );
			} elseif ( is_array( $instance_value ) && ! empty( $instance_value ) ) {
				$is_contains = in_array( $condition_value, $instance_value );
			} else {
				$is_contains = $instance_value === $condition_value;
			}

			if ( $is_negative_condition && $is_contains || ! $is_negative_condition && ! $is_contains ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Start controls section.
	 *
	 * Used to add a new section of controls. When you use this method, all the
	 * registered controls from this point will be assigned to this section,
	 * until you close the section using `end_controls_section()` method.
	 *
	 * This method should be used inside `_register_controls()`.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $section_id Section ID.
	 * @param array  $args       Section arguments.
	 */
	public function start_controls_section( $section_id, array $args ) {
		$section_name = $this->get_name();

		/**
		 * Before section start.
		 *
		 * Fires before Elementor section starts in the editor panel.
		 *
		 * @since 1.4.0
		 *
		 * @param Controls_Stack $this       The control.
		 * @param string         $section_id Section ID.
		 * @param array          $args       Section arguments.
		 */
		do_action( 'elementor/element/before_section_start', $this, $section_id, $args );

		/**
		 * Before section start.
		 *
		 * Fires before Elementor section starts in the editor panel.
		 *
		 * The dynamic portions of the hook name, `$section_name` and `$section_id`, refers to the section name and section ID, respectively.
		 *
		 * @since 1.4.0
		 *
		 * @param Controls_Stack $this The control.
		 * @param array          $args Section arguments.
		 */
		do_action( "elementor/element/{$section_name}/{$section_id}/before_section_start", $this, $args );

		$args['type'] = Controls_Manager::SECTION;

		$this->add_control( $section_id, $args );

		if ( null !== $this->_current_section ) {
			wp_die( sprintf( 'Elementor: You can\'t start a section before the end of the previous section: `%s`', $this->_current_section['section'] ) ); // XSS ok.
		}

		$this->_current_section = $this->get_section_args( $section_id );

		if ( $this->injection_point ) {
			$this->injection_point['section'] = $this->_current_section;
		}

		/**
		 * After section start.
		 *
		 * Fires after Elementor section starts in the editor panel.
		 *
		 * @since 1.4.0
		 *
		 * @param Controls_Stack $this       The control.
		 * @param string         $section_id Section ID.
		 * @param array          $args       Section arguments.
		 */
		do_action( 'elementor/element/after_section_start', $this, $section_id, $args );

		/**
		 * After section start.
		 *
		 * Fires after Elementor section starts in the editor panel.
		 *
		 * The dynamic portions of the hook name, `$section_name` and `$section_id`, refers to the section name and section ID, respectively.
		 *
		 * @since 1.4.0
		 *
		 * @param Controls_Stack $this The control.
		 * @param array          $args Section arguments.
		 */
		do_action( "elementor/element/{$section_name}/{$section_id}/after_section_start", $this, $args );
	}

	/**
	 * End controls section.
	 *
	 * Used to close an existing open controls section. When you use this method
	 * it stops adding new controls to this section.
	 *
	 * This method should be used inside `_register_controls()`.
	 *
	 * @since 1.4.0
	 * @access public
	 */
	public function end_controls_section() {
		$section_name = $this->get_name();

		// Save the current section for the action.
		$current_section = $this->_current_section;
		$section_id = $current_section['section'];
		$args = [
			'tab' => $current_section['tab'],
		];

		/**
		 * Before section end.
		 *
		 * Fires before Elementor section ends in the editor panel.
		 *
		 * @since 1.4.0
		 *
		 * @param Controls_Stack $this       The control.
		 * @param string         $section_id Section ID.
		 * @param array          $args       Section arguments.
		 */
		do_action( 'elementor/element/before_section_end', $this, $section_id, $args );

		/**
		 * Before section end.
		 *
		 * Fires before Elementor section ends in the editor panel.
		 *
		 * The dynamic portions of the hook name, `$section_name` and `$section_id`, refers to the section name and section ID, respectively.
		 *
		 * @since 1.4.0
		 *
		 * @param Controls_Stack $this The control.
		 * @param array          $args Section arguments.
		 */
		do_action( "elementor/element/{$section_name}/{$section_id}/before_section_end", $this, $args );

		$this->_current_section = null;

		/**
		 * After section end.
		 *
		 * Fires after Elementor section ends in the editor panel.
		 *
		 * @since 1.4.0
		 *
		 * @param Controls_Stack $this       The control.
		 * @param string         $section_id Section ID.
		 * @param array          $args       Section arguments.
		 */
		do_action( 'elementor/element/after_section_end', $this, $section_id, $args );

		/**
		 * After section end.
		 *
		 * Fires after Elementor section ends in the editor panel.
		 *
		 * The dynamic portions of the hook name, `$section_name` and `$section_id`, refers to the section name and section ID, respectively.
		 *
		 * @since 1.4.0
		 *
		 * @param Controls_Stack $this The control.
		 * @param array          $args Section arguments.
		 */
		do_action( "elementor/element/{$section_name}/{$section_id}/after_section_end", $this, $args );
	}

	/**
	 * Start controls tabs.
	 *
	 * Used to add a new set of tabs inside a section. You should use this
	 * method before adding new individual tabs using `start_controls_tab()`.
	 * Each tab added after this point will be assigned to this group of tabs,
	 * until you close it using `end_controls_tabs()` method.
	 *
	 * This method should be used inside `_register_controls()`.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $tabs_id Tabs ID.
	 */
	public function start_controls_tabs( $tabs_id ) {
		if ( null !== $this->_current_tab ) {
			wp_die( sprintf( 'Elementor: You can\'t start tabs before the end of the previous tabs: `%s`', $this->_current_tab['tabs_wrapper'] ) ); // XSS ok.
		}

		$this->add_control(
			$tabs_id,
			[
				'type' => Controls_Manager::TABS,
			]
		);

		$this->_current_tab = [
			'tabs_wrapper' => $tabs_id,
		];

		if ( $this->injection_point ) {
			$this->injection_point['tab'] = $this->_current_tab;
		}
	}

	/**
	 * End controls tabs.
	 *
	 * Used to close an existing open controls tabs. When you use this method it
	 * stops adding new controls to this tabs.
	 *
	 * This method should be used inside `_register_controls()`.
	 *
	 * @since 1.4.0
	 * @access public
	 */
	public function end_controls_tabs() {
		$this->_current_tab = null;
	}

	/**
	 * Start controls tab.
	 *
	 * Used to add a new tab inside a group of tabs. Use this method before
	 * adding new individual tabs using `start_controls_tab()`.
	 * Each tab added after this point will be assigned to this group of tabs,
	 * until you close it using `end_controls_tab()` method.
	 *
	 * This method should be used inside `_register_controls()`.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $tab_id Tab ID.
	 * @param array  $args   Tab arguments.
	 */
	public function start_controls_tab( $tab_id, $args ) {
		if ( ! empty( $this->_current_tab['inner_tab'] ) ) {
			wp_die( sprintf( 'Elementor: You can\'t start a tab before the end of the previous tab: `%s`', $this->_current_tab['inner_tab'] ) ); // XSS ok.
		}

		$args['type'] = Controls_Manager::TAB;
		$args['tabs_wrapper'] = $this->_current_tab['tabs_wrapper'];

		$this->add_control( $tab_id, $args );

		$this->_current_tab['inner_tab'] = $tab_id;

		if ( $this->injection_point ) {
			$this->injection_point['tab']['inner_tab'] = $this->_current_tab['inner_tab'];
		}
	}

	/**
	 * End controls tab.
	 *
	 * Used to close an existing open controls tab. When you use this method it
	 * stops adding new controls to this tab.
	 *
	 * This method should be used inside `_register_controls()`.
	 *
	 * @since 1.4.0
	 * @access public
	 */
	public function end_controls_tab() {
		unset( $this->_current_tab['inner_tab'] );
	}

	/**
	 * Start popover.
	 *
	 * Used to add a new set of controls in a popover. When you use this method,
	 * all the registered controls from this point will be assigned to this
	 * popover, until you close the popover using `end_popover()` method.
	 *
	 * This method should be used inside `_register_controls()`.
	 *
	 * @since 1.9.0
	 * @access public
	 */
	final public function start_popover() {
		$this->current_popover = [
			'initialized' => false,
		];
	}

	/**
	 * End popover.
	 *
	 * Used to close an existing open popover. When you use this method it stops
	 * adding new controls to this popover.
	 *
	 * This method should be used inside `_register_controls()`.
	 *
	 * @since 1.9.0
	 * @access public
	 */
	final public function end_popover() {
		$this->current_popover = null;

		$last_control_key = $this->get_control_key( $this->get_pointer_index() - 1 );

 		$this->update_control( $last_control_key, [
			'popover' => [
				'end' => true,
			],
		], [ 'recursive' => true ] );
	}

	/**
	 * Print element template.
	 *
	 * Used to generate the element template on the editor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_template() {
		ob_start();

		$this->_content_template();

		$template_content = ob_get_clean();

		/**
		 * Filters the controls stack template before it's printed in the editor.
		 *
		 * @since 1.0.0
		 *
		 * @param string         $content_template The controls stack template in the editor.
		 * @param Controls_Stack $this             The controls stack.
		 */
		$template_content = apply_filters( 'elementor/' . $this->get_type() . '/print_template', $template_content, $this );

		if ( empty( $template_content ) ) {
			return;
		}
		?>
		<script type="text/html" id="tmpl-elementor-<?php echo $this->get_type(); ?>-<?php echo esc_attr( $this->get_name() ); ?>-content">
			<?php $this->print_template_content( $template_content ); ?>
		</script>
		<?php
	}

	/**
	 * Start injection.
	 *
	 * Used to inject controls and sections to a specific position in the stack.
	 *
	 * When you use this method, all the registered controls and sections will
	 * be injected to a specific position in the stack, until you stop the
	 * injection using `end_injection()` method.
	 *
	 * @since 1.7.1
	 * @access public
	 *
	 * @param array $position {
	 *     The position where to start the injection.
	 *
	 *     @type string $type Injection type, either `control` or `section`.
	 *                        Default is `control`.
	 *     @type string $at   Where to inject. If `$type` is `control` accepts
	 *                        `before` and `after`. If `$type` is `section`
	 *                        accepts `start` and `end`. Default values based on
	 *                        the `type`.
	 *     @type string $of   Control/Section ID.
	 * }
	 */
	final public function start_injection( array $position ) {
		if ( $this->injection_point ) {
			wp_die( 'A controls injection is already opened. Please close current injection before starting a new one (use `end_injection`).' );
		}

		$this->injection_point = $this->get_position_info( $position );
	}

	/**
	 * End injection.
	 *
	 * Used to close an existing open injection point. When you use this method
	 * it stops adding new controls to this point and continue to add controls
	 * to the regular position in the stack.
	 *
	 * @since 1.7.1
	 * @access public
	 */
	final public function end_injection() {
		$this->injection_point = null;
	}

	/**
	 * @access public
	 * @return array|null
	 */
	final public function get_injection_point() {
		return $this->injection_point;
	}

	/**
	 * Set settings.
	 *
	 * Change or add new settings to an existing control in the stack.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string|array $key   Setting name, or an array of key/value.
	 * @param string|null  $value Optional. Setting value. Optional field if
	 *                            `$key` is an array. Default is null.
	 */
	final public function set_settings( $key, $value = null ) {
		// strict check if override all settings.
		if ( is_array( $key ) ) {
			$this->_settings = $key;
		} else {
			$this->_settings[ $key ] = $value;
		}
	}

	/**
	 * Register controls.
	 *
	 * Used to add new controls to any element type. For example, external
	 * developers use this method to register controls in a widget.
	 *
	 * Should be inherited and register new controls using `add_control()`,
	 * `add_responsive_control()` and `add_group_control()`, inside control
	 * wrappers like `start_controls_section()`, `start_controls_tabs()` and
	 * `start_controls_tab()`.
	 *
	 * @since 1.4.0
	 * @access protected
	 */
	protected function _register_controls() {}

	/**
	 * Get default data.
	 *
	 * Retrieve the default data. Used to reset the data on initialization.
	 *
	 * @since 1.4.0
	 * @access protected
	 *
	 * @return array Default data.
	 */
	protected function get_default_data() {
		return [
			'id' => 0,
			'settings' => [],
		];
	}

	/**
	 * Get parsed settings.
	 *
	 * Retrieve the parsed settings for all the controls that represent them.
	 * The parser set default values and process the settings.
	 *
	 * Classes that extend `Controls_Stack` can add new process to the settings
	 * parser.
	 *
	 * @since 1.4.0
	 * @access protected
	 *
	 * @return array Parsed settings.
	 */
	protected function _get_parsed_settings() {
		$settings = $this->_data['settings'];

		foreach ( $this->get_controls() as $control ) {
			$control_obj = Plugin::$instance->controls_manager->get_control( $control['type'] );

			if ( ! $control_obj instanceof Base_Data_Control ) {
				continue;
			}

			$control = array_merge( $control_obj->get_settings(), $control );

			if (
				isset( $settings[ $control['name'] ] ) &&
			    ! empty( $control['dynamic']['valueController'] ) &&
			    'mentions' === $control['dynamic']['valueController'] &&
				! empty( $settings[ 'dynamic_' . $control['name'] ] )
			)
			{
				continue;
			}

			$settings[ $control['name'] ] = $control_obj->get_value( $control, $settings );
		}

		return $settings;
	}

	/**
	 * Get initial config.
	 *
	 * Retrieve the current element initial configuration - controls list and
	 * the tabs assigned to the control.
	 *
	 * @since 1.4.0
	 * @access protected
	 *
	 * @return array The initial config.
	 */
	protected function _get_initial_config() {
		return [
			'controls' => $this->get_controls(),
			'tabs_controls' => $this->get_tabs_controls(),
		];
	}

	/**
	 * Get section arguments.
	 *
	 * Retrieve the section arguments based on section ID.
	 *
	 * @since 1.4.0
	 * @access protected
	 *
	 * @param string $section_id Section ID.
	 *
	 * @return array Section arguments.
	 */
	protected function get_section_args( $section_id ) {
		$section_control = $this->get_controls( $section_id );

		$section_args_keys = [ 'tab', 'condition' ];

		$args = array_intersect_key( $section_control, array_flip( $section_args_keys ) );

		$args['section'] = $section_id;

		return $args;
	}

	/**
	 * Render element.
	 *
	 * Generates the final HTML on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {}

	protected function print_template_content( $template_content ) {
		echo $template_content;
	}

	/**
	 * Render element output in the editor.
	 *
	 * Used to generate the live preview, using a Backbone JavaScript template.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _content_template() {}

	/**
	 * Initialize controls.
	 *
	 * Register the all controls added by `_register_controls()`.
	 *
	 * @since 1.4.0
	 * @access protected
	 */
	protected function init_controls() {
		Plugin::$instance->controls_manager->open_stack( $this );

		$this->_register_controls();
	}

	/**
	 * Initialize the class.
	 *
	 * Set the raw data, the ID and the parsed settings.
	 *
	 * @since 1.4.0
	 * @access protected
	 */
	protected function _init( $data ) {
		$this->_data = array_merge( $this->get_default_data(), $data );

		$this->_id = $data['id'];

		$this->_settings = $this->_get_parsed_settings();
	}

	/**
	 * Controls stack constructor.
	 *
	 * Initializing the control stack class using `$data`. The `$data` is required
	 * for a normal instance. It is optional only for internal `type instance`.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param array $data Optional. Control stack data. Default is an empty array.
	 */
	public function __construct( array $data = [] ) {
		if ( $data ) {
			$this->_init( $data );
		}
	}
}
