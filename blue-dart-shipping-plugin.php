<?php

/**
 * Plugin Name: Blue Dart shipment for woocommerce
 * Description: Blue Dart shipment AWB Generation for woocommerce
 * Author: Bhavik Tanna
 * Author URI: https://github.com/bhavikwork
 * Version: 1.1.0
 * Copyright: © 2022 Bhavik Tanna. (email : bmtanna25@gmail.com)
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
/*

/**

 * Check if WooCommerce is active

 */


//echo  $logoUrl = home_url().'/wp-content/uploads/'.unserialize(get_option('Bluedart_information_logo')); 

//die("testing");


global $woocommerce, $product, $woocommerce_loop, $wpdb;;

global $post;



if (!function_exists('WC')) {

	function sp_bluedart_install_woocommerce_admin_notice()
	{

?>

		<div class="error">

			<p><?php _e('Bluedart  plugin is enabled but not effective. It requires WooCommerce in order to work.', 'swdpd'); ?></p>

		</div>

	<?php

	}



	add_action('admin_notices', 'sp_bluedart_install_woocommerce_admin_notice');

	return;
} else {



	function sp_bluedart_createTable()
	{

		global $wpdb;

		if ($wpdb->get_var("show tables like " . $wpdb->prefix . "orders_manifests") != '' . $wpdb->prefix . 'orders_manifests') {

			$sql = "CREATE TABLE " . $wpdb->prefix . "orders_manifests(

				id int(99) NOT NULL AUTO_INCREMENT,

				order_id bigint(255) NOT NULL,

				awb_no 	bigint(255)	 NOT NULL,

				customer_name 	varchar(255)	 NOT NULL,

				shipping_address varchar(255) NOT NULL,

				pin_code	varchar(255) NOT NULL,

				items	varchar(255) NOT NULL,

				weight 	varchar(240) NOT NULL,

				declared_value 	varchar(240) NOT NULL,

				collectable 	varchar(240) NOT NULL,

				mode 	varchar(240) NOT NULL,

				destination VARCHAR( 255 ) NOT NULL,

				created_at	datetime NOT NULL,

				UNIQUE KEY id (id)

				);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			dbDelta($sql);
		}
	}

	register_activation_hook(__FILE__, 'sp_bluedart_createTable');
}









add_action('admin_footer-edit.php', 'custom_bulk_admin_footer');



function custom_bulk_admin_footer()
{



	global $post_type;



	if ($post_type == 'shop_order') {

	?>

		<script type="text/javascript">
			jQuery(document).ready(function() {

				jQuery('<option>').val('awb_generation').text('<?php _e('Generate AWB No.') ?>').appendTo("select[name='action']");

				jQuery('<option>').val('shipping_labels_print').text('<?php _e('Generate shipping labels') ?>').appendTo("select[name='action']");

				jQuery('<option>').val('awb_generation').text('<?php _e('Generate AWB No.') ?>').appendTo("select[name='action2']");

				jQuery('<option>').val('shipping_labels_print').text('<?php _e('Generate shipping labels') ?>').appendTo("select[name='action2']");

			});
		</script>

		<form method="post" action="<?php echo home_url() . '/wp-content/plugins/woocommrce-Blue-dart-shipment-extension/shipping_labels_printing.php' ?>" id="sp_shipping_labels_printing_form">

			<input type="hidden" value="" name="order_ids" id="sp_shipping_labels_all">

			<input type="submit" value="submit" name="sp_shipping_labels_all_sub" id="sp_shipping_labels_all_sub">

		</form>

	<?php





	}
}



/***************************custom waybill column in orders section**************************************/



add_filter('manage_edit-shop_order_columns', 'sp_set_custom_column_order_columns');



function sp_set_custom_column_order_columns($columns)
{

	$nieuwearray = array();

	foreach ($columns as $key => $title) {

		if ($key == 'shipping_address') // in front of the Billing column

			$nieuwearray['awb_no']  = __('AWB / Waybill No.', 'woocommerce');

		$nieuwearray[$key] = $title;
	}

	return $nieuwearray;
}

add_action('manage_shop_order_posts_custom_column', 'sp_custom_shop_order_column', 10, 2);

function sp_custom_shop_order_column($column)
{

	global $post, $woocommerce, $the_order;

	switch ($column) {

		case 'awb_no':

			$awb_no = get_post_meta($the_order->id, 'awb_no', true);

			echo ($awb_no != "") ? $awb_no : 'not generated';

			break;
	}
}





//add_action( 'woocommerce_after_add_to_cart_button','cod_single_product_page',10, 4);

function cod_single_product_page()
{



	global $product;

	$single_product_price = $product->get_price();



	?>



	<div class="cod-view">

		<div style="clear: both;padding-bottom: 10px;padding-top: 13px;">Check COD Availability</div>

		<input type="text" placeholder="Enter your pincode" id="pincodevalue" value="" name="pincode" style="margin-bottom: 18px;"><br />

		<input type="hidden" id="single_product_price" value="<?php echo $single_product_price; ?>" name="price_single_product" style="margin-bottom: 18px;">

		<a href="javascript:void(0);" id="cod_check" style="text-decoration:none; background:#EEEEEE; padding:10px; border:1px solid #ccc;">Check</a>

		<div style="display:none;width: 20px;" id="loader">

			<img src="<?php echo plugin_dir_url(__FILE__); ?>/images/opc-ajax-loader.gif">

		</div>

		<div style="padding-top:3px;" id="pinresult"></div>

	</div>



<?php



}



/*

 * Adding Plugin Css

 * */

add_action('wp_enqueue_scripts', 'css_styles');

function css_styles()
{



	wp_register_style('bluedart_global_style', plugins_url('css/global.css', __FILE__));

	wp_enqueue_style('bluedart_global_style');
}





/**

 * for admin user

 */

add_action('wp_ajax_get_remote_content_admin_shipping_labels_printing', 'shipping_labels_printing_function');

function shipping_labels_printing_function()
{
	include_once(plugin_dir_path(__FILE__) . 'shipping_labels_printing.php');
}


add_action('wp_ajax_get_remote_content_admin', 'get_remote_content_admin');

