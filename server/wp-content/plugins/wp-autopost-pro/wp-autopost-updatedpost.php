<?php 
global $t_ap_config,$t_ap_updated_record;
$saction = @$_REQUEST['saction'];
?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2>Auto Post - <?php echo __('Posts'); ?><a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=new" class="add-new-h2"><?php echo __('Add New Task','wp-autopost'); ?></a> </h2>
<?php 
if($saction=='emptyData'){
 set_time_limit(0);
 $condition = '';
 $url_status=$_REQUEST['url_status'];
 
 if($url_status=='notexist'){

   $ids = $wpdb->get_results('SELECT t1.id FROM '.$t_ap_updated_record.' t1 where t1.post_id >0 AND not exists (select * from '.$wpdb->posts.' t2 where t2.ID = t1.post_id)');
   
   $num=0;
   foreach($ids as $the_id){
     $num++;
	 $wpdb->query('DELETE FROM '.$t_ap_updated_record. ' WHERE id='.$the_id->id );
   }

 }else{

  
 if($url_status=='')$url_status=-2;
 if($url_status==-2)$condition.='';
 else $condition.=' AND url_status ='.$url_status;
 
 if($_POST['trash_post']==0){
   if($_POST['taskId']==0){
      $num = $wpdb->query('DELETE FROM '.$t_ap_updated_record. ' WHERE date_time < '.(current_time('timestamp')-$_POST['rdays']*24*60*60).$condition);
   }else{
      $num = $wpdb->query('DELETE FROM '.$t_ap_updated_record. ' WHERE config_id = '.$_POST['taskId'].' AND date_time < '.(current_time('timestamp')-$_POST['rdays']*24*60*60).$condition);
   }
 }else{
   if($_POST['taskId']==0){    
	 $posts = $wpdb->get_results('SELECT post_id,config_id FROM '.$t_ap_updated_record.' WHERE date_time < '.(current_time('timestamp')-$_POST['rdays']*24*60*60).$condition);
	 foreach($posts as $post){
	   if(($post->post_id)>0){
	     echo '<p>begin delete post #'.$post->post_id.'</p>';@ob_flush();flush();
		 wp_trash_post($post->post_id);
		 $wpdb->query('UPDATE '.$t_ap_config. ' SET  updated_num = updated_num - 1 WHERE id = '.($post->config_id) );
	     wp_autopost_remove_post_img($post->post_id);
	   }
	 }  
	 $num = $wpdb->query('DELETE FROM '.$t_ap_updated_record. ' WHERE date_time < '.(current_time('timestamp')-$_POST['rdays']*24*60*60).$condition); 
	
   }else{
     $posts = $wpdb->get_results('SELECT post_id,config_id FROM '.$t_ap_updated_record.' WHERE config_id = '.$_POST['taskId'].' AND date_time < '.(current_time('timestamp')-$_POST['rdays']*24*60*60).$condition);
	 foreach($posts as $post){
	   if(($post->post_id)>0){
	     echo '<p>begin delete post #'.$post->post_id.'</p>';@ob_flush();@flush();
		 wp_trash_post($post->post_id);
	     $wpdb->query('UPDATE '.$t_ap_config. ' SET  updated_num = updated_num - 1 WHERE id = '.($post->config_id) );
		 wp_autopost_remove_post_img($post->post_id);
	   }
	 } 
	 $num = $wpdb->query('DELETE FROM '.$t_ap_updated_record. ' WHERE config_id = '.$_POST['taskId'].' AND date_time < '.(current_time('timestamp')-$_POST['rdays']*24*60*60).$condition);
	 
   }
 }

 }// end if($url_status=='notexist'){

 $wpdb->get_row( 'OPTIMIZE TABLE '.$t_ap_updated_record);
  
  echo '<div class="updated fade"><p>'.$num.' '.__('items permanently deleted.','wp-autopost').'</p></div>';
}

$duplicate = @$_GET['duplicate'];

if($saction=='queryDuplicate'){
  echo '<div class="updated fade"><p><b>'.__('Being processed, the processing may take some time, you can close the page','wp-autopost').'</b></p></div>';ob_flush();flush();
  
  if($_POST['query_days']>0){
     $posts = $wpdb->get_results('SELECT id,title FROM '.$t_ap_updated_record.' WHERE date_time > '.(current_time('timestamp')-$_POST['query_days']*24*60*60).' AND url_status=1');
  }else{
     $posts = $wpdb->get_results('SELECT id,title FROM '.$t_ap_updated_record.' WHERE url_status=1');
  }
  queryDuplicate($_POST['similar_percent'],$posts);
  
  $duplicate = 'show';
}

