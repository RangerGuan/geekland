<script type="text/javascript">
jQuery(function($){
   $("h3.hndle").click(function(){$(this).next(".inside").slideToggle('fast');});
});

jQuery(function($){
   $(".apShowHtml").click(function(){$(this).next(".apdebugHtml").slideToggle('fast');});
});

function findObj(theObj, theDoc){  var p, i, foundObj;    if(!theDoc) theDoc = document;  if( (p = theObj.indexOf("?")) > 0 && parent.frames.length)  {    theDoc = parent.frames[theObj.substring(p+1)].document;    theObj = theObj.substring(0,p);  }  if(!(foundObj = theDoc[theObj]) && theDoc.all) foundObj = theDoc.all[theObj];  for (i=0; !foundObj && i < theDoc.forms.length; i++)     foundObj = theDoc.forms[i][theObj];  for(i=0; !foundObj && theDoc.layers && i < theDoc.layers.length; i++)     foundObj = findObj(theObj,theDoc.layers[i].document);  if(!foundObj && document.getElementById) foundObj = document.getElementById(theObj);    return foundObj;}


function addNew(){
  if(document.getElementById("new_config_name").value=='')return;
  document.getElementById("myform").submit();
}

function testFetch(){
  document.getElementById("saction").value='testFetch';
  document.getElementById("myform").submit();
}
function AddRowType1(){
 var TRLastIndex = findObj("Type1TRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);

 var table = findObj("OptionType1",document);
 
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "type1" + rowID; 

 var newTD1=newTR.insertCell(0);
 newTD1.innerHTML = '<input type="text" name="type1_para1[]" value="" style="width:100%">';
 
 var newTD2=newTR.insertCell(1);
 newTD2.innerHTML = '<input type="text" name="type1_para2[]" value="" style="width:100%">';

 var newTD3=newTR.insertCell(2);
 newTD3.innerHTML = '<input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType1(\'type1'+rowID+'\')"/>';

 TRLastIndex.value = (rowID + 1).toString() ;

}

function deleteRowType1(rowid){
 var table = findObj("OptionType1",document);
 var signItem = findObj(rowid,document);
 var rowIndex = signItem.rowIndex;
 table.deleteRow(rowIndex);
}

function AddRowType2(){
 var TRLastIndex = findObj("Type2TRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);

 var table = findObj("OptionType2",document);
 
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "type2" + rowID;  

 var newTD1=newTR.insertCell(0);
 newTD1.innerHTML = '<input type="text" name="type2_para1[]" value="">';
 
 var newTD2=newTR.insertCell(1);
 newTD2.innerHTML = '<select name="type2_para2[]" ><option value="0"><?php echo __('No'); ?></option><option value="1" ><?php echo __('Yes'); ?></option></select>';

 var newTD3=newTR.insertCell(2);
 newTD3.innerHTML = '<input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType2(\'type2'+rowID+'\')"/>';
 
 TRLastIndex.value = (rowID + 1).toString() ;

}
function deleteRowType2(rowid){
 var table = findObj("OptionType2",document);
 var signItem = findObj(rowid,document);
 var rowIndex = signItem.rowIndex;
 table.deleteRow(rowIndex);
}

function AddRowType3(){
 var TRLastIndex = findObj("Type3TRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);

 var table = findObj("OptionType3",document);
 
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "type3" + rowID; 

 var newTD1=newTR.insertCell(0);
 newTD1.innerHTML = '<input type="text" name="type3_para1[]" value="" style="width:100%">';
 
 var newTD2=newTR.insertCell(1);
 newTD2.innerHTML = '<input type="text" name="type3_para2[]" value="" style="width:100%">';

 var newTD2=newTR.insertCell(2);
 newTD2.innerHTML = '<div style="text-align:center;"><select name="type3_option[]"><option value="0"><?php echo __('No'); ?></option><option value="1"><?php echo __('Yes'); ?></option></select></div>';

 var newTD3=newTR.insertCell(3);
 newTD3.innerHTML = '<input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType3(\'type3'+rowID+'\')"/>';
 
 TRLastIndex.value = (rowID + 1).toString() ;

}
function deleteRowType3(rowid){
 var table = findObj("OptionType3",document);
 var signItem = findObj(rowid,document);
 var rowIndex = signItem.rowIndex;
 table.deleteRow(rowIndex);
}

function AddRowType4(){
 var TRLastIndex = findObj("Type4TRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);

 var table = findObj("OptionType4",document);
 
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "type4" + rowID; 

 var newTD1=newTR.insertCell(0);
 newTD1.innerHTML = '<input type="text" name="type4_para1[]" value="" style="width:100%">';
 
 var newTD2=newTR.insertCell(1);
 newTD2.innerHTML = '<input type="text" name="type4_para2[]" value="" style="width:100%">';

 var newTD3=newTR.insertCell(2);
 newTD3.innerHTML = '<input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType4(\'type4'+rowID+'\')"/>';
 
 TRLastIndex.value = (rowID + 1).toString() ;
}

function deleteRowType4(rowid){
 var table = findObj("OptionType4",document);
 var signItem = findObj(rowid,document);
 var rowIndex = signItem.rowIndex;
 table.deleteRow(rowIndex);
}

function AddRowType5(){
 var TRLastIndex = findObj("Type5TRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);

 var table = findObj("OptionType5",document);
 
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "type5" + rowID; 

 var newTD1=newTR.insertCell(0);
 newTD1.innerHTML = '<input type="text" name="type5_para1[]" value="" style="width:100%" />';
 
 var newTD2=newTR.insertCell(1);
 newTD2.innerHTML = '<input type="text" name="type5_para2[]" value="0" size="1" />';

 var newTD3=newTR.insertCell(2);
 newTD3.innerHTML = '<input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType5(\'type5'+rowID+'\')"/>';
 
 TRLastIndex.value = (rowID + 1).toString() ;
}

function deleteRowType5(rowid){
 var table = findObj("OptionType5",document);
 var signItem = findObj(rowid,document);
 var rowIndex = signItem.rowIndex;
 table.deleteRow(rowIndex);
}

function SaveOption1(){
  document.getElementById("saction6").value='SaveOption1';
  document.getElementById("myform6").submit();
}
function SaveOption5(){
  document.getElementById("saction6").value='SaveOption5';
  document.getElementById("myform6").submit();
}
function SaveOption2(){
  document.getElementById("myform7").submit();
}
function SaveOption3(){
  document.getElementById("myform8").submit();
}
function SaveOption4(){
  document.getElementById("myform9").submit();
}
function SaveConfigOption(){
  document.getElementById("myform11").submit();
}

function edit(){
  if(document.getElementById("config_name").value==''){
	alert('Please Enter Task Name');  
    return;
  }
  if(document.getElementById("set_watermark_image").checked==true){
	if(document.getElementById("watermark_id").value==0){
	  alert('Please Selected the Watermark Name');  
      return;
	}
  }
  document.getElementById("myform1").submit();
}


function save2(){
  if((document.getElementById("source_type").value==0 || document.getElementById("source_type").value==2) && document.getElementById("urls").value==''){
	 alert("<?php echo __('Please enter URL!','wp-autopost'); ?>");
	 return;
  }
  if(document.getElementById("source_type").value==1 && document.getElementById("url").value==''){
	 alert("<?php echo __('Please enter URL!','wp-autopost'); ?>");
	 return;
  }
  if(document.getElementById("source_type").value!=2 && document.getElementById("a_match_type").value==0 && document.getElementById("a_selector_0").value==''){
	 alert("<?php echo __('Please enter Article URL matching rules!','wp-autopost'); ?>");
	 return;
  }
  if(document.getElementById("source_type").value!=2 && document.getElementById("a_match_type").value==1 && document.getElementById("a_selector_1").value==''){
	 alert("<?php echo __('Please enter Article URL matching rules!','wp-autopost'); ?>");
	 return;
  }
  document.getElementById("myform2").submit();
}
function test2(){
  if(document.getElementById("source_type").value==0 && document.getElementById("urls").value==''){
	 alert("<?php echo __('Please enter URL!','wp-autopost'); ?>");
	 return;
  }
  if(document.getElementById("source_type").value==1 && document.getElementById("url").value==''){
	 alert("<?php echo __('Please enter URL!','wp-autopost'); ?>");
	 return;
  }  
  if(document.getElementById("source_type").value!=2 && document.getElementById("a_match_type").value==0 && document.getElementById("a_selector_0").value==''){
	 alert("<?php echo __('Please enter Article URL matching rules!','wp-autopost'); ?>");
	 return;
  }
  if(document.getElementById("source_type").value!=2 && document.getElementById("a_match_type").value==1 && document.getElementById("a_selector_1").value==''){
	 alert("<?php echo __('Please enter Article URL matching rules!','wp-autopost'); ?>");
	 return;
  }
  document.getElementById("saction2").value='test2';
  document.getElementById("myform2").submit();
}

function autoseturl(){
  if(document.getElementById("source_type").value==0 && document.getElementById("urls").value==''){
	 alert("<?php echo __('Please enter URL!','wp-autopost'); ?>");
	 return;
  }
  if(document.getElementById("source_type").value==1 && document.getElementById("url").value==''){
	 alert("<?php echo __('Please enter URL!','wp-autopost'); ?>");
	 return;
  }
  document.getElementById("saction2").value='autoseturl';
  document.getElementById("myform2").submit();
}

function save3(){
  if(document.getElementById("title_match_type").value==0 && document.getElementById("title_selector_0").value==''){
	 alert("<?php echo __('Please enter The Article Title Matching Rules!','wp-autopost'); ?>");
	 return;
  }
  if(document.getElementById("title_match_type").value==1 && (document.getElementById("title_selector_1_start").value=='' || document.getElementById("title_selector_1_end").value=='') ){
	 alert("<?php echo __('Please enter The Article Title Matching Rules!','wp-autopost'); ?>");
	 return;
  }
  if(document.getElementById("content_match_type_0").value==0 && document.getElementById("content_selector_0_0").value==''){
	  alert("<?php echo __('Please enter The Article Content Matching Rules!','wp-autopost'); ?>");
	 return;
  }
  if(document.getElementById("content_match_type_0").value==1 && (document.getElementById("content_selector_1_start_0").value==''||document.getElementById("content_selector_1_end_0").value=='')){
	  alert("<?php echo __('Please enter The Article Content Matching Rules!','wp-autopost'); ?>");
	 return;
  }
  document.getElementById("myform3").submit();
}
function showTest3(){ 
  jQuery("#test3").show();	
}
function test3(){
  if(document.getElementById("testUrl").value==''){
	 alert("<?php echo __('Please enter the URL of test!','wp-autopost'); ?>");
	 return;
  }
  document.getElementById("saction3").value='test3';
  document.getElementById("myform3").submit();
}
function changePostType(){
  document.getElementById("saction1").value='changePostType';
  document.getElementById("myform1").submit(); 
}

function save15(){
  document.getElementById("myform15").submit();
}
function showTestCookie(){ 
  jQuery("#testCookie").show();	
}
function testCookie(){
  if(document.getElementById("testcCookieUrl").value==''){
	 alert("<?php echo __('Please enter the URL of test!','wp-autopost'); ?>");
	 return;
  }
  document.getElementById("saction15").value='testCookie';
  document.getElementById("myform15").submit();
}

function save16(){
  document.getElementById("myform16").submit();
}

function autosetSettings(){
  if(document.getElementById("autoset_url").value==''){
	 alert("<?php echo __('Please enter the URL of test!','wp-autopost'); ?>");
	 return;
  }
  document.getElementById("saction3").value='autosetSettings';
  document.getElementById("myform3").submit();
}


jQuery(document).ready(function($){    
	
	$('#published_interval').change(function(){
	    var theValue = $(this).val();
		$("#published_interval_1").val(theValue);
	});

	$('#published_interval_1').change(function(){
	    var theValue = $(this).val();
		$("#published_interval").val(theValue);
	});


	$('#post_scheduled').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#post_scheduled_more").hide();
		}else{
           $("#post_scheduled_more").show();
		}
	});

	$('#use_publish_date').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#publish_date_more").hide();
		}else{
           $("#publish_date_more").show();
		}
	});
	
	$('.charset').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#ohterSet").hide();
		}else{
           $("#ohterSet").show();
		}
	});
    
	$('#download_img').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#img_insert_attachment_div").hide();
		}else{
           $("#img_insert_attachment_div").show();
		}
	});

	$('#set_featured_image').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#set_featured_image_div").hide();
		}else{
           $("#set_featured_image_div").show();
		}
	});

	$('#download_attach').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $(".download_attach_option").hide();
		}else{
           $(".download_attach_option").show();
		}
	});

	$('#auto_tags').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#tags_div").hide();
		}else{
           $("#tags_div").show();
		}
	});

	$('#auto_excerpt').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#auto_excerpt_div").hide();
		}else{
           $("#auto_excerpt_div").show();
		}
	});


	$('.source_type').change(function(){
	    var sSwitch = $(this).val();
        $("#source_type").val(sSwitch);
		if(sSwitch == 0 || sSwitch == 2){
           $("#urlArea1").show();
	       $("#urlArea2").hide();
		}else{
           $("#urlArea2").show();
	       $("#urlArea1").hide();;
		}
        
		if(sSwitch == 2){
          $(".TipRss").show();    
		  $(".a_match_type").attr("disabled",true);
		  $(".a_selector").attr("disabled",true);         
		  $(".title_match_type").attr("disabled",true);
          $(".title_selector").attr("disabled",true);
		  $(".content_match_type").attr("disabled",true);
          $(".content_selector").attr("disabled",true);
		  $(".rss_disable").attr("disabled",true); 
		}else{
		  $(".TipRss").hide();
          $(".a_match_type").attr("disabled",false);
		  $(".a_selector").attr("disabled",false);
		  $(".title_match_type").attr("disabled",false);
		  $(".title_selector").attr("disabled",false);
		  $(".content_match_type").attr("disabled",false);
          $(".content_selector").attr("disabled",false);         
		  $(".rss_disable").attr("disabled",false); 
		}

	});

	$('.a_match_type').change(function(){
	    var sSwitch = $(this).val();
        $("#a_match_type").val(sSwitch);
		if(sSwitch == 0){
           $("#a_match_0").show();
	       $("#a_match_1").hide();
		}else{
           $("#a_match_1").show();
	       $("#a_match_0").hide();;
		}
	});

	$('#post_filter').change(function(){
		if(document.getElementById("post_filter").checked==true){
          $("#post_filter_div").show();
		}else{
          $("#post_filter_div").hide();
		}
	});

	$('.title_match_type').change(function(){
	    var sSwitch = $(this).val();
        $("#title_match_type").val(sSwitch);
		if(sSwitch == 0){
           $("#title_match_0").show();
	       $("#title_match_1").hide();
		}else{
           $("#title_match_1").show();
	       $("#title_match_0").hide();
		}
	});

	$('#fecth_paged').change(function(){
		if(document.getElementById("fecth_paged").checked==true){
          $("#page").show();
		}else{
          $("#page").hide();
		}
	});

	$('#watermark_id').change(function(){
		var sSwitch = $(this).val();
		
		if(sSwitch>0){
          document.getElementById("set_watermark_image").checked=true;
		}else{
          document.getElementById("set_watermark_image").checked=false;
		}

	});



	$('.fecth_paged_type').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#page_match_0").show();
	       $("#page_match_1").hide();
		}else{
           $("#page_match_1").show();
	       $("#page_match_0").hide();;
		}
	});  

	$('#add_source_url').change(function(){
		if(document.getElementById("add_source_url").checked==true){
          $("#source_url_custom_fields").show();
		}else{
          $("#source_url_custom_fields").hide();
		}
	});

	$('#use_default_image').change(function(){
       var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#default_image_area").hide();
		}else{
           $("#default_image_area").show();
		}
	});


	$('.login_set_mode').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 1){
           $("#login_mode1").show();
	       $("#login_mode2").hide();
		}else{
           $("#login_mode2").show();
	       $("#login_mode1").hide();
		}
	});

	$('#use_rewriter').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#WordAi").hide();
		   $("#MicrosoftTranslator").hide();
		   $("#SpinRewriter").hide();
		   $("#baiduTranslator").hide();
		}else if(sSwitch == 1){
           $("#MicrosoftTranslator").show();

		   $("#WordAi").hide();
		   $("#SpinRewriter").hide();
		   $("#baiduTranslator").hide();
		}else if(sSwitch == 2){
           $("#WordAi").show();

		   $("#MicrosoftTranslator").hide();
		   $("#SpinRewriter").hide();
		   $("#baiduTranslator").hide();
		}else if(sSwitch == 3){
           $("#SpinRewriter").show();

		   $("#MicrosoftTranslator").hide();
		   $("#WordAi").hide();
		   $("#baiduTranslator").hide();
		}else if(sSwitch == 4){
           $("#baiduTranslator").show();

		   $("#MicrosoftTranslator").hide();
		   $("#WordAi").hide();
		   $("#SpinRewriter").hide();
		}
	});


    $('#use_trans').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#Translator1").hide();
		   $("#Translator2").hide();
		   $("#use_translator").hide();
		}else if(sSwitch == 1){
           $("#Translator2").hide();	   
		   $("#Translator1").show();
		   $("#use_translator").show();
		}else if(sSwitch == 2){
           $("#Translator1").hide();	   
		   $("#Translator2").show();
		   $("#use_translator").show();
		}
	});



	$('#copy_task_id').change(function(){
        var sSwitchValue = $(this).val();
		if(sSwitchValue!=0){
          var sSwitch = $(this).find("option:selected").text();
		  $("#new_config_name").val(sSwitch+'_copy');
		}else{
          $("#new_config_name").val('');
		}
    });

    $('#rewrite_origi_language').change(function(){
        var sSwitch = $(this).find("option:selected").text();
		$("#rewrite_origi_language_span").html(sSwitch);
    });

	$('#rewrite_trans_language').change(function(){
        var sSwitch = $(this).find("option:selected").text();
		$("#rewrite_trans_language_span").html(sSwitch);
    });

	$('#rewrite_origi_language_baidu').change(function(){
        var sSwitch = $(this).find("option:selected").text();
		$("#rewrite_origi_language_span_baidu").html(sSwitch);
    });

	$('#rewrite_trans_language_baidu').change(function(){
        var sSwitch = $(this).find("option:selected").text();
		$("#rewrite_trans_language_span_baidu").html(sSwitch);
    });

	$('#wordai_spinner').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 1){
           
		   $("#standard_quality").show();
		   $("#turing_quality").hide();
           
		   $("#standard_nonested").show();
		   $("#turing_nonested").hide();

		}else if(sSwitch == 2){
           $("#standard_quality").hide();
		   $("#turing_quality").show();

		   $("#standard_nonested").hide();
		   $("#turing_nonested").show();
		}
	});
	


	$('#post_method').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == -1){
           $("#translated_cat1").hide();
		   $("#translated_cat2").hide();
		   $("#translated_cat3").hide();
		}else if(sSwitch == -2){
           $("#translated_cat2").hide();
		   $("#translated_cat3").hide();
		   $("#translated_cat1").show();	   
		}else if(sSwitch == -3){
           $("#translated_cat1").hide();
		   $("#translated_cat3").hide();
		   $("#translated_cat2").show();	   
		}else{
		   $("#translated_cat1").hide();
		   $("#translated_cat2").hide();
           $("#translated_cat3").show();
		}
	
	});
	
    $("#autoset-button").click( function() {
        if($("#autosetenterurl_status").val()==0){
           $("#autosetenterurl_status").val(1);
           $("#autosetenterurl").show();
		}else{
           $("#autosetenterurl_status").val(0);
           $("#autosetenterurl").hide();
		} 
    });

    $(".default_imgs").click( function() {
       
	   var id = $(this).attr("id");
	   var lastValue = $("#img_"+id).val();
	   if(lastValue==0){
         $(this).removeClass("noselecedimg");
		 $(this).addClass("selectedimg");
         $("#img_"+id).val(id);
	   }else{
         $(this).removeClass("selectedimg");
		 $(this).addClass("noselecedimg");
         $("#img_"+id).val(0);
	   }

    });

});