function get_remote_content_admin()
{

	include_once(plugin_dir_path(__FILE__) . 'DebugSoapClient.php');

	$Bluedart_information_select_mode = unserialize(get_option('Bluedart_information_select_mode'));
	$Bluedart_information_licence_key = unserialize(get_option('Bluedart_information_licence_key'));
	$Bluedart_information_loginid = unserialize(get_option('Bluedart_information_loginid'));
	$Bluedart_information_email = unserialize(get_option('Bluedart_information_email'));
	$Bluedart_information_store_name = unserialize(get_option('Bluedart_information_store_name'));
	$Bluedart_information_phone = unserialize(get_option('Bluedart_information_phone'));
	$Bluedart_information_store_address = unserialize(get_option('Bluedart_information_store_address'));
	$Bluedart_information_pincode = unserialize(get_option('Bluedart_information_pincode'));
	$Bluedart_information_customercode = unserialize(get_option('Bluedart_information_customercode'));
	$Bluedart_information_vandercode = unserialize(get_option('Bluedart_information_vandercode'));
	$Bluedart_information_originarea = unserialize(get_option('Bluedart_information_originarea'));
	$Bluedart_information_tin_no = unserialize(get_option('Bluedart_information_tin_no'));
	$blueAddress = $Bluedart_information_store_address;
	$line_store_address = $blueAddress;

	$line_store_address_1 = substr($line_store_address, 0, 30);
	$line_store_address_2 = substr($line_store_address, 30, 30);
	$line_store_address_3 = substr($line_store_address, 60, 30);
	$line_store_address_4 = substr($line_store_address, 90, 30);
	$line_store_address_5 = substr($line_store_address, 120, 30);

	$order_ids = $_REQUEST['order_ids'];

	// print_r($item_id);
	//$dimension_breadth =$_REQUEST['breadth'];
	//$dimension_height = $_REQUEST['height'];
	//$dimension_length = $_REQUEST['length'];
	//$weight= $_REQUEST['weight'];

	$dimension_breadth = 10;
	$dimension_height = 10;
	$dimension_length = 10;
	$weight = 0.5;

	foreach ($order_ids as $order_id) {

		$order = new WC_Order($order_id);
		$order_post = get_post($order_id);
		$datetime = $order_post->post_date;
		$date = explode(" ", $datetime);
		$order_date = $date[0];
		$payment_method_code = get_post_meta($order_id, '_payment_method', true); //cod
		$collectableAmount = 0;
		$SubProductCode = 'P';
		$pdf_method = "PREPAID ORDER";

		if ($payment_method_code == 'cod') {
			$collectableAmount = $order->get_total();
			$SubProductCode = 'C';
			$pdf_method = "CASH ON DELIVERY";

			$cod_tr = '<tr>
						<td colspan="4" align="center" valign="middle" style="width: 46%;"></td>
						<td colspan="3" align="center" valign="middle" style="width: 24%;">Cod Charges</td>
						<td align="center" valign="middle" style="width: 12%;">60</td>
					</tr>';
		}

		$mrp = 0;
		$commodityDetail = array();
		$i = 1;
		$qty = 0;
		$specialInstruction = '';
		$items_name = '';
		$ordered_items = $order->get_items();

		foreach ($ordered_items as $item) {

			$product_id = $item['product_id']; //product id     
			$product_name = $item['name']; //product id     
			$product_description = get_post($item['product_id'])->post_content;  //product description  
			$sku = get_post_meta($product_id, '_sku', true);
			$qty = $qty + $item['qty']; //ordered qty of item     
			$mrp = $mrp + $item['line_total'];


			$commodityDetail['CommodityDetail' . $i] =  preg_replace('/[^a-zA-Z0-9]/', ' ', $item['name']);
			$commodityDetail['CommodityDetail' . $i] =  preg_replace('/[^a-zA-Z0-9]/', ' ', substr($item['name'], 0, 30));

			$specialInstruction = $commodityDetail['CommodityDetail' . $i];

			//~ print_r($specialInstruction); echo '</br>' ; echo '++++++++';
			//~ print_r($commodityDetail['CommodityDetail'.$i]); echo '</br>' ; echo '*****************';

			$items_name = $commodityDetail['CommodityDetail' . $i] . ',' . $items_name;
			$i++;
		}

		if ($mrp1 == 0) {
			$mrp = 0.1;
		} else if ($mrp1 != 0) {
			$mrp = $mrp1;
		}

		if ($collectableAmount) {
			$mrp = $collectableAmount;
		}


		$dimension = $dimension_length . '*' . $dimension_breadth . '*' . $dimension_height;
		$customer_name = $order->shipping_first_name . ' ' . $order->shipping_last_name;
		$shipping_address_3 = $order->shipping_city . ' ' . $order->shipping_state . ' ' . $order->shipping_country;
		/*-------- Start Blue Dart API---------*/



		if ($Bluedart_information_select_mode == 1)
			$ApiUrl = 'https://netconnect.bluedart.com/Ver1.10/Demo/ShippingAPI/WayBill/WayBillGeneration.svc';
		else
			$ApiUrl = "https://netconnect.bluedart.com/Ver1.10/ShippingAPI/WayBill/WayBillGeneration.svc";
			
			//'http://netconnect.bluedart.com/Ver1.8/ShippingAPI/WayBill/WayBillGeneration.svc';



		$commodityDetail = array();

		//ini_set('display_errors',1);

		//error_reporting(-1);

		$soap = new DebugSoapClient(
			$ApiUrl . '?wsdl',
			array(
				'trace'         => 1,
				'style'         => SOAP_DOCUMENT,
				'use'           => SOAP_LITERAL,
				'soap_version'  => SOAP_1_2
			)
		);


		$soap->__setLocation($ApiUrl);
		$soap->sendRequest = true;
		$soap->printRequest = false;
		$soap->formatXML = true;
		$actionHeader = new SoapHeader(
			'http://www.w3.org/2005/08/addressing',
			'Action',
			'http://tempuri.org/IWayBillGeneration/GenerateWayBill',
			true
		);

		$soap->__setSoapHeaders($actionHeader);



		$consignee = array(

			'ConsigneeAddress1'     => substr($order->shipping_address_1, 0, 30),

			'ConsigneeAddress2'     => substr($order->shipping_address_2, 0, 30),

			'ConsigneeAddress3'     => $shipping_address_3,

			'ConsigneeAttention'    => '',

			'ConsigneeMobile'       => $order->billing_phone,

			'ConsigneeName'         => $customer_name,

			'ConsigneePincode'      => $order->shipping_postcode,

			'ConsigneeTelephone'    => $order->billing_phone,

		);



		$services = array(

			'ActualWeight'          => $weight,

			'CollectableAmount'     => $collectableAmount,

			'Commodity'             => $commodityDetail,

			'CreditReferenceNo'     => $order_id . '-EC',    //imp

			'ItemID'     => $product_id,    //productid

			'OrderItemID'     => $order_id,    //orderid

			'Itemname'     => $product_name,    //name

			'ProductDesc'     => $product_description, // product_des

			'DeclaredValue'         => $mrp,

			'ItemValue'         => $mrp,

			'Dimensions'            => array(

				'Dimension' => array(

					'Breadth' => $dimension_breadth,

					'Count' => '1',

					'Height' => $dimension_height,

					'Length' => $dimension_length

				),

			),

			'InvoiceNo'             => '',

			'ReturnReason'          => '',

			'PackType'              => '',

			'PickupDate'            => date('Y-m-d'),

			'PickupTime'            => '1800', //(optional)

			'PieceCount'            => '1', //(#default)

			'ProductCode'           => 'A', //(#default)

			'ProductType'           => 'Dutiables', //(#default)

			'SpecialInstruction'    => mb_strimwidth($specialInstruction, 0, 49, "..."),

			'Instruction'           => mb_strimwidth($specialInstruction, 0, 49, "..."),

			'SubProductCode'        => $SubProductCode,

		);

		//  echo "<pre>"; print_r($services);die;

		$shipper = array(

			'CustomerAddress1'  => @$line_store_address_1,

			'CustomerAddress2'  => @$line_store_address_2,

			'CustomerAddress3'  => @$line_store_address_3,

			'CustomerAddress4'  => @$line_store_address_4,

			'CustomerAddress5'  => @$line_store_address_5,

			'CustomerCode'      => $Bluedart_information_customercode,

			'CustomerEmailID'   => $Bluedart_information_email,

			'CustomerMobile'    => $Bluedart_information_phone,

			'CustomerName'      => $Bluedart_information_store_name,

			'CustomerPincode'   => $Bluedart_information_pincode,

			'CustomerTelephone' => $Bluedart_information_phone,

			'IsToPayCustomer'   => '',

			'OriginArea'        => $Bluedart_information_originarea,

			'Sender'            => '',

			'VendorCode'        => $Bluedart_information_vandercode,

			'GSTNo'        => $Bluedart_information_tin_no

		);



		$subshipper = array(

			'CustomerAddress1'  => @$line_store_address_1,

			'CustomerAddress2'  => @$line_store_address_2,

			'CustomerAddress3'  => @$line_store_address_3,

			'CustomerAddress4'  => @$line_store_address_4,

			'CustomerAddress5'  => @$line_store_address_5,

			'CustomerCode' => $Bluedart_information_customercode,

			'CustomerEmailID' => $Bluedart_information_email,

			'CustomerMobile' => $Bluedart_information_phone,

			'CustomerName' => $Bluedart_information_store_name,

			'CustomerPincode' => $Bluedart_information_pincode,

			'CustomerTelephone' => $Bluedart_information_phone,

			'IsToPayCustomer' => '',

			'OriginArea' => $Bluedart_information_originarea,

			'Sender' => '',

			'VendorCode' => $Bluedart_information_vandercode

		);



		$params = array(

			'Request' => array(

				'Consignee' => $consignee,

				'Services'  => $services,

				'Shipper' => $shipper,

				'SubShipper' => $subshipper

			),

			'Profile' => array(

				'Api_type' => 'S',

				'LicenceKey' => $Bluedart_information_licence_key,

				'LoginID' => $Bluedart_information_loginid,

				'Version' => '1.3'

			)

		);

		// echo"<pre>";print_r($params);die;

		$result = $soap->__soapCall('GenerateWayBill', array($params));

		// echo"<pre>";print_r($result);die;

		$data = $result->GenerateWayBillResult->AWBPrintContent;



		$error = $result->GenerateWayBillResult->IsError;





		if (!empty($error)) {

			$check_err = $result->GenerateWayBillResult->Status->WayBillGenerationStatus;



			if (is_array($check_err)) {



				$k = 1;

				$error_msg = '';

				foreach ($check_err as $err) {

					if ($k == 1)

						$error_msg = $k . ')-' . $err->StatusInformation;

					else

						$error_msg = $error_msg . '. ' . $k . ')-' . $err->StatusInformation;



					$k++;
				}
			} else {



				$error_msg = $result->GenerateWayBillResult->Status->WayBillGenerationStatus->StatusInformation;
			}



			echo 'For order #' . $order_id . ' errors:-' . $error_msg . ' ';
		} else {



			$AWB_No = $result->GenerateWayBillResult->AWBNo;

			$des_area = $result->GenerateWayBillResult->DestinationArea;

			$des_loc = $result->GenerateWayBillResult->DestinationLocation;



			update_post_meta($order_id, 'awb_no', $AWB_No);

			global $wpdb;

			$count_result = $wpdb->get_results(

				"SELECT *

										FROM `" . $wpdb->prefix . "orders_manifests`

										WHERE `order_id` =" . $order_id . "

										ORDER BY `order_id` DESC Limit 1"

			);



			if (count($count_result) == 0) {

				$wpdb->insert(

					'' . $wpdb->prefix . 'orders_manifests',

					array(

						'order_id' => $order_id,

						'awb_no' => $AWB_No,

						'customer_name' => $customer_name,

						'shipping_address' => $shipping_address_3,

						'pin_code' => $order->shipping_postcode,

						'items' => rtrim($items_name, ','),

						'weight' => $weight,

						'declared_value' => $mrp,

						'collectable' => $collectableAmount,

						'mode' => $pdf_method,

						'destination' => $des_area . '/' . $des_loc,

						'created_at' => date("Y-m-d H:i:s")

					),

					array(

						'%s',

						'%s',

						'%s',

						'%s',

						'%s',

						'%s',

						'%s',

						'%s',

						'%s',

						'%s',

						'%s',

						'%s'

					)

				);
			}







			//require_once( plugin_dir_path(__FILE__).'lib/MPDF57/mpdf.php' );

			require_once(plugin_dir_path(__FILE__) . 'lib/MPDF57/mpdf.php');

			$mpdf = new mPDF('c', 'A4', '', '', 9, 9, 9, 9, 9, 9);

			$mpdf->debug = true;

			$mpdf->SetDisplayMode('fullpage');

			$mpdf->showImageErrors = true;



			$mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list   



			$AWB_No = $result->GenerateWayBillResult->AWBNo;



			$field_awb_no = "order_" . $order_id . "_awbno";



			update_option($field_awb_no, $AWB_No);







			$order_date = $order_date;

			$address = '';



			$cust_street = $order->shipping_address_1;





			$cust_resion = $order->shipping_city;

			$cust_pin = $order->shipping_postcode;

			$cust_phone = 'cust_phone';

			$oneLine_address = $line_store_address;

			//~ foreach($line_store_address as $add)

			//~ {

			//~ $address .= '<p>'.$add.'</p>';

			//~ $oneLine_address .= $add;

			//~ }



			$html_2 = '';

			if ($payment_method_code == 'cod') {

				$html_2 = '<div class="ttl-amnt" style="border:1px solid #000;" >

										<h2>COD - AMOUNT TO BE COLLECTED <br> Rs. ' . $collectableAmount . '</h2>

									</div>';
			}



			$html_3 = '<table width="100%" cellspacing="0" cellpadding="8" >

											<tr>

												<td align="center" valign="middle" style="width: 6%;">Sr.</td>

												<td align="center" valign="middle" style="width: 10%;">Item ID</td>

												<td align="center" valign="middle" style="width: 30%;">Item Name</td>

												<td align="center" valign="middle" style="width: 30%;">Sku</td>

												<td align="center" valign="middle" style="width: 30%;">Instructions</td>

												<td align="center" valign="middle" style="width: 12%;">Quantity</td>

												<td align="center" valign="middle" style="width: 12%;">Value</td>

												<td align="center" valign="middle" style="width: 12%;">Total Amount</td>

											</tr>';



			$j = 1;

			$specialInstruction = '';

			$commodityDetail1 = array();

			foreach ($ordered_items as $item) {



				$product_id = $item['product_id'];

				$sku = get_post_meta($product_id, '_sku', true);

				$product_description = get_post($item['product_id'])->post_content;  //product description

				$item_reg_price = get_post_meta($product_id, '_regular_price', true);

				$item_sale_price = get_post_meta($product_id, '_sale_price', true);

				if ($item['qty'] > 0) {

					$item_price = (float)$item['line_subtotal'] / (float)$item['qty'];
				} else {
					$item_price = 0;
				}

				$commodityDetail1['CommodityDetail' . $j] =  preg_replace('/[^a-zA-Z0-9]/', ' ', $item['name']);

				$commodityDetail1['CommodityDetail' . $j] =  preg_replace('/[^a-zA-Z0-9]/', ' ', substr($item['name'], 0, 30));



				$specialInstruction = $commodityDetail1['CommodityDetail' . $j];



				//~ print_r($specialInstruction); echo '</br>'; echo '===';

				//~ print_r($commodityDetail1['CommodityDetail'.$j]); echo '</br>'; echo '&&&&&&&';

				//$final_price =  number_format((($item['qty']) * ($item_price)),2);

				$html_3 .= '<tr>

										<td align="center" valign="middle" style="width: 6%;">' . $j . '</td>

										<td align="center" valign="middle" style="width: 10%;">' . $product_id . '</td>

										<td align="center" valign="middle" style="width: 30%;">' . $item['name'] . '</td>

										<td align="center" valign="middle" style="width: 10%;">' . $sku/*mb_strimwidth($product_description,0, 49 ,"...")*/ . '</td>

										<td align="center" valign="middle" style="width: 30%;">' . $specialInstruction . '</td>

										<td align="center" valign="middle" style="width: 12%;">' . $item['qty'] . '</td>

										<td align="center" valign="middle" style="width: 12%;">' . $item_price . '</td>

										<td align="center" valign="middle" style="width: 12%;">' . $item['line_total'] . '</td>

									</tr>';



				$j++;
			}







			$order_total = get_post_meta($order_id, '_order_total', true);

			//	$order_seq_id = get_post_meta( $order_id, '_order_number', true );

			$grand_total = number_format($order_total, 2);

			$shipping_charges = number_format(get_post_meta($order_id, '_order_shipping', true), 2);

			$ship_charges = number_format($shipping_charges, 2);

			$order_shipping_tax = number_format(get_post_meta($order_id, '_order_shipping_tax', true), 2);

			$order_tax = number_format(get_post_meta($order_id, '_order_tax', true), 2);

			$tax_amt = number_format($order_tax, 2) + number_format($order_shipping_tax, 2);

			$order_discount = new WC_Order($order_id);

			$discount = number_format($order_discount->get_total_discount(), 2);

			$discount = !empty($discount) ? $discount : 0;



			$html_3 .= '<tr>

								<td colspan="4" align="center" valign="middle" style="width: 46%;"></td>

								<td colspan="3" align="center" valign="middle" style="width: 24%;">Shipping Charges</td>

								<td align="center" valign="middle" style="width: 12%;">' . $ship_charges . '</td>

								</tr>

								' . $cod_tr . '

								<tr>

								<td colspan="4" align="center" valign="middle" style="width: 46%;"></td>

								<td colspan="3" align="center" valign="middle" style="width: 24%;">Discount</td>

								<td align="center" valign="middle" style="width: 12%;">' . $discount . '</td>

								</tr>

								

								<tr>

									<td colspan="4" align="center" valign="middle" style="width: 46%;"></td>

									<td colspan="3" align="center" valign="middle" style="width: 24%;">Tax Charges</td>

									<td align="center" valign="middle" style="width: 12%;">' . $tax_amt . '</td>

								</tr>	

								

								<tr>

									<td colspan="4" align="center" valign="middle" style="width: 46%;"></td>

									<td colspan="3" align="center" valign="middle" style="width: 24%;">Total</td>

									<td align="center" valign="middle" style="width: 12%;">' . $grand_total . '</td>

								</tr>	

							</table>';



			$logoUrl = home_url() . '/wp-content/uploads/' . unserialize(get_option('Bluedart_information_logo'));

			/*			<img alt="logo" src="'.$logoUrl.'"  style="height:70px;width:250px;"/>*/

			$html = '<html>

					<head>

					<meta charset="utf-8">

					<meta http-equiv="X-UA-Compatible" content="IE=edge">

					<title></title>

					<meta name="description" content="">



					</head>

					<body>

						<div class="main-block" style="border:none;">

							<div class="sectn-top">

								<div class="log-main">

						

								</div>

								<div class="ship-adrs">

									<h2>' . $Bluedart_information_store_name . '</h2>

									<p>' . $Bluedart_information_store_address . '</p>

									<p>PIN : ' . $Bluedart_information_pincode . '</p>

									<p>Phone : ' . $Bluedart_information_phone . '</p>

									<p>Email : ' . $Bluedart_information_email . '</p>

									

								</div>

								<div class="inv-dtails">

									<p>INVOICE NO <span style="font-size:12px;">: ' . $order_id . '</span></p>

									<p>INVOICE DATE <span style="font-size:12px;">: ' . date("Y-m-d") . '</span></p>

									<p>GST NO <span style="font-size:12px;">: ' . $Bluedart_information_tin_no . '</span></p><br>

									<p><img alt="logo" src="https://lelys.in/wp-content/uploads/2022/08/blue-dart.jpg"  style="height:40px;width:250px;"/></p>	

								</div>

							</div>

						

							

							<div class="sectn-mid">

								<div class="ship-adrs border-no">

									<h2>DELIVER TO</h2>

									<p>' . $customer_name . '<br>' . $order->shipping_address_1 . ' ' . $order->shipping_address_2 . '</p>

									<p>' . $order->shipping_city . '</p>

									<h2>' . $order->shipping_postcode . ' - ' . $des_area . '/' . $des_loc . '</h2>

									<p>' . $order->shipping_state . '</p>

									<p>Phone ' . $order->billing_phone . '</p>

									

								</div>

								

								<div class="ordr-dtails">

									<div class="o-id">

										<h2>ORDER ID</h2>

										<div class="img-cntr">

										<barcode code=' . $order_id . ' type="C39" size="1.0" height="2.0" /></div>

										<p style="text-align:center;">' . $order_id . '</p>

									</div>

									<div class="o-id o-cod">

										<h2>' . $pdf_method . '</h2>

										<div class="img-cntr"><barcode code=' . $AWB_No . '  type="C39" size="1.0" height="2.0" /></div>

										<p style="width:100%; text-align:center;">' . $AWB_No . '</p>

									</div>

										

									

									<div class="p-details">

										<p>AWB No. <span>: ' . $AWB_No . '</span></p>

										<p>Weight (kgs) <span>: ' . number_format($weight, 2) . '</span></p>

										<p>Dimensions (cms) <span>: ' . $dimension . '</span></p>

										<p>Order ID <span>: ' . $order_id . '</span></p>

										<p>Order Date <span>: ' . $order_date . '</span></p>

										<p>Pieces <span>:1</span></p> 

									</div>

								</div>

								

								' . $html_2 . '

								

							</div>

							

							<div class="tble-btm">

								' . $html_3 . '

							</div>

							<p style="width: 100%; text-align:center; font-size: 12px; margin-bottom: 5px;">This is computer generated document, hence does not require signature.</p>

							

							

							<p style="width: 100%; text-align:center; font-size: 12px; margin-top: 10px;margin-bottom:20px;">Return Address :' . ' ' . $Bluedart_information_store_name . ', ' . $oneLine_address . ',' . $Bluedart_information_pincode . '</p>

							

							

						</div>

						

						



					</body>

					</html>';

			$cssPath = plugin_dir_path(__FILE__) . 'lib/css/pdf.css';



			$stylesheet = file_get_contents($cssPath);



			$mpdf->WriteHTML($stylesheet, 1);    // The parameter 1 tells that this is css/style only and no body/html/text



			$mpdf->WriteHTML($html, 2);



			$file_name = 'order_' . $order_id . '.pdf';

			$filename = plugin_dir_path(__FILE__) . 'pdf_invoice_bluedart/' . $file_name;

			$mpdf->Output($filename, 'F');

			echo "For order #'" . $order_id . "'Waybill number generated successfully.Waybill number is" . $result->GenerateWayBillResult->AWBNo;



			$pdf_link = plugin_dir_url(__FILE__) . 'pdf_invoice_bluedart/order_' . $order_id . '.pdf';

			update_post_meta($order_id, 'pdf_link', $pdf_link);

			/*$order = new WC_Order($order_id);



					if (!empty($order)) {

						$order->update_status( 'Dispatch' );

					}*/
		}
	}
}









