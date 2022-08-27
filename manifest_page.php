<?php 
global $wpdb;
		$results= $wpdb->get_results(
					"SELECT *
						FROM {$wpdb->prefix}orders_manifests ORDER BY order_id DESC"
					);
		
					?>
<div class="content-header menifest_main" style="margin-top:1% width:97%;">
	<?php if(isset($_REQUEST['manifest_error'])){
		echo '<div class="error" style="margin-top: 3%; text-align: center; color: red;">No waybill generated during this date</div>';
		echo '<script>setTimeout(function(){jQuery(\'.error\').hide(\'slow\')},5000);</script>';
	}
	?>
	
	
	<div class="generate_menifest_form">
		<form action="<?php echo plugin_dir_url(__FILE__).'manifest_pdf.php'?>" name="menifest_form1" method="post">
			<div class="" style="margin-bottom:10px;"><span style="text-align:center;font-size:14px;">Manifest generation</span> </div>
		
			<div class="row1"><span>From  </span><input type="text" name="menifest_from" value="" class="datepicker">
			<span>To </span><input type="text" name="menifest_to" value="" class="datepicker"></div>
			<input type="submit" value="generate manifest" name="generate_menifest" style="margin-top: 2%; margin-left: 1%;"/>
		</form>
	</div>
	<script>
		jQuery(function($){
			$('.datepicker').datepicker({
			 dateFormat: 'yy-mm-dd'
			});
		});
		
	</script>
	<form action="<?php echo plugin_dir_url(__FILE__).'manifest_pdf.php'?>" name="menifest_form1" method="post">
	<div style="margin-bottom: 2%; margin-top: 5%;">
		<label style="margin-left:40%;font-size:20px;"><strong>Complete Listing</strong></label>
	</div>
	<table border="" cellpadding="10px" cellspacing="4px"style="width:98%">
			<tbody>
			<tr>
				<th><input type="checkbox" value="check_all" name="check_all" id="check_all"></th>
				<th>order id</th>
				<th>awb no.</th>
				<th>shipping address</th>
				<th>items</th>
				<th>weight</th>
				<th>created date</th>
			</tr>
			
		<?php 
			/************listing with pagination*******************/
			$adjacents = 3;
			$records_count = $wpdb->get_results("SELECT COUNT(*) as num FROM ".$wpdb->prefix."orders_manifests");
			$total_pages = $records_count[0]->num;
			$targetpage = home_url()."/wp-admin/admin.php?page=Manifest"; 
			$limit = 10; 
			$page = isset($_REQUEST['page_no'])?$_REQUEST['page_no']:0;
			if($page) 
				$start = ($page - 1) * $limit; 			//first item to display on this page
			else
				$start = 0;								//if no page var is given, set start to 0
		
			$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."orders_manifests LIMIT $start, $limit");
			/* Setup page vars for display. */
			if ($page == 0) $page = 1;					//if no page var is given, default to 1.
			$prev = $page - 1;							//previous page is page - 1
			$next = $page + 1;							//next page is page + 1
			$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
			$lpm1 = $lastpage - 1;						//last page minus 1
			
			/* 
				Now we apply our rules and draw the pagination object. 
				We're actually saving the code to a variable in case we want to draw it more than once.
			*/
			$pagination = "";
			if($lastpage > 1){	
				$pagination .= "<div class=\"pagination\" style=\"margin-top:1%;\">";
				//previous button
				if ($page > 1) 
					$pagination.= "<a href=\"$targetpage&page_no=$prev\"> previous</a>";
				else
					$pagination.= "<span class=\"disabled\"> previous</span>";	
				
				//pages	
				if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
				{	
					for ($counter = 1; $counter <= $lastpage; $counter++)
					{
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";					
					}
				}
				elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
				{
					//close to beginning; only hide later pages
					if($page < 1 + ($adjacents * 2))		
					{
						for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
						{
							if ($counter == $page)
								$pagination.= "<span class=\"current\">$counter</span>";
							else
								$pagination.= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";					
						}
						$pagination.= "...";
						$pagination.= "<a href=\"$targetpage&page_no=$lpm1\">$lpm1</a>";
						$pagination.= "<a href=\"$targetpage&page_no=$lastpage\">$lastpage</a>";		
					}
					//in middle; hide some front and some back
					elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
					{
						$pagination.= "<a href=\"$targetpage?page_no=1\">1</a>";
						$pagination.= "<a href=\"$targetpage?page_no=2\">2</a>";
						$pagination.= "...";
						for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
						{
							if ($counter == $page)
								$pagination.= "<span class=\"current\">$counter</span>";
							else
								$pagination.= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";					
						}
						$pagination.= "...";
						$pagination.= "<a href=\"$targetpage&page_no=$lpm1\">$lpm1</a>";
						$pagination.= "<a href=\"$targetpage&page_no=$lastpage\">$lastpage</a>";		
					}
					//close to end; only hide early pages
					else
					{
						$pagination.= "<a href=\"$targetpage&page_no=1\">1</a>";
						$pagination.= "<a href=\"$targetpage&page_no=2\">2</a>";
						$pagination.= "...";
						for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
						{
							if ($counter == $page)
								$pagination.= "<span class=\"current\">$counter</span>";
							else
								$pagination.= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";					
						}
					}
				}
				
				if ($page < $counter - 1) 
					$pagination.= "<a href=\"$targetpage&page_no=$next\">next </a>";
				else
					$pagination.= "<span class=\"disabled\">next </span>";
				$pagination.= "</div>\n";		
			}
				foreach($results as $result){
							
								echo '<td><input type="checkbox" name="ids[]" value="'.$result->id.'" class="check_one"></td>
								<td>'.$result->order_id.'</td><td>'.$result->awb_no.'</td><td>'
								.$result->shipping_address.'</td><td>'.rtrim($result->items,',').'</td><td>'.$result->weight.'</td><td>'
								.$result->created_at.'</td></tr>';
							} 	
				
			?>

			</tbody>
		</table>
		<input type="submit" value="generate manifest for selected orders" name="generate_menifest1" style="margin-top: 2%; margin-left: 1%;"/>
		</form>
	<?php echo $pagination;
	?>
</div>
