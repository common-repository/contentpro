<?php

	class ContentProAutor {
		
		var $name = "Author";
		var $typ = "Plugin";
		var $classname = "ContentProAutor";
		var $table = "ContentProAutor";
		var $description = "This plugin enables you to display information about the author within an article or page.";
		
		function __construct(){
			add_shortcode('AUTHOR',array(&$this,'_replaceContent'));
		}
		
		function _replaceContent($atts){
			global $post,$wpdb;
			$tmp_post = $post;
			
			$author_ID = $post->post_author;
			$settings = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_ContentProAutor");
			foreach($settings as $key){
				$instance[(string)$key->name] = $key->value;
			}
			
			$name = get_the_author_meta('display_name',$author_ID);			
			$link = get_author_posts_url( $author_ID );
			$format_date = $instance["format_date"];

			if($instance["display_info"] == "true")	$bio = get_the_author_meta('description',$author_ID);
			if($instance["display_email"] == "true")	$email = get_the_author_meta('user_email',$author_ID);
			if($instance["display_gravatar"] == "true"):
				if(function_exists('get_avatar')) $gravatar = get_avatar($author_ID, $instance["format_gravatar"]);
			endif;
			if($instance["display_website"] == "true") $website = "http://".str_replace('http://','',get_the_author_meta('user_url',$author_ID));
			if($instance["display_posts"] == "true"):
			
				
				$return .= $instance['template_posts_before'];
				$posts = get_posts('exclude='.$post->ID.'&showposts=3&author='.$author_ID);
 				foreach($posts as $post) :
						
						setup_postdata($post);
						
						$return .= $instance['template_posts_middle'];
						$metaboxes = cp_getResults('ContentProMetaBox','position');
						if(is_array($metaboxes)):
							foreach($metaboxes as $metabox) {
								if($metabox->replacement != "")
								$return = str_replace('{'.$metabox->replacement.'}',get_post_meta(get_the_ID(),$metabox->value,true),$return);
							}
						endif;
						$return = str_replace('{DATE}',get_the_time($format_date),$return);
						$return = str_replace('{PERMALINK}',get_permalink(get_the_ID()),$return);
						$return = str_replace('{THUMBNAIL}',get_the_post_thumbnail(get_the_ID(),'thumbnail'),$return);
						$return = str_replace('{TITLE}',get_the_title(get_the_ID()),$return);
						$return = str_replace('{EXCERPT}',get_the_excerpt(get_the_ID()),$return);
					
				endforeach;
				$return .= $instance['template_posts_after'];
				
				
			endif;

			$instance["template_author"] = str_replace('{NAME}','<a href="'.$link.'" title="'.__("Author").': '.$name.'">'.$name.'</a>',$instance['template_author']);
			$instance["template_author"] = str_replace('{BIOGRAPHICALINFO}',$bio,$instance['template_author']);
			$instance["template_author"] = str_replace('{GRAVATAR}',$gravatar,$instance['template_author']);
			$instance["template_author"] = str_replace('{EMAIL}','<a href="mailto:'.$email.'" title="'.$name.'">'.$email.'</a>',$instance['template_author']);
			$instance["template_author"] = str_replace('{WEBSITE}','<a href="'.$website.'" rel="bookmark" target="_blank">'.$website.'</a>',$instance['template_author']);
			$instance["template_author"] = str_replace('{RECENTPOSTS}',$return,$instance['template_author']);

			echo '<div class="contentproauthor '.$instance["format_align"].'" style="width:'.$instance["format_width"].';">'.$instance["template_author"].'</div>';
			$post = $tmp_post;
		}
		
	}

	class ContentProAutor_option extends ContentProOptions {

		var $name = "Author";
		var $typ = "Plugin";
		var $classname = "ContentProAutor";
		var $table = "ContentProAutor";
		var $description = "This plugin enables you to display information about the author within an article or page.";

		var $table_columns = array(
			'name' => "VARCHAR(64) NOT NULL",
			'value' => "LONGTEXT NOT NULL",
		);

		public function update($new_instance){
			$instance['format_date']   		= stripslashes($new_instance['format_date']);
			$instance['format_align']  		= stripslashes($new_instance['format_align']);
			$instance['format_gravatar']  = stripslashes($new_instance['format_gravatar']);
			$instance['format_width']  		= stripslashes($new_instance['format_width']);

			$instance['display_gravatar'] = $new_instance['display_gravatar'];
			$instance['display_info']  		= $new_instance['display_info'];
			$instance['display_website']  = $new_instance['display_website'];
			$instance['display_email']  	= $new_instance['display_email'];
			$instance['display_posts']  	= $new_instance['display_posts'];

			$instance['template_author'] = stripslashes($new_instance['template_author']);
			$instance['template_posts_before'] = stripslashes($new_instance['template_posts_before']);
			$instance['template_posts_middle'] = stripslashes($new_instance['template_posts_middle']);
			$instance['template_posts_after'] = stripslashes($new_instance['template_posts_after']);
			return $instance;	
		}

		public function options(){
			global $wpdb;
			
		?>
    <p>
		<table class="form-table">
    <tbody>
    <tr valign="top">
    	<th scope="row">Shortcodes</th>
    	<td>
      	<p>
        <strong>[AUTHOR]</strong> - Insert this code into your article to display the information about the author.
        </p>
      </td>
    </tr>
    </tbody>
    </table>
		</p>

    <p>
    <table class="widefat">
    <thead>
    	<tr><th colspan="2">Display</th></tr>
    </thead>
    <tbody>
	    <tr><td width="150"><?php _e('Gravatar'); ?></td><td><select id="display_gravatar" name="display_gravatar" /><option value="true" <?php if($this->_getOption('display_gravatar') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('display_gravatar') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td></tr>
	   	<tr><td width="150"><?php _e('E-mail'); ?></td><td><select id="display_email" name="display_email" /><option value="true" <?php if($this->_getOption('display_email') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('display_email') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td></tr>
     	<tr><td width="150"><?php _e('Website'); ?></td><td><select id="display_website" name="display_website" /><option value="true" <?php if($this->_getOption('display_website') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('display_website') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td></tr>
      <tr><td width="150"><?php _e('Biographical Info'); ?></td><td><select id="display_info" name="display_info" /><option value="true" <?php if($this->_getOption('display_info') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('display_info') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td></tr>
      <tr><td width="150"><?php _e('Recent Posts'); ?></td><td><select id="display_posts" name="display_posts" /><option value="true" <?php if($this->_getOption('display_posts') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('display_posts') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td></tr>
    </tbody>
    </table>
    </p>    
    
    <p>
    <table class="widefat">
    <thead>
    	<tr><th colspan="2"><?=__("Author")?> - Template</th></tr>
    </thead>
    <tbody>  	  
    	<tr>
      	<td width="150"><?=__("Align")?>:</td>
        <td>
        	<select id="format_align" name="format_align" /><option value="alignleft" <?php if($this->_getOption('format_align') == "alignleft"): ?> selected<?php endif; ?>><?=__("Left")?></option><option value="aligncenter" <?php if($this->_getOption('format_align') == "aligncenter"): ?> selected<?php endif; ?>><?=__("Center")?></option><option value="alignright" <?php if($this->_getOption('format_align') == "alignright"): ?> selected<?php endif; ?>><?=__("Right")?></option></select>
If the alignment is not correctly displayed add the following lines to your <a href="theme-editor.php">stylesheet</a>.       
<pre style="background-color:#EEE; padding: 10px; border: 1px solid #DDDDDD;">
.alignleft   { float: left; }
.alignright  { float: right; }
.aligncenter { margin: 0 auto; }
</pre>
        </td>
      </tr>
      <tr>
      	<td>Standard Class</td>
        <td>This plugin will automatically put a DIV-Layer around your code. To style this Layer use:<pre style="background-color:#EEE; padding: 10px; border: 1px solid #DDDDDD;">.contentproauthor</pre></td>
      </tr>
	   	<tr><td width="150"><?=__("Width")?>:</td><td><input id="format_width" name="format_width" type="text" value="<? echo htmlspecialchars($this->_getOption('format_width')); ?>" /> Set the width of the box. For example: 100px or 100%</td></tr>
      <tr><td width="150"><?=__("Gravatar Size")?>:</td><td><input id="format_gravatar" name="format_gravatar" type="text" value="<? echo htmlspecialchars($this->_getOption('format_gravatar')); ?>" /> The default Gravatar size is <strong>96</strong> if you do not set the size. (max size is <strong>512</strong> - set the value without "px")</td></tr>
      <tr>
      	<td>Codes</td>
        <td>      	
          <p>
						You can use these default shortcodes to adjust the display to your wishes.
          </p>
          <p>
            <strong>{NAME}</strong> - Displays the name of the author.<br />
            <strong>{GRAVATAR}</strong> - Displays the <a href="http://www.gravatar.com" target="_blank">Gravatar</a> of the author.<br />
            <strong>{EMAIL}</strong> - Displays the e-mail of the author.<br />
            <strong>{WEBSITE}</strong> - Displays the website of the author.<br />
            <strong>{BIOGRAPHICALINFO}</strong> - Displays the Biography of the author.<br />
            <strong>{RECENTPOSTS}</strong> - Shows the last entries of the author.<br />
          </p> 
        </td>
      </tr>
	    <tr>
      	<td width="150"><?=__("Template")?>:</td>
      	<td>
      	<textarea rows="10" cols="70" id="template_author" name="template_author"><?=stripslashes($this->_getOption('template_author'))?></textarea>
      	</td>
      </tr>
    </tbody>
    </table>
    </p>    
      
		<p>
    <table class="widefat">
    <thead>
    	<tr><th colspan="2"><?php _e('Recent Posts'); ?> - Template</th></tr>
    </thead>
    <tbody>
	    <tr><td width="150"><?=__("Date")?>:</td><td><input id="format_date" name="format_date" type="text" value="<? echo htmlspecialchars($this->_getOption('format_date')); ?>" /> N&auml;heres zum Datums- und Zeitformat unter: <a href="http://de.php.net/manual/de/function.date.php" target="_blank">PHP-Funktion date()</a>.</td></tr>

			<tr>
      	<td>Codes</td>
      	<td>
          <p>
            You can use these default shortcodes to adjust the display to your wishes.
          </p>
          
          <p>
            <strong>{TITLE}</strong> - Displays the title of the article.<br />
            <strong>{DATE}</strong> - Displays the date of the article.<br />
            <strong>{THUMBNAIL}</strong> - Displays the thumbnail of the article.<br />
            <strong>{EXCERPT}</strong> - Displays the excerpt of the article.<br />
            <strong>{PERMALINK}</strong> - The permalink of the article.<br />
          </p>
  
  				<p>
          <h3>Post Settings</h3>
          <a href="admin.php?page=ContentProMetaBox">MetaBox Einstellungen</a><br />
          <a href="admin.php?page=ContentProMetaBox&action=new">Neue Post-Settings hinzuf&uuml;gen</a>   
          </p>
          <br>
          <br>
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

    	<tr>
      	<td><?=__("Before Entry")?>:</td>
        <td><input id="template_posts_before" name="template_posts_before" type="text" value="<? echo htmlspecialchars($this->_getOption('template_posts_before')); ?>" /></td>
      </tr>
	    <tr>
      	<td width="150"><?=__("Each Entry")?>:</td>
      	<td>
      	<textarea rows="10" cols="70" id="template_posts_middle" name="template_posts_middle"><?=stripslashes($this->_getOption('template_posts_middle'))?></textarea>
      	</td>
      </tr>
      <tr>
      	<td><?=__("After Entry")?>:</td>
        <td><input id="template_posts_after" name="template_posts_after" type="text" value="<? echo htmlspecialchars($this->_getOption('template_posts_after')); ?>" /></td>
      </tr>
    </tbody>
    </table>
    </p>   
    
    <input name="action" id="action" value="update" type="hidden">
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
    <?		
		}
	}
	
	global $ContentProAutor;
	$ContentProAutor = new ContentProAutor();
	
?>