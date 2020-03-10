<?php
function author_footer( $id ) {
	$output = '';
	$author_terms = get_the_terms( $id, 'author' );
	if ( empty( $author_terms ) ) :
		return $output;
	endif;
	$matches = [];
	preg_match( "/([a-z\-]+) ([0-9]{1,3})/", $author_terms[0]->description, $matches );
	if ( empty( $matches ) ) :
		return $output;
	endif;
	$author_name = $matches[1];
	$author_trans = get_transient( 'hpm_author_'.$author_name );
	if ( !empty( $author_trans ) ) :
		return $author_trans;
	endif;

	$authid = $matches[2];
	$author_check = new WP_Query( [
		'post_type' => 'staff',
		'name' => $author_name,
		'post_status' => 'publish'
	] );
	if ( !$author_check->have_posts() ) :
		$author_check = new WP_Query([
			'post_type' => 'staff',
			'post_status' => 'publish',
			'meta_query' => [ [
				'key' => 'hpm_staff_authid',
				'compare' => '=',
				'value' => $authid
			] ]
		] );
	endif;
	if ( !$author_check->have_posts() ) :
		return $output;
	endif;
	while ( $author_check->have_posts() ) :
		$author_check->the_post();
		$author = get_post_meta( get_the_ID(), 'hpm_staff_meta', TRUE );
		$authid = get_post_meta( get_the_ID(), 'hpm_staff_authid', TRUE );
		$output .= '<div class="author-info-wrap"><div class="author-image">'.get_the_post_thumbnail( get_the_ID(), 'post-thumbnail', [ 'alt' => get_the_title() ] ).'</div><div class="author-info"><h2>'.get_the_title().'</h2><h3>'.$author['title'].'</h3><div class="author-social">';
		if ( !empty( $author['facebook'] ) ) :
			$output .= '<div class="social-icon"><a href="'.$author['facebook'].'" target="_blank"><span class="fa fa-facebook" aria-hidden="true"></span></a></div>';
		endif;
		if ( !empty( $author['twitter'] ) ) :
			$output .= '<div class="social-icon"><a href="'.$author['twitter'].'" target="_blank"><span class="fa fa-twitter" aria-hidden="true"></span></a></div>';
		endif;
		$author_bio = get_the_content();
		if ( preg_match( '/Biography pending/', $author_bio ) ) :
			$author_bio = '';
		endif;
		$output .= '</div><p>'.wp_trim_words( $author_bio, 50, '...' ).'</p><p><a href="'.get_the_permalink().'">More Information</a></p></div>';
	endwhile;
	$output .= '</div><div class="author-other-stories">';
	$q = new WP_query([
		'posts_per_page' => 5,
		'author' => $authid,
		'post_type' => 'post',
		'post_status' => 'publish'
	 ] );
	if ( $q->have_posts() ) :
		$output .= "<h4>Recent Stories</h4><ul>";
		while ( $q->have_posts() ) :
			$q->the_post();
			$output .= '<li><h2 class="entry-title"><a href="'.esc_url( get_permalink() ).'" rel="bookmark">'.get_the_title().'</a></h2></li>';
		endwhile;
		$output .= "</ul>";
	endif;
	wp_reset_query();
	$output .= '</div>';
	set_transient( 'hpm_author_'.$author_name, $output, 7200 );
	return $output;
}



function hpm_houston_matters_check() {
	$hm_air = get_transient( 'hpm_hm_airing' );
	if ( !empty( $hm_air ) ) :
		return $hm_air;
	endif;
	$t = time();
	$offset = get_option( 'gmt_offset' ) * 3600;
	$t = $t + $offset;
	$date = date( 'Y-m-d', $t );
	$hm_airtimes = [
		9 => false,
		19 => false
	];
	$remote = wp_remote_get( esc_url_raw( "https://api.composer.nprstations.org/v1/widget/519131dee1c8f40813e79115/day?date=".$date."&format=json" ) );
	if ( is_wp_error( $remote ) ) :
		return false;
	else :
		$api = wp_remote_retrieve_body( $remote );
		$json = json_decode( $api, TRUE );
		foreach ( $json['onToday'] as $j ) :
			if ( $j['program']['name'] == 'Houston Matters with Craig Cohen' ) :
				if ( $j['start_time'] == '09:00' ) :
					$hm_airtimes[9] = true;
				elseif ( $j['start_time'] == '19:00' ) :
					$hm_airtimes[19] = true;
				endif;
			endif;
		endforeach;
	endif;
	set_transient( 'hpm_hm_airing', $hm_airtimes, 3600 );
	return $hm_airtimes;
}

