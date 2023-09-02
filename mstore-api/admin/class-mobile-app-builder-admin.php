<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://inspireui.com
 * @since      1.0.0
 *
 * @package    Mobile_App_Builder
 * @subpackage Mobile_App_Builder/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mobile_App_Builder
 * @subpackage Mobile_App_Builder/admin
 * @author     InspireUI <support@inspireui.com>
 */
include_once DIR_PATH . "templates/class-mobile-detect.php";
include_once DIR_PATH. "templates/class-rename-generate.php";
include_once DIR_PATH . "controllers/flutter-user.php";
include_once DIR_PATH . "controllers/flutter-home.php";
include_once DIR_PATH . "controllers/flutter-booking.php";
include_once DIR_PATH . "controllers/flutter-vendor-admin.php";
include_once DIR_PATH . "controllers/flutter-woo.php";
include_once DIR_PATH . "controllers/flutter-delivery.php";
include_once DIR_PATH . "functions/index.php";
include_once DIR_PATH . "functions/utils.php";
include_once DIR_PATH . "controllers/flutter-tera-wallet.php";
include_once DIR_PATH . "controllers/flutter-paytm.php";
include_once DIR_PATH . "controllers/flutter-paystack.php";
include_once DIR_PATH . "controllers/flutter-flutterwave.php";
include_once DIR_PATH . "controllers/flutter-myfatoorah.php";
include_once DIR_PATH . "controllers/flutter-midtrans.php";
include_once DIR_PATH . "controllers/flutter-paid-memberships-pro.php";
include_once DIR_PATH . "controllers/listing-rest-api/class.api.fields.php";
include_once DIR_PATH . "controllers/flutter-blog.php";
include_once DIR_PATH . "controllers/flutter-wholesale.php";
include_once DIR_PATH . "controllers/flutter-stripe.php";
include_once DIR_PATH . "controllers/flutter-notification.php";
include_once DIR_PATH . "controllers/flutter-thawani.php";
include_once DIR_PATH . "controllers/flutter-expresspay.php";
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}

