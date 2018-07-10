<?php
/**
 * @link 			https://github.com/jwcounts
 * @since  			20170906
 * @package  		HPM-Staff
 *
 * @wordpress-plugin
 * Plugin Name: 	HPM Staff
 * Plugin URI: 		https://github.com/jwcounts
 * Description: 	A custom post type and archive pages for a staff directory
 * Version: 		20170906
 * Author: 			Jared Counts
 * Author URI: 		http://www.houstonpublicmedia.org/staff/jared-counts/
 * License: 		GPL-2.0+
 * License URI: 	http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: 	hpmv2
 *
 * Works best with Wordpress 4.6.0+
 */
add_action( 'init', 'create_staff_post' );
add_action( 'init', 'create_staff_taxonomies' );
function create_staff_post() {
	register_post_type( 'staff',
		array(
			'labels' => array(
				'name' => __( 'Staff' ),
				'singular_name' => __( 'Staff' ),
				'menu_name' => __( 'Staff' ),
				'add_new_item' => __( 'Add New Staff' ),
				'edit_item' => __( 'Edit Staff' ),
				'new_item' => __( 'New Staff' ),
				'view_item' => __( 'View Staff' ),
				'search_items' => __( 'Search Staff' ),
				'not_found' => __( 'Staff Not Found' ),
				'not_found_in_trash' => __( 'Staff not found in trash' )
			),
			'description' => 'Staff Members of Houston Public Media',
			'public' => true,
			'menu_position' => 20,
			'menu_icon' => 'dashicons-groups',
			'has_archive' => true,
			'rewrite' => array(
				'slug' => __( 'staff' ),
				'with_front' => false,
				'feeds' => false,
				'pages' => true
			),
			'supports' => array( 'title', 'editor', 'thumbnail' ),
			'taxonomies' => array('staff_category'),
			'capability_type' => array('hpm_staffer','hpm_staffers'),
			'map_meta_cap' => true,
		)
	);
}

function create_staff_taxonomies() {
	register_taxonomy('staff_category', 'staff', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => _x( 'Staff Category', 'taxonomy general name' ),
			'singular_name' => _x( 'staff-category', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Staff Categories' ),
			'all_items' => __( 'All Staff Categories' ),
			'parent_item' => __( 'Parent Staff Category' ),
			'parent_item_colon' => __( 'Parent Staff Category:' ),
			'edit_item' => __( 'Edit Staff Category' ),
			'update_item' => __( 'Update Staff Category' ),
			'add_new_item' => __( 'Add New Staff Category' ),
			'new_item_name' => __( 'New Staff Category Name' ),
			'menu_name' => __( 'Staff Categories' )
		),
		'public' => true,
		'rewrite' => array(
			'slug' => 'staff-category',
			'with_front' => false,
			'hierarchical' => true
		)
	));
}

add_action('admin_init','hpm_staff_add_role_caps',999);
function hpm_staff_add_role_caps() {
	// Add the roles you'd like to administer the custom post types
	$roles = array('editor','administrator','author');

	// Loop through each role and assign capabilities
	foreach($roles as $the_role) :
		$role = get_role($the_role);
        if ( $the_role != 'author' ) :
            $role->add_cap( 'read' );
            $role->add_cap( 'read_hpm_staffer');
	        $role->add_cap( 'add_hpm_staffer' );
	        $role->add_cap( 'add_hpm_staffers' );
            $role->add_cap( 'read_private_hpm_staffers' );
            $role->add_cap( 'edit_hpm_staffer' );
            $role->add_cap( 'edit_hpm_staffers' );
            $role->add_cap( 'edit_others_hpm_staffers' );
            $role->add_cap( 'edit_published_hpm_staffers' );
            $role->add_cap( 'publish_hpm_staffers' );
            $role->add_cap( 'delete_others_hpm_staffers' );
            $role->add_cap( 'delete_private_hpm_staffers' );
            $role->add_cap( 'delete_published_hpm_staffers' );
        else :
	        $role->add_cap( 'read' );
	        $role->add_cap( 'read_hpm_staffer');
	        $role->remove_cap( 'add_hpm_staffer' );
	        $role->remove_cap( 'add_hpm_staffers' );
	        $role->remove_cap( 'read_private_hpm_staffers' );
	        $role->add_cap( 'edit_hpm_staffer' );
	        $role->add_cap( 'edit_hpm_staffers' );
	        $role->remove_cap( 'edit_others_hpm_staffers' );
	        $role->remove_cap( 'edit_published_hpm_staffers' );
	        $role->remove_cap( 'publish_hpm_staffers' );
	        $role->remove_cap( 'delete_others_hpm_staffers' );
	        $role->remove_cap( 'delete_private_hpm_staffers' );
	        $role->remove_cap( 'delete_published_hpm_staffers' );
        endif;
	endforeach;
}

