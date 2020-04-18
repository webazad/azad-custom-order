<?php
/* 
 Plugin Name: Azad Custom Order
 Description: A very simple plugin to reorder posts in a desired way.
  Plugin URI: gittechs.com/plugin/azad-custom-order
      Author: Md. Abul Kalam Azad
  Author URI: gittechs.com/author
Author Email: webdevazad@gmail.com
     Version: 1.0.0
     License: GPL2
 License URI: http: //www.gnu.org/licenses/gpl-2.0.html
 Text Domain: azad-custom-order
 Domain Path: /languages
*/

defined( 'ABSPATH' ) || exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$plugin_data = get_plugin_data( __FILE__ );

define( 'ACO_NAME', $plugin_data['Name'] );
define( 'ACO_VERSION', $plugin_data['Version'] );
define( 'ACO_TEXTDOMAIN', $plugin_data['TextDomain'] );
define( 'ACO_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACO_URL', plugin_dir_url( __FILE__ ) );
define( 'ACO_BASENAME', plugin_basename( __FILE__ ) );

if( ! class_exists( 'Azad_Custom_Order' ) ) {

    final class Azad_Custom_Order{

        public static $_instance = null;
        public $slug = ACO_TEXTDOMAIN;

        public function __construct(){

            add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
            add_action( 'admin_init', array( $this, 'admin_init' ) );
            add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
            add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

        }

        public function admin_init(){}

        /* Add the plugin settings link */
        function plugin_settings_link( $actions, $file ) {

            if ( $file != ACO_BASENAME ) {
                return $actions;
            }

            $actions['awr_settings'] = '<a href="' . esc_url( admin_url( 'tools.php?page=' . $this->slug ) ) . '" aria-label="settings"> ' . __( 'Settings', ACO_TEXTDOMAIN ) . '</a>';

            return $actions;

        }

        public function i18n(){}

        public function add_settings_page(){

            if( current_user_can( 'activate_plugins' ) && function_exists( 'add_management_page' ) ){
                $hook = add_management_page(
                    esc_html__( 'Azad Custom Order', ACO_TEXTDOMAIN ),
                    esc_html__( 'Azad Custom Order', ACO_TEXTDOMAIN ),
                    'activate_plugins',
                    $this->slug,
                    array( $this, 'admin_settings_page' )
                );
            }

        }

        public function admin_settings_page(){  
            require ACO_PATH . 'settings.php'; 
        }

        public static function _get_instance(){

            if( is_null( self::$_instance ) && ! isset( self::$_instance ) && ! ( self::$_instance instanceof self ) ){
                self::$_instance = new self();            
            }
            return self::$_instance;

        }

        public function __destruct(){}
    }
}

if( ! function_exists('load_azad_custom_order')){
    function load_azad_custom_order(){
        return Azad_Custom_Order::_get_instance();
    }
}

if( is_admin() ){
    $GLOBALS['load_azad_custom_order'] = load_azad_custom_order();
}

require_once( ACO_PATH . 'class-custom-order.php' );
register_activation_hook( __FILE__, array( 'ACO_Activator', 'activate_plugin' ) );