class Mobile_App_Builder_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    public function __construct($plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
		$this->version = $version;

        define('MSTORE_CHECKOUT_VERSION', $this->version);
        define('MSTORE_PLUGIN_FILE', __FILE__);
        
        /**
         * Prepare data before checkout by webview
         */
        add_action('template_redirect', 'flutter_prepare_checkout');

        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        //include_once(ABSPATH . 'wp-includes/pluggable.php');

        //migrate old versions to re-verify purchase code automatically
        verifyPurchaseCodeAuto();

        if (is_plugin_active('woocommerce/woocommerce.php') == false) {
            return 0;
        }
        add_action('woocommerce_init', 'woocommerce_mstore_init');
        function woocommerce_mstore_init()
        {
            include_once DIR_PATH . "controllers/flutter-order.php";
            include_once DIR_PATH . "controllers/flutter-multi-vendor.php";
            include_once DIR_PATH . "controllers/flutter-vendor.php";
            include_once DIR_PATH . "controllers/helpers/delivery-wcfm-helper.php";
            include_once DIR_PATH . "controllers/helpers/delivery-wcfm-helper.php";
            include_once DIR_PATH . "controllers/helpers/vendor-admin-woo-helper.php";
            include_once DIR_PATH . "controllers/helpers/vendor-admin-wcfm-helper.php";
            include_once DIR_PATH . "controllers/helpers/vendor-admin-dokan-helper.php";
            include_once DIR_PATH . "controllers/flutter-customer.php";
            include_once DIR_PATH . "functions/video-setting-embed.php";
        }

        $order = filter_has_var(INPUT_GET, 'code') && strlen(filter_input(INPUT_GET, 'code')) > 0 ? true : false;
        if ($order) {
            add_filter('woocommerce_is_checkout', '__return_true');
        }

        /*
		add_filter( 'woocommerce_get_item_data', 'display_custom_product_field_data_mstore_api', 10, 2 );

		function display_custom_product_field_data_mstore_api( $cart_data, $cart_item ) {

			if( !empty( $cart_data ) ){
                $custom_items = $cart_data;

				$code = sanitize_text_field($_GET['code']) ?: get_transient( 'mstore_code' );
				set_transient( 'mstore_code', $code, 600 );

				global $wpdb;
				$table_name = $wpdb->prefix . "mstore_checkout";
				$item = $wpdb->get_row("SELECT * FROM $table_name WHERE code = '$code'");
				if ($item) {
					$data = json_decode(urldecode(base64_decode($item->order)), true);
					$line_items = $data['line_items'];
					$product_ids = [];
					foreach($line_items as $line => $item) {
						$product_ids[$item['product_id']] = $item;
					}

					if (array_key_exists($cart_item['product_id'], $product_ids)) {
						if ($varian = $product_ids[$cart_item['product_id']]) {
							$variations = $varian['meta_data'];
							foreach($variations as $v => $f) {
								preg_match('#\((.*?)\)#', $f['key'], $match);
								$val = $match[1];
								$custom_items[] = array(
									'key'       => $f['value'],
									'value'     => $val,
									'display'   => $val,
								);
							}
						}
					}
				}

			    return $custom_items;
            }
            return $cart_data;
		}


		add_action( 'woocommerce_before_calculate_totals', 'add_custom_price_mstore_api' );

		function add_custom_price_mstore_api( $cart_object ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$add_price = 0;
				if ($variations = $cart_item['variation']) {
					foreach($variations as $v => $f) {
						preg_match('#\((.*?)\)#', $v, $match);
                        if(is_array($match) && array_key_exists(1,$match)){
                            $val = $match[1];
                            $cents = filter_var($val, FILTER_SANITIZE_NUMBER_INT);
                            if(is_numeric($cents)){
                                $add_price += floatval($cents / 100);
                            }
                        }
					}
				}
				$new_price = $cart_item['data']->get_price() + $add_price;
				$cart_item['data']->set_price($new_price);   
			}
		}
        */

        add_action('wp_print_scripts', array($this, 'handle_received_order_page'));

        //add meta box shipping location in order detail
        add_action('add_meta_boxes', 'mv_add_meta_boxes');
        if (!function_exists('mv_add_meta_boxes')) {
            function mv_add_meta_boxes()
            {
                add_meta_box('mv_other_fields', __('Shipping Location', 'woocommerce'), 'mv_add_other_fields_for_packaging', 'shop_order', 'side', 'core');
            }
        }
        // Adding Meta field in the meta container admin shop_order pages
        if (!function_exists('mv_add_other_fields_for_packaging')) {
            function mv_add_other_fields_for_packaging()
            {
                global $post;
                $note = $post->post_excerpt;
                $items = explode("\n", $note);
                if (strpos($items[0], "URL:") !== false) {
                    $url = str_replace("URL:", "", $items[0]);
                    echo esc_html('<iframe width="600" height="500" src="' . esc_url($url) . '"></iframe>');
                }
            }
        }

        register_activation_hook(__FILE__, array($this, 'create_custom_mstore_table'));


        /**
         * Register js file to theme
         */
        function mstore_frontend_script()
        {
            wp_enqueue_script('my_script', plugins_url('assets/js/mstore-inspireui.js', MSTORE_PLUGIN_FILE), array('jquery'), '1.0.0', true);
            wp_localize_script('my_script', 'MyAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
        }

        add_action('wp_enqueue_scripts', 'mstore_frontend_script');
        // Setup Ajax action hook
        add_action('wp_ajax_mstore_delete_json_file', array($this, 'mstore_delete_json_file'));
        add_action('wp_ajax_mstore_delete_apple_file', array($this, 'mstore_delete_apple_file'));
        add_action('wp_ajax_mstore_update_limit_product', array($this, 'mstore_update_limit_product'));
        add_action('wp_ajax_mstore_update_firebase_server_key', array($this, 'mstore_update_firebase_server_key'));
        add_action('wp_ajax_mstore_update_new_order_title', array($this, 'mstore_update_new_order_title'));
        add_action('wp_ajax_mstore_update_new_order_message', array($this, 'mstore_update_new_order_message'));
        add_action('wp_ajax_mstore_update_status_order_title', array($this, 'mstore_update_status_order_title'));
        add_action('wp_ajax_mstore_update_status_order_message', array($this, 'mstore_update_status_order_message'));

        // listen changed order status to notify
        add_action('woocommerce_order_status_changed', array($this, 'track_order_status_changed'), 9, 4);
        add_action('woocommerce_checkout_update_order_meta', array($this, 'track_new_order'));
        add_action('woocommerce_rest_insert_shop_order_object', array($this, 'track_api_new_order'), 10, 4);

        $path = get_template_directory() . "/templates";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        if (file_exists($path)) {
            $templatePath = DIR_PATH . "templates/mstore-api-template.php";
            if (!copy($templatePath, $path . "/mstore-api-template.php")) {
                return 0;
            }
        }
    }

    function mstore_delete_json_file(){
        if(checkIsAdmin(get_current_user_id())){
            $id = sanitize_text_field($_REQUEST['id']);
            $nonce = sanitize_text_field($_REQUEST['nonce']);
            FlutterUtils::delete_config_file($id, $nonce);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_delete_apple_file(){
        if(checkIsAdmin(get_current_user_id())){
            $nonce = sanitize_text_field($_REQUEST['nonce']);
            FlutterAppleSignInUtils::delete_config_file($nonce);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_limit_product()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_limit_product')){
            $limit = sanitize_text_field($_REQUEST['limit']);
            if (is_numeric($limit)) {
                update_option("mstore_limit_product", intval($limit));
            }
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_firebase_server_key()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_firebase_server_key')){
            $serverKey = sanitize_text_field($_REQUEST['serverKey']);
            update_option("mstore_firebase_server_key", $serverKey);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_new_order_title()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_new_order_title')){
            $title = sanitize_text_field($_REQUEST['title']);
            update_option("mstore_new_order_title", $title);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_new_order_message()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_new_order_message')){
            $message = sanitize_text_field($_REQUEST['message']);
            update_option("mstore_new_order_message", $message);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_status_order_title()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_status_order_title')){
            $title = sanitize_text_field($_REQUEST['title']);
            update_option("mstore_status_order_title", $title);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_status_order_message()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_status_order_message')){
            $message = sanitize_text_field($_REQUEST['message']);
            update_option("mstore_status_order_message", $message);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    // update order via website
    function track_order_status_changed($id, $previous_status, $next_status)
    {
        trackOrderStatusChanged($id, $previous_status, $next_status);
    }

    // new order via website
    function track_new_order($order_id)
    {
        trackNewOrder($order_id);
    }

    //new order or update order via API
    function track_api_new_order($object,$request, $creating)
    {
        if($creating){
            trackNewOrder($object->id);
        }else{
            $body = $request->get_body_params();
            if(isset($body['status'])){
                sendNotificationForOrderStatusUpdated($object->id, $body['status']);
            }
        }
    }

    public function handle_received_order_page()
    {
        // default return true for getting checkout library working
        if (is_order_received_page()) {
            $detect = new MDetect;
            if ($detect->isMobile()) {
                wp_register_style('mstore-order-custom-style', plugins_url('assets/css/mstore-order-style.css', MSTORE_PLUGIN_FILE));
                wp_enqueue_style('mstore-order-custom-style');
            }
        }

    }

    function create_custom_mstore_table()
    {
        global $wpdb;
        // include upgrade-functions for maybe_create_table;
        if (!function_exists('maybe_create_table')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'mstore_checkout';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            `code` tinytext NOT NULL,
            `order` text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        $success = maybe_create_table($table_name, $sql);
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mobile_App_Builder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mobile_App_Builder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mobile-app-builder-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mobile_App_Builder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mobile_App_Builder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mobile-app-builder-admin.js', array( 'jquery' ), $this->version, false );

	}

}
