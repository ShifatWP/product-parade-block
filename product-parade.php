<?php
/**
 * Plugin Name:       Product Parade Block
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Sifat
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       product-parade-block
 *
 * @package Wpdev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Makes sure the plugin is defined before trying to use it.
if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

/**
 * Function to display admin notice if WooCommerce is not active
 */
function product_parade_block_error_admin_notice() {
	$plugin_page_url = admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ); // URL to search for WooCommerce plugin.
	?>
	<div class="notice notice-error">
	<p>
	<?php esc_html_e( '<strong>Product Parade Block</strong> plugin requires <strong>WooCommerce</strong> to be active.', 'product-parade-block' ); ?>
	<a href="<?php echo esc_url( $plugin_page_url ); ?>"><?php esc_html_e( 'Install or activate WooCommerce here.', 'product-parade-block' ); ?></a>
	</p>

	</div>
	<?php
}


/**
 * Check if WooCommerce is active
 */
if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) && ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	add_action( 'admin_notices', 'product_parade_block_error_admin_notice' );
	return; // Exit if WooCommerce is not active.
}

/**
 * Render the block in frontend.
 *
 * @param array $attributes The block attributes.
 * @return string The rendered block HTML.
 */
function product_parade_block_render_callback( $attributes ) {
	$css_string   = str_replace( '"', '', $attributes['frontendCss'] );
	$unique_id    = $attributes['uniqueId'];
	$class_name   = 'wp-block-wpdev-product-parade-block-' . esc_attr( $unique_id );
	$category_ids = ! empty( $attributes['categories'] ) ? array_map(
		function ( $cat ) {
			return $cat['id'];
		},
		$attributes['categories']
	) : array();

	/**
	 * Fetch all products.
	 */
	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => $attributes['postPerPage'],
	);

	if ( ! empty( $category_ids ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $category_ids,
				'operator' => 'IN',
			),
		);
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '<p>No products found</p>';
	}

	/**
	 * Add sorting dropdown.
	 */
	$content  = '<div ' . get_block_wrapper_attributes( array( 'class' => $class_name ) ) . '>';
	$content .= '<style>' . $css_string . '</style>';
	if ( $attributes['showSortingDropdown'] ) {
		$content .= '<select id="product-sort">
                    <option value="date">Latest</option>
                    <option value="price">Sort by Price (Low to High)</option>
                    <option value="price-desc">Sort by Price (High to Low)</option>
                    <option value="rating">Sort by Rating</option>
                    <option value="popularity">Sort by Popularity</option>
                    </select>';
	}
	$content .= '<div class="product-container">';

	while ( $query->have_posts() ) {
		$query->the_post();
		global $product;

		$price_html     = $product->get_price_html();
		$average_rating = $product->get_average_rating();
		$add_to_cart    = do_shortcode( '[add_to_cart id="' . get_the_ID() . '" show_price="false" style="" class="add-to-cart"]' );
		$on_sale        = $product->is_on_sale();

		$content .= '<div class="ppb-product content-position-' . $attributes['contentPosition'] . '" data-price="' . esc_attr( $product->get_price() ) . '" data-rating="' . esc_attr( $average_rating ) . '" data-sales="' . esc_attr( $product->get_total_sales() ) . '" data-date="' . get_the_date( 'U' ) . '">';
		if ( $on_sale && $attributes['showOnSaleRibbon'] ) {
			$content .= '<div class="on-sale-label position-' . esc_attr( $attributes['ribbonPosition'] ) . '"><div>' . esc_html( $attributes['onSaleLabelText'] ) . '</div></div>';
		}
		$content .= '<a href="' . get_the_permalink() . '">';
		$content .= woocommerce_get_product_thumbnail();
		$content .= '</a>';
		$content .= '<div class="product-contents">';
		$content .= '<a href="' . get_the_permalink() . '">';
		$content .= '<h2 class="product-name">' . get_the_title() . '</h2>';
		$content .= '</a>';
		$content .= '<span class="price">' . $price_html . '</span>';
		if ( $attributes['showAverageRatings'] ) {
			$content .= '<div class="product-parade-block-rating-area">
                            <div class="empty-icons">
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <div style="width: ' . ( ( $average_rating / 5 ) * 100 ) . '%;" class="filled-icons">
                                <i class="fas fa-star"></i> 
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                         </div>';
		}
		$content .= $add_to_cart;
		$content .= '</div>'; // Closing product-contents div.
		$content .= '</div>'; // Closing ppb-product div.
	}

	$content .= '</div>'; // Closing product-container div.
	$content .= '</div>'; // Closing main wrapper div.

	wp_reset_postdata();

	return $content;
}
/**
 * Enqueue sorting script.
 */
function enqueue_sorting_script() {
	wp_enqueue_script(
		'product-parade-sorting',
		plugin_dir_url( __FILE__ ) . 'assets/sorting.js',
		array( 'jquery' ),
		'1.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'enqueue_sorting_script' );

/**
 * Fetch product data for localizing.
 */
function fetch_product_data() {
	$args          = array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	);
	$products      = get_posts( $args );
	$products_data = array();

	foreach ( $products as $product ) {
		$product_id   = $product->ID;
		$product_item = wc_get_product( $product_id );

		/**
		 * Process the shortcode to get the button HTML.
		 */
		$add_to_cart_html = do_shortcode( '[add_to_cart id="' . $product_id . '" show_price="false" style="" class="add-to-cart"]' );

		/**
		 * Collect product data into an array.
		 */
		$products_data[] = array(
			'id'          => $product_id,
			'price'       => $product_item->get_price_html(),
			'rating'      => $product_item->get_average_rating(),
			'onSale'      => $product_item->is_on_sale() ? true : false,
			'add_to_cart' => $add_to_cart_html,
		);
	}

	return $products_data;
}

/**
 * Function to initialize the block.
 */
function wpdev_product_parade_block_block_init() {
	wp_register_style( 'blockCss', plugin_dir_url( __FILE__ ) . 'assets/block.css', array(), '1.0', 'all' );

	register_block_type(
		__DIR__ . '/build',
		array(
			'render_callback' => 'product_parade_block_render_callback',
			'style'           => array( 'blockCss' ),
		)
	);

	/**
	 * Function to enqueue FontAwesome.
	 */
	function enqueue_fontawesome() {
		wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css' );
	}
	add_action( 'wp_enqueue_scripts', 'enqueue_fontawesome' );

	/**
	 * Function to enqueue FontAwesome for the block editor.
	 */
	function enqueue_fontawesome_editor() {
		wp_enqueue_style( 'fontawesome-editor', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css' );
	}
	add_action( 'enqueue_block_editor_assets', 'enqueue_fontawesome_editor' );

	/**
	 *Enqueue script for block editor.
	 */
	wp_enqueue_script(
		'product-parade-block-editor',
		plugins_url( 'build/index.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' ),
		true
	);

	/**
	 * Localize script with product data.
	 */
	wp_localize_script(
		'product-parade-block-editor',
		'exampleWooCommerceBlock',
		array(
			'productsMeta' => fetch_product_data(),
		)
	);
}

add_action( 'init', 'wpdev_product_parade_block_block_init' );