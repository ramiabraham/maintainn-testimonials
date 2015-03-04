<?php
/*
Plugin Name: Maintainn Testimonials
Plugin URI: http://maintainn.com
Description: Testimonials plugin scaffold
Version: 1.0
Author: Maintainn
License: GPL v3 or later

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Maintainn_Testimonials {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'MaintainnTestimonials';
	const slug = 'Maintainn_Testimonials';

	/**
	 * Constructor
	 */
	function __construct() {

		register_activation_hook( __FILE__, array( &$this, 'install_maintainn_testimonials' ) );

		add_action( 'init', array( &$this, 'init_maintainn_testimonials' ) );
	}

	/**
	 * Runs when the plugin is activated
	 */
	function install_maintainn_testimonials() {
	}

	/**
	 * Runs when the plugin is initialized
	 */
	function init_maintainn_testimonials() {

		load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

		$this->register_scripts_and_styles();

		// Register the shortcode [testimonial]
		add_shortcode( 'testimonials', array( &$this, 'render_shortcode' ) );

		// Registers 'testimonials' post type

		$labels = array(
		'name'                => _x( 'Testimonials', 'Post Type General Name', 'maintainn_testimonials' ),
		'singular_name'       => _x( 'Testimonial', 'Post Type Singular Name', 'maintainn_testimonials' ),
		'menu_name'           => __( 'Testimonials', 'maintainn_testimonials' ),
		'parent_item_colon'   => __( 'Parent Item:', 'maintainn_testimonials' ),
		'all_items'           => __( 'All Testimonials', 'maintainn_testimonials' ),
		'view_item'           => __( 'View Testimonial', 'maintainn_testimonials' ),
		'add_new_item'        => __( 'Add New Testimonial', 'maintainn_testimonials' ),
		'add_new'             => __( 'Add New Testimonial', 'maintainn_testimonials' ),
		'edit_item'           => __( 'Edit Testimonial', 'maintainn_testimonials' ),
		'update_item'         => __( 'Update Testimonial', 'maintainn_testimonials' ),
		'search_items'        => __( 'Search Testimonials', 'maintainn_testimonials' ),
		'not_found'           => __( 'Not found', 'maintainn_testimonials' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'maintainn_testimonials' ),
	);
	$args = array(
		'label'               => __( 'testimonials', 'maintainn_testimonials' ),
		'description'         => __( 'Add a testimonial', 'maintainn_testimonials' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail', ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => false,
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-format-status',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'testimonials', $args );

	}

	function render_shortcode($atts, $content = null ) {

		extract(shortcode_atts(array(
			'number' => '',
			'exclude' => ''
			), $atts));

		$slider  = '<div class="testimonials-container">';

		//loop

		$args = array (
			'post_type'              => 'testimonials',
			'post_status'            => 'publish',
			'posts_per_page'         => $number,
			'orderby'                => 'date',
			'cache_results'          => false,
		);

		// The Query
		$query = new WP_Query( $args );

		// The Loop
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$slider .= '<div class="testimonials-inner">';

				$slider .= '<div class="maintainn-testimonials-featured-image">';

				if ( has_post_thumbnail() ) {
				$slider .= get_the_post_thumbnail();
				}

				$slider .= '</div>';

				$slider .= '<div class="maintainn-testimonials-text">';

				$slider .= get_the_content();

				$slider .= '</div>';

				$slider .= '</div><!-- end testimonials single -->';

			}
		} else {
			// no testimonials
			$slider .= 'There are no testimonials!';
		}

		// Restore original Post Data
		wp_reset_postdata();

		$slider .= '<script>';
		$slider .= 'jQuery(document).ready(function(){';
		$slider .= 'jQuery(".testimonials-container").slick({
		  dots: true,
		  infinite: true,
		  speed: 300,
		  slidesToShow: 1,
		  adaptiveHeight: true,
		  arrows: true,
		  autoplay: true,
		  autoplaySpeed: 3000
		})';
		$slider .='});';
		$slider .= '</script>';

		$slider .= '</div><!-- end .testimonials-container -->';

		return $slider;
	}

	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function register_scripts_and_styles() {

			$this->load_file( self::slug . '-script', '/lib/slick.min.js', true );
			$this->load_file( self::slug . '-style', '/lib/slick.css' );

	}

	/**
	 * load slick scripts and styles
	 *
	 * @access private
	 * @param mixed $name
	 * @param mixed $file_path
	 * @param bool $is_script (default: false)
	 * @return void
	 */
	private function load_file( $name, $file_path, $is_script = false ) {

		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;

		if( file_exists( $file ) ) {
			if( $is_script ) {
				wp_register_script( $name, $url, array('jquery') );
				wp_enqueue_script( $name );
			} else {
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			}
		}

	}

}
new Maintainn_Testimonials();