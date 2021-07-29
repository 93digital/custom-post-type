<?php
/**
 * Custom Post type utility functions.
 *
 * @link https://github.com/93digital/custom-post-type
 * @author 93digital
 * @link  https://93digital.co.uk/
 * @version 1.0
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 *
 * @package CPT
 */

namespace Nine3;

/**
 * Custom Post type helper class.
 */
require_once __DIR__ . '/inc/PostType.php';

/**
 * This class extend the Post_Type class, by adding useful function to
 * manage the CPT data.
 *
 * **The class will throw an exception if we try to register a class ending by 's',
 * as the CPT MUST be registered with singular name.**
 * This because the permalink for the single element have to contain the singular name,
 * not the plural.
 *
 * ## Usage:
 *
 * $book = new Nine3_Custom_Post_Type( 'book' );
 *
 *
 *
 * ## Example:
 *
 *
 *  <h1>Custom post</h1>
 *  <?php while( $books->have_posts() ) : $books->the_post(); ?>
 *    <h2><?php the_title(); ?></h2>
 *    <p><?php the_content(); ?></p>
 *  <?php endwhile; ?>
 */
class PostType extends PostType\Post_Type {

	/**
	 * Contains the last WP_Query executed by the class, and is used
	 * to loop through the CPT elements, without necessary use another variable.
	 *
	 * @internal
	 * @var WP_Query
	 */
	private $_last_query = null;

	/**
	 * Default orderby
	 *
	 * @internal
	 * @var string
	 */
	private $_orderby = 'date';

	/**
	 * Default sort ASC/DESC
	 * ASC - DESC, RANDOM
	 *
	 * @internal
	 * @var string
	 */
	private $_order = 'DESC';

	/**
	 * Keep track of the taxonomy assigned to the CPT.
	 * This is needed by the "Load More" feature, as the latter have to check
	 * if the parameters in the url refer to any of the CPT taxonomy.
	 *
	 * @internal
	 * @var array.
	 */
	private static $_taxonomies = array();

	/**
	 * Used to register metaboxes
	 *
	 * @internal
	 * @var array.
	 */
	private $metaboxes = array();

	/**
	 * List of taxonomy names that CANNOT BE used, as the keyword is WP reserved one, and
	 * using it will causes unexpected behaviour.
	 *
	 * @param [type] $names
	 * @param [type] $options
	 */
	private static $protected_taxonomies = array(
		'month',
		'year',
		'category',
		'tag',
	);

	/**
	 * Create the CPT.
	 *
	 * @internal
	 * @param string $singular The post type name to register.
	 * @param array  $plural   The plural name for the CPT.
	 * @param array  $icon     The menu icon, for the full list visit: https://developer.wordpress.org/resource/dashicons/.
	 *                         Default 'dashicons-format-aside'.
	 * @param array  $supports The 'supports' parameter for the CPT.
	 *                         By default the supports values is set to:
	 *                         'title', 'editor', 'thumbnail', 'excerpt'
	 *
	 * @param array  $options  Used to override the default parameters.
	 */
	public function __construct( $singular, $plural = null, $icon = null, $supports = array(), $options = array() ) {
		// The CPT name (slug).
		$name = strtolower( sanitize_title( $singular ) );

		// Pluralize the name, if $plural == null, by just adding 's' to it.
		if ( $plural === null ) {
			$plural = $singular . 's';
		}

		/**
		 * By default has_archive is true, and in this case we're going to create a
		 * option page that can be customised with ACF.
		 */
		$is_public = ! isset( $options['public'] ) || $options['public'] != false;
		if ( ! isset( $options['has_archive'] ) && $is_public || ( isset( $options['has-options'] ) && $options['has-options'] ) ) {
<<<<<<< HEAD
			$has_archive = isset( $options['has_archive'] ) ? $options['has_archive'] : false;
=======
			$has_archive = isset( $options['has_archive'] ) ? $options['has_archive'] : true;
>>>>>>> remotes/github/master

			if ( $has_archive ) {
				$options['has_archive'] = sanitize_title( $plural );
			}

<<<<<<< HEAD
			if ( is_string( $has_archive ) ) {
				$options['has_archive'] = $has_archive;
			}

=======
>>>>>>> remotes/github/master
			/**
			 * Add a option page to be used to set/retrieve information from the archive
			 * page.
			 * We use add_option_page, so ACF fields can be added to the archive page.
			 *
			 * This function is automatically called when the 'has_archive' option is present.
			 *
			 * https://www.advancedcustomfields.com/resources/acf_add_options_page/
			 */
			$args = array(
				'post_id'     => 'cpt-' . $name,
				'page_title'  => $plural . ' Settings',
				'parent_slug' => 'edit.php?post_type=' . $name,
				'menu_slug'   => 'cpt-' . $name,
			);

			if ( function_exists( 'acf_add_options_page' ) ) {
				acf_add_options_page( $args );
			}
		}

		// If is not public, by default set the SHOW_UI and show_in_nav_menus parameters are set to false,
		// but by default we want/need to show it in the back-end.
		if ( ! $is_public && ! isset( $options['show_ui'] ) ) {
			$options['show_ui'] = true;
		}

		if ( ! $is_public && ! isset( $options['show_in_nav_menus'] ) ) {
			$options['show_in_nav_menus'] = true;
		}

		// Supports?
		if ( empty( $supports ) ) {
			$supports = array(
				'editor',
				'title',
				'thumbnail',
				'excerpt',
			);
		}
		$options['supports']  = $supports;
		$options['menu_icon'] = empty( $icon ) ? 'dashicons-format-aside' : $icon;

		if ( ! isset( $options['show_in_rest'] ) ) {
			$options['show_in_rest'] = true;
		}

		// Let's register it.
		$args = array(
			'name'     => $name,
			'singular' => $singular,
			'plural'   => $plural,
		);
		parent::__construct( $args, $options );

		// Add the meta boxes, if any.
		add_action( 'add_meta_boxes_' . $name, array( & $this, 'register_meta_boxes' ) );
	}

