<?php
set_time_limit(0);
$Flickr = get_option('wp-autopost-flickr-options');

function insertNewFlickrOauth($oauth_token,$oauth_token_secret){
  global $wpdb,$t_ap_flickr_oauth;
  $wpdb->query($wpdb->prepare("insert into $t_ap_flickr_oauth(oauth_token,oauth_token_secret) values (%s,%s)",$oauth_token,$oauth_token_secret));
  return $wpdb->get_var("SELECT LAST_INSERT_ID()");
}

if(isset($_GET['oauth_verifier'])&&$_GET['oauth_verifier']!=''){ // process callback
  $f = new autopostFlickr($Flickr['api_key'],$Flickr['api_secret']);
  $f->getAccessToken();
  $oauth_token = $f->getOauthToken();
  $oauth_token_secret = $f->getOauthSecretToken();
  
  $Flickr['oauth_token'] = $oauth_token;
  $Flickr['oauth_token_secret'] = $oauth_token_secret;
  
  $re = $f->call("flickr.auth.oauth.checkToken", array("oauth_token"=>$Flickr['oauth_token']));
  
  //print_r($re);
  
  $Flickr['user_id'] = $re['oauth']['user']['nsid'];
  
  $oauth_id = insertNewFlickrOauth($oauth_token,$oauth_token_secret);
  $Flickr['oauth_id'] = $oauth_id;

  update_option( 'wp-autopost-flickr-options', $Flickr );
  
  $Flickr = get_option('wp-autopost-flickr-options');
}

if(isset($_GET['log_out'])&&$_GET['log_out']=='true'){
  $Flickr['oauth_token'] = '';
  $Flickr['oauth_token_secret'] = '';
  $Flickr['user_id'] = '';
  $Flickr['oauth_id'] = '';
  update_option( 'wp-autopost-flickr-options', $Flickr );
  $Flickr = get_option('wp-autopost-flickr-options');
}

if(isset($_POST['save_setting'])&&$_POST['save_setting']!=''){
  $Flickr['flickr_set'] =  $_POST['flickr_set'];
  $Flickr['is_public'] =  $_POST['is_public'];
  if($_POST['not_save']=='on'){
    $Flickr['not_save'] = 0 ;
  }else{
    $Flickr['not_save'] = 1 ;
  }
  update_option( 'wp-autopost-flickr-options', $Flickr);
  $Flickr = get_option('wp-autopost-flickr-options');
}

$tab = @$_GET['tab'];
if($tab==null)$tab='default';
?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  
  <h2 class="nav-tab-wrapper">
   <a href="admin.php?page=wp-autopost-pro/wp-autopost-flickr.php" class="nav-tab <?php if($tab=='default') echo 'nav-tab-active';?>"><?php _e( 'Flickr Options', 'wp-autopost' );?></a>
   <a href="admin.php?page=wp-autopost-pro/wp-autopost-flickr.php&tab=uploaded_images" class="nav-tab <?php if($tab=='uploaded_images') echo 'nav-tab-active';?>"><?php _e( 'Uploaded Images', 'wp-autopost' );?></a>
  </h2>