add_action('wp_ajax_get_remote_content', 'get_remote_content');

add_action('wp_ajax_nopriv_get_remote_content', 'get_remote_content');

function get_remote_content()
{

	$postal_code = trim($_POST['pin']);

	include_once(plugin_dir_path(__FILE__) . 'DebugSoapClient.php');

	$Bluedart_information_licence_key = unserialize(get_option('Bluedart_information_licence_key'));
	$Bluedart_information_loginid = unserialize(get_option('Bluedart_information_loginid'));
	$Bluedart_information_select_mode = unserialize(get_option('Bluedart_information_select_mode'));


	if ($Bluedart_information_select_mode == 1)
		$ApiUrl = 'https://netconnect.bluedart.com/Ver1.10/Demo/ShippingAPI/Finder/ServiceFinderQuery.svc';
	else
		$ApiUrl = "https://netconnect.bluedart.com/Ver1.10/ShippingAPI/Finder/ServiceFinderQuery.svc"; 
		

		//'http://netconnect.bluedart.com/ver1.9/ShippingAPI/Finder/ServiceFinderQuery.svc';


	$soap = new DebugSoapClient(
		$ApiUrl . '?wsdl',
		array(

			'trace'         => 1,

			'style'         => SOAP_DOCUMENT,

			'use'           => SOAP_LITERAL,

			'soap_version'  => SOAP_1_2

		)

	);



	$soap->__setLocation($ApiUrl);

	$soap->sendRequest = true;

	$soap->printRequest = false;

	$soap->formatXML = true;

	$actionHeader = new SoapHeader(

		'http://www.w3.org/2005/08/addressing',
		'Action',

		'http://tempuri.org/IServiceFinderQuery/GetServicesforPincode',
		true
	);

	$soap->__setSoapHeaders($actionHeader);

	$params = array(

		'pinCode' => $postal_code,

		'profile' => array(

			'Api_type'      => 'S',

			'Area'          => '',

			'Customercode'  => '',

			'IsAdmin'       => '',

			'LicenceKey'    => $Bluedart_information_licence_key,

			'LoginID'       => $Bluedart_information_loginid,

			'Password'      => '',

			'Version'       => '1.3'

		)

	);

	$result = $soap->__soapCall('GetServicesforPincode', array($params));

	$response['is_error'] = $result->GetServicesforPincodeResult->ErrorMessage;

	$response['place'] = $result->GetServicesforPincodeResult->PincodeDescription;

	$response['cod_in'] = $result->GetServicesforPincodeResult->eTailCODAirInbound;

	$response['cod_out'] = $result->GetServicesforPincodeResult->eTailCODAirOutbound;

	$response['value_limit'] = $result->GetServicesforPincodeResult->AirValueLimit;



	//echo json_encode($response);

	exit;
}



