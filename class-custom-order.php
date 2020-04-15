<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if(! class_exists('ACO_Activator')){

    class ACO_Activator{

        public static $_instance = null;

        public function __construct(){
            add_action( 'admin_init', array( 'AWR_Activator', 'awr_safe_welcome_redirect' ) );
        }

        public function awr_safe_welcome_redirect(){

			if ( ! get_transient( 'welcome_redirect_awr' ) ) {
                return;
            }
            delete_transient( 'welcome_redirect_awr' );
            if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
                return;
            }
            wp_safe_redirect( add_query_arg(
                array(
                    'page' => ACO_TEXTDOMAIN
                    ),
                admin_url( 'admin.php' )
            ) );

        }

        public static function activate_plugin() {

            set_transient( 'welcome_redirect_awr', true, 60 );
			
            $awr_textdomain = get_option( ACO_TEXTDOMAIN );
            
			if( ! $awr_textdomain ){
                update_option( ACO_TEXTDOMAIN, time() );
            }
            
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

if( ! function_exists( 'load_aco_activator' )){
    function load_aco_activator(){
        return ACO_Activator::_get_instance();
    }
}
$GLOBALS['load_aco_activator'] = load_aco_activator();