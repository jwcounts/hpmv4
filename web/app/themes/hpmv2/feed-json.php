<?php
/**
 * JSON Feed Template for displaying JSON Posts feed.
 *
 */
$callback = trim( esc_html( get_query_var( 'callback' ) ) );
$charset  = get_option( 'charset' );
global $wp_query;

if ( 'podcasts' === get_query_var( 'post_type' ) ) :
	$pods = get_option( 'hpm_podcast_settings' );
	if ( !empty( $pods['upload-flats'] ) ) :
		if ( $pods['upload-flats'] == 's3' ) :
			$base_url = 'https://s3-'.$pods['credentials']['s3']['region'].'.amazonaws.com/'.$pods['credentials']['s3']['bucket'].'/'.$pods['credentials']['s3']['folder'].'/';
		elseif ( $pods['upload-flats'] == 'ftp' || $pods['upload-flats'] == 'sftp' ) :
			if ( !empty( $pods['credentials'][$pods['upload-flats']]['folder'] ) ) :
				$folder = "/".$pods['credentials'][$pods['upload-flats']]['folder']."/";
			else :
				$folder = "/";
			endif;
			$base_url = $pods['credentials'][$pods['upload-flats']]['url'].$folder;
		endif;
	else :
		$uploads = wp_upload_dir();
		$base_url = $uploads['basedir'].'/hpm-podcasts/';
	endif;
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
			header( "Content-Type: application/json; charset={$charset}" );
			if ( $pods['upload-flats'] == 'database' ) :
				echo get_option( 'hpm_podcast-json-'.$post->post_name );
			else :
				$remote = wp_remote_get( esc_url_raw( $base_url.$post->post_name.".json" ) );
				if ( !is_wp_error( $remote ) ) :
					echo wp_remote_retrieve_body( $remote );
				endif;
			endif;
		endwhile;
	else :
		status_header( '404' );
		wp_die( "404 Not Found" );
	endif;