$duplicateIds = get_option('wp-autopost-duplicate-ids');

if($saction=='emptyDuplicate'){
  $duplicateIds = null;
  update_option('wp-autopost-duplicate-ids',$duplicateIds);
  $duplicate = 'show';
}

if($saction=='abortDuplicate'){
  update_option('wp-autopost-run-query-duplicate',0);
  $duplicate = 'show';
}

if($saction=='delete'){
  set_time_limit(0);
  
  $ids = $_POST['ids'];
  if($ids!=null){
     
    if($_POST['is_duplicate']==1){  // 删除重复的文章，不删除t_ap_updated_record记录
      if($_POST['trash_post_bulkAction']==1){
        foreach($ids  as $id ){
		  $row = $wpdb->get_row('SELECT post_id,config_id,url_status FROM '.$t_ap_updated_record.' WHERE id = '. $id );
		  if(($row->url_status)==1){
            echo '<p>begin delete post #'.$row->post_id.'</p>';@ob_flush();flush();
			//wp_trash_post($row->post_id);
			wp_autopost_remove_post_img($row->post_id);
			wp_delete_post($row->post_id,true);
			$wpdb->query('UPDATE '.$t_ap_config. ' SET  updated_num = updated_num - 1 WHERE id = '.($row->config_id) );
			$wpdb->query('UPDATE '.$t_ap_updated_record. ' SET url_status = -2 WHERE id = '.$id );
		  } 
		}   
	  }
      $duplicate = 'show';
	}else{ //if($_POST['is_duplicate']==1){

	 if(@($_POST['trash_post_bulkAction']==0)){
	   foreach($ids  as $id ){       
		  $wpdb->query('DELETE FROM '.$t_ap_updated_record. ' WHERE id = '.$id );       
	   }
	 }else{
       foreach($ids  as $id ){
          $row = $wpdb->get_row('SELECT post_id,config_id,url_status FROM '.$t_ap_updated_record.' WHERE id = '. $id );
		  if(($row->url_status)==1){
			  echo '<p>begin delete post #'.$row->post_id.'</p>';@ob_flush();flush();
			  wp_trash_post($row->post_id);
			  wp_autopost_remove_post_img($row->post_id);
		  }
		  $wpdb->query('DELETE FROM '.$t_ap_updated_record. ' WHERE id = '.$id );
          if(($row->url_status)==1){
            $wpdb->query('UPDATE '.$t_ap_config. ' SET  updated_num = updated_num - 1 WHERE id = '.($row->config_id) );
		  }
       }
	 }
	 echo '<div class="updated fade"><p>'.count($ids).' '.__('items permanently deleted.','wp-autopost').'</p></div>';
    }//end if($_POST['is_duplicate']==1){
  }// end if($ids!=null){
}

if($saction=='extraction'){
  extractionUrls($_POST['ids']);
}

if($saction=='ignore'){
   $i=0;
   if($_POST['ids']!=NULL)
   foreach($_POST['ids']  as $id ){
      $re = $wpdb->query('UPDATE '.$t_ap_updated_record. ' SET  url_status = - 1 WHERE id = '.$id.' AND url_status = 0' );
	  if($re==1)$i++;
   }
   echo '<div class="updated fade"><p>'.$i.' '.__('items updated.','wp-autopost').'</p></div>';
}

if( isset($_REQUEST['extractionId']) && $_REQUEST['extractionId']>0){
  extractionUrl($_REQUEST['extractionId']);
}

$condition ='';
$taskId=@$_REQUEST['taskId'];
if($taskId>0)$condition .=' AND t1.config_id = '.$taskId;
if($taskId=='null')$condition .=' AND t2.id is null';
else $condition .='';


$url_status=@$_REQUEST['url_status'];

if($url_status=='')$url_status=-2;

if($url_status==-2)$condition.='';
else $condition.=' AND t1.url_status ='.$url_status;


//同步已更新文章数量
if($taskId>0&&$url_status==1){
  $syncUpdated=true;
}


if(isset($_POST['filter']) && $_POST['filter']!=''){
  $_POST['s']='';
}
if(isset($_POST['s']) && $_POST['s']!=''){
  $condition =' AND ( title like "%'.$_POST['s'].'%" or url="'.$_POST['s'].'" )';
}

