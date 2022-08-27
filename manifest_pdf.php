<?php 

include('../../../wp-load.php');
$ids=array();
if(isset($_REQUEST['generate_menifest']) || isset($_REQUEST['generate_menifest1']) ){
if(isset($_REQUEST['generate_menifest'])){
				$results=$wpdb->get_results("SELECT id
						FROM `".$wpdb->prefix."orders_manifests`
						WHERE DATE(created_at)
						BETWEEN '{$_REQUEST['menifest_from']}'
						AND '{$_REQUEST['menifest_to']}' ");
						foreach($results as $result){
							$ids[]=$result->id;
						}
}
if(isset($_REQUEST['generate_menifest1']) && isset($_REQUEST['ids'])){
				$ids=$_REQUEST['ids'];
}
			if(count($ids)>0){
				$menifest_ids = implode(',',$ids);
					
					
				if(count($ids) > 0){
					
					require_once( plugin_dir_path(__FILE__).'lib/MPDF57/mpdf.php' );
					 
					
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
					
					$menifests= $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."orders_manifests` where id in (".$menifest_ids.")");
					
					$html1="";
					
					
					foreach($menifests as $result){
				
						$html1 .='<tr>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.$result->awb_no.'</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.$result->order_id.'</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.$result->customer_name.'</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.$result->shipping_address.'</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.$result->pin_code.'</td>
						
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.rtrim($result->items,',').'</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.$result->weight.'</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.number_format($result->declared_value,2).'</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.number_format($result->collectable,2).'</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">'.$result->mode.'</td>
							<td style="border-bottom: 1px solid #000;padding:10px;text-align:center;">
								<div class="img-cntr"><barcode code='.$result->awb_no.'  type="C39" size="1.0" height="2.0" /></div>
											<p style="width:100%; text-align:center;">'.$result->awb_no.'</p>
							</td>
						</tr>';
					}
					
				
					$html= '<html>
					<head>
					<meta charset="utf-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<title></title>
					<meta name="description" content="">
					</head>
					<body>
						
							<div style="margin-bottom:10px;">	
								<div style="font-size: 16px; text-align: center;margin-bottom:10px;">Shipping Provider Name :Blue Dart</div>
									<table cellpadding="10px" cellspacing="4px" style=" border:0px solid #fff; width:100%;">
										<tbody>
											<tr style="border: 1px solid #fff;">
												<td style="border: 1px solid #fff;font-size: 14px;">Manifest No:'.strtotime("now").'</td>
												<td style="border: 1px solid #fff;text-align:right;font-size: 14px;">Channel Name:'.strtolower($Bluedart_information_store_name).'</td>
												<td style="border: 1px solid #fff;text-align:right;font-size: 14px;">Daate:'.date("Y-m-d").'</td>	
											</tr>
										</tbody>
									</table>
								<div style="text-align: center;font-size: 16px;">'.$Bluedart_information_store_address.'</div>
							</div>	
							<div style="margin-bottom:10px;">	
								<table  cellpadding="10px" cellspacing="4px" style="border:1px solid #000;width:100%;border-bottom:none;">
									<tbody>
									<tr>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Airwaybill</th>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Reference Number</th>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Attention</th>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Address</th>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Pincode</th>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Contents</th>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Weight(Kg)</th>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Declared Value</th>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Collectable</th>
										<th style="border-bottom: 1px solid #000;border-right: 1px solid #000;padding:10px;text-align:center;">Mode</th>
										<th style="border-bottom: 1px solid #000;padding:10px;text-align:center;">Barcode</th>
									</tr>
									'.$html1.'
									</tbody>
								</table>
							</div>
							</div>
							<p style="width: 100%; text-align:center; font-size: 16px; margin-bottom: 5px;">This is computer generated document, hence does not require signature.</p>
						
					</body>
					</html>'; 
					
						ob_start();
						$mpdf=new mPDF('c','A4','','',9,9,9,9,9,9); 
						$mpdf->SetDisplayMode('fullpage');
						$mpdf->showImageErrors = true;
						$mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list  
						//$stylesheet = file_get_contents($cssPath);

						//$mpdf->WriteHTML($stylesheet,1);

						$mpdf->WriteHTML($html,2);
						if(isset($_REQUEST['menifest_from'])){
							$file_name = 'manifest_from-'.$_REQUEST['menifest_from'].'_to-'.$_REQUEST['menifest_to'].'.pdf';
						}else{
							$file_name = 'manifest.pdf';
						}	
						
						
						$mpdf->Output($file_name,'D');
						ob_end_flush() ;
			}
		}
			else{
			  echo '<script>window.location="'.admin_url('/admin.php?page=Manifest&manifest_error=1').'";</script>';
			
			}
			
		}
?>
