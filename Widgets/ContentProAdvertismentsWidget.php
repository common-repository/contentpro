<?
	class ContentProAdvertismentsWidget extends WP_Widget {
		
		function ContentProAdvertismentsWidget() {
			$widget_ops = array('classname' => 'widget_contentpro_advertisments', 'description' => 'Display Advertisments' );
			$control_ops = array('width' => 300, 'height' => 350);
			$this->WP_Widget('contentproadvertisments', __('ContentPro Advertisments'), $widget_ops, $control_ops);
		}
	 
		function widget($args, $instance) {
			global $wpdb;
			extract($args, EXTR_SKIP);
	 
			$title 				= apply_filters('widget_title',empty($instance['title']) ? '' : $instance['title'],$instance);
			$description 	= apply_filters('widget_title',empty($instance['description']) ? '' : $instance['description'],$instance);
			$content			= apply_filters('widget_text',$instance['content'],$instance);
			
			$query = "SELECT code FROM ".$wpdb->prefix."contentpro_ContentProAdvertisments WHERE id = ".$instance['advert']."";
			$advertisment = $wpdb->get_var($query); 
			
			echo $before_widget;
				if($instance['hide'] == false){
					if(!empty($description) && $instance['description_position'] == true)	{ echo '<div class="description">'.$description.'</div>'; };
					if(!empty($title))				{ echo $before_title . $title . $after_title; }
					if(!empty($description) && $instance['description_position'] == false)	{ echo '<div class="description">'.$description.'</div>'; }; 
				}
			?>
			<div class="advertismentwidget">
				<? echo stripslashes($advertisment); ?>
      </div>
			<?php
			echo $after_widget;
			
		}
	 
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['description'] = strip_tags($new_instance['description']);
			$instance['hide'] = isset($new_instance['hide']);
			$instance['advert'] = $new_instance['advert'];
			$instance['description_position'] = isset($new_instance['description_position']);
			return $instance;
		}
	 
		function form($instance) {
			global $wpdb;
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'description' => '', 'comments_title' => '' ) );
			$title = strip_tags($instance['title']);
			$description = strip_tags($instance['description']);
			$content = $instance['content'];
	?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('description'); ?>">Description: <input class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" type="text" value="<?php echo attribute_escape($description); ?>" /></label></p>
				
        <p>
				<?php
					$result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_ContentProAdvertisments ORDER BY position");
					if(is_array($result) && !empty($result)){				
				?>  
				<label for="<?php echo $this->get_field_name('advert'); ?>">Advertisment: 
          <select id="<?php echo $this->get_field_id('advert'); ?>" name="<?php echo $this->get_field_name('advert'); ?>" class="widefat">
          <?php foreach ($result as $data) { ?>
            <option value="<?=$data->id?>" <?php if((int)$instance['advert'] == (int)$data->id){ echo 'selected'; } ?>><?=$data->name?></option>
          <?php } ?>
          </select>
        </label>
        <?php }else{ ?>
        Es wurden noch keine Advertisments angelegt.<br>
        </p>
        
        <p>
        <a href="admin.php?page=ContentProAdvertisments">Advertisment Einstellungen</a><br />
      	<a href="admin.php?page=ContentProAdvertisments&action=new">Neues Advertisment hinzuf&uuml;gen</a><br>
        <?php } ?>
        </p>
				
        <p><input id="<?php echo $this->get_field_id('hide'); ?>" name="<?php echo $this->get_field_name('hide'); ?>" type="checkbox" <?php checked(isset($instance['hide']) ? $instance['hide'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('hide'); ?>"><?php _e('Hide Title and Description'); ?></label></p>
				<p><input id="<?php echo $this->get_field_id('description_position'); ?>" name="<?php echo $this->get_field_name('description_position'); ?>" type="checkbox" <?php checked(isset($instance['description_position']) ? $instance['description_position'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('description_position'); ?>"><?php _e('Display Description above the Title'); ?></label></p>
	<?php
		}
	}
?>