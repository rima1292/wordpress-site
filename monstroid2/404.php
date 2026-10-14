<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Monstroid2
 */

get_header();

	do_action( 'monstroid2-theme/site/site-content-before', '404' ); ?>

	<div <?php monstroid2_content_class() ?>>
		<div class="row">

			<?php do_action( 'monstroid2-theme/site/primary-before', '404' ); ?>

			<div id="primary" class="col-xs-12">

				<?php do_action( 'monstroid2-theme/site/main-before', '404' ); ?>

				<main id="main" class="site-main">

					<?php monstroid2_theme()->do_location( 'single', 'template-parts/404' ); ?>

				</main><!-- #main -->

				<?php do_action( 'monstroid2-theme/site/main-after', '404' ); ?>

			</div><!-- #primary -->

			<?php do_action( 'monstroid2-theme/site/primary-after', '404' ); ?>

		</div>
	</div>
    </div>
	<?php do_action( 'monstroid2-theme/site/site-content-after', '404' );

get_footer();
