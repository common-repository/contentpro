<?
	class ContentProWidget extends WP_Widget {
		
		var $name = "ContentPro";
		var $typ = "Widget";
		var $classname = "ContentProWidget";
		var $table = "ContentProWidget";
		var $description = "Beschreibung des Plugins";
		
		function ContentProWidget(){
			$widget_ops = array('classname' => 'widget_contentpro', 'description' => 'ContentPro Widget' );
			$control_ops = array('width' => 310, 'height' => 350);
			$this->WP_Widget('contentpro', __('ContentPro'), $widget_ops, $control_ops);
		}

		function widget($args, $instance){
			extract($args, EXTR_SKIP);
	 
			$title          = apply_filters('widget_title',empty($instance['title']) ? '' : $instance['title'],$instance);
			$description    = apply_filters('widget_title',empty($instance['description']) ? '' : $instance['description'],$instance);
			$tabs           = $instance['tabs'];
			$style          = $instance['style'];
			$display        = $instance['display'];
			
			$style1_before  = cp_getOption('style1_before' ,$this->table);
			$style1_middle  = cp_getOption('style1_middle' ,$this->table);
			$style1_after   = cp_getOption('style1_after'  ,$this->table);
			
			$style2_before  = cp_getOption('style2_before' ,$this->table);
			$style2_middle  = cp_getOption('style2_middle' ,$this->table);
			$style2_after   = cp_getOption('style2_after'  ,$this->table);
			
			$style3_before  = cp_getOption('style3_before' ,$this->table);
			$style3_middle  = cp_getOption('style3_middle' ,$this->table);
			$style3_after   = cp_getOption('style3_after'  ,$this->table);
			
			$format_date    = cp_getOption('format_date'   ,$this->table); 
			
			$special_tab_typ = array(2,3,4);	
			
			(integer) $i = 0;
			while($i < $tabs){
				(integer) $current = $i+1; 
				(string) $fieldname = 'tab_name'.$current;
				(string) $fieldid = 'tab_id'.$current;
				$contents[$current]['tab_id'] = $current;
				$contents[$current]['tab_name'] = htmlspecialchars($instance['tab_name'.$current]);
				$contents[$current]['tab_content'] = $instance['tab_content'.$current];
				$contents[$current]['tab_style'] = $instance['tab_style'.$current];
				$contents[$current]['tab_tags'] = $instance['tab_tags'.$current];
				$contents[$current]['tab_limit'] = $instance['tab_limit'.$current];
				$contents[$current]['tab_typ'] = $instance['tab_typ'.$current];
				$contents[$current]['tab_customfield'] = stripslashes($instance['tab_customfield'.$current]);
				$contents[$current]['tab_categories'] = unserialize($instance['tab_categories'.$current]);
				$contents[$current]['tab_order'] = htmlspecialchars($instance['tab_order'.$current]);
				$contents[$current]['tab_orderdir'] = htmlspecialchars($instance['tab_orderdir'.$current]);
				$i++;
			}
			
			//PREPARE CONTENT
			foreach($contents as $key => $value){
				
					if(in_array($value['tab_typ'],$special_tab_typ)){
						
						unset($return);
						if($value['tab_style'] == "") $value['tab_style'] = 1;
						$return .= ${'style'.$value['tab_style'].'_before'};
						
						//BUILD THE QUERY
						unset($query);
						$query['orderby']          = $value['tab_order'];
						$query['order']            = $value['tab_orderdir'];
						$query['showposts']        = $value['tab_limit'];
						$query['caller_get_posts'] = "1";
						
						
						switch($value['tab_typ']){
							// CATEGORIES
							case 2: 
								unset($categories);	
								foreach($value['tab_categories'] as $category){ 
									$categories .= $category.","; 
								}	
								$query['cat'] = $categories; 
							break;
							
							// TAGS
							case 3:
								$query['tag'] = $value['tab_tags'];
							break;
							
							// SPECIAL
							case 4:
								$query['meta_key'] = "contentpro"; 
								$query['meta_value'] = $value['tab_customfield'];
							break;
							
						}
						
						$my_query = new WP_Query($query);
						while ($my_query->have_posts()) { 
							$my_query->the_post();
							unset($style);
							$style 	 = ${'style'.(string)$value['tab_style'].'_middle'};
							
							$metaboxes = cp_getResults('ContentProMetaBox','position');
							if(is_array($metaboxes)):
								foreach($metaboxes as $metabox) {
									if($metabox->replacement != "")
									$style = str_replace('{'.$metabox->replacement.'}',get_post_meta(get_the_ID(),$metabox->value,true),$style);
								}
							endif;
							
							$style 	 = str_replace('{DATE}',get_the_time($format_date),$style);
							$style 	 = str_replace('{PERMALINK}',get_permalink(),$style);
							$style 	 = str_replace('{THUMBNAIL}',get_the_post_thumbnail(get_the_ID(),'thumbnail'),$style);
							$style 	 = str_replace('{TITLE}',get_the_title(),$style);
							$style 	 = str_replace('{EXCERPT}',get_the_excerpt(),$style);
							$return .= $style;
						}
			
						$return .= ${'style'.$value['tab_style'].'_after'};	
						$content[$key] = $return;
						
						unset($return);
					}elseif($value['tab_typ'] == 5){
						$content[$key] = '<ul>';
						$content[$key] .= '<li>'.$value["tab_content"].'</li>';
						$content[$key] .= '<li>'.TvNewsletter::activateNewsletterPlugin($value['tab_meenews_list']).'</li>';
						$content[$key] .= '</ul>';
					}else{						
						$content[$key] = '<ul><li>'.$value["tab_content"].'</li></ul>';
					}
					
			}
			
			
			//PREPARE STRUCTURE
			unset($return);
			switch($display){
				
				//TABS JQUERY UI
				case 2:
					$return .= "<ul>";
					foreach($contents as $key => $value){
						$return .= '<li><a href="#'.$args["widget_id"].'-'.$value['tab_id'].'">'.$value['tab_name'].'</a></li>';
					}
					$return .= "</ul>";
					foreach($contents as $key => $value){
						$return .= '<div id="'.$args["widget_id"].'-'.$value['tab_id'].'">__CONTENT'.$key.'__</div>';
					}
				break;
				
				//ACCCORDION JQUERY UI
				case 3:
					foreach($contents as $key => $value){
						$return .= '<h3><a href="#">'.$value["tab_name"].'</a></h3>';
						$return .= '<div>__CONTENT'.$key.'__</div>';
					}
				break;
				
				//CUSTOM TABS - Style1
				case 4:
					$return .= "<ul class=\"CustomStyle_1 tab_navigation\">";
					foreach($contents as $key => $value){
						$return .= '<li><a href="#'.$args["widget_id"].'-'.$value['tab_id'].'">'.$value['tab_name'].'</a></li>';
					}
					$return .= "</ul>";
					foreach($contents as $key => $value){
						$return .= '<div id="'.$args["widget_id"].'-'.$value['tab_id'].'" class="CustomStyle_1 tab_content">__CONTENT'.$key.'__</div>';
					}
				break;

				//CUSTOM TABS - Style2
				case 5:
					$return .= "<ul class=\"CustomStyle_2 tab_navigation\">";
					foreach($contents as $key => $value){
						$return .= '<li><a href="#'.$args["widget_id"].'-'.$value['tab_id'].'">'.$value['tab_name'].'</a></li>';
					}
					$return .= "</ul>";
					foreach($contents as $key => $value){
						$return .= '<div id="'.$args["widget_id"].'-'.$value['tab_id'].'" class="CustomStyle_2 tab_content">__CONTENT'.$key.'__</div>';
					}
				break;

				//STANDARD
				default:
					foreach($contents as $key => $value){
						$return .= '<h3>'.$value["tab_name"].'</h3>';
						$return .= '<div>__CONTENT'.$key.'__</div>';
					}
				break;
				
			}
		
			//CREATE JAVASCRIPT
			switch($display){
				case 2:
				
					### TABS
					$script = "
            <script type=\"text/javascript\">
              jQuery(function() {
                jQuery(\"#content-".$args['widget_id']."\").tabs();
              });
            </script>
					";
				break;
				case 3:
					### ACCORDION
					$script = "
            <script type=\"text/javascript\">
              jQuery(function() {
                jQuery(\"#content-".$args['widget_id']."\").accordion({ autoHeight: false });
              });
            </script>
					";				
				break;
				case 4:
				case 5:
					### CUSTOM TABS
					$script = "
            <script type=\"text/javascript\">
							jQuery(function () {
							
    						var tabContainers = $('#content-".$args['widget_id']." > div');
								$('#content-".$args['widget_id']." > ul').addClass('clearfix');
								$('#content-".$args['widget_id']." ul.tab_navigation a').click(function () {
								
									tabContainers.hide().filter(this.hash).show();
									$('#content-".$args['widget_id']." ul li').removeClass('selected');
									$(this).parent().addClass('selected');
									return false;
									
								}).filter(':first').click();
								
							});
            </script>
					";				
				break;
			}
			echo $script;	
		
		
			echo $before_widget;
		 	if(!empty($description) && $instance['description_position'] == true)	{ echo '<div class="description">'.$description.'</div>'; };
			if(!empty($title))				{ echo $before_title . $title . $after_title; }
			if(!empty($description) && $instance['description_position'] == false)	{ echo '<div class="description">'.$description.'</div>'; };

			echo '<div id="content-'.$args['widget_id'].'">';
				
				foreach($contents as $key => $value){
					$return = str_replace('__CONTENT'.$key.'__',$content[$key],$return);
				}
				echo $return;			

			echo '</div>';
			
		}

		function update($new_instance, $old_instance){
			$instance = $old_instance;
			$instance['title'] = strip_tags(stripslashes($new_instance['title']));
			$instance['description'] = strip_tags(stripslashes($new_instance['description']));
			$instance['description_position'] = isset($new_instance['description_position']);
			$instance['tabs'] = $new_instance['tabs'];
			$instance['style'] = $new_instance['style'];
			$instance['display'] = $new_instance['display'];

			(integer) $i = 0;
			while($i < $new_instance['tabs']){
				$current = $i+1;
				$instance['tab_name'.$current] = strip_tags(stripslashes($new_instance['tab_name'.$current]));
				$instance['tab_typ'.$current] = strip_tags(stripslashes($new_instance['tab_typ'.$current]));
				$instance['tab_content'.$current] = $new_instance['tab_content'.$current];
				$instance['tab_customfield'.$current] = strip_tags(stripslashes($new_instance['tab_customfield'.$current]));
				$instance['tab_tags'.$current] = strip_tags(stripslashes($new_instance['tab_tags'.$current]));
				$instance['tab_style'.$current] = strip_tags(stripslashes($new_instance['tab_style'.$current]));
				$instance['tab_limit'.$current] = strip_tags(stripslashes($new_instance['tab_limit'.$current]));
				$instance['tab_order'.$current] = strip_tags(stripslashes($new_instance['tab_order'.$current]));
				$instance['tab_orderdir'.$current] = strip_tags(stripslashes($new_instance['tab_orderdir'.$current]));
				$instance['tab_categories'.$current] = serialize($new_instance['tab_categories'.$current]);
				
				//MEENEWS SUPPORT
				$instance['tab_meenews_list'.$current] = strip_tags(stripslashes($new_instance['tab_meenews_list'.$current]));
				$i++;
			}
			return $instance;
		}
		
		
		function form($instance){
			global $wpdb;
			
			$instance = wp_parse_args((array) $instance,array('title'=>'','description'=>'','tabs'=>'1','style'=>'1'));
			$title = strip_tags($instance['title']);
			$description = strip_tags($instance['description']);

			$tabs = $instance['tabs'];
			$style = $instance['style'];
			$display = $instance['display'];

			(integer) $i = 1;
			while($i <= $tabs){
				$current = $i;
				$fieldname = 'tab_name'.$current;
				$contents[$current]['tab_id'] = $current;
				$contents[$current]['tab_style'] = htmlspecialchars($instance['tab_style'.$current]);
				$contents[$current]['tab_name'] = htmlspecialchars($instance['tab_name'.$current]);
				$contents[$current]['tab_typ'] = empty($instance['tab_typ'.$current]) ? '1' : $instance['tab_typ'.$current];
				$contents[$current]['tab_content'] = $instance['tab_content'.$current];
				$contents[$current]['tab_customfield'] = htmlspecialchars($instance['tab_customfield'.$current]);
				$contents[$current]['tab_tags'] = htmlspecialchars($instance['tab_tags'.$current]);
				$contents[$current]['tab_limit'] = empty($instance['tab_limit'.$current]) ? '5' : $instance['tab_limit'.$current];
				$contents[$current]['tab_order'] = empty($instance['tab_order'.$current]) ? 'date' : $instance['tab_order'.$current];
				$contents[$current]['tab_orderdir'] = empty($instance['tab_orderdir'.$current]) ? 'DESC' : $instance['tab_orderdir'.$current];
				$contents[$current]['tab_categories'] = $instance['tab_categories'.$current];
				$contents[$current]['name_style'] = 'tab_style'.$current;
				$contents[$current]['name_name'] = 'tab_name'.$current;
				$contents[$current]['name_typ'] = 'tab_typ'.$current;
				$contents[$current]['name_content'] = 'tab_content'.$current;
				$contents[$current]['name_customfield'] = 'tab_customfield'.$current;
				$contents[$current]['name_tags'] = 'tab_tags'.$current;
				$contents[$current]['name_limit'] = 'tab_limit'.$current;
				$contents[$current]['name_order'] = 'tab_order'.$current;
				$contents[$current]['name_orderdir'] = 'tab_orderdir'.$current;
				$contents[$current]['name_categories'] = 'tab_categories'.$current;
				
				//MEE NEWS SUPPORT
				$contents[$current]['tab_meenews_list'] = $instance['tab_meenews_list'.$current];
				$contents[$current]['name_meenews_list'] = 'tab_meenews_list'.$current;
				$i++;
			}
				
?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('description'); ?>">Description: <input class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" type="text" value="<?php echo attribute_escape($description); ?>" /></label></p>
				<p><input id="<?php echo $this->get_field_id('description_position'); ?>" name="<?php echo $this->get_field_name('description_position'); ?>" type="checkbox" <?php checked(isset($instance['description_position']) ? $instance['description_position'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('description_position'); ?>"><?php _e('Display Description above the Title'); ?></label></p>
        <p>
        	<label for="<?php echo $this->get_field_name('display'); ?>">Display: 
        		<select style="width: 160px;" id="<?php echo $this->get_field_id('display'); ?>" name="<? echo $this->get_field_name('display'); ?>">
            	<option value="1" <?php if($display == '1') { echo ' selected="selected"'; } ?>>Standard</option>
            	<option value="2" <?php if($display == '2') { echo ' selected="selected"'; } ?>>jQuery UI - Tabs</option>
            	<option value="3" <?php if($display == '3') { echo ' selected="selected"'; } ?>>jQuery UI - Accordion</option>
            	<option value="4" <?php if($display == '4') { echo ' selected="selected"'; } ?>>CustomStyle_1 - Tabs</option>
            	<option value="5" <?php if($display == '5') { echo ' selected="selected"'; } ?>>CustomStyle_2 - Tabs</option>
						</select>
        	</label>
          
          <label for="<?php echo $this->get_field_name('tabs'); ?>">Tabs: 
          	<select style="width: 40px;" id="<?php echo $this->get_field_id('tabs'); ?>" name="<?php echo $this->get_field_name('tabs'); ?>">
            	<option value="1" <?php if($tabs == '1') { echo ' selected="selected"'; } ?>>1</option>
            	<option value="2" <?php if($tabs == '2') { echo ' selected="selected"'; } ?>>2</option>
            	<option value="3" <?php if($tabs == '3') { echo ' selected="selected"'; } ?>>3</option>
            	<option value="4" <?php if($tabs == '4') { echo ' selected="selected"'; } ?>>4</option>
            	<option value="5" <?php if($tabs == '5') { echo ' selected="selected"'; } ?>>5</option>
						</select>
        	</label>
        </p>
        
				<?php foreach($contents as $content): ?>
				<div style="background-color:#ECFFC8; border: 1px solid #CCC; margin: 2px 0px; padding: 10px;">
            <p>
          	<label for="<?php echo $this->get_field_name($content['name_name']); ?>">Name: 
            <input class="widefat" id="<?php echo $this->get_field_id($content['name_name']); ?>" name="<?php echo $this->get_field_name($content['name_name']); ?>" type="text" value="<?php echo $content['tab_name']; ?>" />
            </label>
						</p>
            
            <p>
          	<label for="<?php echo $this->get_field_name($content['name_typ']); ?>">Typ:
            <select class="widefat" id="<?php echo $this->get_field_id($content['name_typ']); ?>" name="<?php echo $this->get_field_name($content['name_typ']); ?>">
              <option value="1" <?php if($content['tab_typ'] == '1') { echo ' selected="selected"'; } ?>>Freitext</option>
              <option value="2" <?php if($content['tab_typ'] == '2') { echo ' selected="selected"'; } ?>>Kategorie</option>
              <option value="3" <?php if($content['tab_typ'] == '3') { echo ' selected="selected"'; } ?>>Tags</option>
              <option value="4" <?php if($content['tab_typ'] == '4') { echo ' selected="selected"'; } ?>>Spezialfeld</option>
              <? if(class_exists("TvNewsletter")){ ?>
              <option value="5" <?php if($content['tab_typ'] == '5') { echo ' selected="selected"'; } ?>>MeeNews Newsletter</option>
            	<? } ?>
            </select>
            </label>
            </p>
            
       			<?php if($content['tab_typ'] == 1): ?>
            <p>
            <label for="<?php echo $this->get_field_name($content['name_content']); ?>">Content: 
            <textarea style="width: 100%;height: 100px;" id="<?php echo $this->get_field_id($content['name_content']); ?>" name="<?php echo $this->get_field_name($content['name_content']); ?>"><?php echo stripslashes($content['tab_content']); ?></textarea>
            </label>
            </p>
						<?php endif; ?>
            
       			<?php if($content['tab_typ'] == 2): ?>
            <label for="<?php echo $this->get_field_name($content['name_categories']); ?>">Categories to Display: 
            <div style="overflow:scroll; background-color:#FFFFFF; border: 1px solid #999999; height: 150px; padding: 10px; margin: 5px 0px;">
             	<?php	$menue = get_categories("hide_empty=0&orderby=name");	foreach($menue as $key){	?>
           		<input type="checkbox" id="<?php echo $this->get_field_id($content['name_categories']); ?>" name="<?php echo $this->get_field_name($content['name_categories']); ?>[]" value="<?php echo $key->cat_ID; ?>"<?php if(in_array((integer)$key->cat_ID,(array)unserialize($content['tab_categories']))): ?> checked="checked" <?php endif; ?>> <?php echo $key->name; ?><br />
							<?php } ?>
            </div>
            </label>
					<?php endif; ?>
		       	
       		<?php if($content['tab_typ'] == 3): ?>
            <p>
						<label for="<?php echo $this->get_field_name($content['name_tags']); ?>">Tags:
            <input class="widefat" id="<?php echo $this->get_field_id($content['name_tags']); ?>" name="<?php echo $this->get_field_name($content['name_tags']); ?>" type="text" value="<?php echo $content['tab_tags']; ?>" />
            </label>
            </p>
          <?php endif; ?>
              
            <?php if($content['tab_typ'] == 4): ?>
            <p>
            <label for="<?php echo $this->get_field_name($content['name_customfield']); ?>">Spezialfeld:
            <input class="widefat" id="<?php echo $this->get_field_id($content['name_customfield']); ?>" name="<?php echo $this->get_field_name($content['name_customfield']); ?>" type="text" value="<?php echo $content['tab_customfield']; ?>" />
						</label>
            </p>
            <?php endif; ?>
            
            <?php if($content['tab_typ'] == 5): ?>
            <p>
            <label for="<?php echo $this->get_field_name($content['name_content']); ?>">Content: 
            <textarea style="width: 100%;height: 100px;" id="<?php echo $this->get_field_id($content['name_content']); ?>" name="<?php echo $this->get_field_name($content['name_content']); ?>"><?php echo stripslashes($content['tab_content']); ?></textarea>
            </label>
            </p>            
            <p>
            <label for="<?php echo $this->get_field_name($content['name_meenews_list']); ?>"><?=__('List'); ?>:
           	<? $results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."newscategories"); if($results){ ?>
              <select class="widefat" id="<?php echo $this->get_field_id($content['name_meenews_list']); ?>" name="<?php echo $this->get_field_name($content['name_meenews_list']); ?>">
              <? foreach($results as $result) { ?><option value="<?=$result->id?>" <?php if($content['tab_meenews_list'] == $result->id) { echo ' selected="selected"'; } ?>><?=$result->categoria?></option><? } ?>            
              </select>
						<? } ?>
            </label>
            </p>
            <?php endif; ?>

						<?php if($content['tab_typ'] == 2 || $content['tab_typ'] == 3 || $content['tab_typ'] == 4 ): ?>
						<p>
          	<label for="<?php echo $this->get_field_name($content['name_style']); ?>">Display Style: 
					  <select class="widefat" id="<?php echo $this->get_field_id($content['name_style']); ?>" name="<?php echo $this->get_field_name($content['name_style']); ?>">
            	<option value="1" <?php if($content['tab_style'] == '1') { echo ' selected="selected"'; } ?>>Style 1: <?php echo cp_getOption('style1_name',$this->table); ?></option>
            	<option value="2" <?php if($content['tab_style'] == '2') { echo ' selected="selected"'; } ?>>Style 2: <?php echo cp_getOption('style2_name',$this->table); ?></option>
            	<option value="3" <?php if($content['tab_style'] == '3') { echo ' selected="selected"'; } ?>>Style 3: <?php echo cp_getOption('style3_name',$this->table); ?></option>
					  </select>
           	</label>
						</p>
            
            <p>
          	Anzahl <input style="width: 50px;" id="<?php echo $this->get_field_id($content['name_limit']); ?>" name="<?php echo $this->get_field_name($content['name_limit']); ?>" type="text" value="<?php echo $content['tab_limit']; ?>" /><br />
						</p>
            
            <p>
            Sortierung
            <select id="<?php echo $this->get_field_id($content['name_order']); ?>" name="<?php echo $this->get_field_name($content['name_order']); ?>">
           		<option value="author"<?php if($content['tab_order'] == 'author') { echo ' selected="selected"'; } ?>>Author</option>
              <option value="date"<?php if($content['tab_order'] == 'date') { echo ' selected="selected"'; } ?>>Datum</option>
              <option value="title"<?php if($content['tab_order'] == 'title') { echo ' selected="selected"'; } ?>>Titel</option>
					   	<option value="modified"<?php if($content['tab_order'] == 'modified') { echo ' selected="selected"'; } ?>>zuletzt ge&auml;ndert</option>
					   	<option value="ID"<?php if($content['tab_order'] == 'ID') { echo ' selected="selected"'; } ?>>ID</option>
						</select>
					   
					  <select id="<?php $this->get_field_id($content['name_orderdir']); ?>" name="<?php $this->get_field_name($content['name_orderdir']); ?>">
					  	<option value="ASC"<?php if($content['tab_orderdir'] == 'ASC') { echo ' selected="selected"'; } ?>><?=__('ascending')?></option>
					  	<option value="DESC"<?php if($content['tab_orderdir'] == 'DESC') { echo ' selected="selected"'; } ?>><?=__('descending')?></option>
					  </select>
						</p>
						<?php endif; ?>
				</div>
				<?php endforeach; ?>

<?
		}

	}// END class
	
	
	
	class ContentProWidget_option extends ContentProOptions {
		
		var $name = "ContentPro";
		var $typ = "Widget";
		var $classname = "ContentProWidget";
		var $table = "ContentProWidget";
		var $description = "Beschreibung des Plugins";
		
		var $table_columns = array(
			'name' => "VARCHAR(64) NOT NULL",
			'value' => "LONGTEXT NOT NULL"
		);

		public function update($new_instance){
			$instance['style1_name']   = strip_tags(stripslashes($new_instance['style1_name']));
			$instance['style1_before'] = stripslashes($new_instance['style1_before']);
			$instance['style1_middle'] = stripslashes($new_instance['style1_middle']);
			$instance['style1_after']  = stripslashes($new_instance['style1_after']);
			$instance['style2_name']   = strip_tags(stripslashes($new_instance['style2_name']));
			$instance['style2_before'] = stripslashes($new_instance['style2_before']);
			$instance['style2_middle'] = stripslashes($new_instance['style2_middle']);
			$instance['style2_after']  = stripslashes($new_instance['style2_after']);
			$instance['style3_name']   = strip_tags(stripslashes($new_instance['style3_name']));
			$instance['style3_before'] = stripslashes($new_instance['style3_before']);
			$instance['style3_middle'] = stripslashes($new_instance['style3_middle']);
			$instance['style3_after']  = stripslashes($new_instance['style3_after']);
			
			$instance['format_date']   = stripslashes($new_instance['format_date']);
			return $instance;	
		}

		public function options(){
?>
		<p>
		<table class="form-table">
    <tbody>
    <tr valign="top">
    	<th scope="row">Shortcodes</th>
    	<td>
      	<p>
      		You can use this default shortcodes to style the display as you want.
      	</p>
        
        <p>
        	<strong>{TITLE}</strong> - Displays the title of the post.<br />
        	<strong>{DATE}</strong> - Displays the date of the post.<br />
       		<strong>{THUMBNAIL}</strong> - Displays the thumbnail of the post.<br />
        	<strong>{EXCERPT}</strong> - Displays the excerpt of the post.<br />
        	<strong>{PERMALINK}</strong> - The permalink of the post.<br />
        </p>

        <h3>Post Settings</h3>
        <a href="admin.php?page=ContentProMetaBox">MetaBox Einstellungen</a><br />
      	<a href="admin.php?page=ContentProMetaBox&action=new">Neue Post-Settings hinzuf&uuml;gen</a>        
				<?
					$metaboxes = cp_getResults('ContentProMetaBox','position');
					if(is_array($metaboxes)):
				?>
				<p>			
       	<? foreach($metaboxes as $metabox) { ?>
	        <? if($metabox->replacement != ""): ?>
  	      <strong>{<?=$metabox->replacement?>}</strong> - <?=$metabox->description?><br />
          <? endif; ?>
        <? } ?>
        </p>
        <? endif; ?>
      </td>
    </tr>
    </tbody>
    </table>
		</p>

		<p>
    <table class="widefat">
    <thead>
    <tr>
    	<th colspan="2">Format</th>
    </tr>
    </thead>
    <tbody>
    <tr><td width="100">Date Format:</td><td><input id="format_date" name="format_date" type="text" value="<? echo htmlspecialchars($this->_getOption('format_date')); ?>" /> N&auml;heres zum Datums- und Zeitformat unter: <a href="http://de.php.net/manual/de/function.date.php" target="_blank">PHP-Funktion date()</a>.</td></tr>
    </tbody>
    </table>
    </p>

		<p>
    <table class="widefat">
    <thead>
    <tr>
    	<th colspan="2">Style 1</th>
    </tr>
    </thead>
    <tbody>
    <tr><td width="100">Name:</td><td><input style="width: 500px;" id="style1_name" name="style1_name" type="text" value="<? echo htmlspecialchars($this->_getOption('style1_name')); ?>" /></td></tr>
    <tr><td>Before:</td><td><input style="width: 500px;" id="style1_before" name="style1_before" type="text" value="<? echo htmlspecialchars($this->_getOption('style1_before')); ?>" /></td></tr>
    <tr><td>Middle:</td><td><textarea style="width:500px; height:150px;" id="style1_middle" name="style1_middle"><? echo $this->_getOption('style1_middle'); ?></textarea></td></tr>
    <tr><td>After:</td><td><input style="width: 500px;" id="style1_after" name="style1_after" type="text" value="<? echo htmlspecialchars($this->_getOption('style1_after')); ?>" /></td></tr>
    </tbody>
    </table>
    </p>
        
    <p>
    <table class="widefat">
    <thead>
    <tr>
    	<th colspan="2">Style 2</th>
    </tr>
    </thead>
    <tbody>
    <tr><td width="100">Name:</td><td><input style="width: 500px;" id="style2_name" name="style2_name" type="text" value="<? echo htmlspecialchars($this->_getOption('style2_name')); ?>" /></td></tr>
    <tr><td>Before:</td><td><input style="width: 500px;" id="style2_before" name="style2_before" type="text" value="<? echo htmlspecialchars($this->_getOption('style2_before')); ?>" /></td></tr>
    <tr><td>Middle:</td><td><textarea style="width:500px; height:150px;" id="style2_middle" name="style2_middle"><? echo $this->_getOption('style2_middle'); ?></textarea></td></tr>
    <tr><td>After:</td><td><input style="width: 500px;" id="style2_after" name="style2_after" type="text" value="<? echo htmlspecialchars($this->_getOption('style2_after')); ?>" /></td></tr>
    </tbody>
    </table>
    </p>
    
    <p>
    <table class="widefat">
    <thead>
    <tr>
    	<th colspan="2">Style 3</th>
    </tr>
    </thead>
    <tbody>
    <tr><td width="100">Name:</td><td><input style="width: 500px;" id="style3_name" name="style3_name" type="text" value="<? echo htmlspecialchars($this->_getOption('style3_name')); ?>" /></td></tr>
    <tr><td>Before:</td><td><input style="width: 500px;" id="style3_before" name="style3_before" type="text" value="<? echo htmlspecialchars($this->_getOption('style3_before')); ?>" /></td></tr>
    <tr><td>Middle:</td><td><textarea style="width:500px; height:150px;" id="style3_middle" name="style3_middle"><? echo $this->_getOption('style3_middle'); ?></textarea></td></tr>
    <tr><td>After:</td><td><input style="width: 500px;" id="style3_after" name="style3_after" type="text" value="<? echo htmlspecialchars($this->_getOption('style3_after')); ?>" /></td></tr>
    </tbody>
    </table>
    </p>
    
    </div>
    
    <input name="action" id="action" value="update" type="hidden">
    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
<?		
		}
			
	}

?>