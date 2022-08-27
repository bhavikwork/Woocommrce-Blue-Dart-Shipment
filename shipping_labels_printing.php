<?php 
include('../../../wp-load.php');

$order_ids=explode(',',$_REQUEST['order_ids']);
global $post;

if(count($_REQUEST['order_ids']) > 0){
$Bluedart_information_select_mode=unserialize(get_option('Bluedart_information_select_mode'));
$Bluedart_information_licence_key= unserialize(get_option('Bluedart_information_licence_key'));
$Bluedart_information_loginid= unserialize(get_option('Bluedart_information_loginid'));
$Bluedart_information_email= unserialize(get_option('Bluedart_information_email') );
$Bluedart_information_store_name= unserialize(get_option('Bluedart_information_store_name') );
$Bluedart_information_phone= unserialize(get_option('Bluedart_information_phone') );
$Bluedart_information_store_address= unserialize(get_option('Bluedart_information_store_address') );	
$Bluedart_information_pincode= unserialize(get_option('Bluedart_information_pincode') ); 
$Bluedart_information_customercode= unserialize(get_option('Bluedart_information_customercode') ); 
$Bluedart_information_vandercode= unserialize(get_option('Bluedart_information_vandercode') ); 
$Bluedart_information_originarea= unserialize(get_option('Bluedart_information_originarea') ); 
$Bluedart_information_tin_no= unserialize(get_option('Bluedart_information_tin_no') ); 
//$logoUrl = home_url().'/wp-content/uploads/'.unserialize(get_option('Bluedart_information_logo')); 
$html1="";
foreach($order_ids as $order_id){
		  
	$order = new WC_Order($order_id);	

    $order_post = get_post($order_id);
 
    $datetime=$order_post->post_date;
    $date= explode(" ", $datetime);
    $order_date= $date[0];
    $payment_method_code = get_post_meta( $order_id, '_payment_method', true ); //cod
    $collectableAmount = 0;
    $SubProductCode = 'p';
    $payment_method = "PREPAID";
    $awb_field_label="PREPAID";
    if( $payment_method_code == 'cod' ){
        $collectableAmount = $order->get_total(); 
        $SubProductCode = 'c';
        $payment_method = "COD"; 
        $awb_field_label="COD Amount : ".number_format($collectableAmount,2);
    }	
   

    $mrp = 0;
	if($collectableAmount){ 
            $mrp = $collectableAmount; 
	}
	$i = 1;
	$items = ''; 
	$ordered_items = $order->get_items();
	foreach($ordered_items as $item){ 
		
            $commodityDetail['CommodityDetail'.$i] = $item['qty'].'x '.preg_replace('/[^a-zA-Z0-9]/', ' ', $item['name']);
            $items = $commodityDetail['CommodityDetail'.$i].','.$items; 
            
            $i++;
				  
	}
	$items=rtrim($items,',');
	
        
	$customer_name = $order->shipping_first_name.' '.$order->shipping_last_name;
	$company=$order->shipping_company;
	$shipping_address_3 = $order->shipping_city.' '.$order->shipping_state;
	$shipping_postcode=$order->shipping_country.' '.$order->shipping_postcode;
	    global $wpdb;
		$order_menifest_details = $wpdb->get_results(
									"SELECT *
										FROM `".$wpdb->prefix."orders_manifests`
										WHERE `order_id` =".$order_id."
										ORDER BY `order_id` DESC Limit 1"
									);
								
			$order_menifest_details_awb_no=isset($order_menifest_details[0])?$order_menifest_details[0]->awb_no:"";
			$order_menifest_details_awb_no_text=isset($order_menifest_details[0])?$order_menifest_details[0]->awb_no:"awb no.";
			$destination=isset($order_menifest_details[0])?$order_menifest_details[0]->destination:"";
			$weight=isset($order_menifest_details[0])?$order_menifest_details[0]->weight:"";
			$declared_value=isset($order_menifest_details[0])?$order_menifest_details[0]->declared_value:0;
			
			$html1.='<div style="margin-bottom:80px;" class="sp_blue_ship_labels_class">
			
							<table border="1px" style="width:800px;" cellpadding="20">
								<tr>
								<td style="border: 1px solid #000;padding:10px;text-align:left;font-size:12px;">Origin: '.$Bluedart_information_originarea.'<br/>
								Destination: '.$destination.'</td>
								<td style="border: 1px solid #000;padding:10px;text-align:left;font-size:12px;font-weight:bold;" rowspan="2">
									
									<br/>'.$items.'<br/>
										'.$customer_name.', 
										'.$company.'<br/>
										'.$order->shipping_address_1.', '.$order->shipping_address_2.'<br/>
										'.$shipping_address_3.',<br/>'.$shipping_postcode.' Phone: '.$order->billing_phone.'<br/>
										
									</td>
								</tr>
								<tr>
								<td style="border: 1px solid #000;padding:10px;text-align:left;font-size:12px;">
								
								Sender:  '.$Bluedart_information_store_name.'<br/>
								GST No. : 08AFDPC7911A1Z2<br/>
								Address: '.$Bluedart_information_store_address.' 334001<br/>
								Shipper: 335635<br/>
								Tel/Mob: '.$Bluedart_information_phone.'<br/>
								</td>		

								</tr>
							</table>
							<table border="1px" style="width:800px;" cellpadding="20"  >
								<tr>
									<td style="border: 1px solid #000;padding:10px;text-align:left;font-size:12px;">Pickup Date:<br/>
									Time:<br/>
									Emp#:<br/>
									Sign:<br/>
									PUR :</td>
									<td style="border: 1px solid #000;padding:10px;text-align:center">
									   <span style="font-size:14px;font-weight:bold">'.$awb_field_label.'</span><br/>
										<div class="img-cntr"><barcode code='.$order_menifest_details_awb_no.'  type="C39" size="1.0" height="1.0" />
										</div>
										<p style="width:100%; text-align:center;margin-top:20px;">'.$order_menifest_details_awb_no_text.'</p>
									</td>
									<td style="border: 1px solid #000;padding:10px;text-align:left;font-size:12px;">No.of pack: 1<br/>
									Act Wt (kg) :'.$weight.'<br/>
									Declared Value : '.number_format($declared_value,2).'
									</td>
											
								</tr>
								<tr>
								<td style="border: 1px solid #000;padding:10px;text-align:left;font-size:12px;">Transaction Code:</td>
								<td style="border: 1px solid #000;padding:10px;text-align:left;font-size:12px;">Reference Number :'.$order_id.'
								<br/>Product Name :'.$items.'</td>
								<td style="border: 1px solid #000;padding:10px;text-align:left;font-size:12px;">
								Collectable Value : '.number_format($collectableAmount,2).'</td>
											
								</tr>
								<tr colspan="3">
								<td style="border: 1px solid #fff;padding:10px;text-align:center;font-size:12px;"></td>
								<td style="border: 1px solid #fff;padding:10px;text-align:left;font-size:12px;">Please Call The Customer If Any Problems in Delivering the Parcel : '.$order->billing_phone.'</td>
										<td style="border: 1px solid #fff;padding:10px;text-align:center;font-size:12px;"></td>		
								</tr>
							</table>
						</div>';
			
				   
        
}
					
					require_once( plugin_dir_path(__FILE__).'lib/MPDF57/mpdf.php' );
					ob_start();
				    $mpdf=new mPDF('c','A4','','',9,9,18,9,9,9); 
					$mpdf->SetDisplayMode('fullpage');
					$mpdf->showImageErrors = false;

					$mpdf->list_indent_first_level = 0; 
					
					
					
					 
					$html = '<html>
					<head>
					<meta charset="utf-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<title></title>
					<meta name="description" content="">
					</head>
					<body>'.$html1.'</body></html>'; 
					
					$cssPath1 = "https://fonts.googleapis.com/css?family=Lato"; 
					$stylesheet1 = file_get_contents($cssPath1); 					  

					$mpdf->WriteHTML($stylesheet1,1);    // The parameter 1 tells that this is css/style only and no body/html/text
					
					
					$cssPath = plugin_dir_path(__FILE__).'lib/css/pdf.css'; 
					$stylesheet = file_get_contents($cssPath); 					  

					$mpdf->WriteHTML($stylesheet,1);    // The parameter 1 tells that this is css/style only and no body/html/text
					
					$mpdf->WriteHTML($html,2);

					$file_name = 'shipping_labels.pdf';	
					$mpdf->Output($file_name,'D');
					ob_end_flush() ;
}
?>