<?php 
switch ($tab){
 case 'default':
?>
 <div class="updated fade"><p><strong>Warning:</strong> upload images to Flickr, when involve the copyright issues of photos, Flickr may block those photos.</p></div>
 
 <h3><?php _e( 'Automatically upload images to <a href="http://www.flickr.com" target="_blank">Flickr</a> (1TB Free Storage), save bandwidth and space, speed up your website.', 'wp-autopost' );?></h3>
  
<?php
if($Flickr['oauth_token']==''): ?>
  
 <div>
  <h3><?php _e( 'To use this plugin requires login to your Flickr account and authorize the plugin to connect to your account!', 'wp-autopost' );?></h3>
  <a href="admin.php?page=wp-autopost-pro/wp-autopost-flickr.php&wp_autopost_flickr_request_token=true" class="button button-primary button-hero load-customize" ><?php _e( 'Click here to authentication', 'wp-autopost' );?></a>

 </div>

<?php
else:  // else if($Flickr['oauth_token']==''):
?>

<?php
   //print_r($Flickr);

   $f = new autopostFlickr($Flickr['api_key'],$Flickr['api_secret']);
   $f->setOauthToken($Flickr['oauth_token'], $Flickr['oauth_token_secret']);
?>
<h3><?php _e( 'Flickr Account', 'wp-autopost' );?></h3>
<table class="form-table">
  <tr>
    <th scope="row"><label><?php _e( 'User Name', 'wp-autopost' );?>:</label></th>
	<td> 
	   <?php
         $re = $f->people_getInfo($Flickr['user_id']);
         echo $re['username'];
	   ?>
	</td>
  </tr>
  <tr>
    <th scope="row"><label><?php _e( 'User Id', 'wp-autopost' );?>:</label></th>
	<td> 
	   <?php echo $Flickr['user_id']; ?>
	</td>
  </tr>
  <tr>
    <th scope="row"><label><?php _e( 'Photo Stream', 'wp-autopost' );?>:</label></th>
	<td> 
	   <a href="http://www.flickr.com/photos/<?php echo $Flickr['user_id']; ?>/" target="_blank">http://www.flickr.com/photos/<?php echo $Flickr['user_id']; ?>/</a>
	   <p><?php _e( 'If properly connected to your Flickr account, 5 of your recent photos from your Flickr photostream should appear here.', 'wp-autopost' );?></p>
<?php
  $re = $f->people_getPhotos($Flickr['user_id'], array('per_page' => 5, 'page' => 1));
  if($re!=null){
?>
<div class="view_img">
<?php
 foreach($re['photos']['photo'] as $photo) {
   $photo_url = "http://farm{$photo['farm']}.static.flickr.com/{$photo['server']}/{$photo['id']}_{$photo['secret']}_s.jpg";
   echo '<a href="http://www.flickr.com/photos/'.$Flickr['user_id'].'/'.$photo['id'].'" target="_blank"><img src="'.$photo_url.'" title="'.$photo['title'].'"/></a>&nbsp;&nbsp;&nbsp;';
 }
?>
</div>
<?php }else{ ?>
 <div style="background-color:#ffebe8;border-color:#cc0000;border-style:solid;border-width:1px;padding:10px;">
   <?php _e( 'Can not connected to your Flickr account.', 'wp-autopost' );?>
 </div>
<?php } ?>
	</td>
  </tr>
  <tr>
    <td colspan="2">
	 <a href="admin.php?page=wp-autopost-pro/wp-autopost-flickr.php&log_out=true" class="button"><?php _e( 'Log Out');?></a>
	</td>
  </tr>
</table>

<?php
  if($re!=null){
?>
<form action="admin.php?page=wp-autopost-pro/wp-autopost-flickr.php" method="post">
<h3><?php _e( 'Flickr Photo Sets', 'wp-autopost' );?></h3>
<p><?php _e( 'Which Flickr Photo Set you want to use to store images?' , 'wp-autopost');?></p>
<?php  
  $setsList = $f->photosets_getList($Flickr['user_id']);  
  
?>
<?php if($setsList['total']>0):?>
  <table class="form-table">
    <tr>
      <th scope="row"><label><?php _e( 'Flickr Photo Sets', 'wp-autopost' );?>:</label></th>
	  <td>
	   <select name="flickr_set" id="flickr_set" >
	       <option value="0"><?php _e( 'Please Select' , 'wp-autopost');?></option> 
   <?php foreach($setsList['photoset'] as $photoset){ ?>
           <option value="<?php echo $photoset['id']; ?>" <?php if($photoset['id']==$Flickr['flickr_set']) echo 'selected="true"'; ?>><?php echo $photoset['title']; ?></option>   	   
   <?php } ?> 
	   </select>
	  </td>
    </tr>
  </table>
<?php else: ?>
  <p><strong><?php _e( 'Not found any Photo Set' , 'wp-autopost');?></strong><p> 
  <a href="http://www.flickr.com/photos/<?php echo $Flickr['user_id'];?>/sets/" target="_blank" class="button button-primary button-hero load-customize" ><?php _e( 'Create your first set now' , 'wp-autopost');?></a>
<?php endif; ?>


<h3><?php _e( 'Flickr Privacy', 'wp-autopost' );?></h3>
<p><?php _e( 'Who can view the images uploaded by this plugin?' , 'wp-autopost');?></p>
<table class="form-table">
    <tr>
      <th scope="row"><label><?php _e( 'Privacy', 'wp-autopost' );?>:</label></th>
	  <td>
	    <input type="radio" name="is_public" value="0" <?php if($Flickr['is_public']==0) echo 'checked="true"'; ?> /> <?php _e( 'Only you (private)', 'wp-autopost' );?>
		&nbsp;&nbsp;
		<input type="radio" name="is_public" value="1" <?php if($Flickr['is_public']==1) echo 'checked="true"'; ?> /> <?php _e( 'Anyone (public)', 'wp-autopost' );?>
	  </td>
    </tr>
 </table>
 
 <br/>
 <p>
   <input type="checkbox" name="not_save" <?php if($Flickr['not_save']!=1) echo 'checked="true"'; ?> /> <?php _e( 'Save a copy on local server', 'wp-autopost' );?></td>
 </p>
<p><input type="submit" name="save_setting" class="button-primary" value="<?php echo __('Save Changes'); ?>" ></p>
</form>
<?php
  }
?>

<?php
endif;// end if($Flickr['oauth_token']==''):
?>
  

<?php
 break; // end  case 'default':
 case 'uploaded_images':
?> 
<?php
 global $t_ap_flickr_img;
 $mode = @$_GET['mode'];
 $size=@$_POST['size'];
 if($size==null){
   $size=@$_GET['size'];
 }

 $PostTotal = $wpdb->get_var('SELECT count(distinct id) FROM '.$t_ap_flickr_img);
 $ImgTotal = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_flickr_img);

 function bulidFlickrUrl($photo_id,$url_info,$size,$title=''){
    $urlInfo = json_decode($url_info);//[0]:farm  [1]:server  [2]:secret  [3]:originalsecret  [4]:originalformat [5]:user_id
	switch($size){
      case 'o':
	   $imgurl = 'http://farm'.$urlInfo[0].'.static.flickr.com/'.$urlInfo[1].'/'.$photo_id.'_'.$urlInfo[3].'_o.'.$urlInfo[4];	    
	  break;
	  case '-':
	   $imgurl = 'http://farm'.$urlInfo[0].'.static.flickr.com/'.$urlInfo[1].'/'.$photo_id.'_'.$urlInfo[2].'.jpg'; 
	  break;
	  default:
	   $imgurl = 'http://farm'.$urlInfo[0].'.static.flickr.com/'.$urlInfo[1].'/'.$photo_id.'_'.$urlInfo[2].'_'.$size.'.jpg'; 
	  break;
	}
	return '<a href="http://www.flickr.com/photos/'.$urlInfo[5].'/'.$photo_id.'" target="_blank"><img src="'.$imgurl.'" alt="'.$title.'" title="'.$title.'"/></a>';
 }

