<?php
set_time_limit(0);
$upyunOptions = get_option('wp-autopost-upyun-options');

if(isset($_POST['save_setting'])&&$_POST['save_setting']!=''){
  $upyunOptions['domain'] =  $_POST['domain'];
  $upyunOptions['bucket'] =  $_POST['bucket'];
  $upyunOptions['operator_user_name'] =  $_POST['operator_user_name'];
  $upyunOptions['operator_password'] =  $_POST['operator_password'];  
  if($_POST['not_save']=='on'){
    $upyunOptions['not_save'] = 0 ;
  }else{
    $upyunOptions['not_save'] = 1 ;
  }
  update_option( 'wp-autopost-upyun-options', $upyunOptions);
  $upyunOptions = get_option('wp-autopost-upyun-options');
}

$tab = @$_GET['tab'];
if($tab==null)$tab='default';
?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  
  <h2 class="nav-tab-wrapper">
   <a href="admin.php?page=wp-autopost-pro/wp-autopost-upyun.php" class="nav-tab <?php if($tab=='default') echo 'nav-tab-active';?>"><?php _e( 'Upyun Options', 'wp-autopost' );?></a>
   <a href="admin.php?page=wp-autopost-pro/wp-autopost-upyun.php&tab=uploaded_images" class="nav-tab <?php if($tab=='uploaded_images') echo 'nav-tab-active';?>"><?php _e( 'Uploaded Images', 'wp-autopost' );?></a>
  </h2>
<?php 
switch ($tab){
 case 'default':
?>
 
 <h3><?php _e( 'Automatically upload images to <a href="http://www.upyun.com" target="_blank" >upyun</a> (No Free Storage), save bandwidth and space, speed up your website.', 'wp-autopost' );?></h3>
  
<form action="admin.php?page=wp-autopost-pro/wp-autopost-upyun.php" method="post">
<h3><?php _e( 'Upyun Account', 'wp-autopost' );?></h3>
<table class="form-table">
  <tr>
    <th scope="row"><label><?php _e( 'The Domain of Upyun Bucket', 'wp-autopost' );?>:</label></th>
	<td> 
	   <input type="text" name="domain" value="<?php echo $upyunOptions['domain']; ?>" size="60"/>
	   <span class="gray">xxxxx.b0.upaiyun.com</span>
	</td>
  </tr>
  <tr>
    <th scope="row"><label><?php _e( 'Bucket', 'wp-autopost' );?>:</label></th>
	<td> 
	   <input type="text" name="bucket" value="<?php  echo $upyunOptions['bucket']; ?>" size="60"/>
	</td>
  </tr>
  <tr>
    <th scope="row"><label><?php _e( 'Operator User Name', 'wp-autopost' );?>:</label></th>
	<td> 
	   <input type="text" name="operator_user_name" value="<?php  echo $upyunOptions['operator_user_name']; ?>" size="60"/>
	</td>
  </tr>
  <tr>
    <th scope="row"><label><?php _e( 'Operator Password', 'wp-autopost' );?>:</label></th>
	<td> 
	   <input type="text" name="operator_password" value="<?php  echo $upyunOptions['operator_password']; ?>" size="60"/>
	</td>
  </tr>

  <tr>
     <th scope="row"></th>
	 <td>
	   <p><?php _e( 'If the settings are correct, will upload a image to your Upyun Bucket and appear here.', 'wp-autopost' );?></p>
  <?php
       if($upyunOptions['domain']!=''&&$upyunOptions['bucket']!=''&&$upyunOptions['operator_user_name']!=''&&$upyunOptions['operator_password']!=''):
	     
         $upyun = new apUpYun($upyunOptions['bucket'], $upyunOptions['operator_user_name'], $upyunOptions['operator_password']);
		 
		 try {
		   $upload_image = dirname(__FILE__).'/images/watermark.png';
		   $fh = fopen($upload_image, 'rb');
           $rsp = $upyun->writeFile('/upload-test/wp-autopost-logo.png', $fh, True);   // 上传图片，自动创建目录
           fclose($fh);
           
		   $baseUrl = $upyun->makeBaseUrl($upyunOptions['domain'], '/upload-test/wp-autopost-logo.png');
		   echo '<div class="view_img"><img src='.$baseUrl.' /></div>';
		   echo __('The image URL','wp-autopost').' : <a href="'.$baseUrl.'" target="_blank">'.$baseUrl.'</a>';

           $upyunOptions['set_ok'] =  1;
           update_option( 'wp-autopost-upyun-options', $upyunOptions);
		 }catch(Exception $e) {
           echo '<div style="background-color:#ffebe8;border-color:#cc0000;border-style:solid;border-width:1px;padding:10px;">';
		   echo $e->getCode();
		   echo ' : ';
           echo $e->getMessage();
		   echo '</div>';
		   $upyunOptions['set_ok'] =  0;
           update_option( 'wp-autopost-upyun-options', $upyunOptions);
         }
		 
	   endif;
   ?>
	 </td>
  </tr>

  <tr>
    <td colspan="2">
	<input type="checkbox" name="not_save" <?php if($upyunOptions['not_save']!=1) echo 'checked="true"'; ?> /> <?php _e( 'Save a copy on local server', 'wp-autopost' );?></td>
  </tr>
</table>
<p class="submit"><input type="submit" name="save_setting" class="button-primary" value="<?php echo __('Save Changes'); ?>" ></p>
</form>

  

<?php
 break; // end  case 'default':
 case 'uploaded_images':
?> 
<?php
 global $t_ap_qiniu_img;
 $mode = @$_GET['mode'];
 $size=@$_POST['size'];
 if($size==null){
   $size=@$_GET['size'];
 }

 function bulidUpyunUrl($domain,$key,$size,$title=''){
	$imgurl = "http://$domain$key";	
	return '<a href="'.$imgurl.'" target="_blank">'.$imgurl.'</a>';
	
 }

 $PostTotal = $wpdb->get_var('SELECT count(distinct id) FROM '.$t_ap_upyun_img);
 $ImgTotal = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_upyun_img);