if($duplicate=='show'){
  $queryIds = '';
  if($duplicateIds!=''&&$duplicateIds!=null){
    foreach($duplicateIds  as $id ){ $queryIds .= $id.',';}
    $queryIds=substr($queryIds, 0, -1);
  }else{ $queryIds = 0; }
  $condition =' AND t1.id in ('.$queryIds.')';
}

if(!isset($_REQUEST['paged'])){ 
  $page = 1; 
} else { 
  $page = $_REQUEST['paged']; 
}
$perPage=30;
// Figure out the limit for the query based on the current page number. 
$from = (($page * $perPage) - $perPage);
  
if($url_status=='notexist'){
  $total = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' t1 where t1.post_id >0 AND not exists (select * from '.$wpdb->posts.' t2 where t2.ID = t1.post_id)');
  $total_pages = ceil($total / $perPage);

  $logs = $wpdb->get_results('SELECT t1.*,t3.name FROM '.$t_ap_updated_record.' t1 left join '.$t_ap_config.' t3 on t1.config_id = t3.id where t1.post_id >0 AND not exists (select * from '.$wpdb->posts.' t2 where t2.ID = t1.post_id) ORDER BY t1.id DESC LIMIT '.$from.','.$perPage);

}else{
  $total = $wpdb->get_var('SELECT count(t1.id) FROM '.$t_ap_updated_record.' t1 left join '.$t_ap_config.' t2 on t1.config_id = t2.id WHERE 1=1  '.$condition);
  $total_pages = ceil($total / $perPage);

  if($duplicate=='show'){
    $logs = $wpdb->get_results('SELECT t1.*,t2.name FROM '.$t_ap_updated_record.' t1 left join '.$t_ap_config.' t2 on t1.config_id = t2.id WHERE 1=1 '.$condition.' ORDER BY t1.title DESC LIMIT '.$from.','.$perPage);
  }else{
    $logs = $wpdb->get_results('SELECT t1.*,t2.name FROM '.$t_ap_updated_record.' t1 left join '.$t_ap_config.' t2 on t1.config_id = t2.id WHERE 1=1 '.$condition.' ORDER BY t1.id DESC LIMIT '.$from.','.$perPage);
  }
}

if(@$syncUpdated){
   $wpdb->query('UPDATE '.$t_ap_config.' SET updated_num = '.$total.' WHERE id = '.$taskId );
}

// select t1.id,t2.id from wp_ap_updated_record t1 left join wp_ap_config t2 on t1.config_id = t2.id
?>



