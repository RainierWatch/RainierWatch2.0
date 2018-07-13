<?php
/**
 * The sidebar containing the shop sidebar widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Mystery Themes
 * @subpackage Easy Store
 * @since 1.0.0
 */

$default_sidebar = apply_filters( 'easy_store_filter_shop_sidebar_id', 'easy_store_shop_sidebar', 'shop-sidebar' );
?>

<div id="sidebar-shop" class="widget-area sidebar" role="complementary">
	<?php if ( is_active_sidebar( $default_sidebar ) ) : ?>
		<?php dynamic_sidebar( $default_sidebar ); ?>
	<?php else : ?>
		<?php
			/**
			 * Hook - easy_store_action_default_sidebar.
			 */
			do_action( 'easy_store_action_left_sidebar', $default_sidebar, 'shop-sidebar' );
		?>
	<?php endif; ?>
</div><!-- #sidebar-shop -->