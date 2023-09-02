<?php include_once('admin/functions/common.php'); 
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://inspireui.com
 * @since             1.0.0
 * @package           Mobile_App_Builder
 *
 * @wordpress-plugin
 * Plugin Name:       Mobile App Builder
 * Description:       Build mobile application with flutter
 * Version:           1.0.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mobile-app-builder
 * Domain Path:       /languages
 */

defined('ABSPATH') or wp_die('No script kiddies please!');


// use MStoreCheckout\Templates\MDetect;

include_once plugin_dir_path(__FILE__) . "templates/class-mobile-detect.php";
include_once plugin_dir_path(__FILE__) . "templates/class-rename-generate.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-user.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-home.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-booking.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-vendor-admin.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-woo.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-delivery.php";
include_once plugin_dir_path(__FILE__) . "functions/index.php";
include_once plugin_dir_path(__FILE__) . "functions/utils.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-tera-wallet.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-paytm.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-paystack.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-flutterwave.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-myfatoorah.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-midtrans.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-paid-memberships-pro.php";
include_once plugin_dir_path(__FILE__) . "controllers/listing-rest-api/class.api.fields.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-blog.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-wholesale.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-stripe.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-notification.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-thawani.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-expresspay.php";
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}


function activate_mobile_app_builder() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mobile-app-builder-activator.php';
    $random_key = wp_generate_password($length = 12, $include_standard_special_chars = false);
    update_option("mstore_active_random_key", $random_key);
    update_option("mstore_active_hash_code", $random_key);
    update_option("mstore_purchase_code_key", $random_key);
    create_custom_mstore_table();
	Mobile_App_Builder_Activator::activate();
}

