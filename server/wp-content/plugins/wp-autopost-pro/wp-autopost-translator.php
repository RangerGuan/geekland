<?php
$MicroTransOptions = get_option('wp-autopost-micro-trans-options');
if(isset($_POST['save_setting'])&&$_POST['save_setting']!=''){
  if($_POST['clientID']!=''&&$_POST['clientSecret']!=''){
    $newKeyArray = array();
    $newKeyArray['clientID'] =  $_POST['clientID'];
    $newKeyArray['clientSecret'] =  $_POST['clientSecret'];
    $MicroTransOptions[]=$newKeyArray;
    update_option( 'wp-autopost-micro-trans-options', $MicroTransOptions);
    $MicroTransOptions = get_option('wp-autopost-micro-trans-options');
  }
}

if(isset($_REQUEST['del'])&&$_REQUEST['del']!=''){
   unset($MicroTransOptions[$_REQUEST['del']]);
   update_option( 'wp-autopost-micro-trans-options', $MicroTransOptions);
   $MicroTransOptions = get_option('wp-autopost-micro-trans-options');
}

?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2><?php echo __('Microsoft Translator Options','wp-autopost'); ?></h2>

<?php
if(isset($_POST['test_translate'])&&$_POST['test_translate']!=''){  
  if(isset($_POST['ids'])&&$_POST['ids']!=NULL){
    foreach($_POST['ids']  as $id ){    
	  $token = autopostMicrosoftTranslator::getTokens($MicroTransOptions[$id]['clientID'],$MicroTransOptions[$id]['clientSecret']);
	  if(isset($token['err'])&&$token['err']!=null){
        echo '<div class="error fade"><p>'.__('Client Secret Group','wp-autopost').' : <strong>'.__( 'Client ID', 'wp-autopost' ).':</strong> <code>'.$MicroTransOptions[$id]['clientID'].'</code> <strong>'.__( 'Client secret', 'wp-autopost' ).':</strong><code>'.$MicroTransOptions[$id]['clientSecret'].'</code></p><p>Error : '.$token['err'].'</p></div>';
      }else{
        $translated = autopostMicrosoftTranslator::translate($token['access_token'],stripslashes($_POST['src_text']),$_POST['fromLanguage'],$_POST['toLanguage']);
        if(isset($translated['err'])&&$translated['err']!=null){
          echo '<div class="error fade"><p>'.__('Client Secret Group','wp-autopost').' : <strong>'.__( 'Client ID', 'wp-autopost' ).':</strong> <code>'.$MicroTransOptions[$id]['clientID'].'</code> <strong>'.__( 'Client secret', 'wp-autopost' ).':</strong><code>'.$MicroTransOptions[$id]['clientSecret'].'</code></p><p>Error : '.$translated['err'].'</p></div>';
	    }else{
         if($translated['str']!=null&&$translated['str']!=''){
		   echo '<div class="updated fade"><p>'.__('Client Secret Group','wp-autopost').' : <strong>'.__( 'Client ID', 'wp-autopost' ).':</strong> <code>'.$MicroTransOptions[$id]['clientID'].'</code> <strong>'.__( 'Client secret', 'wp-autopost' ).':</strong><code>'.$MicroTransOptions[$id]['clientSecret'].'</code></p><p>Result : '.htmlspecialchars($translated['str']).'</p></div>';
		 }else{
           echo '<div class="error fade"><p>'.__('Client Secret Group','wp-autopost').' : <strong>'.__( 'Client ID', 'wp-autopost' ).':</strong> <code>'.$MicroTransOptions[$id]['clientID'].'</code> <strong>'.__( 'Client secret', 'wp-autopost' ).':</strong><code>'.$MicroTransOptions[$id]['clientSecret'].'</code></p><p>Error : Timeout</p></div>';
		 }
	    }
	  }
	  @ob_flush();flush();
    }//end foreach
  }else{
    echo '<div class="error fade"><p>'.__('Please Select Client Secret Group.','wp-autopost').'</p></div>';
  }
}   
?>