?>

<ul class='subsubsub'>
	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-upyun.php&tab=uploaded_images&mode=image" <?php if($mode=='image'||$mode==NULL)  echo 'class="current"'; ?> ><?php echo __('Image List','wp-autopost'); ?> <span class="count">(<?php echo number_format($ImgTotal);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-upyun.php&tab=uploaded_images&mode=post" <?php if($mode=='post') echo 'class="current"'; ?> ><?php echo __('Post List','wp-autopost'); ?> <span class="count">(<?php echo number_format($PostTotal);?>)</span></a> </li>
</ul>

<form action="" method="post"> 
<?php if($mode=='post'): ?>
  
<?php
 if($size==null)$size='t';

 if(!isset($_REQUEST['p'])){ 
  $page = 1; 
 } else { 
  $page = $_REQUEST['p']; 
 }
 $perPage=15;
 
 $from = (($page * $perPage) - $perPage);
 $total = $PostTotal;
 $total_pages = ceil($total / $perPage);
 
 $posts = $wpdb->get_results('SELECT distinct id,date_time FROM '.$t_ap_upyun_img.' ORDER BY id DESC LIMIT '.$from.','.$perPage);
 
?>

 <div class="tablenav">

   <div class="tablenav-pages alignright">
	 <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	   <?php
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-upyun.php&tab=uploaded_images&mode=post',  
						  'p' => "%#%",
						  'size' => $size
						);
						$query_page = add_query_arg( $arr_params , $query_page );				
						echo paginate_links( array(
							'base' => $query_page,
							'prev_text' => __('&laquo; Previous'),
							'next_text' => __('Next &raquo;'),
							'total' => $total_pages,
							'current' => $page,
							'end_size' => 1,
							'mid_size' => 5,
						));
					}
		?>	
   </div>  

 </div>

 <table class="wp-list-table widefat fixed media"  style="margin-top:4px">
  <thead>
   <tr>   
    <th scope="col" width="200"><?php echo __('Post'); ?></th>
	<th scope="col" width="100"><?php echo __('Date'); ?></th>
    <th scope="col" ><?php echo __('Upyun Images','wp-autopost'); ?></th>
   </tr>
  </thead>   
  <tbody id="the-list">
 <?php $rowNum=0; foreach($posts as $post){ $rowNum++;?>
   <tr class="<?php if($rowNum%2==1){ echo 'alternate'; } ?>">
      <td>
	    <b><a href="<?php echo get_permalink($post->id); ?>" target="_blank"><?php echo get_the_title($post->id); ?></a></b>
	  </td>
	  <td><?php echo date('Y/m/d',$post->date_time); ?></td>
	  <td>
	    <?php
	      $photos = $wpdb->get_results('SELECT upyun_key FROM '.$t_ap_upyun_img.' WHERE id = '.$post->id);
        ?>    
		  <?php foreach($photos as $image){ ?>
		     
			 <p><?php echo bulidUpyunUrl($upyunOptions['domain'],$image->upyun_key,$size); ?></p>
		  <?php } ?>	
	  </td>
   </tr>
 <?php } ?>
  </tbody>
 </table>
 <div class="tablenav">
   <div class="tablenav-pages alignright">
	 <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	   <?php
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-upyun.php&tab=uploaded_images&mode=post',  
						  'p' => "%#%",
						  'size' => $size
						);
						$query_page = add_query_arg( $arr_params , $query_page );				
						echo paginate_links( array(
							'base' => $query_page,
							'prev_text' => __('&laquo; Previous'),
							'next_text' => __('Next &raquo;'),
							'total' => $total_pages,
							'current' => $page,
							'end_size' => 1,
							'mid_size' => 5,
						));
					}
		?>	
    </div> 
  </div>


