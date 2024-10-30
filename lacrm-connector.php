<?php

/*
  Plugin Name: CF7 LACRM Connector
  Plugin URI: https://wordpress.org/plugins/lacrm-connector-for-contact-form7/
  Description: Send your Contact Form 7 data directly to your Less Annoying CRM account.
  Version: 1.2
  Author: OC2PS, WesternDeal, julius1986 
  Author URI: http://profiles.wordpress.org/sooskriszta
  Text Domain: lacrmconnector
 */

if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

// Declare some global constants
define( 'LACRM_CONNECTOR_VERSION', '1.0' );
define( 'LACRM_CONNECTOR_DB_VERSION', '1.0' );
define( 'LACRM_CONNECTOR_ROOT', dirname( __FILE__ ) );
define( 'LACRM_CONNECTOR_URL', plugins_url( '/', __FILE__ ) );
define( 'LACRM_CONNECTOR_BASE_FILE', basename( dirname( __FILE__ ) ) . '/lacrm-connector.php' );
define( 'LACRM_CONNECTOR_PATH', plugin_dir_path( __FILE__ ) ); //use for include files to other files
define( 'LACRM_CONNECTOR_PRODUCT_NAME', 'LACRM Connector' );
define( 'LACRM_CONNECTOR_CURRENT_THEME', get_stylesheet_directory() );
load_plugin_textdomain( 'lacrmconnector', false, basename( dirname( __FILE__ ) ) . '/languages' );

/*
 * include utility classes
 */
if ( ! class_exists( 'Lacrm_Connector_Utility' ) ) {
   include( LACRM_CONNECTOR_ROOT . '/includes/class-lacrm-utility.php' );
}

/*
 * Main LACRM connector class
 * @class Lacrm_Connector_Init
 * @since 1.0
 */

class Lacrm_Connector_Init {

   /**
    *  Set things up.
    *  @since 1.0
    */
   public function __construct() {
      //run on activation of plugin
      register_activation_hook( __FILE__, array( $this, 'lacrm_connector_activate' ) );

      //run on deactivation of plugin
      register_deactivation_hook( __FILE__, array( $this, 'lacrm_connector_deactivate' ) );

      // load the classes
      add_action( 'init', array( $this, 'load_all_classes' ) );

      // validate is contact form 7 plugin exist
      add_action( 'admin_init', array( $this, 'validate_parent_plugin_exists' ) );

      // register admin menu under "Contact" > "Integration"
      add_action( 'admin_menu', array( $this, 'register_menu_pages' ) );

      // load the js and css files
      add_action( 'init', array( $this, 'load_css_and_js_files' ) );
   }

   /**
    * Do things on plugin activation
    * @since 1.0
    */
   public function lacrm_connector_activate() {
      global $wpdb;
      $this->run_on_activation();
      if ( function_exists( 'is_multisite' ) && is_multisite() ) {
         // check if it is a network activation - if so, run the activation function for each blog id
         if ( $network_wide ) {
            // Get all blog ids
            $blogids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );
            foreach ( $blogids as $blog_id ) {
               switch_to_blog( $blog_id );
               $this->run_for_site();
               $this->install_admin_database();
               restore_current_blog();
            }
            return;
         }
      }

