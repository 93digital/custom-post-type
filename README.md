# Custom Post type utility functions.

A PHP Class for creating WordPress Custom Post Types easily

## Installation

#### Install with composer

Run the following in your terminal to install PostTypes with [Composer](https://getcomposer.org/).

```
$ composer require "93digital/custom-post-type @dev"
```

Below is a basic example of getting started with the class, though your setup maybe different depending on how you are using composer.

```
<?php
require __DIR__ . '/vendor/autoload.php';

use Nine3\PostType;

$books = new PostType( 'book' );
```

#### Install Manually

Download the project and load the main class file into your themes functions.php like so:

```
require_once 'custom-post-type/class-nine3-custom-post-type.php';
```

## Post Types

```
function __construct( $singular, $plural = null, $icon = 'dashicons-format-aside', $supports = array(), $options = array() )
```

### Parameters

- *$singular* (string|required) singular name.
- *$plural* (string) plural name, by default will append 's' at the singular one.
- $icon (string) the dashicon name or custom url.
- $supports (array) the "supports" parameter. Default: title, editor and thumbnail.
- $options (array) array of agruments to pass to register_post_type (See the [WordPress](https://codex.wordpress.org/Function_Reference/register_post_type#args) codex for all available options)

### Create a new Post Type

A new post type can be created by simply passing the post types name to the class constructor.

```
$books = new PostType( 'Book' );
```

### Defining plural and icon

```
$books = new PostType(
  'Book',
  'Books',
  'dashicons-admin-page'
);
```

[All icons list](https://developer.wordpress.org/resource/dashicons/)

### Set the 'supports' parameter

By default the new custom post type set the following parameters:

- title
- editor
- thumbnail

To override / change pass an array as 4th parameter, like:

```
$books = new PostType(
  'Book',
  'Books',
  'dashicons-admin-page',
  array(
    'title',
  )
);
```

### Adding options

You can pass all the extra available parameters supported by the *register post type* function

```
$books = new PostType(
  'Book',
  'Books',
  'dashicons-admin-page',
  array(
    'title',
  ),
  array(
    'public' => false,
  )
);
```

All available options are on the [WordPress Codex](https://codex.wordpress.org/Function_Reference/register_post_type)

## Add Taxonomies

Adding taxonomies to a post type is easily achieved by using the taxonomy() method.

### Create new taxonomy

To create a new taxonomy simply pass the taxonomy name to the taxonomy() method. Labels and the taxonomy slug are generated from the taxonomy name.

```
function $books->taxonomy( string $singular_name, string $plural, array args );
```

#### Parameters:

- *$singular* (string|required) singular name.
- *$plural* (string) plural name, by default will append 's' at the singular one.
- *args* (array) array of [Arguments](https://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments).


#### Example
```
$books->taxonomy( 'Genre' );
```

### Defining names

You can define the plural name by passing it as secondary parameter.

- singular is the singular label for the post type
- plural is the plural label for the post type
- slug is the post type slug used in the permalinks

```
$books->taxonomy( 'Genre', 'Genres' );
```

### Adding options

You can further customise taxonomies by passing an array of options as the 3rd argument to the method.

```
$options = array(
    'heirarchical' => false
);

$books->taxonomy( 'Genre', 'Genres', $options );
```

All available options are on the [WordPress Codex](https://codex.wordpress.org/Function_Reference/register_taxonomy)

## The settings page

When setting the 'has_archive' parameter to true, the class will add a sub page for the new CPT, called:

_[CPT Plura] Settings_

The page can be used to add fields using ACF, and will be visible inside the *Option page* option.

To retrieve the information stored inside the custom page, use:

```
get_field( '[THE OPTION SLUG]', 'cpt-[SINGULAR NAME]' );
the_field( '[THE OPTION SLUG]', 'cpt-[SINGULAR NAME]' );
```

_the 2nd attribute is the same of the "page" one of the setting page url_

### Example

```
get_field( 'year', 'cpt-book' );
the_field( 'year', 'cpt-book' );
```

*By default the 'has_archive' parameter is set to true.*

## Loop the items

The class has builtin methods to easily loop the custom posts, without manually calling the WP_Query class.

> **Note:** For performance purpose "no_found_rows" is set to true. 
> If pagination is required, you need to set it to true.

### Simple loop

To simple loop through the items using the default WP_Query parameters just use the variable registered before as:

```
<h1>This is a simple loop</h1>
<?php while ( $books->have_posts() ) : $books->the_post(); ?>
  <h2><?php the_title(); ?></h2>
  <p><?php the_content(); ?></p>
<?php endwhile; ?>
```

In this example **$books** is the name of the variable used to register the custom post type in WP.

### Get posts

The following functions allows to customise the WP_Query arguments used for the [Simple loop](#simple-loop).

#### Get the CPT posts using custom parameters.

```
function get_posts( $posts_per_page = 0, $args );
```

- *$posts_per_page* (int) number of post to show per page. Default = 0 (does not set the parameter for WP_Query)
- *$args* (array) array of arguments to pass to [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query)

##### Example

```
// Get the first 10 books.
$books->get_posts( 10 );

// Passing offset attribute.
$books->get_posts( 10, array( 'offset' => 11 ) );

// If don't want to specify the post limit, just pass 0 as first parameter.
$books->get_posts( 0, array( 'offset' => 11 ) );
```

Using with simple loop:

```
$books->get_posts( 0, array( 'offset' => 11 ) );

while ( $books->have_posts() ) : $books->the_post();
  ...
endwhile;

```

If pagination is required

```
$books->get_posts( 0, array( 'offset' => 11, 'no_found_rows' => true ) );

while ( $books->have_posts() ) : $books->the_post();
  ...
endwhile;
```

#### Get posts by meta values

```
$books->get_posts_by_meta( string $meta_key, mixed $meta_value, string $meta_compare, int $post_limit = 0 );
```

#### Get posts by taxonomy

For all the taxonomies registered using the built in [Create new taxonomy](#create-new-taxonomy) method, is available the following virutal method: 

```
$books->get_posts_by_[taxonomy-slug]( string|array values );
```

where:

- *$books* is the variable name previously used to register the custom post type.
- *taxonomy_slug* is the slug of the taxonomy we want to filter.
- $values list of slugs to filter.

##### Example

Suppose we register the taxonomy **genre** for our cpt:

```
$books = new PostType( 'Book' );
$books->taxonomy( 'Genre' );
```

now we can easily filter our posts:

```
$book_cpt->get_posts_by_genre( 'horror' );
```

**we can also pass an array of slugs if needed to filter by multiple values**

##### Single taxonomy

An alternative method to filter by single taxonomy is:
```
$books->get_posts_by_term( string $taxonomy, string|int|array $slugs, string $field = 'slug', array $args = array() );
```

###### Parameters:

- *$taxonomy* the taxonomy name
- *$slugs* the slugs/ids to filter
- *$field* Select taxonomy term by. Possible values are 'term_id', 'name', 'slug' or 'term_taxonomy_id'. Default value is 'slug'.
- *$args* additional arguments to pass to the WP_Query class.

##### Multiple taxonomies

```
$books->get_posts_by_terms( array $terms, $string relation = 'AND', array $args = array() );
```

###### Parameters:

- *$terms* associative array with the taxonomies to filter.
- *$relation* The logical relationship between each inner taxonomy.
- *$args* additional arguments to pass to the WP_Query class.

###### Example:

```
$books = new PostType( 'Book' );
$books->taxonomy( 'Genre' );
$books->taxonomy( 'Language' );

/**
 * Retrieves all the "Horror" books written in "Italian" and "German"
 */
$terms = array(
  'genre' => 'horror',
  'language' => array( 'italian', 'german' ),
);
$books = $books->get_posts_by_terms( $terms );
```

#### Set the order

By default posts are ordered by *Title*: *ASC*

```
$books->set_order( ORDER_BY, ORDER );
```

### The WP_Query object

Is possible to access to the last executed WP_Query object with:

```
$query = $books->wp_query();
```

## Admin Edit Screen

### Filters

When you register a taxonomy it is automagically added to the admin edit screen as a filter and a column.

You can define what filters you want to appear by using the filters() method:

```
$books->filters( array( 'genre' ) );
```

### Meta box

The class has 2 utility functions to easily add meta box for the CPT registered.

#### Add meta box

```
function add_meta_box( $title, $callback, $context = 'normal', $priority = 'low' );
```

##### Example

```
$books->add_meta_box( 'My meta box', 'my_callback_function' );

function my_callback_function( $post ) {
}
```

#### Sidebar meta box

An alternative method, instead of setting $contex = 'side', to register a meta box into the sidebar is:

```
function add_sidebar_meta_box( $title, $callback, $priority = 'low' );
```

##### Example

```
$books->add_sidebar_meta_box( 'My meta box', 'my_callback_function' );

function my_callback_function( $post ) {
}
```


### Columns

#### Add a custom column to the CPT list:

```
function add( $column, $label = null, $callback = null, $position = null );
```

##### Example

```
$books->columns()->add( 'genre', 'Genre', 'genre_callback', 2 );

function genre_callback( $key, $post_id ) {
   echo $post_id;
}
```

#### Hide a column from the CPT list

```
/**
  * Add a column to hide
  *
  * @param  string $column the slug of the column to hdie
  */
function hide( $columns )
```