function create_custom_mstore_table() {
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
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mobile-app-builder-deactivator.php
 */
function deactivate_mobile_app_builder() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mobile-app-builder-deactivator.php';
    delete_option('mstore_purchase_code_key');
    delete_option("mstore_active_random_key");
    delete_option("mstore_active_hash_code");
	Mobile_App_Builder_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mobile_app_builder' );
register_deactivation_hook( __FILE__, 'deactivate_mobile_app_builder' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mobile-app-builder.php';
define( 'MOBILE_APP_BUILDER_VERSION', '1.0.0' );
define('DIR_PATH', plugin_dir_path( __FILE__ ));

// use JO\Module\Templater\Templater;
include plugin_dir_path(__FILE__) . "templates/class-templater.php";

add_action('plugins_loaded', 'load_mstore_templater');
function load_mstore_templater()
{

    // add our new custom templates
    $my_templater = new Templater(
        array(
            // YOUR_PLUGIN_DIR or plugin_dir_path(__FILE__)
            'plugin_directory' => plugin_dir_path(__FILE__),
            // should end with _ > prefix_
            'plugin_prefix' => 'plugin_prefix_',
            // templates directory inside your plugin
            'plugin_template_directory' => 'templates',
        )
    );
    $my_templater->add(
        array(
            'page' => array(
                'mstore-api-template.php' => 'Page Custom Template',
            ),
        )
    )->register();
}

//custom rest api
function flutter_users_routes()
{
    $controller = new FlutterUserController();
    $controller->register_routes();
}

add_action('rest_api_init', 'flutter_users_routes');
add_action('rest_api_init', 'mstore_check_payment_routes');
function mstore_check_payment_routes()
{
    register_rest_route('order', '/verify', array(
            'methods' => 'GET',
            'callback' => 'mstore_check_payment',
            'permission_callback' => function () {
                return true;
            },
        )
    );
}

function mstore_check_payment()
{
    return true;
}


// Add menu Setting
add_action('admin_menu', 'admin_plugin_setup_menu');

function admin_plugin_setup_menu()
{
    add_menu_page(APP_NAME, APP_NAME, 'manage_options', 'mobile-app-builder-plugin', 'admin_init', 'dashicons-art');
	add_submenu_page('mobile-app-builder-plugin', 'Settings', 'Settings', 'manage_options', 'mobile-app-builder-plugin');
	add_submenu_page('mobile-app-builder-plugin', 'Extension', 'Extension', 'manage_options', 'mobile-app-builder-plugin-ext', 'admin_ext');
}

function admin_init()
{
    load_template(dirname(__FILE__) . '/admin/mobile-setting.php');
}

function admin_ext()
{
    load_template(dirname(__FILE__) . '/admin/index.php');
}

function run_mobile_app_builder() {

	$plugin = new Mobile_App_Builder();
	$plugin->run();

}
run_mobile_app_builder();

add_filter('woocommerce_rest_prepare_product_variation_object', 'custom_woocommerce_rest_prepare_product_variation_object', 20, 3);
add_filter('woocommerce_rest_prepare_product_object', 'flutter_custom_change_product_response', 20, 3);
add_filter('woocommerce_rest_prepare_product_review', 'custom_product_review', 20, 3);
add_filter('woocommerce_rest_prepare_product_cat', 'custom_product_category', 20, 3);

function custom_product_category($response, $object, $request)
{
	 $id = $response->data['id'];
	 $children = get_term_children($id, 'product_cat');

    if(empty( $children ) ) {
    	$response->data['has_children'] = false;
    }else{
		$response->data['has_children'] = true;
	}
    return $response;
}

function custom_product_review($response, $object, $request)
{
    if(is_plugin_active('woo-photo-reviews/woo-photo-reviews.php') || is_plugin_active('woocommerce-photo-reviews/woocommerce-photo-reviews.php')){
        $id = $response->data['id'];
        $image_post_ids = get_comment_meta( $id, 'reviews-images', true );
        $image_arr = array();
        if(!is_string($image_post_ids)){
            foreach( $image_post_ids as $image_post_id ) {
                $image_arr[] = wp_get_attachment_thumb_url( $image_post_id );
            }
        }
        $response->data['images'] = $image_arr;
    }
    return $response;
}
 

function flutter_custom_change_product_response($response, $object, $request)
{
    return customProductResponse($response, $object, $request);
}

function custom_woocommerce_rest_prepare_product_variation_object($response, $object, $request)
{

    global $woocommerce_wpml;

    $is_purchased = false;
    if (isset($request['user_id'])) {
        $user_id = $request['user_id'];
        $user_data = get_userdata($user_id);
        $user_email = $user_data->user_email;
        $is_purchased = wc_customer_bought_product($user_email, $user_id, $response->data['id']);
    }
    $response->data['is_purchased'] = $is_purchased;
    if (!empty($woocommerce_wpml->multi_currency) && !empty($woocommerce_wpml->settings['currencies_order'])) {

        $price = $response->data['price'];

        foreach ($woocommerce_wpml->settings['currency_options'] as $key => $currency) {
            $rate = (float)$currency["rate"];
            $response->data['multi-currency-prices'][$key]['price'] = $rate == 0 ? $price : sprintf("%.2f", $price * $rate);
        }
    }

    return $response;
}

// Prepare data before checkout by webview
function flutter_prepare_checkout()
{

    if(empty($_GET) && isset($_SERVER['HTTP_REFERER'])){
		$url_components = parse_url($_SERVER['HTTP_REFERER']);
        if (isset($url_components['query'])) {
            parse_str($url_components['query'], $params);
            if(!empty($params)){
                $_GET = $params;
            }
        }
	}
    
    if (isset($_GET['mobile']) && isset($_GET['code'])) {

        $code = sanitize_text_field($_GET['code']);
        global $wpdb;
        $table_name = $wpdb->prefix . "mstore_checkout";
        $item = $wpdb->get_row("SELECT * FROM $table_name WHERE code = '$code'");
        if ($item) {
            $data = json_decode(urldecode(base64_decode($item->order)), true);
        } else {
            return var_dump("Can't not get the order");
        }

        $shipping = isset($data['shipping']) ? $data['shipping'] : NULL;
        $billing = isset($data['billing']) ? $data['billing'] : $shipping;

        if (isset($data['token'])) {
            // Validate the cookie token
            $userId = validateCookieLogin($data['token']);
            if(!is_wp_error($userId)){
                if (isset($billing)) {
                    if(isset($billing["first_name"]) && !empty($billing["first_name"])){
                        update_user_meta($userId, 'billing_first_name', $billing["first_name"]);
                        update_user_meta($userId, 'shipping_first_name', $billing["first_name"]);
                    }
                    if(isset($billing["last_name"]) && !empty($billing["last_name"])){
                        update_user_meta($userId, 'billing_last_name', $billing["last_name"]);
                        update_user_meta($userId, 'shipping_last_name', $billing["last_name"]);
                    }
                    if(isset($billing["company"]) && !empty($billing["company"])){
                        update_user_meta($userId, 'billing_company', $billing["company"]);
                        update_user_meta($userId, 'shipping_company', $billing["company"]);
                    }
                    if(isset($billing["address_1"]) && !empty($billing["address_1"])){
                        update_user_meta($userId, 'billing_address_1', $billing["address_1"]);
                        update_user_meta($userId, 'shipping_address_1', $billing["address_1"]);
                    }
                    if(isset($billing["address_2"]) && !empty($billing["address_2"])){
                        update_user_meta($userId, 'billing_address_2', $billing["address_2"]);
                        update_user_meta($userId, 'shipping_address_2', $billing["address_2"]);
                    }
                    if(isset($billing["city"]) && !empty($billing["city"])){
                        update_user_meta($userId, 'billing_city', $billing["city"]);
                        update_user_meta($userId, 'shipping_city', $billing["city"]);
                    }
                    if(isset($billing["state"]) && !empty($billing["state"])){
                        update_user_meta($userId, 'billing_state', $billing["state"]);
                        update_user_meta($userId, 'shipping_state', $billing["state"]);
                    }
                    if(isset($billing["postcode"]) && !empty($billing["postcode"])){
                        update_user_meta($userId, 'billing_postcode', $billing["postcode"]);
                        update_user_meta($userId, 'shipping_postcode', $billing["postcode"]);
                    }
                    if(isset($billing["country"]) && !empty($billing["country"])){
                        update_user_meta($userId, 'billing_country', $billing["country"]);
                        update_user_meta($userId, 'shipping_country', $billing["country"]);
                    }
                    if(isset($billing["email"]) && !empty($billing["email"])){
                        update_user_meta($userId, 'billing_email', $billing["email"]);
                        update_user_meta($userId, 'shipping_email', $billing["email"]);
                    }
                    if(isset($billing["phone"]) && !empty($billing["phone"])){
                        update_user_meta($userId, 'billing_phone', $billing["phone"]);
                        update_user_meta($userId, 'shipping_phone', $billing["phone"]);
                    }
                } else {
                    $billing = [];
                    $shipping = [];
    
                    $billing["first_name"] = get_user_meta($userId, 'billing_first_name', true);
                    $billing["last_name"] = get_user_meta($userId, 'billing_last_name', true);
                    $billing["company"] = get_user_meta($userId, 'billing_company', true);
                    $billing["address_1"] = get_user_meta($userId, 'billing_address_1', true);
                    $billing["address_2"] = get_user_meta($userId, 'billing_address_2', true);
                    $billing["city"] = get_user_meta($userId, 'billing_city', true);
                    $billing["state"] = get_user_meta($userId, 'billing_state', true);
                    $billing["postcode"] = get_user_meta($userId, 'billing_postcode', true);
                    $billing["country"] = get_user_meta($userId, 'billing_country', true);
                    $billing["email"] = get_user_meta($userId, 'billing_email', true);
                    $billing["phone"] = get_user_meta($userId, 'billing_phone', true);
    
                    $shipping["first_name"] = get_user_meta($userId, 'shipping_first_name', true);
                    $shipping["last_name"] = get_user_meta($userId, 'shipping_last_name', true);
                    $shipping["company"] = get_user_meta($userId, 'shipping_company', true);
                    $shipping["address_1"] = get_user_meta($userId, 'shipping_address_1', true);
                    $shipping["address_2"] = get_user_meta($userId, 'shipping_address_2', true);
                    $shipping["city"] = get_user_meta($userId, 'shipping_city', true);
                    $shipping["state"] = get_user_meta($userId, 'shipping_state', true);
                    $shipping["postcode"] = get_user_meta($userId, 'shipping_postcode', true);
                    $shipping["country"] = get_user_meta($userId, 'shipping_country', true);
                    $shipping["email"] = get_user_meta($userId, 'shipping_email', true);
                    $shipping["phone"] = get_user_meta($userId, 'shipping_phone', true);
    
                    if (isset($billing["first_name"]) && !isset($shipping["first_name"])) {
                        $shipping = $billing;
                    }
                    if (!isset($billing["first_name"]) && isset($shipping["first_name"])) {
                        $billing = $shipping;
                    }
                }
    
                // Check user and authentication
                $user = get_userdata($userId);
                if ($user && (!is_user_logged_in() || get_current_user_id() != $userId)) {
                    wp_set_current_user($userId, $user->user_login);
                    wp_set_auth_cookie($userId);
    
                    header("Refresh:0");
                }
            }
        } else {
            if (is_user_logged_in()) {
                wp_logout();
                wp_set_current_user(0);
                header("Refresh:0");
            }
        }

        if (is_plugin_active('woocommerce/woocommerce.php') == true) {
            //header("Content-Security-Policy: frame-ancestors 'self' *.yourdomain.com");
            global $woocommerce;
            WC()->session->set('refresh_totals', true);
            WC()->cart->empty_cart();

            $products = $data['line_items'];

            foreach ($products as $product) {
                $productId = absint($product['product_id']);

                $quantity = $product['quantity'];
                $variationId = isset($product['variation_id']) ? $product['variation_id'] : "";

                $attributes = [];
                if (isset($product["meta_data"])) {
                    foreach ($product["meta_data"] as $item) {
                        if($item["value"] != null){
                            $attributes[strtolower($item["key"])] = $item["value"];
                        }
                    }
                }

                if (isset($product['addons'])) {
                    $_POST = $product['addons'];
                }
                
                // Check the product variation
                if (!empty($variationId)) {
                    $productVariable = new WC_Product_Variable($productId);
                    $listVariations = $productVariable->get_available_variations();
                    foreach ($listVariations as $vartiation => $value) {
                        if ($variationId == $value['variation_id']) {
                            $attributes = array_merge($value['attributes'], $attributes);
                            $woocommerce->cart->add_to_cart($productId, $quantity, $variationId, $attributes);
                        }
                    }
                } else {
                    parseMetaDataForBookingProduct($product);
                    $cart_item_data = array();
                    if (is_plugin_active('woo-wallet/woo-wallet.php')) {
                        $wallet_product = get_wallet_rechargeable_product();
                        if ($wallet_product->get_id() == $productId) {
                            $cart_item_data['recharge_amount'] = $product['total'];
                        }
                    }

                    $woocommerce->cart->add_to_cart($productId, $quantity, 0, $attributes, $cart_item_data);

                }
            }

            if (isset($shipping)) {
                $woocommerce->customer->set_shipping_first_name($shipping["first_name"]);
                $woocommerce->customer->set_shipping_last_name($shipping["last_name"]);
                $woocommerce->customer->set_shipping_company($shipping["company"]);
                $woocommerce->customer->set_shipping_address_1($shipping["address_1"]);
                $woocommerce->customer->set_shipping_address_2($shipping["address_2"]);
                $woocommerce->customer->set_shipping_city($shipping["city"]);
                $woocommerce->customer->set_shipping_state($shipping["state"]);
                $woocommerce->customer->set_shipping_postcode($shipping["postcode"]);
                $woocommerce->customer->set_shipping_country($shipping["country"]);
            }

            if (isset($billing)) {
                $woocommerce->customer->set_billing_first_name($billing["first_name"]);
                $woocommerce->customer->set_billing_last_name($billing["last_name"]);
                $woocommerce->customer->set_billing_company($billing["company"]);
                $woocommerce->customer->set_billing_address_1($billing["address_1"]);
                $woocommerce->customer->set_billing_address_2($billing["address_2"]);
                $woocommerce->customer->set_billing_city($billing["city"]);
                $woocommerce->customer->set_billing_state($billing["state"]);
                $woocommerce->customer->set_billing_postcode($billing["postcode"]);
                $woocommerce->customer->set_billing_country($billing["country"]);
                $woocommerce->customer->set_billing_email($billing["email"]);
                $woocommerce->customer->set_billing_phone($billing["phone"]);
            }

            if (!empty($data['coupon_lines'])) {
                $coupons = $data['coupon_lines'];
                foreach ($coupons as $coupon) {
                    $woocommerce->cart->add_discount($coupon['code']);
                }
            }

            if (!empty($data['shipping_lines'])) {
                $shippingLines = $data['shipping_lines'];
                $shippingMethod = $shippingLines[0]['method_id'];
                WC()->session->set('chosen_shipping_methods', array($shippingMethod));
            }
            if (!empty($data['payment_method'])) {
                WC()->session->set('chosen_payment_method', $data['payment_method']);
            }

            if (isset($data['customer_note']) && !empty($data['customer_note'])) {
                $_POST["order_comments"] = sanitize_text_field($data['customer_note']);
                $checkout_fields = WC()->checkout->__get("checkout_fields");
                $checkout_fields["order"] = ["order_comments" => ["type" => "textarea", "class" => [], "label" => "Order notes", "placeholder" => "Notes about your order, e.g. special notes for delivery."]];
                WC()->checkout->__set("checkout_fields", $checkout_fields);
            }
        }
    }

    if (isset($_GET['cookie'])) {
        $cookie = urldecode(base64_decode(sanitize_text_field($_GET['cookie'])));
        $userId = validateCookieLogin($cookie);
        if (!is_wp_error($userId)) {
            $user = get_userdata($userId);
            if ($user !== false) {
                wp_set_current_user($userId, $user->user_login);
                wp_set_auth_cookie($userId);
                if (isset($_GET['vendor_admin'])) {
                    global $wp;
                    $request = $wp->request;
                    wp_redirect(esc_url_raw(home_url("/" . $request)));
                    die;
                }
            }
        }
    }
}

// Add product image to order
add_filter('woocommerce_rest_prepare_shop_order_object', 'custom_woocommerce_rest_prepare_shop_order_object', 10, 1);
function custom_woocommerce_rest_prepare_shop_order_object($response)
{
    if (empty($response->data) || empty($response->data['line_items'])) {
        return $response;
    }
    $api = new WC_REST_Products_Controller();
    $req = new WP_REST_Request('GET');
    $line_items = [];
    foreach ($response->data['line_items'] as $item) {
        $product_id = $item['product_id'];
        $req->set_query_params(["id" => $product_id]);
        $res = $api->get_item($req);
        if (is_wp_error($res)) {
            $item["product_data"] = null;
        } else {
            $item["product_data"] = $res->get_data();
        }
        $line_items[] = $item;

    }
    $response->data['line_items'] = $line_items;

    // Get the value
    $bacs_info = get_option( 'woocommerce_bacs_accounts');
    $response->data['bacs_info'] = $bacs_info;
    
    return $response;
}


function mstore_register_order_refund_requested_order_status()
{
    register_post_status('wc-refund-req', array(
        'label' => esc_attr__('Refund Requested'),
        'public' => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list' => true,
        'exclude_from_search' => false,
        'label_count' => _n_noop('Refund requested <span class="count">(%s)</span>', 'Refund requested <span class="count">(%s)</span>')
    ));
}

add_action('init', 'mstore_register_order_refund_requested_order_status');


function mstore_add_custom_order_statuses($order_statuses)
{
    // Create new status array.
    $new_order_statuses = array();
    // Loop though statuses.
    foreach ($order_statuses as $key => $status) {
        // Add status to our new statuses.
        $new_order_statuses[$key] = $status;
        // Add our custom statuses.
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-refund-req'] = esc_attr__('Refund Requested');
        }
    }

    return $new_order_statuses;
}

add_filter('wc_order_statuses', 'mstore_add_custom_order_statuses');


function custom_status_bulk_edit($actions)
{
    // Add order status changes.
    $actions['mark_refund-req'] = __('Change status to refund requested');

    return $actions;
}

add_filter('bulk_actions-edit-shop_order', 'custom_status_bulk_edit', 20, 1);