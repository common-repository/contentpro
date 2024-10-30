<?
	class ContentProFacebookLikeBox extends WP_Widget {
		
		function ContentProFacebookLikeBox() {
			$widget_ops = array('classname' => 'widget_contentpro_facebooklikebox', 'description' => 'The Like Box is a social plugin that enables Facebook Page owners to attract and gain Likes from their own website.' );
			$control_ops = array('width' => 280, 'height' => 350);
			$this->WP_Widget('contentprofacebooklikebox', __('ContentPro FB:Likebox'), $widget_ops, $control_ops);
		}
	 
		function widget($args, $instance) {
			extract($args, EXTR_SKIP);
	 
			$title 				= apply_filters('widget_title',empty($instance['title']) ? '' : $instance['title'],$instance);
			$description 	= apply_filters('widget_title',empty($instance['description']) ? '' : $instance['description'],$instance);
			$content			= apply_filters('widget_text',$instance['content'],$instance);
			
			//FACEBOOK
			$pageid = $instance['pageid'];
			$width = $instance['width'];
			$height = $instance['height'];
			$connections = $instance['connections'];

			$stream = ($instance['stream'] == true) ? 'true' : 'false';
			$header = ($instance['header'] == true) ? 'true' : 'false';
			
			echo $before_widget;
		 	if(!empty($description) && $instance['description_position'] == true)	{ echo '<div class="description">'.$description.'</div>'; };
			if(!empty($title))				{ echo $before_title . $title . $after_title; }
			if(!empty($description) && $instance['description_position'] == false)	{ echo '<div class="description">'.$description.'</div>'; };
			
			if(!empty($pageid)){
				echo '<iframe src="http://www.facebook.com/plugins/likebox.php?id='.$pageid.'&amp;width='.$width.'&amp;height='.$height.'&amp;connections='.$connections.'&amp;stream='.$stream.'&amp;header='.$header.'" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:'.$width.'px; height:'.$height.'px"></iframe>';
			}
			echo $after_widget;
			
		}
	 
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['description'] = strip_tags($new_instance['description']);
			$instance['description_position'] = isset($new_instance['description_position']);
			
			//FACEBOOK
			$instance['pageid'] = $new_instance['pageid'];
			$instance['width'] = $new_instance['width'];
			$instance['height'] = $new_instance['height'];
			$instance['connections'] = $new_instance['connections'];
			$instance['stream'] = isset($new_instance['stream']);
			$instance['header'] = isset($new_instance['header']);
			
			return $instance;
		}
	 
		function form($instance) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'description' => '', 'comments_title' => '' ) );
			$title = strip_tags($instance['title']);
			$description = strip_tags($instance['description']);
			$content = $instance['content'];

			//FACEBOOK
			$pageid = $instance['pageid'];
			$width = $instance['width'];
			$height = $instance['height'];
			$connections = $instance['connections'];
			$stream = $instance['stream'];
			$header = $instance['header'];

	?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('description'); ?>">Description: <input class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" type="text" value="<?php echo attribute_escape($description); ?>" /></label></p>
				<p><input id="<?php echo $this->get_field_id('description_position'); ?>" name="<?php echo $this->get_field_name('description_position'); ?>" type="checkbox" <?php checked(isset($instance['description_position']) ? $instance['description_position'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('description_position'); ?>"><?php _e('Display Description above the Title'); ?></label></p>
				<p><label for="<?php echo $this->get_field_id('pageid'); ?>">Facebook Page ID: <input class="widefat" id="<?php echo $this->get_field_id('pageid'); ?>" name="<?php echo $this->get_field_name('pageid'); ?>" type="text" value="<?php echo attribute_escape($pageid); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('width'); ?>">Width: <input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo attribute_escape($width); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('height'); ?>">Height: <input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo attribute_escape($height); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('connections'); ?>">Connections: <input id="<?php echo $this->get_field_id('connections'); ?>" name="<?php echo $this->get_field_name('connections'); ?>" type="text" value="<?php echo attribute_escape($connections); ?>" /></label></p>
				
				<p><input id="<?php echo $this->get_field_id('stream'); ?>" name="<?php echo $this->get_field_name('stream'); ?>" type="checkbox" <?php checked(isset($instance['stream']) ? $instance['stream'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('stream'); ?>"><?php _e('Display the Stream'); ?></label></p>
				<p><input id="<?php echo $this->get_field_id('header'); ?>" name="<?php echo $this->get_field_name('header'); ?>" type="checkbox" <?php checked(isset($instance['header']) ? $instance['header'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('header'); ?>"><?php _e('Display the Header'); ?></label></p>
	<?php
		}
	}
?>