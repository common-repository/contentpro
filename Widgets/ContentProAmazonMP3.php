<?
	class ContentProAmazonMP3 extends WP_Widget {
		
		function ContentProAmazonMP3() {
			$widget_ops = array('classname' => 'widget_contentpro_amazonmp3', 'description' => 'Amazon MP3 Widget (post_type=album)' );
			$control_ops = array('width' => 400, 'height' => 350);
			$this->WP_Widget('widget_contentpro_amazonmp3', __('ContentPro Amazon MP3'), $widget_ops, $control_ops);
		}
	 
		function widget($args, $instance) {
			extract($args, EXTR_SKIP);
			
				global $post;
				$custom = get_post_custom($post->ID); 
      	$band_id = $custom['contentpro_band_id'][0];
			
				if(get_post_type( $post ) == "album"){
				
			?>
				<ul class="CustomStyle_1 tab_navigation clearfix">
        	<li class="selected"><a href="#"><?php echo get_the_title($post->ID); ?></a></li>
        </ul>
        <div class="CustomStyle_1 tab_content" style="padding: 10px 25px 25px 25px; margin-bottom: 25px;">
        
          <script type='text/javascript'>
          var amzn_wdgt={widget:'MP3Clips'};
          amzn_wdgt.tag='nocoffsitistn-21';
          amzn_wdgt.widgetType='SearchAndAdd';
          amzn_wdgt.keywords='<?php echo get_the_title($band_id); ?> - <?php echo get_the_title($post->ID); ?>';
          amzn_wdgt.title='<?php echo get_the_title($band_id); ?> - <?php echo get_the_title($post->ID); ?>';
          amzn_wdgt.width='250';
          amzn_wdgt.height='250';
          amzn_wdgt.shuffleTracks='False';
          amzn_wdgt.marketPlace='DE';
          </script>
          <script type='text/javascript' src='http://wms.assoc-amazon.de/20070822/DE/js/swfobject_1_5.js'>
          </script> 
                   
        </div>
			
			<?
				}

			
		}

	}
?>