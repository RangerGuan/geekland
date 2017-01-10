<?php
 global $t_ap_watermark;
 
 $id = null;

 if(isset($_POST['saction'])){
   $size = $_POST['size'];
   if(!preg_match("/^\+?[1-9][0-9]*$/",$size))$size=16;
   
   $transparency = $_POST['transparency'];
   if(!preg_match("/^\d+$/",$transparency))$transparency=80;
   if($transparency>100)$transparency=100;
   if($transparency<0)$transparency=0;

   $jpeg_quality = $_POST['jpeg_quality'];
   if(!preg_match("/^\+?[1-9][0-9]*$/",$jpeg_quality))$jpeg_quality=90;
   if($jpeg_quality>100)$jpeg_quality=100;

   $xadjustment = $_POST['x-adjustment'];
   if(!preg_match("/^[-]?\d+$/",$xadjustment))$xadjustment=0;
   $yadjustment = $_POST['y-adjustment'];
   if(!preg_match("/^[-]?\d+$/",$yadjustment))$yadjustment=0;
   $min_width = $_POST['min_width'];
   if(!preg_match("/^\+?[1-9][0-9]*$/",$min_width))$min_width=300;
   $min_height = $_POST['min_height'];
   if(!preg_match("/^\+?[1-9][0-9]*$/",$min_height))$min_height=300;


   $wm_text = (trim($_POST['text'])=='')?get_bloginfo('url'):$_POST['text'];
   $wm_color = (trim($_POST['color'])=='')?'#ffffff':$_POST['color'];
   $wm_name = (trim($_POST['wm_name'])=='')?'temp_name':$_POST['wm_name'];
 }

 if(@$_POST['saction'] == 'updateOption' || @$_POST['saction'] == 'preview'){
   
 
   if($_POST['wm_id']=='new'){ // 插入
     
	 $upload_image = dirname(__FILE__).'/watermark/uploads/watermark.png';
	 $upload_image_url = plugins_url('/watermark/uploads/watermark.png', __FILE__ );

	 $wpdb->query($wpdb->prepare("insert into $t_ap_watermark (name,wm_type,wm_position,wm_font,wm_text,wm_size,wm_color,x_adjustment,y_adjustment,transparency,upload_image,upload_image_url,min_width,min_height,jpeg_quality) values (%s,%d,%d,%s,%s,%d,%s,%d,%d,%d,%s,%s,%d,%d,%d)",$wm_name,$_POST['type'],$_POST['position'],stripslashes($_POST['font']),$wm_text,$size,$wm_color,$xadjustment,$yadjustment,$transparency,$upload_image,$upload_image_url,$min_width,$min_height,$jpeg_quality));
     

	 if($_POST['saction'] == 'preview')$id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
	   
   }elseif($_POST['wm_id']>0){ // 更新

	  $wpdb->query($wpdb->prepare("update $t_ap_watermark set 
	   name = %s,
	   wm_type = %d,
	   wm_position = %d,
	   wm_font = %s,
	   wm_text = %s,
	   wm_size = %d,
	   wm_color = %s,
	   x_adjustment = %d,
	   y_adjustment = %d,
	   transparency = %d,
	   min_width = %d,
	   min_height = %d,
	   jpeg_quality = %d where id = %d",
	   $wm_name,$_POST['type'],$_POST['position'],stripslashes($_POST['font']),$wm_text,$size,$wm_color,$xadjustment,$yadjustment,$transparency,$min_width,$min_height,$jpeg_quality,$_POST['wm_id']));

       if($_POST['saction'] == 'preview')$id = $_POST['wm_id'];
   }

   if($_POST['saction'] == 'preview'){
     $wm = $wpdb->get_row('SELECT * FROM '.$t_ap_watermark.' where id = '.$id);
     WP_Autopost_Watermark::genPreviewWaterMark($wm);
     $showPreview=true;
   }

 }


 if(isset($_POST['saction']) && $_POST['saction'] == 'uploadImg'){
   
   $wm_dir = dirname(__FILE__).'/watermark/uploads';
   $wm_url = plugins_url('/watermark/uploads', __FILE__ );
   $fileinfo = $_FILES['watermark-image'];
   $file = $fileinfo['tmp_name'];
   $des = $wm_dir.'/'.$fileinfo['name'];
   $res = move_uploaded_file( $file, $des);
   if( $res ){
     if($_POST['wm_id']=='new'){ // 插入
	    
		$upload_image = $des;
	    $upload_image_url = $wm_url.'/'.$fileinfo['name'];

		$wpdb->query($wpdb->prepare("insert into $t_ap_watermark (name,wm_type,wm_position,wm_font,wm_text,wm_size,wm_color,x_adjustment,y_adjustment,transparency,upload_image,upload_image_url,min_width,min_height,jpeg_quality) values (%s,%d,%d,%s,%s,%d,%s,%d,%d,%d,%s,%s,%d,%d,%d)",$wm_name,1,$_POST['position'],stripslashes($_POST['font']),$wm_text,$size,$wm_color,$xadjustment,$yadjustment,$transparency,$upload_image,$upload_image_url,$min_width,$min_height,$jpeg_quality));

		$id = $wpdb->get_var("SELECT LAST_INSERT_ID()");

	 }else{ // 更新

	   $upload_image = $des;
	   $upload_image_url = $wm_url.'/'.$fileinfo['name'];
        
	   $wpdb->query($wpdb->prepare("update $t_ap_watermark set 
	   name = %s,
	   wm_type = %d,
	   wm_position = %d,
	   wm_font = %s,
	   wm_text = %s,
	   wm_size = %d,
	   wm_color = %s,
	   x_adjustment = %d,
	   y_adjustment = %d,
	   transparency = %d,
	   min_width = %d,
	   min_height = %d,
	   jpeg_quality = %d,
	   upload_image = %s,
       upload_image_url = %s 
	   where id = %d",
	   $wm_name,$_POST['type'],$_POST['position'],stripslashes($_POST['font']),$wm_text,$size,$wm_color,$xadjustment,$yadjustment,$transparency,$min_width,$min_height,$jpeg_quality,$upload_image,$upload_image_url,$_POST['wm_id']));

       $id = $_POST['wm_id'];
	 
	 }
   
   }// end  if( $res ){

 }
   


