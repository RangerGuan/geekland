<?php
set_time_limit(0);
$Qiniu = get_option('wp-autopost-qiniu-options');

if(isset($_POST['save_setting'])&&$_POST['save_setting']!=''){
  $Qiniu['domain'] =  $_POST['domain'];
  $Qiniu['bucket'] =  $_POST['bucket'];
  $Qiniu['access_key'] =  $_POST['access_key'];
  $Qiniu['secret_key'] =  $_POST['secret_key'];  
  if($_POST['not_save']=='on'){
    $Qiniu['not_save'] = 0 ;
  }else{
    $Qiniu['not_save'] = 1 ;
  }
  update_option( 'wp-autopost-qiniu-options', $Qiniu);
  $Qiniu = get_option('wp-autopost-qiniu-options');
}

$tab = @$_GET['tab'];
if($tab==null)$tab='default';
?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  
  <h2 class="nav-tab-wrapper">
   <a href="admin.php?page=wp-autopost-pro/wp-autopost-qiniu.php" class="nav-tab <?php if($tab=='default') echo 'nav-tab-active';?>"><?php _e( 'Qiniu Options', 'wp-autopost' );?></a>
   <a href="admin.php?page=wp-autopost-pro/wp-autopost-qiniu.php&tab=uploaded_images" class="nav-tab <?php if($tab=='uploaded_images') echo 'nav-tab-active';?>"><?php _e( 'Uploaded Images', 'wp-autopost' );?></a>
  </h2>
<?php 
switch ($tab){
 case 'default':
?>
 
 <h3><?php _e( 'Automatically upload images to <a href="http://www.qiniu.com" target="_blank" >Qiniu</a> (10GB Free Storage), save bandwidth and space, speed up your website.', 'wp-autopost' );?></h3>
  
<form action="admin.php?page=wp-autopost-pro/wp-autopost-qiniu.php" method="post">
<h3><?php _e( 'Qiniu Account', 'wp-autopost' );?></h3>
<table class="form-table">
  <tr>
    <th scope="row"><label><?php _e( 'The Domain of Qiniu Bucket', 'wp-autopost' );?>:</label></th>
	<td> 
	   <input type="text" name="domain" value="<?php echo $Qiniu['domain']; ?>" size="60"/>
	   <span class="gray">xxxxx.qiniudn.com</span>
	</td>
  </tr>
  <tr>
    <th scope="row"><label><?php _e( 'Bucket', 'wp-autopost' );?>:</label></th>
	<td> 
	   <input type="text" name="bucket" value="<?php  echo $Qiniu['bucket']; ?>" size="60"/>
	</td>
  </tr>
  <tr>
    <th scope="row"><label><?php _e( 'Access Key', 'wp-autopost' );?>:</label></th>
	<td> 
	   <input type="text" name="access_key" value="<?php  echo $Qiniu['access_key']; ?>" size="60"/>
	</td>
  </tr>
  <tr>
    <th scope="row"><label><?php _e( 'Secret Key', 'wp-autopost' );?>:</label></th>
	<td> 
	   <input type="text" name="secret_key" value="<?php  echo $Qiniu['secret_key']; ?>" size="60"/>
	</td>
  </tr>

  <tr>
     <th scope="row"></th>
	 <td>
	   <p><?php _e( 'If the settings are correct, will upload a image to your Qiniu Bucket and appear here.', 'wp-autopost' );?></p>
  <?php
       if($Qiniu['domain']!=''&&$Qiniu['bucket']!=''&&$Qiniu['access_key']!=''&&$Qiniu['secret_key']!=''):
	     $upload_image = dirname(__FILE__).'/images/watermark.png';      
		 Qiniu_setKeys($Qiniu['access_key'], $Qiniu['secret_key']);      
         list($ret, $err) = Qinniu_upload_to_bucket($Qiniu['bucket'],$upload_image,'wp-autopost-logo.png');
         if ($err !== null) {
           echo '<div style="background-color:#ffebe8;border-color:#cc0000;border-style:solid;border-width:1px;padding:10px;">';
		   //var_dump($err);
		   echo $err->Err;
		   echo '</div>';
		   $Qiniu['set_ok'] =  0;
           update_option( 'wp-autopost-qiniu-options', $Qiniu);
         } else {
		   $Qiniu['set_ok'] =  1;
           update_option( 'wp-autopost-qiniu-options', $Qiniu);
 
		   $baseUrl = Qiniu_RS_MakeBaseUrl($Qiniu['domain'], $ret['key']);
		   echo '<div class="view_img"><img src='.$baseUrl.' /></div>';
		   echo __('The image URL','wp-autopost').' : <a href="'.$baseUrl.'" target="_blank">'.$baseUrl.'</a>';
         }    
	   endif;
   ?>
	 </td>
  </tr>

  <tr>
    <td colspan="2">
	<input type="checkbox" name="not_save" <?php if($Qiniu['not_save']!=1) echo 'checked="true"'; ?> /> <?php _e( 'Save a copy on local server', 'wp-autopost' );?></td>
  </tr>

  <tr>
    <td colspan="2"><?php _e( 'Access Key and Secret Key vist <a href="https://portal.qiniu.com/setting/key" target="_blank">https://portal.qiniu.com/setting/key</a> to obtain', 'wp-autopost' );?></td>
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

 function bulidQiniuUrl($domain,$key,$size,$title=''){
	$baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);	
	switch($size){
      case 't':
	   $imgurl = $baseUrl.'?imageView/2/w/100';	    
	  break;
	  case 's':
	   $imgurl = $baseUrl.'?imageView/1/w/75/h/75';	    
	  break;
	  case 'q':
	   $imgurl = $baseUrl.'?imageView/1/w/150/h/150';	 	    
	  break;
	  case 'm':
	   $imgurl = $baseUrl.'?imageView/2/w/240';		    
	  break;
	  case 'n':
	   $imgurl = $baseUrl.'?imageView/2/w/320';		    
	  break;
	  case '-':
	   $imgurl = $baseUrl.'?imageView/2/w/500';
	  break;
	  case 'z':
	   $imgurl = $baseUrl.'?imageView/2/w/640';	    
	  break;
	  case 'c':
	   $imgurl = $baseUrl.'?imageView/2/w/800';		    
	  break;
	  case 'b':
	   $imgurl = $baseUrl.'?imageView/2/w/1024';	    
	  break;
	  case 'o':
	   $imgurl = $baseUrl;	    
	  break;
	}
	return '<a href="'.$imgurl.'" target="_blank"><img src="'.$imgurl.'" alt="'.$title.'" title="'.$title.'"/></a>';
	
 }

 $PostTotal = $wpdb->get_var('SELECT count(distinct id) FROM '.$t_ap_qiniu_img);
 $ImgTotal = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_qiniu_img);

