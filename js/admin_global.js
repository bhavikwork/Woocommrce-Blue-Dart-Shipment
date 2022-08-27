jQuery(document).ready(function( $ ) {
	

	$('#check_all').click(function() {
		if($(this).is(":checked")) {
			$('.check_one').prop('checked', true)
			  
		}else {
				
			$('.check_one').prop('checked', false)
				
		}
    });
	
	$('.fancybox').fancybox();
	
	$(document).on("click", '#bulk-action-selector-top option[value="awb_generation"]',function(){
			if(jQuery("#the-list [type='checkbox']:checked").length > 0){
				
				$("#fancy_popup").trigger("click");
				
			}
			else{
				alert("Please select checkboxes of orders first..");
			}
	});
	$(document).on("click", '#bulk-action-selector-bottom option[value="awb_generation"]',function(){
			if(jQuery("#the-list [type='checkbox']:checked").length > 0){
				
				$("#fancy_popup").trigger("click");
				
			}
			else{
				alert("Please select checkboxes of orders first..");
			}
	});
	
	
	/* for multiple awb generation js start */
	$('#doaction').click(function(event){
		var action=$('#bulk-action-selector-top').val();
		if(action == 'awb_generation'){
			event.preventDefault();
			event.stopPropagation();
			var order_ids = [];
			jQuery("#the-list [type='checkbox']:checked").each(function(){
				var id = jQuery(this).val();
				order_ids.push(id);
			});
			var length=$('.pack_length').val();
            var breadth=$('.pack_breadth').val();
            var height=$('.pack_height').val();
            var weight=$('.pack_weight').val();
			if(order_ids.length >0){
				var data = { action:'get_remote_content_admin', order_ids: order_ids , length:length, breadth:breadth, height:height, weight:weight};
				
				jQuery.ajax({
				type: "POST",
				url: ajax_url.url,
				data:data,
				dataType: 'html',
				beforeSend: function(){ 
					jQuery('#wpwrap').prepend('<div class="overlay">&nbsp;</div>');
						    $('#load2').show();   
					 },
				success: function(res) {
										alert(res);
									},
						   complete: function(){  
							   $('#load2').hide();
							jQuery('.overlay').remove();
							   } 
						});
			}
			else{
				alert("Please select checkboxes of orders first..");
			}
		}
		/***************shipping labels printing **************/
		else if(action == 'shipping_labels_print'){
		
			
			event.preventDefault();
			event.stopPropagation();
			var order_ids = [];
			jQuery("#the-list [type='checkbox']:checked").each(function(){
				var id = jQuery(this).val();
				order_ids.push(id);
			});
			
			 if(order_ids.length >0){
			$("#sp_shipping_labels_all").val(order_ids);
			
			   $('#sp_shipping_labels_printing_form').submit();
				//~ var data = { action:'get_remote_content_admin_shipping_labels_printing', order_ids: order_ids};
				//~ 
				//~ jQuery.ajax({
				//~ type: "POST",
				//~ url: ajax_url.url,
				//~ data:data,
				//~ dataType: 'html',
				//~ beforeSend: function(){ 
					//~ jQuery('#wpwrap').prepend('<div class="overlay">&nbsp;</div>');
						 //~ $('#load2').show();   
					 //~ },
				//~ success: function(res) {
										//~ alert(res);
									//~ },
						   //~ complete: function(){  
							//~ $('#load2').hide();
							//~ jQuery('.overlay').remove();
							   //~ } 
						//~ });
			}
			else{
				alert("please select the orders first.");
			}
		
		}
	});
	
	
		$('#doaction2').click(function(event){
		var action=$('#bulk-action-selector-bottom').val();
		if(action == 'awb_generation'){
			event.preventDefault();
			event.stopPropagation();
			var order_ids = [];
			jQuery("#the-list [type='checkbox']:checked").each(function(){
				var id = jQuery(this).val();
				order_ids.push(id);
			});
			var length=$('.pack_length').val();
            var breadth=$('.pack_breadth').val();
            var height=$('.pack_height').val();
            var weight=$('.pack_weight').val();
			if(order_ids.length >0){
				var data = { action:'get_remote_content_admin', order_ids: order_ids , length:length, breadth:breadth, height:height, weight:weight};
				
				jQuery.ajax({
				type: "POST",
				url: ajax_url.url,
				data:data,
				dataType: 'html',
				beforeSend: function(){ 
					jQuery('#wpwrap').prepend('<div class="overlay">&nbsp;</div>');
						    $('#load2').show();   
					 },
				success: function(res) {
										alert(res);
									},
						   complete: function(){  
							   $('#load2').hide();
							jQuery('.overlay').remove();
							   } 
						});
			}
			else{
				alert("Please select checkboxes of orders first..");
			}
		}
		/***************shipping labels printing **************/
		else if(action == 'shipping_labels_print'){
		
			
			event.preventDefault();
			event.stopPropagation();
			var order_ids = [];
			jQuery("#the-list [type='checkbox']:checked").each(function(){
				var id = jQuery(this).val();
				order_ids.push(id);
			});
			if(order_ids.length >0){
				var data = { action:'get_remote_content_admin_shipping_labels_printing', order_ids: order_ids};
				
				jQuery.ajax({
				type: "POST",
				url: ajax_url.url,
				data:data,
				dataType: 'html',
				beforeSend: function(){ 
					jQuery('#wpwrap').prepend('<div class="overlay">&nbsp;</div>');
						 $('#load2').show();   
					 },
				success: function(res) {
										alert("shipping labels for the selected orders are generated successfully.");
									},
						   complete: function(){  
							$('#load2').hide();
							jQuery('.overlay').remove();
							   } 
						});
			}
			else{
				alert("please select the orders first.");
			}
		
		}
	});
	
	
	//for sending dimensions through pop up 
	
	$('#shipment_button1').click(function(){
		length  = $('#length').val();
		breadth = $('#breadth').val();
		height  = $('#height').val();
		weight  = $('#weight').val();
		if(isNaN(length) || isNaN(breadth) || isNaN(height) || isNaN(weight)){
			jQuery("#error").html("");
			alert("please enter no. only in all the fields ");
		}
		else{
			if(length == "" || breadth == "" || height == "" || weight == ""){
					
					jQuery("#error").html("please enter all values");
					
			}
			else if(length == 0 || breadth == 0 || height == 0 || weight == 0){
					
					jQuery("#error").html("Please enter values other than 0.");
					
			}
			else{
				    jQuery("#error").html("");
					$('.pack_length').val(length);
					$('.pack_breadth').val(breadth);
					$('.pack_height').val(height);
					$('.pack_weight').val(weight);
					$('#length').val();
					$('#breadth').val();
					$('#height').val();
					$('#weight').val();
					alert("Dimensions added successfully");
					$.fancybox.close();
			}
		}
			   
	});
	
	/* for multiple awb generation js end */

});
