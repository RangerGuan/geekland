jQuery(document).ready(function($){
	
	
	startHookActivate();
	startHookDesactivate();
	
	
	jQuery('.inp_license_key').keyup(function(e){
		var plugin_name = jQuery(this).data('plugin');
		if (jQuery(this).val() == '') {
			jQuery('#tr_license_status_'+plugin_name).fadeOut();
			return false;
		}
		
		jQuery('#td_license_status_'+plugin_name).html('<input id="'+plugin_name+'_btn_license_check" class="btn_license_check button-secondary" name="'+plugin_name+'_btn_license_check" type="button" value="'+wpematico_license_object.txt_check_license+'"/><div id="'+plugin_name+'_ajax_status_license" style="display:none;"></div>');
		jQuery('#tr_license_status_'+plugin_name).fadeIn();
		jQuery('.btn_license_check').click(function(e){
			var plugin_name_check = this.id.replace('_btn_license_check', '');
			jQuery(this).prop('disabled', true);
			jQuery('#'+plugin_name_check+'_ajax_status_license').html('Checking key...');
			jQuery('#'+plugin_name_check+'_ajax_status_license').fadeIn();
			var data_request = {
				action: 'wpematico_check_license',
				plugin_name: plugin_name_check,
				license:jQuery('#license_key_'+plugin_name_check).val()
			}
			
			jQuery.post(wpematico_license_object.ajax_url, data_request, function( data ) {
				var response = jQuery.parseJSON(data);
				console.log(response);
				if (response.license == 'invalid' || response.license == 'expired') {
					jQuery('#'+data_request.plugin_name+'_ajax_status_license').html('<strong style="color:red;">This key is invalid</strong>');
				} else if (response.license == 'item_name_mismatch') {
					jQuery('#'+data_request.plugin_name+'_ajax_status_license').html('<strong style="color:yellow;">This key is invalid for this plugin.</strong>');
				} else {
					jQuery('#'+data_request.plugin_name+'_ajax_status_license').html('<strong style="color:green;">This key is valid, please update settings.</strong>');
				}
				
				jQuery('#'+data_request.plugin_name+'_btn_license_check').prop('disabled', false);
			});
			
		});
	});
	
	
});
function startHookActivate() {
	jQuery('.btn_license_activate').click(function(e){
		var plugin_name = this.id.replace('_btn_license_activate', '');
		jQuery(this).prop('disabled', true);
		jQuery('#'+plugin_name+'_ajax_status_license').html('Activating key...');
		jQuery('#'+plugin_name+'_ajax_status_license').fadeIn();
		var data_request = {
			action: 'wpematico_status_license',
			plugin_name: plugin_name,
			status:'activate_license'
		}
		
		jQuery.post(wpematico_license_object.ajax_url, data_request, function( data ) {
			var response = jQuery.parseJSON(data);
			console.log(response);
			jQuery('#'+data_request.plugin_name+'_btn_license_activate').prop('disabled', false);
			jQuery('#td_license_status_'+data_request.plugin_name+'').html('<p><strong>Status:</strong> Valid<span class="validcheck"> </span><br><input id="'+data_request.plugin_name+'_btn_license_deactivate" class="btn_license_deactivate button-secondary" name="'+data_request.plugin_name+'_btn_license_deactivate" type="button" value="Deactivate License" style="vertical-align: middle;"/></p><div id="'+data_request.plugin_name+'_ajax_status_license">');
			startHookDesactivate();
			
		});
	});
}
function startHookDesactivate() {
	jQuery('.btn_license_deactivate').click(function(e){
		var plugin_name = this.id.replace('_btn_license_deactivate', '');
		jQuery(this).prop('disabled', true);
		jQuery('#'+plugin_name+'_ajax_status_license').html('Desactivating key...');
		jQuery('#'+plugin_name+'_ajax_status_license').fadeIn();
		var data_request = {
			action: 'wpematico_status_license',
			plugin_name: plugin_name,
			status:'deactivate_license'
		}
		
		jQuery.post(wpematico_license_object.ajax_url, data_request, function( data ) {
			var response = jQuery.parseJSON(data);
			console.log(response);
			jQuery('#'+data_request.plugin_name+'_btn_license_deactivate').prop('disabled', false);
			jQuery('#td_license_status_'+data_request.plugin_name+'').html('<p><strong>Status:</strong> Inactive<i class="warningcheck"></i><br><input id="'+data_request.plugin_name+'_btn_license_activate" class="btn_license_activate button-secondary" name="'+data_request.plugin_name+'_btn_license_activate" type="button" value="Activate License"></p><div id="'+data_request.plugin_name+'_ajax_status_license">');
			startHookActivate();
		});
	});
}