function hpm_chartbeat() {
	global $wp_query;
	$anc = get_post_ancestors( get_the_ID() );
	if ( !in_array( 61383, $anc ) ) : ?>
		<script type='text/javascript'>
			var _sf_async_config={};
			/** CONFIGURATION START **/
			_sf_async_config.uid = 33583;
			_sf_async_config.domain = 'houstonpublicmedia.org';
			_sf_async_config.useCanonical = true;
			_sf_async_config.sections = "<?php echo ( is_front_page() ? 'News, Arts & Culture, Education' : str_replace( '&amp;', '&', wp_strip_all_tags( get_the_category_list( ', ', 'multiple', get_the_ID() ) ) ) );
			?>";
			_sf_async_config.authors = "<?php echo ( is_front_page() ? 'Houston Public Media' : coauthors( ',', ',', '', '', false ) ); ?>";
			(function(){
				function loadChartbeat() {
					window._sf_endpt=(new Date()).getTime();
					var e = document.createElement('script');
					e.setAttribute('language', 'javascript');
					e.setAttribute('type', 'text/javascript');
					e.setAttribute('src', '//static.chartbeat.com/js/chartbeat.js');
					document.body.appendChild(e);
				}
				var oldonload = window.onload;
				window.onload = (typeof window.onload != 'function') ?
					loadChartbeat : function() { oldonload(); loadChartbeat(); };
			})();
		</script>
<?php
	endif;
}

function hpm_masonry() {
	wp_reset_query();
	global $wp_query;
	$post_type = get_post_type();
	if ( is_page_template( 'page-main-categories.php' ) || is_front_page() || ( $post_type == 'shows' && !is_page_template( 'single-shows-health-matters.php' ) && !is_page_template( 'single-shows-skyline.php' ) ) || is_page_template( 'page-series-tiles.php' ) || is_page_template( 'page-vietnam.php' ) ) :
		if ( get_the_ID() != 61247 ) : ?>
	<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
	<script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.js"></script>
	<script>
		function masonLoad() {
			var isActive = false;
			if ( window.wide > 800 )
			{
				imagesLoaded( '#float-wrap', function() {
					var msnry = new Masonry( '#float-wrap', {
						itemSelector: '.grid-item',
						stamp: '.stamp',
						columnWidth: '.grid-sizer'
					});
					isActive = true;
				});
<?php
		/*
			Manually set the top pixel offset of the NPR articles box on the homepage, since Masonry doesn't calculate offsets for stamped elements
		*/
			if ( is_front_page() ) : ?>
				var topSched = document.querySelector('#top-schedule-wrap').getBoundingClientRect().height;
				document.getElementById('npr-side').style.cssText += 'top: '+topSched+'px';
<?php
			endif; ?>
			}
			else
			{
				if ( isActive ) {
					msnry.destroy();
					isActive = !isActive;
				}
				var gridItem = document.querySelectorAll('.grid-item');
				for ( i = 0; i < gridItem.length; ++i ) {
					gridItem[i].removeAttribute('style');
				}
			}
		}
		document.addEventListener("DOMContentLoaded", function() {
			masonLoad();
			var resizeTimeout;
			function resizeThrottler() {
				if ( !resizeTimeout ) {
					resizeTimeout = setTimeout(function() {
						resizeTimeout = null;
						masonLoad();
					}, 66);
				}
			}
			window.addEventListener("resize", resizeThrottler(), false);
			window.setTimeout(masonLoad(), 5000);
		});
	</script>
<?php
			endif;
		endif;
}

function hpm_hm_banner() {
	wp_reset_query();
	global $wp_query;
	$t = time();
	$offset = get_option('gmt_offset')*3600;
	$t = $t + $offset;
	$now = getdate($t);
	if ( !empty( $_GET['testtime'] ) ) :
		$tt = explode( '-', $_GET['testtime'] );
		$now = getdate( mktime( $tt[0], $tt[1], 0, $tt[2], $tt[3], $tt[4] ) );
	endif;
	$anc = get_post_ancestors( get_the_ID() );
	$bans = [ 135762, 290722, 303436, 303018, 315974 ];
	$hm_air = hpm_houston_matters_check();
	if ( !in_array( 135762, $anc ) && !in_array( get_the_ID(), $bans ) ) :
		if ( ( $now['wday'] > 0 && $now['wday'] < 6 ) && ( $now['hours'] == 9 || $now['hours'] == 19 ) && $hm_air[ $now['hours'] ] ) : ?>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			document.getElementById('hm-top').innerHTML = '<?php echo ( $now['hours'] == 19 ? '<p><span>This is an encore presentation of <strong>Houston Matters</strong>, but you can still get in touch:</span> Call <strong>713-440-8870</strong> | Email <a href="mailto:talk@houstonmatters.org">talk@houstonmatters.org</a> | Tweet <a href="https://twitter.com/houstonmatters">@houstonmatters</a></p>': '<p><span><strong>Houston Matters</strong> is on the air now! Join the conversation:</span> Call <strong>713-440-8870</strong> | Email <a href="mailto:talk@houstonmatters.org">talk@houstonmatters.org</a> | Tweet <a href="https://twitter.com/houstonmatters">@houstonmatters</a></p>' ); ?>';
			var topBanner = document.getElementById('hm-top');
			for (i = 0; i < topBanner.length; ++i) {
				topBanner[i].addEventListener('click', function() {
					var attr = this.id;
					if ( typeof attr !== typeof undefined && attr !== false) {
						ga('send', 'event', 'Top Banner', 'click', attr);
						ga('hpmRollup.send', 'event', 'Top Banner', 'click', attr);
					}
				});
			}
		});
	</script>
<?php
		endif;
	endif;
}
add_action( 'wp_footer', 'hpm_chartbeat', 100 );
add_action( 'wp_footer', 'hpm_masonry', 99 );
add_action( 'wp_footer', 'hpm_hm_banner', 100 );