?>

<ul class='subsubsub'>
	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-flickr.php&tab=uploaded_images&mode=image" <?php if($mode=='image'||$mode==NULL)  echo 'class="current"'; ?> ><?php echo __('Image View','wp-autopost'); ?> <span class="count">(<?php echo number_format($ImgTotal);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-flickr.php&tab=uploaded_images&mode=post" <?php if($mode=='post') echo 'class="current"'; ?> ><?php echo __('Post View','wp-autopost'); ?> <span class="count">(<?php echo number_format($PostTotal);?>)</span></a> </li>
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
 
 $posts = $wpdb->get_results('SELECT distinct id,date_time FROM '.$t_ap_flickr_img.' ORDER BY id DESC LIMIT '.$from.','.$perPage);
 
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
						  'page' => 'wp-autopost-pro/wp-autopost-flickr.php&tab=uploaded_images&mode=post',  
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
    <th scope="col" ><?php echo __('Flickr Images','wp-autopost'); ?></th>
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
	      $photos = $wpdb->get_results('SELECT flickr_photo_id,url_info FROM '.$t_ap_flickr_img.' WHERE id = '.$post->id);
        ?>
	    <div class="view_img">
		  <?php foreach($photos as $image){ ?>
		     <?php echo bulidFlickrUrl($image->flickr_photo_id,$image->url_info,$size); ?>
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
						  'page' => 'wp-autopost-pro/wp-autopost-flickr.php&tab=uploaded_images&mode=post',  
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
 
 $images = $wpdb->get_results('SELECT id,flickr_photo_id,url_info,date_time FROM '.$t_ap_flickr_img.' ORDER BY id DESC LIMIT '.$from.','.$perPage);

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
						  'page' => 'wp-autopost-pro/wp-autopost-flickr.php&tab=uploaded_images&mode=image',  
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
 function bulidBlockImg($image,$size){
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
     echo bulidFlickrUrl($image->flickr_photo_id,$image->url_info,$size,$title);
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
     bulidBlockImg($image,$size); 
   } ?>
 </div>
 

 <div class="tablenav">
   <div class="tablenav-pages alignright">
	 <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	   <?php
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-flickr.php&tab=uploaded_images&mode=image',  
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
