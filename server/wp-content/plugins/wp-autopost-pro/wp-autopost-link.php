<?php global $t_autolink;?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2>Auto Link <a href="admin.php?page=wp-autopost-pro/wp-autopost-link.php&saction=new" class="add-new-h2"><?php echo __('Add New Keyword','wp-autopost'); ?></a> </h2>


<?php 
$saction = @$_REQUEST['saction'];
switch($saction){
 case 'new':
 case 'edit':
?>
<script type="text/javascript">
function addNew(){
  if(document.getElementById("Keyword").value=='' || document.getElementById("Link").value==''){
	 alert("<?php echo __('Please enter both a keyword and URL','wp-autopost'); ?>");
	 return;
  }
  document.getElementById("myform").submit();
}
</script>

<form id="myform"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-link.php" > 
<?php
if($saction=='edit'){
  $autolink = $wpdb->get_row('SELECT * FROM '.$t_autolink.' WHERE id = '.$_REQUEST['id'] );
  list($Link,$Description,$NoFollow,$NewWindow,$FirstMatchOnly,$Ignorecase,$WholeWord) = explode("|",$autolink->details);
?>
<input type="hidden" name="tid" id="tid" value="<?php echo $_REQUEST['id']; ?>">
<input type="hidden" name="saction" id="saction" value="saveKeyword">
<?php }else{ ?>
<input type="hidden" name="saction" id="saction" value="newKeyword">
 <?php } ?> 
  <br/> 
  <table> 	   
       <tbody id="the-list">         	  
       <tr> 
		 <td width="10%"><?php echo __('Keyword','wp-autopost'); ?>:</td>
		 <td><input type="text" name="Keyword" id="Keyword" value="<?php if(isset($autolink->keyword))echo $autolink->keyword; ?>"> * </td>
	   </tr>
	   <tr> 
		 <td width="10%"><?php echo __('Link','wp-autopost'); ?>:</td>
		 <td><input type="text" name="Link" id="Link" value="<?php if(isset($Link))echo $Link; ?>" size="100"> * </td>
	   </tr>
	   <tr> 
		 <td width="10%"><?php echo __('Description','wp-autopost'); ?>:</td>
		 <td><input type="text" name="Description" id="Description" value="<?php if(isset($Description))echo $Description; ?>" size="50"></td>
	   </tr>
       <tr>
	     <td></td>
         <td>  
		    <input type="checkbox" name="NoFollow"  value="1" <?php if(isset($NoFollow)&&$NoFollow==1)echo 'checked'; ?> /> <?php echo __('No Follow','wp-autopost'); ?> <a title='<?php echo __('This adds a rel= "nofollow" to the link.','wp-autopost'); ?>'>[?]</a>
		 </td>
	   </tr>
	   <tr>
	     <td></td>
         <td>  
		    <input type="checkbox" name="NewWindow"  value="1" <?php if(isset($NewWindow)&&$NewWindow==1)echo 'checked'; ?> /> <?php echo __('New Window','wp-autopost'); ?> <a title='<?php echo __('This adds a target="_blank" to the link, forcing a new browser window on clicking.','wp-autopost'); ?>'>[?]</a>
		 </td>
	   </tr>
	   <tr>
	     <td></td>
         <td>  
		    <input type="checkbox" name="FirstMatchOnly"  value="1" <?php if(isset($FirstMatchOnly)&&$FirstMatchOnly==1)echo 'checked'; ?> /> <?php echo __('First Match Only','wp-autopost'); ?> <a title='<?php echo __('Only add links on the first matched.','wp-autopost'); ?>'>[?]</a>
		 </td>
	   </tr>
	   <tr>
	     <td></td>
         <td>  
		    <input type="checkbox" name="Ignorecase"  value="1" <?php if(isset($Ignorecase)&&$Ignorecase==1)echo 'checked'; ?> /> <?php echo __('Ignore Case','wp-autopost'); ?>
		 </td>
	   </tr>  
       <tr>
	     <td></td>
         <td>
		 <?php if($saction=='edit'){ ?>
		    <input type="checkbox" name="WholeWord"  value="1" <?php if(isset($WholeWord)&&$WholeWord==1)echo 'checked'; ?> /> <?php echo __('Match Whole Word','wp-autopost'); ?> 
		 <?php }else{ ?>
            <input type="checkbox" name="WholeWord"  value="1" <?php if(!(get_bloginfo('language')=='zh-CN'))echo 'checked'; ?> /> <?php echo __('Match Whole Word','wp-autopost'); ?>
		 <?php } ?>
         <?php if((get_bloginfo('language')=='zh-CN'))echo '(中文请勿勾选)'; ?>
		 <a title='<?php echo __('Match whole word only. For language split by "space", like English or other Latin languages.','wp-autopost'); ?>'>[?]</a>
		 </td>
	   </tr> 
	   </tbody>
  </table>
  <p class="submit"><input type="button" class="button-primary" value="<?php echo __('Submit'); ?>"  onclick="addNew()"/> <a href="admin.php?page=wp-autopost-pro/wp-autopost-link.php<?php if($_REQUEST['p']!=null) echo '&p='.$_REQUEST['p']; ?>" class="button"><?php echo __('Return','wp-autopost'); ?></a></p>
</form>


<?php
 break; // end case 'edit':
 case 'auto_link':
 
 $n = $_GET['n'];
 $pageNum=20;
 
 $autolinks = $wpdb->get_results('SELECT * FROM '.$t_autolink);

 // Get objects
 $objects = (array) $wpdb->get_results( $wpdb->prepare("SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' ORDER BY ID DESC LIMIT %d, %d", $n,$pageNum) );
 
 if( !empty($objects) ) {
	echo '<ul>';
	foreach( $objects as $object ) {
	  wpAutoPostLinkPost($object,$autolinks);						    
	  echo '<li>#'. $object->ID .' '. $object->post_title .'</li>';
	  unset($object);
	}
	echo '</ul>';
?>
	<p><?php _e("If your browser doesn't start loading the next page automatically click this link:", 'wp-autopost'); ?> <a href="admin.php?page=wp-autopost-pro/wp-autopost-link.php&saction=auto_link&n=<?php echo $n + $pageNum; ?>"><?php _e('Next content', 'wp-autopost'); ?></a></p>
	<script type="text/javascript">
	// <![CDATA[
	function nextPage() {
	  location.href = "admin.php?page=wp-autopost-pro/wp-autopost-link.php&saction=auto_link&n=<?php echo $n + $pageNum; ?>";
	}
	window.setTimeout( 'nextPage()', 300 );
	// ]]>
	</script>
<?php
 } else {
	echo '<p><strong>All done! </strong></p>';
 }
?>
  

<?php
 break;
 
 case 'newKeyword':
 case 'saveKeyword':
 case 'BatchDelete':
 case 'delete':	 
 default:
?>

<?php
if($saction=='newKeyword'){
  $Keyword = $_POST['Keyword'];
  $Link = $_POST['Link'];
  $Description = ($_POST['Description']=='')?$Keyword:$_POST['Description'];
  $NoFollow = @$_POST["NoFollow"]?$_POST["NoFollow"]:0;
  $NewWindow = @$_POST["NewWindow"]?$_POST["NewWindow"]:0;
  $FirstMatchOnly = @$_POST["FirstMatchOnly"]?$_POST["FirstMatchOnly"]:0;
  $Ignorecase = @$_POST["Ignorecase"]?$_POST["Ignorecase"]:0;
  $WholeWord = @$_POST["WholeWord"]?$_POST["WholeWord"]:0;
  
  $details=$Link.'|'.$Description.'|'.$NoFollow.'|'.$NewWindow.'|'.$FirstMatchOnly.'|'.$Ignorecase.'|'.$WholeWord;

  $wpdb->query("INSERT INTO $t_autolink(keyword,details) VALUES ( '$Keyword','$details')");
  
  echo '<div id="message" class="updated fade"><p>'.__('A new keyword has been created.','wp-autopost').'</p></div>';
}

if($saction=='saveKeyword'){
  $Keyword = $_POST['Keyword'];
  $Link = $_POST['Link'];
  $Description = ($_POST['Description']=='')?$Keyword:$_POST['Description'];
  $NoFollow = $_POST["NoFollow"]?$_POST["NoFollow"]:0;
  $NewWindow = $_POST["NewWindow"]?$_POST["NewWindow"]:0;
  $FirstMatchOnly = $_POST["FirstMatchOnly"]?$_POST["FirstMatchOnly"]:0;
  $Ignorecase = $_POST["Ignorecase"]?$_POST["Ignorecase"]:0;
  $WholeWord = $_POST["WholeWord"]?$_POST["WholeWord"]:0;
  
  $details=$Link.'|'.$Description.'|'.$NoFollow.'|'.$NewWindow.'|'.$FirstMatchOnly.'|'.$Ignorecase.'|'.$WholeWord;

  $wpdb->query("UPDATE $t_autolink SET keyword = '$Keyword', details='$details' WHERE id = ".$_POST['tid'] );
  
   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
}

if($saction=='delete'){
  $wpdb->query("DELETE FROM $t_autolink WHERE id = ".$_REQUEST['id'] );
  
  echo '<div id="message" class="updated fade"><p>'.__('Deleted!','wp-autopost').'</p></div>';
}
if($saction=='BatchDelete'){
   $ids = $_POST['ids']; 
   if($ids!=null)
   foreach($ids as $id){
     $wpdb->query("DELETE FROM $t_autolink WHERE id = ".$id );
   }
   echo '<div id="message" class="updated fade"><p>'.__('Deleted!','wp-autopost').'</p></div>';
}
?>

<p><?php echo __('Auto Link can automatically add links on keywords when publish post.','wp-autopost'); ?></p>
<form id="myform"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-link.php" >
  <input type="hidden" name="saction" id="saction" value="">
  
  <table class="widefat plugins"  style="margin-top:4px"> 
	<thead>
	  <tr>
	    <th class='manage-column column-cb check-column'><input type="checkbox" name="All" id="checkAll" onclick="checkAll('ids[]')"></th>
		<th scope="col" style="text-align:left" ><?php echo __('Keyword','wp-autopost'); ?></th>
		<th scope="col" style="text-align:left" ><?php echo __('Link','wp-autopost'); ?></th>
		<th scope="col" style="text-align:left" ><?php echo __('Description','wp-autopost'); ?></th>
		<th scope="col" style="text-align:left" ><?php echo __('Attributes','wp-autopost'); ?></th>
		<th scope="col" style="text-align:center" ></th>
	  </tr>
	</thead>   
    <tbody id="the-list">         
<?php
$perPage=20;
$total = $wpdb->get_var('SELECT count(*) FROM '.$t_autolink);
$total_pages = ceil($total / $perPage);	  

if(!isset($_REQUEST['p'])){ 
  $page = 1; 
} else { 
  $page = $_REQUEST['p']; 
}



if($saction=='newKeyword')$page = $total_pages;
if($page>$total_pages)$page = $total_pages;

// Figure out the limit for the query based on the current page number. 
$from = (($page * $perPage) - $perPage);

if( $from < 0 )$from=0;

$autoLinks = $wpdb->get_results('SELECT * FROM '.$t_autolink.' ORDER BY id LIMIT '.$from.','.$perPage); 
?>
<?php 
foreach ($autoLinks as $autoLink) {
	
	@$details=$Link.'|'.$Description.'|'.$NoFollow.'|'.$NewWindow.'|'.$FirstMatchOnly.'|'.$Ignorecase.'|'.$WholeWord;

    list($Link,$Description,$NoFollow,$NewWindow,$FirstMatchOnly,$Ignorecase,$WholeWord) = explode("|",$autoLink->details);
?>
     <tr>
	   <th scope='row' class='check-column'><input type="checkbox" name="ids[]" value="<?php echo $autoLink->id; ?>" class="checkrow" /></th>
	   <td>
	     <a href="admin.php?page=wp-autopost-pro/wp-autopost-link.php&saction=edit&id=<?php echo $autoLink->id; ?>&p=<?php echo $page; ?>" >
	      <?php echo $autoLink->keyword; ?>
		 </a>
	   </td>
	   <td> 
	     <a href="<?php echo $Link; ?>" target="_blank">
	      <?php echo $Link; ?>
		 </a>
	   </td>
	   <td> 
	      <?php echo $Description; ?>
	   </td>
	   <td>
	      <?php if($NoFollow==1){?>[<code><?php echo __('No Follow','wp-autopost'); ?></code>]<?php } ?>
		  <?php if($NewWindow==1){?>[<code><?php echo __('New Window','wp-autopost'); ?></code>]<?php } ?>
		  <?php if($FirstMatchOnly==1){?>[<code><?php echo __('First Match Only','wp-autopost'); ?></code>]<?php } ?>
		  <?php if($Ignorecase==1){?>[<code><?php echo __('Ignore Case','wp-autopost'); ?></code>]<?php } ?>
		  <?php if($WholeWord==1){?>[<code><?php echo __('Match Whole Word','wp-autopost'); ?></code>]<?php } ?>    
	   </td>
	   <td> 
	     <span class="trash"><a class="delete" href="admin.php?page=wp-autopost-pro/wp-autopost-link.php&saction=delete&id=<?php echo $autoLink->id; ?>&p=<?php echo $page; ?>" ><?php echo __('Delete'); ?></a></span>
	   </td>
 	 </tr>
<?php } ?>
    </tbody>
	<tfoot>
	   <tr style="text-align:center">  
		  <td colspan="6" style="text-align:left">
		  <input type="button" class="button-primary" value=" <?php echo __('Batch Delete','wp-autopost'); ?> "  onclick="BatchDelete()"/>
		  </td>
		</tr>    
    </tfoot>
  </table>
   <div class="tablenav">
      <div class="tablenav-pages alignright">
	   <?php
			// $total_pages=3;
		    // $page = 2;
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-link.php',  
						  'p' => "%#%"
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
   
    <h3><?php echo __('Auto links for tags','wp-autopost'); ?></h3>
    <p><?php echo __('When content contain the keyword of tag, add a link to the tag page.','wp-autopost'); ?></p>
    
    <?php
     if(isset($_POST['submit_tag_link']) && $_POST['submit_tag_link'] != null){
      
	   $autoLinkTag = array();
	   $autoLinkTag[0] = @$_POST['use_tag_link'];
       $autoLinkTag[1] = @$_POST['NoFollow'];
	   $autoLinkTag[2] = @$_POST['NewWindow'];
	   $autoLinkTag[3] = @$_POST['FirstMatchOnly'];
	   $autoLinkTag[4] = @$_POST['Ignorecase'];
	   $autoLinkTag[5] = @$_POST['WholeWord'];
       
	   update_option( 'wp-autopost-link-tag',$autoLinkTag);

	 }



      $autoLinkTag = get_option( 'wp-autopost-link-tag');

	?>
	<table>
	  <tbody id="the-list">         	  
       <tr> 
		 <td><?php echo __('Use Wordpress Tags Library','wp-autopost'); ?>:</td>
		 <td>
		   <select name="use_tag_link" id="use_tag_link">
             <option value="0" <?php if($autoLinkTag[0]==0||$autoLinkTag[0]==null) echo 'selected="true"'; ?> ><?php echo __('No'); ?></option>
			 <option value="1" <?php if($autoLinkTag[0]==1) echo 'selected="true"'; ?> ><?php echo __('Yes'); ?></option>
		   </select>
		  </td>
	   </tr>

	   <tr>
         <td>
         </td>
		 <td>
		  <div id="tag_link_attribute" <?php if($autoLinkTag[0]==0||$autoLinkTag[0]==null) echo 'style="display:none;"'   ?>>
           
		   <input type="checkbox" name="NoFollow"  value="1" <?php if($autoLinkTag[1]==1)echo 'checked'; ?> /> <?php echo __('No Follow','wp-autopost'); ?> <a title='<?php echo __('This adds a rel= "nofollow" to the link.','wp-autopost'); ?>'>[?]</a>
           <br/>
		   <input type="checkbox" name="NewWindow"  value="1" <?php if($autoLinkTag[2]==1)echo 'checked'; ?> /> <?php echo __('New Window','wp-autopost'); ?> <a title='<?php echo __('This adds a target="_blank" to the link, forcing a new browser window on clicking.','wp-autopost'); ?>'>[?]</a>
           <br/>
           <input type="checkbox" name="FirstMatchOnly"  value="1" <?php if($autoLinkTag[3]==1)echo 'checked'; ?> /> <?php echo __('First Match Only','wp-autopost'); ?> <a title='<?php echo __('Only add links on the first matched.','wp-autopost'); ?>'>[?]</a>
           <br/>
		   <input type="checkbox" name="Ignorecase"  value="1" <?php if($autoLinkTag[4]==1)echo 'checked'; ?> /> <?php echo __('Ignore Case','wp-autopost'); ?>
		   <br/>
		   <input type="checkbox" name="WholeWord"  value="1"  <?php if($autoLinkTag[5]==1)echo 'checked'; ?> /> <?php echo __('Match Whole Word','wp-autopost'); ?> <?php if((get_bloginfo('language')=='zh-CN'))echo '(中文请勿勾选)'; ?>
		   <a title='<?php echo __('Match whole word only. For language split by "space", like English or other Latin languages.','wp-autopost'); ?>'>[?]</a>
          </div>
		 </td>
	   </tr>

	</table>
	<p><input type="submit" class="button-primary" name="submit_tag_link" value="<?php echo __('Save Changes'); ?>" /></p>
   

  </form>



  <h3><?php echo __('Auto links old content','wp-autopost'); ?></h3>
  <p><?php echo __('Auto Link can also add keyword links all existing contents of your blog.','wp-autopost'); ?> <?php echo __('This feature use keyword list above-mentioned.','wp-autopost'); ?></p>
  <a class="button-primary" href="admin.php?page=wp-autopost-pro/wp-autopost-link.php&saction=auto_link&n=0"><?php echo __('Auto links all old content &raquo;','wp-autopost'); ?></a>

<script type="text/javascript">
function BatchDelete(){
  document.getElementById("saction").value="BatchDelete";
  document.getElementById("myform").submit();
}

		
function checkAll(str){   
  var a = document.getElementsByName(str);   
  var n = a.length;   
  for (var i=0; i<n; i++) a[i].checked = window.event.srcElement.checked;   
}

jQuery(document).ready(function($){  
   $('#use_tag_link').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
	       $("#tag_link_attribute").hide();
		}else{
           $("#tag_link_attribute").show();
		}
	});
});
</script>
<?php 
  break;
}// end switch($saction){
?>
</div>