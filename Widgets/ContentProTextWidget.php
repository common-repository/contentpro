<?
	class ContentProTextWidget extends WP_Widget {
		
		function ContentProTextWidget() {
			$widget_ops = array('classname' => 'widget_contentpro_text', 'description' => 'Customized Text Widget' );
			$control_ops = array('width' => 400, 'height' => 350);
			$this->WP_Widget('contentprotext', __('ContentPro Text'), $widget_ops, $control_ops);
		}
	 
		function widget($args, $instance) {
			extract($args, EXTR_SKIP);
	 
			$title 				= apply_filters('widget_title',empty($instance['title']) ? '' : $instance['title'],$instance);
			$description 	= apply_filters('widget_title',empty($instance['description']) ? '' : $instance['description'],$instance);
			$content			= apply_filters('widget_text',$instance['content'],$instance);
			
			echo $before_widget;
		 	if(!empty($description) && $instance['description_position'] == true)	{ echo '<div class="description">'.$description.'</div>'; };
			if(!empty($title))				{ echo $before_title . $title . $after_title; }
			if(!empty($description) && $instance['description_position'] == false)	{ echo '<div class="description">'.$description.'</div>'; }; ?>
				<div class="textwidget"><?php echo $instance['filter'] ? wpautop($content) : $content; ?></div>
			<?php
			echo $after_widget;
			
		}
	 
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['description'] = strip_tags($new_instance['description']);
			if(current_user_can('unfiltered_html'))
				$instance['content'] =  $new_instance['content'];
			else
				$instance['content'] = stripslashes(wp_filter_post_kses(addslashes($new_instance['content'])));
			$instance['filter'] = isset($new_instance['filter']);
			$instance['description_position'] = isset($new_instance['description_position']);
			return $instance;
		}
	 
		function form($instance) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'description' => '', 'comments_title' => '' ) );
			$title = strip_tags($instance['title']);
			$description = strip_tags($instance['description']);
			$content = $instance['content'];
	?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('description'); ?>">Description: <input class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" type="text" value="<?php echo attribute_escape($description); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('content'); ?>">Content: <textarea rows="10" class="widefat" id="<?php echo $this->get_field_id('content'); ?>" name="<?php echo $this->get_field_name('content'); ?>"><?php echo attribute_escape($content); ?></textarea></label></p>
				<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs.'); ?></label></p>
				<p><input id="<?php echo $this->get_field_id('description_position'); ?>" name="<?php echo $this->get_field_name('description_position'); ?>" type="checkbox" <?php checked(isset($instance['description_position']) ? $instance['description_position'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('description_position'); ?>"><?php _e('Display Description above the Title'); ?></label></p>
	<?php
		}
	}
?>