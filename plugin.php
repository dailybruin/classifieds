<?php
/*
Plugin Name: The Daily Bruin Classifieds Importer
Plugin URI: http://www.dailybruin.com
Description: Import Daily Bruin classified ads exported from AdPro in XML.
Version: 0.5
Author: Kiran Sonnad
Author URI: http://www.ksonnad.com
Author Email: ksonnad@gmail.com
License:

  Copyright 2012 TODO (online@media.ucla.edu)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

class db_classifieds {
	 
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	// Initializes the plugin by setting localization, filters, and administration functions.
	function __construct() {
	
		// TODO: replace "db_classifieds-locale" with a unique value for your plugin
		//load_plugin_textdomain( 'db_classifieds-locale', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		
		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( &$this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'register_admin_scripts' ) );
	
		// Register site styles and scripts
		add_action( 'wp_print_styles', array( &$this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_plugin_scripts' ) );
		
		// Register admin menu
		add_action( 'admin_menu', array( &$this, 'db_classifieds_menu' ));

		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
		  
	    // Register AJAX hooks
		add_action('wp_ajax_insert_posts', array( &$this, 'insert_posts'));
	    
	    // Allow XML uploads
	    add_filter( 'upload_mimes', array( &$this, 'addUploadXML' )	);
	    	function addUploadXML($mimes) {
	   			$mimes = array_merge($mimes, array('xml' => 'text/xml'));
	    		return $mimes;
			}

	    // Initiate PHP Session
	    add_action('init','init_sessions');
			function init_sessions() {
				if (!session_id()) {
					session_start();
				}
			}

		

	} // end constructor
	

	// Fired when the plugin is activated.
	// @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	function activate( $network_wide ) {
		// TODO define activation functionality here
		
	}
	

	// Fired when the plugin is deactivated.
	// @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	function deactivate( $network_wide ) {
		// TODO define deactivation functionality here	

	}
	

	// Registers and enqueues admin-specific styles.
	public function register_admin_styles() {
	
		wp_register_style( 'db_classifieds-admin-styles', plugins_url( 'db_classifieds/css/admin.css' ) );
		wp_enqueue_style( 'db_classifieds-admin-styles' );
	
	}


	// Registers and enqueues admin-specific JavaScript.	
	public function register_admin_scripts() {
	
		wp_register_script( 'db_classifieds-admin-script', plugins_url( 'db_classifieds/js/admin.js' ) );
		wp_enqueue_script( 'db_classifieds-admin-script' );
	
	}


	// Registers and enqueues plugin-specific styles.
	// public function register_plugin_styles() {

	// 	wp_register_style( 'db_classifieds-plugin-styles', plugins_url( 'db_classifieds/css/display.css' ) );
	// 	wp_enqueue_style( 'db_classifieds-plugin-styles' );
	
	// }
	

	// // Registers and enqueues plugin-specific scripts.
	// public function register_plugin_scripts() {
	
	// 	wp_register_script( 'db_classifieds-plugin-script', plugins_url( 'db_classifieds/js/display.js' ) );
	// 	wp_enqueue_script( 'db_classifieds-plugin-script' );
	
	// }
	
	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	
	function db_classifieds_menu() {
		add_menu_page( 'Classifieds', 'Classifieds', 'manage_options', 'classifieds',array( &$this, 'classifieds_page' ));
	}

	function insert_posts() {

		global $wpdb;
		ini_set('max_execution_time', 300);
		$publish = $_POST['publish'];
		$classcat = get_cat_ID('Classified');
		$response;

		if ($publish == 'replace') {
			
			$args = array('numberposts' => -1, 'category' => $classcat);
			$classifieds = get_posts($args);
			$response['count'] = count($classifieds);
			foreach ($classifieds as $ad) {
				$deleted = wp_delete_post($ad->ID,true);
				if ($deleted) {
					$response['deleted']++;
				}
			}
		}
		foreach ($_SESSION['db_classifieds'] as $ad) {
			$post = array(
		 				//'post_author' => 'AUTHORID', // TODO: what's the author id?
		 				'post_category' => array($classcat), // TODO: what's the category id?
		 				'post_title' => $ad['id'],
		 				'post_content' => $ad['run'],
		 				'tags_input' => $ad['code'].','.$ad['name'], // TODO: what are the tags?
		 				'post_status' => 'publish'
		 	);
			
			$postid = wp_insert_post($post);
			if ($postid != 0) {
				$response['added']++;
			}
			else {
				$response['failed'][] = $post;
			}
		}
		echo json_encode($response);
		session_destroy();
		unset($_SESSION['db_classifieds']);
		exit();
	}

	function classifieds_page() {
		
		echo "
			<script type='text/javascript'>
				var ajaxurl = '".admin_url('admin-ajax.php')."';
				var pluginurl = '".plugin_dir_url(__FILE__)."';
			</script>
		";

		echo "
			<div class='wrap'>
				<h2>Classifieds</h2>
				<p>Upload the XML file exported from AdPro. Check the previews for errors, then add or replace.</p>
				<div id='forms'>
					<form enctype='multipart/form-data' action='' method='POST'>
						<input type='hidden' name='MAX_FILE_SIZE' value='100000' />
						<input name='uploadedfile' type='file' />
						<input class='button-primary' type='submit' value='Upload' />
					</form>
		";

		if (!empty($_FILES['uploadedfile']) && file_exists($_FILES['uploadedfile']['tmp_name'])) {
   			
   			$xml = file_get_contents($_FILES['uploadedfile']['tmp_name']);
   			$ads = new SimpleXMLElement($xml);
	 		
	 		$_SESSION['db_classifieds'] = array();

	 		$table = "
 				<table class='widefat'>
 					<thead>
 						<tr>
 							<th>ID</th>
 							<th>Run</th>
 							<th>Code</th>
 							<th>Category</th>
 						</tr>
 					</thead>
 					<tfoot>
 						<tr>
 							<th>ID</th>
 							<th>Run</th>
 							<th>Code</th>
 							<th>Category</th>
 						</tr>
 					</tfoot>
 					<tbody>
 			";

		 	foreach ($ads->section as $section) {
		 		$category = (string) $section->attributes()->name;
		 		$category = explode(" - ", $category);
		 		$category = $category[1];
		 		$code = (string) $section->attributes()->code;
		 		foreach ($section->ad as $ad) {
		 			$id = (string) $ad->attributes()->id;
		 			$run= "";
		 			foreach ($ad->paragraph as $paragraph) {
		 				foreach ($paragraph->run as $arun) {
		 					$run .= $arun;
		 					$run .= " ";
		 				}
		 			
		 			}

		 			$_SESSION['db_classifieds'][] = array(
		 				'id' => $id,
		 				'run' => $run,
		 				'code' => $code,
		 				'category' => $category
		 			);

		 			$table .= "
		 				<tr>
		 					<td>$id</td>
		 					<td>$run</td>
		 					<td>$code</td>
		 					<td>$category</td>
		 				</tr>
		 			";
		 		}
		 	}

		 	$table .= "</tbody></table>";
		 	
		 	echo "		 	
				<button class='button-primary' type='button' id='add_posts'>Add</button>
				<button class='button-primary' type-'button' id='replace_posts'>Replace</button>
		 		<span id='status'></span>
		 		</div> <!-- end #forms --!>
				";

			echo $table;
		}

		echo "</div> <!-- end .wrap --!>";
		exit();
	}

	function db_classifieds_options() {

	}

	  
} // end class
new db_classifieds();
?>