<?
	class ContentProMetaBox {

		var $name = "MetaBox";
		var $typ = "Plugin";
		var $classname = "ContentProMetaBox";
		var $table = "ContentProMetaBox";
		var $description = "Beschreibung des Plugins";
		
		function __construct(){
				add_action('admin_menu', array('ContentProMetaBox','_createBoxes'));
				add_action('save_post', array('ContentProMetaBox','_save'));
		}

		function _createBoxes(){
			if(function_exists('add_meta_box')){
				
				$args=array(
					'public'   => true,
					'_builtin' => false
				); 
				$output = 'names'; // names or objects
				$operator = 'and'; // 'and' or 'or'
				$post_types=get_post_types($args,$output,$operator); 
				foreach ($post_types as $post_type ) {
					add_meta_box( 'contentpro_metaboxes', 'ContentPro Post Settings', array('ContentProMetaBox', '_applyMetaBoxes'), $post_type, 'normal', 'high' );  
				}
				
				add_meta_box( 'contentpro_metaboxes', 'ContentPro Post Settings', array('ContentProMetaBox', '_applyMetaBoxes'), 'post', 'normal', 'high' );  
				add_meta_box( 'contentpro_metaboxes', 'ContentPro Post Settings', array('ContentProMetaBox', '_applyMetaBoxes'), 'page', 'normal', 'high' );  
			}
		}
		
		function _applyMetaBoxes() {  
			global $post;
			
			$metaboxes = cp_getResults('ContentProMetaBox','position');
			if(is_array($metaboxes)){
				foreach($metaboxes as $metabox) {
					
					$metabox->post_type = unserialize($metabox->post_type);
					$post_type = get_post_type($post->ID);
					if($metabox->post_type[$post_type] == "on"):
					
					$metabox_value = get_post_meta($post->ID, $metabox->value, true); 
					if($metabox_value == "") $metabox_value = $metabox->std;  
			?>
					<p>
					<input type="hidden" name="<?=$metabox->value?>_noncename" id="<?=$metabox->value?>_noncename" value="<?=wp_create_nonce(plugin_basename(__FILE__))?>" />
					<label for="<?=$metabox->value?>"><strong><?=$metabox->name?></strong>: 
			<?	
					switch ($metabox->typ) {
						case 'posts':
						  echo '<select name="'.$metabox->value.'" class="widefat">';
							global $post;
							$tmp_post = $post;
							$parent_posts = get_posts('post_type='.$metabox->std);
							foreach($parent_posts as $post) :
							  setup_postdata($post);
            		echo '<option value="'.$post->ID.'" '; 
								if($metabox_value ==  $post->ID){ echo "selected"; }
								echo '>'.get_the_title().' ('.$post->ID.')</option>';
							endforeach;
							$post = $tmp_post; 
							echo '</select>';
						break;
						case 'select':
							echo '<select name="'.$metabox->value.'" class="widefat">';
							echo '<option value="yes"'; if($metabox_value == "yes"){ echo 'selected'; } echo '>Yes</option>';
							echo '<option value="no"';	if($metabox_value == "no"){  echo 'selected'; } echo '>No</option>';
							echo '</select>';
						break;
						case 'textarea':
							echo '<textarea name="'.$metabox->value.'" class="widefat" rows="8">'.$metabox_value.'</textarea>';
						break;
						default:
							echo '<input type="text" name="'.$metabox->value.'" value="'.$metabox_value.'" class="widefat" />';  
						break;
					}
			?>
      		<br />
          <em><?=$metabox->description?></em>
          </label>
					</p>
			<?
					endif;
      	}
			}else{
			?>
			<p>
				Es wurden keine MetaBoxen gefunden.
			</p>	
			<? } ?>
			
      <div style="height: 1px; background-color: #CCCCCC;"></div>
      
      <p>
      <a href="admin.php?page=ContentProMetaBox">MetaBox Einstellungen</a><br />
      <a href="admin.php?page=ContentProMetaBox&action=new">Neue Post-Settings hinzuf&uuml;gen</a>
      </p>
      
			<?
		}  

		function _save($post_id) {  
			global $post;  
			
			$metaboxes = cp_getResults('ContentProMetaBox','position');
			
			if(is_array($metaboxes)):
			
				foreach($metaboxes as $metabox){
				
					$metabox->post_type = unserialize($metabox->post_type);
					$post_type = get_post_type($post->ID);
					if($metabox->post_type[$post_type] == "on"):
				
						// Verify  
						if(!wp_verify_nonce($_POST[$metabox->value.'_noncename'],plugin_basename(__FILE__))){  
							return $post_id;  
						}  
							 
						if('page' == $_POST['post_type']){  
							if(!current_user_can('edit_page',$post_id))  
							return $post_id;  
						}else{  
							if(!current_user_can('edit_post',$post_id))  
							return $post_id;  
						}
						
						$data = $_POST[$metabox->value];  
							
						if(get_post_meta($post_id, $metabox->value) == "")  
							add_post_meta($post_id, $metabox->value, $data, true);  
						elseif($data != get_post_meta($post_id, $metabox->value, true))  
							update_post_meta($post_id, $metabox->value, $data);  
						elseif($data == "")  
							delete_post_meta($post_id, $metabox->value, get_post_meta($post_id, $metabox->value, true));
							
					endif;
				} 
				
			endif;
		} 

	}
	
	
	class ContentProMetaBox_option extends ContentProOptions {
		
		var $name = "MetaBox";
		var $typ = "Plugin";
		var $classname = "ContentProMetaBox";
		var $table = "ContentProMetaBox";
		var $description = "Beschreibung des Plugins";
		
		var $table_columns = array(
			'name' => "VARCHAR(64) NOT NULL",
			'value' => "LONGTEXT NOT NULL",
			'replacement' => "VARCHAR(20) NOT NULL",
			'typ' => "VARCHAR(20) NOT NULL",
			'description' => "LONGTEXT NOT NULL",
			'std' => "LONGTEXT NOT NULL",
			'position' => "MEDIUMINT(10) NOT NULL",
			'post_type' => "LONGTEXT NOT NULL"
		);
		
		public function update($new_instance){
			$new_instance['post_type'] = serialize($new_instance['post_type']);
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
					$meta = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contentpro_".$this->table." WHERE `id` = ".$_REQUEST['id']);
				}else{
					$meta->id = "New";
				}
				$meta->post_type = unserialize($meta->post_type);
				
		?>
    <br style="clear" />
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?=$this->classname?>" class="button"><?=__("&laquo; Back")?></a>	
    
		<p>
		<table class="widefat">
		<thead>
		<tr>
			<th colspan="2">Meta Box: <?=$meta->id?></th>
		</tr>
		</thead>
    <tr>
			<th scope="row" width="200"><?=__("Post Type")?></th>
			<td>
      	<ul>
        <li><input type="checkbox" name="post_type[page]" <?php if($meta->post_type['page'] == "on") echo "checked "; ?>/> Page</li>
        <li><input type="checkbox" name="post_type[post]" <?php if($meta->post_type['post'] == "on") echo "checked "; ?>/> Post</li>
        <?php
					$args=array(
						'public'   => true,
						'_builtin' => false
					); 
					$output = 'names'; // names or objects
					$operator = 'and'; // 'and' or 'or'
					$post_types=get_post_types($args,$output,$operator); 
					foreach ($post_types as $post_type ) {
				?>
        <li><input type="checkbox" name="post_type[<?=$post_type?>]" <?php if($meta->post_type[$post_type] == "on") echo "checked "; ?>/> <?=$post_type?></li>
        <? } ?>
        
        
        </ul>
      </td>
		</tr>
		<tr>
			<th scope="row" width="200">Name</th>
			<td><input class="widefat" id="name" name="name" type="text" value="<?=$meta->name?>" /></td>
		</tr>
		<tr>
			<th scope="row" width="200"><?=__("Description")?></th>
			<td><input class="widefat" id="description" name="description" type="text" value="<?=stripslashes($meta->description)?>" /></td>
		</tr>
		<tr>
			<th scope="row" width="200">Variable</th>
			<td><input class="widefat" id="value" name="value" type="text" value="<?=$meta->value?>" /></td>
		</tr>
    <tr>
			<th scope="row" width="200"><?=__("Replacement")?></th>
			<td><input class="widefat" id="replacement" name="replacement" type="text" value="<?=stripslashes($meta->replacement)?>" /></td>
		</tr>
		<tr>
			<th scope="row" width="200"><?=__("Type")?></th>
			<td>
				<select class="widefat" id="typ" name="typ" />
				<option value="text" <?php if($meta->typ == "text"): ?> selected<?php endif; ?>>Text</option>
				<option value="posts" <?php if($meta->typ == "posts"): ?> selected<?php endif; ?>>Posts</option>
				<option value="textarea" <?php if($meta->typ == "textarea"): ?> selected<?php endif; ?>>Textarea</option>
				<option value="select" <?php if($meta->typ == "select"): ?> selected<?php endif; ?>>Select (Yes/No)</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row" width="200"><?=__("Default")?></th>
			<td><input class="widefat" id="std" name="std" type="text" value="<?=$meta->std?>" /></td>
		</tr>
		<tr>
			<th scope="row" width="200"><?=__("Position")?></th>
			<td><input class="widefat" id="position" name="position" type="text" value="<?=$meta->position?>" /></td>
		</tr>
		</table>		
		</p>
    
		<input type="hidden" name="id" value="<?=$meta->id?>" />
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
			<th><?=__("Description")?></th>
			<th>Variable</th>
      <th>Replacement</th>
			<th><?=__("Type")?></th>
			<th><?=__("Default")?></th>
			<th><?=__("Position")?></th>
			<th></th>
		</tr>
    </thead>
    <tbody>
    <? 			
			$metaboxes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."contentpro_".$this->table." ORDER BY position");
			foreach ($metaboxes as $meta) { 
		?>
		<tr>
			<td class="post-title column-title"><strong><a class="row-title" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?=$this->classname?>&action=edit&id=<?=$meta->id?>"><?=$meta->name?></a></strong></td>
			<td><?=stripslashes($meta->description)?></td>
			<td><?=$meta->value?></td>
      <td><? if($meta->replacement != ""): ?><strong>{<?=$meta->replacement?>}</strong><? endif; ?></td>
			<td><?=$meta->typ?></td>
			<td><?=$meta->std?></td>
			<td><?=$meta->position?></td>
			<td><a class="row-title" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?=$this->classname?>&action=trash&id=<?=$meta->id?>"><?=__("Delete")?></a></td>
		</tr>
		<? } ?>
    </tbody>
    </table>
    </p>
    <?php } ?>
<?	
		}
	}
	
	global $ContentProMetaBox;
	$ContentProMetaBox = new ContentProMetaBox();

	
?>
