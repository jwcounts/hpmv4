<?php
/*
Template Name: Series-Tiles
*/

get_header(); ?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<?php
			while ( have_posts() ) : the_post();
			$header_back = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
			$embeds = get_post_meta( get_the_ID(), 'hpm_series_embeds', true );
			$show_title = get_the_title();
			$show_content = get_the_content();
			$page_head_style = '';
			$page_head_class = '';
			if ( !empty( $embeds['banner']['mobile'] ) || !empty( $embeds['banner']['tablet'] ) || !empty( $embeds['banner']['desktop'] ) ) :
				$page_head_class = ' shows-banner-variable';
				foreach ( $embeds['banner'] as $bk => $bv ) :
					if ( $bk == 'mobile' ) :
						$page_head_style .= ".page-header.shows-banner-variable { background-image: url(".wp_get_attachment_url( $bv )."); }";
					elseif ( $bk == 'tablet' ) :
						$page_head_style .= " @media screen and (min-width: 34em) { .page-header.shows-banner-variable { background-image: url(".wp_get_attachment_url( $bv )."); } }";
					elseif ( $bk == 'desktop' ) :
						$page_head_style .= " @media screen and (min-width: 52.5em) { .page-header.shows-banner-variable { background-image: url(".wp_get_attachment_url( $bv )."); } }";
					endif;
				endforeach;
			elseif ( !empty( $header_back[0] ) ) :
				$page_head_style = ".page-header { background-image: url($header_back[0]); }";
			else :
				$page_head_class = ' no-back';
			endif;
			if ( !empty( $page_head_style ) ) :
				echo "<style>".$page_head_style."</style>";
			endif; ?>
		<header class="page-header<?php echo $page_head_class; ?>">
			<h1 class="page-title<?php echo (!empty( $header_back ) ? ' screen-reader-text' : ''); ?>"><?php the_title(); ?></h1>
		</header>
		<?php
			endwhile; ?>
			<div id="float-wrap">
				<div class="grid-sizer"></div>
				<aside class="column-right grid-item stamp">
<?php
	if ( $show_title == '#TXDecides' ) : ?>
					<div class="show-content">
						<p>Houston Public Media and its partners across the state are collaborating to provide comprehensive coverage on the important legislation, politics and new laws that will impact all Texans.</p>
					</div>
<?php
	endif; ?>
					<h3>About <?php echo $show_title; ?></h3>
					<div class="show-content">
						<?php echo apply_filters( 'the_content', $show_content ); ?>
					</div>
					<div class="sidebar-ad">
						<h4>Support Comes From</h4>
						<div id="div-gpt-ad-1394579228932-1">
							<script type='text/javascript'>
								googletag.cmd.push(function() { googletag.display('div-gpt-ad-1394579228932-1'); });
							</script>
						</div>
					</div>
		<?php
			if ( !empty( $embeds['twitter'] ) || !empty( $embeds['facebook'] ) ) : ?>
					<section id="embeds">
				<?php
					if ( !empty( $embeds['twitter'] ) ) : ?>
						<h4>Twitter</h4>
					<?php
						echo $embeds['twitter'];
					endif;

					if ( !empty( $embeds['facebook'] ) ) : ?>
						<h4>Facebook</h4>
					<?php
						echo $embeds['facebook'];
					endif; ?>
					</section>
			<?php
				endif; ?>
				</aside>
		<?php
			$cat_no = get_post_meta( get_the_ID(), 'hpm_series_cat', true );
			$top = get_post_meta( get_the_ID(), 'hpm_series_top', true );
			$terms = get_terms( array( 'include'  => $cat_no, 'taxonomy' => 'category' ) );
			$term = reset( $terms );
			if ( empty( $embeds['order'] ) ) :
				$embeds['order'] = 'ASC';
			endif;
			$cat_args = array(
				'cat' => $cat_no,
				'orderby' => 'date',
				'order'   => $embeds['order'],
				'posts_per_page' => 15,
				'ignore_sticky_posts' => 1
			);
			if ( !empty( $top ) && $top !== 'None' ) :
				$top_art = new WP_query( array(
					'p' => $top
				) );
				$cat_args['posts_per_page'] = 14;
				$cat_args['post__not_in'] = array( $top );
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
								<?php
								if ( $show_title != 'DiverseCity' && $show_title != '#TXDecides' ) : ?>
									<h3><?php echo hpm_top_cat( get_the_ID() ); ?></h3>
								<?php
								endif; ?>
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
				<?php
					if ( $show_title != 'DiverseCity' && $show_title != '#TXDecides' ) : ?>
						<h3><?php echo hpm_top_cat( get_the_ID() ); ?></h3>
				<?php
					endif; ?>
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
			if ( !empty( $embeds['bottom'] ) ) :
				echo $embeds['bottom'];
			endif; ?>
		</main><!-- .site-main -->
	</div><!-- .content-area -->
<?php
	wp_reset_query();
	get_footer(); ?>
