<?php

use GT3\PhotoVideoGalleryPro\Block\Albums as Gallery;

get_header();

?>
	<div class="container">
		<div class="content-container">
			<section id='main_content' class='module_team'>
						<?php
						$settings = GT3_Post_Type_Gallery::instance()->getSettings('gt3_gallery');
						$settings = array(
							'paginationType'   => 'pagination',
							'paginationEnable' => true,
							'gridType'         => 'square',
							'albumType'        => 'grid',
							'columns'          => $settings['columns'],
							'columnsTablet'    => $settings['columnsTablet'],
							'columnsMobile'    => $settings['columnsMobile'],
						);

						/* @var \GT3\PhotoVideoGalleryPro\Block\Basic $gallery */
						$gallery = Gallery::instance();

						echo $gallery->render_block($settings);
						?>
						<div class="clear"></div>
			</section>
		</div>
	</div>
<?php
get_footer();

