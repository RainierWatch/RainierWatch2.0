<?php
/**
 * Child theme functions
 *
 * @since    1.0.0
 * @version  1.0.0
 */





/**
 * Enqueue parent theme stylesheet
 *
 * This runs only if parent theme does not claim support for
 * `child-theme-stylesheet`, and so we need to enqueue this
 * child theme's `style.css` file ourselves.
 *
 * If parent theme supports `child-theme-stylesheet`, it enqueues
 * this child theme's `style.css` file automatically.
 *
 * @since    1.0.0
 * @version  1.0.0
 */
function reykjavik_child_parent_theme_style() {
	if ( ! current_theme_supports( 'child-theme-stylesheet' ) ) {
		wp_enqueue_style( 'reykjavik_child-parent-style', get_template_directory_uri() . '/style.css' );
		wp_enqueue_style( 'reykjavik_child-child-style', get_stylesheet_uri() );
	}
} // /reykjavik_child_parent_theme_style

add_action( 'wp_enqueue_scripts', 'reykjavik_child_parent_theme_style', 1000 );



/**
 * Copy parent theme options (customizer settings)
 *
 * This runs only during child theme activation,
 * and only when there are no child theme options saved.
 *
 * @since    1.0.0
 * @version  1.0.0
 */
function reykjavik_child_parent_theme_options() {
	if ( false === get_theme_mods() ) {
		$parent_theme_options = get_option( 'theme_mods_' . get_template() );
		update_option( 'theme_mods_' . get_stylesheet(), $parent_theme_options );
	}
} // /reykjavik_child_parent_theme_options

add_action( 'after_switch_theme', 'reykjavik_child_parent_theme_options' );





/**
 * Put your custom PHP code below...
 */

// setting up a custom WP Log in page

function custom_login_stylesheet() {
    wp_enqueue_style( 'custom-login', get_stylesheet_directory_uri() . '/login/custom-login-styles.css' );
}
add_action( 'login_enqueue_scripts', 'custom_login_stylesheet' );

// adding a function that replaces the login page logo link with the RW link
function my_login_logo_url() {
return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
return 'Rainier Watch Website';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );

// remove login error message to prevent hackers
function login_error_override()
{
    return 'Incorrect login details.';
}
add_filter('login_errors', 'login_error_override');

// remove login page shake on incorrect creds
function my_login_head() {
remove_action('login_head', 'wp_shake_js', 12);
}
add_action('login_head', 'my_login_head');

// route anyone who is not an admin to the home page
function custom_login_redirect( $redirect_to, $request, $user ) {
  if ( isset( $user->roles ) && is_array( $user->roles ) ) {
    if( in_array('administrator', $user->roles)) {
      return admin_url();
    } else {
      return home_url();
    }
  } else {
      return home_url();
  }
}
 
add_filter('login_redirect', 'custom_login_redirect', 10, 3);

// add footer to the log in box
function custom_loginfooter() { ?>
    <p class="custom-footer-link">
		NOTE: Currently the Rainier Watch site is under development and will launch soon.
		</p>
		<p class="custom-footer-link">In the meantime, check out the <a href="http://shop.rainierwatch.com">Rainier Watch Shop!</a> 
    </p>
<?php }
add_action('login_footer','custom_loginfooter');