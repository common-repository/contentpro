<?
	/*
		Plugin Name: Content Pro
		Plugin URI: http://www.bk2k.info
		Description: Content Management Plugin, Custom MetaBoxes, Advertisments, Facebook, Amazon MP3
		Version: 0.1
		Author: Benjamin Kott
		Author URI: http://www.bk2k.info
		Min WP Version: 2.8
		
	*/
	
	
	function array_push_after($src,$in,$pos){
		if(is_int($pos)) $R=array_merge(array_slice($src,0,$pos+1), $in, array_slice($src,$pos+1));
	  	else{
					foreach($src as $k=>$v){
							$R[$k]=$v;
							if($k==$pos)$R=array_merge($R,$in);
					}
			}return $R;
	}
	
	
	function cp_sort(&$array, $subkey="id", $sort_ascending=false) {
	
		if(count($array)) $temp_array[key($array)] = array_shift($array);
		foreach($array as $key => $val):
		
			$offset = 0;
			$found = false;
			foreach($temp_array as $tmp_key => $tmp_val):
				
				if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])):
					$temp_array = array_merge((array)array_slice($temp_array,0,$offset),array($key => $val),array_slice($temp_array,$offset));
					$found = true;
				endif;
				$offset++;
				
			endforeach;
			if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
			
		endforeach;
	
		if ($sort_ascending) $array = array_reverse($temp_array);
		else $array = $temp_array;
	}

	function cp_getResults($table = false,$order = false){
		global $wpdb;
		if(isset($table)):
			if(isset($order)) $order = " ORDER BY ".$order;
			$query = "SELECT * FROM ".$wpdb->prefix."contentpro_".$table . $order;
			$results = $wpdb->get_results($query,OBJECT);
			return $results;
		endif;
	}
	
	function cp_getOption($option = false,$table){
		global $wpdb;
		if(isset($option)):
			$query = "SELECT value FROM ".$wpdb->prefix."contentpro_".$table." where name = '".$option."'";
			$value = $wpdb->get_var($query);
			return $value;
		endif;
	}
	
	class ContentProOptions {
		
		function __construct(){		
			$this->_install();
		}
		
		function _install(){
			global $wpdb;
			ContentPro::table_add("contentpro_".$this->table);
			foreach($this->table_columns as $key => $value){
				ContentPro::table_add_column($wpdb->prefix."contentpro_".$this->table,$key,$value." AFTER id");
			}			
		}
		
		function _update($instance){
			$instance = $this->update($instance);
			if(!isset($instance['id'])){
				foreach($instance as $key => $value){
					$this->_updateOption($key,$value);
				}
			}else{
					$this->_updateRow($instance);
			}
		}
		
		function _updateOption($option = false,$value = ""){
			global $wpdb;
			$table = $wpdb->prefix."contentpro_".$this->table;
			//$wpdb->show_errors();
			if(isset($option)):
				$query = "SELECT * FROM ".$table." where name = '".$option."'";
				$exists = $wpdb->get_row($query);
				if(isset($exists)){
					$wpdb->update( $table, array('value' => $value), array( 'name' => $option ));
				}else{
					$wpdb->insert( $table, array( 'name' => $option ,'value' => $value ));
				}
			endif;			
      //$wpdb->hide_errors();
			//$wpdb->print_error();
		}
		
		function _updateRow($instance = false){
			global $wpdb;
			$table = $wpdb->prefix."contentpro_".$this->table;
			$wpdb->show_errors();
			if(isset($instance)):
				if(is_numeric($_REQUEST['id'])){
					$id = $instance['id'];
					unset($instance['id']);
					unset($instance['action']);
					$wpdb->update( $table, $instance, array( 'id' => $id ));
				}else{
					unset($instance['id']);
					unset($instance['action']);
					$wpdb->insert( $table, $instance );
				}
			endif;
			$wpdb->hide_errors();
			$wpdb->print_error();
		}
		
		function _getOption($option = false){
			global $wpdb;
			if(isset($option)):
				$query = "SELECT value FROM ".$wpdb->prefix."contentpro_".$this->table." where name = '".$option."'";
				$value = $wpdb->get_var($query);
				return $value; 
			endif;
		}
		
	}



	class ContentPro {
	
		var $plugin_name = 'ContentPro';
    var $plugin_url  = '';
		var $plugin_dir  = '';

		var $pluggable = array();

		function __construct() {
			global $wpdb;
			
			$this->plugin_name =  plugin_basename( dirname(__FILE__) );
    	$this->plugin_url  = WP_PLUGIN_URL . '/' . ltrim( $this->plugin_name, '/' ) . '/';
			$this->plugin_dir  = WP_PLUGIN_DIR . '/' . ltrim( $this->plugin_name, '/' ) . '/';
			$wpdb->ContentPro = $wpdb->prefix . 'contentpro';
			
			register_activation_hook( plugin_basename( __FILE__ ), array('ContentPro', 'activate') );
			register_deactivation_hook( plugin_basename( __FILE__ ), array('ContentPro', 'uninstall') );
			
			$this->find_pluggable('Widgets');			
			$this->find_pluggable('Plugins');			

			add_action('template_redirect', array(&$this, 'load_scripts') );
			add_action('plugins_loaded',array(&$this,'start'));

		}

		function load_scripts(){
			
			wp_enqueue_style('jquery-ui-bk2k', $this->plugin_url.'Javascript/jQueryUI/css/custom-theme/jquery-ui-1.8rc3.custom.css' , false, '1.8rc3', 'screen');
			
			wp_deregister_script('jquery');
			wp_register_script('jquery',$this->plugin_url.'Javascript/jQueryUI/js/jquery-1.4.2.min.js', false, '1.4.2.min');
			wp_enqueue_script('jquery');
			
			wp_deregister_script('jquery-ui');
			wp_register_script('jquery-ui',$this->plugin_url.'Javascript/jQueryUI/js/jquery-ui-1.8rc3.custom.min.js', 'jquery', '1.8rc3');
			wp_enqueue_script('jquery-ui');
			
			wp_register_script('contentpro',$this->plugin_url.'Javascript/ContentPro.js', 'flase', '1.1');
			wp_enqueue_script('contentpro');
		}
		
		function find_pluggable($what){
			
			if ($handle = opendir($this->plugin_dir . $what)):
				while(false !== ($file = readdir($handle))) {
					if($file != "." && $file != ".."):
						$path_info = pathinfo($this->plugin_dir . $what.'/'.$file);
						if($path_info['extension'] == "php"):
							$this->pluggable[$what][] = $path_info['filename'];
							endif;
					endif;
				}
				closedir($handle);
			endif;
		}
		
		function start(){
			$this->applyPluggable('Plugins');
			$this->applyPluggable('Widgets');
			$this->applyOptions();
		}
		
		function activate() {
			global $wpdb;
			ContentPro::table_add("contentpro");
			ContentPro::table_add_column($wpdb->prefix."contentpro","typ","MEDIUMTEXT NULL AFTER id");
			ContentPro::table_add_column($wpdb->prefix."contentpro","name","MEDIUMTEXT NULL AFTER id");
		}


		function uninstall() {
			global $wpdb;
			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}contentpro");
		}
		

		function table_add($table_name){
			global $wpdb;
			$charset_collate = '';
			if(version_compare(mysql_get_server_info(),'4.1.0','>=')){
				if(!empty($wpdb->charset))	$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				if(!empty($wpdb->collate))	$charset_collate .= " COLLATE $wpdb->collate";
			}
			$wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}".$table_name." (
										id MEDIUMINT(10) NOT NULL AUTO_INCREMENT,
										PRIMARY KEY (id)
										) $charset_collate;" );
		}
		
		
		function table_add_column($table_name, $column_name, $create_ddl) {
			global $wpdb;
			foreach ($wpdb->get_col("SHOW COLUMNS FROM $table_name") as $column){
				if($column == $column_name) return true;
			}
			$wpdb->query("ALTER TABLE $table_name ADD $column_name ".$create_ddl);
			foreach ($wpdb->get_col("SHOW COLUMNS FROM $table_name") as $column){
				if($column == $column_name) return true;
			}
			return false;
		}
		
		function __set($var,$val){
			$this->pluggable[$var] = $val;
		}
		function __get($var){
			return $this->pluggable[$var];
		}
		
		
		// CREATE OPTIONS
		function applyPluggable($what = false){
			
			if(isset($what)):
				
				sort($this->pluggable[$what]);
			
				foreach($this->pluggable[$what] as $plugin){	
					include($what.'/'.$plugin.'.php');
					if($what == "Widgets") add_action('widgets_init', create_function('', 'return register_widget("'.$plugin.'");') );
					
					if(class_exists($plugin.'_option')):
						$classname = $plugin."_option";
						$option =  new $classname;
						$this->pluggable['options'][] = $option;
					endif;
				}
			endif;
			
		}
		
		function applyOptions(){
			add_action('admin_menu', create_function("", "return add_object_page('ContentPro Options','ContentPro', 'administrator', 'ContentPro', array('ContentPro','contentpro_overview') ,'".$this->plugin_url."/Images/icon.png' );"));
			foreach($this->pluggable['options'] as $option){
				add_action('admin_menu', create_function("", "return add_submenu_page('ContentPro', 'ContentPro Options: ".(string)$option->name."', '".(string)$option->typ.": ".(string)$option->name."', 'administrator','".$option->classname."',array( 'ContentPro','load_options' ));"));
			}
		}
		
		function contentpro_overview(){
			global $ContentPro;
?>
		<div class="wrap">
      <div style="background-image: url('<?php echo WP_PLUGIN_URL.'/ContentPro/Images/icon32.png'; ?>'); background-position: center; background-repeat:no-repeat;" class="icon32"><br /></div>
			<h2>ContentPro</h2>
			<p>Beschreibung</p>
      
			<div style="width: 380px; float:left; margin-right: 20px;">
      <h3>Plugins</h3>
			<? 
				foreach($ContentPro->pluggable['Plugins'] as $plugin){ 
				$object = new $plugin;
			?>
        <table class="widefat">
        <thead>
          <tr><th><?=$object->name?></th></tr>
        </thead>
        <tbody>
          <tr><td><?=$object->description?></td></tr>
          <? if(class_exists($object->classname.'_option')): ?>
          <tr><td><a href="admin.php?page=<?=$object->classname?>"><?=__('Settings');?></a></td></tr>
          <? endif; ?>
        </tbody>
        </table>
        <br />
      <? 
				unset($object);
				} 
			?>
      </div>
     	
      <div style="width: 380px; float:left;">
      <h3>Widgets</h3>
			<? 
				foreach($ContentPro->pluggable['Widgets'] as $widget){ 
				$object = new $widget;
			?>
        <table class="widefat">
        <thead>
          <tr><th><?=$object->name?></th></tr>
        </thead>
        <tbody>
          <tr><td><?=$object->description?></td></tr>
          <? if(class_exists($object->classname.'_option')): ?>
          <tr><td><a href="admin.php?page=<?=$object->classname?>"><?=__('Settings');?></a></td></tr>
          <? else: ?>
	        <tr><td>This widget does not have separate settings.</td></tr>
          <? endif; ?>
        </tbody>
        </table>
        <br />
      <? 
				unset($object);
				} 
			?>
      </div>

		</div>
<?			
		}
		
		function load_options(){
			$classname = $_GET['page'].'_option';
			$object = new $classname;
			
			if($_POST['action'] == "update"){
				$object->_update($_POST);
			}
?>
	<div class="wrap">
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $object->classname; ?>">
      
    	<div style="background-image: url('<?php echo WP_PLUGIN_URL.'/ContentPro/Images/icon32.png'; ?>'); background-position: center; background-repeat:no-repeat;" class="icon32"><br /></div>
		<h2><?php echo $object->typ; ?>: <?php echo $object->name; ?></h2>
    	<p><?php echo $object->description; ?></p>
      	<? $object->options(); ?>
		
   	</form>
    </div>
<?
		}
	}
	
	global $ContentPro;
	$ContentPro = new ContentPro();
	
?>