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
    @package: azad-custom-order
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

            add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
            add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

            if ( ! get_option( 'aco_install' ) )
                $this->aco_install();
                
            add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
            // add_action( 'admin_init', array( $this, 'aco_refresh' ) );
            // add_filter( 'aco_post_types_args', array( $this, 'scpo_filter_post_types' ), 10, 2 );

            add_action( 'admin_init', array( $this, 'aco_update_options' ) );
            add_action( 'admin_init', array( $this, 'load_script_css' ) );

            // sortable ajax action
            add_action( 'wp_ajax_update-menu-order', array( $this, 'update_menu_order' ) );
            //add_action( 'wp_ajax_update-menu-order-tags', array( $this, 'update_menu_order_tags' ) );

            // add_action( 'wp_ajax_update-menu-order-users', array( $this, 'update_menu_order_users' ) );
			// add_action( 'wp_ajax_update-menu-order-extras', array( $this, 'update_menu_order_extras' ) );

            // reorder post types
            add_action( 'pre_get_posts', array( $this, 'aco_pre_get_posts' ) );
            
            // add_filter( 'get_previous_post_where', array( $this, 'scporder_previous_post_where' ) );
            // add_filter( 'get_previous_post_sort', array( $this, 'scporder_previous_post_sort' ) );
            // add_filter( 'get_next_post_where', array( $this, 'scporder_next_post_where' ) );
            // add_filter( 'get_next_post_sort', array( $this, 'scporder_next_post_sort' ) );
            
            // reorder taxonomies
            // add_filter( 'get_terms_orderby', array( $this, 'scporder_get_terms_orderby' ), 10, 3 );
            // add_filter( 'wp_get_object_terms', array( $this, 'scporder_get_object_terms' ), 10, 3 );
            // add_filter( 'get_terms', array( $this, 'scporder_get_object_terms' ), 10, 3 );
            
            // reorder users
            // add_filter( 'pre_user_query', array( $this, 'aco_pre_user_query' ) );
            
            // notice perposes
            // add_action( 'admin_notices', array( $this, 'aco_notice_not_checked' ) );
            // add_action( 'wp_ajax_aco_dismiss_notices', array( $this, 'aco_dismiss_notices' ) );
            
            // reset ajax action
            // add_action( 'wp_ajax_aco_reset_order', array( $this, 'aco_ajax_reset_order' ) );
            
        }

        public function aco_refresh(){
            if ( aco_doing_ajax() ) {
                return;
            }

            global $wpdb;
            $objects = $this->get_aco_options_objects();
            $tags = $this->get_aco_options_tags();

            if ( ! empty( $objects ) ) {
                
                // foreach ( $objects as $object ) {
                //     $result = $wpdb->get_results("
                //         SELECT count(*) as cnt, max( menu_order ) as max, min( menu_order ) as min
                //         FROM $wpdb->posts
                //         WHERE post_type = '" . $object . "' AND post_status IN ( 'publish', 'pending', 'draft', 'private', 'future' )
                //     " );

                //     if ( $result[0]->cnt == 0 || $result[0]->cnt == $result[0]->max )
                //         continue;

                //     // Here's the optimization
                //     $wpdb->query( "SET @row_number = 0;" );
                //     $wpdb->query( "UPDATE $wpdb->posts as pt JOIN (
                //     SELECT ID, (@row_number:=@row_number + 1) AS `rank`
                //     FROM $wpdb->posts
                //     WHERE post_type = '$object' AND post_status IN ( 'publish', 'pending', 'draft', 'private', 'future' )
                //     ORDER BY menu_order ASC
                //     ) as pt2
                //     ON pt.id = pt2.id
                //     SET pt.menu_order = pt2.`rank`;" );

                // }
            }
        }

        public function update_menu_order() {

            global $wpdb;
    
            parse_str( $_POST['order'], $data );
            
            if ( ! is_array( $data ) )
            return false;
            
            $id_arr = array();
            foreach ( $data as $key => $values ) {
                foreach ( $values as $position => $id ) {
                    $id_arr[] = $id;
                }
            }
            
            $menu_order_arr = array();
            foreach ( $id_arr as $key => $id ) {
                $results = $wpdb->get_results( "SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval( $id ) );
                foreach ( $results as $result ) {
                    $menu_order_arr[] = $result->menu_order;
                }
            }
    
            sort( $menu_order_arr );
    
            foreach ( $data as $key => $values ) {
                foreach ( $values as $position => $id ) {
                    $wpdb->update( $wpdb->posts, array( 'menu_order' => $menu_order_arr[$position] ), array( 'ID' => intval( $id ) ) );
                }
            }
    
            do_action( 'aco_update_menu_order' );
        }

        public function _check_load_script_css() {
            $active = false;
    
            $objects = $this->get_aco_options_objects();
            $tags = $this->get_aco_options_tags();
    
            if ( empty( $objects ) && empty( $tags ) )
                return false;
    
            if ( isset( $_GET['orderby'] ) || strstr( $_SERVER['REQUEST_URI'], 'action=edit' ) || strstr( $_SERVER['REQUEST_URI'], 'wp-admin/post-new.php' ) )
                return false;
    
            if ( ! empty( $objects ) ) {
                if ( isset( $_GET['post_type']) && !isset( $_GET['taxonomy'] ) && in_array( $_GET['post_type'], $objects ) ) { // if page or custom post types
                    $active = true;
                }
                if ( ! isset($_GET['post_type']) && strstr( $_SERVER['REQUEST_URI'], 'wp-admin/edit.php') && in_array( 'post', $objects ) ) { // if post
                    $active = true;
                }
            }
    
            if ( ! empty( $tags )) {
                if ( isset( $_GET['taxonomy'] ) && in_array( $_GET['taxonomy'], $tags ) ) {
                    $active = true;
                }
            }
    
            return $active;
        }

        public function load_script_css() {
            if ( $this->_check_load_script_css() ) {
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'jquery-ui-sortable' );
                wp_enqueue_script( 'aco', ACO_URL . '/assets/aco.js', array( 'jquery' ), ACO_VERSION, true);
    
                wp_enqueue_style( 'aco', ACO_URL . '/assets/aco.css', array(), ACO_VERSION );
            }
        }

        public function aco_update_options() {
            global $wpdb;
    
            if ( ! isset( $_POST['aco_submit'] ) )
                return false;
    
            check_admin_referer( 'aco_nonce' );
    
            $input_options = array();
            $input_options['objects'] = isset( $_POST['objects'] ) ? $_POST['objects'] : '';
            $input_options['tags'] = isset( $_POST['tags'] ) ? $_POST['tags'] : '';
            $input_options['show_advanced_view'] = isset( $_POST['show_advanced_view'] ) ? $_POST['show_advanced_view'] : '';
    
            update_option( 'aco_options', $input_options );
    
            $objects = $this->get_aco_options_objects();
            $tags = $this->get_aco_options_tags();
    
            if ( ! empty( $objects ) ) {
                foreach ( $objects as $object ) {
                    $result = $wpdb->get_results( "
                        SELECT count(*) as cnt, max( menu_order ) as max, min( menu_order ) as min
                        FROM $wpdb->posts
                        WHERE post_type = '" . $object . "' AND post_status IN ( 'publish', 'pending', 'draft', 'private', 'future' )
                    ");
                    if ( $result[0]->cnt == 0 || $result[0]->cnt == $result[0]->max )
                        continue;
    
                    if ( $object == 'page' ) {
                        $results = $wpdb->get_results( "
                            SELECT ID
                            FROM $wpdb->posts
                            WHERE post_type = '" . $object . "' AND post_status IN ( 'publish', 'pending', 'draft', 'private', 'future' )
                            ORDER BY post_title ASC
                        " );
                    } else {
                        $results = $wpdb->get_results( "
                            SELECT ID
                            FROM $wpdb->posts
                            WHERE post_type = '" . $object . "' AND post_status IN ('publish', 'pending', 'draft', 'private', 'future' )
                            ORDER BY post_date DESC
                        " );
                    }
                    foreach ( $results as $key => $result ) {
                        $wpdb->update( $wpdb->posts, array( 'menu_order' => $key + 1), array( 'ID' => $result->ID ) );
                    }
                }
            }
    
            // if ( ! empty( $tags ) ) {
            //     foreach ( $tags as $taxonomy ) {
            //         $result = $wpdb->get_results("
            //             SELECT count(*) as cnt, max( term_order ) as max, min( term_order ) as min
            //             FROM $wpdb->terms AS terms
            //             INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON ( terms.term_id = term_taxonomy.term_id )
            //             WHERE term_taxonomy.taxonomy = '" . $taxonomy . "'
            //         ");
            //         if ( $result[0]->cnt == 0 || $result[0]->cnt == $result[0]->max )
            //             continue;
    
            //         $results = $wpdb->get_results( "
            //             SELECT terms.term_id
            //             FROM $wpdb->terms AS terms
            //             INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON ( terms.term_id = term_taxonomy.term_id )
            //             WHERE term_taxonomy.taxonomy = '" . $taxonomy . "'
            //             ORDER BY name ASC
            //         " );
            //         foreach ( $results as $key => $result ) {
            //             $wpdb->update( $wpdb->terms, array( 'term_order' => $key + 1 ), array( 'term_id' => $result->term_id ) );
            //         }
            //     }
            // }
    
            wp_redirect( "admin.php?page=" . $this->slug . "&msg=update" );
        }

        public function aco_install(){

            global $wpdb;
            $result = $wpdb->query( "DESCRIBE $wpdb->terms `term_order`" );
            if ( ! $result ) {
                $query = "ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'";
                $result = $wpdb->query( $query );
            }
            update_option( 'aco_install', 1 );

        }

        /* Add the plugin settings link */
        function plugin_settings_link( $actions, $file ) {

            if ( $file != ACO_BASENAME ) {
                return $actions;
            }

            $actions['aco_settings'] = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . $this->slug ) ) . '" aria-label="settings"> ' . __( 'Settings', ACO_TEXTDOMAIN ) . '</a>';

            return $actions;

        }

        public function i18n(){
            load_plugin_textdomain( $this->slug, false, basename( dirname( __FILE__ ) ) . '/languages/' );
        }

        public function scporder_notice_not_checked() {

            $settings = $this->get_scporder_options_objects();
            if ( ! empty( $settings ) ){
                return;
            }
    
            $screen = get_current_screen();
    
            if ( 'settings_page_scporder-settings' == $screen->id ) {
                return;
            }
    
            $dismessed = get_option( 'scporder_notice', false );
    
            if ( $dismessed ) {
                return;
            }
    
            ?>
            <div class="notice scpo-notice" id="scpo-notice">
                <img src="<?php echo esc_url( plugins_url( 'assets/logo.jpg', __FILE__ ) ); ?>" width="80">
    
                <h1><?php esc_html_e( 'Azad Custom Order', ACO_TEXTDOMAIN ); ?></h1>
    
                <p><?php esc_html_e( 'Thank you for installing our awesome plugin, in order to enable it you need to go to the settings page and select which custom post or taxonomy you want to order.', ACO_TEXTDOMAIN ); ?></p>
    
                <p><a href="<?php echo admin_url( 'options-general.php?page=scporder-settings' ) ?>" class="button button-primary button-hero"><?php esc_html_e( 'Get started !', ACO_TEXTDOMAIN ); ?></a></p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', ACO_TEXTDOMAIN ); ?></span></button>
            </div>
    
            <style>
                .scpo-notice {
                    background: #e9eff3;
                    border: 10px solid #fff;
                    color: #608299;
                    padding: 30px;
                    text-align: center;
                    position: relative;
                }
            </style>
            <script>
                jQuery(document).ready(function(){
                    jQuery( '#scpo-notice .notice-dismiss' ).click(function( evt ){
                        evt.preventDefault();
    
                        var ajaxData = {
                            'action' : 'scporder_dismiss_notices',
                            'scporder_nonce' : '<?php echo wp_create_nonce( 'scporder_dismiss_notice' ) ?>'
                        }
    
                        jQuery.ajax({
                            url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                            method: "POST",
                            data: ajaxData,
                            dataType: "html"
                        }).done(function(){
                            jQuery("#scpo-notice").hide();
                        });
    
                    });
                })
            </script>
            <?php
        }

        public function add_settings_page(){

            if( current_user_can( 'activate_plugins' ) && function_exists( 'add_options_page' ) ){
                $hook = add_options_page(
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

        public function update_menu_order_tags() {
            global $wpdb;
    
            parse_str( $_POST['order'], $data );
    
            if ( ! is_array( $data ) )
                return false;
    
            $id_arr = array();
            foreach ( $data as $key => $values ) {
                foreach ( $values as $position => $id ) {
                    $id_arr[] = $id;
                }
            }
    
            $menu_order_arr = array();
            foreach ( $id_arr as $key => $id ) {
                $results = $wpdb->get_results( "SELECT term_order FROM $wpdb->terms WHERE term_id = " . intval( $id ) );
                foreach ( $results as $result ) {
                    $menu_order_arr[] = $result->term_order;
                }
            }
            sort( $menu_order_arr );
    
            foreach ( $data as $key => $values ) {
                foreach ( $values as $position => $id ) {
                    $wpdb->update( $wpdb->terms, array( 'term_order' => $menu_order_arr[$position]), array( 'term_id' => intval( $id ) ) );
                }
            }
    
            do_action( 'scp_update_menu_order_tags' );
        }

        public function aco_pre_get_posts( $wp_query ) {
            $objects = $this->get_aco_options_objects();
    
            if ( empty( $objects ) )
                return false;
            if ( is_admin() ) {
    
                if ( isset( $wp_query->query['post_type']) && ! isset( $_GET['orderby'] ) ) {
                    if ( in_array( $wp_query->query['post_type'], $objects ) ) {
                        $wp_query->set( 'orderby', 'menu_order' );
                        $wp_query->set( 'order', 'ASC' );
                    }
                }
            } else {
    
                $active = false;
    
                if ( isset( $wp_query->query['post_type'] ) ) {
                    if ( ! is_array( $wp_query->query['post_type'] ) ) {
                        if ( in_array( $wp_query->query['post_type'], $objects ) ) {
                            $active = true;
                        }
                    }
                } else {
                    if ( in_array( 'post', $objects ) ) {
                        $active = true;
                    }
                }
    
                if ( ! $active )
                    return false;
    
                if ( isset( $wp_query->query['suppress_filters'] ) ) {
                    if ( $wp_query->get( 'orderby' ) == 'date' )
                        $wp_query->set( 'orderby', 'menu_order' );
                    if ( $wp_query->get( 'order' ) == 'DESC' )
                        $wp_query->set( 'order', 'ASC' );
                } else {
                    if ( ! $wp_query->get( 'orderby' ) )
                        $wp_query->set( 'orderby', 'menu_order' );
                    if ( ! $wp_query->get( 'order' ) )
                        $wp_query->set( 'order', 'ASC' );
                }
    
            }
        }

        public function scporder_get_object_terms( $terms ) {
            $tags = $this->get_scporder_options_tags();
    
            if ( is_admin() && isset( $_GET['orderby'] ) )
                return $terms;
    
            foreach ( $terms as $key => $term ) {
                if ( is_object( $term ) && isset( $term->taxonomy ) ) {
                    $taxonomy = $term->taxonomy;
                    if ( ! in_array( $taxonomy, $tags ) )
                        return $terms;
                } else {
                    return $terms;
                }
            }
    
            usort( $terms, array( $this, 'taxcmp' ) );
            return $terms;
        }
    
        public function taxcmp( $a, $b ) {
            if ( $a->term_order == $b->term_order )
                return 0;
            return ( $a->term_order < $b->term_order ) ? -1 : 1;
        }
    
        public function get_aco_options_objects() {
            $aco_options = get_option( 'aco_options' ) ? get_option( 'aco_options' ) : array();
            $objects = isset( $aco_options['objects'] ) && is_array( $aco_options['objects'] ) ? $aco_options['objects'] : array();
            return $objects;
        }
    
        public function get_aco_options_tags() {
            $aco_options = get_option( 'aco_options' ) ? get_option( 'aco_options' ) : array();
            $tags = isset( $aco_options['tags'] ) && is_array( $aco_options['tags'] ) ? $aco_options['tags'] : array();
            return $tags;
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

if( ! function_exists( 'load_azad_custom_order' ) ){
    function load_azad_custom_order(){
        return Azad_Custom_Order::_get_instance();
    }
}

if( is_admin() ){
    $GLOBALS['load_azad_custom_order'] = load_azad_custom_order();
}

require_once( ACO_PATH . 'class-custom-order.php' );
register_activation_hook( __FILE__, array( 'ACO_Activator', 'activate_plugin' ) );

function aco_doing_ajax(){

    if ( function_exists( 'wp_doing_ajax' ) ) {
        return wp_doing_ajax();
    }

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return true;
    }

    return false;

}

/**
 * SCP Order Uninstall hook
 */
register_uninstall_hook( __FILE__, 'aco_uninstall' );

function aco_uninstall() {
    global $wpdb;
    if ( function_exists( 'is_multisite' ) && is_multisite() ) {
        $curr_blog = $wpdb->blogid;
        $blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blogids as $blog_id ) {
            switch_to_blog( $blog_id );
            aco_uninstall_db();
        }
        switch_to_blog( $curr_blog );
    } else {
        aco_uninstall_db();
    }
}

function aco_uninstall_db() {
    global $wpdb;
    $result = $wpdb->query( "DESCRIBE $wpdb->terms `term_order`" );
    if ( $result ) {
        $query = "ALTER TABLE $wpdb->terms DROP `term_order`";
        $result = $wpdb->query( $query );
    }
    delete_option( 'aco_install' );
}