<?php
$proxy = get_option('wp-autopost-proxy');
if(isset($_POST['save_setting'])&&$_POST['save_setting']!=''){
  $proxy['ip'] =  $_POST['ip'];
  $proxy['port'] =  $_POST['port'];
  $proxy['user'] =  $_POST['user'];
  $proxy['password'] =  $_POST['password'];
  update_option( 'wp-autopost-proxy', $proxy);
  $proxy = get_option('wp-autopost-proxy');
}



if(isset($_POST['test_proxy'])&&$_POST['test_proxy']!=''){
  if($proxy['ip']==''||$proxy['ip']==null){
    echo '<div class="error"><p>'.__( 'Please fill Hostname/IP first', 'wp-autopost' ).'</p></div>'; 
  }elseif($_POST['url']==''){
     
  }else{
	$curlHandle = curl_init();
    $agent='Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.19 (KHTML, like Gecko) Chrome/25.0.1323.1 Safari/537.19';
    curl_setopt( $curlHandle , CURLOPT_URL, $_POST['url'] );
    curl_setopt( $curlHandle , CURLOPT_TIMEOUT, 30 );
    curl_setopt( $curlHandle , CURLOPT_USERAGENT, $agent );  
    @curl_setopt( $curlHandle , CURLOPT_REFERER, _REFERER_ );     
    curl_setopt( $curlHandle , CURLOPT_HEADER, false);
    curl_setopt( $curlHandle , CURLOPT_RETURNTRANSFER, 1 );
    
	
	curl_setopt($curlHandle,CURLOPT_PROXY,$proxy['ip']);
    curl_setopt($curlHandle,CURLOPT_PROXYPORT,$proxy['port']);
	if($proxy['user']!=''&& $proxy['user']!=NULL && $proxy['password']!='' && $proxy['password']!=NULL){
       $userAndPass = $proxy['user'].':'.$proxy['password'];
	   curl_setopt($curlHandle,CURLOPT_PROXYUSERPWD,$userAndPass);    // curl_setopt($ch,CURLOPT_PROXYUSERPWD,'user:password');
	}
	   
    $result = curl_exec( $curlHandle );
    curl_close( $curlHandle );
	
	$file = dirname(__FILE__).'/proxy_temp.html';
    $fileUrl=plugins_url('/proxy_temp.html', __FILE__ );
    
	file_put_contents ( $file, $result );

	$show=true;
  }
}

?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2><?php echo __('Proxy Options','wp-autopost'); ?></h2>
  <form action="admin.php?page=wp-autopost-pro/wp-autopost-proxy.php" method="post">
   <table class="form-table">
    <tr>
      <th scope="row"><label><?php _e( 'Hostname / IP', 'wp-autopost' );?>:</label></th>
	  <td>
	    <input type="text" name="ip" value="<?php echo $proxy['ip']; ?>" size="60"/>
	  </td>
    </tr>
	<tr>
      <th scope="row"><label><?php _e( 'Port', 'wp-autopost' );?>:</label></th>
	  <td>
	    <input type="text" name="port" value="<?php echo $proxy['port']; ?>"  size="60"/>
	  </td>
    </tr>
	<tr>
      <th scope="row"><label><?php _e( 'User', 'wp-autopost' );?> (<i><?php _e( 'optional', 'wp-autopost' );?></i>) :</label></th>
	  <td>
	    <input type="text" name="user" value="<?php echo $proxy['user']; ?>"  size="60"/>
	  </td>
    </tr>
	<tr>
      <th scope="row"><label><?php _e( 'Password', 'wp-autopost' );?> (<i><?php _e( 'optional', 'wp-autopost' );?></i>) :</label></th>
	  <td>
	    <input type="text" name="password" value="<?php echo $proxy['password']; ?>"  size="60"/>
	  </td>
    </tr>
   </table>
   <p class="submit"><input type="submit" name="save_setting" class="button-primary" value="<?php echo __('Save Changes'); ?>" ></p>
   

   
   <table class="form-table" width="100%">
	<tr>
      <td colspan="2"><?php _e( 'URL', 'wp-autopost' );?> : <input type="text" name="url" value=""  size="90"/> <input type="submit" name="test_proxy" class="button" value="<?php echo __('Test','wp-autopost'); ?>" ></td>
    </tr>
  <?php if(@$show){ ?>
    <tr>
      <td colspan="2" >
	     <textarea cols="180" rows="5"><?php echo htmlspecialchars($result); ?></textarea>
	  </td>
	</tr>
	
	<tr>
      <td colspan="2" style="border-width:2px;border-style:solid;border-color:#dfdfdf">
		<iframe src="<?php echo $fileUrl; ?>"  width="100%" height="600" frameborder="0"  ></iframe>
	  </td>
	</tr>
	
  <?php } ?>
   </table>

  </form>
</div>