?>

<script type="text/javascript">
jQuery(document).ready(function($){

	$('#type-switch input').change(function(){
		var sSwitch = $(this).val();
		if( sSwitch == 0 ){
			$("#image_type").hide();
			$("#text_type").show();
		}
		else{
			$("#image_type").show();
			$("#text_type").hide();
		}
	});

});
function updateOption(){
  document.getElementById("saction").value='updateOption';
  document.getElementById("myform").action='admin.php?page=wp-autopost-pro/wp-autopost-watermark.php';
  document.getElementById("myform").submit();
}
function uploadImg(){
  document.getElementById("saction").value='uploadImg';
  document.getElementById("myform").action='admin.php?page=wp-autopost-pro/wp-autopost-watermark.php';
  document.getElementById("myform").submit();
}
function preview(){
  document.getElementById("saction").value='preview';
  document.getElementById("myform").action='admin.php?page=wp-autopost-pro/wp-autopost-watermark.php#preview_pic';
  document.getElementById("myform").submit();
}
</script>

<div class="wrap">

<?php
 
 if(isset($_GET['id']))$id = $_GET['id'];
 if($id==''||$id==null ):
?>
 
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2><?php echo __('Watermark Options','wp-autopost'); ?> <a href="admin.php?page=wp-autopost-pro/wp-autopost-watermark.php&id=new" class="add-new-h2"><?php echo __('Add New','wp-autopost'); ?></a> </h2>
   
 <?php

   if(isset($_GET['del'])){
     $count = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_watermark);
	 if($count==1){
        echo '<div class="error fade"><p>'.__('Please keep at least one.','wp-autopost').'</p></div>';
	 }else{
       $wpdb->query('DELETE FROM '.$t_ap_watermark.' WHERE id = '.$_GET['del']);  
	 }

   }

   $wms = $wpdb->get_results('SELECT * FROM '.$t_ap_watermark.' order by id');
 ?>
   <p><?php _e( 'Tips: You can create different watermarks applied to different tasks.', 'wp-autopost' );?></p>
   <table class="widefat tablehover plugins"  style="margin-top:4px"> 
     <thead>
      <tr>
        <th scope="col" width="250" style="text-align:center;"><?php _e( 'Watermark Name', 'wp-autopost' );?></th>
        <th scope="col" width="150" style="text-align:center;"><?php _e( 'Watermark Type', 'wp-autopost' );?></th>
		<th scope="col" style="text-align:center;"><?php echo __('Preview'); ?></th>
      </tr>
     </thead>
	 <tbody id="the-list">
