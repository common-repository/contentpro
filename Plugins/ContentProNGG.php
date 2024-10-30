<?
	class ContentProNGG {

		var $name = "NGG";
		var $typ = "Plugin";
		var $classname = "ContentProNGG";
		var $table = "ContentProNGG";
		var $description = "Beschreibung des Plugins";
		
		function __construct(){
			add_shortcode('album', array(&$this,'_album_replace'));
			add_shortcode('nggallery', array(&$this,'_gallery_replace'));
			add_action('template_redirect', array(&$this, 'load_scripts') );
		}
		
		function load_scripts(){
			global $ContentPro;
			
			$display_style = cp_getOption('display_style',"ContentProNGG");
			switch($display_style){
				case "galleryview": 
					wp_enqueue_style('GalleryView', $ContentPro->plugin_url.'Plugins/ContentProNGG/galleryview/galleryview.css' , false, '2.1.1', 'screen'); 

					wp_deregister_script('jquery-galleryview');
					wp_register_script('jquery-galleryview',$ContentPro->plugin_url.'Plugins/ContentProNGG/galleryview/jquery.galleryview-2.1.1.js', 'jquery', '2.1.1');
					wp_enqueue_script('jquery-galleryview');
					
					wp_deregister_script('jquery-timers');
					wp_register_script('jquery-timers',$ContentPro->plugin_url.'Plugins/ContentProNGG/galleryview/jquery.timers-1.2.js', 'jquery', '1.2');
					wp_enqueue_script('jquery-timers');
					
					wp_deregister_script('jquery-easing');
					wp_register_script('jquery-easing',$ContentPro->plugin_url.'Plugins/ContentProNGG/galleryview/jquery.easing.1.3.js', 'jquery', '1.3');
					wp_enqueue_script('jquery-easing');
					
				break;
				
				case "contentprogallery": 
					wp_enqueue_style('ContentProGallery', $ContentPro->plugin_url.'Plugins/ContentProNGG/contentprogallery/contentprogallery.css' , false, '1.0', 'screen'); 

					wp_deregister_script('jquery-cpgallery');
					wp_register_script('jquery-cpgallery',$ContentPro->plugin_url.'Plugins/ContentProNGG/contentprogallery/jquery.contentprogallery-1.0.js', 'jquery', '1.0');
					wp_enqueue_script('jquery-cpgallery');
				break;				
				
					
				case "coda-slider": 
				
					wp_enqueue_style('CodaSlider', $ContentPro->plugin_url.'Plugins/ContentProNGG/coda-slider/coda-slider-2.0.css' , false, '2.0', 'screen'); 

					wp_deregister_script('jquery-coda-slider');
					wp_register_script('jquery-coda-slider',$ContentPro->plugin_url.'Plugins/ContentProNGG/coda-slider/jquery.coda-slider-2.0.js', 'jquery', '2.0');
					wp_enqueue_script('jquery-coda-slider');
					
					wp_deregister_script('jquery-easing');
					wp_register_script('jquery-easing',$ContentPro->plugin_url.'Plugins/ContentProNGG/coda-slider/jquery.easing.1.3.js', 'jquery', '1.3');
					wp_enqueue_script('jquery-easing');
					
				break;	
			}
			
		}
		
		
		function _gallery_replace($atts){
			global $wpdb,$ContentProAdvertisments;
			extract(shortcode_atts(array('id' => 'all','template' => 'default'), $atts));
			$display_style = cp_getOption('display_style',"ContentProNGG");
			
			$display_advertisment = cp_getOption('display_advertisment',"ContentProNGG");
			$advert = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_ContentProAdvertisments where id = '".$display_advertisment."'");
			$advert = stripslashes($advert[0]->code);
			
			if(is_numeric($id)){
				$sql = "SELECT gid,name,path,title,galdesc,pageid,previewpic,previewpic.filename,pictures.filename as filename_alternative,author,count(pictures.pid) as quantity
								FROM ".$wpdb->prefix."ngg_gallery as gallery
								LEFT JOIN ".$wpdb->prefix."ngg_pictures as pictures on ( gallery.gid = pictures.galleryid )
								LEFT JOIN ".$wpdb->prefix."ngg_pictures as previewpic on ( gallery.previewpic = previewpic.pid )
								WHERE gid = ".$id."
								GROUP BY gallery.gid";
				$gallery = $wpdb->get_row($sql);
				//$content = "<h1>Title ($id): ".$gallery->title."</h1>";
			}else{
				//$content = "<h1>All</h1>";
			}
			
			$sql = "SELECT
								pictures.pid,
								pictures.post_id,
								pictures.galleryid,
								gallery.path,
								pictures.filename,
								pictures.description,
								pictures.alttext,
								pictures.imagedate,
								pictures.exclude,
								pictures.sortorder,
								pictures.meta_data
							FROM ".$wpdb->prefix."ngg_pictures as pictures
							LEFT JOIN ".$wpdb->prefix."ngg_gallery as gallery on ( pictures.galleryid = gallery.gid ) ";
			if(is_numeric($id)){
				$sql .= "WHERE galleryid = ".$id;
			}
			$result = $wpdb->get_results($sql);
			$anzahl = count($result);
			
			switch($display_style){
				case "galleryview": 
					$script = "
						<script type=\"text/javascript\">
							jQuery(function() {
									
								panelH=300;
								panelW=460;
			
								filmH = 50;
								filmW = 50;
		
								jQuery('.gallery').galleryView({ 
										show_panels: true,
										show_filmstrip: true,
										panel_width: panelW,
										panel_height: panelH,
										frame_width: filmW,
										frame_height: filmH,
										frame_gap: 5,
										filmstrip_size: 8,
										filmstrip_position: 'bottom',
										transition_speed: 700,
										transition_interval: '0',
										nav_theme: 'light',
										frame_opacity: 0.5,
										easing: 'easeOutSine',
										pause_on_hover: true
								});
							});
						</script>
						<style type=\"text/css\">
						</style>
					";
		
					$content .= "<ul class=\"gallery\">";
					foreach ($result as $data) {
						$content .= "<li>";
						$content .= "<img src=\"http://".$_SERVER['HTTP_HOST']."/".$data->path."/thumbs/thumbs_".$data->filename."\" alt=\"image\" />";
						$content .= "<div class=\"panel-content\">";
						$content .= "<img src=\"http://".$_SERVER['HTTP_HOST']."/".$data->path."/".$data->filename."\" style=\"max-width: 480px; height: auto;\" />";				
						$content .= "</div>";
						$content .= "</li>";
					}
					$content .= "</ul>";
				break;
				case "contentprogallery":
					$script = "\n  <script type=\"text/javascript\">\n    jQuery(document).ready(function() {\n      jQuery('.contentprogallery').cpgallery();\n    });\n  </script>\n";
					
					$content .= "  <div class=\"contentprogallery\">\n    <h2>".$gallery->title."</h2>\n    <div class=\"wrapper\">\n      <ul>\n";
					$show = 0;
					$count = 1;
					foreach ($result as $data) {
						
						if($anzahl/2 < $count && $show == 0 && $anzahl > 1){
							$content .= "        <li>\n          <div class=\"panel\">\n";
							$content .= "<div style=\"padding: 30px 0px; background-color: #DDD; text-align: center; vertical-align: center;\">";
							$content .= $advert;				
							$content .= "</div>";
							$content .= "          </div>\n        </li>\n";
							$show = 1;							
						}						
						
						$content .= "        <li>\n          <div class=\"panel\">\n";

						//CONTENT
						$special = false;
						$width = 480;
						$height = 300;
						
						$suchbegriff = 'youtube'; 
						if(strstr($data->description,$suchbegriff)){
  						$youtube_url = parse_url($data->description);
							parse_str($youtube_url['query']);
							$content .= " 
							<object width=\"".$width."\" height=\"".$height."\">
								<param name=\"allowFullScreen\" value=\"true\"></param>
								<param name=\"allowscriptaccess\" value=\"always\"></param>								
								<param name=\"movie\" value=\"http://www.youtube.com/v/".$v."&hl=de_DE&fs=1&hd=1\"></param>
								<embed src=\"http://www.youtube.com/v/".$v."&hl=de_DE&fs=1&hd=1\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"".$width."\" height=\"".$height."\"></embed>
							</object>
							";
						}else if(strstr($data->description,'vimeo')){
							$vimeo_url = parse_url($data->description);
							$v = str_replace('/','',$vimeo_url['path']);
							$content .= " 
								<object width=\"".$width."\" height=\"".$height."\">
								<param name=\"allowfullscreen\" value=\"true\" />
								<param name=\"allowscriptaccess\" value=\"always\" />
								<param name=\"movie\" value=\"http://vimeo.com/moogaloop.swf?clip_id=".$v."&server=vimeo.com&show_title=1&show_byline=1&show_portrait=0&color=&fullscreen=1\" />
								<embed src=\"http://vimeo.com/moogaloop.swf?clip_id=".$v."&server=vimeo.com&show_title=1&show_byline=1&show_portrait=0&color=&fullscreen=1\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" allowscriptaccess=\"always\" width=\"".$width."\" height=\"".$height."\"></embed>
								</object>
							";
						}else if(strstr($data->description,'myvideo')){
							$vimeo_url = parse_url($data->description);
							$v = explode('/',$vimeo_url['path']);
							$content .= "
								<object width='".$width."' height='".$height."'>
								<param name='AllowFullscreen' value='true'></param>
								<param name='AllowScriptAccess' value='always'></param>
								<param name='movie' value='http://www.myvideo.de/movie/".$v[2]."'></param>
								<embed src='http://www.myvideo.de/movie/".$v[2]."' width='".$width."' height='".$height."' type='application/x-shockwave-flash' allowscriptaccess='always' allowfullscreen='true'></embed>
								</object>
							";
						}else if(strstr($data->description,'[media id=')){
							
							$content .= do_shortcode($data->description);
							
						}else{
							$content .= "            <img src=\"http://".$_SERVER['HTTP_HOST']."/".$data->path."/".$data->filename."\" class=\"image\" style=\"max-width: 480px; height: auto;\" />\n";
							$spezial = true;							
							if(!empty($data->alttext) || !empty($data->description)){
								$content .= "            <div class=\"description\">\n";
								if(!empty($data->alttext)) $content .= "              <h3>".$data->alttext."</h3>\n";
								if($spezial){
									$content .= $data->description;
								}
								$content .= "            </div>\n";	
							}		
						}						 
						 
					        	
					
						$content .= "          </div>\n        </li>\n";
						$count++;
					}
					$content .= "      </ul>\n    </div>\n  </div>\n";
					
														
				break;
				case "coda-slider": 
					$script = "
						<script type=\"text/javascript\">
							jQuery(document).ready(function() {
       					jQuery('#coda-slider-".$id."').codaSlider({
									dynamicArrows: false,
									dynamicTabs: false,
									sliderID: ".$id.",
								});
   						});
						</script>
					";
					
					$content .= "
						<div class=\"coda-slider-wrapper\">
							<div class=\"coda-slider preload\" id=\"coda-slider-".$id."\">
					";
					
					$show = 0;
					$count = 1;
					foreach ($result as $data) {
						if($anzahl/2 < $count && $show == 0 && $anzahl > 1){
							$content .= "<div class=\"panel\">";
							$content .= "<div class=\"panel-wrapper\" style=\"text-align: center\">";
							$content .= "<div style=\"margin: 30px auto;\">";
							$content .= $advert;				
							$content .= "</div>";
							$content .= "</div>";
							$content .= "</div>";
							$show = 1;							
						}
						$content .= "<div class=\"panel\">";
						$content .= "<div class=\"panel-wrapper\" style=\"text-align: center\">";
						$content .= "<div style=\"margin: 0 auto; position: relative;\">";
						
						
						
						//CONTENT
						$special = false;
						$width = 480;
						$height = 300;
						
						$suchbegriff = 'youtube'; 
						if(strstr($data->description,$suchbegriff)){
  						$youtube_url = parse_url($data->description);
							parse_str($youtube_url['query']);
							$content .= " 
							<object width=\"".$width."\" height=\"".$height."\">
								<param name=\"allowFullScreen\" value=\"true\"></param>
								<param name=\"allowscriptaccess\" value=\"always\"></param>								
								<param name=\"movie\" value=\"http://www.youtube.com/v/".$v."&hl=de_DE&fs=1&hd=1\"></param>
								<embed src=\"http://www.youtube.com/v/".$v."&hl=de_DE&fs=1&hd=1\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"".$width."\" height=\"".$height."\"></embed>
							</object>
							";
						}else if(strstr($data->description,'vimeo')){
							$vimeo_url = parse_url($data->description);
							$v = str_replace('/','',$vimeo_url['path']);
							$content .= " 
								<object width=\"".$width."\" height=\"".$height."\">
								<param name=\"allowfullscreen\" value=\"true\" />
								<param name=\"allowscriptaccess\" value=\"always\" />
								<param name=\"movie\" value=\"http://vimeo.com/moogaloop.swf?clip_id=".$v."&server=vimeo.com&show_title=1&show_byline=1&show_portrait=0&color=&fullscreen=1\" />
								<embed src=\"http://vimeo.com/moogaloop.swf?clip_id=".$v."&server=vimeo.com&show_title=1&show_byline=1&show_portrait=0&color=&fullscreen=1\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" allowscriptaccess=\"always\" width=\"".$width."\" height=\"".$height."\"></embed>
								</object>
							";
						}else if(strstr($data->description,'myvideo')){
							$vimeo_url = parse_url($data->description);
							$v = explode('/',$vimeo_url['path']);
							$content .= "
								<object width='".$width."' height='".$height."'>
								<param name='AllowFullscreen' value='true'></param>
								<param name='AllowScriptAccess' value='always'></param>
								<param name='movie' value='http://www.myvideo.de/movie/".$v[2]."'></param>
								<embed src='http://www.myvideo.de/movie/".$v[2]."' width='".$width."' height='".$height."' type='application/x-shockwave-flash' allowscriptaccess='always' allowfullscreen='true'></embed>
								</object>
							";
						}else if(strstr($data->description,'[media id=')){
							$content .= do_shortcode($data->description);
						}else{
							$content .= "<img src=\"http://".$_SERVER['HTTP_HOST']."/".$data->path."/".$data->filename."\" class=\"image\" style=\"max-width: 480px; height: auto;\" />";
							$spezial = true;
						}
						
						if(!empty($data->alttext) || !empty($data->description)){
							$content .= "<div class=\"image_description\">";
							if(!empty($data->alttext)) $content .= "<h2>".$data->alttext."</h2>";
							if($spezial){
								$content .= $data->description;
							}
							$content .= "</div>";	
						}
						
							
						$content .= "</div>";
						$content .= "</div>";
						$content .= "</div>";
						$count++;
					}
					$content .= "
							</div><!-- .coda-slider -->
							
								<div id=\"coda-nav-left-".$id."\" class=\"coda-nav-left\"><a href=\"#\" title=\"Slide left\">&#171;</a></div>
								<div id=\"coda-nav-".$id."\" class=\"coda-nav\"> 
									<ul>
						"; 
						if($anzahl > 1){ $anzahl++; } 
						for($i = 1;$i <= $anzahl; $i++){
						$content .= "<li><a class=\"xtrig\" rel=\"coda-slider-".$id."\" href=\"#$i\">$i</a></li>";
						}
						$content .= "
									</ul> 
								</div> 							
								<div id=\"coda-nav-right-".$id."\" class=\"coda-nav-right\"><a href=\"#\" title=\"Slide right\">&#187;</a></div>
							
							
   					</div><!-- .coda-slider-wrapper -->
					";
					
					$content .= "";
					
					
				break;
			}

			
			$return = $content . $script;
			return $return;
		}
		
		function _album_replace($atts){
			global $wpdb;
			
			extract(shortcode_atts(array('id' => 'all','template' => 'default'), $atts));
			$settings = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_".$this->table);
			foreach($settings as $key){
				$instance[(string)$key->name] = $key->value;
			}
			$positions = unserialize($instance['positions']);	
			$pageid = unserialize($instance['page']);		
			$show = unserialize($instance['show']);
			
			$sql = "SELECT gid,name,path,title,galdesc,pageid,previewpic,previewpic.filename,pictures.filename as filename_alternative,author,count(pictures.pid) as quantity
							FROM ".$wpdb->prefix."ngg_gallery as gallery
							LEFT JOIN ".$wpdb->prefix."ngg_pictures as pictures on ( gallery.gid = pictures.galleryid )
							LEFT JOIN ".$wpdb->prefix."ngg_pictures as previewpic on ( gallery.previewpic = previewpic.pid )
							GROUP BY gallery.gid ORDER BY gid DESC";
			$count = 0;
			$result = $wpdb->get_results($sql);
			
			foreach ($result as $data) {
				if($data->quantity >= 1){
					if($show[$data->gid] == "yes" || empty($show[$data->gid])) $dataset[$positions[$data->gid]][] = $data;
				}
			}	
			if(is_array($dataset)){
				
				krsort($dataset);	
				foreach ($dataset as $datas) {
					foreach($datas as $data){
						$results[] = $data;
					}
				}
			
				$content = "";	
				$content .= '<ul class="gallery_overview">';		
				foreach ($results as $data) {
					
					if($data->filename){ $filename = $data->filename;	} 
					else{	$filename = $data->filename_alternative; }
					if($data->galdesc){ $galdesc = $data->galdesc;	} 
					else{	$galdesc = ""; }
					
					$content .= '
											<li>
												<a href="'.get_page_link( $pageid[$data->gid] ).'">
												<div class="clearfix">
													<div class="image">
														<img src="http://'.$_SERVER['HTTP_HOST'].'/'.$data->path.'/'.$filename.'" />
													</div>
													<p>
														<h3>'.$data->title.'</h3>
														<font color: color="#333333">'.$galdesc.'</font><br>
														Anzahl Bilder: '.$data->quantity.'
													</p>
												</div>
												</a>
											</li>';
					
				}
				$content .= "</ul>";
				$return = $content;
			}
			
			return $return;
		}
		
	}
	
	
	class ContentProNGG_option extends ContentProOptions {
		
		var $name = "NGG";
		var $typ = "Plugin";
		var $classname = "ContentProNGG";
		var $table = "ContentProNGG";
		var $description = "Beschreibung des Plugins";
		
		var $table_columns = array(
			'name' => "VARCHAR(64) NOT NULL",
			'value' => "LONGTEXT NOT NULL"
		);
		
		public function update($new_instance){
			$instance['positions'] = serialize($new_instance['position']);
			$instance['page'] = serialize($new_instance['page']);
			$instance['show'] = serialize($new_instance['show']);
			$instance['display_top'] = stripslashes($new_instance['display_top']);
			$instance['display_gallery'] = stripslashes($new_instance['display_gallery']);
			$instance['display_advertisment'] = stripslashes($new_instance['display_advertisment']);
			$instance['display_style'] = stripslashes($new_instance['display_style']);
			return $instance;	
		}

		public function options(){
			global $wpdb,$ContentProAdvertisments;
			
			$positions = unserialize($this->_getOption('positions'));	
			$page = unserialize($this->_getOption('page'));		
			$show = unserialize($this->_getOption('show'));
			$advertisments = $ContentProAdvertisments->_get_Advertisments(); 
			
			$sql = "SELECT gid,name,path,title,galdesc,pageid,previewpic,previewpic.filename,pictures.filename as filename_alternative,author,count(pictures.pid) as quantity
							FROM ".$wpdb->prefix."ngg_gallery as gallery
							LEFT JOIN ".$wpdb->prefix."ngg_pictures as pictures on ( gallery.gid = pictures.galleryid )
							LEFT JOIN ".$wpdb->prefix."ngg_pictures as previewpic on ( gallery.previewpic = previewpic.pid )
							GROUP BY gallery.gid ORDER BY gid DESC";
			$count = 0;
			$result = $wpdb->get_results($sql);
			
			foreach ($result as $data) {
				$dataset[$positions[$data->gid]][] = $data;
			}	
			krsort($dataset);	
			foreach ($dataset as $datas) {
				foreach($datas as $data){
					$results[] = $data;
				}
			}				
		?>
    <table class="widefat">
    <thead>
    	<tr><th colspan="2">Display</th></tr>
    </thead>
    <tbody>
	    <tr>
      	<td width="150"><?php _e('Gallery on Top?'); ?></td>
        <td><select id="display_top" name="display_top" /><option value="true" <?php if($this->_getOption('display_top') == "true"): ?> selected<?php endif; ?>><?php _e('Yes') ?></option><option value="false" <?php if($this->_getOption('display_top') == "false"): ?> selected<?php endif; ?>><?php _e('No') ?></option></select></td>
      </tr>
      <tr>
      	<td width="150"><?php _e('Gallery'); ?></td>
        <td>
        	<select id="display_gallery" name="display_gallery" />
          <? foreach ($results as $data) { ?>
          	<option value="<?=$data->gid?>" <?php if($this->_getOption('display_gallery') == $data->gid): ?> selected<?php endif; ?>><?=$data->title?></option>
      		<? } ?>
          </select>
        </td>
      </tr>
      <tr>
      	<td width="150"><?php _e('Style'); ?></td>
        <td>
        	<select id="display_style" name="display_style" />
          	<option value="contentprogallery" <?php if($this->_getOption('display_style') == "contentprogallery"): ?> selected<?php endif; ?>>ContentPro Gallery</option>
          </select>
        </td>
      </tr>
		</tbody>
    </table>
    
    <br />

    <table class="widefat">
    <thead>
    	<tr><th colspan="2"><?php _e('Advertisments'); ?></th></tr>
    </thead>
    <tbody>
    	<tr>
      	<td width="150"><?php _e('Advertisment'); ?></td>
        <td>
        	<select id="display_advertisment" name="display_advertisment" />
          <? foreach ($advertisments as $ad) { ?>
          	<option value="<?=$ad->id?>" <?php if($this->_getOption('display_advertisment') == $ad->id): ?> selected<?php endif; ?>><?=$ad->name?></option>
      		<? } ?>
          </select>
        </td>
      </tr>
		</tbody>
    </table>      
    
   	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>

		<table class="widefat">
    <thead>
		<tr>
			<th></th>
			<th><?=__("Title")?></th>
			<th><?=__("Page ID")?></th>
			<th><?=__("Quantity")?></th>
      <th><?=__("Show")?></th>
      <th><?=__("Position")?></th>
		</tr>
    </thead>
    <tbody>
    <?
			$count = 0;
			foreach ($results as $data) { 
			
				if($data->filename){ $filename = $data->filename;	} 
				else{	$filename = $data->filename_alternative; }
				if($data->galdesc){ $galdesc = $data->galdesc;	} 
				else{	$galdesc = "the description is missing"; }
				
				if($count % 2 != 0){ $bar = "alternate"; }else{ $bar = ""; }
		?>
    <tr class="<?=$bar?>">
    	<td width="30"><img src="http://<?=$_SERVER['HTTP_HOST']?>/<?=$data->path?>/thumbs/thumbs_<?=$filename?>" height="35" width="35" /></td>
    	<td>
      	<a href="?page=nggallery-manage-gallery&mode=edit&gid=<?=$data->gid?>" class="row-title"><?=$data->title?></a><br />
				<?=$galdesc?>
      </td>
    	<td><input name="page[<?=$data->gid?>]" type="text" size="6" value="<?=$page[$data->gid]?>" />
      	<!--<a href="page.php?action=edit&post=<?=$data->pageid?>"><?php echo get_the_title( $data->pageid ); ?></a> (<?=$data->pageid?>)<br />-->
				<!--<a href="page.php?action=edit&post=<?=$data->pageid?>">Edit</a> | <a href="<?php echo get_page_link( $data->pageid ); ?>" target="_blank">Preview</a>-->
      </td>
    	<td>
      	<?=$data->quantity?>
      </td>
      <td width="60">
      	<select name="show[<?=$data->gid?>]" />
        	<option value="yes" <?php if($show[$data->gid] == "yes"): ?> selected<?php endif; ?>><?=__("Yes")?></option>
          <option value="no" <?php if($show[$data->gid] == "no"): ?> selected<?php endif; ?>><?=__("No")?></option>
        </select>
      </td>
      <td width="80"><input name="position[<?=$data->gid?>]" type="text" size="6" value="<?=$positions[$data->gid]?>" /></td>
    </tr>	
    <? 
				$count++;
			}
		?>
		</tbody>
    </table>
    
    <input name="action" id="action" value="update" type="hidden">
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>

   <?
		}
	}
	
	global $ContentProNGG;
	$ContentProNGG = new ContentProNGG();

	
?>