add_action( 'load-post.php', 'hpm_staff_setup' );
add_action( 'load-post-new.php', 'hpm_staff_setup' );
function hpm_staff_setup() {
	add_action( 'add_meta_boxes', 'hpm_staff_add_meta' );
	add_action( 'save_post', 'hpm_staff_save_meta', 10, 2 );
}

function hpm_staff_add_meta() {
	add_meta_box(
		'hpm-staff-meta-class',
		esc_html__( 'Title, Social Media, Etc.', 'example' ),
		'hpm_staff_meta_box',
		'staff',
		'normal',
		'core'
	);
}

function hpm_staff_meta_box( $object, $box ) {
	wp_nonce_field( basename( __FILE__ ), 'hpm_staff_class_nonce' );

	$exists_meta = metadata_exists( 'post', $object->ID, 'hpm_staff_meta' );
	$exists_alpha = metadata_exists( 'post', $object->ID, 'hpm_staff_alpha' );
	$exists_authid = metadata_exists( 'post', $object->ID, 'hpm_staff_authid' );

	if ( $exists_meta ) :
		$hpm_staff_meta = get_post_meta( $object->ID, 'hpm_staff_meta', true );
		if ( empty( $hpm_staff_meta ) ) :
			$hpm_staff_meta = array('title' => '', 'email' => '', 'twitter' => '', 'facebook' => '', 'phone' => '');
		endif;
	else :
		$hpm_staff_meta = array('title' => '', 'email' => '', 'twitter' => '', 'facebook' => '', 'phone' => '');
	endif;

	if ( $exists_alpha ) :
		$hpm_staff_alpha = get_post_meta( $object->ID, 'hpm_staff_alpha', true );
		if ( empty( $hpm_staff_alpha ) ) :
			$hpm_staff_alpha = array('', '');
		else :
			$hpm_staff_alpha = explode( '|', $hpm_staff_alpha );
		endif;
	else :
		$hpm_staff_alpha = array( '', '' );
	endif;

	if ( $exists_authid ) :
        $hpm_staff_authid = get_post_meta( $object->ID, 'hpm_staff_authid', true );
    else :
        $hpm_staff_authid = '';
    endif;

	?>
	<p><?PHP _e( "Enter the staff member's details below", 'example' ); ?></p>
	<ul>
		<li><label for="hpm-staff-name-first"><?php _e( "First Name: ", 'example' ); ?></label> <input type="text" id="hpm-staff-name-first" name="hpm-staff-name-first" value="<?PHP echo $hpm_staff_alpha[1]; ?>" placeholder="Kenny" style="width: 60%;" /></li>
		<li><label for="hpm-staff-name-last"><?php _e( "Last Name: ", 'example' ); ?></label> <input type="text" id="hpm-staff-name-last" name="hpm-staff-name-last" value="<?PHP echo $hpm_staff_alpha[0]; ?>" placeholder="Loggins" style="width: 60%;" /></li>
		<li><label for="hpm-staff-title"><?php _e( "Job Title: ", 'example' ); ?></label> <input type="text" id="hpm-staff-title" name="hpm-staff-title" value="<?PHP echo $hpm_staff_meta['title']; ?>" placeholder="Top Gun" style="width: 60%;" /></li>
		<li><label for="hpm-staff-email"><?php _e( "Email: ", 'example' ); ?></label> <input type="text" id="hpm-staff-email" name="hpm-staff-email" value="<?PHP echo $hpm_staff_meta['email']; ?>" placeholder="highway@thedanger.zone" style="width: 60%;" /></li>
		<li><label for="hpm-staff-fb"><?php _e( "Facebook: ", 'example' ); ?></label> <input type="text" id="hpm-staff-fb" name="hpm-staff-fb" value="<?PHP echo $hpm_staff_meta['facebook']; ?>" placeholder="https://facebook.com/first.last" style="width: 60%;" /></li>
		<li><label for="hpm-staff-twitter"><?php _e( "Twitter: ", 'example' ); ?></label> <input type="text" id="hpm-staff-twitter" name="hpm-staff-twitter" value="<?PHP echo $hpm_staff_meta['twitter']; ?>" placeholder="https://twitter.com/houpubmedia" style="width: 60%;" /></li>
		<li><label for="hpm-staff-phone"><?php _e( "Phone: ", 'example' ); ?></label> <input type="text" id="hpm-staff-phone" name="hpm-staff-phone" value="<?PHP echo $hpm_staff_meta['phone']; ?>" placeholder="(713) 555-5555" style="width: 60%;" /></li>
		<li><label for="hpm-staff-author"><?php _e( "Author ID:", 'example' ); ?></label> <?php
			wp_dropdown_users([
				'show_option_none' => 'None',
				'show' => 'display_name',
				'echo' => true,
				'selected' => $hpm_staff_authid,
				'include_selected' => true,
				'name' => 'hpm-staff-author',
				'id' => 'hpm-staff-author'
			]); ?></li>
	</ul>
<?php }