      // for non-network sites only
      $this->run_for_site();
   }

   /**
    * deactivate the plugin
    * @since 1.0
    */
   public function lacrm_connector_deactivate() {
      
   }

   /**
    * Load all the classes - as part of init action hook
    *
    * @since 1.0
    */
   public function load_all_classes() {
      // Service class
      if ( ! class_exists( 'Lacrm_Connector_Service' ) ) {
         include( LACRM_CONNECTOR_ROOT . '/includes/class-lacrm-service.php' );
      }
   }

   /**
    * Validate parent Plugin Contact Form 7 exist and activated
    * @access public
    * @since 1.0
    */
   public function validate_parent_plugin_exists() {
      $plugin = plugin_basename( __FILE__ );
      if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
         add_action( 'admin_notices', array( $this, 'contact_form_7_missing_notice' ) );
         deactivate_plugins( $plugin );
         if ( isset( $_GET[ 'activate' ] ) ) {
            // Do not sanitize it because we are destroying the variables from URL
            unset( $_GET[ 'activate' ] );
         }
      }
   }

   /**
    * If Contact Form 7 plugin is not installed or activated then throw the error
    *
    * @access public
    * @return mixed error_message, an array containing the error message
    *
    * @since 1.0 initial version
    */
   public function contact_form_7_missing_notice() {
      $plugin_error = Lacrm_Connector_Utility::instance()->admin_notice( array(
          'type' => 'error',
          'message' => 'LACRM Connector Add-on requires Contact Form 7 plugin to be installed and activated.'
              ) );
      echo $plugin_error;
   }

   /**
    * Create/Register menu items for the plugin.
    * @since 1.0
    */
   public function register_menu_pages() {
      $current_role = Lacrm_Connector_Utility::instance()->get_current_user_role();
      add_submenu_page( 'wpcf7', __( 'LACRM', 'lacrmconnector' ), __( 'LACRM', 'lacrmconnector' ), $current_role, 'wpcf7-lacrm-config', array( $this, 'lacrm_config' ) );
   }

   /**
    * Lacrm page action.
    * This method is called when the menu item "LACRM" is clicked.
    *
    * @since 1.0
    */
   public function lacrm_config() {
      include( LACRM_CONNECTOR_PATH . "includes/pages/lacrm-config.php" );
   }

   public function load_css_and_js_files() {
      add_action( 'admin_print_styles', array( $this, 'add_css_files' ) );
      add_action( 'admin_print_scripts', array( $this, 'add_js_files' ) );
   }

   /**
    * enqueue CSS files
    * @since 1.0
    */
   public function add_css_files() {
      if ( is_admin() && ( isset( $_GET[ 'page' ] ) && ( ( $_GET[ 'page' ] == 'wpcf7' ) || ( $_GET[ 'page' ] == 'wpcf7-lacrm-config' ) || ( $_GET[ 'page' ] == 'wpcf7-new' ) ) ) ) {
         wp_enqueue_style( 'lacrm-connector-css', LACRM_CONNECTOR_URL . 'assets/css/lacrm.css', LACRM_CONNECTOR_VERSION, true );
      }
   }

   /**
    * enqueue JS files
    * @since 1.0
    */
   public function add_js_files() {
      if ( is_admin() && ( isset( $_GET[ 'page' ] ) && ( ( $_GET[ 'page' ] == 'wpcf7' ) || ( $_GET[ 'page' ] == 'wpcf7-lacrm-config' ) || ( $_GET[ 'page' ] == 'wpcf7-new' ) ) ) ) {
         wp_enqueue_script( 'lacrm-connector-js', LACRM_CONNECTOR_URL . 'assets/js/lacrm.js', LACRM_CONNECTOR_VERSION, true );
         wp_enqueue_script( 'jquery-json', LACRM_CONNECTOR_URL . 'assets/js/jquery.json.js', '', '2.3', true );
      }
   }

   /**
    * called on upgrade.
    * checks the current version and applies the necessary upgrades from that version onwards
    * @since 1.0
    */
   public function run_on_upgrade() {
      $plugin_options = get_site_option( 'lacrm_info' );
   }

   /**
    * Called on activation.
    * Creates the site_options (required for all the sites in a multi-site setup)
    * If the current version doesn't match the new version, runs the upgrade
    * @since 1.0
    */
   private function run_on_activation() {
      $plugin_options = get_site_option( 'lacrm_info' );
      if ( false === $plugin_options ) {
         $lacrm_info = array(
             'version' => LACRM_CONNECTOR_VERSION,
             'db_version' => LACRM_CONNECTOR_DB_VERSION
         );
         update_site_option( 'lacrm_info', $lacrm_info );
      } else if ( LACRM_CONNECTOR_VERSION != $plugin_options[ 'version' ] ) {
         $this->run_on_upgrade();
      }
   }

   /**
    * Called on activation.
    * Creates the options and DB (required by per site)
    * @since 1.0
    */
   private function run_for_site() {
      if ( ! get_option( 'lacrm_user_code' ) ) {
         update_option( "lacrm_user_code", '' );
      }
      if ( ! get_option( 'lacrm_api_token' ) ) {
         update_option( "lacrm_api_token", '' );
      }
      if ( ! get_option( 'lacrm_verify_token' ) ) {
         update_option( "lacrm_verify_token", 'invalid' );
      }
      $this->install_admin_database();
   }

   /**
    * Install admin table
    * @since 1.0
    */
   private function install_admin_database() {
      global $wpdb;
      if ( ! empty( $wpdb->charset ) ) {
         $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
      }
      if ( ! empty( $wpdb->collate ) ) {
         $charset_collate .= " COLLATE {$wpdb->collate}";
         require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      }

      //lacrm_connector table
      $table_name = Lacrm_Connector_Utility::instance()->get_lacrm_table_name();
      if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
         $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
         ID int(11) NOT NULL AUTO_INCREMENT,
         form_id int(11) NOT NULL,
         contact_info longtext,
         custom_fields longtext,
         pipeline_info longtext,
         notes longtext,
         pipeline_fields longtext,
         tasks longtext,
         PRIMARY KEY (ID)
         ){$charset_collate};";
         dbDelta( $sql );
      }
   }

}

// Initialize the LACRM connector class
$init = new Lacrm_Connector_Init();