add_action('admin_footer', 'css_js_files');

add_action('wp_footer', 'css_js_files');

function css_js_files()
{





	wp_register_style('bluedart_global_css', plugin_dir_url(__FILE__) . 'css/global.css');

	wp_enqueue_style('bluedart_global_css');



	wp_register_script('jquery.validate.min', plugin_dir_url(__FILE__) . 'js/jquery.validate.min.js');

	wp_register_script('bluedart_global_js', plugin_dir_url(__FILE__) . 'js/global.js');

	// setTimeout(executeMainFunction, 500); 

	$ajax_url = admin_url('admin-ajax.php');

	wp_localize_script('bluedart_global_js', 'ajax_url', array('url' => $ajax_url));

	wp_enqueue_script('jquery.validate.min');

	wp_enqueue_script('bluedart_global_js');
}

add_action('admin_footer', 'admin_js_css');

function admin_js_css()
{





	wp_register_script('bluedart_admin_global_js', plugin_dir_url(__FILE__) . 'js/admin_global.js');

	$ajax_url = admin_url('admin-ajax.php');

	wp_localize_script('bluedart_admin_global_js', 'ajax_url', array('url' => $ajax_url));

	wp_enqueue_script('bluedart_admin_global_js');

	wp_register_style('jquery-ui-css', plugin_dir_url(__FILE__) . 'css/jquery-ui.css');

	wp_enqueue_style('jquery-ui-css');

	wp_register_style('jquery.fancybox.css', plugin_dir_url(__FILE__) . 'css/jquery.fancybox.css');

	wp_enqueue_style('jquery.fancybox.css');

	wp_register_script('jquery-ui', plugin_dir_url(__FILE__) . 'js/jquery-ui.js');

	wp_register_script('jquery.fancybox', plugin_dir_url(__FILE__) . 'js/jquery.fancybox.js');

	wp_register_script('jquery.fancybox.pack', plugin_dir_url(__FILE__) . 'js/jquery.fancybox.pack.js');

	wp_enqueue_script('jquery-ui');

	wp_enqueue_script('jquery.fancybox');

	wp_enqueue_script('jquery.fancybox.pack');



	echo '<div id="load2" style="display:none"><img src="' . plugin_dir_url(__FILE__) . '/images/opc-ajax-loader.gif"/></div>';

?>





	<input type="hidden" name="length" class="pack_length" value="<?= unserialize(get_option('package_length')); ?>" />

	<input type="hidden" name="breadth" class="pack_breadth" value="<?= unserialize(get_option('package_breadth')); ?>" />

	<input type="hidden" name="height" class="pack_height" value="<?= unserialize(get_option('package_height')); ?>" />

	<input type="hidden" name="weight" class="pack_weight" value="<?= unserialize(get_option('package_weight')); ?>" />



	<a class="portfolio-item-link fancybox" id="fancy_popup" href="#div"><span></span></a>

	<div style="display:none;">

		<div id="div">

			<div id="bluedart_box">



				<span>



					<b>Packing Box Dimensions (In cm)</b></span><br />

				<span id="error" style="color:red"></span><br />

				<label>Length</label><input type="text" id="length" name="length">

				<label>breadth</label><input type="text" id="breadth" name="breadth">

				<label>height</label><input type="text" id="height" name="height">

				<label>weight (in kgs) </label><input type="text" id="weight" name="weight"><br /><br />

				<input class="button button-primary" type="button" id="shipment_button1" name="send_bluedart_shipment" value="OK" /><br />

				<span>(click on close button to use default package dimensions)</span>

				<div style="display:none;width: 20px;" id="loader">

					<img src="<?php echo plugin_dir_url(__FILE__); ?>/images/opc-ajax-loader.gif">

				</div>

			</div>

		</div>

	</div>



	<?php }



