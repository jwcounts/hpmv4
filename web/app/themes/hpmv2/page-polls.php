<?php
/*
Template Name: Polls
*/

get_header(); ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$(".acc-section").hide();
			$(".featured").show();
			$('h3').click(function(e) {
				$(this).next(".acc-section").slideToggle('slow');
			});
			$('#expand').click(function(e) {
				$(".acc-section").slideDown('slow');
			});
			$('#collapse').click(function(e) {
				$(".acc-section").slideUp('slow');
			});
		});
	</script>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<?PHP while ( have_posts() ) : the_post(); $current_page = get_the_ID(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php 						
						the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header><!-- .entry-header -->
				<div class="entry-content">
					<?php
						if ( has_post_thumbnail() ) :
					?>
					<div class="post-thumbnail">
						<?php 
							the_post_thumbnail( 'hpm-large' );
							$thumb_caption = get_post(get_post_thumbnail_id())->post_excerpt;
							if (!empty($thumb_caption)) :
								echo "<p>".$thumb_caption."</p>";
							endif;
						?>
					</div><!-- .post-thumbnail -->
					<?PHP
						endif;
						the_content( sprintf(
							__( 'Continue reading %s', 'hpmv2' ),
							the_title( '<span class="screen-reader-text">', '</span>', false )
						) );
					?>
				</div><!-- .entry-content -->

				<footer class="entry-footer">
				<?PHP	
					$tags_list = get_the_tag_list( '', _x( ' ', 'Used between list items, there is a space after the comma.', 'hpmv2' ) );
					if ( $tags_list ) {
						printf( '<p class="screen-reader-text"><span class="tags-links"><span class="screen-reader-text">%1$s </span>%2$s</span></p>',
							_x( 'Tags', 'Used before tag names.', 'hpmv2' ),
							$tags_list
						);
					}
					edit_post_link( __( 'Edit', 'hpmv2' ), '<span class="edit-link">', '</span>' ); ?>
				</footer><!-- .entry-footer -->
			</article><!-- #post-## -->
			<?php
				endwhile; ?>
			<aside class="column-right">
			<?php
				$orig_post = $post;
				global $post;
				$tags = wp_get_post_tags($post->ID);

				if ($tags) :
					$tag_ids = array();
					foreach($tags as $individual_tag): 
						$tag_ids[] = $individual_tag->term_id;
					endforeach;
					$args = array(
						'tag__in' => $tag_ids,
						'post__not_in' => array($post->ID),
						'posts_per_page'=> 4,
						'ignore_sticky_posts'=> 1
					);
					$my_query = new wp_query( $args );
					if ( $my_query->have_posts() ) : ?>
				<div id="related-posts">
					<h4>Related</h4>
				<?php
						while( $my_query->have_posts() ) :
							$my_query->the_post(); ?>
					<article class="related-content">
						<div class="related-image">
							<a href="<?PHP the_permalink(); ?>" class="post-thumbnail"><?PHP the_post_thumbnail( 'thumbnail' ); ?></a>
						</div>
						<div class="related-text">
							<h2><a href="<?php the_permalink(); ?>"><?PHP the_title(); ?></a></h2>
						</div>
					</article>
					<?php
						endwhile; ?>
				</div>
				<?php
					endif;
				endif;
				$post = $orig_post;
				wp_reset_query();
				get_template_part( 'sidebar', 'none' ); ?>
			</aside>
			<section id="search-results">
		<?php
				$poll_args = array(
					'post_parent' => $current_page,
					'post_type' => 'page'
				);
				$q = new wp_query( $poll_args );
		if ( $q->have_posts() ) :
			while ( $q->have_posts() ) : $q->the_post();
				get_template_part( 'content', get_post_format() );
			endwhile;
		endif; ?>
			</section>
		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