function addMoreURLLevel(){
 var levelNum = parseInt(document.getElementById("levelNum").value);
 
 levelNum = levelNum+1;
 document.getElementById("levelNum").value = levelNum;

 var TRLastIndex = findObj("urlLevelTRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);
 
 var table = findObj("url_levels",document);
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "url_level_tr" + rowID;

 var newTD1=newTR.insertCell(0);
 
 newTD1.innerHTML = '<div class="apmatchtype"><p><input class="a_match_type_'+levelNum+'" type="radio" name="a_match_type_'+levelNum+'" value="0" checked="true" /><?php echo __('Use URL wildcards match pattern','wp-autopost'); ?>&nbsp;&nbsp;&nbsp;<input class="a_match_type_'+levelNum+'" type="radio" name="a_match_type_'+levelNum+'" value="1" /><?php echo __('Use CSS Selector','wp-autopost'); ?><a style="float:right;" class="apdelete" title="delete" href="javascript:;" onclick="deleteUrlLevel(\'url_level_tr'+rowID+'\')" ><?php echo __('Delete'); ?></a></p></div>'+
 '<div id="a_match_0_'+levelNum+'" ><?php echo __('Article URL','wp-autopost'); ?>:<input type="text" name="a_selector_0_'+levelNum+'" id="a_selector_0_'+levelNum+'" size="80" value=""></div>'+ 
 '<div id="a_match_1_'+levelNum+'" style="display:none;" ><?php echo __('The Article URLs CSS Selector','wp-autopost'); ?>:<input type="text" name="a_selector_1_'+levelNum+'" id="a_selector_1_'+levelNum+'" size="80" value=""></div>';

 TRLastIndex.value = (rowID + 1).toString() ; 
 
 jQuery(function($){
	$('.a_match_type_'+levelNum).change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#a_match_0_"+levelNum).show();
	       $("#a_match_1_"+levelNum).hide();
		}else{
           $("#a_match_1_"+levelNum).show();
	       $("#a_match_0_"+levelNum).hide();;
		}
	});
 });  
}

