<style>
.postbox h3 {
	font-family: Georgia, "Times New Roman", "Bitstream Charter", Times, serif;
	font-size: 15px;
	padding: 10px 10px;
	margin: 0;
	line-height: 1;
}
</style>
<script type="text/javascript">
jQuery(function($){
   $("h3.hndle").click(function(){$(this).next(".inside").slideToggle('fast');});
});
</script>

<div class="wrap">
 <div class="icon32" id="icon-wp-autopost"><br/></div>
 <h2>Auto Post - <?php echo __('Options','wp-autopost'); ?></h2>
 <div class="clear"></div>
 <br/>
<?php
 if(isset($_POST['submit1'])&&$_POST['submit1']!=''){
   update_option('wp_autopost_updateMethod', $_POST['updateMethod']);
   $t=$_POST['timeLimit'];
   
   if(!preg_match("/^\+?[1-9][0-9]*$/",$t))$t=0;
   
   if($t<0)$t=0;
   
   update_option('wp_autopost_timeLimit', $t);
   
   update_option('wp_autopost_runOnlyOneTask', $_POST['runOnlyOneTask']);

   $t=$_POST['pause_time'];
   
   if(!preg_match("/^\+?[1-9][0-9]*$/",$t))$t=0;
   
   if($t<0)$t=0;
   
   update_option('wp_autopost_pauseTime', $t);


   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';       
 }
 elseif(isset($_POST['submit2'])&&$_POST['submit2']!=''){
   $w = $_POST['imgMinWidth'];
   if(!preg_match("/^\+?[1-9][0-9]*$/",$w))$w=100;  
   
   if($w<0)$w=100;
   update_option('wp_autopost_downImgMinWidth', $w);
   
   $s = $_POST['downImgTimeOut'];
   if(!preg_match("/^\+?[1-9][0-9]*$/",$s))$s=120;  
   if($s<0)$s=120;
   update_option('wp_autopost_downImgTimeOut', $s);

   
   $maxWidth = $_POST['downImgMaxWidth'];
   if(!preg_match("/^\+?[1-9][0-9]*$/",$maxWidth))$maxWidth=800;    
   if($maxWidth<0)$maxWidth=0;
   update_option('wp_autopost_downImgMaxWidth', $maxWidth);

   $downImgQuality = $_POST['downImgQuality'];
   if(!preg_match("/^\+?[1-9][0-9]*$/",$downImgQuality))$downImgQuality=90;    
   if($downImgQuality<1)$downImgQuality=1;
   if($downImgQuality>100)$downImgQuality=100;
   update_option('wp_autopost_downImgQuality', $downImgQuality);
   


   update_option('wp_autopost_downImgFailsNotPost', $_POST['downImgFailsNotPost']); 
   update_option('wp_autopost_downImgThumbnail', $_POST['downImgThumbnail']);  
   update_option('wp_autopost_downImgRelativeURL', $_POST['downImgRelativeURL']); 
   update_option('wp_autopost_downFileOrganize', $_POST['downFileOrganize']);

   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';       
 }
 elseif(isset($_POST['submit3'])&&$_POST['submit3']!=''){
   update_option('wp_autopost_delComment', $_POST['delComment']);
   update_option('wp_autopost_delAttrId', $_POST['delAttrId']);
   update_option('wp_autopost_delAttrClass', $_POST['delAttrClass']);
   update_option('wp_autopost_delAttrStyle', $_POST['delAttrStyle']);
   //update_option('wp_autopost_delEmptyTag', $_POST['delEmptyTag']);
   

   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';       
 }
 elseif(isset($_POST['submit4'])&&$_POST['submit4']!=''){
   $downloadTypes = explode("\r\n",stripslashes($_POST['downloadTypes']));
   $wp_autopost_download_types = array();
   foreach($downloadTypes as $downloadType){
     if(trim($downloadType)!=''||$downloadType!=NULL)$wp_autopost_download_types[] = $downloadType;
   }

   update_option('wp_autopost_download_types', json_encode($wp_autopost_download_types));

   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';  
 }
 elseif($_POST['submit5']!=''){

   update_option('wp_autopost_cdkey', $_POST['cdkey']);

   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';  
 }
 elseif($_POST['submitReset']!=''){
	 deleteflickr();
	 echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';  
 }