?>

<ul class='subsubsub'>
	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-qiniu.php&tab=uploaded_images&mode=image" <?php if($mode=='image'||$mode==NULL)  echo 'class="current"'; ?> ><?php echo __('Image View','wp-autopost'); ?> <span class="count">(<?php echo number_format($ImgTotal);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-qiniu.php&tab=uploaded_images&mode=post" <?php if($mode=='post') echo 'class="current"'; ?> ><?php echo __('Post View','wp-autopost'); ?> <span class="count">(<?php echo number_format($PostTotal);?>)</span></a> </li>
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
 
 $posts = $wpdb->get_results('SELECT distinct id,date_time FROM '.$t_ap_qiniu_img.' ORDER BY id DESC LIMIT '.$from.','.$perPage);
 
?>

 <div class="tablenav">
  
   <div class="alignleft">
     <?php echo __('Image Size','wp-autopost'); ?> : 
	 <select name="size" id="size">
	  <option value="t" <?php selected($size, 't'); ?>>thumbnail, 100 on longest side</option>
	  <option value="s" <?php selected($size, 's'); ?>>small square 75x75</option>
	  <option value="q" <?php selected($size, 'q'); ?>>large square 150x150</option>
	  <option value="m" <?php selected($size, 'm'); ?>>small, 240 on longest side</option>
	  <option value="n" <?php selected($size, 'n'); ?>>small, 320 on longest side</option>
	  <option value="-" <?php selected($size, '-'); ?>>medium, 500 on longest side</option>
	  <option value="z" <?php selected($size, 'z'); ?>>medium, 640 on longest side</option>
	  <option value="c" <?php selected($size, 'c'); ?>>medium, 800 on longest side</option>
	  <option value="b" <?php selected($size, 'b'); ?>>large, 1024 on longest side</option>
	  <option value="o" <?php selected($size, 'o'); ?>>original image</option>	  
	 </select>
     <input type="submit" name="" class="button action" value="<?php echo __('Apply'); ?>" />
   </div>

   <div class="tablenav-pages alignright">
	 <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	   <?php
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-qiniu.php&tab=uploaded_images&mode=post',  
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
    <th scope="col" ><?php echo __('Qiniu Images','wp-autopost'); ?></th>
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
	      $photos = $wpdb->get_results('SELECT qiniu_key FROM '.$t_ap_qiniu_img.' WHERE id = '.$post->id);
        ?>
	    <div class="view_img">
		  <?php foreach($photos as $image){ ?>
		     <?php echo bulidQiniuUrl($Qiniu['domain'],$image->qiniu_key,$size); ?>
		  <?php } ?>	
		</div>
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
						  'page' => 'wp-autopost-pro/wp-autopost-qiniu.php&tab=uploaded_images&mode=post',  
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

 <script type="text/javascript" src="<?php echo $wp_autopost_root; ?>js/masonry.pkgd.min.js" /></script>
 <script type="text/javascript">
  jQuery(document).ready(function($){ 
    $(window).load( function() {
        var container = document.querySelector('#ImgContainer');
        var msnry = new Masonry( container, {
           itemSelector: '.blockImg'
        });
	});  
  });
 </script>

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
 
 $images = $wpdb->get_results('SELECT id,qiniu_key,date_time FROM '.$t_ap_qiniu_img.' ORDER BY id DESC LIMIT '.$from.','.$perPage);

