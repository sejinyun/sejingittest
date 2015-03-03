<?php

/* Template Name: archive */

?>


     <h2>
          <?php $taxonomy = get_taxonomy(get_query_var('yoggyblog'));
                    echo sprintf('%s', single_term_title('', false)); ?>BLOG
     </h2>


	
	<?php if ( have_posts() ) : query_posts('post_type=yoggyblog&posts_per_page=30&paged='.$paged); ?>
	<ul>
	<?php while (have_posts()) : the_post(); ?>
	<li><p class="thumbnail"><a href="<?php echo the_permalink(); ?>"><img src="<?php echo $thumb[0]; ?>" height="170" width="255" alt="<?php echo the_title(); ?>"></a></p>
					<p class="mt10"><a href="<?php echo the_permalink(); ?>"><?php echo the_title(); ?></a></p>
		<br><?php the_time('Y.n.j'); ?></li>
	<?php endwhile; endif; ?>
	</ul>
		<div class="pager">
			<?php wp_pagenavi(array('query'=>$my_query)); ?>
		</div>

test
				