//////////////////////////////////////////////////////////////////////////////////Comment////////////////////////


/*


add_action( 'admin_footer', 'admin_js_css' );

function admin_js_css(){

	

	

	wp_register_script('jquery.js',plugin_dir_url(__FILE__).'js/jquery.js');

	wp_enqueue_script('jquery.js');

	

	wp_register_script('jquery.validate.min',plugin_dir_url(__FILE__).'js/jquery.validate.min.js');

	wp_enqueue_script('jquery.validate.min');

	

	wp_register_script('jquery-ui', plugin_dir_url(__FILE__).'js/jquery-ui.js');  

	 wp_enqueue_script('jquery-ui');

	wp_register_script('bluedart_admin_global_js', plugin_dir_url(__FILE__).'js/admin_global.js');  

	$ajax_url = admin_url('admin-ajax.php');

	wp_localize_script( 'bluedart_admin_global_js', 'ajax_url', array('url'=>$ajax_url ));

	wp_enqueue_script('bluedart_admin_global_js');

	wp_register_style('jquery-ui-css',plugin_dir_url(__FILE__).'css/jquery-ui.css'); 

	wp_enqueue_style('jquery-ui-css');

	wp_register_style('jquery.fancybox.css',plugin_dir_url(__FILE__).'css/jquery.fancybox.css'); 

	wp_enqueue_style('jquery.fancybox.css');

	

	wp_register_script('jquery.fancybox', plugin_dir_url(__FILE__).'js/jquery.fancybox.js');  

	wp_register_script('jquery.fancybox.pack', plugin_dir_url(__FILE__).'js/jquery.fancybox.pack.js');  

   

	wp_enqueue_script('jquery.fancybox');

	wp_enqueue_script('jquery.fancybox.pack');

	

	wp_register_script( 'jquery.dataTables.min', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.min.js' );

	wp_enqueue_script( 'jquery.dataTables.min' );

	

	wp_register_style('jquery.dataTables.min',plugin_dir_url(__FILE__).'css/jquery.dataTables.min.css'); 

	wp_enqueue_style('jquery.dataTables.min');

	

	

	echo '<div id="load2" style="display:none"><img src="'.plugin_dir_url(__FILE__).'/images/opc-ajax-loader.gif"/></div>';

?>

	



	<input type="hidden" name="length" class="pack_length" value="<?=unserialize(get_option('package_length')); ?>" />

	<input type="hidden" name="breadth" class="pack_breadth" value="<?=unserialize(get_option('package_breadth')); ?>" />

	<input type="hidden" name="height" class="pack_height" value="<?=unserialize(get_option('package_height')); ?>" />

	<input type="hidden" name="weight" class="pack_weight" value="<?=unserialize(get_option('package_weight')); ?>" />

	

	<a class="portfolio-item-link fancybox"  id="fancy_popup" href="#div"><span></span></a>

	<div style="display:none;">

		<div id="div">

		 <div id="bluedart_box">

			

			<span>

				

			<b>Packing Box Dimensions (In cm)</b></span><br/>

			<span id="error" style="color:red"></span><br/>

			<label>Length</label><input type="text" id="length" name="length">

			<label>breadth</label><input type="text" id="breadth" name="breadth">

			<label>height</label><input type="text" id="height" name="height">

			<label>weight (in kgs) </label><input type="text" id="weight" name="weight"><br/><br/>

			<input type="button" id="shipment_button1" name="send_bluedart_shipment" value="OK" /><br/>

			<span>(click on close button to use default package dimensions)</span>

			<div style="display:none;width: 20px;" id="loader">

			  <img src="<?php echo plugin_dir_url(__FILE__);?>/images/opc-ajax-loader.gif">

			</div>

		</div>

	</div>

	</div>



<?php }



add_action( 'admin_footer', 'css_js_files' );

add_action( 'wp_footer', 'css_js_files' );

function css_js_files(){

	

	

	wp_register_style('bluedart_global_css',plugin_dir_url(__FILE__).'css/global.css'); 

	wp_enqueue_style('bluedart_global_css');

	

	wp_register_script('jquery.validate.min',plugin_dir_url(__FILE__).'js/jquery.validate.min.js');

	wp_register_script('bluedart_global_js', plugin_dir_url(__FILE__).'js/global.js');  

  

	$ajax_url = admin_url('admin-ajax.php');

	wp_localize_script( 'bluedart_global_js', 'ajax_url', array('url'=>$ajax_url ));

   

	wp_enqueue_script('jquery.validate.min');

	wp_enqueue_script('bluedart_global_js');

   

}

*/







