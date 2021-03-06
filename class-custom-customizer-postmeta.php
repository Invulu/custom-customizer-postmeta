<?php
/**
 * Custom Customizer Postmeta
 *
 * @package CustomCustomizerPostmeta
 *
 */

class ACCP_Custom_Customizer_Postmeta {

  /**
	 * Meta key
	 *
	 * @var string
	 */
	public $meta_key;

  /**
	 * Plural version of meta key
	 *
	 * @var string
	 */
	public $plural_meta_key;

  /**
	 * Meta display name
	 *
	 * @var string
	 */
	public $meta_name;

  /**
	 * Field type (text, select, etc...)
	 *
	 * @var string
	 */
	public $field_type;

  /**
	 * Post types (Only single post type supported for now)
	 *
	 * @var array
	 */
	public $post_types;

  /**
	 * Current post type
	 *
	 * @var string
	 */
	public $current_post_type;

  /**
	 * Setting ID pattern
	 *
	 * @var string
	 */
	public $setting_id_pattern;

  /**
	 * Input arguments
	 *
	 * @var array
	 */
	public $input_args;

  /**
   * Choices for 'select' inputs
   *
   * @var string
   */
  public $choices;

  /**
   * Default for checkbox input
   *
   * @var boolean
   */
  public $checkbox_default;

  /**
   * Choices JSON
   *
   * @var string
   */
  public $choices_json;

  /**
	 * Control display priority
	 *
	 * @var int
	 */
	public $display_priority;

  /**
	 * Transport method
	 *
	 * @var string
	 */
	public $transport;

  /**
	 * Control constructor type
	 *
	 * @var string
	 */
	public $control_constructor_type;

  /**
	 * Post meta constructor.
	 *
	 * @access public
	 */
  public function __construct( $args = array() ){

    // Set default variable values
    $this->transport = 'refresh';

    // Set variable values
    $keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

    // Setup Variables
    if ( $this->field_type == 'post_editor' ) {
      $this->control_constructor_type = 'post_editor';
    } else {
      $this->control_constructor_type = 'dynamic';
    }

    $this->setting_id_pattern = '^postmeta\[(.+?)]\[(\d+)]\['.$this->meta_key.']$';
    // Set post type (currently only supporting 1 type, revisit this)
    $this->current_post_type = $this->post_types[0];

    // If select input, setup choices
    if ( $this->field_type == 'select' ) {
        $this->choices = $this->input_args['choices'];
    }

    // If checkbox input, set default
    if ( $this->field_type == 'checkbox' ) {

      $this->checkbox_default = $this->input_args['default'] ? 1 : 0;

    }

    // Make everything happen
    $this->add_post_type_support();
    $this->register_postmeta();
    $this->add_editor_control();
    $this->register_partial();
    $this->recognize_partial();

  }

  // Add post type support for preambles.
  function add_post_type_support(){
    add_action( 'init', function() {
    	add_post_type_support( $this->current_post_type, $this->meta_key );
    }, 10 );
  }

  // Register the preamble postmeta.
  function register_postmeta(){
    add_action( 'customize_posts_register_meta', function ( \WP_Customize_Posts $customize_posts ) {
      foreach ( get_post_types_by_support( $this->meta_key ) as $post_type ) {
        $customize_posts->register_post_type_meta( $post_type, $this->meta_key, array(
          'transport' => $this->transport,
          'sanitize_callback' => function( $value ) {
            return wp_kses_post( $value );
          },
        ) );
      }
    } );
  }

