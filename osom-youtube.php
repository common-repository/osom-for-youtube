<?php
/**
 * Osom Youtube
 *
 * Plugin Name:       Osom for YouTube - Make YouTube embed block privacy-friendly
 * Plugin URI:        https://osompress.com/plugins/osom-youtube/
 * Description:       Osom YouTube enhances the YouTube embed block user experience by loading the video from the nocookie domain.
 * Version:           1.0.1
 * Author:            OsomPress
 * Author URI:        https://osompress.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       osom-youtube
 * Domain Path:       /languages
 */

namespace osom\Osom_YouTube;

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}

//  Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// Function to clear oEmbed cache from post meta
function clear_oembed_postmeta_cache() {
	global $wpdb;
	$meta_key_like = '_oembed_%';
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE %s", $meta_key_like ) );
}

// Function to clear oEmbed transients
function clear_oembed_transient_cache() {
	global $wpdb;
	$transient_like = '_transient_%_oembed_%';
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $transient_like ) );
}

// Combined function to clear all oEmbed caches
function clear_all_oembed_caches() {
	clear_oembed_postmeta_cache();
	clear_oembed_transient_cache();
}

// Register activation hook
register_activation_hook( __FILE__, __NAMESPACE__ . '\clear_all_oembed_caches' );

// Filter the YouTube block (including legacy markup) to use the youtube-nocookie.com domain.
add_filter( 'render_block_core/embed', __NAMESPACE__ . '\filter_youtube_block_embed_attributes', 10, 2 );
add_filter( 'render_block_core-embed/youtube', __NAMESPACE__ . '\filter_youtube_block_embed_attributes', 10, 2 );
function filter_youtube_block_embed_attributes( $block_content, $block ) {

	if ( isset( $block['attrs']['providerNameSlug'] ) && 'youtube' === $block['attrs']['providerNameSlug'] ) {
		$new_content = new \WP_HTML_Tag_Processor( $block_content );
		if ( $new_content->next_tag( 'iframe' ) ) {
			$src = $new_content->get_attribute( 'src' );
			$src = str_replace( 'youtube.com', 'youtube-nocookie.com', $src );
			$src = str_replace( 'youtu.be', 'youtube-nocookie.com', $src );
			$src = str_replace( '?feature=oembed', '?feature=oembed&rel=0', $src );
			$new_content->set_attribute( 'src', $src );
		}
		$block_content = $new_content->get_updated_html();
	}

	return $block_content;

}