/*************************************************************************************************************/



add_action('woocommerce_admin_order_data_after_order_details', 'action_woocommerce_admin_order_actions_start');







function action_woocommerce_admin_order_actions_start($order)
{

	$version = '2.1';

	$order_id = defined('WC_VERSION') && version_compare(WC_VERSION, $version, '>=') ? $order->get_id() : $order->id;

	//$order_id=$order->ID;

	$field_awb_no = "order_" . $order_id . "_awbno";

	$awb_no = get_post_meta($order_id, 'awb_no', true);

	$pdf_link = get_post_meta($order_id, 'pdf_link', true);



	if (isset($awb_no) && $awb_no != "") {

		$pdf_link = plugin_dir_url(__FILE__) . 'pdf_invoice_bluedart/order_' . $order_id . '.pdf';

	?>

		<div id="bluedart_box" style="marign-top:30px;">

			<!--label><strong>AWB NO.</strong><?= $awb_no; ?></label-->

			<?php

			$homepage = file_get_contents('https://indian-courier-api.herokuapp.com/bluedart/' . $awb_no . '');

			$final_data = json_decode($homepage, true);

			$final_data2 =  $final_data['result'][0];

			//echo ($awb_no)? $final_data2['detail'].'</br>':'';

			?>

			<label><strong>Status</strong> <?php echo $final_data2['detail']; ?></label>

			<label><a target="_blank" href="http://bluedart.com/servlet/RoutingServlet?action=awbquery&awb=awb&handler=tnt&numbers=<?php echo $awb_no; ?>"><strong>AWB NO. </strong><?php echo $awb_no; ?></a></label>

			<a href="<?php echo $pdf_link; ?>" target="_blank" />

			<input class="button button-primary" type="button" id="" name="download_bluedart_shipment" value="DownLoad Bluedart Shipment pdf" style="margin-top: 27px;text-transform: capitalize;font-size: 16px;"></a>

		</div>
	
		<br />

	<?php

	} else {

	?>

		<br />

		<div id="bluedart_box">

			<span><b>Packing Box Dimensions (In cm)</b></span><br />

			<span id="error" style="color:red"></span><br />

			<label>Length</label><input type="text" id="length" value="11" name="length">

			<label>breadth</label><input type="text" id="breadth" value="10" name="breadth">

			<label>height</label><input type="text" id="height" value="10" name="height">

			<label>weight (in kgs) </label><input type="text" value="0.5" id="weight" name="weight">

			<input class="button button-primary" type="button" id="shipment_button" name="send_bluedart_shipment" value="Send Bluedart Shipment" style="text-transform: capitalize;font-size: 16px;"/>

			<div style="display:none;width: 20px;" id="loader">
				<img src="<?php echo plugin_dir_url(__FILE__); ?>/images/opc-ajax-loader.gif">
			</div>
		</div>
	<?php

	}

	?>

	<input type="hidden" id="order_id" name="order_id" value="<?php echo $order_id; ?>" data-action="awb_no" />
	<span id="awb_no"></span>

	<?php
}


