<?php

	class ContentProFacebook {
		
		var $name = "Facebook";
		var $typ = "Plugin";
		var $classname = "ContentProFacebook";
		var $table = "ContentProFacebook";
		var $description = "Make your article Facebook ready";
		
		function __construct(){
			add_action('the_content', array(&$this,'_likebutton'),2);
		}
		
		function _likebutton($text){
			global $wpdb,$post;
			
			$settings = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_ContentProFacebook");
			foreach($settings as $key){
				$instance[(string)$key->name] = $key->value;
			}
			
			if($instance["display_likebutton"] == "true"){
				$permalink 	= urlencode(get_permalink(get_the_ID()));
				$return = "<iframe src=\"http://www.facebook.com/plugins/like.php?href=".$permalink."&amp;layout=".$instance['likebutton_style']."&amp;show_faces=".$$instance['likebutton_faces']."&amp;width=".$instance['likebutton_width']."&amp;action=".$instance['likebutton_verb']."&amp;font=".$instance['likebutton_font']."&amp;colorscheme=".$instance['likebutton_color']."&amp;height=".$instance['likebutton_height']."\" scrolling=\"no\" frameborder=\"0\" style=\"margin-top: 20px; border:none; overflow:hidden; width:".$instance['likebutton_width']."px; height:".$instance['likebutton_height']."px;\" allowTransparency=\"true\"></iframe>";
				return $text . $return;				
			}
			return $text;
		}
		
	}

	class ContentProFacebook_option extends ContentProOptions {

		var $name = "Facebook";
		var $typ = "Plugin";
		var $classname = "ContentProFacebook";
		var $table = "ContentProFacebook";
		var $description = "Make your article Facebook ready";

		var $table_columns = array(
			'name' => "VARCHAR(64) NOT NULL",
			'value' => "LONGTEXT NOT NULL",
		);

		public function update($new_instance){
			//Like Button
			$instance['display_likebutton'] = $new_instance['display_likebutton'];
			$instance['likebutton_style']   = $new_instance['likebutton_style'];
			$instance['likebutton_faces']   = $new_instance['likebutton_faces'];
			$instance['likebutton_width']   = $new_instance['likebutton_width'];
			$instance['likebutton_height']  = $new_instance['likebutton_height'];
			$instance['likebutton_verb']    = $new_instance['likebutton_verb'];
			$instance['likebutton_font']    = $new_instance['likebutton_font'];
			$instance['likebutton_color']   = $new_instance['likebutton_color'];

			return $instance;	
		}

		public function options(){
			global $wpdb;
			
		?>
    <p>
    <table class="widefat">
    <thead>
    	<tr><th colspan="2">Display</th></tr>
    </thead>
    <tbody>
	    <tr><td width="150"><?php _e('Like Box'); ?></td><td><select id="display_likebutton" name="display_likebutton" /><option value="true" <?php if($this->_getOption('display_likebutton') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('display_likebutton') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td></tr>
    </tbody>
    </table>
    </p>  
    
    <p>
    <table class="widefat">
    <thead>
    	<tr><th colspan="2">Like Button</th></tr>
    </thead>
    <tbody>
	    <tr><td width="150"><?=__('Layout Style'); ?></td><td><select id="likebutton_style" name="likebutton_style" /><option value="standard" <?php if($this->_getOption('likebutton_style') == "standard"): ?> selected<?php endif; ?>><?php _e('standard') ?></option><option value="button_count" <?php if($this->_getOption('likebutton_style') == "button_count"): ?> selected<?php endif; ?>><?php _e('button_count') ?></option></select></td></tr>
	    <tr><td width="150"><?=__('Show Faces'); ?></td><td><select id="likebutton_faces" name="likebutton_faces" /><option value="true" <?php if($this->_getOption('likebutton_faces') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('likebutton_faces') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td></tr>
	   	<tr><td width="150"><?=__('Width')?>:</td><td><input size="4" id="likebutton_width" name="likebutton_width" type="text" value="<? echo htmlspecialchars($this->_getOption('likebutton_width')); ?>" />px</td></tr>
	   	<tr><td width="150"><?=__('Height')?>:</td><td><input size="4"  id="likebutton_height" name="likebutton_height" type="text" value="<? echo htmlspecialchars($this->_getOption('likebutton_height')); ?>" />px</td></tr>
	    <tr><td width="150"><?=__('Verb to display'); ?>:</td><td><select id="likebutton_verb" name="likebutton_verb" /><option value="like" <?php if($this->_getOption('likebutton_verb') == "like"): ?> selected<?php endif; ?>>Like</option><option value="recommend" <?php if($this->_getOption('likebutton_verb') == "recommend"): ?> selected<?php endif; ?>>Recommend</option></select> The verb to display in the button. Currently only 'like' and recommend are supported.</td></tr>
	    <tr><td width="150"><?=__('Font'); ?>:</td><td>
      	<select id="likebutton_font" name="likebutton_font" />
        	<option value="" <?php if($this->_getOption('likebutton_font') == ""): ?> selected<?php endif; ?>></option>
          <option value="arial" <?php if($this->_getOption('likebutton_font') == "arial"): ?> selected<?php endif; ?>>arial</option>
          <option value="lucida+grande" <?php if($this->_getOption('likebutton_font') == "lucida+grande"): ?> selected<?php endif; ?>>lucida grande</option>
          <option value="segoe+ui" <?php if($this->_getOption('likebutton_font') == "segoe+ui"): ?> selected<?php endif; ?>>segoe ui</option>
          <option value="tahoma" <?php if($this->_getOption('likebutton_font') == "tahoma"): ?> selected<?php endif; ?>>tahoma</option>
          <option value="trebuchet+ms" <?php if($this->_getOption('likebutton_font') == "trebuchet+ms"): ?> selected<?php endif; ?>>trebuchet ms</option>
          <option value="verdana" <?php if($this->_getOption('likebutton_font') == "verdana"): ?> selected<?php endif; ?>>verdana</option>
        </select> 
        The font of the plugin.</td></tr>
	    <tr><td width="150"><?=__('Color Scheme'); ?></td><td><select id="likebutton_color" name="likebutton_color" /><option value="light" <?php if($this->_getOption('likebutton_color') == "light"): ?> selected<?php endif; ?>><?php _e('Light') ?></option><option value="dark" <?php if($this->_getOption('likebutton_color') == "dark"): ?> selected<?php endif; ?>><?php _e('Dark') ?></option></select></td></tr>
        
      <tr>
        <th width="150"><?=__('Preview')?></th>
        <td>
          <iframe src="http://www.facebook.com/plugins/like.php?href=<?php urlencode(bloginfo('url')); ?>&amp;layout=<?=$this->_getOption('likebutton_style')?>&amp;show_faces=<?=$this->_getOption('likebutton_faces')?>&amp;width=<?=$this->_getOption('likebutton_width')?>&amp;action=<?=$this->_getOption('likebutton_verb')?>&amp;font=<?=$this->_getOption('likebutton_font')?>&amp;colorscheme=<?=$this->_getOption('likebutton_color')?>&amp;height=<?=$this->_getOption('likebutton_height')?>" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:<?=$this->_getOption('likebutton_width')?>px; height:<?=$this->_getOption('likebutton_height')?>px;" allowTransparency="true"></iframe>
        </td>
      </tr>
    </tbody>
    </table>
    </p>  
    
    <input name="action" id="action" value="update" type="hidden">
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
    <?		
		}
	}
	
	global $ContentProFacebook;
	$ContentProFacebook = new ContentProFacebook();
	
?>