<?php if($wms!=null)foreach($wms as $wm){ ?>
      <tr style="text-align:center" >
		<td style="vertical-align: middle; text-align:center;">
		   <strong><?php echo $wm->name; ?></strong>
		   <div class="row-actions-visible">
              <a href="admin.php?page=wp-autopost-pro/wp-autopost-watermark.php&id=<?php echo $wm->id; ?>"><?php echo __('Setting','wp-autopost'); ?></a> | <span class="trash"><a class="submitdelete delete" title="delete" href="admin.php?page=wp-autopost-pro/wp-autopost-watermark.php&del=<?php echo $wm->id; ?>" ><?php echo __('Delete'); ?></a></span> 
		   </div>
		</td>
        <td style="vertical-align: middle; text-align:center;">
		  <?php if($wm->wm_type==0) _e( 'Text', 'wp-autopost' ); else _e( 'Image', 'wp-autopost' ); ?>	
		</td>
		<td style="text-align:center;"> 
		  <div class="watermark_preview_pic" >
		   <?php 
		   if($wm->wm_type==0){ ?>

		     <?php
			    
                 $r = hexdec( substr( $wm->wm_color, 1, 2 ) );
                 $g = hexdec( substr( $wm->wm_color, 3, 2 ) );
                 $b = hexdec( substr( $wm->wm_color, 5, 2 ) );

			    $tempUrl = plugins_url('wp-autopost-gentextimg.php', __FILE__ ).'?text='.urlencode($wm->wm_text).'&size='.urlencode($wm->wm_size).'&font='.urlencode($wm->wm_font).'&r='.$r.'&g='.$g.'&b='.$b;
			  ?>
			  
			  <img src="<?php echo $tempUrl; ?>" alt="" />  

			  
			   
    <?php  }else{ ?>
			  <img src="<?php echo $wm->upload_image_url;  ?>" alt="" />   
    <?php  }?>		  
		  </div>	
		</td>
	  </tr>
 <?php } ?>
	 </tbody>
   </table>
  
 
<?php
 else: 