  // Add the editor control for the preamble.
  function add_editor_control(){
    add_action( 'customize_controls_enqueue_scripts', function() {
    	ob_start();
    	?>
    	<script>
    	wp.customize.bind( 'ready', function() {
    		var api = wp.customize,
    			metaKey = <?php echo wp_json_encode( $this->meta_key ) ?>,
    			feature = <?php echo wp_json_encode( $this->meta_key ) ?>,
          field_type = <?php echo wp_json_encode( $this->field_type ) ?>,
          choices = <?php echo wp_json_encode($this->choices ) ?>,
          controlLabel = <?php echo wp_json_encode( __( $this->meta_name, 'customize-'.$this->current_post_type.'-'.$this->plural_meta_key ) ) ?>;
    		api.section.bind( 'add', function( section ) {
    			var control, customizeId, postTypeObj;
    			if ( ! section.extended( wp.customize.Posts.PostSection ) ) {
    				return;
    			}
    			postTypeObj = api.Posts.data.postTypes[ section.params.post_type ];
    			if ( ! postTypeObj || ! postTypeObj.supports[ feature ] ) {
    				return;
    			}
    			customizeId = 'postmeta[' + section.params.post_type + '][' + section.params.post_id + '][' + metaKey + ']';
    			control = new api.controlConstructor.<?php echo $this->control_constructor_type ?>( customizeId, {
    				params: {
    					section: section.id,
              priority: <?php echo $this->display_priority; ?>,
    					active: true,
    					settings: {
    						'default': customizeId
    					},
              field_type: field_type,
              <?php if ( $this->field_type == 'select' ) { ?>choices: choices,<?php } ?>
              <?php if ( $this->control_constructor_type == 'dynamic' ) { ?>
              input_attrs: {
    						'data-customize-setting-link': customizeId,
                <?php if ( $this->field_type == 'checkbox' ) { ?>default: <?php echo $this->checkbox_default.','; } ?>
    					},
              <?php } ?>
              label: controlLabel
    				}
    			} );
    			// Override preview trying to de-activate control not present in preview context. See WP Trac #37270.
    			control.active.validate = function() {
    				return true;
    			};
    			// Register.
    			api.control.add( control.id, control );
    		} );
    	} );
    	</script>
    	<?php
    	$js = str_replace( array( '<script>', '</script>' ), '', trim( ob_get_clean() ) );
    	wp_add_inline_script( 'customize-posts', $js );
    } );
    // Style the partial.
    // add_action( 'wp_print_styles', function() {
      // Output Styles Here
    // } );
  }

  // Dynamically register the partial when the setting is present.
  function register_partial(){
    add_action( 'wp_enqueue_scripts', function() {
    	if ( ! is_customize_preview() ) {
    		return;
    	}
    	ob_start();
    	?>
    	<script>
    	( function( api ) {
    		var registerPartial,
    			idPattern = new RegExp( <?php echo wp_json_encode( $this->setting_id_pattern ); ?> );
    		registerPartial = function( setting ) {
    			var ensuredPartial, partialId, postId, matches;
    			matches = setting.id.match( idPattern );
    			if ( ! matches ) {
    				return null;
    			}
    			postId = parseInt( matches[2] );
    			partialId = setting.id;
    			ensuredPartial = api.selectiveRefresh.partial( partialId );
    			if ( ensuredPartial ) {
    				return ensuredPartial;
    			}
    			ensuredPartial = new api.selectiveRefresh.partialConstructor.deferred( partialId, {
    				params: {
    					selector: <?php echo wp_json_encode( '.'.$this->current_post_type.'-'.$this->meta_key.'-' ); ?> + String( postId ),
    					settings: [ setting.id ],
    					containerInclusive: false,
    					fallbackRefresh: false
    				}
    			} );
    			api.selectiveRefresh.partial.add( partialId, ensuredPartial );
    			return ensuredPartial;
    		};
    		api.each( registerPartial );
    		api.bind( 'add', registerPartial );
    	} ( wp.customize ) );
    	</script>
    	<?php
    	$js = str_replace( array( '<script>', '</script>' ), '', trim( ob_get_clean() ) );
    	wp_add_inline_script( 'customize-selective-refresh', $js );
    } );
  }

  // Recognize the partial.
  function recognize_partial(){
    add_filter( 'customize_dynamic_partial_args', function( $args, $id ) {
    	if ( preg_match( '#' . $this->setting_id_pattern . '#', $id, $matches ) ) {
    		$post_id = $matches[2];
    		$args = array_merge(
    			false === $args ? array() : $args,
    			array(
    				'container_inclusive' => false,
    				'render_callback' => function() use ( $post_id ) {
    					echo wp_kses_post( get_post_meta( $post_id, $this->meta_key, true ) );
    				},
    			)
    		);
    	}
    	return $args;
    }, 10, 2 );
  }

}