<script type="text/javascript">
function emptyData(){
  if(document.getElementById("rdays").value==0&&document.getElementById("trash_post").value==0){
    if(confirm("<?php echo __('This operation may cause duplicate publish, continue?','wp-autopost'); ?>")){ 
      document.getElementById("saction").value="emptyData";
      document.getElementById("myform").submit();
	}else return false; 
  }else if(document.getElementById("trash_post").value==1){
    if(confirm("<?php echo __('Confirm Delete?','wp-autopost'); ?>")){ 
      document.getElementById("saction").value="emptyData";
      document.getElementById("myform").submit();
	}else return false;
  }else{
    document.getElementById("saction").value="emptyData";
    document.getElementById("myform").submit();
  }
}
function dobulkAction(){
  document.getElementById("saction").value=document.getElementById("bulkAction").value;
  document.getElementById("myform").submit();
}
function emptyDuplicate(){
  document.getElementById("saction").value="emptyDuplicate";
  document.getElementById("myform").submit();
}
function queryDuplicate(){
  if(confirm("<?php echo __('This operation may consume large amounts of memory and time, continue?','wp-autopost'); ?>")){ 
    document.getElementById("saction").value="queryDuplicate";
    document.getElementById("myform").submit();
  }else return false; 
}
jQuery(document).ready(function($){
 <?php if($url_status==-2||$url_status==1){ ?>   
  $('#bulkAction').change(function(){
	 var sSwitch = $(this).val();
	 if(sSwitch == 'delete'){
           $("#trash_post_bulkAction").show();
	 }else{
           $("#trash_post_bulkAction").hide();
	 }
  });
 
  $('#trash_post_bulkAction').change(function(){
	 var sSwitch = $(this).val();
	 if(sSwitch == 0){
       if(confirm("<?php echo __('This operation may cause duplicate publish, continue?','wp-autopost'); ?>")){ 
         ; 
	   }else{
		  document.getElementById("trash_post_bulkAction").value=1; 
	   }
	 }
  });
<?php } ?>
  
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

 <form id="myform" method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php" >
 <input type="hidden" name="saction" id="saction" value="" />
 
 <input type="hidden" name="is_duplicate" id="is_duplicate" value="<?php if($duplicate=='show'){ echo '1';  } ?>" /> 
 
 <?php
  $AllNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record);
  $PublishedNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' WHERE url_status = 1');
  $PendingNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' WHERE url_status = 0');
  $IgnoredNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' WHERE url_status = -1');
  
  $NotExistNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' t1 where t1.post_id >0 AND not exists (select * from '.$wpdb->posts.' t2 where t2.ID = t1.post_id)');
 
  $DuplicateNum = 0;
  if($duplicateIds!=''&&$duplicateIds!=null){
     $queryIds = '';
     if($duplicateIds!=''&&$duplicateIds!=null){
       foreach($duplicateIds  as $id ){ $queryIds .= $id.',';}
       $queryIds=substr($queryIds, 0, -1);
     }else{ $queryIds = 0; }
    $DuplicateNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' WHERE id in ('.$queryIds.')');
  }
 ?>
 <ul class='subsubsub'>
	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php" <?php if($url_status==-2&&(!$taskId>0)&&$duplicate!='show')echo 'class="current"';?> ><?php echo __('All'); ?> <span class="count">(<?php echo number_format($AllNum);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&url_status=1" <?php if($url_status==1&&(!$taskId>0))echo 'class="current"';?> ><?php echo __('Published'); ?> <span class="count">(<?php echo number_format($PublishedNum);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&url_status=0" <?php if($url_status==0&&$url_status!='notexist'&&(!$taskId>0))echo 'class="current"';?> ><?php echo __('Pending Extraction','wp-autopost'); ?> <span class="count">(<?php echo number_format($PendingNum);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&url_status=-1" <?php if($url_status==-1&&(!$taskId>0))echo 'class="current"';?> ><?php echo __('Ignored','wp-autopost'); ?> <span class="count">(<?php echo number_format($IgnoredNum);?>)</span>  |</a></li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&url_status=notexist" <?php if($url_status=='notexist'&&(!$taskId>0))echo 'class="current"';?> ><?php echo __('Not Exists','wp-autopost'); ?> <span class="count">(<?php echo number_format($NotExistNum);?>)</span>  |</a></li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&duplicate=show" <?php if($duplicate=='show')echo 'class="current"';?> ><?php echo __('Duplicate Posts','wp-autopost'); ?><?php if($DuplicateNum>0){ ?> <span class="count">(<?php echo number_format($DuplicateNum);?>)</span><?php } ?></a></li>
 </ul>
 
<?php if($duplicate!='show'){ ?>
 <p class="search-box">
	<label class="screen-reader-text" for="post-search-input"><?php echo __('Search'); ?>:</label>
	<input type="search" id="search-input" name="s" size="40" value="<?php if(isset($_POST['s']))echo $_POST['s']; ?>" />
	<input type="submit" name="" id="search-submit" class="button" value="<?php echo __('Search'); ?>"  />
 </p>
<?php } ?>

 <div class="tablenav">
  <div class="alignleft actions">
    <select name="bulkAction" id="bulkAction">
       <option value="-1" selected="selected"><?php echo __('Bulk Actions'); ?></option>
<?php if($url_status==0 || $url_status==-2){ ?>
       <option value="extraction"><?php echo __('Extraction and post','wp-autopost'); ?></option>
	   <option value="ignore"><?php echo __('Ignore','wp-autopost'); ?></option>
<?php } ?>  
<?php if($url_status==-1){ ?>
       
<?php } ?> 	   
	   <option value="delete"><?php echo __('Delete'); ?></option>
	</select>
<?php if($url_status==-2||$url_status==1){ ?>    
	<select name="trash_post_bulkAction" id="trash_post_bulkAction" style="display:none;" >
	  <option value="1" selected="true" ><?php echo __('Delete posts simultaneously','wp-autopost'); ?></option>
	  <option value="0"><?php echo __('Do not delete posts','wp-autopost'); ?></option>
	</select>
<?php }elseif($url_status=='notexist'){  ?>
      <input type="hidden" name="trash_post_bulkAction" value="1" /> 
<?php } ?>
	<input type="button" name="" class="button action" value="<?php echo __('Apply'); ?>"  onclick="dobulkAction()"/>
  </div>

<?php if($duplicate!='show'){ ?>
  <div class="alignleft actions">
	<?php $tasks = $wpdb->get_results('SELECT id,name FROM '.$t_ap_config.' ORDER BY id'); ?>
	<select name="taskId" id="taskId">
       <option value="0"><?php echo __('View all tasks','wp-autopost'); ?></option>
 <?php foreach ($tasks as $task) {  ?> 
	   <option value="<?php echo $task->id; ?>" <?php if($taskId==($task->id))echo 'selected="true"'; ?>><?php echo $task->name;  ?></option>
 <?php } ?>
       <option value="null" <?php if($taskId=='null')echo 'selected="true"'; ?>><?php echo __('Has been deleted','wp-autopost'); ?></option>
	</select>
    
	<select name="url_status" id="url_status">
      <option value="-2" <?php if($url_status == -2)echo 'selected="true"';  ?>><?php echo __('View all status','wp-autopost'); ?></option>
	  <option value="1" <?php if($url_status == 1)echo 'selected="true"';  ?>><?php echo __('Published'); ?></option>
	  <option value="0" <?php if($url_status == 0)echo 'selected="true"';  ?>><?php echo __('Pending Extraction','wp-autopost'); ?></option>
	  <option value="-1" <?php if($url_status == -1)echo 'selected="true"';  ?>><?php echo __('Ignored','wp-autopost'); ?></option>
      <option value="notexist" <?php if($url_status == 'notexist')echo 'selected="true"';  ?>><?php echo __('Not Exists','wp-autopost'); ?></option>
	</select>	
	<input type="submit" name="filter" class="button" value="<?php echo __('Filter'); ?>"  />
  </div>
<?php }else{ ?>
  <div class="alignleft actions">
    <select name="query_days" > 
	  <option value="30"><?php echo __('Query the posts of last 30 days','wp-autopost'); ?></option>
	  <option value="60"><?php echo __('Query the posts of last 60 days','wp-autopost'); ?></option>
	  <option value="90"><?php echo __('Query the posts of last 90 days','wp-autopost'); ?></option>
	  <option value="0"><?php echo __('Query all posts','wp-autopost'); ?></option>
	</select>
    <?php echo __('Similar Percent','wp-autopost'); ?>:<input type="text" name="similar_percent" size="2" value="90" />%
  
   <?php 
   $runDuplicate = get_option('wp-autopost-run-query-duplicate'); 
   if($runDuplicate!=1){ ?>
     &nbsp;&nbsp;
	 <input value="<?php echo __('Query Duplicate Posts','wp-autopost'); ?>" class="button-primary" type="button" onclick="queryDuplicate()"/>
   <?php }else{ ?>
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	 <?php echo __('Is running','wp-autopost'); ?> <img src="<?php echo $wp_autopost_root; ?>images/running.gif" width="15" height="15" style="vertical-align:text-bottom;" />
     <a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&saction=abortDuplicate" class="button-primary" ><?php echo __('Abort','wp-autopost'); ?></a>	
   <?php } ?>
  </div>
<?php } ?>

<?php if($duplicate!='show'){ ?>
  <div class="alignright">
    <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	<select name="rdays" id="rdays">
	  <option value="90"><?php echo __('Retain the data of last 90 days','wp-autopost'); ?></option>
	  <option value="60"><?php echo __('Retain the data of last 60 days','wp-autopost'); ?></option>
	  <option value="30"><?php echo __('Retain the data of last 30 days','wp-autopost'); ?></option>
	  <option value="0"><?php echo __('Does not retain any data','wp-autopost'); ?></option>
	</select>
	<select name="trash_post" id="trash_post">
	  <option value="0"><?php echo __('Do not delete posts','wp-autopost'); ?></option>
	  <option value="1"><?php echo __('Delete posts simultaneously','wp-autopost'); ?></option>
	</select>
	<input value="<?php echo __('Delete'); ?>" class="button" type="button" onclick="emptyData()"/>
  </div>
<?php }else{ ?>
  <div class="alignright">
    <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
    <input value="<?php echo __('Empty Result','wp-autopost'); ?>" class="button" type="button" onclick="emptyDuplicate()"/>
  </div>
<?php } ?>
 </div>
 <table class="widefat tablehover"  style="margin-top:4px"> 
  <thead>
   <tr>
    <th scope="col" class='manage-column column-cb check-column'><input type="checkbox" name="All" id="checkAll" ></th>
    <th scope="col" ><?php echo __('Task Name','wp-autopost'); ?></th>
    <th scope="col" width="300"><?php echo __('Source URL','wp-autopost'); ?></th>
    <th scope="col" ><?php echo __('Title','wp-autopost'); ?></th>
	<th scope="col" ><?php echo __('Date'); ?></th>
	<th scope="col" ><?php echo __('Status'); ?></th>
   </tr>
  </thead>   
  <tbody id="the-list">
 <?php $rowNum=0;
  foreach ($logs as $log) { $rowNum++; ?>

  <?php
   if($url_status=='notexist'){ 
	 if($log->url_status==1){
	   echo '$log->url_status'.$log->url_status;
       $wpdb->query('UPDATE '.$t_ap_updated_record. ' SET url_status = -2 WHERE id = '.$log->id );  
	 }
   }
  ?>

   <tr  class="row <?php if($log->url_status==-1)echo 'lightRed'; ?> <?php if($log->url_status==0)echo 'alternate'; ?>"  id="row<?php echo $log->id; ?>"  >
	<td>
	  <input type="checkbox" name="ids[]" value="<?php echo $log->id; ?>" class="checkrow" />
	</td>
	<td>
	<?php if($log->name!=null){ ?>
	   <a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&taskId=<?php echo $log->config_id; ?>"><?php echo $log->name; ?></a>
	<?php }else{ ?>
	   <span class="trash"><a class="red" href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&taskId=null"><?php echo __('Has been deleted','wp-autopost'); ?></a></span>
	<?php } ?>
	</td>
	<td>
	  <a href="<?php echo $log->url; ?>" target="_blank" title="<?php echo $log->url; ?>"><?php  echo substr($log->url,0,40).((strlen($log->url)>40)?'...':''); ?></a>	
	</td>
	<td>
	  <?php if(($log->url_status)==1 || ($log->url_status)==-2){ ?>
       <?php
         $postTitle = get_the_title($log->post_id);
         if($postTitle!=null){
		   $postTitle = '<strong><a href="'.get_permalink($log->post_id).'" target="_blank">'.$postTitle.'</a></strong>';
		 }else{
           $postTitle = '[ <span class="trash"><a class="red" href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&url_status=notexist" ><b>'.__('Not Exists','wp-autopost').'</b></a></span> ] '.$log->title;
		 }
	   ?>
	   <?php echo $postTitle; ?>	  
	  <?php }elseif(($log->url_status)==0){ ?>
       <span class="gray"><?php echo $log->title; ?></span>
	  <?php }elseif(($log->url_status)==-1){ ?>
       <span class="gray"><i><?php echo $log->title; ?></i></span>
      <?php } ?>
	</td>
	<td><?php echo date('Y-m-d H:i:s',$log->date_time); ?></td>
	<td><strong>
	<?php  
	  switch($log->url_status){
	    case 0: $status='<a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&extractionId='.$log->id.'" title="'.__('Extraction and post','wp-autopost').'">'.__('Pending Extraction','wp-autopost').'</a>'; break;
		case 1: $status=__('Published').'</a>'; break;
		case -1:$status='<i>'.__('Ignored','wp-autopost').'</i>'; break;
		case -2:$status='<i>'.__('Not Exists','wp-autopost').'</i>'; break;
	  }
	  echo $status;
	?></strong>
	</td>
   </tr>
<?php } ?>
  </tbody>
 </table>
 </form>
 <div class="tablenav">
      <div class="tablenav-pages alignright">
	  <span class="displaying-num"><?php echo number_format($total);?> <?php echo __('items'); ?></span>
	   <?php
					if ($total_pages>1) {
		                $arr_params = array();
                        $arr_params['page'] ='wp-autopost-pro/wp-autopost-updatedpost.php';						
						$arr_params['paged'] = '%#%';
						$arr_params['taskId'] = $taskId;				
					    if($duplicate=='show'){
                          $arr_params['duplicate'] = $duplicate;
					    }else{
                          $arr_params['url_status'] = $url_status;
					    }
						@$query_page = add_query_arg( $arr_params , $query_page );				
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
</div>