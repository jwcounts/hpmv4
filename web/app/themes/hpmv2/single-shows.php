<?php
/*
Template Name: Default Show
Template Post Type: shows
*/
/**
 * The template for displaying show pages
 *
 * @package WordPress
 * @subpackage HPMv2
 * @since HPMv2 1.0
 */

get_header(); ?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<?php
			while ( have_posts() ) : the_post();
				$show_name = $post->post_name;
				$social = get_post_meta( get_the_ID(), 'hpm_show_social', true );
				$show = get_post_meta( get_the_ID(), 'hpm_show_meta', true );
				$header_back = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' ); 
				$show_title = get_the_title(); 
				$show_content = get_the_content(); ?>
			<header class="page-header<?php echo (!empty( $header_back ) ? '" style="background-image: url(\''.$header_back[0].'\');"' : ' no-back'); ?>">
				<h1 class="page-title<?php echo (!empty( $header_back ) ? ' screen-reader-text' : ''); ?>"><?php the_title(); ?></h1>
			<?php
				$no = $sp = $c = 0;
				foreach( $show as $sh ) :
					if ( empty( $sh ) ) :
						$no++;
					endif;
				endforeach;
				foreach( $social as $soc ) :
					if ( empty( $soc ) ) :
						$no++;
					endif;
				endforeach;
				if ( $no > 0 ) : ?>
				<div id="station-social">
			<?php
					if ( !empty( $show['times'] ) ) : ?>
					<h3><?php echo $show['times']; ?></h3>
			<?php
					endif;
					if ( !empty( $social['fb'] ) ) : ?>
					<div class="station-social-icon">
						<a href="https://www.facebook.com/<?php echo $social['fb']; ?>" target="_blank" title="Facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
					</div>
			<?php
					endif;
					if ( !empty( $social['twitter'] ) ) : ?>
					<div class="station-social-icon">
						<a href="https://twitter.com/<?php echo $social['twitter']; ?>" target="_blank" title="Twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
					</div>
			<?php
					endif;
					if ( !empty( $social['yt'] ) ) : ?>
					<div class="station-social-icon">
						<a href="<?php echo $social['yt']; ?>" target="_blank" title="YouTube"><span class="fa fa-youtube-play" aria-hidden="true"></span></a>
					</div>
			<?php
					endif;
					if ( !empty( $social['sc'] ) ) : ?>
					<div class="station-social-icon">
						<a href="https://soundcloud.com/<?php echo $social['sc']; ?>" target="_blank" title="SoundCloud"><span class="fa fa-soundcloud" aria-hidden="true"></span></a>
					</div>
			<?php
					endif;
					if ( !empty( $social['insta'] ) ) : ?>
					<div class="station-social-icon">
						<a href="https://instagram.com/<?php echo $social['insta']; ?>" target="_blank" title="Instagram"><span class="fa fa-instagram" aria-hidden="true"></span></a>
					</div>
			<?php
					endif;
					if ( !empty( $social['tumblr'] ) ) : ?>
					<div class="station-social-icon">
						<a href="<?php echo $social['tumblr']; ?>" target="_blank" title="Tumblr"><span class="fa fa-tumblr" aria-hidden="true"></span></a>
					</div>
			<?php
					endif;
					if ( !empty( $social['snapchat'] ) ) : ?>
					<div class="station-social-icon">
						<a href="http://www.snapchat.com/add/<?php echo $social['snapchat']; ?>" target="_blank" title="Snapchat"><span class="fa fa-snapchat-ghost" aria-hidden="true"></span></a>
					</div>
			<?php
					endif;
					if ( !empty( $show['itunes'] ) ) : ?>
					<div class="station-social-icon">
						<a href="<?php echo $show['itunes']; ?>" target="_blank" title="iTunes Feed"><span class="fa fa-apple" aria-hidden="true"></span></a>
					</div>
			<?php
					endif;
					if ( !empty( $show['podcast'] ) ) : ?>
					<div class="station-social-icon">
						<a href="<?php echo $show['podcast']; ?>" target="_blank" title="Podcast Feed"><span class="fa fa-rss" aria-hidden="true"></span></a>
					</div>
			<?php
					endif;
					if ( !empty( $show['gplay'] ) ) : ?>
					<div class="station-social-icon">
						<a href="<?php echo $show['gplay']; ?>" target="_blank" title="Google Play Podcasts Feed"><span class="fa fa-google" aria-hidden="true"></span></a>
					</div>
			<?php
					endif; ?>
				</div>
			<?php 
				endif;?>
			</header>
		<?php
			endwhile; ?>
			<div id="float-wrap">
				<div class="grid-sizer"></div>
				<aside class="column-right grid-item stamp">
					<h3>About <?php echo $show_title; ?></h3>
					<div class="show-content">
						<?php echo apply_filters( 'the_content', $show_content ); ?>
					</div>
			<?php
						echo HPM_Listings::generate( $show_name );
						if ( $show_name == 'skyline-sessions' || $show_name == 'music-in-the-making' ) :
							$googletag = 'div-gpt-ad-1470409396951-0';
						else :
							$googletag = 'div-gpt-ad-1394579228932-1';
						endif; ?>
						<div class="sidebar-ad">
							<div id="<?php echo $googletag; ?>">
								<h4>Support Comes From</h4>
								<script type='text/javascript'>
									googletag.cmd.push(function() { googletag.display('<?php echo $googletag; ?>'); });
								</script>
							</div>
						</div>
					</aside>
		<?php
			$cat_no = get_post_meta( get_the_ID(), 'hpm_shows_cat', true );
			$top =  get_post_meta( get_the_ID(), 'hpm_shows_top', true );
			$terms = get_terms( array( 'include'  => $cat_no, 'taxonomy' => 'category' ) );
			$term = reset( $terms );
			$cat_args = array(
				'cat' => $cat_no,
				'orderby' => 'date',
				'order'   => 'DESC',
				'posts_per_page' => 15,
				'ignore_sticky_posts' => 1
			);
			if ( !empty( $top ) && $top !== 'None' ) :
				$top_art = new WP_query( [ 'p' => $top ] );
				$cat_args['posts_per_page'] = 14;
				$cat_args['post__not_in'] = [ $top ];
				if ( $top_art->have_posts() ) :
					while ( $top_art->have_posts() ) : $top_art->the_post();
						$postClass = get_post_class();
						$postClass[] = 'grid-item';
						$fl_array = preg_grep("/felix-type-/", $postClass);
						$fl_arr = array_keys( $fl_array );
						$postClass[] = 'pinned';
						$postClass[] = 'grid-item--width2';
						if ( has_post_thumbnail() ) :
							$postClass[$fl_arr[0]] = 'felix-type-a';
						else :
							$postClass[$fl_arr[0]] = 'felix-type-b';
						endif;
						$thumbnail_type = 'large'; ?>
						<article id="post-<?php the_ID(); ?>" <?php echo "class=\"".implode( ' ', $postClass )."\""; ?>>
							<?php
							if ( has_post_thumbnail() ) : ?>
								<div class="thumbnail-wrap" style="background-image: url(<?php the_post_thumbnail_url($thumbnail_type); ?>)">
									<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true"></a>
								</div>
							<?php
							endif; ?>
							<header class="entry-header">
								<h3><?php echo hpm_top_cat( get_the_ID() ); ?></h3>
								<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
								<div class="screen-reader-text"><?PHP coauthors_posts_links( ' / ', ' / ', '<address class="vcard author">', '</address>', true ); ?> </div>
							</header><!-- .entry-header -->
						</article>
					<?PHP
					endwhile;
					$post_num = 14;
				endif;
				wp_reset_query();
			endif;
			$cat = new WP_query( $cat_args );
			if ( $cat->have_posts() ) :
				while ( $cat->have_posts() ) : $cat->the_post();
					$postClass = get_post_class();
					$postClass[] = 'grid-item';
					$fl_array = preg_grep("/felix-type-/", $postClass);
					$fl_arr = array_keys( $fl_array );
					if ( $cat->current_post == 0 && empty( $top_art ) ) :
						$postClass[] = 'pinned';
						$postClass[] = 'grid-item--width2';
						if ( has_post_thumbnail() ) :
							$postClass[$fl_arr[0]] = 'felix-type-a';
						else :
							$postClass[$fl_arr[0]] = 'felix-type-b';
						endif;
					else :
						$postClass[$fl_arr[0]] = 'felix-type-d';
					endif;
					if ( in_array( 'felix-type-a', $postClass ) ) :
						$thumbnail_type = 'large';
					else :
						$thumbnail_type = 'thumbnail';
					endif; ?>
					<article id="post-<?php the_ID(); ?>" <?php echo "class=\"".implode( ' ', $postClass )."\""; ?>>
						<?php
						if ( has_post_thumbnail() ) : ?>
							<div class="thumbnail-wrap" style="background-image: url(<?php the_post_thumbnail_url($thumbnail_type); ?>)">
								<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true"></a>
							</div>
						<?php
						endif; ?>
						<header class="entry-header">
							<h3><?php echo hpm_top_cat( get_the_ID() ); ?></h3>
							<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
							<div class="screen-reader-text"><?PHP coauthors_posts_links( ' / ', ' / ', '<address class="vcard author">', '</address>', true ); ?> </div>
						</header><!-- .entry-header -->
					</article>
				<?PHP
				endwhile;
			endif; ?>
			</div>
		<?php
			if ( $cat->found_posts > 15 ) : ?>
			<div class="readmore">
				<a href="/topics/<?php echo $term->slug; ?>/page/2">View More <?php echo $term->name; ?></a>
			</div>
		<?php
			endif;
					if ( !empty( $show['ytp'] ) ) : ?>
			<div id="shows-youtube">
				<div id="youtube-wrap">
				<?php
					$json = hpm_youtube_playlist( $show['ytp'] );
					foreach ( $json as $tubes ) :
						$pubtime = strtotime( $tubes['snippet']['publishedAt'] );
						if ( $c == 0 ) : ?>
					<div id="youtube-main">
						<div id="youtube-player" style="background-image: url( '<?php echo $tubes['snippet']['thumbnails']['high']['url']; ?>' );" data-ytid="<?php echo $tubes['snippet']['resourceId']['videoId']; ?>" data-yttitle="<?php echo htmlentities( $tubes['snippet']['title'], ENT_COMPAT ); ?>">
							<span class="fa fa-play" id="play-button"></span>
						</div>
						<h2><?php echo $tubes['snippet']['title']; ?></h2>
						<p class="desc"><?php echo $tubes['snippet']['description']; ?></p>
						<p class="date"><?php echo date( 'F j, Y', $pubtime); ?></p>
					</div>
					<div id="youtube-upcoming">
						<h4>Past Shows</h4>
					<?php
						endif; ?>
						<div class="youtube" id="<?php echo $tubes['snippet']['resourceId']['videoId']; ?>" data-ytid="<?php echo $tubes['snippet']['resourceId']['videoId']; ?>" data-yttitle="<?php echo htmlentities( $tubes['snippet']['title'], ENT_COMPAT ); ?>" data-ytdate="<?php echo date( 'F j, Y', $pubtime); ?>" data-ytdesc="<?php echo htmlentities($tubes['snippet']['description']); ?>">
							<img src="<?php echo $tubes['snippet']['thumbnails']['medium']['url']; ?>" alt="<?php echo $tubes['snippet']['title']; ?>" />
							<h2><?php echo $tubes['snippet']['title']; ?></h2>
							<p class="date"><?php echo date( 'F j, Y', $pubtime); ?></p>
						</div>
					<?php
						$c++;
					endforeach; ?>
					</div>
				</div>
			</div>
			<script>
				var tag = document.createElement('script');
				tag.src = "//www.youtube.com/player_api";
				var firstScriptTag = document.getElementsByTagName('script')[0];
				firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
				function ytdimensions() {
					var youtube = document.getElementById('youtube-player');
					window.ytwide = youtube.getBoundingClientRect().width;
					window.ythigh = ytwide/1.77777777777778;
					youtube.style.height = ythigh+'px';
				}
				function parseURL(url) {
					var parser = document.createElement('a'),
						searchObject = {},
						queries, split, i;
					// Let the browser do the work
					parser.href = url;
					// Convert query string to object
					queries = parser.search.replace(/^\?/, '').split('&');
					for( i = 0; i < queries.length; i++ ) {
						split = queries[i].split('=');
						searchObject[split[0]] = split[1];
					}
					return {
						protocol: parser.protocol,
						host: parser.host,
						hostname: parser.hostname,
						port: parser.port,
						pathname: parser.pathname,
						search: parser.search,
						searchObject: searchObject,
						hash: parser.hash
					};
				}
				function onPlayerReady(event) {
					if (navigator.userAgent.match(/(iPad|iPhone|iPod touch)/i) == null)
					{
						event.target.playVideo();
					}
				}
				function onPlayerStateChange(event) {
					if (event.data == YT.PlayerState.ENDED)
					{
						var current = parseURL(player.getVideoUrl());
						var nextVid = document.getElementById(current.searchObject.v).nextSibling();
						var newYtid = nextVid.getAttribute('data-ytid');
						if ( newYtid !== undefined )
						{
							var yttitle = nextVid.getAttribute('data-yttitle');
							var ytdesc = nextVid.getAttribute('data-ytdesc');
							var ytdate = nextVid.getAttribute('data-ytdate');
							ytid = newYtid;
							player.stopVideo();
							player.loadVideoById({
								videoId: ytid
							});
							var d = document.getElementById('youtube-main');
							d.querySelector('h2').innerHTML = yttitle;
							d.querySelector('.desc').innerHTML = ytdesc;
							d.querySelector('.date').innerHTML = ytdate;
							var c = document.getElementById('yt-nowplay');
							c.parentNode.removeChild(c);
							document.getElementById(newYtid).innerHTML += '<div id="yt-nowplay">Now Playing</div>';
						}
						else
						{
							return false;
						}
					}
				}
				document.addEventListener("DOMContentLoaded", function() {
					ytdimensions();
					var resizeTimeout;
					function resizeThrottler() {
						if (!resizeTimeout) {
							resizeTimeout = setTimeout(function () {
								resizeTimeout = null;
								ytdimensions();
							}, 66);
						}
					}
					window.addEventListener("resize", resizeThrottler(), false);
					document.getElementById('play-button').addEventListener('click', function(){
						window.ytid = this.parentNode.getAttribute('data-ytid');
						var f = document.getElementById('yt-nowplay');
						if ( f !== null ) {
							f.parentNode.removeChild(f);
						}
						document.getElementById(ytid).innerHTML += '<div id="yt-nowplay">Now Playing</div>';
						window.player;
						player = new YT.Player('youtube-player', {
							height: ythigh,
							width: ytwide,
							videoId: ytid,
							events: {
								'onReady': onPlayerReady,
								'onStateChange': onPlayerStateChange
							}
						});
						var yttitle = this.parentNode.getAttribute('data-yttitle');
					});
					var ytc = document.querySelectorAll('.youtube');
					for ( i = 0; i < ytc.length; i++ ) {
						ytc[i].addEventListener('click', function(){
							var newYtid = this.getAttribute('data-ytid');
							var yttitle = this.getAttribute('data-yttitle');
							var ytdesc = this.getAttribute('data-ytdesc');
							var ytdate = this.getAttribute('data-ytdate');
							if ( typeof ytid === typeof undefined ) {
								var d = document.getElementById('youtube-main');
								d.querySelector('h2').innerHTML = yttitle;
								d.querySelector('.desc').innerHTML = ytdesc;
								d.querySelector('.date').innerHTML = ytdate;
								var c = document.getElementById('yt-nowplay');
								if ( c !== null ) {
									c.parentNode.removeChild(c);
								}
								document.getElementById(newYtid).innerHTML += '<div id="yt-nowplay">Now Playing</div>';
								window.ytid = newYtid;
								window.player;
								player = new YT.Player('youtube-player', {
									height: ythigh,
									width: ytwide,
									videoId: ytid,
									events: {
										'onReady': onPlayerReady,
										'onStateChange': onPlayerStateChange
									}
								});
							}
							else if ( typeof ytid !== typeof undefined )
							{
								if ( ytid !== newYtid )
								{
									ytid = newYtid;
									player.stopVideo();
									player.loadVideoById({
										videoId: ytid
									});
									var d = document.getElementById('youtube-main');
									d.querySelector('h2').innerHTML = yttitle;
									d.querySelector('.desc').innerHTML = ytdesc;
									d.querySelector('.date').innerHTML = ytdate;
									var c = document.getElementById('yt-nowplay');
									if ( c !== null ) {
										c.parentNode.removeChild(c);
									}
									document.getElementById(newYtid).innerHTML += '<div id="yt-nowplay">Now Playing</div>';
								}
								else
								{
									return false;
								}
							}
							else
							{
								return false;
							}
						});
					}
				});
			</script>
			<?php
				endif; ?>
		</main><!-- .site-main -->
	</div><!-- .content-area -->
<?php get_footer(); ?>