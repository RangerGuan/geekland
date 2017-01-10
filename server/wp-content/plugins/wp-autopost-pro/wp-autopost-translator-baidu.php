<?php
$BaiduTransOptions = get_option('wp-autopost-baidu-trans-options');

if(isset($_POST['save_setting'])&&$_POST['save_setting']!=''){
    $BaiduTransOptions['api_key'] = trim($_POST['api_key']);
    update_option( 'wp-autopost-baidu-trans-options', $BaiduTransOptions);
    $BaiduTransOptions = get_option('wp-autopost-baidu-trans-options');
}

?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2><?php echo __('Baidu Translator Options','wp-autopost'); ?></h2>

<?php
if(isset($_POST['test_translate'])&&$_POST['test_translate']!=''){  

  $response = autopostBaiduTranslator::translate($_POST['src_text'],$_POST['fromLanguage'],$_POST['toLanguage'],$BaiduTransOptions['api_key']);
  if(isset($response['err'])){    
	echo '<div class="error fade"><p><strong>'.__('API Key','wp-autopost').':</strong><code>'.$BaiduTransOptions['api_key'].'</code></p><p>Error : '.$response['err'].'</p></div>';
  }else{   
	echo '<div class="updated fade"><p><strong>'.__('API Key','wp-autopost').':</strong><code>'.$BaiduTransOptions['api_key'].'</code></p><p>Result : ';	
	foreach($response['trans_result'] as $trans_result){
      echo htmlspecialchars($trans_result).'<br/>';
	}	
	echo '</p></div>';
  }  
  @ob_flush();flush();
}   
?> 
  
  <div class="updated fade"> 
    <p><?php _e( 'Tip: The frequency of API usage for each IP 1000 times per hour', 'wp-autopost' );?></p>
  </div>
  
  
  <form action="admin.php?page=wp-autopost-pro/wp-autopost-translator-baidu.php" method="post">
 
   <table class="form-table">
    <tr>
      <th scope="row"><label><?php _e( 'API Key', 'wp-autopost' );?>:</label></th>
	  <td>
	    <input type="text" name="api_key" value="<?php echo @$BaiduTransOptions['api_key']; ?>" size="60"/>
	  </td>
    </tr>
   </table>
  <?php if(get_bloginfo('language')=='zh-CN'): ?>
   <p><a href="http://wp-autopost.org/zh/manual/how-to-apply-baidu-translator-api-key/" target="_blank">如何申请百度翻译API Key？</a></p>
  <?php else: ?> 
   <p><a href="http://wp-autopost.org/manual/how-to-apply-baidu-translator-api-key/" target="_blank">How to apply Baidu Translator API Key?</a></p>
  <?php endif; ?>
   <p class="submit"><input type="submit" name="save_setting" class="button-primary" value="<?php echo __('Save Changes'); ?>" ></p>
 
 
   <p><?php _e( 'If set up correctly, the following language can be translated into other languages', 'wp-autopost' );?></p>
   <table>
   <?php if(isset($errMsg)&&$errMsg!=null){ ?>
	<tr>
      <td colspan="2"> 
	    <div style="background-color:#ffebe8;border-color:#cc0000;border-style:solid;border-width:1px;padding:10px;">
         <?php echo $errMsg; ?>
		</div>
	  </td>
	</tr>
	<?php } ?>
	<tr>
      <td>
	   <?php echo __('Original Language','wp-autopost'); ?> : <select name="fromLanguage" ><?php $fromLanguage = ($_POST['fromLanguage']!='')?$_POST['fromLanguage']:'en'; echo autopostBaiduTranslator::bulid_lang_options($fromLanguage); ?></select>
	  </td>
	</tr>
	<tr>
      <td>
	    <textarea name="src_text" cols="50" rows="5"><?php echo (isset($_POST['src_text'])&&$_POST['src_text']!='')?stripslashes($_POST['src_text']):'Hello world!'; ?></textarea>
	  </td>
    </tr>
	<tr>
     <td><?php echo __('Translated into','wp-autopost'); ?> : <select name="toLanguage" ><?php $toLanguage = ($_POST['toLanguage']!='')?$_POST['toLanguage']:'';echo autopostBaiduTranslator::bulid_lang_options($toLanguage); ?></select></td>
	</tr>
   </table>
   <p><input type="submit" name="test_translate" class="button-primary" value="<?php echo __('Test Translate', 'wp-autopost'); ?>" ></p>

  </form>
</div>