/*
 * Adding Bluedart menu in admin section
 * */


class Bluedart_Options_Page
{

	function __construct()
	{
		add_action('admin_menu', array($this, 'Blue_Dart_Shipment_menu'));
	}

	function Blue_Dart_Shipment_menu()
	{

		add_menu_page(
			'Blue Dart Shipment Configuaraion page',
			'BlueDart Settings',
			'manage_options',

			'Blue-Dart-Shipment',
			array($this, 'Blue_Dart_Shipment_Settings_Page')
		);



		add_submenu_page(
			'Blue-Dart-Shipment',
			'Manifest',
			'Manifest',
			'manage_options',
			'Manifest',
			array($this, 'Manifest_page')

		);
	}



	/*

	 * Page shown in admin section When click on Bluedart menu start

	 */



	function Blue_Dart_Shipment_Settings_Page()
	{



	?>



		<div class="main-col-inner">

			<div id="messages"></div>



			<?php



			if (isset($_POST['Bluedart_information_licence_key'])) {



				$Bluedart_information_enable = update_option('Bluedart_information_enable', serialize($_POST['Bluedart_information_enable']));

				$Bluedart_information_select_mode = update_option('Bluedart_information_select_mode', serialize($_POST['Bluedart_information_select_mode']));

				$Bluedart_information_licence_key = update_option('Bluedart_information_licence_key', serialize($_POST['Bluedart_information_licence_key']));

				$Bluedart_information_loginid = update_option('Bluedart_information_loginid', serialize($_POST['Bluedart_information_loginid']));

				$Bluedart_information_email = update_option('Bluedart_information_email', serialize($_POST['Bluedart_information_email']));

				$Bluedart_information_store_name = update_option('Bluedart_information_store_name', serialize($_POST['Bluedart_information_store_name']));

				$Bluedart_information_phone = update_option('Bluedart_information_phone', serialize($_POST['Bluedart_information_phone']));

				$Bluedart_information_store_address = update_option('Bluedart_information_store_address', serialize($_POST['Bluedart_information_store_address']));

				$Bluedart_information_pincode = update_option('Bluedart_information_pincode', serialize($_POST['Bluedart_information_pincode']));

				$Bluedart_information_customercode = update_option('Bluedart_information_customercode', serialize($_POST['Bluedart_information_customercode']));

				$Bluedart_information_vandercode = update_option('Bluedart_information_vandercode', serialize($_POST['Bluedart_information_vandercode']));

				$Bluedart_information_originarea = update_option('Bluedart_information_originarea', serialize($_POST['Bluedart_information_originarea']));

				$Bluedart_information_tin_no = update_option('Bluedart_information_tin_no', serialize($_POST['Bluedart_information_tin_no']));

				$package_length = update_option('package_length', serialize($_POST['package_length']));

				$package_breadth = update_option('package_breadth', serialize($_POST['package_breadth']));

				$package_height = update_option('package_height', serialize($_POST['package_height']));

				$package_weight = update_option('package_weight', serialize($_POST['package_weight']));



				if ($_FILES["Bluedart_information_logo"]["name"] != "") {



					$upload_dir = wp_upload_dir();

					$target_dir = $upload_dir['basedir'];

					$target_file = $target_dir . '/' . basename($_FILES["Bluedart_information_logo"]["name"]);



					$uploadOk = 1;

					$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

					// Check if image file is a actual image or fake image



					$check = getimagesize($_FILES["Bluedart_information_logo"]["tmp_name"]);

					if ($check !== false) {



						$uploadOk = 1;
					} else {



						echo "File is not an image.";

						$uploadOk = 0;
					}



					// Check if file already exists

					if (file_exists($target_file)) {



						echo "Sorry, file already exists.";

						$uploadOk = 0;
					}



					// Check file size

					if ($_FILES["Bluedart_information_logo"]["size"] > 500000) {
						echo "Sorry, your file is too large.";
						$uploadOk = 0;
					}
					// Allow certain file formats
					if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
						echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
						$uploadOk = 0;
					}

					// Check if $uploadOk is set to 0 by an error
					if ($uploadOk == 0) {
						echo "Sorry, your file was not uploaded.";
						// if everything is ok, try to upload file

					} else {

						if (move_uploaded_file($_FILES["Bluedart_information_logo"]["tmp_name"], $target_file)) {
							//echo "The file ". basename( $_FILES["Bluedart_information_logo"]["name"]). " has been uploaded.";
						} else {
							echo "Sorry, there was an error uploading your file.";
						}
					}
					$Bluedart_information_logo = update_option('Bluedart_information_logo', serialize($_FILES["Bluedart_information_logo"]["name"]));
				}
			}
			?>