?>
 
 <div class="tablenav">
  
   <div class="alignleft">
     <?php echo __('Image Size','wp-autopost'); ?> : 
	 <select name="size" id="size">
	  <option value="t" <?php selected($size, 't'); ?> >thumbnail, 100 on longest side</option>
	  <option value="s" <?php selected($size, 's'); ?>>small square 75x75</option>
	  <option value="q" <?php selected($size, 'q'); ?>>large square 150x150</option>
	  <option value="m" <?php selected($size, 'm'); ?>>small, 240 on longest side</option>
	  <option value="n" <?php selected($size, 'n'); ?>>small, 320 on longest side</option>
	  <option value="-" <?php selected($size, '-'); ?>>medium, 500 on longest side</option>
	  <option value="z" <?php selected($size, 'z'); ?>>medium, 640 on longest side</option>
	  <option value="c" <?php selected($size, 'c'); ?>>medium, 800 on longest side</option>
	  <option value="b" <?php selected($size, 'b'); ?>>large, 1024 on longest side</option>
	  <option value="o" <?php selected($size, 'o'); ?>>original image</option>	  
	 </select>
     <input type="submit" name="" class="button action" value="<?php echo __('Apply'); ?>" />
   </div>

   <div class="tablenav-pages alignright">
	 <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	   <?php
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-qiniu.php&tab=uploaded_images&mode=image',  
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
 function bulidBlockImg($domain,$image,$size){
   $title =  get_the_title($image->id);
   $pad=10;
   switch($size){
     case 'm': $width=240; break;
     case 'n': $width=320; break;
	 case '-': $width=500; break;
	 case 'z': $width=640; break;
	 case 'c': $width=800; break;
	 case 'b': $width=1024; break;
	 case 'o': $width=1024; break;
   }
   $width = $width+$pad;
   if($width>$pad){
     echo '<div class="blockImg" style="width:'.$width.'px;">';
   }else{
     echo '<div class="blockImg">';
   }
   echo '<div class="innerImg">';
     echo bulidQiniuUrl($domain,$image->qiniu_key,$size,$title);
   echo '</div>';
   if($size!='t'&&$size!='s'&&$size!='q'){
      echo '<p><a style="text-decoration:none;" href="'.get_permalink($image->id).'" target="_blank"><strong>'.$title.'</strong></a>,'.date('Y/m/d',$image->date_time).'</p>';
   }
   echo '</div>';
 }
 ?>
 
 <div id="ImgContainer">
  <?php 
   foreach($images as $image){ 
     bulidBlockImg($Qiniu['domain'],$image,$size); 
   } ?>
 </div>
 

 <div class="tablenav">
   <div class="tablenav-pages alignright">
	 <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	   <?php
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-qiniu.php&tab=uploaded_images&mode=image',  
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
