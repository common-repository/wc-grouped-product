<?php
/*
Plugin Name: Woo Grouped Product
Version: 1.0.4
Description: Description
Text Domain: wc-grouped-product
Domain Path: /languages
*/

if ( ! defined('ABSPATH') ) exit;


add_action('admin_notices', 'wgp_check_woo');
function wgp_check_woo(){
	if ( ! class_exists('WooCommerce')){
		
		echo '<div class="notice notice-error"><p>' . __('WooCommerce is not activated. Woo Grouped Product need it to work properly. Please activate WooCommerce.', 'wc-grouped-product' ) . '</p></div>';

		deactivate_plugins(plugin_basename(__FILE__));

	}
}

function wgp_check_cart(){

	
	$cart = WC()->cart->get_cart();
	
	$excluded = array();
	$return_children_product = array();

	foreach ($cart as $value){
		if ($value['data']->post->post_parent != 0 ){
			
			$produit_parent = $value['data']->post->post_parent;
			array_push($excluded, $value['product_id']);
			
			$args = array(
				'post_parent'	=>	$produit_parent,
				'post_type' 	=>	'product',
				'number_post'	=>	-1,
				'post_status'	=> 'publish',
				'exclude'		=>	$excluded,
			);
				
			$children_products = get_children($args);
				
			foreach ($children_products as $child){
				$return_children_product[] = $child->ID; 
			}
		}
	}
	

	$return_children_product = array_diff($return_children_product, $excluded);
	$return_children_product = array_unique($return_children_product);
	
	return $return_children_product;
}

add_action('woocommerce_after_cart_table', 'wgp_display');
function wgp_display(){
	$children_products = wgp_check_cart();
	
	if ( ! empty($children_products)){
	?>
		<div class="grouped-product cart-collaterals">
		<h2><?php _e('Grouped Product', 'wc-grouped-product');?></h2>
		<?php
		foreach ( $children_products as $children){
			echo '<div class="product-child">';
			$product = wc_get_product($children);

			echo '<a href="'.$product->get_permalink().'">'.get_the_post_thumbnail($children, array(150,150)).'</a>'; // picture with link.		
			echo '<a href="'.$product->get_permalink().'"><h4>' . get_the_title($children) . '</h4></a>'; // title with link			
			echo '<h5>' . $product->get_title() . '</h5>'; // grouped product reference			
			echo '<span class="price">' . $product->get_price_html() .'</span>';
			?>
			
			
			<?php

			?>
			<a href="<?php echo $product->add_to_cart_url()  ?>"><?php echo $product->add_to_cart_text(); ?></a>
			
			<?php
			
			echo '</div>';
		}
		
		
		echo '</div>';	
	}
}

add_filter( 'woocommerce_add_to_cart_redirect', 'wgp_custom_cart_redirect' );
function wgp_custom_cart_redirect() {
	return get_permalink( wc_get_page_id( 'cart' ) );
}


add_action( 'plugins_loaded', 'wgp_load_textdomain' );
function wgp_load_textdomain(){

	load_plugin_textdomain('wc-grouped-product', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
}


add_action('wp_enqueue_scripts', 'wgp_add_style');
function wgp_add_style(){
	wp_enqueue_style('wgp_style', plugin_dir_url(__FILE__) . '/assets/css/wgp.css');
}