	/**
	 * Regiter the taxonomy for the CPT.
	 *
	 * This function call the omonimous function on main class, but is needed
	 * to keep track of the taxonomies regsitered for the CPT.
	 *
	 * @param mixed $names   The names for the taxonomy.
	 * @param array $options Taxonomy options.
	 */
	public function taxonomy( $singular, $plural = null, $options = array() ) {
		$name = sanitize_title( $singular );

		// Check if the name is a protected term.		
		$slug = isset( $option['slug'] ) ? sanitize_title( $option['slug'] ) : $name;
		if ( in_array( $slug, self::$protected_taxonomies ) ) {
			throw new Exception( $name . ' cannot be used as custom taxonmy!!!' );
		}

		if ( $plural === null ) {
			$plural = $name . 's';
		}

		$names = array(
			'name'     => $name,
			'singular' => $singular,
			'plural'   => $plural,
		);

		parent::taxonomy( $names, $options );

		self::$_taxonomies[$this->postTypeName][] = $names;
	}

	/**
	 * Get custom posts
	 *
	 * Utility function to get posts for the current post type.
	 *
	 * @param  integer $limit       posts_per_page.
	 * @param  array   $custom_args override default args.
	 * @return WP_Query               [description]
	 */
	public function get_posts( $limit = 0, $custom_args = array() ) {
		$args = array(
			'post_type' => $this->postTypeName,

			// For performance purpose.
			'no_found_rows' => true,

			// default order.
			'orderby' => $this->_orderby,
			'order' => $this->_order,
		);

		// No custom meta?
		if ( ! isset( $custom_args['meta_key'] ) ) {
			$args['update_post_meta_cache'] = false;
		}

		// No custom taxonomy?
		if ( ! isset( $custom_args['tax_query'] ) ) {
			$args['update_post_meta_cache'] = false;
		}

		if ( $limit != 0 && ! isset( $args['posts_per_page'] ) ) {
			$args['posts_per_page'] = $limit;
		}

		$args = array_merge( $args, $custom_args );
		$this->_last_query = new \WP_Query( $args );

		return $this->_last_query;
	}

	/**
	 * Allow the dev to filter the posts by taxonomy, but without
	 * need to create a function for each registered taxonomy.
	 *
	 * For example:
	 * 
	 *   $book_cpt->taxonomy('genre');
	 * 
	 * we can easily exec the tax_query by calling:
	 * 
	 *   $book_cpt->get_posts_by_genre('horror');
	 * 
	 * @param string $func the function called
	 * @param any $params
	 * @return void
	 */
	function __call($func, $params) {
		if (stripos($func, 'get_posts_by_') === 0) {
			// Check that the taxonomy name is valid
			$taxonomy   = str_replace('get_posts_by_', '', $func);
			$taxonomy   = str_replace('_', '-', $taxonomy);
			$taxonomies = array_map( 'sanitize_title', $this->taxonomies );

			if ( in_array( $taxonomy, $taxonomies ) ) {
				return $this->get_posts_by_term( $taxonomy, $params );
			}
		}

		throw error('Method not found');
	}

