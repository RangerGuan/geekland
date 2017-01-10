<?php global $t_ap_config,$t_ap_log;?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2>Auto Post - <?php echo __('Logs','wp-autopost'); ?><a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=new" class="add-new-h2"><?php echo __('Add New Task','wp-autopost'); ?></a> </h2>
<?php 

$saction = @$_POST['saction'];
if($saction=='emptyLogs'){
  $num = $wpdb->query('DELETE FROM '.$t_ap_log);
  $wpdb->get_row( 'OPTIMIZE TABLE '.$t_ap_log);
  $wpdb->query('UPDATE '.$t_ap_config.' SET last_error = 0');
  echo '<div class="updated fade"><p>'.$num.' '.__('items permanently deleted.','wp-autopost').'</p></div>';
}

$logId=@$_REQUEST['logId'];
$taskId=@$_REQUEST['taskId'];
if($logId>0)$condition=' AND t1.id = '.$logId;
else if($taskId>0)$condition=' AND t1.config_id = '.$taskId;
else $condition='';

if(!isset($_REQUEST['p'])){ 
  $page = 1; 
} else { 
  $page = $_REQUEST['p']; 
}
$perPage=15;
// Figure out the limit for the query based on the current page number. 
$from = (($page * $perPage) - $perPage);
$total = $wpdb->get_var('SELECT count(t1.id) FROM '.$t_ap_log.' t1,'.$t_ap_config.' t2 WHERE t1.config_id=t2.id '.$condition);
$total_pages = ceil($total / $perPage);
$logs = $wpdb->get_results('SELECT t1.*,t2.name FROM '.$t_ap_log.' t1,'.$t_ap_config.' t2 WHERE t1.config_id=t2.id '.$condition.' ORDER BY t1.id DESC LIMIT '.$from.','.$perPage); 
?>

<script type="text/javascript">
function queryTask(){
  document.getElementById("myform").submit();
}
function emptyLogs(){
 if(confirm("<?php echo __('Confirm Empty Logs?','wp-autopost'); ?>")){ 
   document.getElementById("saction").value="emptyLogs";
   document.getElementById("myform").submit();
 }else return false;  
}
</script>

 <form id="myform" method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-logs.php" >
 <input type="hidden" name="saction" id="saction" value="" />
 <div class="tablenav">
  <div class="alignleft actions">
	<?php $tasks = $wpdb->get_results('SELECT id,name FROM '.$t_ap_config.' ORDER BY id'); ?>
	<select name="taskId" id="taskId" onchange="queryTask()">
       <option value=""><?php echo __('View all tasks','wp-autopost'); ?></option>
<?php foreach ($tasks as $task) {  ?> 
	   <option value="<?php echo $task->id; ?>" <?php if($taskId==($task->id))echo 'selected="true"'; ?>><?php echo $task->name;  ?></option>
<?php } ?>
	</select>
  </div>
  <div class="alignright">
	<input value="<?php echo __('Empty Logs','wp-autopost'); ?>" class="button" type="button" onclick="emptyLogs()"/>
  </div>
 </div>
 </form>
 <table class="widefat"  style="margin-top:4px"> 
  <thead>
   <tr>
    <th scope="col" style="text-align:center"></th>
	<th scope="col" style="text-align:center"><?php echo __('Task Name','wp-autopost'); ?></th>
    <th scope="col" style="text-align:center"><?php echo __('Info','wp-autopost'); ?></th>
    <th scope="col" style="text-align:center"><?php echo __('Involved URL','wp-autopost'); ?></th>
   </tr>
  </thead>   
  <tbody id="the-list">
 <?php $rowNum=0;
  foreach ($logs as $log) { $rowNum++; ?>
   <tr style="text-align:center"  <?php if($rowNum%2==1){ ?> class="alternate" <?php } ?>  >
	<td><?php echo date('Y-m-d H:i:s',$log->date_time) ?></td>
    <td><?php echo $log->name; ?></td>
	<td><span class="red"><?php echo $log->info; ?></span></td>
	<td><a href="<?php echo $log->url; ?>" target="_blank"><?php echo $log->url; ?></a></td>
   </tr>
<?php } ?>
  </tbody>
 </table>
 <div class="tablenav">
      <div class="tablenav-pages alignright">
	   <?php
			// $total_pages=3;
		    // $page = 2;
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-logs.php',  
						  'p' => "%#%",
						  'taskId' => $taskId
						);
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