<?php else: //else if($mode=='post'||$mode==NULL) ?>

<?php
 if($size==null)$size='m';
 
 if(!isset($_REQUEST['p'])){ 
  $page = 1; 
 } else { 
  $page = $_REQUEST['p']; 
 }
 $perPage=20;
 if($size=='t'||$size=='s'||$size=='q'){
   $perPage = $perPage*3;
 }
 $from = (($page * $perPage) - $perPage);
 $total = $ImgTotal;
 $total_pages = ceil($total / $perPage);
 
 $images = $wpdb->get_results('SELECT id,upyun_key,date_time FROM '.$t_ap_upyun_img.' ORDER BY id DESC LIMIT '.$from.','.$perPage);

?>
 
 <div class="tablenav">

   <div class="tablenav-pages alignright">
	 <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	   <?php
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-upyun.php&tab=uploaded_images&mode=image',  
						  'p' => "%#%",
						  'size' => $size
						);
						$query_page = add_query_arg( $arr_params , $query_page );				
						echo paginate_links( array(
							'base' => $query_page,
							'prev_text' => __('&laquo; Previous'),
							'next_text' => __('Next &raquo;'),
							'total' => $total_pages,
							'current' => $page,
							'end_size' => 1,
							'mid_size' => 5,
						));
					}
		?>	
   </div>

 </div>
   
 <table class="wp-list-table widefat fixed media"  style="margin-top:4px">
  <thead>
   <tr>   
    <th scope="col" width="50%"><?php echo __('Image'); ?></th>
	<th scope="col" width="10%"><?php echo __('Date'); ?></th>
    <th scope="col" width="40%"><?php echo __('Attached to'); ?></th> 
   </tr>
  </thead>   
  <tbody id="the-list">
<?php $rowNum=0; foreach($images as $image){ $rowNum++;?>
   <tr class="<?php if($rowNum%2==1){ echo 'alternate'; } ?>">
      <td>
	    <?php echo bulidUpyunUrl($upyunOptions['domain'],$image->upyun_key,$size); ?>  
	  </td>
	  <td><?php echo date('Y/m/d',$image->date_time); ?></td>
	  <td>
	    <b><a href="<?php echo get_permalink($image->id); ?>" target="_blank"><?php echo get_the_title($image->id); ?></a></b>	
	  </td>
   </tr>
 <?php } ?>
  </tbody>
 </table>

 

 <div class="tablenav">
   <div class="tablenav-pages alignright">
	 <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	   <?php
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-upyun.php&tab=uploaded_images&mode=image',  
						  'p' => "%#%",
						  'size' => $size
						);
						$query_page = add_query_arg( $arr_params , $query_page );				
						echo paginate_links( array(
							'base' => $query_page,
							'prev_text' => __('&laquo; Previous'),
							'next_text' => __('Next &raquo;'),
							'total' => $total_pages,
							'current' => $page,
							'end_size' => 1,
							'mid_size' => 5,
						));
					}
		?>	
   </div>

 </div>


<?php 
  endif;//end if($mode=='post'||$mode==NULL) 
?> 
 </form>
	
<?php
 break; // end  case 'view_uploaded':
}//end switch ($tab){
?>

</div> <!-- end <div class="wrap"> -->
