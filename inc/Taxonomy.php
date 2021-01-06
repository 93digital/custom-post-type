<?php
/**
 * Taxonomy helper class.
 *
 * @package nine3-custom-post-type
 */

namespace Nine3\PostType;

/**
 * You can add taxonomies easily using the register_taxonomy() method like so:
 *
 * $books->register_taxonomy('genres');
 *
 * This method accepts two arguments, names and options.
 * The taxonomy name is required and can be string (the taxonomy name),
 * or an array of names following same format as post types:
 *
 * $books->register_taxonomy(array(
 *    'taxonomy_name' => 'genre',
 *    'singular' => 'Genre',
 *    'plural' => 'Genres',
 *    'slug' => 'genre'
 * ));
 */
class Taxonomy {

	/**
	 * The name of the taxonomy
	 *
	 * @var string
	 */
	public $taxonomyName;

	/**
	 * The singular label
	 *
	 * @var string
	 */
	public $singular;

	/**
	 * The plural label
	 *
	 * @var string
	 */
	public $plural;

	/**
	 * The slug
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * The options for the taxonomy
	 *
	 * @var array
	 */
	public $options;

	/**
	 * The textdomain for translation
	 *
	 * @var string
	 */
	public $textdomain = 'cpt';

	/**
	 * Create the taxonomy object
	 *
	 * @param mixed $names   an array/string of taxonomy names.
	 * @param array $options an array of taxonomy options.
	 */
	public function __construct( $names, $options ) {
		// Set names for taxonomy
		$this->setNames( $names );

		// Set the options for the taxonomy
		$this->setOptions( $options );
	}

	/**
	 * Set the required names for the taxonomy
	 *
	 * @param mixed $names an array/string of taxonomy names.
	 */
	public function setNames( $names ) {
		if ( ! is_array( $names ) ) {
			$names = array( 'name' => $names );
		}

		$required = array(
			// 'taxonomyName',
			'singular',
			'plural',
			'slug',
		);

		foreach ( $required as $key ) {

			// If the name has not been passed, generate it
			if ( ! isset( $names[ $key ] ) ) {

				// If it is the singular/plural make the post type name human friendly
				if ( $key === 'singular' || $key === 'plural' ) {
					$name = ucwords( strtolower( str_replace( '-', ' ', str_replace( '_', ' ', $names['name'] ) ) ) );

					// If plural add an s
					if ( $key === 'plural' ) {
						$name .= 's';
					}

					// If the slug, slugify the post type name
				} elseif ( $key === 'slug' ) {
					$name = strtolower( str_replace( array( ' ', '_' ), '-', $names['name'] ) );
				}

				// Otherwise use the name passed
			} else {
				$name = $names[ $key ];
			}

			// Set the name
			$this->$key = $name;
		}
	}

	/**
	 * Set the options for the taxonomy
	 *
	 * @param array $options an array of options for the taxonomy.
	 */
	public function setOptions( $options ) {

		// Default labels
		$labels = array(
			'name'                       => sprintf( __( '%s', $this->textdomain ), $this->plural ),
			'singular_name'              => sprintf( __( '%s', $this->textdomain ), $this->singular ),
			'menu_name'                  => sprintf( __( '%s', $this->textdomain ), $this->plural ),
			'all_items'                  => sprintf( __( 'All %s', $this->textdomain ), $this->plural ),
			'edit_item'                  => sprintf( __( 'Edit %s', $this->textdomain ), $this->singular ),
			'view_item'                  => sprintf( __( 'View %s', $this->textdomain ), $this->singular ),
			'update_item'                => sprintf( __( 'Update %s', $this->textdomain ), $this->singular ),
			'add_new_item'               => sprintf( __( 'Add New %s', $this->textdomain ), $this->singular ),
			'new_item_name'              => sprintf( __( 'New %s Name', $this->textdomain ), $this->singular ),
			'parent_item'                => sprintf( __( 'Parent %s', $this->textdomain ), $this->plural ),
			'parent_item_colon'          => sprintf( __( 'Parent %s:', $this->textdomain ), $this->plural ),
			'search_items'               => sprintf( __( 'Search %s', $this->textdomain ), $this->plural ),
			'popular_items'              => sprintf( __( 'Popular %s', $this->textdomain ), $this->plural ),
			'separate_items_with_commas' => sprintf( __( 'Seperate %s with commas', $this->textdomain ), $this->plural ),
			'add_or_remove_items'        => sprintf( __( 'Add or remove %s', $this->textdomain ), $this->plural ),
			'choose_from_most_used'      => sprintf( __( 'Choose from most used %s', $this->textdomain ), $this->plural ),
			'not_found'                  => sprintf( __( 'No %s found', $this->textdomain ), $this->plural ),
		);

		// Default options.
		$defaults = array(
			'labels'       => $labels,
			'hierarchical' => true,
			'rewrite'      => array(
				'slug' => $this->slug,
			),
		);

		// Merge default options with user submitted options.
		$this->options = array_replace_recursive( $defaults, $options );
	}

	/**
	 * Set the textdomain for translation
	 *
	 * @param  string $textdomain the textdomain.
	 */
	public function textdomain( $textdomain ) {
		$this->textdomain = $textdomain;
	}
}