<script type="text/javascript">
jQuery(document).ready(function($){
  $('#checkAll').change(function(){
	 if(!!$(this).attr("checked")){
	   $('.checkrow').attr("checked",true);
       $('.row').addClass("selectbg");
	 }else{
       $('.checkrow').attr("checked",false);
	   $('.row').removeClass("selectbg");
	 }
  }); 
  $('.checkrow').change(function(){
      var ids = $(this).val();
	  var checked = $(this).attr("checked");
	  if(!!$(this).attr("checked")){
          $("#row"+ids).addClass("selectbg");
	  }else{
          $("#row"+ids).removeClass("selectbg");
	  }
  });
});
</script>
  
  <form action="admin.php?page=wp-autopost-pro/wp-autopost-translator.php" method="post">
   
   <table class="widefat tablehover"  style="margin-top:4px"> 
     <thead>
      <tr>
        <th scope="col" class='manage-column column-cb check-column'><input type="checkbox" name="All" id="checkAll" ></th>
        <th scope="col" ><?php _e( 'Client ID', 'wp-autopost' );?></th>
        <th scope="col" ><?php _e( 'Client secret', 'wp-autopost' );?></th>
        <th scope="col" ></th>
      </tr>
     </thead>
	 <tbody id="the-list">
<?php if($MicroTransOptions!=null)foreach($MicroTransOptions as $k => $v){ ?>
      <tr class="row" id="row<?php echo $k; ?>">
        <td><input type="checkbox" name="ids[]" value="<?php echo $k; ?>" class="checkrow" /></td>
		<td><?php echo $v['clientID']; ?></td>
        <td><?php echo $v['clientSecret']; ?></td>
		<td> <span class="trash"><a class="submitdelete" title="delete" href="admin.php?page=wp-autopost-pro/wp-autopost-translator.php&del=<?php echo $k; ?>" ><?php echo __('Delete'); ?></a></span> </td>
	  </tr>
 <?php } ?>
	 </tbody>
   </table>
 
   <table class="form-table">
    <tr>
      <th scope="row"><label><?php _e( 'Client ID', 'wp-autopost' );?>:</label></th>
	  <td>
	    <input type="text" name="clientID" value="" size="60"/>
	  </td>
    </tr>
	<tr>
      <th scope="row"><label><?php _e( 'Client secret', 'wp-autopost' );?>:</label></th>
	  <td>
	    <input type="text" name="clientSecret" value=""  size="60"/>
	  </td>
    </tr>
   </table>
  <?php if(get_bloginfo('language')=='zh-CN'): ?>
   <p><a href="http://wp-autopost.org/zh/manual/how-to-apply-microsoft-translator-client-id-and-client-secret/" target="_blank">如何申请微软翻译客户端密钥？</a></p>
  <?php else: ?> 
   <p><a href="http://wp-autopost.org/manual/how-to-apply-microsoft-translator-client-id-and-client-secret/" target="_blank">How to apply Microsoft Translator Client ID and Client secret?</a></p>
  <?php endif; ?>
   <p class="submit"><input type="submit" name="save_setting" class="button-primary" value="<?php echo __('Add Client Secret Group','wp-autopost'); ?>" ></p>
 
 
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
	   <?php echo __('Original Language','wp-autopost'); ?> : <select name="fromLanguage" ><?php $fromLanguage = ($_POST['fromLanguage']!='')?$_POST['fromLanguage']:'en'; echo autopostMicrosoftTranslator::bulid_lang_options($fromLanguage); ?></select>
	  </td>
	</tr>
	<tr>
      <td>
	    <textarea name="src_text" cols="50" rows="5"><?php echo (isset($_POST['src_text'])&&$_POST['src_text']!='')?stripslashes($_POST['src_text']):'Hello world!'; ?></textarea>
	  </td>
    </tr>
	<tr>
     <td><?php echo __('Translated into','wp-autopost'); ?> : <select name="toLanguage" ><?php $toLanguage = ($_POST['toLanguage']!='')?$_POST['toLanguage']:'';echo autopostMicrosoftTranslator::bulid_lang_options($toLanguage); ?></select></td>
	</tr>
   </table>
   <p><input type="submit" name="test_translate" class="button-primary" value="<?php echo __('Test Translate', 'wp-autopost'); ?>" ></p>

  </form>
</div>


<?php
 //shuffle
?>