function hpm_staff_save_meta( $post_id, $post ) {
	if ($post->post_type == 'staff') :
		/* Verify the nonce before proceeding. */
		if ( !isset( $_POST['hpm_staff_class_nonce'] ) || !wp_verify_nonce( $_POST['hpm_staff_class_nonce'], basename( __FILE__ ) ) )
		return $post_id;

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

		/* Get the posted data and sanitize it for use as an HTML class. */
		$hpm_staff = array(
			'title'		=> ( isset( $_POST['hpm-staff-title'] ) ? sanitize_text_field( $_POST['hpm-staff-title'] ) : '' ),
			'email'		=> ( isset( $_POST['hpm-staff-email'] ) ? sanitize_text_field( $_POST['hpm-staff-email'] ) : '' ),
			'facebook'	=> ( isset( $_POST['hpm-staff-fb'] ) ? sanitize_text_field( $_POST['hpm-staff-fb'] ) : '' ),
			'twitter'	=> ( isset( $_POST['hpm-staff-twitter'] ) ? sanitize_text_field( $_POST['hpm-staff-twitter'] ) : '' ),
			'phone'	=> ( isset( $_POST['hpm-staff-phone'] ) ? sanitize_text_field( $_POST['hpm-staff-phone'] ) : '')
		);
		$hpm_first = ( isset( $_POST['hpm-staff-name-first'] ) ? sanitize_text_field( $_POST['hpm-staff-name-first'] ) : '' );
		$hpm_last = ( isset( $_POST['hpm-staff-name-last'] ) ? sanitize_text_field( $_POST['hpm-staff-name-last'] ) : '' );
		$hpm_staff_alpha = $hpm_last."|".$hpm_first;
		$hpm_staff_authid = ( isset( $_POST['hpm-staff-author'] ) ? sanitize_text_field( $_POST['hpm-staff-author'] ) : '' );

		if ( !empty( $hpm_staff_authid ) ) :
			update_post_meta( $post_id, 'hpm_staff_authid', $hpm_staff_authid );
        endif;
		update_post_meta( $post_id, 'hpm_staff_meta', $hpm_staff );
		update_post_meta( $post_id, 'hpm_staff_alpha', $hpm_staff_alpha );
	endif;
}

