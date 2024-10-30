<?
	class ContentProImageWidget extends WP_Widget {
		
		function ContentProImageWidget() {
			$widget_ops = array('classname' => 'widget_contentpro_images', 'description' => 'Display Images' );
			$control_ops = array('width' => 300, 'height' => 350, 'id_base' => 'contentproimages' );
			$this->WP_Widget('contentproimages', __('ContentPro Image'), $widget_ops, $control_ops);
			
			global $pagenow;
			if (WP_ADMIN) {
					add_action( 'admin_init', array( $this, 'fix_async_upload_image' ) );
				if ( 'widgets.php' == $pagenow ) {
					wp_enqueue_style('thickbox');
					wp_enqueue_script($control_ops['id_base'], WP_PLUGIN_URL.'/ContentPro/Widgets/ContentProImageWidget/ContentProImageWidget.js',array('thickbox'), false, true );
					add_action( 'admin_head-widgets.php', array( $this, 'admin_head' ) );
				} elseif ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
					add_filter( 'image_send_to_editor', array( $this,'image_send_to_editor'), 1, 8 );
					add_filter( 'gettext', array( $this, 'replace_text_in_thitckbox' ), 1, 3 );
					add_filter( 'media_upload_tabs', array( $this, 'media_upload_tabs' ) );
				}
			}
		}
	 
		function widget($args, $instance) {
			global $wpdb;
			extract($args, EXTR_SKIP);
	 
			$title 				= apply_filters('widget_title',empty($instance['title']) ? '' : $instance['title'],$instance);
			$description 	= apply_filters('widget_title',empty($instance['description']) ? '' : $instance['description'],$instance);
			$content			= apply_filters('widget_text',$instance['content'],$instance);
			
			echo $before_widget;

			if(!empty($instance['image'])){
				if ($instance['link']) {
					echo '<a class="'.$this->widget_options['classname'].'-image-link" href="'.$instance['link'].'" target="'.$instance['linktarget'].'">';
				}
				if ($instance['imageurl']) {
					echo "<img src=\"{$instance['imageurl']}\" style=\"";
					if (!empty($instance['width']) && is_numeric($instance['width'])) {
						echo "max-width: {$instance['width']}px;";
					}
					if (!empty($instance['height']) && is_numeric($instance['height'])) {
						echo "max-height: {$instance['height']}px;";
					}
					echo "\"";
					if (!empty($instance['align']) && $instance['align'] != 'none') {
						echo " class=\"align{$instance['align']}\"";
					}
					if (!empty($instance['alt'])) {
						echo " alt=\"{$instance['alt']}\"";
					} else {
						echo " alt=\"{$instance['title']}\"";					
					}
					echo " />";
				}
				if($instance['link']) { echo '</a>'; }
			}

			
			if($instance['hide'] == false){
				if(!empty($description) && $instance['description_position'] == true)	{ echo '<div class="description">'.$description.'</div>'; };
				if(!empty($title))				{ echo $before_title . $title . $after_title; }
				if(!empty($description) && $instance['description_position'] == false)	{ echo '<div class="description">'.$description.'</div>'; }; 
			}
			
			if(!empty($instance['caption'])) {
				$text = apply_filters( 'widget_text', $instance['caption'] );
				echo '<div class="'.$this->widget_options['classname'].'-caption" >';
				echo wpautop($text);
				if(!empty($instance['more'])) {
					if($instance['link']) {
						echo '<a class="'.$this->widget_options['classname'].'-image-link" href="'.$instance['link'].'" target="'.$instance['linktarget'].'">';
						echo $instance['more'];
						echo '</a>'; 
					}
				}
				echo "</div>";
			}

			echo $after_widget;
			
		}
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['description'] = strip_tags($new_instance['description']);
			$instance['hide'] = isset($new_instance['hide']);
			$instance['advert'] = $new_instance['advert'];
			$instance['description_position'] = isset($new_instance['description_position']);
			
			if(isset($new_instance['caption'])){
				if(current_user_can('unfiltered_html')){
					$instance['caption'] = $new_instance['caption'];
				} else {
					$instance['caption'] = wp_filter_post_kses($new_instance['caption']);
				}
			}
			
			$instance['link'] = $new_instance['link'];
			$instance['image'] = $new_instance['image'];
			$instance['imageurl'] = $this->get_image_url($new_instance['image'],$new_instance['width'],$new_instance['height']);  // image resizing not working right now
			$instance['linktarget'] = $new_instance['linktarget'];
			$instance['width'] = $new_instance['width'];
			$instance['height'] = $new_instance['height'];
			$instance['align'] = $new_instance['align'];
			$instance['alt'] = $new_instance['alt'];
			$instance['more'] = $new_instance['more'];
			
			return $instance;
		}
		function form($instance) {
			global $wpdb;
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'description' => '', 'comments_title' => '' ) );
			$title = strip_tags($instance['title']);
			$description = strip_tags($instance['description']);
			$content = $instance['content'];
	?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>"><?=__('Title')?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('description'); ?>"><?=__('Description')?>: <input class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" type="text" value="<?php echo attribute_escape($description); ?>" /></label></p>
	
  			<p><label for="<?php echo $this->get_field_id('image'); ?>"><?=__('Image')?>:</label>
					<?php
						$media_upload_iframe_src = "media-upload.php?type=image&widget_id=".$this->id; //NOTE #1: the widget id is added here to allow uploader to only return array if this is used with image widget so that all other uploads are not harmed.
						$image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src");
						$image_title = __(($instance['image'] ? 'Change Image' : 'Add Image'), $this->pluginDomain);
					?><br />
					<a href="<?php echo $image_upload_iframe_src; ?>&TB_iframe=true" id="add_image-<?php echo $this->get_field_id('image'); ?>" class="thickbox-image-widget" title='<?php echo $image_title; ?>' onClick="set_active_widget('<?php echo $this->id; ?>');return false;" style="text-decoration:none"><img src='images/media-button-image.gif' alt='<?php echo $image_title; ?>' align="absmiddle" /> <?php echo $image_title; ?></a>
          <div id="display-<?php echo $this->get_field_id('image'); ?>">
					<?php 
						if($instance['imageurl']) {
							echo "<img src=\"{$instance['imageurl']}\" alt=\"{$instance['title']}\" style=\"";
							if($instance['width'] && is_numeric($instance['width'])){				echo "max-width: {$instance['width']}px;";		}
 							if($instance['height'] && is_numeric($instance['height'])){			echo "max-height: {$instance['height']}px;";	}
							echo "\"";
							if(!empty($instance['align']) && $instance['align'] != 'none'){	echo " class=\"align{$instance['align']}\"";	}
							echo " />";
						}
					?>
          </div>
					<br clear="all" />
					<input id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" type="hidden" value="<?php echo $instance['image']; ?>" />
				</p>

				<p><label for="<?php echo $this->get_field_id('caption'); ?>"><?=__('Caption')?>:</label><textarea rows="8" class="widefat" id="<?php echo $this->get_field_id('caption'); ?>" name="<?php echo $this->get_field_name('caption'); ?>"><?php echo format_to_edit($instance['caption']); ?></textarea></p>

				<p>
        	 <label for="<?php echo $this->get_field_id('link'); ?>"><?=__('Link')?>:</label>
					 <input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['link'])); ?>" /><br />
					 <select name="<?php echo $this->get_field_name('linktarget'); ?>" id="<?php echo $this->get_field_id('linktarget'); ?>">
           		<option value="_self"<?php selected( $instance['linktarget'], '_self' ); ?>><?=__('Stay in Window')?></option>
           		<option value="_blank"<?php selected( $instance['linktarget'], '_blank' ); ?>><?=__('Open New Window')?></option>
           </select>
        </p>

				<p><label for="<?php echo $this->get_field_id('width'); ?>"><?=__('Width')?>:</label><input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['width'])); ?>" onchange="changeImgWidth('<?php echo $this->id; ?>')" /></p>
				<p><label for="<?php echo $this->get_field_id('height'); ?>"><?=__('Height')?>:</label><input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['height'])); ?>" onchange="changeImgHeight('<?php echo $this->id; ?>')" /></p>
  
				<p>
        	 <label for="<?php echo $this->get_field_id('align'); ?>"><?=__('Align')?>:</label>
        	 <select name="<?php echo $this->get_field_name('align'); ?>" id="<?php echo $this->get_field_id('align'); ?>" onchange="changeImgAlign('<?php echo $this->id; ?>')">
					   <option value="none"<?php selected( $instance['align'], 'none' ); ?>><?=__('None')?></option>
						 <option value="left"<?php selected( $instance['align'], 'left' ); ?>><?=__('Left')?></option>
						 <option value="center"<?php selected( $instance['align'], 'center' ); ?>><?=__('Center')?></option>
						 <option value="right"<?php selected( $instance['align'], 'right' ); ?>><?=__('Right')?></option>
					 </select>
        </p>

				<p><label for="<?php echo $this->get_field_id('alt'); ?>"><?=__('Alternate Text')?>:</label><input id="<?php echo $this->get_field_id('alt'); ?>" name="<?php echo $this->get_field_name('alt'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['alt'])); ?>" /></p>

				<p><label for="<?php echo $this->get_field_id('more'); ?>"><?=__('More Text')?>:</label><input id="<?php echo $this->get_field_id('more'); ?>" name="<?php echo $this->get_field_name('more'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['more'])); ?>" /></p>

        <p><input id="<?php echo $this->get_field_id('hide'); ?>" name="<?php echo $this->get_field_name('hide'); ?>" type="checkbox" <?php checked(isset($instance['hide']) ? $instance['hide'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('hide'); ?>"><?php _e('Hide Title and Description'); ?></label></p>
				<p><input id="<?php echo $this->get_field_id('description_position'); ?>" name="<?php echo $this->get_field_name('description_position'); ?>" type="checkbox" <?php checked(isset($instance['description_position']) ? $instance['description_position'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('description_position'); ?>"><?php _e('Display Description above the Title'); ?></label></p>
	<?php
		}
		function fix_async_upload_image() {
			if(isset($_REQUEST['attachment_id'])) {
				$GLOBALS['post'] = get_post($_REQUEST['attachment_id']);
			}
		}
		
		function get_image_url( $id, $width=false, $height=false ) {
			$attachment = wp_get_attachment_metadata( $id );
			$attachment_url = wp_get_attachment_url( $id );
			if (isset($attachment_url)) {
				if ($width && $height) {
					$uploads = wp_upload_dir();
					$imgpath = $uploads['basedir'].'/'.$attachment['file'];
					error_log($imgpath);
					$image = image_resize( $imgpath, $width, $height );
					if ( $image && !is_wp_error( $image ) ) {
						error_log( is_wp_error($image) );
						$image = path_join( dirname($attachment_url), basename($image) );
					} else {
						$image = $attachment_url;
					}
				} else {
					$image = $attachment_url;
				}
				if (isset($image)) {
					return $image;
				}
			}
		}
		function is_sp_widget_context() {
			if ( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],$this->id_base) !== false ) {
				return true;
			} elseif ( isset($_REQUEST['_wp_http_referer']) && strpos($_REQUEST['_wp_http_referer'],$this->id_base) !== false ) {
				return true;
			} elseif ( isset($_REQUEST['widget_id']) && strpos($_REQUEST['widget_id'],$this->id_base) !== false ) {
				return true;
			}
			return false;
		}
		function replace_text_in_thitckbox($translated_text, $source_text, $domain) {
			if ( $this->is_sp_widget_context() ) {
				if ('Insert into Post' == $source_text) {
					return __('Insert Into Widget', $this->pluginDomain );
				}
			}
			return $translated_text;
		}
		function image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt = '' ) {
			// Normally, media uploader return an HTML string (in this case, typically a complete image tag surrounded by a caption).
			// Don't change that; instead, send custom javascript variables back to opener.
			// Check that this is for the widget. Shouldn't hurt anything if it runs, but let's do it needlessly.
			if ( $this->is_sp_widget_context() ) {
				if ($alt=='') $alt = $title;
				?>
				<script type="text/javascript">
					// send image variables back to opener
					var win = window.dialogArguments || opener || parent || top;
					win.IW_html = '<?php echo addslashes($html) ?>';
					win.IW_img_id = '<?php echo $id ?>';
					win.IW_alt = '<?php echo addslashes($alt) ?>';
					win.IW_caption = '<?php echo addslashes($caption) ?>';
					win.IW_title = '<?php echo addslashes($title) ?>';
					win.IW_align = '<?php echo $align ?>';
					win.IW_url = '<?php echo $url ?>';
					win.IW_size = '<?php echo $size ?>';
					//alert("sending variables: id: "+win.IW_img_id+"\n"+"alt: "+win.IW_alt+"\n"+"title: "+win.IW_title+"\n"+"align: "+win.IW_align+"\n"+"url: "+win.IW_url+"\n"+"size: "+win.IW_size);
				</script>
				<?php
			}
			return $html;
		}
		function media_upload_tabs($tabs) {
			if ( $this->is_sp_widget_context() ) {
				unset($tabs['type_url']);
			}
			return $tabs;
		}
		function admin_head() {
			?>
			<style type="text/css">
				.aligncenter {
					display: block;
					margin-left: auto;
					margin-right: auto;
				}
			</style>
			<?php
		}
		
	}
?>