else :

	if ( have_posts() ) :
		$server_name = $_SERVER['SERVER_NAME'];
		$uri = $_SERVER['REQUEST_URI'];
		$base = preg_replace( '/feed\/json\/?/', '', $uri );

		$author_name = 'Houston Public Media';
		$author_url = 'https://www.houstonpublicmedia.org';
		$author_avatar = 'https://cdn.hpm.io/assets/images/HPM_UH_ConnectivityLogo_OUT.jpg';
		$desc = 'Houston Public Media provides informative, thought-provoking and entertaining content through a multi-media platform that includes TV 8, News 88.7 and HPM Classical and reaches a combined weekly audience of more than 1.5 million.';
		$icon = 'https://cdn.hpm.io/assets/images/HPM_UH_ConnectivityLogo_OUT.jpg';
		$favicon = 'https://cdn.hpm.io/assets/images/favicon-192x192.png';
		$title = '';

		// Determine feed types (authors, categories, tags, etc.) and fill out feed info accordingly
		$obj = $wp_query->queried_object;
		if ( !empty( $obj->data ) && $obj->data->type == 'wpuser' ) :
			$title = 'Author feed for '.$obj->data->display_name.' | ';
			$author_id = $obj->data->ID;
			$author_name = $obj->data->display_name;
			$authid = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value = '$obj->ID' AND meta_key = 'hpm_staff_authid' LIMIT 1",OBJECT );
			if ( !empty( $authid ) ) :
				$author_check = new WP_Query(
					array(
						'post_type' => 'staff',
						'p' => $authid[0]->post_id,
						'posts_per_page' => 1
					)
				);
				if ( !empty( $author_check ) ) :
					if ( $author_check->post->post_content !== '<p>Biography pending.</p>' ) :
						$desc = wp_trim_words( wp_strip_all_tags( $author_check->post->post_content ), 50, '...' );
					endif;
					$author_url = get_the_permalink( $author_check->post->ID );
					$thumbnail = get_the_post_thumbnail_url( $author_check->post->ID );
					if ( !empty( $thumbnail ) ) :
						$author_avatar = $thumbnail;
					endif;
				endif;
			endif;
		elseif ( !empty( $obj->type ) && $obj->type == 'guest-author' ) :
			$title = 'Author feed for '.$obj->display_name.' | ';
			$author_name = $obj->display_name;
			$author_id = $obj->ID;
			$author_check = new WP_Query(
				array(
					'post_type' => 'staff',
					'name' => $obj->user_nicename,
					'posts_per_page' => 1
				)
			);
			if ( !empty( $author_check ) ) :
				if ( $author_check->post->post_content !== '<p>Biography pending.</p>' ) :
					$desc = wp_trim_words( wp_strip_all_tags( $author_check->post->post_content ), 50, '...' );
				endif;
				$author_url = get_the_permalink( $author_check->post->ID );
				$thumbnail = get_the_post_thumbnail_url( $author_check->post->ID );
				if ( !empty( $thumbnail ) ) :
					$author_avatar = $thumbnail;
				endif;
			endif;
		elseif ( !empty( $obj->taxonomy ) ) :
			if ( $obj->taxonomy == 'category' || $obj->taxonomy == 'post_tag' ) :
				if ( !empty( $obj->description ) ) :
					$desc = $obj->description;
				endif;
				$title = ucwords( str_replace( '_', ' ', $obj->taxonomy ) ).": ".ucwords( $obj->name ).' | ';
			endif;
		endif;
		
		$query_array = $wp_query->query;

		// Make sure query args are always in the same order
		ksort( $query_array );

		$json = array(
			'version' => 'https://jsonfeed.org/version/1',
			'title' => $title . 'Houston Public Media',
			'home_page_url' => 'https://'.$server_name.$base,
			'feed_url' => 'https://'.$server_name.$uri,
			'description' => $desc,
			'icon' => $icon,
			'favicon' => $favicon,
			'author' => array(
				'name' => $author_name,
				'url' => $author_url,
				'avatar' => $author_avatar
			),
			'items' => array()
		);

		while ( have_posts() ) :
			the_post();
			$id = (int) $post->ID;

			$single = array(
				'id' => $id ,
				'title' => get_the_title() ,
				'permalink' => get_permalink(),
				'content_html' => apply_filters( 'hpm_filter_text', get_the_content() ),
				'content_text' => strip_shortcodes( wp_strip_all_tags( get_the_content() ) ),
				'excerpt' => get_the_excerpt(),
				'date_published' => get_the_date( 'c', '', '', false),
				'date_modified' => get_the_modified_date( 'c', '', '', false),
				'author' => coauthors( '; ', '; ', '', '', false ),
				'attachments' => array()
			);

			$media = get_attached_media( '', $id );
			if ( !empty( $media ) ) :
				foreach ( $media as $m ) :
					$url = wp_get_attachment_url( $m->ID );
					$meta = get_post_meta( $m->ID, '_wp_attachment_metadata', true );
					if ( strpos( $m->post_mime_type, 'image' ) !== FALSE ) :
						$single['attachments'][] = array(
							'url' => $url,
							'mime_type' => $m->post_mime_type,
							'filesize' => ( !empty( $meta['filesize'] ) ? $meta['filesize'] : 0 ),
							'width' => ( !empty( $meta['width'] ) ? $meta['width'] : 0 ),
							'height' => ( !empty( $meta['height'] ) ? $meta['height'] : 0 )
						);
					elseif ( strpos( $m->post_mime_type, 'audio' ) !== FALSE ) :
						$single['attachments'][] = array(
							'url' => $url,
							'mime_type' => $m->post_mime_type,
							'filesize' => $meta['filesize'],
							'duration_in_seconds' => $meta['length']
						);
					endif;
				endforeach;
			endif;

			// category
			$single["categories"] = array();
			$categories = get_the_category();
			if ( ! empty( $categories ) ) :
				$single["categories"] = wp_list_pluck( $categories, 'cat_name' );
			endif;

			// tags
			$single["tags"] = array();
			$tags = get_the_tags();
			if ( ! empty( $tags ) ) :
				$single["tags"] = wp_list_pluck( $tags, 'name' );
			endif;
			$json['items'][] = $single;
		endwhile;

		$json = json_encode( $json );

		nocache_headers();
		if ( !empty( $callback ) ) :
			header( "Content-Type: application/x-javascript; charset={$charset}" );
			echo "{$callback}({$json});";
		else :
			header( "Content-Type: application/json; charset={$charset}" );
			echo $json;
		endif;
	else :
		status_header( '404' );
		wp_die( "404 Not Found" );
	endif;
endif;