add_filter( 'manage_edit-staff_columns', 'hpm_edit_staff_columns' ) ;
function hpm_edit_staff_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Name' ),
		'job_title' => __( 'Title' ),
		'staff_category' => __( 'Departments' ),
		'authorship' => __( 'Author?' )
	);
	return $columns;
}

add_action( 'manage_staff_posts_custom_column', 'hpm_manage_staff_columns', 10, 2 );
function hpm_manage_staff_columns( $column, $post_id ) {
	global $post;
	$staff_meta = get_post_meta( $post_id, 'hpm_staff_meta', true );
	$staff_authid = get_post_meta( $post_id, 'hpm_staff_authid', true );
	switch( $column ) {
		case 'job_title' :
			if ( empty( $staff_meta['title'] ) ) :
				echo __( 'None' );
			else :
				echo __( $staff_meta['title'] );
			endif;
			break;
		case 'authorship' :
			if ( !empty( $staff_authid ) ) :
				echo __( 'Yes' );
			else :
				echo __( 'No' );
			endif;
			break;
		case 'staff_category' :
			$terms = get_the_terms( $post_id, 'staff_category' );
			if ( !empty( $terms ) ) :
				$out = array();
				foreach ( $terms as $term ) :
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'staff_category' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'staff_category', 'display' ) )
					);
				endforeach;
				echo join( ', ', $out );
			else :
				_e( 'No Department Affiliations' );
			endif;
			break;
		default :
			break;
	}
}

add_action('restrict_manage_posts', 'hpm_filter_post_type_by_taxonomy');
function hpm_filter_post_type_by_taxonomy() {
	global $typenow;
	$post_type = 'staff'; // change to your post type
	$taxonomy  = 'staff_category'; // change to your taxonomy
	if ($typenow == $post_type) {
		$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$info_taxonomy = get_taxonomy($taxonomy);
		wp_dropdown_categories(array(
			'show_option_all' => __("Show All {$info_taxonomy->label}"),
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'orderby'         => 'name',
			'selected'        => $selected,
			'hierarchical'    => true,
			'depth'           => 3,
			'show_count'      => true,
			'hide_empty'      => true,
		));
	};
}

add_filter('parse_query', 'hpm_convert_id_to_term_in_query');
function hpm_convert_id_to_term_in_query($query) {
	global $pagenow;
	$post_type = 'staff'; // change to your post type
	$taxonomy  = 'staff_category'; // change to your taxonomy
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}

/*
 * Changes number of posts loaded when viewing the staff directory
 */
function staff_meta_query( $query ) {
	if ( $query->is_archive() && $query->is_main_query() ) :
		$staff_check = $query->get( 'post_type' );
		if ( $staff_check == 'staff' ) :
			$query->set( 'posts_per_page', 30 );
			$query->set( 'meta_query', array( 'hpm_staff_alpha' => array( 'key' => 'hpm_staff_alpha' ) ) );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
		endif;
	endif;
}
add_action( 'pre_get_posts', 'staff_meta_query' );

/*
 * Always sort staff alphabetically by hpm_staff_alpha ( last name, first name )
 */
function staff_tax_meta_query( $query ) {
	if ( $query->is_archive() && $query->is_main_query() ) :
		$staff_check = $query->get( 'staff_category' );
		if ( !empty( $staff_check ) ) :
			$query->set( 'posts_per_page', 30 );
			$query->set( 'meta_query', array( 'hpm_staff_alpha' => array( 'key' => 'hpm_staff_alpha' ) ) );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
		endif;
	endif;
}
add_action( 'pre_get_posts', 'staff_tax_meta_query' );