?> 
 <form id="myform" method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-options.php">
 
  <div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Authorization Option','wp-autopost'); ?></h3>
  <div class="inside">
	 <table width="100%"> 	         	  
	   <tr>
         <td width="170"><?php echo __('Authorization Code','wp-autopost'); ?>:</td>
		 <td><input type="text" name="cdkey" id="cdkey" value="<?php echo get_option('wp_autopost_cdkey'); ?>" size="30" />
		 </td>
	   </tr>
	   <tr>
         <td colspan="2">
		   <input type="submit" class="button-primary"  name="submit5"  value="<?php echo __('Save Changes'); ?>" />
		 </td>
	   </tr>
    </table>
  </div>
  </div>
  
  <div class="postbox">
	<div class="inside">
		<table width="100%">
			<tr>
				<td width="170"><?php echo __('Reset','wp-autopost'); ?>:</td>
					<td><input type="submit" class="button-primary"  name="submitReset"  value="<?php echo __('Reset','wp-autopost')."(如果出现采集错误请尝试点击此按钮)"; ?>" />
				</td>
			</tr>
		</table>
	</div>
  </div>

  <div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Update Option','wp-autopost'); ?></h3>
  <div class="inside">
	 <table width="100%"> 	         	  
       <tr> 
		 <td width="20%"><?php echo __('Update Method','wp-autopost'); ?>:</td>
		 <td><div>
		   <?php $updateMethod = get_option('wp_autopost_updateMethod');  ?>
		   <select name="updateMethod" id="updateMethod" onchange="showCron(this.value)">
		     <option value="0" <?php if($updateMethod==0)  echo 'selected="true"'; ?> >  
			   <?php echo __('Automatically check for updates after pages load','wp-autopost'); ?>
			 </option>
			 <option value="1" <?php if($updateMethod==1)  echo 'selected="true"'; ?> >  
			   <?php echo __('Cron job or manual updates','wp-autopost'); ?>
			 </option>
		   </select>
		   </div>
		 </td>
	   </tr>
	   <tr>
         <td></td>
		 <td>
		 <div id="cron" <?php if($updateMethod!=1)echo 'style="display:none;"' ?> >
          <p><?php echo __('If you want to use a cron job, you can perform scheduled updates by sending regularly-scheduled requests to','wp-autopost'); ?>  <code><a href="<?php echo get_bloginfo('url');  ?>?update_autopost=1" target="_blank" ><?php echo get_bloginfo('url');  ?>?update_autopost=1</a></code> <?php echo __('For example, inserting the following line in your crontab:','wp-autopost'); ?></p>
		  <p><pre style="font-size: 0.80em"><code>*/10 * * * * /usr/bin/curl --silent <?php echo get_bloginfo('url');  ?>?update_autopost=1</code></pre></p>
          <p><?php echo __('will check in every 10 minutes and check for updates on any activated tasks that are ready to be polled for updates.','wp-autopost'); ?></p>
		 </div>
		 </td>
	   </tr>
	   <tr>
         <td width="20%"><?php echo __('Time Limit on Updates','wp-autopost'); ?>:</td>
		 <td><input type="text" name="timeLimit" id="timeLimit" value="<?php echo get_option('wp_autopost_timeLimit'); ?>" size="10" /> <?php echo __(' seconds','wp-autopost'); ?>
		 <span class="gray">( <?php echo __('Recommend the use of 0, which means that no time limit.','wp-autopost'); ?> )</span>
		 </td>
	   </tr>
       
	   <tr>
         <td width="20%"><?php echo __('How many tasks can run simultaneously','wp-autopost'); ?>:</td>
		 <td>
		  <?php $runOnlyOneTask = get_option('wp_autopost_runOnlyOneTask');  ?>
           <select name="runOnlyOneTask" >
             <option value="1" <?php if($runOnlyOneTask==1||$runOnlyOneTask==null)  echo 'selected="true"'; ?>> 1 </option>
			 <option value="0" <?php if($runOnlyOneTask==0&&$runOnlyOneTask!=null)  echo 'selected="true"'; ?>><?php echo __('Unlimited','wp-autopost'); ?></option>
		   </select>
		 <span class="gray">( <?php echo __('If Unlimited, may affect server performance','wp-autopost'); ?> )</span>
		 </td>
	   </tr>

	   <tr>
         <td width="20%"><?php echo __('Extraction Pause Time','wp-autopost'); ?>:</td>
		 <td><input type="text" name="pause_time" id="pause_time" value="<?php echo get_option('wp_autopost_pauseTime'); ?>" size="10" /> <?php echo __(' seconds','wp-autopost'); ?>
		 <span class="gray">( <?php echo __('After extracted one article, sleep some seconds then begin next extraction','wp-autopost'); ?> )</span>
		 </td>
	   </tr>

	   <tr>
         <td colspan="2">
		   <input type="submit" class="button-primary"  name="submit1"  value="<?php echo __('Save Changes'); ?>" />
		 </td>
	   </tr>
    </table>
  </div>
  </div>

  <div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Remote Images Download Option','wp-autopost'); ?></h3>
  <div class="inside">
	 <table width="100%"> 	         	  
	   <tr>
         <td width="260"><?php echo __('Min Width Image to Download','wp-autopost'); ?>:</td>
		 <td><input type="text" name="imgMinWidth" id="imgMinWidth" value="<?php echo get_option('wp_autopost_downImgMinWidth'); ?>" size="10" />  px
		 </td>
	   </tr>
	   
	   <tr>
         <td width="260"><?php echo __('Download timeout','wp-autopost'); ?>:</td>
		 <td><input type="text" name="downImgTimeOut" id="downImgTimeOut" value="<?php echo get_option('wp_autopost_downImgTimeOut'); ?>" size="10" /> <?php echo __(' seconds','wp-autopost'); ?>
		 </td>
	   </tr>

       
	   <tr>
         <td width="260"><?php echo __('Downloaded Images Use Relative URL','wp-autopost'); ?>:</td>
		 <td>
         <?php $downImgRelativeURL = get_option('wp_autopost_downImgRelativeURL'); ?>
		 <select name="downImgRelativeURL">
            <option value="0" <?php if($downImgRelativeURL==0)echo 'selected="true"'; ?> ><?php echo __('No'); ?></option>
			<option value="1" <?php if($downImgRelativeURL==1)echo 'selected="true"'; ?> ><?php echo __('Yes'); ?></option>
		 </select>
		 <span class="gray"><?php echo __('The downloaded images and save in the local server use relative URL','wp-autopost'); ?></span>
		 
		 </td>
	   </tr>

	   <tr>
         <td width="260"><?php echo __('When download images fails will not post','wp-autopost'); ?>:</td>
		 <td>
         <?php $downImgFailsNotPost = get_option('wp_autopost_downImgFailsNotPost'); ?>
		 <select name="downImgFailsNotPost">
            <option value="0" <?php if($downImgFailsNotPost==0)echo 'selected="true"'; ?> ><?php echo __('No'); ?></option>
			<option value="1" <?php if($downImgFailsNotPost==1)echo 'selected="true"'; ?> ><?php echo __('Yes'); ?></option>
		 </select>
		 
		 </td>
	   </tr>


	   <tr>
         <td width="260"><?php echo __('All downloaded images generate thumbnail','wp-autopost'); ?>:</td>
		 <td>
         <?php $downImgThumbnail = get_option('wp_autopost_downImgThumbnail'); ?>
		 <select name="downImgThumbnail">
            <option value="0" <?php if($downImgThumbnail==0)echo 'selected="true"'; ?> ><?php echo __('No'); ?></option>
			<option value="1" <?php if($downImgThumbnail==1)echo 'selected="true"'; ?> ><?php echo __('Yes'); ?></option>
		 </select>
		 <span class="gray"><?php echo __('Only take effect when choose save the images to wordpress media library','wp-autopost'); ?></span>
		 </td>
	   </tr>


	   <tr>
         <td width="260"><?php echo __('Downloaded Images Size Optimization','wp-autopost'); ?>:</td>
		 <td>
		   
		   <table>
             <tr>
               <td> <?php echo __('Max Width','wp-autopost'); ?></td>
			   <td>
			     <input type="text" name="downImgMaxWidth" id="downImgMaxWidth" value="<?php echo get_option('wp_autopost_downImgMaxWidth'); ?>" size="2" /> PX
		          <span class="gray">( <?php echo __("When the original image's width exceed this PX, will resize this image",'wp-autopost'); ?> )</span>
			   </td>
			 </tr>

             <tr>
               <td> <?php echo __('Jpeg quality','wp-autopost'); ?></td>
			   <td>
			     <input type="text" name="downImgQuality" id="downImgQuality" value="<?php echo get_option('wp_autopost_downImgQuality'); ?>" size="2" /> 
		         <span class="gray"><?php _e( 'ranges from 1 (worst quality, smaller file) to 100 (best quality, biggest file)', 'wp-autopost' );?></span>
			   </td>
			 </tr>

		   </table>
		   	 
		 </td>
	   </tr>


	   <tr>
         <td colspan="2">
         <?php 
		   $downFileOrganize = get_option('wp_autopost_downFileOrganize'); 
		 ?>
		 <p><?php echo __('Uploading Files'); ?>: </p>
		 <p><input type="radio" name="downFileOrganize" value="0" <?php if($downFileOrganize==0)echo 'checked="true"'; ?> /> <?php echo __('Organize my uploads into month- and year-based folders'); ?></p>
	   
		 <p><input type="radio" name="downFileOrganize" value="1" <?php if($downFileOrganize==1)echo 'checked="true"'; ?>/> <?php echo __('Organize my uploads into day- and month- and year-based folders','wp-autopost'); ?></p>		 
		 </td>
	   </tr>


	   <tr>
         <td colspan="2">
		   <input type="submit" class="button-primary"  name="submit2"  value="<?php echo __('Save Changes'); ?>" />
		 </td>
	   </tr>
    </table>
  </div>
  </div>
  
  
  <div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><a name="RemoteAttachmentDownloadOption"></a><?php echo __('Remote Attachment Download Option','wp-autopost'); ?></h3>
  <div class="inside">
    <?php 
	  $downloadTypes = get_option('wp_autopost_download_types');  
	  if($downloadTypes==null||$downloadTypes==''){
         $types = array('.zip','.rar','.pdf','.doc','.docx','.xls','.ppt');
		 $downloadTypes = json_encode($types);
		 update_option('wp_autopost_download_types', $downloadTypes);	 
	  }	  	 
	?>
    <table width="100%"> 	         	  
	   <tr>
         <td>
		    <?php echo __('The following match types can be downloaded','wp-autopost'); ?>
			<br/>
			<span class="gray"><?php echo __('You can add multiple match types, each begin at a new line','wp-autopost'); ?></span>
			<br/>
			<span class="gray">
			<?php echo __('For example','wp-autopost'); ?>: <i><b>.zip</b></i>&nbsp;&nbsp;<i><b>.doc</b></i>&nbsp;&nbsp;<i><b>attachment.php?aid=(*)</b></i>
			</span>
		    <textarea name="downloadTypes" id="downloadTypes" rows="16" style="width:100%"><?php 
			  
			  if( $downloadTypes!=NULL ){
				$downloadTypes = json_decode($downloadTypes);
				foreach($downloadTypes as $downloadType)echo $downloadType."\n"; 
			  
			  } 
			  
			  ?></textarea>
		 </td>
	   </tr>
	   <tr>
         <td colspan="2">
		   <input type="submit" class="button-primary"  name="submit4"  value="<?php echo __('Save Changes'); ?>" />
		 </td>
	   </tr>
    </table>
  </div>
  </div>

  <div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Other Option','wp-autopost'); ?></h3>
  <div class="inside">
	 <table> 	         	  
	   <tr>
         <td ><?php echo __('Automatically remove the HTML comments','wp-autopost'); ?>:</td>
		 <td>
		   <?php $delComment = get_option('wp_autopost_delComment'); if($delComment==null||$delComment=='')$delComment=1;  ?>
		   <select name="delComment" id="delComment">
		     <option value="1" <?php if($delComment==1)  echo 'selected="true"'; ?> >  
			   <?php echo __('Yes'); ?>
			 </option>
			 <option value="0" <?php if($delComment==0)  echo 'selected="true"'; ?> >  
			   <?php echo __('No'); ?>
			 </option>
		   </select>
		   <span class="gray">( <?php echo __('Remove html element like &lt!-- *** -->','wp-autopost'); ?> )</span>
		 </td>
	   </tr>
	  <!-- 
	   <tr>
         <td ><?php echo __('Automatically remove the empty HTML element','wp-autopost'); ?>:</td>
		 <td>
		   <?php $delEmptyTag = get_option('wp_autopost_delEmptyTag'); if($delEmptyTag==null||$delEmptyTag=='')$delEmptyTag=1;  ?>
		   <select name="delEmptyTag" id="delEmptyTag">
		     <option value="1" <?php if($delEmptyTag==1)  echo 'selected="true"'; ?> >  
			   <?php echo __('Yes'); ?>
			 </option>
			 <option value="0" <?php if($delEmptyTag==0)  echo 'selected="true"'; ?> >  
			   <?php echo __('No'); ?>
			 </option>
		   </select>
		   <span class="gray">( <?php echo __('Remove empty html element, like &lt;p> &lt;/p>','wp-autopost'); ?> )</span>
		 </td>
	   </tr>
      -->
	   <tr>
         <td ><?php echo __('Automatically remove the HTML ID attribute','wp-autopost'); ?>:</td>
		 <td>
		   <?php $delAttrId = get_option('wp_autopost_delAttrId'); if($delAttrId==null||$delAttrId=='')$delAttrId=1;  ?>
		   <select name="delAttrId" id="delAttrId">
		     <option value="1" <?php if($delAttrId==1)  echo 'selected="true"'; ?> >  
			   <?php echo __('Yes'); ?>
			 </option>
			 <option value="0" <?php if($delAttrId==0)  echo 'selected="true"'; ?> >  
			   <?php echo __('No'); ?>
			 </option>
		   </select>
		   <span class="gray">( <?php echo __('Remove html element like id=" *** "','wp-autopost'); ?> )</span>
		 </td>
	   </tr>
	   <tr>
         <td ><?php echo __('Automatically remove the HTML CLASS attribute','wp-autopost'); ?>:</td>
		 <td>
		   <?php $delAttrClass = get_option('wp_autopost_delAttrClass'); if($delAttrClass==null||$delAttrClass=='')$delAttrClass=1;  ?>
		   <select name="delAttrClass" id="delAttrClass">
		     <option value="1" <?php if($delAttrClass==1)  echo 'selected="true"'; ?> >  
			   <?php echo __('Yes'); ?>
			 </option>
			 <option value="0" <?php if($delAttrClass==0)  echo 'selected="true"'; ?> >  
			   <?php echo __('No'); ?>
			 </option>
		   </select>
		   <span class="gray">( <?php echo __('Remove html element like class=" *** "','wp-autopost'); ?> )</span>
		 </td>
	   </tr>
	   <tr>
         <td ><?php echo __('Automatically remove the HTML STYLE attribute','wp-autopost'); ?>:</td>
		 <td>
		   <?php $delAttrStyle = get_option('wp_autopost_delAttrStyle'); if($delAttrStyle==null||$delAttrStyle=='')$delAttrStyle=1;  ?>
		   <select name="delAttrStyle" id="delAttrStyle">
		     <option value="1" <?php if($delAttrStyle==1)  echo 'selected="true"'; ?> >  
			   <?php echo __('Yes'); ?>
			 </option>
			 <option value="0" <?php if($delAttrStyle==0)  echo 'selected="true"'; ?> >  
			   <?php echo __('No'); ?>
			 </option>
		   </select>
		   <span class="gray">( <?php echo __('Remove html element like style=" *** "','wp-autopost'); ?> )</span>
		 </td>
	   </tr>
	   <tr>
         <td colspan="2">
		   <input type="submit" class="button-primary"  name="submit3"  value="<?php echo __('Save Changes'); ?>" />
		 </td>
	   </tr>
    </table>
  </div>
  </div>

  

 </form>
</div>

<script type="text/javascript">
function  showCron(v){
 if(v==1)jQuery('#cron').show('fast');else jQuery('#cron').hide();
}
</script>