function deleteUrlLevel(rowid){
  var table = findObj("url_levels",document);
  var signItem = findObj(rowid,document);
  var rowIndex = signItem.rowIndex;
  table.deleteRow(rowIndex);
}




function addMoreMR(){
 
 var  s = '<?php echo $moreMRstr; ?>';
 
 var cmrNum = parseInt(document.getElementById("cmrNum").value);
 
 cmrNum = cmrNum+1;
 document.getElementById("cmrNum").value = cmrNum;

 var TRLastIndex = findObj("cmrTRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);
 
 var table = findObj("cmr",document);
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "cmr" + rowID;

 var newTD1=newTR.insertCell(0);

 s = s.replace(/{cmrNum}/g, cmrNum );
 s = s.replace(/{rowID}/g, rowID );
 
 /*
 newTD1.innerHTML = '<div class="apmatchtype"><p><input type="hidden" id="content_match_type_'+cmrNum+'" value="0" />'+
	   '<input class="content_match_type content_match_type_'+cmrNum+'" type="radio" name="content_match_type_'+cmrNum+'" value="0"  checked="true" /><?php echo __("Use CSS Selector","wp-autopost"); ?>&nbsp;&nbsp;&nbsp;'+
	   '<input class="content_match_type content_match_type_'+cmrNum+'" type="radio" name="content_match_type_'+cmrNum+'" value="1" /><?php echo __("Use Wildcards Match Pattern","wp-autopost"); ?>&nbsp;&nbsp;&nbsp;'+
	   '<input type="checkbox" name="outer_'+cmrNum+'" /> <?php echo __("Contain The Outer HTML Text","wp-autopost"); ?><a style="float:right;" class="apdelete" title="delete" href="javascript:;" onclick="deleteRowCmr(\'cmr'+rowID+'\')" ><?php echo __('Delete'); ?></a></p></div>'+ 
	   '<span id="content_match_0_'+cmrNum+'" >'+
       '<?php echo __("CSS Selector","wp-autopost"); ?>: <input type="text" name="content_selector_0_'+cmrNum+'" id="content_selector_0_'+cmrNum+'" class="content_selector" size="40" value="">'+     
       ' <span class="clickBold" id="index_'+cmrNum+'"><?php echo __("Index","wp-autopost"); ?></span><span id="index_num_'+cmrNum+'" style="display:none;">: <input type="text" name="index_'+cmrNum+'" size="1" value="0" /><input type="hidden" id="index_show_'+cmrNum+'" value="0" /></span>'+
       ' </span>'+
	   '<span id="content_match_1_'+cmrNum+'"  style="display:none;" ><table><tr><td><?php echo __('Starting Unique HTML','wp-autopost'); ?>:</td><td><input type="text" name="content_selector_1_start_'+cmrNum+'" id="content_selector_1_start_'+cmrNum+'" class="content_selector" size="40" value="" /></td><tr><td><?php echo __('Ending Unique HTML','wp-autopost'); ?>:</td><td><input type="text" name="content_selector_1_end_'+cmrNum+'" id="content_selector_1_end_'+cmrNum+'" class="content_selector" size="40" value="" /></td></tr></table></span>'+
	   '<p><label><?php echo __("To: ","wp-autopost"); ?></label> <select name="objective_'+cmrNum+'" id="objective_'+cmrNum+'" ><option value="0" ><?php echo __('Post Content','wp-autopost'); ?></option><option value="2" ><?php echo __('Post Excerpt','wp-autopost'); ?></option><option value="3" ><?php echo __('Post Tags','wp-autopost'); ?></option><option value="5" ><?php echo __('Categories'); ?></option><option value="4" ><?php echo __('Featured Image'); ?></option><option value="1" ><?php echo __('Post Date','wp-autopost'); ?></option><option value="-1" ><?php echo __('Custom Fields'); ?></option></select>'+	   
       '<span><input id="objective_customfields_'+cmrNum+'" name="objective_customfields_'+cmrNum+'" style="display:none;" type="text" value="" /></span></p>';
 */
  newTD1.innerHTML = s;


 TRLastIndex.value = (rowID + 1).toString() ; 
 jQuery(function($){
	$('.content_match_type_'+cmrNum).change(function(){
	    var sSwitch = $(this).val();
        $("#content_match_type_"+cmrNum).val(sSwitch);
		if(sSwitch == 0){
           $("#content_match_0_"+cmrNum).show();
	       $("#content_match_1_"+cmrNum).hide();
		}else{
           $("#content_match_1_"+cmrNum).show();
	       $("#content_match_0_"+cmrNum).hide();;
		}
	});
	
	$('#objective_'+cmrNum).change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == -1){
           $("#objective_customfields_"+cmrNum).show();
		}else{
           $("#objective_customfields_"+cmrNum).hide();
		}
    });

    $('#index_'+cmrNum).click(function(){
	   var s = $('#index_show_'+cmrNum).val(); 
	   if(s==0){
	     $("#index_num_"+cmrNum).show();
		 $('#index_show_'+cmrNum).val('1');
	   }else{
         $("#index_num_"+cmrNum).hide();
		 $('#index_show_'+cmrNum).val('0');
	   }
    });

 
 });  
}