			<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script> -->

			<form method="post" action="options-general.php?page=Blue-Dart-Shipment" enctype="multipart/form-data" id="bluedart_shipping_form">

				<div id="messages1"></div>

				<div class="content-header">

					<table cellspacing="0">

						<tbody>

							<tr>

								<td style="width: 64%;text-align: center;">

									<h3>Bluedart Information</h3>

								</td>

								<td class="form-buttons"></td>

							</tr>

						</tbody>

					</table>

				</div>



				<table class="form-list" cellspacing="0">

					<colgroup class="label"></colgroup>

					<colgroup class="value"></colgroup>

					<colgroup class="scope-label"></colgroup>

					<colgroup class=""></colgroup>

					<tbody>

						<tr id="row_Bluedart_information_enable">

							<td class="label"><label for="Bluedart_information_enable"> Enable</label></td>

							<td class="value">

								<?php $enable = unserialize(get_option('Bluedart_information_enable')); ?>

								<select class=" select" name="Bluedart_information_enable" id="">



									<option value="1" <?php if ($enable == 1) echo "selected=" . "select"; ?>>Yes</option>

									<option value="0" <?php if ($enable == 0) echo "selected=" . "select"; ?>>No</option>



								</select>

							</td>

						</tr>

						<tr id="row_Bluedart_information_select_mode">
							<td class="label">
								<label for="Bluedart_information_select_mode"> Select Mode</label>
							</td>

							<td class="value">
								<?php $mode = unserialize(get_option('Bluedart_information_select_mode')); ?>
								<select class=" select" name="Bluedart_information_select_mode" id="Bluedart_information_select_mode">
									<option <?php if ($mode == 1) echo "selected=" . "select"; ?> value="1">Sandbox</option>
									<option <?php if ($mode == 2) echo "selected=" . "select"; ?> value="2">Live</option>
								</select>
							</td>
						</tr>

						<tr id="row_bluedart_Bluedart_information_licence_key">

							<td class="label"><label for="Bluedart_information_licence_key"> Licence Key</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_licence_key')); ?>" name="Bluedart_information_licence_key" id="Bluedart_information_licence_key">

							</td>

						</tr>

						<tr id="row_Bluedart_information_loginid">

							<td class="label"><label for="Bluedart_information_loginid"> LoginID</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_loginid')); ?>" name="Bluedart_information_loginid" id="Bluedart_information_loginid">

							</td>

						</tr>

						<tr id="row_Bluedart_information_email">

							<td class="label"><label for="Bluedart_information_email"> Email Id (From email send)</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_email')); ?>" name="Bluedart_information_email" id="Bluedart_information_email">

							</td>

						</tr>

						<tr id="row_Bluedart_information_store_name">

							<td class="label"><label for="Bluedart_information_store_name"> Store Name</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_store_name')); ?>" name="Bluedart_information_store_name" id="Bluedart_information_store_name">

							</td>

						</tr>

						<tr id="row_Bluedart_information_phone">

							<td class="label"><label for="Bluedart_information_phone"> India's Contact Telephone</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_phone')); ?>" name="Bluedart_information_phone" id="Bluedart_information_phone">

							</td>

						</tr>

						<tr id="row_Bluedart_information_store_address">

							<td class="label"><label for="Bluedart_information_store_address"> Store Contact Address</label></td>

							<td class="value">

								<textarea cols="15" rows="6" class=" textarea" name="Bluedart_information_store_address" id="Bluedart_information_store_address"><?php echo unserialize(get_option('Bluedart_information_store_address')); ?></textarea>

							</td>

						</tr>

						<tr id="row_Bluedart_information_pincode">

							<td class="label"><label for="Bluedart_information_pincode"> PinCode</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_pincode')); ?>" name="Bluedart_information_pincode" id="Bluedart_information_pincode">

							</td>

						</tr>

						<tr id="row_Bluedart_information_customercode">

							<td class="label"><label for="Bluedart_information_customercode"> Customer code</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_customercode')); ?>" name="Bluedart_information_customercode" id="Bluedart_information_customercode">

							</td>

						</tr>

						<tr id="row_Bluedart_information_vandercode">

							<td class="label"><label for="Bluedart_information_vandercode"> Vander code</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_vandercode')); ?>" name="Bluedart_information_vandercode" id="Bluedart_information_vandercode">

							</td>

						</tr>

						<tr id="row_Bluedart_information_originarea">

							<td class="label"><label for="Bluedart_information_originarea"> Origin area</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_originarea')); ?>" name="Bluedart_information_originarea" id="Bluedart_information_originarea">

							</td>

						</tr>

						<tr id="row_Bluedart_information_tin_no">

							<td class="label"><label for="Bluedart_information_tin_no"> GST No.</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('Bluedart_information_tin_no')); ?>" name="Bluedart_information_tin_no" id="Bluedart_information_tin_no">

							</td>

						</tr>

						<tr id="row_Bluedart_information_logo">

							<td class="label"><label for="Bluedart_information_logo"> Logo for PDF</label></td>

							<td class="value">

								<?php $logo_src = home_url() . '/wp-content/uploads/' . unserialize(get_option('Bluedart_information_logo')); ?>

								<a hrfe="<?php echo $logo_src; ?>">

									<img src="<?php echo $logo_src; ?>" style="width:50px;height:20px;" /> </a>

								<input type="file" class="input-file" value="" name="Bluedart_information_logo" id="Bluedart_information_logo">

							</td>

						</tr>

						<tr>
							<td><strong>Default package dimensions (in cm)</strong></td>
							<td></td>
						</tr>

						<tr id="row_Bluedart_information_tin_no">

							<td class="label"><label for="">length</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('package_length')); ?>" name="package_length" id="package_length">

							</td>

						</tr>

						<tr id="row_Bluedart_information_tin_no">

							<td class="label"><label for="">Breadth</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('package_breadth')); ?>" name="package_breadth" id="package_breadth">

							</td>

						</tr>

						<tr id="row_Bluedart_information_tin_no">

							<td class="label"><label for="">Height</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('package_height')); ?>" name="package_height" id="package_height">

							</td>

						</tr>

						<tr id="row_Bluedart_information_tin_no">

							<td class="label"><label for="">Weight (in kgs)</label></td>

							<td class="value"><input type="text" class=" input-text" value="<?php echo unserialize(get_option('package_weight')); ?>" name="package_weight" id="package_weight">

							</td>

						</tr>

						<tr>
							<td></td>
							<td></td>

						<tr>

							<td></td>

							<td class="form-buttons">
								<input type="button" value="Save Config" name="info_save" id="bluedart_info_save" onclick="submitForm();" class="button button-primary">
							</td>

						</tr>

					</tbody>

				</table>

			</form>

		</div>



<?php

	}



	function Manifest_page()
	{

		include_once(plugin_dir_path(__FILE__) . 'manifest_page.php');
	}
}

new Bluedart_Options_Page;
