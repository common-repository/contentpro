<?
	class ContentProMenueWidget extends WP_Widget {
	
		function ContentProMenueWidget() {
			$widget_ops = array( 'description' => __('Use this widget to add one of your navigation menus as a widget.') );
			parent::WP_Widget( 'contentpronav', __('ContentPro Navigation'), $widget_ops );
		}
	
	
		function widget($args, $instance) {
			// Get menu
			$nav_menu = wp_get_nav_menu_object( $instance['nav_menu'] );
			if(!$nav_menu) return;
			
			extract($args, EXTR_SKIP);
			$title       = apply_filters('widget_title',empty($instance['title']) ? '' : $instance['title'],$instance);
			$description = apply_filters('widget_title',empty($instance['description']) ? '' : $instance['description'],$instance);
			
			echo $before_widget;
		 	if(!empty($description) && $instance['description_position'] == true)	{ echo '<div class="description">'.$description.'</div>'; };
			if(!empty($title)){ echo $before_title . $title . $after_title; }
			if(!empty($description) && $instance['description_position'] == false)	{ echo '<div class="description">'.$description.'</div>'; }; 
			
			wp_nav_menu( array( 'menu' => $nav_menu ) );
			echo $args['after_widget'];
		}
	
	
		function update( $new_instance, $old_instance ) {
			$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
			$instance['description'] = strip_tags($new_instance['description']);
			$instance['nav_menu'] = (int) $new_instance['nav_menu'];
			$instance['description_position'] = isset($new_instance['description_position']);
			return $instance;
		}
	
	
		function form( $instance ) {
			$title = isset( $instance['title'] ) ? $instance['title'] : '';
			$description = strip_tags($instance['description']);
			$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';
			
			// Get menus
			$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
	
			// If no menus exists, direct the user to go and create some.
			if ( !$menus ) {
				echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
				return;
			}
			?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label><input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" /></p>
			<p><label for="<?php echo $this->get_field_id('description'); ?>">Description: <input class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" type="text" value="<?php echo attribute_escape($description); ?>" /></label></p>
			<p>
				<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
				<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
			<?php
				foreach ( $menus as $menu ) {
					$selected = $nav_menu == $menu->term_id ? ' selected="selected"' : '';
					echo '<option'. $selected .' value="'. $menu->term_id .'">'. $menu->name .'</option>';
				}
			?>
				</select>
			</p>
			<p><input id="<?php echo $this->get_field_id('description_position'); ?>" name="<?php echo $this->get_field_name('description_position'); ?>" type="checkbox" <?php checked(isset($instance['description_position']) ? $instance['description_position'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('description_position'); ?>"><?php _e('Display Description above the Title'); ?></label></p>
			<?php
		}
	}

?>