function deleteRowCmr(rowid){
  var table = findObj("cmr",document);
  var signItem = findObj(rowid,document);
  var rowIndex = signItem.rowIndex;
  table.deleteRow(rowIndex);
}


function AddRowType14(){
 var TRLastIndex = findObj("Type14TRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);

 var table = findObj("OptionType14",document);
 
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "type14" + rowID;
 

 var newTD1=newTR.insertCell(0);
 newTD1.innerHTML = '<input type="text" name="type14_para1[]" value="" />';

 var newTD1=newTR.insertCell(1);
 newTD1.innerHTML = '<input type="text" name="type14_para2[]" size="1" value="0" />';
 
 var newTD2=newTR.insertCell(2);
 newTD2.innerHTML = '<input type="text" name="type14_para3[]" size="8" value="" />';

 var newTD1=newTR.insertCell(3);
 newTD1.innerHTML = '<input type="text" name="type14_para4[]" size="60" value="" />';

 var newTD3=newTR.insertCell(4);
 newTD3.innerHTML = '<input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType14(\'type14'+rowID+'\')"/>';

 
 TRLastIndex.value = (rowID + 1).toString() ;
}

function deleteRowType14(rowid){
 var table = findObj("OptionType14",document);
 var signItem = findObj(rowid,document);
 var rowIndex = signItem.rowIndex;
 table.deleteRow(rowIndex);
}

function SaveOption14(){
  document.getElementById("myform14").submit();
}


function AddRowType6(){
 var TRLastIndex = findObj("Type6TRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);

 var table = findObj("OptionType6",document);
 
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "type6" + rowID; 

 var newTD1=newTR.insertCell(0);
 newTD1.innerHTML = 
	 '<?php echo __("HTML Element (Use CSS Selector)","wp-autopost"); ?>:<input type="text" name="type6_para1[]" value="" >&nbsp;&nbsp;&nbsp;'+
     '<?php echo __("Index","wp-autopost"); ?>:<input type="text" name="type6_para2[]" value="1" size="2">&nbsp;&nbsp;&nbsp;'+	  
	 '<select name="type6_para3[]" ><option value="0"><?php echo __("Outer - Behind","wp-autopost"); ?></option><option value="1"><?php echo __("Outer - Front","wp-autopost"); ?></option><option value="2"><?php echo __("Inner - Behind","wp-autopost"); ?></option><option value="3"><?php echo __("Inner - Front","wp-autopost"); ?></option></select>&nbsp;&nbsp;&nbsp;'+	  
	 '<table><tr><td><?php echo __("Content","wp-autopost"); ?><br/>(<i>HTML</i>):</td><td><textarea name="type6_para4[]" id="type6_para4[]" cols="102" rows="3"></textarea></td><td><input type="button" class="button"  value="<?php echo __("Delete"); ?>"  onclick="deleteRowType6(\'type6'+rowID+'\')"/></td></tr></table>';
 
 TRLastIndex.value = (rowID + 1).toString() ;
}

function deleteRowType6(rowid){
 var table = findObj("OptionType6",document);
 var signItem = findObj(rowid,document);
 var rowIndex = signItem.rowIndex;
 table.deleteRow(rowIndex);
}

function SaveOption6(){
  document.getElementById("myform10").submit();
}


function DeleteCustomField(key){
  document.getElementById("custom_field_key").value=key;
  document.getElementById("saction12").value='DeleteCustomField'; 
  document.getElementById("myform12").submit();
}
function newCustomField(){
  document.getElementById("saction12").value='newCustomField';
  document.getElementById("myform12").submit();
}

function updateAll(){
  document.getElementById("saction").value='updateAll';
  document.getElementById("myform").submit();
}
function changePerPage(){
  document.getElementById("saction").value='changePerPage';
  document.getElementById("myform").submit();
}
function testFetch(){
  document.getElementById("saction").value='testFetch';
  document.getElementById("myform").submit();
}

function showHTML(){
 var s=document.getElementById("ap_content_s").value;
  if(s==0){
    jQuery("#ap_content").hide();
	jQuery("#ap_content_html").show();
	document.getElementById("ap_content_s").value=1;
  }else{
	jQuery("#ap_content_html").hide();
	jQuery("#ap_content").show();
	document.getElementById("ap_content_s").value=0;  
  }
}

function Delete(id){
  if(confirm("Confirm Delete?")){ 
	 document.getElementById("saction").value='deleteSubmit';
	 document.getElementById("configId").value=id;
     document.getElementById("myform").submit();
  }else return false; 
}


function AddLoginPara(){
 var TRLastIndex = findObj("login_para_tableTRLastIndex",document);
 var rowID = parseInt(TRLastIndex.value);

 var table = findObj("login_para_table",document);
 
 var newTR = table.insertRow(table.rows.length);
 newTR.id = "login_para_table_tr" + rowID; 

  var newTD1=newTR.insertCell(0);
 newTD1.innerHTML = '<input type="text" name="loginParaName[]" value=""  />';
 
 var newTD2=newTR.insertCell(1);
 newTD2.innerHTML = '<input type="text" name="loginParaValue[]" value=""  />';

 var newTD3=newTR.insertCell(2);
 newTD3.innerHTML = '<input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteLoginPara(\'login_para_table_tr'+rowID+'\')"/>';
 
 TRLastIndex.value = (rowID + 1).toString() ;

}

function deleteLoginPara(rowid){
 var table = findObj("login_para_table",document);
 var signItem = findObj(rowid,document);
 var rowIndex = signItem.rowIndex;
 table.deleteRow(rowIndex);
}

function getLoginPara(){
  var login_url = document.getElementById("login_url").value;
  if(login_url==''){
    return;
  }
  
  jQuery.ajax({
     type: "GET",
     url: "<?php echo get_home_url(); ?>",
	 data: "login_url="+login_url,
	 beforeSend: function(data){
		document.getElementById("login_para").innerHTML='<div style="text-align:center;"><p><img src="<?php echo plugins_url('/images/loading.gif', __FILE__ ); ?>" /></p></div>';
	 }, 
	 success: function(r_msg){     
		document.getElementById("login_para").innerHTML=r_msg;
	 }
 
  });

}

function autoSetURL(url){ 
  document.getElementById("targetURL").value=url;
  document.getElementById("autoSetForm").submit();
}

function autoSetTitle(selector,selector_index){
  document.getElementById("selector").value=selector;
  document.getElementById("selector_index").value=selector_index;
  document.getElementById("autoSetForm").submit();
}

function uploadDefaultImg(){
  document.getElementById("saction17").value='uploadDefaultImg';
  document.getElementById("myform17").submit();
}

function deleteDefaultImg(id){
  document.getElementById("saction17").value='deleteDefaultImg';
  document.getElementById("attach_id").value=id;
  document.getElementById("myform17").submit();
}

function save17(){
  document.getElementById("saction17").value='save17';
  document.getElementById("myform17").submit();
}

</script>

