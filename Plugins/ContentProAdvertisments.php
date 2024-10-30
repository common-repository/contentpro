<?php

	class ContentProAdvertisments {
		
		var $name = "Advertisments";
		var $typ = "Plugin";
		var $classname = "ContentProAdvertisments";
		var $table = "ContentProAdvertisments";
		var $description = 'This plugin allows you to predefine advertisments and display them within an article or a page. You can also display the advertisments in your Sidebar with the related widget <a href="widgets.php">ContentPro Advertisments</a>.';
	
		function __construct(){
			add_action('init', array(&$this,'_applyShortCodes'));
			add_action('admin_menu', array('ContentProAdvertisments','_createBox'));
		}
		
		function _get_Advertisments(){
			global $wpdb;
			$advertisments = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_ContentProAdvertisments ORDER BY position");
			return $advertisments;
		}
			
		function _displayShortCode($atts, $content=null, $code=""){
			global $wpdb;
			$result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_ContentProAdvertisments where replacement = '".$code."'");
			if(is_array($result)){	
				foreach ($result as $data) {
					return '<div class="contentproadvertisments '.$data->align.'">'.stripslashes($data->code).'</div>';
				}
			}		
		}
		
		function _applyShortCodes(){
			global $wpdb;
			$result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_ContentProAdvertisments");
			if(is_array($result)){	
				foreach ($result as $data) {
					add_shortcode($data->replacement, array(&$this,'_displayShortCode'));
				}
			}		
		}
		
		function _createBox(){
			if(function_exists('add_meta_box')){  
				add_meta_box( 'contentpro_advertismentbox', 'ContentPro Advertisments', array('ContentProAdvertisments', '_applyBox'), 'post', 'normal', 'high' );  
				add_meta_box( 'contentpro_advertismentbox', 'ContentPro Advertisments', array('ContentProAdvertisments', '_applyBox'), 'page', 'normal', 'high' );  
			}
		}
		
		function _applyBox() {  
			global $post,$wpdb;
			
			$result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_ContentProAdvertisments ORDER BY position");
			if(is_array($result)){	
			?>
			<p><strong>Shortcodes</strong></p>
			<?
				foreach ($result as $data) {
			?>
      <p>
      [<?=$data->replacement?>] - <?=$data->name?>
      </p>
     	<?					
				}

			}else{ ?>
      <p>
      	Es wurden keine Advertisments angelegt.<br>
      </p>      
      <?php } ?>
      <div style="height: 1px; background-color: #CCCCCC;"></div>
      
      <p>
        <a href="admin.php?page=ContentProAdvertisments">Advertisment Einstellungen</a><br />
      	<a href="admin.php?page=ContentProAdvertisments&action=new">Neues Advertisment hinzuf&uuml;gen</a><br>
      </p>			
<?
		} 

	}


	class ContentProAdvertisments_option extends ContentProOptions {

		var $name = "Advertisments";
		var $typ = "Plugin";
		var $classname = "ContentProAdvertisments";
		var $table = "ContentProAdvertisments";
		var $description = 'This plugin allows you to predefine advertisments and display them within an article or a page. You can also display the advertisments in your Sidebar with the related widget <a href="widgets.php">ContentPro Advertisments</a>.';
	
		var $table_columns = array(
			'name' => "VARCHAR(64) NOT NULL",
			'value' => "LONGTEXT NOT NULL",
			'replacement' => "VARCHAR(20) NOT NULL",
			'typ' => "VARCHAR(20) NOT NULL",
			'align' => "VARCHAR(20) NOT NULL",
			'description' => "LONGTEXT NOT NULL",
			'code' => "LONGTEXT NOT NULL",
			'std' => "LONGTEXT NOT NULL",
			'position' => "MEDIUMINT(10) NOT NULL"
		);
		
		public function update($new_instance){
			$instance = $new_instance;
			return $instance;	
		}

		public function options(){
			global $wpdb;
			
			if($_REQUEST['action'] == 'trash' && is_numeric($_REQUEST['id'])){
				$wpdb->query("DELETE FROM ".$wpdb->prefix."contentpro_".$this->table." WHERE `id` = ".$_REQUEST['id']);
			}
		
			if($_REQUEST['action'] == 'edit' || $_REQUEST['action'] == 'new'){
				
				if(is_numeric($_REQUEST['id'])){
					$data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contentpro_".$this->table." WHERE `id` = ".$_REQUEST['id']);
				}else{
					$data->id = "New";
				}			
?>
    <br style="clear" />
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?=$this->classname?>" class="button"><?=__("&laquo; Back")?></a>	
    
		<p>
		<table class="widefat">
		<thead>
		<tr>
			<th colspan="2">Advertisment: <?=$data->id?></th>
		</tr>
		</thead>
		<tr>
			<th scope="row" width="200">Name</th>
			<td><input id="name" name="name" type="text" value="<?=$data->name?>" /></td>
		</tr>    
    <tr>
			<th scope="row" width="200"><?=__("Shortcode")?></th>
			<td><input id="replacement" name="replacement" type="text" value="<?=stripslashes($data->replacement)?>" /></td>
		</tr>
    <tr>
			<th scope="row" width="200"><?=__("Align")?></th>
			<td>
				<select id="align" name="align" />
				<option value="alignleft" <?php if($data->align == "alignleft"): ?> selected<?php endif; ?>><?=__('Left');?></option>
				<option value="aligncenter" <?php if($data->align == "aligncenter"): ?> selected<?php endif; ?>><?=__('Center');?></option>
				<option value="alignright" <?php if($data->align == "alignright"): ?> selected<?php endif; ?>><?=__('Right');?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row" width="200">Code</th>
			<td><textarea class="widefat" id="code" name="code" rows="10"><?=stripslashes($data->code)?></textarea></td>
		</tr>

		<tr>
			<th scope="row" width="200"><?=__("Position")?></th>
			<td><input id="position" name="position" type="text" value="<?=$data->position?>" /></td>
		</tr>
		</table>		
		</p>
    
		<input type="hidden" name="id" value="<?=$data->id?>" />
    <input name="action" id="action" value="update" type="hidden">
    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
    
		<?	
			}else{ 
		?>
    <br style="clear" />
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?=$this->classname?>&action=new" class="button"><?=__("Add New")?></a>
		<p>
		<table class="widefat">
    <thead>
		<tr>
			<th>Name</th>
			<th><?=__("Align")?></th>
			<th><?=__("Shortcode")?></th>
			<th><?=__("Preview")?></th>
			<th></th>
		</tr>
    </thead>
    <tbody>
    <? 			
			$result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_".$this->table." ORDER BY position");
			foreach ($result as $data) { 
		?>
		<tr>
			<td class="post-title column-title"><strong><a class="row-title" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?=$this->classname?>&action=edit&id=<?=$data->id?>"><?=$data->name?></a></strong></td>
			<td>
			<?
				switch($data->align):
					case "alignleft":
						echo __('Left');
					break;
					case "alignright";
						echo __('Right');
					break;
					default:
						echo __('Center');
					break;				
				endswitch;
			?>
			</td>
			<td><? if($data->replacement != ""): ?>[<?=$data->replacement?>]<? endif; ?></td>
			<td><?=stripslashes($data->code)?></td>
			<td><a class="row-title" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?=$this->classname?>&action=trash&id=<?=$data->id?>"><?=__("Delete")?></a></td>
		</tr>
		<? } ?>
    </tbody>
    </table>
    </p>
    <?php } ?>			
<?php		
		}
	}
	
	global $ContentProAdvertisments;
	$ContentProAdvertisments = new ContentProAdvertisments();

?>