?>  
 <?php
   if($id >0 ){
     $wm = $wpdb->get_row('SELECT * FROM '.$t_ap_watermark.' where id = '.$id);
	 $name = $wm->name;
	 $wm_type = $wm->wm_type;
	 $wm_position = $wm->wm_position;
	 $wm_font = $wm->wm_font;

	 $wm_text = $wm->wm_text;
	 $wm_size = $wm->wm_size;
	 $wm_color = $wm->wm_color;
	 $x_adjustment = $wm->x_adjustment;
	 $y_adjustment = $wm->y_adjustment;
	 $transparency = $wm->transparency;
	 $upload_image = $wm->upload_image;
	 $upload_image_url = $wm->upload_image_url;

	 $min_width = $wm->min_width;
	 $min_height = $wm->min_height;
	 $jpeg_quality = $wm->jpeg_quality;
   }else{
     $name = '';
	 $wm_type = 0;
	 $wm_position = 9;
	 $wm_font = '';

	 $wm_text = get_bloginfo('url');
	 $wm_size = 16;
	 $wm_color = '#ffffff';
	 $x_adjustment = 0;
	 $y_adjustment =0;
	 $transparency = 80;
	 $upload_image = dirname(__FILE__).'/watermark/uploads/watermark.png';
	 $upload_image_url = plugins_url('/watermark/uploads/watermark.png', __FILE__ );

	 $min_width = 150;
	 $min_height = 150;
	 $jpeg_quality = 90;

   }
 ?>

 <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2><?php echo __('Watermark Options','wp-autopost'); ?> <a href="admin.php?page=wp-autopost-pro/wp-autopost-watermark.php" class="add-new-h2"><?php echo __('Return','wp-autopost'); ?></a> </h2>

  <form action="" method="post" id="myform" enctype="multipart/form-data">
  <input type="hidden" name="wm_id" id="wm_id" value="<?php echo $id; ?>" />
  <input type="hidden" name="saction" id="saction" value="" />
  
  <table class="form-table">
   <tr>
      <th scope="row"><label><?php _e( 'Watermark Name', 'wp-autopost' );?>:</label></th>
	  <td>
	     <input type="text" name="wm_name" value="<?php echo $name; ?>" size="60" />
	  </td>
   </tr>
  </table>

  <table class="form-table">
   <tr>
      <th scope="row"><label><?php _e( 'Watermark Type', 'wp-autopost' );?>:</label></th>
	  <td>
	   <div id="type-switch">
	    <input type="radio" name="type" value="0" <?php checked( '0', $wm_type ); if( empty( $wm_type ) ) echo 'checked'; ?> /><?php _e( 'Text', 'wp-autopost' );?>
	    &nbsp;&nbsp;&nbsp;
	    <input type="radio" name="type" value="1" <?php checked( '1', $wm_type ); ?> /><?php _e( 'Image', 'wp-autopost' );?>
	   </div>
	  </td>
   </tr>
  </table>
  
  <table id="text_type" class="form-table" <?php if($wm_type==1) echo 'style="display:none;"'; ?> >
   <tr>
      <th scope="row"><label><?php _e( 'Text', 'wp-autopost' );?>:</label></th>
	  <td>
	   <input type="text" name="text" value="<?php echo $wm_text; ?>" size="60" />
	  </td>
   </tr>
   <tr>
      <th scope="row"><label><?php _e( 'Fonts', 'wp-autopost' );?>:</label></th>
	  <td>
	    <select id="fonts" name="font">
    <?php 
		 $default_fonts = WP_Autopost_Watermark::get_fonts(); 
		 foreach( $default_fonts as $key=>$default_font ){ ?>
          <option value="<?php echo $default_font ?>" <?php selected( $default_font, $wm_font ); ?>><?php echo $key; ?></option>
     <?php } ?>
		</select>
		<br/>
        <span class="gray">
		<?php $fontsDirectory = dirname(__FILE__).'/watermark/fonts/'; ?>
	       <?php echo __( 'you can upload the <b>xxx.ttf</b> font files to the ','wp-autopost').'<i><b>'.$fontsDirectory.'</b></i>'.__(' directory', 'wp-autopost' );?>
           <?php if(get_bloginfo('language')=='zh-CN'): 
                   echo '<br/>(若水印文本为中文，需要使用中文字体)';
		         endif; ?>
		</span>
	  </td>
   </tr>
   <tr>
      <th scope="row"><label><?php _e( 'Fonts Size', 'wp-autopost' );?>:</label></th>
	  <td>
	   <input type="text" name="size" value="<?php echo $wm_size; ?>" /> px
	  </td>
   </tr>
   <tr>
      <th scope="row"><label><?php _e( 'Fonts Color', 'wp-autopost' );?>:</label></th>
	  <td>
	   <input type="text" name="color" value="<?php echo $wm_color; ?>" />&nbsp;<span class="gray">( <?php _e( 'eg. #ffffff', 'wp-autopost' );?> )</span>
	  </td>
   </tr>
  </table>


  <table id="image_type" class="form-table" <?php if($wm_type==0||empty( $wm_type) ) echo 'style="display:none;"'; ?>>
   <tr>
      <th scope="row"><label><?php _e( 'Upload Image', 'wp-autopost' );?>:</label></th>
	  <td>
	   <input type="file" class="button" name="watermark-image" id="watermark-image" size="60" />
	   <input type="button" class="button" value="<?php _e( 'Upload Image', 'wp-autopost' );?>"  onclick="uploadImg()"/>
	  </td>
   </tr>
   <tr>
      <th></th>
	  <td>
         <?php if($upload_image_url!=''){ ?>
		 <div class="watermark_preview_pic" ><img src="<?php echo $upload_image_url;  ?>" alt="" /></div>
		 <?php } ?>
	  </td>
   </tr>
  </table>

  <table class="form-table">
   <tr>
      <th scope="row"><label><?php _e( 'Transparency', 'wp-autopost' );?>:</label></th>
	  <td>
	   <input type="text" name="transparency" value="<?php echo $transparency; ?>" />&nbsp;<span class="gray">( <?php _e( 'from 0 to 100', 'wp-autopost' );?> )</span>
	  </td>
   </tr>
   <tr>
      <th scope="row"><label><?php _e( 'Jpeg quality', 'wp-autopost' );?>:</label></th>
	  <td>
	   <input type="text" name="jpeg_quality" value="<?php echo $jpeg_quality; ?>" />&nbsp;<span class="gray"><?php _e( 'ranges from 1 (worst quality, smaller file) to 100 (best quality, biggest file)', 'wp-autopost' );?></span>
	  </td>
   </tr>
   <tr>
      <th scope="row"><label><?php _e( 'Watermark Position', 'wp-autopost' );?>:</label></th>
	  <td>
	    <table border="1" cellpadding="5" bordercolor="#ccc" id="dw-position">
		<tr>
			<td>&nbsp;&nbsp;<input type="radio" name="position" value="1" <?php checked( '1', $wm_position );?>/></td>
			<td>&nbsp;&nbsp;<input type="radio" name="position" value="2" <?php checked( '2', $wm_position );?>/></td>
			<td>&nbsp;&nbsp;<input type="radio" name="position" value="3" <?php checked( '3', $wm_position );?>/></td>
		</tr>
		<tr>
			<td>&nbsp;&nbsp;<input type="radio" name="position" value="4" <?php checked( '4', $wm_position );?>/></td>
			<td>&nbsp;&nbsp;<input type="radio" name="position" value="5" <?php checked( '5', $wm_position );?>/></td>
			<td>&nbsp;&nbsp;<input type="radio" name="position" value="6" <?php checked( '6', $wm_position );?>/></td>
		</tr>
		<tr>
			<td>&nbsp;&nbsp;<input type="radio" name="position" value="7" <?php checked( '7', $wm_position );?>/></td>
			<td>&nbsp;&nbsp;<input type="radio" name="position" value="8" <?php checked( '8', $wm_position );?>/></td>
			<td>&nbsp;&nbsp;<input type="radio" name="position" value="9" <?php checked( '9', $wm_position ); if( empty($wm_position) ) echo 'checked'; ?>/></td>
		</tr>					
	    </table>
	  </td>
   </tr>
   <tr>
      <th scope="row"><label><?php _e( 'Level Adjustment', 'wp-autopost' );?>:</label></th>
	  <td>
	   <input type="text" name="x-adjustment" value="<?php echo $x_adjustment; ?>" /> px&nbsp;<span class="gray">(<?php _e( 'eg. 5 or -5', 'wp-autopost' );?>)</span>
	  </td>
   </tr>
   <tr>
      <th scope="row"><label><?php _e( 'Vertical Adjustment', 'wp-autopost' );?>:</label></th>
	  <td>
	   <input type="text" name="y-adjustment" value="<?php echo $y_adjustment; ?>" /> px&nbsp;<span class="gray">(<?php _e( 'eg. 5 or -5', 'wp-autopost' );?>)</span>
	  </td>
   </tr>
   <tr>
      <th scope="row"><label><?php _e( 'Can add a watermark image size', 'wp-autopost' );?>:</label></th>
	  <td>
	    <?php _e( 'Min Width', 'wp-autopost' );?>:<input type="text" name="min_width" value="<?php echo $min_width; ?>" />
		&nbsp;&nbsp;
		<?php _e( 'Min Height', 'wp-autopost' );?>:<input type="text" name="min_height" value="<?php echo $min_height; ?>" />
	  </td>
   </tr>
  </table>
  
  <table class="form-table">
   <tr>
    <td> 
	  <div id="preview_pic" class="watermark_preview_pic" ><img width="610" height="377" src="<?php  echo plugins_url('/watermark/preview_img.jpg', __FILE__ ).'?random='.time(); ?>" alt="" /></div>
	</td>
   </tr>
   <tr>
     <td>
	    <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>" onclick="updateOption()"/>&nbsp;
        <input type="button" class="button"   value="<?php echo __('Preview'); ?>"  onclick="preview()" />
	 </td>
   </tr>
  </table>
 </form>
<?php

 endif; //if($id==''||$id==null):

?>


</div>