<?

	class ContentProBookmarks {
		
		var $name = "Bookmarks";
		var $typ = "Plugin";
		var $classname = "ContentProBookmarks";
		var $table = "ContentProBookmarks";
		var $description = 'Beschreibung';

		function __construct(){
			add_action('the_content', array(&$this,'_display'),98);
		}		
		
		function _display($text){
			global $wpdb,$post;
			
			$settings = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_ContentProBookmarks");
			foreach($settings as $key){
				$instance[(string)$key->name] = $key->value;
			}
			
			if($instance["display_typ"] == "single"){
				if(is_single() == true){
				
					if($post->post_type == "page"){
						$display = $instance["display_attach_page"];
					}elseif($post->post_type == "post"){
						$display = $instance["display_attach_post"];
					}
							
				}	
			}elseif($instance["display_typ"] == "all"){
			
					if($post->post_type == "page"){
						$display = $instance["display_attach_page"];
					}elseif($post->post_type == "post"){
						$display = $instance["display_attach_post"];
					}
					
			}
			
			
						
			if($display == "true"):
			
				$excerpt = get_the_content();
				$excerpt = strip_shortcodes($excerpt);
				$excerpt = strip_tags($excerpt);
				$excerpt_length = 55;
				$words = explode(' ', $excerpt, $excerpt_length + 1);
				if(count($words) > $excerpt_length){
				  array_pop($words);
				  array_push($words, '[...]');
				  $excerpt = implode(' ', $words);
				}
				$excerpt 	= urlencode($excerpt);
				$blogname	= urlencode(get_bloginfo('name'));
				$permalink 	= urlencode(get_permalink(get_the_ID()));
				$title		= urlencode(get_the_title(get_the_ID()));

				$instance['network'] = unserialize($instance['network']);
				cp_sort($instance['network'], "position",true);
				if(is_array($instance['network'])):
				
					$return .= '<div class="contentprobookmarks">';
					$return .= $instance['template_posts_before'];
					
					foreach($instance['network'] as $network):
						
						if($network['display'] == "on"):
							$return .= $instance['template_posts_middle'];
							$return = str_replace('{NAME}',$network['name'],$return);
							$return = str_replace('{LINK}',$network['link'],$return);
							$return = str_replace('{ICON}','<img src="'.$network[$instance['display_imgsize']].'" width="'.$instance['display_imgsize'].'" height="'.$instance['display_imgsize'].'" border="0">',$return);
							$return = str_replace('{BLOGNAME}',	$blogname,	$return);
							$return = str_replace('{EXCERPT}',	$excerpt,	$return);
							$return = str_replace('{PERMALINK}',$permalink,	$return);
							$return = str_replace('{TITLE}',	$title,		$return);
						endif;
							
					endforeach;
					
					$return .= $instance['template_posts_after'];
					$return .= '</div>';
	
				endif;
			
				$text .= $return;
			
			endif;
			return $text;
			
		}

	}
	
	
	class ContentProBookmarks_option extends ContentProOptions {
	
		var $name = "Bookmarks";
		var $typ = "Plugin";
		var $classname = "ContentProBookmarks";
		var $table = "ContentProBookmarks";
		var $description = 'Beschreibung';
		
		var $table_columns = array(
			'name' => "VARCHAR(64) NOT NULL",
			'value' => "LONGTEXT NOT NULL",
		);

		public function update($new_instance){
			$instance['display_attach_post'] = $new_instance['display_attach_post'];
			$instance['display_attach_page'] = $new_instance['display_attach_page'];
			$instance['display_typ'] = $new_instance['display_typ'];
			$instance['display_imgsize'] = $new_instance['display_imgsize'];
			$instance['display_number'] = $new_instance['display_number'];
			$instance['network'] = serialize($new_instance['network']);
			$instance['template_posts_before'] = stripslashes($new_instance['template_posts_before']);
			$instance['template_posts_middle'] = stripslashes($new_instance['template_posts_middle']);
			$instance['template_posts_after'] = stripslashes($new_instance['template_posts_after']);
			return $instance;	
		}

		public function options(){
			global $wpdb;
			$network = unserialize($this->_getOption('network'));
		?>
			
		<br style="clear" />
		<p>
    <table class="widefat">
    <thead>
    	<tr><th colspan="2">Display</th></tr>
    </thead>
    <tbody>
	    <tr><td width="150"><?php _e('Attach to Post'); ?></td><td><select id="display_attach_post" name="display_attach_post" /><option value="true" <?php if($this->_getOption('display_attach_post') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('display_attach_post') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td></tr>
	    <tr><td width="150"><?php _e('Attach to Page'); ?></td><td><select id="display_attach_page" name="display_attach_page" /><option value="true" <?php if($this->_getOption('display_attach_page') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('display_attach_page') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td></tr>
	    <tr><td width="150"><?php _e('Display'); ?></td><td><select id="display_typ" name="display_typ" /><option value="all" <?php if($this->_getOption('display_typ') == "all"): ?> selected<?php endif; ?>><?php _e('All') ?></option><option value="single" <?php if($this->_getOption('display_typ') == "single"): ?> selected<?php endif; ?>><?php _e('Single') ?></option></select></td></tr>
	    <tr><td width="150"><?php _e('Image Size'); ?></td><td><select id="display_imgsize" name="display_imgsize" /><option value="16" <?php if($this->_getOption('display_imgsize') == "16"): ?> selected<?php endif; ?>><?php _e('16px') ?></option><option value="32" <?php if($this->_getOption('display_imgsize') == "32"): ?> selected<?php endif; ?>><?php _e('32px') ?></option></select></td></tr>
    </tbody>
    </table>
    </p> 
    
    <p>
    <table class="widefat">
    <thead>
    	<tr><th colspan="2"><?php _e('Bookmark'); ?> - Template</th></tr>
    </thead>
    <tbody>
      <tr>
      	<td>Standard Class</td>
        <td>This plugin will automatically put a DIV-Layer around your code. To style this Layer use:<pre style="background-color:#EEE; padding: 10px; border: 1px solid #DDDDDD;">.contentprobookmarks</pre></td>
      </tr>
    	<tr>
      	<td><?=__("Before Entry")?>:</td>
        <td><textarea rows="2" cols="70" id="template_posts_before" name="template_posts_before"><?=stripslashes($this->_getOption('template_posts_before'))?></textarea></td>
      </tr>
	    <tr>
      	<td width="150"><?=__("Each Entry")?>:</td>
      	<td>
      	<textarea rows="10" cols="70" id="template_posts_middle" name="template_posts_middle"><?=stripslashes($this->_getOption('template_posts_middle'))?></textarea>
      	</td>
      </tr>
      <tr>
      	<td><?=__("After Entry")?>:</td>
        <td><textarea rows="2" cols="70" id="template_posts_after" name="template_posts_after"><?=stripslashes($this->_getOption('template_posts_after'))?></textarea></td>
      </tr>
    </tbody>
    </table>
    </p>   
		
		
		<p>
		<?
			if ($handle = opendir(WP_PLUGIN_DIR . '/ContentPro/Images/SocialNetworks/')):
				while(false !== ($file = readdir($handle))) {
					if($file != "." && $file != ".."):
						$path_info = pathinfo($this->plugin_dir . $what.'/'.$file);
						if($path_info['extension'] == "png"):
							$clear = $path_info['filename'];
							$clear = str_replace('_16','',$clear);
							$clear = str_replace('_32','',$clear);
							$networks[$clear]['name'] = $clear;
							if($clear == str_replace('_16','',$path_info['filename'])) $networks[$clear]['16'] = $path_info['basename'];
							elseif($clear == str_replace('_32','',$path_info['filename'])) $networks[$clear]['32'] = $path_info['basename'];
							endif;
					endif;
				}
				closedir($handle);
			endif;
		?>
		</p>
    
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>

		<h3>Networks</h3>
		<p>
    <table class="widefat">
    <thead>
    	<tr>
      	<th></th>
        <th></th>
				<th><?php _e('Title'); ?></th>
        <th><?php _e('Link'); ?></th>
				<th><?php _e('Image'); ?></th>
				<th>16x16</th>
				<th>32x32</th>
        <th>Position</th>
			</tr>
    </thead>
    <tbody>
		<?
			asort($networks);
			foreach($networks as $data): 
		?>
    	<tr>
      	<td width="16"><input name="network[<?=$data['name']?>][display]" type="checkbox" <?php if($network[$data['name']]['display'] == "on"): ?>checked="checked"<? endif; ?> /></td>
        <td width="16"> <? if(isset($data['16'])): ?><img src="<?=WP_PLUGIN_URL?>/ContentPro/Images/SocialNetworks/<?=$data['16']?>" /><? endif; ?></td>
      	<td width="100"><input class="widefat" name="network[<?=$data['name']?>][name]" type="text" value="<?=$network[$data['name']]['name']?>" /></td>
        <td><input class="widefat" name="network[<?=$data['name']?>][link]" type="text" value="<?=$network[$data['name']]['link']?>" /></td>
        <td width="60"><?=$data['name']?></td>
				<td><? if(isset($data['16'])): ?><input type="hidden" name="network[<?=$data['name']?>][16]" value="<?=WP_PLUGIN_URL?>/ContentPro/Images/SocialNetworks/<?=$data['16']?>" /> <img src="<?=WP_PLUGIN_URL?>/ContentPro/Images/SocialNetworks/<?=$data['16']?>" /><? endif; ?></td>
				<td><? if(isset($data['32'])): ?><input type="hidden" name="network[<?=$data['name']?>][32]" value="<?=WP_PLUGIN_URL?>/ContentPro/Images/SocialNetworks/<?=$data['32']?>" /> <img src="<?=WP_PLUGIN_URL?>/ContentPro/Images/SocialNetworks/<?=$data['32']?>" /><? endif; ?></td>
        <td><input class="widefat" name="network[<?=$data['name']?>][position]" type="text" value="<? if($network[$data['name']]['position'] != ""): echo $network[$data['name']]['position']; else: echo "99"; endif; ?>" /></td>
      </tr>
		<? endforeach; ?>
    </tbody>
    </table>
    </p>   
		
    <input name="action" id="action" value="update" type="hidden">
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		<?
		}

	}
	
	global $ContentProBookmarks;
	$ContentProBookmarks = new ContentProBookmarks();

?>