	/**
	 * Get posts by single taxonomy
	 *
	 * @param string $taxonomy the taxonomy name.
	 * @param mixed $terms string / array containing the terms to filter.
	 */
	public function get_posts_by_term( $taxonomy, $terms, $field = 'slug', $args = array() ) {
		if ( ! is_array( $terms ) ) {
			$terms = array( $terms );
		}

		$args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => $field,
				'terms'    => $terms,
				'operator' => 'IN',
			),
		);

		return $this->get_posts( -1, $args );
	}

	/**
	 * Get posts by multiple taxonomies
	 *
	 * @param string $taxonomies associative array containing all the slugs we want to filter.
	 * @param mixed $relation the logical relationship between each inner taxonomy array
	 */
	public function get_posts_by_terms( $taxonomies, $relation = 'AND', $args = array() ) {
		$tax_query = array(
			'relation' => $relation,
		);

		foreach ( $taxonomies as $taxonomy => $slugs ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $slugs,
				'operator' => 'IN',
			);
		}

		$args['tax_query'] = $tax_query;

		return $this->get_posts( -1, $args );
	}

	/**
	 * Return the posts for the custom post type.
	 *
	 * @param  integer $limit       (optional) the number of posts to return,
	 *                              by default use the WP limit.
	 * @param  array   $custom_args to override/add custom parameters to the WP_Query call
	 * @return WP_Query             the WP_Query object
	 */
	public function get_posts_by_meta( $meta_key, $meta_value, $meta_compare = '=', $limit = 0 ) {
		$args = array(
			'meta_key'     => $meta_key,
			'meta_value'   => $meta_value,
			'meta_compare' => $meta_compare,
		);

		return $this->get_posts( $limit, $args );
	}

	/**
	 * Utility function to loop the custom posts.
	 *
	 * This class allow to loop through the cpt without necessarely create a new variable.
	 * Also:
	 * 1. take care of calling the *$this->get_posts* method, if _last_query is null.
	 * 2. automatically call wp_reset_postdata, when there are no more posts.
	 *
	 * Example:
	 *
	 * while( $book->have_posts() ) {
	 *  $book->the_post();
	 *  ...
	 * }
	 *
	 * When need to use LOAD MORE after a CPT, we can't call wp_reset_postdata() before the
	 * "Load more" button, but we need to do it manually, after the button itself.
	 *
	 * @param  bool $reset call wp_reset_postdata() if no more posts are available.
	 * @return bool true if there are more posts to process.
	 */
	public function have_posts( $reset = true ) {
		if ( null === $this->_last_query ) {
			$this->get_posts();
		}

		if ( $this->_last_query->have_posts() ) {
			return true;
		}

		if ( $reset ) {
			wp_reset_postdata();
		}

		return false;
	}

	/**
	 * Call the WP the_post function
	 */
	public function the_post() {
		$this->_last_query->the_post();
	}

	/**
	 * Set the orderby attribute for the WP_Query
	 *
	 * @param $string $orderby "ORDER BY" key.
	 * @param string $order   sorting type ASC, DESC or RANDOM.
	 */
	public function set_order( $orderby, $order = 'ASC' ) {
		$this->_orderby = $orderby;
		$this->_order = $order;
	}

	/**
	 * Get all the taxonomies registered for the current CPT.
	 *
	 * @return array
	 */
	public static function get_all_taxonomies() {
		return self::$_taxonomies;
	}

	/**
	 * Return the last executed query object (WP_Query)
	 */
	public function wp_query() {
		return $this->_last_query;
	}

	/**
	 * Register the meta boxes defined with the methods:
	 *  - add_meta_box
	 *  - add_sidebar_meta_box
	 *
	 * @return void
	 */
	public function register_meta_boxes() {
		foreach ( $this->metaboxes as $key => $metabox ) {
			$id = $this->postTypeName . sanitize_title( $metabox['title'] );

			add_meta_box( $id, $metabox['title'], $metabox['callback'], $this->postTypeName, $metabox['context'], $metabox['priority'] );
		}
	}

	/**
	 * Add meta box for the CPT
	 *
	 * https://developer.wordpress.org/reference/functions/add_meta_box/
	 *
	 * @param string $title the meta box title.
	 * @param string $callback the callback function.
	 * @param string $context  The context within the screen where the boxes should display.
	 * @param string $priority the priority within the context where the boxes should show.
	 */
	public function add_meta_box( $title, $callback, $context = 'normal', $priority = 'low' ) {
		$this->metaboxes[] = array(
			'title'    => $title,
			'callback' => $callback,
			'context'  => $context,
			'priority' => $priority,
		);
	}

	/**
	 * Add a meta box to the sidebar
	 *
	 * @param string $title the meta box title.
	 * @param string $callback the callback function.
	 * @param string $priority the priority within the context where the boxes should show.
	 */
	public function add_sidebar_meta_box( $title, $callback, $priority = 'low' ) {
		$this->add_meta_box( $title, $callback, 'side', $priority );
	}
}
