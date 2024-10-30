<?php
/**
 * Runs the activation scripts upon plugin activation
 */
register_activation_hook(__FILE__,'sw_kushal_erp_sync_activation');
function sw_kushal_erp_sync_activation()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `{$wpdb->base_prefix}erp_sync_logs` (
      id int(11) NOT NULL AUTO_INCREMENT,
      module varchar(255) NOT NULL,
      time_taken varchar(255) NOT NULL,
      url_requested longtext NOT NULL,
      error_code longtext NOT NULL,
      created_at datetime NOT NULL,
      modified_at datetime NOT NULL,
      PRIMARY KEY  (id)
      ) $charset_collate;";

    $wpdb->query($sql);

    $sql2 =  "CREATE TABLE `{$wpdb->base_prefix}erp_customer_mapping` (
      id int(11) NOT NULL AUTO_INCREMENT,
      wordpress_id int(11) NOT NULL,
      erp_id int(11) NOT NULL,
      created_at datetime NOT NULL,
      modified_at datetime NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $wpdb->query($sql2);


    $sql3 =  "CREATE TABLE `{$wpdb->base_prefix}erp_category_mapping` (
      id int(11) NOT NULL AUTO_INCREMENT,
      wp_category_id int(11) NOT NULL,
      category_name varchar(255) NOT NULL,
      erp_category_id int(11) NOT NULL,
      created_at datetime NOT NULL,
      modified_at datetime NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $wpdb->query($sql3);


    $sql4 =  "CREATE TABLE `{$wpdb->base_prefix}erp_item_mapping` (
      id int(11) NOT NULL AUTO_INCREMENT,
      wp_item_id int(11) NOT NULL,
      erp_item_id int(11) NOT NULL,
      status varchar(255) NOT NULL,
      createdby varchar(255) NOT NULL,
      created_at datetime NOT NULL,
      modified_at datetime NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $wpdb->query($sql4);

    $sql5 =  "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}erp_category` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `erp_cat_id` int(11) NOT NULL,
      `category_id` int(11) NOT NULL,
      `category_name` varchar(255) NOT NULL,
      `created_at` datetime NOT NULL,
      `modified_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) $charset_collate;";

    $wpdb->query($sql5);

    $sql6 = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}erp_settings`(
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `module` varchar(500) NOT NULL,
      `setting_module` varchar(500) NOT NULL,
      `setting_value` varchar(500) NOT NULL,
      `created_at` datetime NOT NULL,
      `modified_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
      ) $charset_collate;";

    $wpdb->query($sql6);

    $date = date("Y-m-d H:i:s");
    $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('item','createitemby','byitems','$date','$date')";
    $wpdb->query($query);

    $sql7 =  "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}erp_order_mapping` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `wp_order_id` int(11) NOT NULL,
      `erp_order_id` int(11) NOT NULL,
      `status` varchar(255) NOT NULL,
      `sync_date` datetime NOT NULL,
      `request` varchar(1000) NOT NULL,
      `response` longtext NOT NULL,
      `notification` int(11) NOT NULL,
      `created_at` datetime NOT NULL,
      `modified_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) $charset_collate;";

    $wpdb->query($sql7);

    $sql8 = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}erp_color_image_mapping` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `wp_product_id` varchar (255) NOT NULL,
      `erp_product_id` varchar (255) NOT NULL,
      `colorname` varchar (255) NOT NULL,
      `imageid` varchar (255) NOT NULL,
      `imagename` varchar (255) NOT NULL,
      `created_at` datetime NOT NULL,
      `modified_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) $charset_collate;";

    $wpdb->query($sql8);
}


/**
 * Runs deactivation scripts upon plugin deactivation
 */
register_deactivation_hook(__FILE__,'sw_kushal_erp_sync_deactivation');
function sw_kushal_erp_sync_deactivation(){
    global $wpdb;

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_category";
    $wpdb->query($sql);

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_category_mapping";
    $wpdb->query($sql);

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_color_image_mapping";
    $wpdb->query($sql);

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_customer_mapping";
    $wpdb->query($sql);

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_item_mapping";
    $wpdb->query($sql);

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_order_mapping";
    $wpdb->query($sql);

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_settings";
    $wpdb->query($sql);

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_store_mapping";
    $wpdb->query($sql);

    $sql = "DROP TABLE {$wpdb->base_prefix}erp_sync_logs";
    $wpdb->query($sql);
}


define('BASE_PATH', plugin_dir_path(__FILE__));
define('BASE_URL', plugin_dir_url(__FILE__));

require_once BASE_PATH . 'vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;


/**
 * @package sync
 */
/*
Plugin Name: Connect Your itsperfect
Description: Plugin for synhcronisation between Woocommerce and It's perfect [https://www.itsperfect-solutions.com/]
Version: 1.0.0
Author: Scrumwheel
Author URI: https://scrumwheel.com/
License: GPLv2 or later
Text-Domain: sync
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}





/**
 * Provides settings link beside the deactivate button in "installed plugins" page.
 */
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );
function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=sync-settings">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
    return $links;
}



/**
 * Creates menu for the plugin in the admin section.
 */
add_action('admin_menu', 'sync_register_options_page');
function sync_register_options_page() {
    add_menu_page('Its erp sync', 'Itsperfect Erp Sync', 'manage_options', 'sync-settings', 'sync_options_page');
    add_submenu_page( 'sync-settings', 'Order Management', 'Order Management', 'manage_options', 'order_management', 'orders_sync_page');
    add_submenu_page( 'sync-settings', 'Product Management', 'Product Management', 'manage_options', 'product_management', 'update_products');
    add_submenu_page( 'sync-settings', 'Settings', 'Settings', 'manage_options', 'settings', 'erp_sync_logs');

}


/**
 * Attaches necessary js & css files
 */
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
function load_custom_wp_admin_style($hook) {
    if($hook == "toplevel_page_sync-settings" || $hook == "itsperfect-erp-sync_page_order_management" || $hook == "itsperfect-erp-sync_page_product_management" || $hook == "itsperfect-erp-sync_page_settings"){
        wp_enqueue_style( 'bootstrap', plugins_url('assets/css/bootstrap.min.css', __FILE__) );
        wp_enqueue_style( 'jquery-confirm', plugins_url('assets/css/jquery-confirm.min.css', __FILE__) );
        wp_enqueue_style( 'dataTables.bootstrap', plugins_url('assets/css/dataTables.bootstrap.min.css', __FILE__) );

        wp_enqueue_script( 'bootstrap', plugins_url( 'assets/js/bootstrap.min.js', __FILE__ ) );
        wp_enqueue_script( 'jquery-confirm', plugins_url( 'assets/js/jquery-confirm.min.js', __FILE__ ) );
        wp_enqueue_script( 'datatables', plugins_url( 'assets/js/jquery.dataTables.min.js', __FILE__ ) );
        wp_enqueue_script( 'datatable-bootstrap', plugins_url( 'assets/js/dataTables.bootstrap.min.js', __FILE__ ) );
    }
    else{
      return;
    }
}


/**
 * For the curl call of the dynamic.
 * type variable represents is method GET,POST.
 * url variable represents the curl url pass.
 * data variable represents the pass the curl data.
 */
function dynamic_curl_call_request($type,$url,$data){

  if($type == 'GET'){
    $request = wp_remote_get($url,$data);
  }else if($type == 'POST'){
    $request = wp_remote_post($url,$data);
  }else{
    $request = '';
  }

  $response_status = wp_remote_retrieve_response_code($request);
  if($response_status == 200){
    $response_body = wp_remote_retrieve_body( $request );
    $response = [
      "status" => $response_status,
      "body" => $response_body
    ];
  }else{
    $response_body = wp_remote_retrieve_body( $request );
    $response = [
      "status" => $response_status,
      "body" => $response_body
    ];
  }
  
  return $response;
}


/**
 * Front page of the plugin.settings-page.
 */
function sync_options_page()
{

    global $wpdb;

    /**
     * Retrives settings and sets them into global variables
     */
    $GLOBALS['url'] = get_site_url();

    $queryck = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumerkey'";
    $wc_consumerkey = $wpdb->get_results($queryck);

    if(empty($wc_consumerkey)){
        $consumerkey =  "";
    }else{
        $consumerkey =  $wc_consumerkey[0]->setting_value; //for consumerkey
    }

    $querycsk = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumersecret'";
    $wc_consumersecret = $wpdb->get_results($querycsk);

    if(empty($wc_consumersecret)){
        $consumersecret = "";
    }else{
        $consumersecret = $wc_consumersecret[0]->setting_value; //for consumersecret key
    }

    $GLOBALS['consumerkey'] = $consumerkey;
    $GLOBALS['consumersecret'] = $consumersecret;



    $queryurl = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_url'";
    $api_auth = $wpdb->get_results($queryurl);

    if(empty($api_auth)){
        $apiStart =  "";
    }else{
        $apiStart =  $api_auth[0]->setting_value; //for consumerkey
    }

    $querytoken = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_token'";
    $api_token = $wpdb->get_results($querytoken);

    if(empty($api_token)){
        $token = "";
    }else{
        $token = $api_token[0]->setting_value; //for api token key
    }

    $GLOBALS['apiStart'] = $apiStart;
    $GLOBALS['token'] = $token;

    if($GLOBALS['consumerkey'] == "" || $GLOBALS['consumersecret'] == "" || $GLOBALS['apiStart'] == "" || $GLOBALS['token'] == ""){
        $url = $GLOBALS['url']."/wp-admin/admin.php?page=settings";
        wp_redirect( $url);
    }

    $gif = BASE_PATH."/ajax-loader.gif";
    $query = "SELECT * from {$wpdb->base_prefix}erp_sync_logs where module = 'products' order by created_at desc limit 1";
    $x = $wpdb->get_results($query);
    $product_last_sync = "Never";
    if(!empty($x)){
        $product_last_sync = Date("Y-m-d H:i",strtotime($x[0]->created_at));
    }

    $query = "SELECT * from {$wpdb->base_prefix}erp_sync_logs where module = 'variations' order by created_at desc limit 1";
    $x = $wpdb->get_results($query);
    $variation_last_sync = "Never";
    if(!empty($x)){
        $variation_last_sync = Date("Y-m-d H:i",strtotime($x[0]->created_at));
    }

    $query = "SELECT * from {$wpdb->base_prefix}erp_sync_logs where module = 'update product variations' order by created_at desc limit 1";
    $x = $wpdb->get_results($query);
    $update_variation_last_sync = "Never";
    if(!empty($x)){
        $update_variation_last_sync = Date("Y-m-d H:i",strtotime($x[0]->created_at));
    }

    $query = "SELECT * from {$wpdb->base_prefix}erp_sync_logs where module = 'categories' order by created_at desc limit 1";
    $x = $wpdb->get_results($query);
    $itemgroup_last_sync = "Never";
    if(!empty($x)){
        $itemgroup_last_sync = Date("Y-m-d H:i",strtotime($x[0]->created_at));
    }
    $query = "SELECT * from {$wpdb->base_prefix}erp_sync_logs where module = 'update_product_stocks' order by created_at desc limit 1";
    $x = $wpdb->get_results($query);
    $update_stock_last_sync = "Never";
    if(!empty($x)){
        $update_stock_last_sync = Date("Y-m-d H:i",strtotime($x[0]->created_at));
    }

    $query = "SELECT * from {$wpdb->base_prefix}erp_sync_logs where module = 'multicurrency' order by created_at desc limit 1";
    $x = $wpdb->get_results($query);
    $multicurrency_last_sync = "Never";
    if(!empty($x)){
        $multicurrency_last_sync = Date("Y-m-d H:i",strtotime($x[0]->created_at));
    }

    //for disable all button if token and url not and woocommerce auth not provides.

    $queryck = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumerkey'";
    $wc_consumerkey = $wpdb->get_results($queryck);

    if(empty($wc_consumerkey)){
        $consumerkey =  "";
    }else{
        $consumerkey =  $wc_consumerkey[0]->setting_value; //for consumerkey
    }

    $querycsk = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumersecret'";
    $wc_consumersecret = $wpdb->get_results($querycsk);

    if(empty($wc_consumersecret)){
        $consumersecret = "";
    }else{
        $consumersecret = $wc_consumersecret[0]->setting_value; //for consumersecret key
    }

    $queryurl = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_url'";
    $api_auth = $wpdb->get_results($queryurl);

    if(empty($api_auth)){
        $apiStart =  "";
    }else{
        $apiStart =  $api_auth[0]->setting_value; //for consumerkey
    }

    $querytoken = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_token'";
    $api_token = $wpdb->get_results($querytoken);

    if(empty($api_token)){
        $token = "";
    }else{
        $token = $api_token[0]->setting_value; //for api token key
    }


    //items api for the count dropdown
    $type = "GET";
    $url = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
    $response_curl = dynamic_curl_call_request($type,$url,$data);
     
    if($response_curl['status'] == 200){
        $mydata = json_decode($response_curl['body']);
        $count = count($mydata->items);
    }else{
      echo "cURL Error #:" . $response_curl['body'];
    }

    
    $consumerkey = $GLOBALS['consumerkey'];
    $consumersecret = $GLOBALS['consumersecret'];
    $apiStart = $GLOBALS['apiStart'];
    $token = $GLOBALS['token'];

    if($apiStart && $token && $consumerkey && $consumersecret){
        $disbled = "";
    }else{ $disbled = "disabled"; } ?>

    <br><br><br>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="wrap">
                    <div class="col-md-9">
                        <div class="wrap">
                            <h1 class="wp-heading-inline">It's Perfect ERP Sync</h1>
                        </div>
                    </div>
                    <div class="col-md-3"></div>
                </div>
                <p></p>
                <table class="wp-list-table widefat fixed striped table-striped">
                    <tbody>
                    <tr class="alternate">
                        <td class="column-columnname">Sync your ERP ItemGroups with Woocommerce.</td>
                        <td class="column-columnname">
                            <button class="button button-primary" <?php echo $disbled; ?> id="syncitemgroup">Create Categories</button>
                            <img style="display:none;" src="<?php echo $gif; ?>" id="itemgrouploader">
                        </td>
                        <td></td>
                        <td></td>
                        <td class="column-columnname">
                            <span class="strong">(Last sync done at  <?php echo $itemgroup_last_sync; ?>)</span>
                        </td>

                    </tr>
                    <tr class="alternate">
                        <td class="column-columnname">Sync your ERP Items with Woocommerce.</td>
                        <td>
                            <?php
                            $sql = "SELECT * FROM {$wpdb->base_prefix}erp_settings limit 1";
                            $result = $wpdb->get_results($sql);
                            $createby  = $result[0]->setting_value;
                            $byitemsselected = "";
                            $bycolorselected = "";
                            if($createby == "byitems"){
                                $byitemsselected = "selected";
                            }
                            if($createby == "bycolors"){
                                $bycolorselected = "selected";
                            }
                            ?>
                            <select id="itemsby" class="form-control">
                                <option value="byitems" <?php echo $byitemsselected; ?> >By Items</option>
                                <option value="bycolors" <?php echo $bycolorselected; ?> >By Colors</option>
                            </select>
                        </td>
                        <td class="column-columnname">
                            <button class="button button-primary" <?php echo $disbled; ?> id="syncitem">Create Items</button>
                            <img style="display:none;" src="<?php echo $gif; ?>" id="itemloader">
                        </td>
                        <td>
                            <select class="counts">
                                <?php
                                for($i=1,$x=1; $i <= $count; $i++){
                                    if($i % 1 == 0){
                                        ?>
                                        <option value="<?php echo $x."_".$i; ?>"><?php echo $x." to ". $i; ?></option>
                                        <?php $x = $i+1;
                                    }
                                }
                                if($count- $x > 0){ ?>
                                    <option value="<?php echo $x."_".$count; ?>"><?php echo $x." to ". ($count); ?></option>
                                <?php }
                                ?>
                            </select>
                        </td>
                        <td class="column-columnname">
                            <span class="strong">(Last sync done at  <?php echo $product_last_sync; ?>)</span>
                        </td>
                    </tr>
                    <tr class="alternate">
                        <?php
                        $query = "SELECT count(*) as itemcount from {$wpdb->base_prefix}erp_item_mapping";
                        $result = $wpdb->get_results($query);
                        $itemcount = $result[0]->itemcount;
                        ?>
                        <td class="column-columnname">Create variations for Items.</td>
                        <td>
                            <button class="button button-primary" <?php echo $disbled; ?> id="createvariations"  >Create Variations</button>
                            <img style="display:none;" src="<?php echo $gif; ?>" id="variationloader">
                        </td>
                        <td></td>
                        <td></td>
                        <td class="column-columnname">
                            <span class="strong">(Last sync done at  <?php echo $variation_last_sync; ?>)</span>
                        </td>
                    </tr>
                    <tr class="alternate">
                        <?php
                        $query = "SELECT count(*) as itemcount from {$wpdb->base_prefix}erp_item_mapping";
                        $result = $wpdb->get_results($query);
                        $itemcount = $result[0]->itemcount;
                        ?>
                        <td class="column-columnname">Update variations for Items.</td>
                        <td>
                            <button class="button button-primary" <?php echo $disbled; ?> id="updatevariations" >Update Variations</button>
                            <img style="display:none;" src="<?php echo $gif; ?>" id="variationupdateloader">
                        </td>
                        <td>
                            <?php
                            if($itemcount != 0){
                                ?>
                                <select  id="updatevariationcount">
                                    <?php
                                    for($i=1,$x=1; $i <= $itemcount; $i++){
                                        if($i % 5 == 0){
                                            ?>
                                            <option value="<?php echo $x."_".$i; ?>"><?php echo $x." to ". $i; ?></option>
                                            <?php $x = $i+1;
                                        }
                                    }
                                    if($itemcount - $x > 0){ ?>
                                        <option value="<?php echo $x."_".$itemcount; ?>"><?php echo $x." to ". ($itemcount-1); ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $x."_".$itemcount; ?>"><?php echo $x." to ". $itemcount; ?></option>
                                    <?php }
                                    ?>
                                </select>
                            <?php } ?>
                        </td>
                        <td></td>
                        <td class="column-columnname">
                            <span class="strong">(Last sync done at  <?php echo $update_variation_last_sync; ?>)</span>
                        </td>
                    </tr>
                    <tr class="alternate">
                        <td class="column-columnname">Update stocks for Items.</td>
                        <td>
                            <button class="button button-primary" <?php echo $disbled; ?> id="updatestocks">Update Stocks</button>
                            <img style="display:none;" src="<?php echo $gif; ?>" id="updatestocksloader">
                        </td>
                        <td>
                            <?php
                            $query = "SELECT count(*) as count from {$wpdb->base_prefix}erp_item_mapping";
                            $result = $wpdb->get_results($query);
                            if(empty($result)){
                                $count = 0;
                            }
                            else{
                                $count = $result[0]->count;
                            }
                            ?>
                            <select class="stockcount">
                                <?php
                                for($i=1,$x=1; $i <= $count; $i++){
                                    if($i % 100 == 0){
                                        ?>
                                        <option value="<?php echo $x."_".$i; ?>"><?php echo $x." to ". $i; ?></option>
                                        <?php $x = $i+1;
                                    }
                                }
                                if($count- $x >= 0){ ?>
                                    <option value="<?php echo $x."_".$count; ?>"><?php echo $x." to ". ($count); ?></option>
                                <?php } 
                                ?>
                            </select>
                        </td>
                        <td></td>
                        <td class="column-columnname">
                            <span class="strong">(Last sync done at  <?php echo $update_stock_last_sync; ?>)</span>
                        </td>
                    </tr>
                    <tr class="alternate">
                        <td class="column-columnname">Update tracking info for orders.</td>
                        <td>
                            <button class="button button-primary" <?php echo $disbled; ?>  id="updatetrackinginfo">Update Tracking status</button>
                            <img style="display:none;" src="<?php echo $gif; ?>" id="updatetrackingloader">
                        </td>
                        <td></td>
                        <td></td>
                        <td class="column-columnname">
                            <span class="strong">(Last sync done at  <?php echo $update_stock_last_sync; ?>)</span>
                        </td>
                    </tr>

                    <tr class="alternate">
                        <td class="column-columnname">Get multi currency prices</td>
                        <td>
                            <button class="button button-primary" <?php echo $disbled; ?>  id="multicurrency">Get Multi currency prices</button>
                            <img style="display:none;" src="<?php echo $gif; ?>" id="multicurrencyloader">
                        </td>
                        <td></td>
                        <td></td>
                        <td class="column-columnname">
                            <span class="strong">(Last sync done at  <?php echo $multicurrency_last_sync; ?>)</span>
                        </td>
                    </tr>
                    <tr class="alternate">
                        <td class="column-columnname">Update Categories</td>
                        <td>
                            <button class="button button-primary" <?php echo $disbled; ?>  id="update-category">Update Categories</button>
                            <img style="display:none;" src="<?php echo $gif; ?>" id="update-category-loader">
                        </td>
                        <td></td>
                        <td></td>
                        <td class="column-columnname">

                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="notificationmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Notification</h5>
                </div>
                <div class="modal-body" id="modalcontent">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php
}


/**
 * Displays the logs for erp_sync
 */
function erp_sync_logs(){
    global $wpdb;

    //for disable all button if token and url not and woocommerce auth not provides.
    $queryck = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumerkey'";
    $wc_consumerkey = $wpdb->get_results($queryck);

    if(empty($wc_consumerkey)){
        $consumerkey =  "";
    }else{
        $consumerkey =  $wc_consumerkey[0]->setting_value; //for consumerkey
    }

    $querycsk = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumersecret'";
    $wc_consumersecret = $wpdb->get_results($querycsk);

    if(empty($wc_consumersecret)){
        $consumersecret = "";
    }else{
        $consumersecret = $wc_consumersecret[0]->setting_value; //for consumersecret key
    }

    $queryurl = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_url'";
    $api_auth = $wpdb->get_results($queryurl);

    if(empty($api_auth)){
        $apiStart =  "";
    }else{
        $apiStart =  $api_auth[0]->setting_value; //for consumerkey
    }

    $querytoken = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_token'";
    $api_token = $wpdb->get_results($querytoken);

    if(empty($api_token)){
        $token = "";
    }else{
        $token = $api_token[0]->setting_value; //for api token key
    }



    $gif = BASE_URL."ajax-loader.gif";
    ?>
    <style>
        hr {
            width: -webkit-fill-available;
            border-top: 1px solid #ddd;
        }body{
             background-color: #f1f1f1 !important;
         }
    </style>
    <br><br><br>

    <input type="hidden" id="logmodule" value="true">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <!-- Nav tabs -->
                <div class="wrap">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link  active" href="#profile" role="tab" data-toggle="tab" aria-selected="true">General</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#logs" role="tab" data-toggle="tab">Logs</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="profile">
                            <br><br>
                            <div class="row">
                                <div class="col-md-6">

                                    <div class="row view-row">
                                        <div class="col-md-4"><b>ConsumerKey :</b></div>
                                        <div class="col-md-8"><input type="text" style="width: -webkit-fill-available;" id="woocommerceKeys" name="woocommerceKeys" value="<?php echo $consumerkey; ?>"></div>
                                    </div>
                                    <br>
                                    <div class="row view-row">
                                        <div class="col-md-4"><b>ConsumerSecretkeys :</b></div>
                                        <div class="col-md-8"><input type="text" style="width: -webkit-fill-available;" id="woocommerceSecretkeys" name="woocommerceSecretkeys" value="<?php echo $consumersecret; ?>"></div>
                                    </div>
                                    <br>
                                    <div class="row view-row">
                                        <div class="col-md-4"><b>Url :</b></div>
                                        <div class="col-md-8"><input type="text" style="width: -webkit-fill-available;" id="apiUrl" name="apiurl" value="<?php echo $apiStart; ?>"></div>
                                    </div>
                                    <br>
                                    <div class="row view-row">
                                        <div class="col-md-4"><b>Token :</b></div>
                                        <div class="col-md-8"><input type="text" style="width: -webkit-fill-available;" id="token" name="token" value="<?php echo $token; ?>"></div>
                                    </div>
                                    <br>
                                    <button type="submit" id="woocommSubmit"  name="save" class="btn btn-primary"> Save</button>
                                    <br>
                                    <p></p><p></p>
                                </div>
                                <br><br><br>
                                <hr>
                                <div class="col-md-6">
                                    <div class="row view-row">
                                        <div class="col-md-4"><b>Reset</b></div>
                                        <div class="col-md-8">
                                            <a href="#" class="btn btn-info" style="width: 95px;" id="clearProducts">
                                                Reset All
                                            </a>
                                            <p>Note : Clears All Products and categories From WooCommerce.</p>
                                            <img style="display:none;" src="<?php echo $gif; ?>" id="clearProductloader">
                                        </div>

                                    </div>
                                </div>


                            </div>
                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="logs">
                            <br><br>
                            <div class="row">
                                <div class="col-md-10">
                                    <h3 style="margin-top: 0px;margin-bottom: 20px;">Logs</h3>
                                </div>
                                <div class="col-md-2"></div>
                            </div>
                            <!-- <hr style="margin-top:unset;"> -->
                            <table class="wp-list-table widefat fixed striped logsofmodules">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Module</th>
                                    <th>Request</th>
                                    <th>Response</th>
                                    <th>Created Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                global $wpdb;
                                $sql = "SELECT id,module,url_requested,error_code,created_at  from {$wpdb->base_prefix}erp_sync_logs order by id desc";
                                $result = $wpdb->get_results($sql);
                                foreach ($result as $key => $value) { ?>
                                    <tr>
                                        <td><?php echo $value->id; ?></td>
                                        <td><?php echo $value->module; ?></td>
                                        <td><?php echo $value->url_requested; ?></td>
                                        <td><?php echo $value->error_code; ?></td>
                                        <td><?php echo $value->created_at; ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- <div role="tabpanel" class="tab-pane fade" id="references">ccc</div> -->




                </div>
            </div>
        </div>
    </div>
    <?php
}


/**
 * Displays the page for update products.
 */
function update_products(){
    global $wpdb;


    //for disable all button if token and url not and woocommerce auth not provides.
    $queryck = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumerkey'";
    $wc_consumerkey = $wpdb->get_results($queryck);

    if(empty($wc_consumerkey)){
        $consumerkey =  "";
    }else{
        $consumerkey =  $wc_consumerkey[0]->setting_value; //for consumerkey
    }

    $querycsk = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumersecret'";
    $wc_consumersecret = $wpdb->get_results($querycsk);

    if(empty($wc_consumersecret)){
        $consumersecret = "";
    }else{
        $consumersecret = $wc_consumersecret[0]->setting_value; //for consumersecret key
    }

    $queryurl = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_url'";
    $api_auth = $wpdb->get_results($queryurl);

    if(empty($api_auth)){
        $apiStart =  "";
    }else{
        $apiStart =  $api_auth[0]->setting_value; //for consumerkey
    }

    $querytoken = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_token'";
    $api_token = $wpdb->get_results($querytoken);

    if(empty($api_token)){
        $token = "";
    }else{
        $token = $api_token[0]->setting_value; //for api token key
    }


    $gif = BASE_URL."ajax-loader.gif";
    
    if($apiStart && $token && $consumerkey && $consumersecret){
        $disbled = "";
    }else{ $disbled = "disabled"; } ?>

    <input type="hidden" value="true" id="productmanagementpage" />
    <div class="container">
        <div class="row">
            <div class="col-md-12">
               <?php
                if(!empty($disbled)){ ?>
                  <p style="color:red;">Note : Please provide Authorization details...! </p>
               <?php } ?> 
                <div class="">
                    <div class="wrap">
                        <div class="col-md-8">
                            <h1 class="wp-heading-inline">List of Products</h1>
                        </div>
                    </div>
                    <p></p>
                    <p></p>
                    <table class="wp-list-table widefat fixed striped updateproductdatatable ">
                        <thead>
                        <tr>
                            <th>Woocommerce product Id</th>
                            <th>Product name</th>
                            <th>Erp product Id</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $args = array( 'post_type' => 'product' ,'posts_per_page' => -1);
                        $allproducts = new WP_Query( $args );
                        $result = $allproducts->posts;

                        foreach($result as $key=>$value){
                            $product = wc_get_product($value->ID);
                            $product = $product->get_data();

                            $createdby = "byitems";
                            $sql = "SELECT createdby from {$wpdb->base_prefix}erp_item_mapping where wp_item_id = ".$value->ID;
                            $myresult = $wpdb->get_results($sql);
                            if(!empty($myresult)){
                                $createdby = $myresult[0]->createdby;
                            }

                            $erpproductid = wc_get_product_terms( $product['id'], 'pa_erp_product_id', array( 'fields' => 'names' ));
                            $erpproductid = reset($erpproductid);

                            if(!empty($erpproductid)){ ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $erpproductid; ?></td>
                                    <?php if($createdby == "byitems"){ ?>
                                        <td>
                                            <button id="<?php echo $value->ID; ?>" <?php echo $disbled; ?> class="button button-primary updateproduct">Update</button>
                                            <img style="display:none;" src="<?php echo $gif; ?>" id="loader_<?php echo $value->ID; ?>">
                                        </td>
                                    <?php } else { ?>
                                        <td>
                                            <button id="<?php echo $value->ID; ?>" <?php echo $disbled; ?> class="button button-primary updateproductbycolor">Update</button>
                                            <img style="display:none;" src="<?php echo $gif; ?>" id="loaderbycolor_<?php echo $value->ID; ?>">
                                        </td>
                                    <?php }?>
                                </tr>
                            <?php }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="notificationmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Notification</h5>
                </div>
                <div class="modal-body" id="modalcontent">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}



/**
 * Display the page for order sync.
 */
function orders_sync_page()
{
    global $wpdb;
    $gif = BASE_URL."ajax-loader.gif";

    ?>
    <br><br><br>
    <input type="hidden" id="ordermanagementpage" value="true">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <!-- Nav tabs -->
                <div class="">
                    <div class="wrap">
                        <div class="col-md-10">
                            <h1 class="wp-heading-inline">List of Orders</h1>
                        </div>
                        <div class="col-md-2">
                            <a href="#" class="btn btn-info" id="UpdateOrdersStatus">
                                Update Orders
                            </a>
                            <img style="display:none;" src="<?php echo $gif; ?>" id="UpdateOrdersLoader">
                        </div>
                    </div>
                    <p></p>
                    <div id="SyncOrder" style="display:none;">
                        <img src="<?php echo $gif; ?>" >
                    </div>
                    <table class="wp-list-table widefat fixed striped" id="example">
                        <thead>
                        <th>View</th>
                        <th>Order Id</th>
                        <th>Erp Id</th>
                        <th>Status</th>
                        <th>Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal for Order Details -->
    <div class="modal fade" id="orderdetailmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Order Details</h5>
                </div>
                <div class="modal-body" id="modalcontentorders">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="notificationmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Notification</h5>
                </div>
                <div class="modal-body" id="modalcontent">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php
}



/**
 * This functions updates the categories of the products according to the itsperfect api.
 */
add_action('wp_ajax_sw_category_updates', 'category_updates');
function category_updates(){
    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    set_time_limit(0);

    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2',
        ]
    );


    global $wpdb;
    $datetime1 = strtotime(Date("Y-m-d H:i:s"));

    //Items api for getting all categories.
    $type = "GET";
    $url = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
    $response_curl = dynamic_curl_call_request($type,$url,$data);

    if ($response_curl['status'] != 200) {
        echo "cURL Error #:" . $response_curl['body'];
    } else {
        $response = $response_curl['body'];
        $data = json_decode($response_curl['body']);

        $allcategory = [];
        $myitemids = array();
        foreach($data->items as $key=>$item){
            foreach($item->webshopCategories as $key=>$category){
                $category = array(
                    'id' => $category->id,
                    'category_name' => $category->category->en,
                    'categoryId' => $category->categoryId
                );
                array_push($allcategory,$category);
            }
        }


        $allcategory = array_map("unserialize", array_unique(array_map("serialize", $allcategory)));

        $sql = "SELECT *  from {$wpdb->base_prefix}erp_category";
        $result = $wpdb->get_results($sql);

        $value1 = array();
        foreach ($result as $key1 => $val) {
            array_push($value1,array("erp_id"=>$val->erp_cat_id,"erp_cat_name"=>$val->category_name));
        }
        $value2 = array();
        foreach ($allcategory as $key => $value) {
            array_push($value2,array("erp_id"=>$value['id'],"erp_cat_name"=>$value['category_name']));
            $date = Date("Y-m-d H:i:s");
            $catname = $value["category_name"];
            $query = "UPDATE {$wpdb->base_prefix}erp_category set category_name = '$catname' , modified_at = '$date' where erp_cat_id = ".$value['id'];
            $result = $wpdb->get_results($query);
            $query1 = "UPDATE {$wpdb->base_prefix}erp_category_mapping set category_name = '$catname' , modified_at = '$date' where erp_category_id = ".$value['id'];
            $result1 = $wpdb->get_results($query1);

            $sql = "SELECT  wp_category_id from {$wpdb->base_prefix}erp_category_mapping where erp_category_id = ".$value['id'];
            $result2 = $wpdb->get_results($sql);

            if(!empty($result2)){
                foreach ($result2 as $vals){
                    if(!empty($value)){
                        $data_cat = array(
                            "name"=> $catname,
                        );

                        try{
                            $asd = $woocommerce->put('products/categories/'.$vals->wp_category_id, $data_cat);
                        }catch (HttpClientException $e) {
                            $request  =  $e->getRequest(); // Last request data.
                            $response = $e->getResponse(); // Last response data.
                        }
                    }

                }
            }
        }
    }
}





/**
 * Creates categories with mapping according to the itsperfect.
 */
add_action('wp_ajax_sw_sync_item_groups', 'sync_item_groups');
function sync_item_groups(){

    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    set_time_limit(0);

    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2',
        ]
    );


    global $wpdb;
    $datetime1 = strtotime(Date("Y-m-d H:i:s"));
    
    //Items api for getting all categories
    $type = "GET";
    $url = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
    $response_curl = dynamic_curl_call_request($type,$url,$data);

    if ($response_curl['status'] != 200) {
        echo "cURL Error #:" . $response_curl['body'];
    } else {
        $response = $response_curl['body'];
        $data = json_decode($response_curl['body']);

        $allcategory = [];
        $myitemids = array();
        foreach($data->items as $key=>$item){
            foreach($item->webshopCategories as $key=>$category){
                $category = array(
                    'id' => $category->id,
                    'category_name' => $category->category->en,
                    'categoryId' => $category->categoryId
                );
                array_push($allcategory,$category);
            }
        }

        $allcategory = array_map("unserialize", array_unique(array_map("serialize", $allcategory)));
        if(!empty($allcategory)){

            //for delete current data and Add new data in tabales.
            // $sqlTruncErpCategory = "TRUNCATE TABLE {$wpdb->base_prefix}erp_category";
            // $results1 = $wpdb->get_results($sqlTruncErpCategory);


            $sql = "SELECT *  from {$wpdb->base_prefix}erp_category";
            $result = $wpdb->get_results($sql);

            if(empty($result)){
                foreach ($allcategory as $key => $value) {
                    $wpid = $value['id'];
                    $catname = $value['category_name'];
                    $erpid = $value['categoryId'];
                    $date = Date("Y-m-d H:i:s");
                    $sql = "INSERT into {$wpdb->base_prefix}erp_category (`erp_cat_id`,`category_name`,`category_id`,`created_at`) values ('$wpid','$catname','$erpid','$date')";
                    $wpdb->query($sql);
                }

                $sqlTruncErpCategoryMapping = "TRUNCATE TABLE {$wpdb->base_prefix}erp_category_mapping";
                $results2 = $wpdb->get_results($sqlTruncErpCategoryMapping);

                $wptermsDelete = "DELETE a,c FROM {$wpdb->base_prefix}terms AS a
                LEFT JOIN {$wpdb->base_prefix}term_taxonomy AS c ON a.term_id = c.term_id
                LEFT JOIN {$wpdb->base_prefix}term_relationships AS b ON b.term_taxonomy_id = c.term_taxonomy_id
                WHERE c.taxonomy = 'product_tag'";

                $wpcatDelete = "DELETE a,c FROM {$wpdb->base_prefix}terms AS a
                  LEFT JOIN {$wpdb->base_prefix}term_taxonomy AS c ON a.term_id = c.term_id
                  LEFT JOIN {$wpdb->base_prefix}term_relationships AS b ON b.term_taxonomy_id = c.term_taxonomy_id
                  WHERE c.taxonomy = 'product_cat'";

                $wpdb->get_results($wptermsDelete);
                $wpdb->get_results($wpcatDelete);

                // for first level Category

                $sql = "SELECT *  from {$wpdb->base_prefix}erp_category where category_id = 0";
                $result = $wpdb->get_results($sql);
                $mynewarray = array();
                foreach ($result as $key => $value) {

                    $mynewarray['create'][] = ['name' => $value->category_name,'parent'=>0];
                }

                try {
                    $endpoint = 'products/categories/batch';
                    $x = $woocommerce->post($endpoint, $mynewarray);

                    foreach($x->create as $key=>$data) {

                        foreach ($result as $key => $value) {
                            if($data->name == $value->category_name){
                                $erpid = $value->erp_cat_id;
                                $wpid = $data->id;
                                $catname = $value->category_name;
                            }
                        }
                        $date = Date("Y-m-d H:i:s");
                        $sql = "INSERT into {$wpdb->base_prefix}erp_category_mapping (`wp_category_id`,`category_name`,`erp_category_id`,`created_at`) values ('$wpid','$catname','$erpid','$date')";
                        $wpdb->query($sql);
                    }

                } catch (HttpClientException $e) {
                    $request  =  $e->getRequest(); // Last request data.
                    $response = $e->getResponse(); // Last response data.
                    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
                    $interval  = abs($datetime2 - $datetime1);
                    $minutes   = round($interval / 60);
                    $date = Date("Y-m-d H:i:s");
                    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('categories','$minutes','$request','$response','$date') ";
                    $wpdb->query($query);
                }



                //for Second level category
                $erpcat_id = array();
                foreach ($result as $key => $value) {
                    $sqls = "SELECT *  from {$wpdb->base_prefix}erp_category where category_id = $value->erp_cat_id";
                    $results = $wpdb->get_results($sqls);
                    foreach ($results as $key => $value) {
                        array_push($erpcat_id,$value);
                    }
                }


                $mynewarray = array();
                foreach ($erpcat_id as $key => $value) {
                    $sqls = "SELECT wp_category_id  from {$wpdb->base_prefix}erp_category_mapping where erp_category_id = $value->category_id";
                    $results = $wpdb->get_results($sqls);

                    $mynewarray['create'][] = ['name' => $value->category_name,'parent'=>$results[0]->wp_category_id];
                }

                try {
                    $endpoint = 'products/categories/batch';
                    $x = $woocommerce->post($endpoint, $mynewarray);

                    foreach($x->create as $Key=>$data) {
                        foreach ($erpcat_id as $key => $value) {

                            if($Key == $key){
                                $erpid = $value->erp_cat_id;
                                $wpid = $data->id;
                                $catname = $value->category_name;
                            }
                        }

                        $date = Date("Y-m-d H:i:s");
                        $sql = "INSERT into {$wpdb->base_prefix}erp_category_mapping (`wp_category_id`,`category_name`,`erp_category_id`,`created_at`) values ('$wpid','$catname','$erpid','$date')";
                        $wpdb->query($sql);
                    }

                } catch (HttpClientException $e) {
                    $request  =  $e->getRequest(); // Last request data.
                    $response = $e->getResponse(); // Last response data.
                    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
                    $interval  = abs($datetime2 - $datetime1);
                    $minutes   = round($interval / 60);
                    $date = Date("Y-m-d H:i:s");
                    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('categories','$minutes','$request','$response','$date') ";
                    $wpdb->query($query);
                }

                //for Third level category
                $lastArray = array();
                foreach ($erpcat_id as $key => $value) {
                    $sqls1 = "SELECT *  from {$wpdb->base_prefix}erp_category where category_id = $value->erp_cat_id";
                    $results1 = $wpdb->get_results($sqls1);

                    //for the remove the last in multiple category with same name in same parent Category
                    //$tempArr = array_unique(array_column($results1, 'category_name'));
                    //$results1 = array_intersect_key($results1, $tempArr);

                    foreach ($results1 as $key => $value1) {
                        array_push($lastArray,$value1);
                    }
                }

                $mynewarray = array();
                foreach ($lastArray as $key => $value) {
                    $sqls = "SELECT wp_category_id  from {$wpdb->base_prefix}erp_category_mapping where erp_category_id = $value->category_id";

                    $results = $wpdb->get_results($sqls);
                    $mynewarray['create'][] = ['name' => $value->category_name,'parent'=>$results[0]->wp_category_id];
                }


                try {
                    $endpoint = 'products/categories/batch';
                    $x = $woocommerce->post($endpoint, $mynewarray);

                    foreach($x->create as $Key=>$data) {
                        foreach ($lastArray as $key => $value) {
                            if($Key == $key){
                                $erpid = $value->erp_cat_id;
                                $wpid = $data->id;
                                $catname = $value->category_name;
                            }
                        }


                        $date = Date("Y-m-d H:i:s");
                        $sql = "INSERT into {$wpdb->base_prefix}erp_category_mapping (`wp_category_id`,`category_name`,`erp_category_id`,`created_at`) values ('$wpid','$catname','$erpid','$date')";
                        $wpdb->query($sql);

                    }

                } catch (HttpClientException $e) {
                    $request  =  $e->getRequest(); // Last request data.
                    $response = $e->getResponse(); // Last response data.
                    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
                    $interval  = abs($datetime2 - $datetime1);
                    $minutes   = round($interval / 60);
                    $date = Date("Y-m-d H:i:s");
                    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('categories','$minutes','$request','$response','$date') ";
                    $wpdb->query($query);
                }

            }else{
                // for the update category if any new category created.

                $value1 = array();
                foreach ($result as $key1 => $val) {
                    array_push($value1,$val->erp_cat_id);
                }
                $value2 = array();
                foreach ($allcategory as $key => $value) {
                    array_push($value2,$value['id']);
                }

                $newCat = array_diff($value2,$value1);
                if(!empty($newCat)){
                    foreach ($newCat as $key => $values) {
                        foreach ($allcategory as $key => $value) {
                            if($value['id'] == $values){
                                $wpid = $value['id'];
                                $catname = $value['category_name'];
                                $erpid = $value['categoryId'];
                                $date = Date("Y-m-d H:i:s");
                                $sql = "INSERT into {$wpdb->base_prefix}erp_category (`erp_cat_id`,`category_name`,`category_id`,`created_at`) values ('$wpid','$catname','$erpid','$date')";
                                $wpdb->query($sql);
                            }
                        }
                    }

                    //for updated Category insert level category
                    $erpcat_id = array();
                    foreach ($newCat as $key => $value) {
                        $sqls = "SELECT *  from {$wpdb->base_prefix}erp_category where erp_cat_id = $value";
                        $results = $wpdb->get_results($sqls);
                        foreach ($results as $key => $value) {
                            array_push($erpcat_id,$value);
                        }
                    }

                    $mynewarray = array();
                    foreach ($erpcat_id as $key => $value) {
                        $sqls = "SELECT wp_category_id  from {$wpdb->base_prefix}erp_category_mapping where erp_category_id = $value->category_id";
                        $results = $wpdb->get_results($sqls);

                        $mynewarray['create'][] = ['name' => $value->category_name,'parent'=>$results[0]->wp_category_id];
                    }

                    try {
                        $endpoint = 'products/categories/batch';
                        $x = $woocommerce->post($endpoint, $mynewarray);

                        foreach($x->create as $Key=>$data) {
                            foreach ($erpcat_id as $key => $value) {

                                if($Key == $key){
                                    $erpid = $value->erp_cat_id;
                                    $wpid = $data->id;
                                    $catname = $value->category_name;
                                }
                            }

                            $date = Date("Y-m-d H:i:s");
                            $sql = "INSERT into {$wpdb->base_prefix}erp_category_mapping (`wp_category_id`,`category_name`,`erp_category_id`,`created_at`) values ('$wpid','$catname','$erpid','$date')";
                            $wpdb->query($sql);
                        }

                    } catch (HttpClientException $e) {
                        $request  =  $e->getRequest(); // Last request data.
                        $response = $e->getResponse(); // Last response data.
                        $datetime2 = strtotime(Date("Y-m-d H:i:s"));
                        $interval  = abs($datetime2 - $datetime1);
                        $minutes   = round($interval / 60);
                        $date = Date("Y-m-d H:i:s");
                        $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('categories','$minutes','$request','$response','$date') ";
                        $wpdb->query($query);
                    }
                }
            }
        }
    }
    $urlrequested = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);
    $date = Date("Y-m-d H:i:s");
    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`created_at`) values ('categories','$minutes','$urlrequested','$date') ";
    $wpdb->query($query);
}


/**
 * Attaches necessary css & js
 */
add_action('wp_print_scripts', 'test_ajax_load_scripts',PHP_INT_MAX);
function test_ajax_load_scripts() {
  $parent_style = 'flatsome-style-css';
  wp_enqueue_style('customcss',plugin_dir_url( __FILE__ ) . 'custom.css',array(),'','all');
  wp_enqueue_script( "ajax-test", plugin_dir_url( __FILE__ ) . '/custom.js', array( 'jquery' ) );
  wp_localize_script( 'ajax-test', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}


/**
 * This function fetches items data from itsperfect and creates products in woocommerce.
 */
add_action('wp_ajax_test_response', 'text_ajax_process_request');
function text_ajax_process_request() {
    $datetime1 = strtotime(Date("Y-m-d H:i:s"));
    global $wpdb;

    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    ini_set('memory_limit', -1);
    set_time_limit(0);

    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2',
        ]
    );

    if(isset($_POST['item_count'])){
        $countstring = sanitize_text_field($_POST['item_count']);
        $countarray = explode("_",$countstring);
        $countfrom = $countarray[0];
        $countto = $countarray[1];

        $type = "GET";
        $url = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
        $response_curl = dynamic_curl_call_request($type,$url,$data);

        if ($response_curl['status'] != 200) {
            echo "cURL Error #:" . $response_curl['body'];
        } else {
            $mydata = json_decode($response_curl['body']);

            $i = 1;
            $erpidarray = array();
            $myarray = array();
            $erpattrid = 1;

            try{
                $mycounter = 0;
                $endpoint = 'products/attributes';
                $result = $woocommerce->get($endpoint);
                foreach($result as $key=>$value){
                    if($value->name == "erp_product_id"){
                        $erpattrid = $value->id;
                        $mycounter++;
                    }
                }
            }
            catch(HttpClientException $e){
                echo $e->getMessage(); // Error message.
                echo $e->getRequest(); // Last request data.
                echo $e->getResponse(); // Last response data.
                die;
            }
            if($mycounter == 0){
                try {
                    $endpoint = 'products/attributes';
                    $attribute = array();
                    $attribute['name'] = "erp_product_id";
                    $attribute['slug'] = "pa_erp_product_id";
                    $x = $woocommerce->post($endpoint, $attribute);
                    $erpattrid = $x->id;

                }
                catch (HttpClientException $e) {
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }
            }

            try{
                $mycounter = 0;
                $sizecounter = 0;
                $barcodecounter = 0;
                $endpoint = 'products/attributes';
                $colorresult = $woocommerce->get($endpoint);
                foreach($colorresult as $key=>$value){
                    if($value->name == "color"){
                        $colorid = $value->id;
                        $mycounter++;
                    }
                    if($value->name == "size"){
                        $sizeid = $value->id;
                        $sizecounter++;
                    }
                    if($value->name == "barcode"){
                        $barcodeid = $value->id;
                        $barcodecounter++;
                    }
                }
            }
            catch(HttpClientException $e){
                echo $e->getMessage(); // Error message.
                echo $e->getRequest(); // Last request data.
                echo $e->getResponse(); // Last response data.
                die;
            }

            if($mycounter == 0){
                try {
                    $endpoint = 'products/attributes';
                    $attribute = array();
                    $attribute['name'] = "color";
                    $attribute['slug'] = "pa_color";
                    $x = $woocommerce->post($endpoint, $attribute);
                    $colorid = $x->id;

                }
                catch (HttpClientException $e) {
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }
            }
            if($sizecounter == 0){
                try {
                    $endpoint = 'products/attributes';
                    $attribute = array();
                    $attribute['name'] = "size";
                    $attribute['slug'] = "pa_size";
                    $x = $woocommerce->post($endpoint, $attribute);
                    $sizeid = $x->id;

                }
                catch (HttpClientException $e) {
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }
            }
            if($barcodecounter == 0){
                try {
                    $endpoint = 'products/attributes';
                    $attribute = array();
                    $attribute['name'] = "barcode";
                    $attribute['slug'] = "pa_barcode";
                    $x = $woocommerce->post($endpoint, $attribute);
                    $barcodeid = $x->id;

                }
                catch (HttpClientException $e) {
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }
            }

            $sql = "SELECT setting_value FROM {$wpdb->base_prefix}erp_settings where module = 'item' and setting_module='createitemby'";
            $result = $wpdb->get_results($sql);

            if(empty($result)){
                $createby = "byitems";
            }
            else{
                $createby = $result[0]->setting_value;
            }

            foreach($mydata->items as $key=>$data){

                if($createby == "byitems"){
                    $product = array();
                    $attributes = array();

                    $product['name'] = $data->item;
                    $product['short_description'] = $data->description->en;


                    $product['sku'] = $data->itemNumber;
                    $attributes["id"] = $erpattrid;
                    $attributes['options'][] = "$data->id";
                    $product['attributes'][] = $attributes;


                    foreach($data->webshopCategories as $key=>$category){
                        $sql = "SELECT wp_category_id from {$wpdb->base_prefix}erp_category_mapping where erp_category_id = ".$category->id;
                        $result = $wpdb->get_results($sql);
                        if(!empty($result)){
                            $categoryid = $result[0]->wp_category_id;
                        }
                        $product['categories'][] = ['id' => $categoryid];
                    }

                    $product['type'] = "variable";
                    $product['regular_price'] = $data->salesListPrice;
                    

                    $product['images'] = array();
                    $date = date("Y-m-d H:i:s");
                    $colors = array();
                    foreach($data->colors as $key=>$color){
                        array_push($colors,$color->color);
                        foreach($color->images as $key=>$images){
                            $urlarray = explode("/",$images->url);
                            $imagename = end($urlarray);
                            $newquery = "INSERT INTO {$wpdb->base_prefix}erp_color_image_mapping (`erp_product_id`,`colorname`,`imagename`,`created_at`) values ('$data->id','$color->color','$imagename','$date')";
                            $wpdb->query($newquery);
                            $product['images'][]['src'] = "$images->url";
                        }
                    }
                    $color = array();
                    $color["id"] = $colorid;
                    $color["visible"] = true;
                    $color["variation"] = true;
                    $color['options'] = $colors;
                    $product['attributes'][] = $color;

                    $sizes = array();
                    foreach($data->sizes as $key=>$value){
                        $mysize = "";
                        if(empty($value->secondSize)){
                            $mysize = $value->size;
                        }
                        else{
                            $mysize = $value->size." - ".$value->secondSize;
                        }
                        array_push($sizes,$mysize);
                    }

                    $size = array();
                    $size["id"] = $sizeid;
                    $size["visible"] = true;
                    $size["variation"] = true;
                    $size['options'] = $sizes;
                    $product['attributes'][] = $size;

                    //echo json_encode($product,  JSON_UNESCAPED_SLASHES);die;

                    //importing products in draft mode
                    // $product['status'] = 'draft';


                    if($i >= $countfrom && $i <= $countto){
                        array_push($erpidarray , $data->id);
                        $query = "SELECT * FROM {$wpdb->base_prefix}erp_item_mapping where erp_item_id = ". $data->id ." and status = 'synced'";
                        $result = $wpdb->get_results($query);
                        if(empty($result)){
                            $myarray['create'][] = $product;
                        }
                    }
                }

                if($createby == "bycolors"){
                    foreach($data->colors as $key=>$color){

                        $product = array();
                        $attributes = array();

                        $product['name'] = $data->item." - ".$color->color;
                        $product['sku'] = $data->itemNumber;
                        $attributes["id"] = $erpattrid;
                        $attributes['options'][] = "$data->id";
                        $product['attributes'][] = $attributes;

                        $mycolor = array();
                        $mycolor["id"] = $colorid;
                        $mycolor["visible"] = true;
                        $mycolor["variation"] = false;
                        $mycolor['options'] = $color->color;
                        $product['attributes'][] = $mycolor;


                        foreach($data->webshopCategories as $key=>$category){
                            $sql = "SELECT wp_category_id from {$wpdb->base_prefix}erp_category_mapping where erp_category_id = ".$category->id;
                            $result = $wpdb->get_results($sql);
                            if(!empty($result)){
                                $categoryid = $result[0]->wp_category_id;
                            }
                            $product['categories'][] = ['id' => $categoryid];
                        }

                        $product['type'] = "variable";
                        $product['regular_price'] = $data->salesListPrice;

                        $product['short_description'] = $data->description->en;


                        $product['images'] = array();

                        foreach($color->images as $key=>$images){
                            $product['images'][]['src'] = "$images->url";
                        }

                        $sizes = array();
                        foreach($data->sizes as $key=>$value){
                            $mysize = "";
                            if(empty($value->secondSize)){
                                $mysize = $value->size;
                            }
                            else{
                                $mysize = $value->size." - ".$value->secondSize;
                            }
                            array_push($sizes,$mysize);
                        }

                        $size = array();
                        $size["id"] = $sizeid;
                        $size["visible"] = true;
                        $size["variation"] = true;
                        $size['options'] = $sizes;
                        $product['attributes'][] = $size;

                        //echo json_encode($product,  JSON_UNESCAPED_SLASHES);die;

                        //importing products in draft mode
                        // $product['status'] = 'draft';

                        if($i >= $countfrom && $i <= $countto){
                            array_push($erpidarray , $data->id);
                            $query = "SELECT * FROM {$wpdb->base_prefix}erp_item_mapping where erp_item_id = ". $data->id ." and status = 'synced'";
                            $result = $wpdb->get_results($query);
                            if(empty($result)){
                                $myarray['create'][] = $product;
                            }
                        }
                    }
                }
                $i++;
            }
            try {
                if(!empty($myarray)){
                    $endpoint = 'products/batch';
                    $x = $woocommerce->post($endpoint, $myarray);
                    foreach($x->create as $key=>$data) {
                        if($data->id != 0){
                            foreach($erpidarray as $key=>$itemerpid){
                                if($itemerpid == $data->attributes[0]->options[0]){
                                    $date = Date("Y-m-d H:i:s");
                                    $sql = "SELECT * FROM {$wpdb->base_prefix}erp_item_mapping where wp_item_id = ".$data->id;
                                    $myresult = $wpdb->get_results($sql);
                                    if(empty($myresult)){
                                        $query = "INSERT INTO {$wpdb->base_prefix}erp_item_mapping (`wp_item_id`,`erp_item_id`,`status`,`createdby`,`created_at`) values ('$data->id','$itemerpid','synced','$createby','$date')";
                                        $wpdb->query($query);

                                        foreach ($data->images as $imagedata){
                                            if(strpos($imagedata->name,"-")){
                                                $imagename = explode("-",$imagedata->name);
                                                $extension = explode(".",$imagename[1]);
                                                $realimagename = $imagename[0].".".$extension[1];
                                            }
                                            else{
                                                $realimagename = $imagedata->name;
                                            }
                                            $newquery = "update {$wpdb->base_prefix}erp_color_image_mapping set wp_product_id = '$data->id',imageid='$imagedata->id',modified_at = '$date' where erp_product_id = '$itemerpid' and imagename = '$realimagename'";
                                            $wpdb->query($newquery);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (HttpClientException $e) {
                $request  =  $e->getRequest(); // Last request data.
                $response = $e->getResponse(); // Last response data.
                $datetime2 = strtotime(Date("Y-m-d H:i:s"));
                $interval  = abs($datetime2 - $datetime1);
                $minutes   = round($interval / 60);
                $date = Date("Y-m-d H:i:s");
                $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('products','$minutes','$request','$response','$date') ";
                $wpdb->query($query);
            }
        }
    }

    $urlrequested = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);
    $date = Date("Y-m-d H:i:s");
    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`created_at`) values ('products','$minutes','$urlrequested','$date') ";
    $wpdb->query($query);
}



/**
 * This functions changes the default api batch limit of 100 to 500.
 */
add_filter( 'woocommerce_rest_batch_items_limit', 'wpse_304237_rest_batch_items_limit' );
function wpse_304237_rest_batch_items_limit( $limit ) {
    $limit = 500;

    return $limit;
}



/*
 * This action creates variations of products.
 * for e.g. if colors = 2 and sizes = 3 then total variations will be 6.
 */
add_action('wp_ajax_sw_createvariations', 'createvariations');
function createvariations(){
    $datetime1 = strtotime(Date("Y-m-d H:i:s"));
    global $wpdb;
    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    set_time_limit(0);
    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 100000000000000000,
            'version' => 'wc/v2',
        ]
    );

    $query = "SELECT * from {$wpdb->base_prefix}erp_item_mapping";
    $result = $wpdb->get_results($query);

    foreach($result as $key=>$itemid){

        $myendpoint = "products/$itemid->wp_item_id";
        try{
            $item = $woocommerce->get($myendpoint);
        }catch (HttpClientException $e) {
            $request  =  json_encode($e->getRequest()); // Last request data.
            $response = json_encode($e->getResponse()); // Last response data.
            $datetime2 = strtotime(Date("Y-m-d H:i:s"));
            $interval  = abs($datetime2 - $datetime1);
            $minutes   = round($interval / 60);
            $date = Date("Y-m-d H:i:s");
            $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('delete product variations','$minutes','$request','$response','$date') ";
            $wpdb->query($query);
        }

        $itemsql = "SELECT createdby FROM {$wpdb->base_prefix}erp_item_mapping where wp_item_id = ".$itemid->wp_item_id;
        $itemresult = $wpdb->get_results($itemsql);
        if(empty($itemresult)){
            $createby = "byitems";
        }
        else{
            $createby = $itemresult[0]->createdby;
        }

        if(!empty($item)){
            if(empty($item->variations)){

                if($createby == "byitems"){
                    $totalarray = array();
                    $vendpoint = "products/$item->id/variations/batch";
                    foreach($item->attributes as $key=>$attribute){

                        if($attribute->name == "erp_product_id"){
                            $erpid = $attribute->options[0];
                            $erpid = $itemid->erp_item_id;

                            //get specific item using this api.
                            $type = "GET";
                            $url = $GLOBALS['apiStart']."/api/v2/items/$erpid/?token=".$GLOBALS['token'];
                            $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                            $response_curl = dynamic_curl_call_request($type,$url,$data);

                            if ($response_curl['status'] != 200) {
                                echo "cURL Error #:" . $response_curl['body'];
                            } else {
                                $response = $response_curl['body'];
                                $data = json_decode($response_curl['body']);
                                $erpitem = $data->items[0];
                            }
                        }

                        if($attribute->name == "color"){
                            $colors = array();
                            $attrid = $attribute->id;
                            foreach($attribute->options as $key=>$color){
                                $temp = array();
                                $temp['id'] = $attrid;
                                $temp['option'] = $color;
                                $colors[]  = $temp;
                            }
                        }

                        if($attribute->name == "size"){
                            $sizes = array();
                            $attrid = $attribute->id;
                            foreach($attribute->options as $key=>$size){
                                $temp = array();
                                $temp['id'] = $attrid;
                                $temp['option'] = $size;
                                $sizes[] = $temp;
                            }
                        }
                    }

                    //getting stock for item
                    if(isset($erpitem->id)){

                        //get stock of specific item using this api.
                        $type = "GET";
                        $url = $GLOBALS['apiStart']."/api/v2/items/$erpitem->id/stock?token=".$GLOBALS['token'];
                        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                        $response_curl = dynamic_curl_call_request($type,$url,$data);

                        if ($response_curl['status'] != 200) {
                            echo "cURL Error #:" . $response_curl['body'];
                        } else {
                            $response = $response_curl['body'];
                            $data = json_decode($response_curl['body']);
                            $stock = $data->stock[0]->warehouses[0]->colors;
                        }

                        foreach($erpitem->barcodes as $key=>$barcodes){
                            foreach($colors as $key=>$color){
                                $newtemp = array();

                                $newtemp['image'] = array();
                                foreach($erpitem->colors as $key=>$erpcolor){
                                    if(strtolower($erpcolor->color) == strtolower($color['option'])){
                                        $sql = "SELECT * FROM {$wpdb->base_prefix}erp_color_image_mapping where erp_product_id='$erpitem->id' and colorname = '$erpcolor->color' ";
                                        $myresult = $wpdb->get_results($sql);
                                        foreach($myresult as $Key=>$imagedata){
                                            $newtemp['image']['id']= $imagedata->imageid;
                                        }
                                    }
                                }

                                foreach($sizes as $key=>$size){
                                    if(strtolower($barcodes->color->color) == strtolower( $color['option'])){

                                        if(empty($barcodes->size->secondSize)){
                                            $barcodesize = $barcodes->size->size;
                                        }
                                        else {
                                            $barcodesize = $barcodes->size->size." - ".$barcodes->size->secondSize;
                                        }


                                        if($barcodesize == $size['option']){
                                            $eanbarcode = $barcodes->barcode;


                                            foreach($stock as $key=>$value){
                                                if(strtolower($value->color) == strtolower($color['option'])){
                                                    foreach($value->sizes as $key=>$erpsize){

                                                        if(empty($erpsize->secondSize)){
                                                            $stocksize = $erpsize->size;
                                                        }else{
                                                            $stocksize = $erpsize->size." - ".$erpsize->secondSize;
                                                        }

                                                        if($stocksize == $size['option']){
                                                            if($erpsize->availableStock > 0){
                                                                $stockquantity = $erpsize->availableStock;
                                                            }
                                                            else{
                                                                $stockquantity = 0;
                                                            }
                                                        }

                                                    }
                                                }
                                            }

                                            $temp = array();
                                            $temp['key'] = "hwp_var_gtin";
                                            $temp['value'] = $eanbarcode;
                                            $newtemp['meta_data'][] = $temp;

                                            $newtemp['regular_price'] = $erpitem->salesListPrice;
                                            $newtemp['manage_stock'] = true;

                                            $newtemp['stock_quantity'] = $stockquantity;
                                            if($stockquantity > 0){
                                                $newtemp['stock_status'] = "instock";
                                            }
                                            $newtemp['attributes'][] = $color;
                                            $newtemp['attributes'][] = $size;
                                            $totalarray['create'][] = $newtemp;
                                        }
                                    }
                                }
                            }
                        }


                        try{
                            $woocommerce->post($vendpoint,$totalarray);
                            $totalarray = array();
                        }
                        catch (HttpClientException $e) {
                            echo $e->getMessage(); // Error message.
                            echo $e->getRequest(); // Last request data.
                            echo $e->getResponse(); // Last response data.
                            die;
                        }
                    }
                }

                if($createby == "bycolors"){
                    $totalarray = array();
                    $vendpoint = "products/$item->id/variations/batch";
                    foreach($item->attributes as $key=>$attribute){

                        if($attribute->name == "erp_product_id"){
                            $erpid = $attribute->options[0];
                            $erpid = $itemid->erp_item_id;

                            //getting all data of specific item using this api.
                            $type = "GET";
                            $url = $GLOBALS['apiStart']."/api/v2/items/$erpid/?token=".$GLOBALS['token'];
                            $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                            $response_curl = dynamic_curl_call_request($type,$url,$data);

                            if ($response_curl['status'] != 200) {
                                echo "cURL Error #:" . $response_curl['body'];
                            } else {
                                $response = $response_curl['body'];
                                $data = json_decode($response_curl['body']);
                                $erpitem = $data->items[0];
                            }
                        }

                        if($attribute->name == "color"){
                            foreach($attribute->options as $key=>$color){
                                $mycolor = $color;
                            }
                        }

                        if($attribute->name == "size"){
                            $sizes = array();
                            $attrid = $attribute->id;
                            foreach($attribute->options as $key=>$size){
                                $temp = array();
                                $temp['id'] = $attrid;
                                $temp['option'] = $size;
                                $sizes[] = $temp;
                            }
                        }
                    }


                    //getting stock for specific item
                    $type = "GET";
                    $url = $GLOBALS['apiStart']."/api/v2/items/$erpitem->id/stock?token=".$GLOBALS['token'];
                    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                    $response_curl = dynamic_curl_call_request($type,$url,$data);

                    if ($response_curl['status'] != 200) {
                        echo "cURL Error #:" . $response_curl['body'];
                    } else {
                        $response = $response_curl['body'];
                        $data = json_decode($response_curl['body']);
                        $stock = $data->stock[0]->warehouses[0]->colors;
                    }


                    foreach($erpitem->barcodes as $key=>$barcodes){
                        if(strtolower($mycolor) == strtolower($barcodes->color->color)){
                            $mysize = array();
                            $mysize['id'] = $attrid;

                            if(empty($barcodes->size->secondSize)){
                                $barcodesize = $barcodes->size->size;
                            }
                            else {
                                $barcodesize = $barcodes->size->size." - ".$barcodes->size->secondSize;
                            }

                            $mysize['option'] = $barcodesize;

                            foreach($sizes as $key=>$size){
                                foreach($stock as $key=>$value){
                                    if(strtolower($value->color) == strtolower($mycolor)){
                                        foreach($value->sizes as $key=>$erpsize){
                                            if(empty($erpsize->secondSize)){
                                                $mainsize = $erpsize->size;
                                            }
                                            else{
                                                $mainsize = $erpsize->size." - ".$erpsize->secondSize;
                                            }
                                            if($mainsize == $mysize['option']){
                                                if($erpsize->availableStock > 0){
                                                    $stockquantity = $erpsize->availableStock;
                                                }
                                                else{
                                                    $stockquantity = 0;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $eanbarcode = $barcodes->barcode;

                            $newtemp = array();

                            $temp = array();
                            $temp['key'] = "hwp_var_gtin";
                            $temp['value'] = $eanbarcode;
                            $newtemp['meta_data'][] = $temp;

                            $newtemp['regular_price'] = $erpitem->salesListPrice;
                            $newtemp['manage_stock'] = true;
                            $newtemp['stock_quantity'] = $stockquantity;
                            if($stockquantity > 0){
                                $newtemp['stock_status'] = "instock";
                            }
                            $newtemp['attributes'][] = $mysize;
                            $totalarray['create'][] = $newtemp;
                        }
                    }

                    try{
                        $woocommerce->post($vendpoint,$totalarray);
                        $totalarray = array();
                    }
                    catch (HttpClientException $e) {
                        echo $e->getMessage(); // Error message.
                        echo $e->getRequest(); // Last request data.
                        echo $e->getResponse(); // Last response data.
                        die;
                    }
                }
            }
        }
    }
    $urlrequested = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);
    $date = Date("Y-m-d H:i:s");
    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`created_at`) values ('variations','$minutes','$urlrequested','$date') ";
    $wpdb->query($query);
}




/*
 * This function updates the variations of products.
 * Price,Stock and all variations are it self updated.
 */
add_action('wp_ajax_sw_updatevariations','updatevariations');
function updatevariations(){
    $from = "";
    $to = "";
    if(isset($_POST['itemcount'])){
        $itemcount = explode("_",sanitize_text_field($_POST['itemcount']));
        $from = $itemcount[0];
        $to = $itemcount[1];
    }
    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    set_time_limit(0);
    global $wpdb;
    $datetime1 = strtotime(Date("Y-m-d H:i:s"));
    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2',
        ]
    );
    if($from != "" && $to != ""){
        $query = "SELECT wp_item_id from {$wpdb->base_prefix}erp_item_mapping where id >=".$from." and id <= ".$to;
    }
    else{
        $query = "SELECT wp_item_id from {$wpdb->base_prefix}erp_item_mapping";
    }
    $result = $wpdb->get_results($query);

    if(!empty($result)){
        //deleting all variations for all products.
        foreach($result as $key=>$itemid){
            $myendpoint = "products/$itemid->wp_item_id";
            try{
                $item = $woocommerce->get($myendpoint);
            }catch (HttpClientException $e) {
                $request  =  $e->getRequest(); // Last request data.
                $response = $e->getResponse(); // Last response data.
                $datetime2 = strtotime(Date("Y-m-d H:i:s"));
                $interval  = abs($datetime2 - $datetime1);
                $minutes   = round($interval / 60);
                $date = Date("Y-m-d H:i:s");
                $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('delete product variations','$minutes','$request','$response','$date') ";
                $wpdb->query($query);
            }

            if(!empty($item)){
                $getallvariations = "products/$item->id/variations";
                $allvariations = $woocommerce->get($getallvariations, array('per_page' => 100));

                if(!empty($allvariations)){
                    $myarray['delete'] = array();
                    foreach($allvariations as $key=>$variation){
                        $myarray['delete'][] = "$variation->id";
                    }
                    try{
                        $endpoint = "products/$item->id/variations/batch";
                        $woocommerce->post($endpoint,$myarray);
                        $myarray['delete'] = array();
                        $myarray = array();
                    }
                    catch (HttpClientException $e) {
                        echo $e->getMessage(); // Error message.
                        echo $e->getRequest(); // Error message.
                        echo $e->getResponse(); // Error message.
                        die;
                        $request  =  $e->getRequest(); // Last request data.
                        $response = $e->getResponse(); // Last response data.
                        $datetime2 = strtotime(Date("Y-m-d H:i:s"));
                        $interval  = abs($datetime2 - $datetime1);
                        $minutes   = round($interval / 60);
                        $date = Date("Y-m-d H:i:s");
                        $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('delete product variations','$minutes','$request','$response','$date') ";
                        $wpdb->query($query);
                    }
                }
            }
        }

        //creating product variations for all products.
        foreach($result as $key=>$itemid){
            $myendpoint = "products/$itemid->wp_item_id";
            try{
                $item = $woocommerce->get($myendpoint);
            }catch (HttpClientException $e) {
                $request  =  $e->getRequest(); // Last request data.
                $response = $e->getResponse(); // Last response data.
                $datetime2 = strtotime(Date("Y-m-d H:i:s"));
                $interval  = abs($datetime2 - $datetime1);
                $minutes   = round($interval / 60);
                $date = Date("Y-m-d H:i:s");
                $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('delete product variations','$minutes','$request','$response','$date') ";
                $wpdb->query($query);
            }

            $itemsql = "SELECT createdby FROM {$wpdb->base_prefix}erp_item_mapping where wp_item_id = ".$itemid->wp_item_id;
            $itemresult = $wpdb->get_results($itemsql);
            if(empty($itemresult)){
                $createby = "byitems";
            }
            else{
                $createby = $itemresult[0]->createdby;
            }


            if(!empty($item)){
                if(empty($item->variations)){

                    if($createby == "byitems"){
                        $totalarray = array();
                        $vendpoint = "products/$item->id/variations/batch";
                        foreach($item->attributes as $key=>$attribute){

                            if($attribute->name == "erp_product_id"){
                                $erpid = $attribute->options[0];
                                
                                $type = "GET";
                                $url = $GLOBALS['apiStart']."/api/v2/items/$erpid/?token=".$GLOBALS['token'];
                                $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                                $response_curl = dynamic_curl_call_request($type,$url,$data);

                                if ($response_curl['status'] != 200) {
                                    echo "cURL Error #:" . $response_curl['body'];
                                } else {
                                    $response = $response_curl['body'];
                                    $data = json_decode($response_curl['body']);
                                    $erpitem = $data->items[0];
                                }
                            }

                            if($attribute->name == "color"){
                                $colors = array();
                                $attrid = $attribute->id;
                                foreach($attribute->options as $key=>$color){
                                    $temp = array();
                                    $temp['id'] = $attrid;
                                    $temp['option'] = $color;
                                    $colors[]  = $temp;
                                }
                            }

                            if($attribute->name == "size"){
                                $sizes = array();
                                $attrid = $attribute->id;
                                foreach($attribute->options as $key=>$size){
                                    $temp = array();
                                    $temp['id'] = $attrid;
                                    $temp['option'] = $size;
                                    $sizes[] = $temp;
                                }
                            }
                        }

                        //getting stock for item


                        $type = "GET";
                        $url = $GLOBALS['apiStart']."/api/v2/items/$erpitem->id/stock?token=".$GLOBALS['token'];
                        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                        $response_curl = dynamic_curl_call_request($type,$url,$data);

                        if ($response_curl['status'] != 200) {
                            echo "cURL Error #:" . $response_curl['body'];
                        } else {
                            $response = $response_curl['body'];
                            $data = json_decode($response_curl['body']);
                            $stock = $data->stock[0]->warehouses[0]->colors;
                        }

                        foreach($erpitem->barcodes as $key=>$barcodes){
                            foreach($colors as $key=>$color){
                                $newtemp = array();

                                $newtemp['image'] = array();
                                foreach($erpitem->colors as $key=>$erpcolor){
                                    if($erpcolor->color == $color['option']){
                                        $sql = "SELECT * FROM {$wpdb->base_prefix}erp_color_image_mapping where erp_product_id='$erpitem->id' and colorname = '$erpcolor->color' ";
                                        $myresult = $wpdb->get_results($sql);
                                        foreach($myresult as $Key=>$imagedata){
                                            $newtemp['image']['id']= $imagedata->imageid;
                                        }
                                    }
                                }

                                foreach($sizes as $key=>$size){
                                    if($barcodes->color->color == $color['option']){

                                        if(empty($barcodes->size->secondSize)){
                                            $barcodesize = $barcodes->size->size;
                                        }
                                        else {
                                            $barcodesize = $barcodes->size->size." - ".$barcodes->size->secondSize;
                                        }


                                        if($barcodesize == $size['option']){
                                            $eanbarcode = $barcodes->barcode;


                                            foreach($stock as $key=>$value){
                                                if($value->color == $color['option']){
                                                    foreach($value->sizes as $key=>$erpsize){

                                                        if(empty($erpsize->secondSize)){
                                                            $stocksize = $erpsize->size;
                                                        }else{
                                                            $stocksize = $erpsize->size." - ".$erpsize->secondSize;
                                                        }

                                                        if($stocksize == $size['option']){
                                                            if($erpsize->availableStock > 0){
                                                                $stockquantity = $erpsize->availableStock;
                                                            }
                                                            else{
                                                                $stockquantity = 0;
                                                            }
                                                        }

                                                    }
                                                }
                                            }

                                            $temp = array();
                                            $temp['key'] = "hwp_var_gtin";
                                            $temp['value'] = $eanbarcode;
                                            $newtemp['meta_data'][] = $temp;

                                            $newtemp['regular_price'] = $erpitem->salesListPrice;
                                            $newtemp['manage_stock'] = true;

                                            $newtemp['stock_quantity'] = $stockquantity;
                                            if($stockquantity > 0){
                                                $newtemp['stock_status'] = "instock";
                                            }
                                            $newtemp['attributes'][] = $color;
                                            $newtemp['attributes'][] = $size;
                                            $totalarray['create'][] = $newtemp;
                                        }
                                    }
                                }
                            }
                        }

                        try{
                            $woocommerce->post($vendpoint,$totalarray);
                            $totalarray = array();
                        }
                        catch (HttpClientException $e) {
                            echo $e->getMessage(); // Error message.
                            echo $e->getRequest(); // Last request data.
                            echo $e->getResponse(); // Last response data.
                            die;
                        }
                    }

                    if($createby == "bycolors"){
                        $totalarray = array();
                        $vendpoint = "products/$item->id/variations/batch";
                        foreach($item->attributes as $key=>$attribute){

                            if($attribute->name == "erp_product_id"){
                                $erpid = $attribute->options[0];

                                $type = "GET";
                                $url = $GLOBALS['apiStart']."/api/v2/items/$erpid/?token=".$GLOBALS['token'];
                                $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                                $response_curl = dynamic_curl_call_request($type,$url,$data);

                                if ($response_curl['status'] != 200) {
                                    echo "cURL Error #:" . $response_curl['body'];
                                } else {
                                    $response = $response_curl['body'];
                                    $data = json_decode($response_curl['body']);
                                    $erpitem = $data->items[0];
                                }
                            }

                            if($attribute->name == "color"){
                                foreach($attribute->options as $key=>$color){
                                    $mycolor = $color;
                                }
                            }

                            if($attribute->name == "size"){
                                $sizes = array();
                                $attrid = $attribute->id;
                                foreach($attribute->options as $key=>$size){
                                    $temp = array();
                                    $temp['id'] = $attrid;
                                    $temp['option'] = $size;
                                    $sizes[] = $temp;
                                }
                            }
                        }


                        //getting stock for item

                        $type = "GET";
                        $url = $GLOBALS['apiStart']."/api/v2/items/$erpitem->id/stock?token=".$GLOBALS['token'];
                        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                        $response_curl = dynamic_curl_call_request($type,$url,$data);

                        if ($response_curl['status'] != 200) {
                            echo "cURL Error #:" . $response_curl['body'];
                        } else {
                            $response = $response_curl['body'];
                            $data = json_decode($response_curl['body']);
                            $stock = $data->stock[0]->warehouses[0]->colors;
                        }


                        foreach($erpitem->barcodes as $key=>$barcodes){
                            if($mycolor==$barcodes->color->color){
                                $mysize = array();
                                $mysize['id'] = $attrid;

                                if(empty($barcodes->size->secondSize)){
                                    $barcodesize = $barcodes->size->size;
                                }
                                else {
                                    $barcodesize = $barcodes->size->size." - ".$barcodes->size->secondSize;
                                }

                                $mysize['option'] = $barcodesize;

                                foreach($sizes as $key=>$size){
                                    foreach($stock as $key=>$value){
                                        if($value->color == $mycolor){
                                            foreach($value->sizes as $key=>$erpsize){
                                                if(empty($erpsize->secondSize)){
                                                    $mainsize = $erpsize->size;
                                                }
                                                else{
                                                    $mainsize = $erpsize->size." - ".$erpsize->secondSize;
                                                }
                                                if($mainsize == $mysize['option']){
                                                    if($erpsize->availableStock > 0){
                                                        $stockquantity = $erpsize->availableStock;
                                                    }
                                                    else{
                                                        $stockquantity = 0;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                $eanbarcode = $barcodes->barcode;

                                $newtemp = array();

                                $temp = array();
                                $temp['key'] = "hwp_var_gtin";
                                $temp['value'] = $eanbarcode;
                                $newtemp['meta_data'][] = $temp;

                                $newtemp['regular_price'] = $erpitem->salesListPrice;
                                $newtemp['manage_stock'] = true;
                                $newtemp['stock_quantity'] = $stockquantity;
                                if($stockquantity > 0){
                                    $newtemp['stock_status'] = "instock";
                                }
                                $newtemp['attributes'][] = $mysize;
                                $totalarray['create'][] = $newtemp;
                            }
                        }

                        try{
                            $woocommerce->post($vendpoint,$totalarray);
                            $totalarray = array();
                        }
                        catch (HttpClientException $e) {
                            echo $e->getMessage(); // Error message.
                            echo $e->getRequest(); // Last request data.
                            echo $e->getResponse(); // Last response data.
                            die;
                        }
                    }

                }
            }
        }
    }
    $urlrequested = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);
    $date = Date("Y-m-d H:i:s");
    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`created_at`) values ('update product variations','$minutes','$urlrequested','$date') ";
    $wpdb->query($query);
}


/*
 * This funciton gets called when order is placed.
 * Send order to ERP backend.
 */
add_action('woocommerce_order_status_processing','send_order_to_erp');
function send_order_to_erp( $order_id ,$orderBy = null ){
    global $wpdb;
    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2',
        ]
    );


    $query = "SELECT * from {$wpdb->base_prefix}erp_order_mapping where wp_order_id = ".$order_id;
    $result = $wpdb->get_results($query);
    $id = 0;
    if(!empty($result)){
        $erporderid = $result[0]->erp_order_id;
        $id = $result[0]->id;
    }


    if(empty($result) || $erporderid == 0)
    {
        $order = wc_get_order($order_id );
        $order = $order->get_data();


        $biiling_housenummerextension = "";
        $shipping_housenummerextension = "";
        foreach ($order['meta_data'] as $key => $meta) {
            $meta = $meta->get_data();
            if($meta['key'] == '_billing_address_3'){
                $biiling_housenummerextension = $meta['value'];
            }

            if($meta['key'] == '_shipping_address_3'){
                $shipping_housenummerextension = $meta['value'];
            }
        }


        if(empty($result)){
            $statusoforder = $order['status'];
            $createddate = date("Y-m-d H:i:s");
            $query = "INSERT INTO {$wpdb->base_prefix}erp_order_mapping (`wp_order_id`,`erp_order_id`,`status`,`sync_date`,`request`,`response`,`created_at`) values('$order_id','','$statusoforder','','','','$createddate')";
            $wpdb->query($query);
        }else{
            $id = $result[0]->id;
        }

        if(is_user_logged_in()){
            $user = wp_get_current_user();
            $role = $user->roles[0];
            if($role != "administrator"){
                $query  = "SELECT * FROM {$wpdb->base_prefix}erp_customer_mapping where wordpress_id = ".$user->data->ID . " limit 1";
                $result = $wpdb->get_results($query);
                if(empty($result)){
                    create_user_wp($user->data->ID);
                    $result2= $wpdb->get_results($query);
                    $customer_id = $result2[0]->erp_id;
                }else{
                    $customer_id = $result[0]->erp_id;
                }
            }
            else{
                $order_email = $order['billing']['email'];
                $user = get_user_by('email',$order_email);
                if(!empty($user)){
                    $query  = "SELECT * FROM {$wpdb->base_prefix}erp_customer_mapping where wordpress_id = ".$user->data->ID . " ";
                    $result = $wpdb->get_results($query);
                    if(empty($result) || $result[0]->erp_id == 0){
                        create_user_wp($user->data->ID);
                        $result2= $wpdb->get_results($query);
                        $customer_id = $result2[0]->erp_id;
                    }else{
                        $customer_id = $result[0]->erp_id;
                    }
                }
                else{
                    $random_password = wp_generate_password();

                    // create new user with email as username & newly created pw
                    $userdata = array(
                        'user_login'  =>  $order_email,
                        'user_pass'   =>  $random_password,
                        'user_email' => $order['billing']['email'],
                        'first_name'  =>  $order['billing']['first_name'],
                        'last_name'  =>  $order['billing']['last_name'],
                    );
                    $new_user_id = wp_insert_user($userdata);
                    add_user_meta( $new_user_id, 'billing_first_name', $order['billing']['first_name']);
                    add_user_meta( $new_user_id,'billing_last_name' , $order['billing']['last_name']);
                    add_user_meta( $new_user_id,'billing_company' , $order['billing']['company']);
                    add_user_meta( $new_user_id,'billing_address_1' , $order['billing']['address_1']);
                    add_user_meta( $new_user_id,'billing_address_2' , $order['billing']['address_2']);
                    add_user_meta( $new_user_id,'billing_address_3' , $biiling_housenummerextension);   //TOM
                    add_user_meta( $new_user_id,'billing_city' , $order['billing']['city']);
                    add_user_meta( $new_user_id,'billing_state' ,$order['billing']['state']);
                    add_user_meta( $new_user_id,'billing_postcode' , $order['billing']['postcode']);
                    add_user_meta( $new_user_id,'billing_country' , $order['billing']['country']);
                    add_user_meta( $new_user_id,'billing_email' , $order['billing']['email']);
                    add_user_meta( $new_user_id,'billing_phone' , $order['billing']['phone']);
                    add_user_meta( $new_user_id,'shipping_first_name' , $order['shipping']['first_name']);
                    add_user_meta( $new_user_id,'shipping_last_name' , $order['shipping']['last_name']);
                    add_user_meta( $new_user_id,'shipping_company' , $order['shipping']['company']);
                    add_user_meta( $new_user_id,'shipping_address_1' , $order['shipping']['address_1']);
                    add_user_meta( $new_user_id,'shipping_address_2' , $order['shipping']['address_2']);
                    add_user_meta( $new_user_id,'shipping_address_3' , $shipping_housenummerextension);  //TOM
                    add_user_meta( $new_user_id,'shipping_city' , $order['shipping']['city']);
                    add_user_meta( $new_user_id,'shipping_state' ,$order['shipping']['state']);
                    add_user_meta( $new_user_id,'shipping_postcode' , $order['shipping']['postcode']);
                    add_user_meta( $new_user_id,'shipping_country' , $order['shipping']['country']);
                    create_user_wp($new_user_id);
                    $query  = "SELECT * FROM {$wpdb->base_prefix}erp_customer_mapping where wordpress_id = ".$new_user_id . " limit 1";
                    $result = $wpdb->get_results($query);
                    $customer_id = $result[0]->erp_id;
                }
            }
          } 
        else{
            //get the user email from the order
            $order_email = $order['billing']['email'];

            // check if there are any users with the billing email as user or email
            $email = email_exists( $order_email );
            $user = get_user_by('email',$order_email);

            // if the UID is null, then it's a guest checkout
            if(empty($user) && $email == false){
                // random password with 12 chars
                $random_password = wp_generate_password();

                // create new user with email as username & newly created pw
                $userdata = array(
                    'user_login'  =>  $order_email,
                    'user_pass'   =>  $random_password,
                    'user_email' => $order['billing']['email'],
                    'first_name'  =>  $order['billing']['first_name'],
                    'last_name'  =>  $order['billing']['last_name'],
                );
                $new_user_id = wp_insert_user($userdata);
                add_user_meta( $new_user_id, 'billing_first_name', $order['billing']['first_name']);
                add_user_meta( $new_user_id,'billing_last_name' , $order['billing']['last_name']);
                add_user_meta( $new_user_id,'billing_company' , $order['billing']['company']);
                add_user_meta( $new_user_id,'billing_address_1' , $order['billing']['address_1']);
                add_user_meta( $new_user_id,'billing_address_2' , $order['billing']['address_2']);
                add_user_meta( $new_user_id,'billing_address_3' , $biiling_housenummerextension);   //TOM
                add_user_meta( $new_user_id,'billing_city' , $order['billing']['city']);
                add_user_meta( $new_user_id,'billing_state' ,$order['billing']['state']);
                add_user_meta( $new_user_id,'billing_postcode' , $order['billing']['postcode']);
                add_user_meta( $new_user_id,'billing_country' , $order['billing']['country']);
                add_user_meta( $new_user_id,'billing_email' , $order['billing']['email']);
                add_user_meta( $new_user_id,'billing_phone' , $order['billing']['phone']);
                add_user_meta( $new_user_id,'shipping_first_name' , $order['shipping']['first_name']);
                add_user_meta( $new_user_id,'shipping_last_name' , $order['shipping']['last_name']);
                add_user_meta( $new_user_id,'shipping_company' , $order['shipping']['company']);
                add_user_meta( $new_user_id,'shipping_address_1' , $order['shipping']['address_1']);
                add_user_meta( $new_user_id,'shipping_address_2' , $order['shipping']['address_2']);
                add_user_meta( $new_user_id,'shipping_address_3' , $shipping_housenummerextension);  //TOM
                add_user_meta( $new_user_id,'shipping_city' , $order['shipping']['city']);
                add_user_meta( $new_user_id,'shipping_state' ,$order['shipping']['state']);
                add_user_meta( $new_user_id,'shipping_postcode' , $order['shipping']['postcode']);
                add_user_meta( $new_user_id,'shipping_country' , $order['shipping']['country']);
                create_user_wp($new_user_id);
                $query  = "SELECT * FROM {$wpdb->base_prefix}erp_customer_mapping where wordpress_id = ".$new_user_id . " limit 1";
                $result = $wpdb->get_results($query);
                $customer_id = $result[0]->erp_id;
            }
            else{
                $query  = "SELECT * FROM {$wpdb->base_prefix}erp_customer_mapping where wordpress_id = ".$user->data->ID . " limit 1";
                $result = $wpdb->get_results($query);
                if(empty($result)){
                    create_user_wp($user->data->ID);
                    $result2= $wpdb->get_results($query);
                    $customer_id = $result2[0]->erp_id;
                }
                else{
                    $customer_id = $result[0]->erp_id;
                }
            }
        }
        
        $erporder = array();
        $erporder['webshopOrderId'] = mt_rand(1,999999999999);
        $erporder['orderType'] = 2;
        $erporder['date'] = Date("Y-m-d");
        $erporder['currency'] = $order['currency'];
        $erporder['exchangeRate'] = "1.0000000";
        $erporder['customerId'] = $customer_id; //$order['customer_id'];
        $erporder['brandId'] = "1";
        $erporder['discountPercentage'] = "0.00";
        $erporder['chargePercentage'] = "0.00";
        $erporder['shippingCosts'] = $order['shipping_total'];
        $erporder['comment'] = $order['customer_note'];
        $erporder['internalComment'] = "";
        $erporder['amount'] = $order['total'];

        //$erporder['shippingAddress']['id'] = "a-14";

        //getting countryid for country
        $type = "GET";
        $url = $GLOBALS['apiStart']."/api/v2/countries/?token=".$GLOBALS['token'];
        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
        $response_curl = dynamic_curl_call_request($type,$url,$data);

        if ($response_curl['status'] != 200) {
            echo "cURL Error #:" . $response_curl['body'];
        } else {
            $response = $response_curl['body'];
            $countryid = 2;
            $countriesdata = json_decode($response_curl['body']);
            foreach($countriesdata->countries as $key=>$country){
                if($country->iso2 == $order['shipping']['country']){
                    $countryid = $country->id;
                }
            }
        }


        $erporder['shippingAddress']['name'] = $order['shipping']['first_name']." ".$order['shipping']['last_name'];
        $erporder['shippingAddress']['street'] = $order['shipping']['address_1'];
        $erporder['shippingAddress']['housenumber'] = $order['shipping']['address_2'];
        $erporder['shippingAddress']['housenumberExtension'] = $shipping_housenummerextension;
        $erporder['shippingAddress']['postalCode'] = $order['shipping']['postcode'];
        $erporder['shippingAddress']['countryId'] = $countryid; //2;
        $erporder['shippingAddress']['city'] = $order['shipping']['city'];
        $erporder['shippingAddress']['email'] = $order['billing']['email'];
        $erporder['shippingAddress']['phone'] = $order['billing']['phone'];

        $ordertotal = 0;
        $flag = 0;
        foreach($order['line_items'] as $key=>$item){
            $myitem = array();
            $item = $item->get_data();
            $erp_product_id = 0;

            $itemsql = "SELECT createdby FROM {$wpdb->base_prefix}erp_item_mapping where wp_item_id = ".$item['product_id'];
            $itemresult = $wpdb->get_results($itemsql);

            if(empty($itemresult)){
                $createby = "byitems";
            }
            else{
                $createby = $itemresult[0]->createdby;
            }

            if($createby == "bycolors"){
                if(isset($item['product_id'])){
                    // print_r($productAttribute->attributes);die;
                    try {
                        $endpoint = 'products/'.$item['product_id'];
                        $product = $woocommerce->get($endpoint);
                        foreach($product->attributes as $key=>$attr){
                            if($attr->name == "color"){
                                $itemcolor = $attr->options[0];
                            }
                        }
                        $endpointAttr = 'products/'.$item['product_id'].'/variations/'.$item['variation_id'];
                        $productAttribute = $woocommerce->get($endpointAttr);
                    } catch (HttpClientException $e) {
                        echo $e->getMessage(); // Error message.
                        echo $e->getRequest(); // Last request data.
                        echo $e->getResponse(); // Last response data.
                        die;
                    }

                    $erp_product_id = $product->attributes[0]->options[0];

                    /* Get productinfo from it's perfect ERP */
                    $type = "GET";
                    $url = $GLOBALS['apiStart']."/api/v2/items/$erp_product_id/?token=".$GLOBALS['token'];
                    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                    $response_curl = dynamic_curl_call_request($type,$url,$data);

                    if ($response_curl['status'] != 200) {
                        echo "cURL Error #:" . $response_curl['body'];
                    } else {
                        $response = $response_curl['body'];
                        $data = json_decode($response_curl['body']);
                        
                        $attributeValue = array();
                        foreach ($productAttribute->attributes as $key => $value) {
                            if($value->name == "size"){
                                $itemsize = $value->option;
                            }
                        }
                        $erpproduct = json_decode($response_curl['body']);
                        $erpproduct = $erpproduct->items[0];
                    }
                }
                $erpAttrBarcodes = array();
                foreach ($erpproduct->barcodes as $key => $value) {

                    if(empty($value->size->secondSize)){
                        $barcodesize = $value->size->size;
                    }
                    else {
                        $barcodesize = $value->size->size." - ".$value->size->secondSize;
                    }


                    if((strtolower($itemcolor) == strtolower($value->color->color)) && ($itemsize == $barcodesize)){
                        array_push($erpAttrBarcodes,array("barcode"=>$value->barcode,"color"=>$value->color->color,"size"=>$value->size->size));
                    }
                }
            }

            if($createby == "byitems"){
                if(isset($item['product_id'])){

                    try {
                        $endpoint = 'products/'.$item['product_id'];
                        $product = $woocommerce->get($endpoint);
                        $endpointAttr = 'products/'.$item['product_id'].'/variations/'.$item['variation_id'];
                        $productAttribute = $woocommerce->get($endpointAttr);
                    } catch (HttpClientException $e) {
                        echo $e->getMessage(); // Error message.
                        echo $e->getRequest(); // Last request data.
                        echo $e->getResponse(); // Last response data.
                        die;
                    }

                    $erp_product_id = $product->attributes[0]->options[0];

                    /* Get productinfo from it's perfect ERP */
                    $type = "GET";
                    $url = $GLOBALS['apiStart']."/api/v2/items/$erp_product_id/?token=".$GLOBALS['token'];
                    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                    $response_curl = dynamic_curl_call_request($type,$url,$data);

                    if ($response_curl['status'] != 200) {
                        echo "cURL Error #:" . $response_curl['body'];
                    } else {
                        $response = $response_curl['body'];

                        $attributeValue = array();
                        foreach ($productAttribute->attributes as $key => $value) {
                            array_push($attributeValue,array($value->name=>$value->option));
                        }
                        if($attributeValue){
                            $prodFinalAttr = array_merge($attributeValue[0],$attributeValue[1]);
                        }
                        $erpproduct = json_decode($response_curl['body']);
                        $erpproduct = $erpproduct->items[0];
                    }
                }
                $erpAttrBarcodes = array();
                foreach ($erpproduct->barcodes as $key => $value) {

                    if(empty($value->size->secondSize)){
                        $barcodesize = $value->size->size;
                    }
                    else {
                        $barcodesize = $value->size->size." - ".$value->size->secondSize;
                    }

                    if((strtolower($prodFinalAttr['color']) == strtolower($value->color->color)) && ($prodFinalAttr['size'] == $barcodesize)){
                        array_push($erpAttrBarcodes,array("barcode"=>$value->barcode,"color"=>$value->color->color,"size"=>$barcodesize));
                    }
                }
            }

            $myitem['barcode'] = $erpAttrBarcodes[0]['barcode'];
            $myitem['itemId'] = $erp_product_id; //$erp_product_id;
            $myitem['item'] = $item['name'];
            $myitem['quantity'] = $item['quantity'];

            $ordertotal = $ordertotal + $erpproduct->salesListPrice;

            $myitem['price'] = $item['total']+$item['total_tax'];
            $myitem['salesListPrice'] = $item['total']+$item['total_tax'];
            $myitem['amount'] = $item['total']+$item['total_tax'];
            $myitem['amountForeignCurrency'] = $item['total']+$item['total_tax'];


            $myitem['vat'] = "0.00";
            $myitem['discountPercentage'] = "0.00";


            $erporder['items'][] = $myitem;
        }

        $erporderstring = json_encode($erporder);

        $type = "POST";
        $url = $GLOBALS['apiStart']."/api/v2/orders/?token=".$GLOBALS['token'];
        $data = $erporder;
        $response_curl = dynamic_curl_call_request($type,$url,$data);

        $response = "";
        if ($response_curl['status'] != 200) {
            echo "cURL Error #:" . $response_curl['body'];
            $err = $response_curl['body'];
        } else {
            $response = $response_curl['body'];
          }
            
        $urlrequested = $GLOBALS['apiStart']."/api/v2/orders/?token=".$GLOBALS['token'];
        $urlrequested = $erporderstring;
        $resultresponse = json_encode($response);
        $date = date("Y-m-d H:i:s");
        $statusoforder = $order['status'];
        $createddate = date("Y-m-d H:i:s");
        $modifiedat = date("Y-m-d H:i:s");

        if ($err) {
            $resultresponse = json_encode($response) . "____" . $err;

            $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`url_requested`,`error_code`,`created_at`) values ('erp_order_sync_admin','$urlrequested','$resultresponse','$date') ";
            $wpdb->query($query);

            $query = "UPDATE {$wpdb->base_prefix}erp_order_mapping SET status = '".$statusoforder."' ,request = '".$urlrequested."',response = '".$resultresponse."' , modified_at = '".$modifiedat."' where id = ".$id;
            $wpdb->query($query);

        } else {
            $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`url_requested`,`error_code`,`created_at`) values ('erp_order_sync_admin','$urlrequested','$resultresponse','$date') ";
            $wpdb->query($query);


            $decode = json_decode($response);

            if(isset($decode->insertId)){
                $syncdate = date("Y-m-d H:i:s");
                $query = "SELECT * from {$wpdb->base_prefix}erp_order_mapping where wp_order_id = ".$order_id;
                $result = $wpdb->get_results($query);
                if(empty($result)){
                    $query = "INSERT INTO {$wpdb->base_prefix}erp_order_mapping (`wp_order_id`,`erp_order_id`,`status`,`sync_date`,`request`,`response`,`created_at`) values('$order_id','$decode->insertId','$statusoforder','$syncdate','$urlrequested','$resultresponse','$createddate')";
                }
                else{

                    $id = $result[0]->id;
                    $modifiedat = date("Y-m-d H:i:s");
                    $query = "UPDATE {$wpdb->base_prefix}erp_order_mapping SET erp_order_id =  '$decode->insertId' ,status = '$statusoforder',sync_date = '$syncdate' , modified_at = '$modifiedat' where id = ".$id;
                }
            }
            else{
                $query = "UPDATE {$wpdb->base_prefix}erp_order_mapping SET status = '$statusoforder' ,request = '$urlrequested' ,response = '".$resultresponse."' , modified_at = '".$modifiedat."' where id = ".$id;
            }

            $wpdb->query($query);

            if(empty($err)){
                $decode = json_decode($response);
                $data = [
                    'note' => 'its_erp_order_id = '.$decode->insertId
                ];
            }else{
                $data = [
                    'note' => $response
                ];
            }

            $woocommerce->post('orders/'.$order_id.'/notes', $data);

            $data1 = [
                'note' => $response
            ];

            $woocommerce->post('orders/'.$order_id.'/notes', $data1);

            // for specify order by which user in erp sysytems
            if(empty($orderBy)){
                $orderBy = "User";
            }else{
                $date = Date("Y-m-d H:i:s");
                $minutes = "1";
                $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`created_at`) values ('erp_order_sync_admin','$minutes','$date') ";
                $wpdb->query($query);
            }

            $data2 = [
                'note' => $orderBy
            ];
            $woocommerce->post('orders/'.$order_id.'/notes', $data2);

        }

    }
}


/*
* This function is display list of orders not synced with erp.
* Notification value 0 is default value of display notification.
* Notification value 1 is closed by admin.
*/
add_action('admin_notices', 'author_admin_notice');
function author_admin_notice(){
    global $wpdb;
    
    $query = "SELECT * from {$wpdb->base_prefix}erp_order_mapping where notification = 0 ORDER BY wp_order_id DESC LIMIT 10";
    $result = $wpdb->get_results($query);

    if(!empty($result)){
      foreach ($result as $order){
        $order_id = $order->wp_order_id;
        if($order->erp_order_id > 0 || !empty($order->erp_order_id)){

        }else{
            $url = admin_url( $path= "post.php?post=".$order_id."&action=edit", $scheme = "");
            echo '<div class="notice notice-error is-dismissible order-notices" data-id="'.$order_id.'" id="'.$order_id.'">
                <p><strong>Warning!</strong> Order id #'.$order_id.' is not synced with ERP ,Check <a href="'.$url.'"> Order Details </a></p>
            </div>';
        }
      }
    }
}



/*
* This function is change in orders notification column update status 0 to 1 
* 0 is default value of display notification.
* 1 is used for notification closed by admin.
*/
add_action('wp_ajax_order_error_notification_close', 'order_error_notification_close');
function order_error_notification_close(){
    global $wpdb;
    if(!empty($_POST['order_id'])){
        $order_id = intval($_POST['order_id']);
        $query = "UPDATE {$wpdb->base_prefix}erp_order_mapping SET notification = 1  where wp_order_id = ".$order_id;
        $wpdb->query($query);
    }else{

    }
}




/*
 * This function gets called when order status is updated manually in woocommerce.
 */
// add the action
if(is_admin()){
    add_action( 'woocommerce_order_status_changed', 'my_action');
    function my_action( $order_id ) {
        global $wpdb;
        $woocommerce = new Client(
            $GLOBALS['url'],
            $GLOBALS['consumerkey'],
            $GLOBALS['consumersecret'],
            [
                'wp_api' => true,
                'verify_ssl'=>false,
                'query_string_auth' => true,
                'timeout' => 0,
                'version' => 'wc/v2',
            ]
        );

        $sql = "SELECT * FROM {$wpdb->base_prefix}erp_order_mapping where wp_order_id = ".$order_id;
        $result = $wpdb->get_results($sql);

        if(empty($result)){
            $endpoint = "orders/$order_id/notes";
            $response = $woocommerce->get($endpoint,array('per_page' => 100));

            $x = 0;

            foreach($response as $key=>$note){
                if (strpos($note->note, 'insertId') !== false) {
                    $mystringarray = explode(",",$note->note);
                    $neededarray = explode(":",$mystringarray[1]);
                    $idarray = $neededarray[1];
                    $id = str_replace('"','',$idarray);
                    $x++;


                    $type = "GET";
                    $url = $GLOBALS['apiStart']."/api/v2/orders/$id/?token=".$GLOBALS['token'];
                    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                    $response_curl = dynamic_curl_call_request($type,$url,$data);

                    if ($response_curl['status'] != 200) {
                        echo "cURL Error #:" . $response_curl['body'];
                    } else {
                        $response = $response_curl['body'];
                        $data = json_decode($response_curl['body']);

                        if(empty($response)){
                            send_order_to_erp($order_id,"order_status_change_by");
                        }
                        else{
                            //order already synced.
                        }
                    }
                }
                else{
                    send_order_to_erp($order_id,"order_status_change_by");
                }
            }
        }
        else{
            $erporderid = $result[0]->erp_order_id;
            $data2 = [
                'note' => "Please change order status in order no : ".$erporderid
            ];
            $woocommerce->post('orders/'.$order_id.'/notes', $data2);
        }
    };
}


/*
 * this function creates user when you call this function
 */
function create_user_wp($userid){
    global $wpdb;
    $user = get_userdata( $userid );
    $meta = get_user_meta($userid);

    $erpuser = array();
    $erpuser['active'] = 1;
    $erpuser['customerType'] = 6;
    $erpuser['postingGroup'] = 5;
    $erpuser['postingGroupVat'] = 2;
    $erpuser['customerNo'] = 0;
    $erpuser['customerStatus'] = null;
    $erpuser['customerGroup'] = null;
    $erpuser['priority'] = 0;
    $erpuser['language'] = "NL";
    $erpuser['orderConfirmationPreOrder'] = 0;
    $erpuser['orderConfirmationDirectOrder'] = 0;
    $erpuser['invoiceInterval'] = 0;
    $erpuser['name'] = $meta['first_name'][0]." ".$meta['last_name'][0];
    $erpuser['companyName'] = $meta['billing_company'][0];
    $erpuser['legalCompanyName'] = $meta['billing_company'][0];
    $erpuser['website'] = $user->data->user_url;
    $erpuser['email'] = $user->data->user_email;
    $erpuser['creditLimit'] = "0.00";
    $erpuser['language'] = "EN";
    $add = array();
    $add['contactPerson'] = $meta['first_name'][0]." ".$meta['last_name'][0];
    $add['street'] = $meta['billing_address_1'][0];
    $add['housenumber'] = $meta['billing_address_2'][0];
    $add['housenumberExtension'] = $meta['billing_address_3'][0];  //TOM
    $add['postalCode'] = $meta['billing_postcode'][0];
    $add['city'] = $meta['billing_city'][0];

    //getting countryid for country
    $type = "GET";
    $url = $GLOBALS['apiStart']."/api/v2/countries/?token=".$GLOBALS['token'];
    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
    $response_curl = dynamic_curl_call_request($type,$url,$data);

    if ($response_curl['status'] != 200) {
        echo "cURL Error #:" . $response_curl['body'];
    } else {
        $response = $response_curl['body'];
        $countryid = 2;
        $countriesdata = json_decode($response_curl['body']);
        foreach($countriesdata->countries as $key=>$country){
            if($country->iso2 == $meta['billing_country'][0]){
                $countryid = $country->id;
            }
        }
    }


    $add['countryId'] = $countryid;
    $add['phone'] = $meta['billing_phone'][0];
    $add['mobile'] = $meta['billing_phone'][0];
    $add['email'] = $user->data->user_email;

    $erpuser['addresses'][1] = $add;


    $erpuserstring = json_encode($erpuser , JSON_UNESCAPED_SLASHES );

    //create this user in erp system.
    $type = "POST";
    $url = $GLOBALS['apiStart']."/api/v2/customers/&token=".$GLOBALS['token'];
    $data = $erpuser;
    $response_curl = dynamic_curl_call_request($type,$url,$data);

    if ($response_curl['status'] != 200) {
        echo "cURL Error #:" . $response_curl['body'];
    } else {
        $response = $response_curl['body'];
        $data = json_decode($response_curl['body']);
        $id = $data->insertId;


        $type = "GET";
        $url = $GLOBALS['apiStart']."/api/v2/customers/$id/&token=".$GLOBALS['token'];
        $mydata = array();
        $mydata['brand']['id'] = 1;
        $data = $mydata;
        $response_curl = dynamic_curl_call_request($type,$url,$data);

        if ($response_curl['status'] != 200) {
            echo "cURL Error #:" . $response_curl['body'];
            $response = $response_curl['body'];
            $resulterror = json_encode($response);
            $query = "INSERT INTO {$wpdb->base_prefix}erp_sync_logs (`module`,`url_requested`,`error_code`) values ('customer_mapping_erp','$erpuserstring','$resulterror') ";
            $wpdb->query($query);
        } else {
            $response = $response_curl['body'];
            $date = Date("Y-m-d H:i:s");
            $query = "INSERT INTO {$wpdb->base_prefix}erp_customer_mapping (`wordpress_id`,`erp_id`,`created_at`) values ('$userid','$id','$date') ";
            $x = $wpdb->query($query);
            return $x;
        }
    }
}



/*
* this function is using for call single order sync with erp.
* require order id
*/
add_action('wp_ajax_erp_order_sync', 'erp_order_sync');
function erp_order_sync(){
    $orderId = intval($_POST['post_var']['orderId']);
    if(!empty($_POST['post_var']['erpId'])){
        $erpId = intval($_POST['post_var']['erpId']);
    }else{
        $erpId = "";
    }
    if(empty($erpId)){
        //call the inbuilt function that sync order with erp
        $orderBy = 'admin';
        send_order_to_erp($orderId,$orderBy);
    }
}



/*
* this function is using for all orders status with erp.
* require order id
*/
add_action('wp_ajax_sync_orders_status', 'sync_orders_status');
function sync_orders_status(){
    global $wpdb;

    $type = "GET";
    $url = $GLOBALS['apiStart']."/api/v2/orders/?token=".$GLOBALS['token'];
    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
    $response_curl = dynamic_curl_call_request($type,$url,$data);

    if ($response_curl['status'] != 200) {
        echo "cURL Error #:" . $response_curl['body'];
        $err = $response_curl['body'];
        $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`error_code`) values ('update_erp_order_status','$err')";
        $wpdb->query($query);
    } else {
        $response = $response_curl['body'];
        $erpOrders = json_decode($response_curl['body']);
        $erpOrderlist = array();
        foreach ($erpOrders->orders as $key => $value) {
            array_push($erpOrderlist,array("erp_order_id"=>$value->id,"erp_order_status"=>$value->status));
        }
    }

    $datetime1 = strtotime(Date("Y-m-d H:i:s"));
    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    set_time_limit(0);
    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 100000000000000000,
            'version' => 'wc/v2',
        ]
    );
    try {
        foreach ($erpOrderlist as $key => $erpvalue) {
            $sql = "SELECT * FROM {$wpdb->base_prefix}erp_order_mapping where erp_order_id = ".$erpvalue['erp_order_id'];
            $order = $wpdb->get_results($sql);
            if(!empty($order)){
                $status = $erpvalue['erp_order_status'];
                if($status == 0){
                    $status = "processing";
                }if($status == 1){
                    $status = "completed";
                }if($status == 2){
                    $status = "cancelled";
                }
                $data = [
                    'status' => $status
                ];
                if($erpvalue['erp_order_id'] == $order[0]->erp_order_id)
                {
                    $orderEndpoint = "orders/".$order[0]->wp_order_id;
                    $woocommerce->put($orderEndpoint, $data);
                    $query = "UPDATE {$wpdb->base_prefix}erp_order_mapping set status = '".$status."' where wp_order_id = ".$order[0]->wp_order_id;
                    $wpdb->query($query);
                }
            }
        }
    } catch (HttpClientException $e) {
        echo $e->getMessage(); // Error message.
        echo $e->getRequest(); // Last request data.
        echo $e->getResponse(); // Last response data.
        die;
    }
    $urlrequested = $GLOBALS['apiStart']."/api/v2/orders/?token=".$GLOBALS['token'];
    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);
    $date = Date("Y-m-d H:i:s");
    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`created_at`) values ('update_order_status','$minutes','$urlrequested','$date') ";
    $wpdb->query($query);
}


/*
* this function is using for all orders status with erp.
* require order id
*/
add_action('wp_ajax_reset_product_category', 'reset_product_category');
function reset_product_category(){
    global $wpdb;
    $deletequery = "DELETE relations.*, taxes.*, terms.*
                    FROM {$wpdb->base_prefix}term_relationships AS relations
                    INNER JOIN {$wpdb->base_prefix}term_taxonomy AS taxes
                    ON relations.term_taxonomy_id=taxes.term_taxonomy_id
                    INNER JOIN {$wpdb->base_prefix}terms AS terms
                    ON taxes.term_id=terms.term_id
                    WHERE object_id IN (SELECT ID FROM {$wpdb->base_prefix}posts WHERE post_type='product');";
    $wpdb->query($deletequery);
    $wpdb->query("DELETE FROM {$wpdb->base_prefix}postmeta WHERE post_id IN (SELECT ID FROM wp_posts WHERE post_type = 'product');");
    $wpdb->query("DELETE FROM {$wpdb->base_prefix}posts WHERE post_type = 'product';");


    $wpdb->query("DELETE a,c FROM {$wpdb->base_prefix}terms AS a
              LEFT JOIN {$wpdb->base_prefix}term_taxonomy AS c ON a.term_id = c.term_id
              LEFT JOIN {$wpdb->base_prefix}term_relationships AS b ON b.term_taxonomy_id = c.term_taxonomy_id
              WHERE c.taxonomy = 'product_tag'");
    $wpdb->query("DELETE a,c FROM {$wpdb->base_prefix}terms AS a
              LEFT JOIN {$wpdb->base_prefix}term_taxonomy AS c ON a.term_id = c.term_id
              LEFT JOIN {$wpdb->base_prefix}term_relationships AS b ON b.term_taxonomy_id = c.term_taxonomy_id
              WHERE c.taxonomy = 'product_cat'");

    $wpdb->query("TRUNCATE TABLE {$wpdb->base_prefix}erp_category;");
    $wpdb->query("TRUNCATE TABLE {$wpdb->base_prefix}erp_category_mapping;");
    $wpdb->query("TRUNCATE TABLE {$wpdb->base_prefix}erp_item_mapping;");
    $wpdb->query("Update wp_postmeta set meta_value ='' WHERE meta_key = '_sku'");

    $wpdb->query("TRUNCATE TABLE {$wpdb->base_prefix}erp_color_image_mapping;");

}


/*
 * this function updates a product one by one when clicked on update product for particular product
 */
add_action('wp_ajax_sw_updateproduct','updateproduct');
function updateproduct(){
    $temp = false;
    $datetime1 = strtotime(Date("Y-m-d H:i:s"));
    global $wpdb;
    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2',
        ]
    );

    if(isset($_POST['product_id'])) {
        $productid = intval($_POST['product_id']);

        $itemsql = "SELECT erp_item_id FROM {$wpdb->base_prefix}erp_item_mapping where wp_item_id = ".$productid;
        $result = $wpdb->get_results($itemsql);

        if(empty($result)){
            $erpid = "";
        }
        else{
            $erpid = $result[0]->erp_item_id;
        }

        if($erpid != ""){

          $type = "GET";
          $url = $GLOBALS['apiStart']."/api/v2/items/$erpid/?token=".$GLOBALS['token'];
          $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
          $response_curl = dynamic_curl_call_request($type,$url,$data);

          if ($response_curl['status'] != 200) {
              echo "cURL Error #:" . $response_curl['body'];
          } else {
              $response = $response_curl['body'];
              $data = json_decode($response_curl['body']);
              $erpitem = $data->items[0];
            }

            //getting attribute id's
            $erpidarray = array();
            $myarray = array();
            $erpattrid = 1;

            try{
                $mycounter = 0;
                $endpoint = 'products/attributes';
                $result = $woocommerce->get($endpoint);
                foreach($result as $key=>$value){
                    if($value->name == "erp_product_id"){
                        $erpattrid = $value->id;
                        $mycounter++;
                    }
                }
            }
            catch(HttpClientException $e){
                echo $e->getMessage(); // Error message.
                echo $e->getRequest(); // Last request data.
                echo $e->getResponse(); // Last response data.
                die;
            }
            if($mycounter == 0){
                try {
                    $endpoint = 'products/attributes';
                    $attribute = array();
                    $attribute['name'] = "erp_product_id";
                    $attribute['slug'] = "pa_erp_product_id";
                    $x = $woocommerce->post($endpoint, $attribute);
                    $erpattrid = $x->id;

                }
                catch (HttpClientException $e) {
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }
            }

            try{
                $mycounter = 0;
                $sizecounter = 0;
                $barcodecounter = 0;
                $endpoint = 'products/attributes';
                $colorresult = $woocommerce->get($endpoint);
                foreach($colorresult as $key=>$value){
                    if($value->name == "color"){
                        $colorid = $value->id;
                        $mycounter++;
                    }
                    if($value->name == "size"){
                        $sizeid = $value->id;
                        $sizecounter++;
                    }
                    if($value->name == "barcode"){
                        $barcodeid = $value->id;
                        $barcodecounter++;
                    }
                }
            }
            catch(HttpClientException $e){
                echo $e->getMessage(); // Error message.
                echo $e->getRequest(); // Last request data.
                echo $e->getResponse(); // Last response data.
                die;
            }

            if($mycounter == 0){
                try {
                    $endpoint = 'products/attributes';
                    $attribute = array();
                    $attribute['name'] = "color";
                    $attribute['slug'] = "pa_color";
                    $x = $woocommerce->post($endpoint, $attribute);
                    $colorid = $x->id;

                }
                catch (HttpClientException $e) {
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }
            }
            if($sizecounter == 0){
                try {
                    $endpoint = 'products/attributes';
                    $attribute = array();
                    $attribute['name'] = "size";
                    $attribute['slug'] = "pa_size";
                    $x = $woocommerce->post($endpoint, $attribute);
                    $sizeid = $x->id;

                }
                catch (HttpClientException $e) {
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }
            }
            if($barcodecounter == 0){
                try {
                    $endpoint = 'products/attributes';
                    $attribute = array();
                    $attribute['name'] = "barcode";
                    $attribute['slug'] = "pa_barcode";
                    $x = $woocommerce->post($endpoint, $attribute);
                    $barcodeid = $x->id;

                }
                catch (HttpClientException $e) {
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }
            }

            $itemsql = "SELECT createdby FROM {$wpdb->base_prefix}erp_item_mapping where wp_item_id = ".$productid;
            $itemresult = $wpdb->get_results($itemsql);
            if(empty($itemresult)){
                $createby = "byitems";
            }
            else{
                $createby = $itemresult[0]->createdby;
            }

            if($createby == "byitems"){
                //updating attributes and products
                $newproduct = array();

                $newproduct['name'] = $erpitem->item;
                $newproduct['short_description'] = $erpitem->description->en;
                $newproduct['sku'] = $erpitem->itemNumber;
                $newproduct['regular_price'] = $erpitem->salesListPrice;

                $attributes["id"] = $erpattrid;
                $attributes['options'][] = "$erpitem->id";
                $newproduct['attributes'][] = $attributes;

                foreach($erpitem->webshopCategories as $key=>$category){
                    $sql = "SELECT wp_category_id from {$wpdb->base_prefix}erp_category_mapping where erp_category_id = ".$category->id;
                    $result = $wpdb->get_results($sql);
                    if(!empty($result)){
                        $categoryid = $result[0]->wp_category_id;
                    }
                    $newproduct['categories'][] = ['id' => $categoryid];
                }

                $newproduct['type'] = "variable";

                $newproduct['images'] = array();
                $date = date("Y-m-d H:i:s");

                $colors = array();

                foreach($erpitem->colors as $key=>$color){
                    array_push($colors,$color->color);
                    foreach($color->images as $key=>$images){
                        $newproduct['images'][]['src'] = "$images->url";
                    }
                }

                $color = array();
                $color["id"] = $colorid;
                $color["visible"] = true;
                $color["variation"] = true;
                $color['options'] = $colors;
                $newproduct['attributes'][] = $color;

                $sizes = array();
                foreach($erpitem->sizes as $key=>$value){
                    $mysize = "";
                    if(empty($value->secondSize)){
                        $mysize = $value->size;
                    }
                    else{
                        $mysize = $value->size." - ".$value->secondSize;
                    }
                    array_push($sizes,$mysize);
                }

                $size = array();
                $size["id"] = $sizeid;
                $size["visible"] = true;
                $size["variation"] = true;
                $size['options'] = $sizes;
                $newproduct['attributes'][] = $size;


                try{
                    $endpoint = "products/$productid";
                    $woocommerce->put($endpoint,$newproduct);

                    updateproductvariation($erpitem,$createby,$productid);

                }
                catch(HttpClientException $e){
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }

            }


            if($createby == "bycolors"){
                $newproduct = array();

                
                $newproduct['short_description'] = $erpitem->description->en;
                $newproduct['sku'] = $erpitem->itemNumber;

                $attributes["id"] = $erpattrid;
                $attributes['options'][] = "$erpitem->id";
                $newproduct['attributes'][] = $attributes;

                foreach($erpitem->webshopCategories as $key=>$category){
                    $sql = "SELECT wp_category_id from {$wpdb->base_prefix}erp_category_mapping where erp_category_id = ".$category->id;
                    $result = $wpdb->get_results($sql);
                    if(!empty($result)){
                        $categoryid = $result[0]->wp_category_id;
                    }
                    $newproduct['categories'][] = ['id' => $categoryid];
                }

                $newproduct['type'] = "variable";
                $newproduct['regular_price'] = $erpitem->salesListPrice;
                $newproduct['description'] = $erpitem->description->en;     //TOM
                $newproduct['subnaam'] = $erpitem->model->model;    //TOM



                $newproduct['images'] = array();
                $date = date("Y-m-d H:i:s");

                $getproduct = "products/$productid";
                $product = $woocommerce->get($getproduct);

                $onlycolor = "";
                foreach($product->attributes as $key=>$attribute){

                    if($attribute->name == "color"){
                        foreach($attribute->options as $key=>$color){
                            $onlycolor = $color;
                        }
                    }

                    if($attribute->name == "size"){
                        $sizes = array();
                        $attrid = $attribute->id;
                        foreach($attribute->options as $key=>$size){
                            $temp = array();
                            $temp['id'] = $attrid;
                            $temp['option'] = $size;
                            $sizes[] = $temp;
                        }
                    }

                }


                $mycolor = array();
                $mycolor["id"] = $colorid;
                $mycolor["visible"] = true;
                $mycolor["variation"] = false;
                $mycolor['options'] = $onlycolor;
                $newproduct['attributes'][] = $mycolor;

                $sizes = array();
                foreach($erpitem->sizes as $key=>$value){
                    $mysize = "";
                    if(empty($value->secondSize)){
                        $mysize = $value->size;
                    }
                    else{
                        $mysize = $value->size." - ".$value->secondSize;
                    }
                    array_push($sizes,$mysize);
                }

                $size = array();
                $size["id"] = $sizeid;
                $size["visible"] = true;
                $size["variation"] = true;
                $size['options'] = $sizes;
                $newproduct['attributes'][] = $size;


                try{
                    $endpoint = "products/$productid";
                    $x = $woocommerce->put($endpoint,$newproduct);

                    updateproductvariation($erpitem,$createby,$productid);
                }
                catch(HttpClientException $e){
                    echo $e->getMessage(); // Error message.
                    echo $e->getRequest(); // Last request data.
                    echo $e->getResponse(); // Last response data.
                    die;
                }
            }
        }
    }

    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);
    $date = Date("Y-m-d H:i:s");
    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`created_at`) values ('update_product_particular','$minutes','$date') ";
    $wpdb->query($query);
}



/**
 * This function updates stocks for the products
 */
add_action('wp_ajax_sw_updatestocks','updatestocks');
function updatestocks(){
    $countfrom = 1;
    $countto = 10000000;

    if(isset($_POST['item_count'])){
        $countstring = sanitize_text_field($_POST['item_count']);
        $countarray = explode("_",$countstring);
        $countfrom = $countarray[0];
        $countto = $countarray[1];
    }

    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    set_time_limit(0);
    global $wpdb;
    $datetime1 = strtotime(Date("Y-m-d H:i:s"));
    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2',
        ]
    );

    $query = "SELECT wp_item_id from {$wpdb->base_prefix}erp_item_mapping";
    $result = $wpdb->get_results($query);

    $type = "GET";
    $url = $GLOBALS['apiStart']."/api/v2/stock/?token=".$GLOBALS['token'];
    $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
    $response_curl = dynamic_curl_call_request($type,$url,$data);

    if ($response_curl['status'] != 200) {
        echo "cURL Error #:" . $response_curl['body'];
    } else {
        $response = $response_curl['body'];
        $data = json_decode($response_curl['body']);
        $mycounter = 0;
        foreach($result as $key=>$itemid) {

            $mycounter++;

            if($mycounter >= $countfrom && $mycounter <= $countto){
                $finalarray = array();
                $newquery = "SELECT erp_item_id from {$wpdb->base_prefix}erp_item_mapping where wp_item_id = " . $itemid->wp_item_id;
                $newresult = $wpdb->get_results($newquery);
                $erpitemid = $newresult[0]->erp_item_id;
                foreach($data->stock as $key=>$stock){
                    if($stock->itemId == $erpitemid){
                        $itemsql = "SELECT createdby FROM {$wpdb->base_prefix}erp_item_mapping where wp_item_id = ".$itemid->wp_item_id;
                        $itemresult = $wpdb->get_results($itemsql);
                        if(empty($itemresult)){
                            $createby = "byitems";
                        }
                        else{
                            $createby = $itemresult[0]->createdby;
                        }

                        $type = "GET";
                        $url = $GLOBALS['apiStart']."/api/v2/items/$erpitemid/prices?token=".$GLOBALS['token'];
                        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
                        $response_curl = dynamic_curl_call_request($type,$url,$data);

                        if ($response_curl['status'] != 200) {
                            echo "cURL Error #:" . $response_curl['body'];
                        } else {
                            $response = $response_curl['body'];
                            $pricedata = json_decode($response_curl['body']);
                            $realdata = $pricedata->prices[0]->pricegroups;
                            foreach($realdata as $key=>$mydata) {
                                if ($key == 1) {
                                    foreach ($mydata->countries as $mykey => $prices) {
                                        if($mykey == 7){
                                            $nok = $prices->salesListPrice;
                                        }
                                        if($mykey == 9){
                                            $sek = $prices->salesListPrice;
                                        }
                                        if($mykey == 10){
                                            $dkk = $prices->salesListPrice;
                                        }
                                        if($mykey == 12){
                                            $chf = $prices->salesListPrice;
                                        }
                                    }
                                }
                            }
                        }

                        if($createby == "byitems"){
                            $query = new \WP_Query( array(
                                'post_parent' => $itemid->wp_item_id,
                                'post_status' => 'publish',
                                'post_type' => 'product_variation',
                                'posts_per_page' => -1,
                            ) );

                            $allvariations = array();
                            if($query->have_posts()){
                                while ($query->have_posts()) {
                                    $query->next_post();
                                    $allvariations[] = $query->post;
                                }
                                wp_reset_postdata();
                            }
                            wp_reset_query();

                            $i = 0;
                            if(!empty($allvariations)){
                                foreach($allvariations as $key=>$variation){
                                    $myvariation = wc_get_product($variation->ID);
                                    $myvariation = $myvariation->get_data();
                                    $color = $myvariation['attributes']['pa_color'];
                                    $size = $myvariation['attributes']['pa_size'];

                                    foreach($stock->warehouses as $key=>$warehouse){
                                        foreach ($warehouse->colors  as $key=>$newcolor){
                                            foreach($newcolor->sizes as $key=>$newsizes){

                                                if(empty($newsizes->secondSize)){
                                                    $mysize = $newsizes->size;
                                                }
                                                else {
                                                    $mysize = $newsizes->size." - ".$newsizes->secondSize;
                                                }

                                                //$string = "Th*()is 999 is <<>> a ~!@# sample st#$%ring.";
                                                $color = strtolower(preg_replace("/[^a-zA-Z]/", "", $color));
                                                $newcolor->color = strtolower(preg_replace("/[^a-zA-Z]/", "", $newcolor->color));
                                                $mysize = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $mysize));
                                                $size = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $size));

                                                if($mysize == $size && $color == $newcolor->color){
                                                    $stockquantity = $newsizes->availableStock;
                                                    if($stockquantity > 0){
                                                        $i++;
                                                        $stockstatus = "instock";
                                                    }
                                                    else{
                                                        $stockstatus = "outofstock";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    update_post_meta($variation->ID, '_stock', $stockquantity);
                                    update_post_meta($variation->ID, '_stock_status', $stockstatus);

                                    update_post_meta($variation->ID,'_woocs_regular_price_NOK',$nok);
                                    update_post_meta($variation->ID,'_woocs_sale_price_NOK',$nok);
                                    update_post_meta($variation->ID,'_woocs_regular_price_CHF',$chf);
                                    update_post_meta($variation->ID,'_woocs_sale_price_CHF',$chf);
                                    update_post_meta($variation->ID,'_woocs_regular_price_DKK',$dkk);
                                    update_post_meta($variation->ID,'_woocs_sale_price_DKK',$dkk);
                                    update_post_meta($variation->ID,'_woocs_regular_price_SEK',$sek);
                                    update_post_meta($variation->ID,'_woocs_sale_price_SEK',$sek);

                                }
                            }
                        }

                        if($createby == "bycolors"){
                            $i = 0;
                            try {
                                $endpoint = 'products/'.$itemid->wp_item_id;
                                $product = $woocommerce->get($endpoint);
                                foreach($product->attributes as $key=>$attr){
                                    if($attr->name == "color"){
                                        $itemcolor = $attr->options[0];
                                    }
                                }
                            } catch (HttpClientException $e) {
                                echo $e->getMessage(); // Error message.
                                echo $e->getRequest(); // Last request data.
                                echo $e->getResponse(); // Last response data.
                                die;
                            }

                            $query = new \WP_Query( array(
                                'post_parent' => $itemid->wp_item_id,
                                'post_status' => 'publish',
                                'post_type' => 'product_variation',
                                'posts_per_page' => -1,
                            ) );

                            $allvariations = array();
                            if($query->have_posts()){
                                while ($query->have_posts()) {
                                    $query->next_post();
                                    $allvariations[] = $query->post;
                                }
                                wp_reset_postdata();
                            }
                            wp_reset_query();

                            if(!empty($allvariations)){
                                foreach($allvariations as $key=>$variation){
                                    $myvariation = wc_get_product($variation->ID);
                                    $myvariation = $myvariation->get_data();
                                    $size = $myvariation['attributes']['pa_size'];

                                    foreach($stock->warehouses as $key=>$warehouse){
                                        foreach ($warehouse->colors as $key=>$newcolor){
                                            foreach($newcolor->sizes as $key=>$newsizes){

                                                if(empty($newsizes->secondSize)){
                                                    $mysize = $newsizes->size;
                                                }
                                                else {
                                                    $mysize = $newsizes->size." - ".$newsizes->secondSize;
                                                }

                                                $itemcolor = strtolower(preg_replace("/[^a-zA-Z]/", "", $itemcolor));
                                                $newcolor->color = strtolower(preg_replace("/[^a-zA-Z]/", "", $newcolor->color));
                                                $mysize = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $mysize));
                                                $size = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $size));


                                                if($mysize == $size && $itemcolor == $newcolor->color){
                                                    $stockquantity = $newsizes->availableStock;
                                                    if($stockquantity > 0){
                                                        $i++;
                                                        $stockstatus = "instock";
                                                    }
                                                    else{
                                                        $stockstatus = "outofstock";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if($variation->ID == 105993){
                                      echo $stockquantity;die;
                                    }
                                    update_post_meta($variation->ID, '_stock', $stockquantity);
                                    update_post_meta($variation->ID, '_stock_status', $stockstatus);

                                    update_post_meta($variation->ID,'_woocs_regular_price_NOK',$nok);
                                    update_post_meta($variation->ID,'_woocs_sale_price_NOK',$nok);
                                    update_post_meta($variation->ID,'_woocs_regular_price_CHF',$chf);
                                    update_post_meta($variation->ID,'_woocs_sale_price_CHF',$chf);
                                    update_post_meta($variation->ID,'_woocs_regular_price_DKK',$dkk);
                                    update_post_meta($variation->ID,'_woocs_sale_price_DKK',$dkk);
                                    update_post_meta($variation->ID,'_woocs_regular_price_SEK',$sek);
                                    update_post_meta($variation->ID,'_woocs_sale_price_SEK',$sek);
                                }
                            }
                        }
                    }
                }
                if($i > 0){
                    update_post_meta($itemid->wp_item_id , '_stock_status','instock');
                }
                else{
                    update_post_meta($itemid->wp_item_id , '_stock_status','outofstock');
                }
            }
        }
    }
    $urlrequested = $GLOBALS['apiStart']."/api/v2/stock/?token=".$GLOBALS['token'];
    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);
    $date = Date("Y-m-d H:i:s");
    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`created_at`) values ('update_product_stocks','$minutes','$urlrequested','$date') ";
    $wpdb->query($query);
}


/*
 * This action is for setting the preference for creating items.
 */
add_action('wp_ajax_set_preference','set_pref');
function set_pref(){
    global $wpdb;
    if(isset($_POST['pref'])){
        $pref = sanitize_text_field($_POST['pref']);
        $date = date("Y-m-d H:i:s");
        $getquery = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module= 'item' and setting_module = 'createitemby'";
        $result = $wpdb->get_results($getquery);
        if(empty($result)){
            $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('item','createitemby','$pref','$date','$date')";
        }
        else{
            $id = $result[0]->id;
            $date = date("Y-m-d H:i:s");
            $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$pref' , modified_at = '$date' where id = ".$id;
        }

        $wpdb->query($query);
    }
}


/*
*data table function for the order display server side data tabales
*/
add_action( 'wp_ajax_order_datatable', 'order_datatable');
function order_datatable(){
    global $wpdb;

    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2',
        ]
    );


    $request= $_GET;

    $columns = array(
        0 => 'id',
        1 => 'wp_order_id',
        2 => 'erp_order_id',
        3 => 'status',
    );

    $query = "SELECT * FROM {$wpdb->base_prefix}erp_order_mapping where 1=1";

    if(($request['search']['value']) !='' ) { // When datatables search is used
        $query.=" AND";
        $query.=" ( wp_order_id LIKE '%" . $request['search']['value'] . "%' ";
        $query.=" OR erp_order_id LIKE '%" . $request['search']['value'] . "%' ";
        $query.=" OR status LIKE '%" . $request['search']['value'] . "%' )";
    }

    $query.=" ORDER BY " . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," .
        $request['length'] . "  ";


    $x = $wpdb->get_results($query);
    $totalrecords = "SELECT * FROM {$wpdb->base_prefix}erp_order_mapping";
    $res = $wpdb->get_results($totalrecords);
    $totalData = count($res);
    $data = [];
    $gif = BASE_URL."ajax-loader.gif";
    foreach ($x as $key => $value) {
        $nestedData = array();
        $nestedData[] = $value->id;
        $nestedData[] = $value->wp_order_id;
        $nestedData[] = $value->erp_order_id;
        $nestedData[] = $value->status;
        $nestedData[] = $value->erp_order_id;
        $nestedData[] = $gif;
        $nestedData[] = admin_url( $path= "post.php?post=".$value->wp_order_id."&action=edit", $scheme = "");

        $data[] = $nestedData;
    }
    wp_reset_query();

    $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalData),
        "data" => $data
    );

    echo json_encode($json_data);
    wp_die();
}

/*
* add the woocommere consumer key and secrect keys in wp_erp_setting table.
*/
add_action( 'wp_ajax_wc_keys_save', 'wc_keys_save');
function wc_keys_save(){
    global $wpdb;
    if(isset($_POST['wc_key'])){
        $wc_key = sanitize_text_field($_POST['wc_key']);
    }
    if(isset($_POST['wc_secret_key'])){
        $wc_sec_key = sanitize_text_field($_POST['wc_secret_key']);
    }
    if(isset($_POST['api_url'])){
        $urlstart = sanitize_text_field($_POST['api_url']);
    }
    if(isset($_POST['token'])){
        $token = sanitize_text_field($_POST['token']);
    }
    if($wc_key && $wc_sec_key && $urlstart && $token){
        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumerkey'";
        $wc_auth = $wpdb->get_results($query);
        $date = date("Y-m-d H:i:s");
        if(empty($wc_auth)){
            $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('wc_consumerkey','consumerkey','$wc_key','$date','$date')";
            $wpdb->query($query);
        }else{
            $id = $wc_auth[0]->id;
            $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$wc_key' , modified_at = '$date' where id = ".$id;
            $wpdb->query($query);
        }

        //for the wc consumersecret

        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'wc_consumersecret'";
        $wc_auth = $wpdb->get_results($query);
        $date = date("Y-m-d H:i:s");
        if(empty($wc_auth)){
            $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('wc_consumersecret','consumersecret','$wc_sec_key','$date','$date')";
            $wpdb->query($query);
        }else{
            $id = $wc_auth[0]->id;
            $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$wc_sec_key' , modified_at = '$date' where id = ".$id;
            $wpdb->query($query);
        }

        //for the apiUrl

        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_url'";
        $wc_auth = $wpdb->get_results($query);
        $date = date("Y-m-d H:i:s");
        if(empty($wc_auth)){
            $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('api_url','api_url','$urlstart','$date','$date')";
            $wpdb->query($query);
        }else{
            $id = $wc_auth[0]->id;
            $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$urlstart' , modified_at = '$date' where id = ".$id;
            $wpdb->query($query);
        }

        //for the api Token

        $query = "SELECT * FROM {$wpdb->base_prefix}erp_settings where module = 'api_token'";
        $wc_auth = $wpdb->get_results($query);
        $date = date("Y-m-d H:i:s");
        if(empty($wc_auth)){
            $query = "INSERT INTO {$wpdb->base_prefix}erp_settings (`module`,`setting_module`,`setting_value`,`created_at`,`modified_at`) values ('api_token','api_token','$token','$date','$date')";
            $wpdb->query($query);
        }else{
            $id = $wc_auth[0]->id;
            $query = "UPDATE {$wpdb->base_prefix}erp_settings set setting_value = '$token' , modified_at = '$date' where id = ".$id;
            $wpdb->query($query);
        }

    }
}


/*
*for the display of order Module details of synced
*/
add_action( 'wp_ajax_order_details', 'order_details');
function order_details(){
    if(!empty($_POST['orderId'])){
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->base_prefix}erp_order_mapping where wp_order_id = ".intval($_POST['orderId']);
        $order = $wpdb->get_results($query);
        if(!empty($order)){
            wp_reset_query();
            // print_r($order);die;
            $html = '
          <div class="row view-row widefat">
            <div class="col-md-3"><b>Id :-</b></div>
            <div class="col-md-7">'.$order[0]->id.'</div>
          </div>
          <br>
          <div class="row view-row widefat">
            <div class="col-md-3"><b>Order Id :-</b></div>
            <div class="col-md-7">'.$order[0]->wp_order_id.'</div>
          </div>
          <br>
          <div class="row view-row widefat">
            <div class="col-md-3"><b>Erp Id :-</b></div>
            <div class="col-md-7">'.$order[0]->erp_order_id.'</div>
          </div>
          <br>
          <div class="row view-row widefat">
            <div class="col-md-3"><b>Sync Date :-</b></div>
            <div class="col-md-8">'.$order[0]->sync_date.'</div>
          </div>
          <br>
          <div class="row view-row widefat">
            <div class="col-md-3"><b>Request :-</b></div>
            <div class="col-md-8">'.$order[0]->request.'</div>
          </div>
          <br>
          <div class="row view-row widefat">
            <div class="col-md-3"><b>Response :-</b></div>
            <div class="col-md-8">'.$order[0]->response.'</div>
          </div>
          <br>
          <div class="row view-row widefat">
            <div class="col-md-3"><b>Created Date :-</b></div>
            <div class="col-md-7">'.$order[0]->created_at.'</div>
          </div>
          <br>
          ';
            echo $html;
        }else{
            $html = '
          <table class="wp-list-table widefat fixed striped table-striped" style="border:unset;">
          <tr>
          <td>
          No data Available
          </td>
          </tr>
          </table>';
            echo $html;

        }
        wp_die();
    }else{
        $html = '
      <table class="wp-list-table widefat fixed striped table-striped" style="border:unset;">
      <tr>
      <td>
      No data Available
      </td>
      </tr>
      </table>';
        echo $html;
    }
}



/*
 * This function updates tracking info for orders.
 */
add_action('wp_ajax_sw_updatetrackingstatus','updatetrackingstatus');
function updatetrackingstatus(){
    $datetime1 = strtotime(Date("Y-m-d H:i:s"));
    global $wpdb;
    $sql = "SELECT erp_order_id FROM {$wpdb->base_prefix}erp_order_mapping";
    $result = $wpdb->get_results($sql);

    if(empty($result)){
        echo "No order synced yet !";
    }
    else{
        //all picks

        $type = "GET";
        $url = $GLOBALS['apiStart']."/api/v2/picks/?token=".$GLOBALS['token'];
        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
        $response_curl = dynamic_curl_call_request($type,$url,$data);

        if ($response_curl['status'] != 200) {
            echo "cURL Error #:" . $response_curl['body'];
        } else {
            $response = $response_curl['body'];
            $allpicks =  json_decode($response_curl['body']);
            foreach($allpicks->picks as $key=>$pick){
                if($pick->shippingNumber != "" || $pick->shippingNumber != null){
                    $orderid = $pick->orderIds;
                    $query = "SELECT wp_order_id FROM {$wpdb->base_prefix}erp_order_mapping where erp_order_id = "."'$orderid'";
                    $newresult = $wpdb->get_results($query);
                    if(empty($newresult)){
                        //this order is not synced.
                    }
                    else{
                        $wp_order_id = $newresult[0]->wp_order_id;
                        $trackingnumber = $pick->shippingNumber;
                        $deliverydate = $pick->deliveryDate;
                        update_post_meta($wp_order_id,'ywot_tracking_code',"$trackingnumber");
                        update_post_meta($wp_order_id,'ywot_pick_up_date',"$deliverydate");
                    }
                }
            }
        }
    }

    $urlrequested = $GLOBALS['apiStart']."/api/v2/picks/?token=".$GLOBALS['token'];
    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);
    $date = Date("Y-m-d H:i:s");
    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`error_code`,`created_at`) values ('update_product_stocks','$minutes','$urlrequested','$response','$date') ";
    $wpdb->query($query);
}







/*
 * function that creates variation for particular product
 */
function updateproductvariation($erpitem,$createdby,$productid){
    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    set_time_limit(0);
    global $wpdb;
    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2',
        ]
    );
    //deleting all variations

    $query = "UPDATE {$wpdb->base_prefix}posts SET post_status = 'trash' WHERE post_type = 'product_variation' AND post_parent = '$productid'";

    $wpdb->query($query);

    if($createdby == "byitems"){
        $totalarray = array();
        $vendpoint = "products/$productid/variations/batch";

        $getproduct = "products/$productid";
        $product = $woocommerce->get($getproduct);


        foreach($product->attributes as $key=>$attribute){
            if($attribute->name == "color"){
                $colors = array();
                $attrid = $attribute->id;
                foreach($attribute->options as $key=>$color){
                    $temp = array();
                    $temp['id'] = $attrid;
                    $temp['option'] = $color;
                    $colors[]  = $temp;
                }
            }

            if($attribute->name == "size"){
                $sizes = array();
                $attrid = $attribute->id;
                foreach($attribute->options as $key=>$size){
                    $temp = array();
                    $temp['id'] = $attrid;
                    $temp['option'] = $size;
                    $sizes[] = $temp;
                }
            }

        }

        //getting stock for item.

        $type = "GET";
        $url = $GLOBALS['apiStart']."/api/v2/items/$erpitem->id/stock?token=".$GLOBALS['token'];
        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
        $response_curl = dynamic_curl_call_request($type,$url,$data);

        if ($response_curl['status'] != 200) {
            echo "cURL Error #:" . $response_curl['body'];
        } else {
            $response = $response_curl['body'];
            $data = json_decode($response_curl['body']);
            $stock = $data->stock[0]->warehouses[0]->colors;
        }

        foreach($erpitem->barcodes as $key=>$barcodes){
            foreach($colors as $key=>$color){
                $newtemp = array();

                $newtemp['image'] = array();
                foreach($erpitem->colors as $key=>$erpcolor){
                    if(strtolower($erpcolor->color) == strtolower($color['option'])){
                        $sql = "SELECT * FROM {$wpdb->base_prefix}erp_color_image_mapping where erp_product_id='$erpitem->id' and colorname = '$erpcolor->color' ";
                        $myresult = $wpdb->get_results($sql);
                        if(!empty($myresult)){
                            foreach($myresult as $Key=>$imagedata){
                                if(!empty($imagedata->imageid)){
                                    $newtemp['image']['id']= $imagedata->imageid;
                                }
                            }
                        }
                    }
                }

                foreach($sizes as $key=>$size){
                    if(strtolower($barcodes->color->color) == strtolower( $color['option'])){

                        if(empty($barcodes->size->secondSize)){
                            $barcodesize = $barcodes->size->size;
                        }
                        else {
                            $barcodesize = $barcodes->size->size." - ".$barcodes->size->secondSize;
                        }


                        if($barcodesize == $size['option']){
                            $eanbarcode = $barcodes->barcode;


                            foreach($stock as $key=>$value){
                                if(strtolower($value->color) == strtolower($color['option'])){
                                    foreach($value->sizes as $key=>$erpsize){

                                        if(empty($erpsize->secondSize)){
                                            $stocksize = $erpsize->size;
                                        }else{
                                            $stocksize = $erpsize->size." - ".$erpsize->secondSize;
                                        }

                                        if($stocksize == $size['option']){
                                            if($erpsize->availableStock > 0){
                                                $stockquantity = $erpsize->availableStock;
                                            }
                                            else{
                                                $stockquantity = 0;
                                            }
                                        }

                                    }
                                }
                            }

                            $temp = array();
                            $temp['key'] = "hwp_var_gtin";
                            $temp['value'] = $eanbarcode;
                            $newtemp['meta_data'][] = $temp;

                            $newtemp['regular_price'] = $erpitem->salesListPrice;
                            $newtemp['manage_stock'] = true;

                            $newtemp['stock_quantity'] = $stockquantity;
                            if($stockquantity > 0){
                                $newtemp['stock_status'] = "instock";
                            }
                            $newtemp['attributes'][] = $color;
                            $newtemp['attributes'][] = $size;
                            $totalarray['create'][] = $newtemp;
                        }
                    }
                }
            }
        }

        try{
            $woocommerce->post($vendpoint,$totalarray);
            $totalarray = array();
        }
        catch (HttpClientException $e) {
            echo $e->getMessage(); // Error message.
            echo $e->getRequest(); // Last request data.
            echo $e->getResponse(); // Last response data.
            die;
        }


    }


    if($createdby == "bycolors"){

        $totalarray = array();
        $vendpoint = "products/$productid/variations/batch";

        $getproduct = "products/$productid";
        $product = $woocommerce->get($getproduct);



        foreach($product->attributes as $key=>$attribute){

            if($attribute->name == "color"){
                foreach($attribute->options as $key=>$color){
                    $mycolor = $color;
                }
            }

            if($attribute->name == "size"){
                $sizes = array();
                $attrid = $attribute->id;
                foreach($attribute->options as $key=>$size){
                    $temp = array();
                    $temp['id'] = $attrid;
                    $temp['option'] = $size;
                    $sizes[] = $temp;
                }
            }

        }

        //getting stock for item

        $type = "GET";
        $url = $GLOBALS['apiStart']."/api/v2/items/$erpitem->id/stock?token=".$GLOBALS['token'];
        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
        $response_curl = dynamic_curl_call_request($type,$url,$data);

        if ($response_curl['status'] != 200) {
            echo "cURL Error #:" . $response_curl['body'];
        } else {
            $response = $response_curl['body'];
            $data = json_decode($response_curl['body']);
            $stock = $data->stock[0]->warehouses[0]->colors;
        }


        foreach($erpitem->barcodes as $key=>$barcodes){
            if(strtolower($mycolor) == strtolower($barcodes->color->color)){
                $mysize = array();
                $mysize['id'] = $attrid;

                if(empty($barcodes->size->secondSize)){
                    $barcodesize = $barcodes->size->size;
                }
                else {
                    $barcodesize = $barcodes->size->size." - ".$barcodes->size->secondSize;
                }

                $mysize['option'] = $barcodesize;

                foreach($sizes as $key=>$size){
                    foreach($stock as $key=>$value){
                        if(strtolower($value->color) == strtolower($mycolor)){
                            foreach($value->sizes as $key=>$erpsize){
                                if(empty($erpsize->secondSize)){
                                    $mainsize = $erpsize->size;
                                }
                                else{
                                    $mainsize = $erpsize->size." - ".$erpsize->secondSize;
                                }
                                if($mainsize == $mysize['option']){
                                    if($erpsize->availableStock > 0){
                                        $stockquantity = $erpsize->availableStock;
                                    }
                                    else{
                                        $stockquantity = 0;
                                    }
                                }
                            }
                        }
                    }
                }

                $eanbarcode = $barcodes->barcode;

                $newtemp = array();

                $temp = array();
                $temp['key'] = "hwp_var_gtin";
                $temp['value'] = $eanbarcode;
                $newtemp['meta_data'][] = $temp;

                $newtemp['regular_price'] = $erpitem->salesListPrice;
                $newtemp['manage_stock'] = true;
                $newtemp['stock_quantity'] = $stockquantity;
                if($stockquantity > 0){
                    $newtemp['stock_status'] = "instock";
                }
                $newtemp['attributes'][] = $mysize;
                $totalarray['create'][] = $newtemp;
            }
        }

        try{
            $x = $woocommerce->post($vendpoint,$totalarray);
            $totalarray = array();
        }
        catch (HttpClientException $e) {
            echo $e->getMessage(); // Error message.
            echo $e->getRequest(); // Last request data.
            echo $e->getResponse(); // Last response data.
            die;
        }
    }
}


/*
 * params is the query array passed to WP_Query
*/
add_action( 'rest_customer_query', 'customer_override_per_page' );
function customer_override_per_page( $params ) {
    $params[ 'per_page' ][ 'maximum' ] = 500;
    $params[ 'posts_per_page' ] = PHP_INT_MAX;
    if ( isset( $params ) AND isset( $params[ 'posts_per_page' ] ) ) {
        $params[ 'posts_per_page' ] = PHP_INT_MAX;
    }
    return $params;
}

/**
 * For getting multi currency prices
 */
add_action('wp_ajax_multi_currency','get_multi_currency');
function get_multi_currency(){
    ini_set('max_execution_time', 10101010101);
    ini_set('default_socket_timeout', 10101010101);
    set_time_limit(0);
    global $wpdb;
    $datetime1 = strtotime(Date("Y-m-d H:i:s"));
    $woocommerce = new Client(
        $GLOBALS['url'],
        $GLOBALS['consumerkey'],
        $GLOBALS['consumersecret'],
        [
            'wp_api' => true,
            'verify_ssl'=>false,
            'query_string_auth' => true,
            'timeout' => 0,
            'version' => 'wc/v2'
        ]
    );

    $query = "select * from {$wpdb->base_prefix}erp_item_mapping";
    $result = $wpdb->get_results($query);

    if(empty($result)){
        echo "Please sync some products first !";
        die;
    }
    else{
        /*GET MULTICURRENCY BY API*/
        $type = "GET";
        $url = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
        $data = array( 'timeout' => 900000000000, 'httpversion' => '1.1' );
        $response_curl = dynamic_curl_call_request($type,$url,$data);

        if ($response_curl['status'] != 200) {
            echo "cURL Error #:" . $response_curl['body'];
        } else {
            $response = $response_curl['body'];
            $data = json_decode($response_curl['body']);
            foreach($data->items as $Key=>$item){
                foreach($item->storeViews as $key=>$storeview){
                    $countryname = $storeview->reference;
                    $countryname = strtolower(preg_replace("/[^a-zA-Z]/", "", $countryname));
                    if($countryname == "norway"){
                        $nok = $storeview->item->salesListPrice;
                    }
                    if($countryname == "switzerland"){
                        $chf = $storeview->item->salesListPrice;
                    }
                    if($countryname == "denmark"){
                        $dkk = $storeview->item->salesListPrice;
                    }
                    if($countryname == "sweden"){
                        $sek = $storeview->item->salesListPrice;
                    }
                }

                $query = "select * from {$wpdb->base_prefix}erp_item_mapping where erp_item_id = ".$item->id;
                $result = $wpdb->get_results($query);

                foreach($result as $key=>$wpitems){
                    $query = new \WP_Query( array(
                        'post_parent' => $wpitems->wp_item_id,
                        'post_status' => 'publish',
                        'post_type' => 'product_variation',
                        'posts_per_page' => -1,
                    ) );

                    $allvariations = array();
                    if($query->have_posts()){
                        while ($query->have_posts()) {
                            $query->next_post();
                            $allvariations[] = $query->post;
                        }
                        wp_reset_postdata();
                    }
                    wp_reset_query();

                    foreach($allvariations as $key=>$variation){
                        $variationid = $variation->ID;
                        update_post_meta($variationid,'_woocs_regular_price_NOK',$nok);
                        update_post_meta($variationid,'_woocs_sale_price_NOK',$nok);
                        update_post_meta($variationid,'_woocs_regular_price_CHF',$chf);
                        update_post_meta($variationid,'_woocs_sale_price_CHF',$chf);
                        update_post_meta($variationid,'_woocs_regular_price_DKK',$dkk);
                        update_post_meta($variationid,'_woocs_sale_price_DKK',$dkk);
                        update_post_meta($variationid,'_woocs_regular_price_SEK',$sek);
                        update_post_meta($variationid,'_woocs_sale_price_SEK',$sek);
                    }
                }

            }
        }
    }


    $urlrequested = $GLOBALS['apiStart']."/api/v2/items/?token=".$GLOBALS['token'];
    $datetime2 = strtotime(Date("Y-m-d H:i:s"));
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);
    $date = Date("Y-m-d H:i:s");
    $query = "INSERT into {$wpdb->base_prefix}erp_sync_logs (`module`,`time_taken`,`url_requested`,`created_at`) values ('multicurrency','$minutes','$urlrequested','$date') ";
    $wpdb->query($query);
}



