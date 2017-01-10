<style>
.postbox h3 {
	font-family: Georgia, "Times New Roman", "Bitstream Charter", Times, serif;
	font-size: 15px;
	padding: 10px 10px;
	margin: 0;
	line-height: 1;
}
.apdelete{padding:2px;color:#aa0000;text-decoration:none;} 
.apdelete:hover{background-color:#aa0000;color:#ffffff;}/*#d54e21;*/
.apmatchtype{
  margin-top:6px;
  border-top-style: dashed;
  border-top-width: 1px;
  border-top-color: #21759b;
}
.autoposttable select{vertical-align:middle;}
.autoposttable label{vertical-align:middle;}
.autoposttable input{vertical-align:middle;}
.clickBold{color:#21759b;font-weight:bold;}
.clickBold:hover{color:#d54e21;font-weight:bold;cursor:pointer;}
.TipRss {
	padding: 5px;
	margin-right: 5px;
	font-size: 12px;
	font-weight: bold;
	background-color: #7ad03a;
	color: #FFFFFF;
}
#ap_content_html
{
width:100%;
height:500px;
}

.auto-set-button{
  color: #FFFFFF;
  background-color: #7ad03a;
  padding: 5px 8px 5px 8px;
  border-width: 1px; 
  border-color: #cccccc;
  border-style: solid;
  text-decoration: none;
}
.auto-set-select{
  color: #FFFFFF;
  background-color: #7ad03a;
  padding: 2px 8px 2px 8px;
  border-width: 1px; 
  border-color: #cccccc;
  border-style: solid;
  text-decoration: none;
}
.auto-set-button:hover,.auto-set-select:hover{
  color: #FFFFFF;
  cursor:pointer;
  border-color:#999999;
  background-color:#58ce01;
}
#default_image_area span{
  margin:6px;
  padding: 4px;
  display: block;
  float: left;
  width: 165px;
  height: 165px;
}
#default_image_area .default_imgs{
  padding: 2px;
  display: block;
  float: left;
  cursor:pointer;
}    
#default_image_area img{
  margin: auto;
  display: block;
}
#default_image_area .action{
  clear:both;
  padding:3px;
  text-align:center;
}
#default_image_area .action a{
  text-decoration: none;
}
.noselecedimg{
  border: 1px solid #CCCCCC;
}
.selectedimg{
  border: 4px solid #1e8cbe;
}
</style>
<?php 
global $t_ap_config,$t_ap_config_option,$t_ap_config_url_list,$t_ap_updated_record,$t_ap_log,$t_ap_more_content;?>
<?php 
@$id = $_REQUEST['id'];
?>

<?php 
@$saction = $_REQUEST['saction'];

switch($saction){
 case 'new':
?> 
<div class="wrap">
<div class="icon32" id="icon-wp-autopost"><br/></div>
<h2>Auto Post - New Task</h2>
<form id="myform"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php" > 
<input type="hidden" name="saction" id="saction" value="newConfig">
  <br/> 
  <table> 	   
       <tbody id="the-list">         	  
       <tr> 
		 <td width="10%"><?php echo __('Task Name','wp-autopost'); ?>:</td>
		 <td><input type="text" name="config_name" id="new_config_name" value="" size="60"></td>
	   </tr>
	   <tr> 
		 <td width="10%"><?php echo __('Copy Task','wp-autopost'); ?>:</td>
		 <td>
		 <?php
           $tasks = $wpdb->get_results('SELECT id,name FROM '.$t_ap_config.' ORDER BY id');
	     ?>
		  <select name="copy_task_id" id="copy_task_id">
		    <option value="0"><?php echo __('No'); ?></option>
			<?php foreach($tasks as $task){ ?>
               <option value="<?php echo $task->id; ?>"><?php echo $task->name; ?></option>
			<?php } ?>
		  </select>
		 </td>
	   </tr>
	   </tbody>
  </table>
  <p class="submit"><input type="button" class="button-primary" value="<?php echo __('Submit'); ?>"  onclick="addNew()"/> <a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php" class="button"><?php echo __('Return','wp-autopost'); ?></a></p>
</form>

</div>
<?php break;
 case 'newConfig':
 case 'edit':
 case 'save2':
 case 'save3':
 case 'save4':
 case 'save5':
 case 'testFetch':
 case 'save6':
 case 'save7':
 case 'save8':
 case 'save9':
 case 'save10':
 case 'save11':
 case 'save12':
 case 'save14':
 case 'save15':
 case 'save16':
 case 'save17':
 case 'testCookie':
 case 'editSubmit':
 case 'autoSetURL':
 case 'autoSetTitle':
 case 'cancelautoset':
 

 if($saction=='autoSetURL'){
   AutoSetNotice();	 
   autoSetURLMatchRule($_POST['id'],$_POST['targetURL'],$_POST['urls']);
 }

 if($saction=='autoSetTitle'){
   
   //echo htmlspecialchars($_POST['pageAutoRules']);
   AutoSetNotice();
   autoSetTtile($_POST['id'],$_POST['selector'],$_POST['selector_index'],$_POST['pageAutoRules']);
 }
 
 if($saction=='cancelautoset'){
   cancelautoset($id);
 }


 if($saction=='newConfig'){
  $config_name = $_POST['config_name'];
  $copy_task_id = $_POST['copy_task_id'];
  if($copy_task_id==0){
    $wpdb->query("insert into $t_ap_config(name) values ( '$config_name')");
    $id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
    $_REQUEST['p'] = 1;
  }else{
   //$old_task = $wpdb->get_row('SELECT * FROM '.$t_ap_config.' WHERE id ='.$copy_task_id );
   $wpdb->query('insert into '.$t_ap_config.' (name,m_extract,page_charset,content_test_url,a_match_type,title_match_type,content_match_type,page_match_type,a_selector,title_selector,content_selector,page_selector,fecth_paged,same_paged,source_type,start_num,end_num,title_prefix,title_suffix,content_prefix,content_suffix,cat,author,update_interval,published_interval,post_scheduled,download_img,img_insert_attachment,auto_tags,whole_word,tags,use_trans,use_rewrite,reverse_sort,add_source_url,proxy,post_type,post_format,check_duplicate,custom_field,err_status,cookie) select "'.$config_name.'", m_extract,page_charset,content_test_url,a_match_type,title_match_type,content_match_type,page_match_type,a_selector,title_selector,content_selector,page_selector,fecth_paged,same_paged,source_type,start_num,end_num,title_prefix,title_suffix,content_prefix,content_suffix,cat,author,update_interval,published_interval,post_scheduled,download_img,img_insert_attachment,auto_tags,whole_word,tags,use_trans,use_rewrite,reverse_sort,add_source_url,proxy,post_type,post_format,check_duplicate,custom_field,err_status,cookie from '.$t_ap_config.' where id='.$copy_task_id);
   
   $id = $wpdb->get_var("SELECT LAST_INSERT_ID()");

   //_ap_config_url_list
   $wpdb->query('insert into '.$t_ap_config_url_list.' (config_id,url) select '.$id.',url from '.$t_ap_config_url_list.' where config_id='.$copy_task_id );
   
   //_ap_config_option
   $wpdb->query('insert into '.$t_ap_config_option.' (config_id,option_type,para1,para2) select '.$id.',option_type,para1,para2 from '.$t_ap_config_option.' where config_id='.$copy_task_id );

   //_ap_more_content
   $wpdb->query('insert into '.$t_ap_more_content.' (config_id,option_type,content) select '.$id.',option_type,content from '.$t_ap_more_content.' where config_id='.$copy_task_id );
     
   $_REQUEST['p'] = 1;

  }
  echo '<div id="message" class="updated fade"><p>'.__('A new task has been created.','wp-autopost').'</p></div>';
}

 $config = $wpdb->get_row('SELECT * FROM '.$t_ap_config.' WHERE id ='.$id ); 
?>




<?php 
if(($config->source_type)==2){
?>
<script type="text/javascript">
jQuery(document).ready(function($){   
  $(".a_match_type").attr("disabled",true);
  $(".a_selector").attr("disabled",true);         
  $(".title_match_type").attr("disabled",true);
  $(".title_selector").attr("disabled",true);
  $(".content_match_type").attr("disabled",true);
  $(".content_selector").attr("disabled",true);
  $(".rss_disable").attr("disabled",true); 
});
</script>
<?php
}
?>



<?php
$moreMRstr=
'<div class="apmatchtype"><p><input type="hidden" id="content_match_type_{cmrNum}" value="0" /><input class="content_match_type content_match_type_{cmrNum}" type="radio" name="content_match_type_{cmrNum}" value="0"  checked="true" />'.__('Use CSS Selector','wp-autopost').'&nbsp;&nbsp;&nbsp;<input class="content_match_type content_match_type_{cmrNum}" type="radio" name="content_match_type_{cmrNum}" value="1" />'.__("Use Wildcards Match Pattern","wp-autopost").'&nbsp;&nbsp;&nbsp;<input type="checkbox" name="outer_{cmrNum}" /> '.__("Contain The Outer HTML Text","wp-autopost").'<a style="float:right;" class="apdelete" title="delete" href="javascript:;" onclick="deleteRowCmr(\\\'cmr{rowID}\\\')" >'.__('Delete').'</a></p></div>'; 
	   
$moreMRstr.=	   
 '<span id="content_match_0_{cmrNum}" >'.__("CSS Selector","wp-autopost").': <input type="text" name="content_selector_0_{cmrNum}" id="content_selector_0_{cmrNum}" class="content_selector" size="40" value=""> <span class="clickBold" id="index_{cmrNum}">'.__("Index","wp-autopost").'</span><span id="index_num_{cmrNum}" style="display:none;">: <input type="text" name="index_{cmrNum}" size="1" value="0" /><input type="hidden" id="index_show_{cmrNum}" value="0" /></span></span><span id="content_match_1_{cmrNum}"  style="display:none;" ><table><tr><td>'.__('Starting Unique HTML','wp-autopost').':</td><td><input type="text" name="content_selector_1_start_{cmrNum}" id="content_selector_1_start_{cmrNum}" class="content_selector" size="40" value="" /></td></tr><tr><td>'.__('Ending Unique HTML','wp-autopost').':</td><td><input type="text" name="content_selector_1_end_{cmrNum}" id="content_selector_1_end_{cmrNum}" class="content_selector" size="40" value="" /></td></tr></table></span><p><label>'.__("To: ","wp-autopost").'</label>';
  
$moreMRstr.= '<select name="objective_{cmrNum}" id="objective_{cmrNum}" >';
$moreMRstr.=    '<option value="0" >'.__('Post Content','wp-autopost').'</option>';
$moreMRstr.=    '<option value="2" >'.__('Post Excerpt','wp-autopost').'</option>';
$moreMRstr.=    '<option value="3" >'.__('Post Tags','wp-autopost').'</option>';
$moreMRstr.=    '<option value="5" >'.__('Categories').'</option>';
$moreMRstr.=    '<option value="4" >'.__('Featured Image').'</option>';
$moreMRstr.=    '<option value="1" >'.__('Post Date','wp-autopost').'</option>';
$moreMRstr.=    '<option value="-1" >'.__('Custom Fields').'</option>';


$taxonomy_names = get_object_taxonomies( $config->post_type,'objects');        
foreach($taxonomy_names as $taxonomy){
  if($taxonomy->name=='category' || $taxonomy->name=='post_tag' || $taxonomy->name =='post_format')continue;
  $moreMRstr.=    '<option value="Taxonomy:'.$taxonomy->name.'" >'.__('Taxonomy','wp-autopost').' - '.$taxonomy->label.'</option>';
}


$moreMRstr.= '</select><span><input id="objective_customfields_{cmrNum}" name="objective_customfields_{cmrNum}" style="display:none;" type="text" value="" /></span></p>';

?>





<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2>Auto Post - Setting : <?php echo $config->name; ?><a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=new" class="add-new-h2"><?php echo __('Add New Task','wp-autopost'); ?></a> </h2>
   <div class="clear"></div>

<br/>
<a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&p=<?php echo $_REQUEST['p']; ?>" class="button"><?php echo __('Return','wp-autopost'); ?></a> 
&nbsp;<input type="button" class="button-primary"  value="<?php echo __('Test Fetch','wp-autopost'); ?>"    onclick="testFetch()"/>
<br/><br/>

<?php include WPAPPRO_PATH.'/wp-autopost-saction.php'; ?>

<?php

if($saction=='editSubmit'){
   echo $msg;
}

if(@($_REQUEST['reset_scheduled']==1)){
  $wpdb->query("update $t_ap_config  set post_scheduled_last_time=0 where id= ".$id); 

  $showBox1=true;
  $msg = '<div class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  echo $msg;
}


?>


<?php

if(isset($_POST['saction2'])&& $_POST['saction2']=='autoseturl'){
  AutoSetNotice();
  
  $wpdb->query($wpdb->prepare("update $t_ap_config set
			   source_type = %d,
			   start_num = %d, 
			   end_num =  %d  WHERE id = %d",$_POST['source_type'],$_POST['start_num'],$_POST['end_num'],$id
			   ));

 if($_POST['source_type']==0 || $_POST['source_type']==2){ 
  $wpdb->query('delete from '.$t_ap_config_url_list.' where config_id ='.$id);
  $urls = explode("\n",$_POST['urls']);  
  foreach($urls as $url){
    $url=trim($url);
	if($url!='')$wpdb->query('insert into '.$t_ap_config_url_list.'(config_id,url) values ('.$id.',"'.$url.'")');
  }
 }

 if($_POST['source_type']==1){
  $wpdb->query('delete from '.$t_ap_config_url_list.' where config_id ='.$id);
  $url=trim($_POST['url']);
  if($url!='')$wpdb->query('insert into '.$t_ap_config_url_list.'(config_id,url) values ('.$id.',"'.$url.'")');
 }  
  
  autoSetURLMatchRuleDisplay($id);
}



if(isset($_POST['saction3'])&& $_POST['saction3']=='autosetSettings'){
   AutoSetNotice();
   autosetSettingsDisplay($id,$_POST['autoset_url']);
}


if( $config->source_type==1 ){
  echo '<div class="updated fade"><p>'.__('Use <strong>Batch generate The URL of Article List</strong>, is best to use manul update','wp-autopost').'</p></div>';
}

?>

<form id="myform"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
 <input type="hidden" name="saction" id="saction" value="">
 <input type="hidden" name="id"  value="<?php echo $id; ?>"> 
 <input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
</form>


<form id="myform1"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php"> 
<input type="hidden" name="saction" value="editSubmit">
<input type="hidden" id="saction1" name="saction1" value="">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">


<?php $config = $wpdb->get_row('SELECT * FROM '.$t_ap_config.' WHERE id ='.$id ); ?>

<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __("Basic Settings","wp-autopost"); ?></h3>
  <div class="inside" <?php if(@!$showBox1)echo 'style="display:none;"'; ?> >
	 <table width="100%"> 	         	  
       <tr> 
		 <td width="18%"><?php echo __('Task Name','wp-autopost'); ?>:</td>
		 <td><input type="text" name="config_name" id="config_name" size="80" value="<?php echo $config->name; ?>"></td>
	   </tr>
       
	   <tr> 
		 <td style="padding:10px 0 10px 0;"><?php echo __('Post Type','wp-autopost'); ?>:</td>
         <td style="padding:10px 0 10px 0;">
		   <?php $custom_post_types = get_post_types( array('_builtin' => false), 'objects'); ?>
		   <input type="radio" name="post_type" value="post" onchange="changePostType()" <?php if($config->post_type=='post') echo 'checked="true"'; ?> /> <?php echo __('Post'); ?>
		   &nbsp;&nbsp;
		   <input type="radio" name="post_type" value="page" onchange="changePostType()" <?php if($config->post_type=='page') echo 'checked="true"'; ?> /> <?php echo __('Page'); ?>
     <?php foreach ( $custom_post_types  as $post_type ) { ?>
		     &nbsp;&nbsp;
		   <input type="radio" name="post_type" value="<?php echo $post_type->name; ?>" onchange="changePostType()" <?php if($config->post_type==$post_type->name) echo 'checked="true"'; ?> /> <?php echo  $post_type->label; ?>
	 <?php } ?>	          
		 </td>
       </tr>

<?php
class Walker_Terms_Checklist extends Walker {
	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); 

	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		extract($args);
		if ( empty($taxonomy) )
			$taxonomy = 'category';

		if ( $taxonomy == 'category' )
			$name = 'post_category';
		else
			$name = 'post_category';

		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
	}

	function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}
?>

<?php if($config->post_type=='post'): ?>
	   <tr> 
		 <td><?php echo __('Taxonomy','wp-autopost');  ?>:</td> 
		 <td>
<?php
          $Walker_Terms_Checklist = new Walker_Terms_Checklist();
	      $selected_cats = explode(',',$config->cat);
	      $taxonomy_names = get_object_taxonomies( 'post','objects');        
		  foreach($taxonomy_names as $taxonomy){
		    if($taxonomy->name=='post_tag' || $taxonomy->name =='post_format')continue;
			$args = array(
	         'descendants_and_self'  => 0,
	         'selected_cats'         => $selected_cats,
	         'popular_cats'          => false,
	         'walker'                => $Walker_Terms_Checklist,
	         'taxonomy'              => $taxonomy->name,
	         'checked_ontop'         => false
            );
		    echo '<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">';	 
			echo '<strong>'.$taxonomy->label.'</strong>';
			wp_terms_checklist( 0, $args );
			echo '</ul>';
		  }
?>
		 </td>
	   </tr>
	   
<?php elseif($config->post_type=='page'): ?>	   
      <tr> 
         <td colspan="2"></td>
	  </tr>
<?php else: ?>
      <tr> 
		 <td><?php echo __('Taxonomy','wp-autopost');  ?>:</td> 
		 <td>
<?php
          $Walker_Terms_Checklist = new Walker_Terms_Checklist();
	      $selected_cats = explode(',',$config->cat);
	      $taxonomy_names = get_object_taxonomies( $config->post_type,'objects');        
		  foreach($taxonomy_names as $taxonomy){
			$args = array(
	         'descendants_and_self'  => 0,
	         'selected_cats'         => $selected_cats,
	         'popular_cats'          => false,
	         'walker'                => $Walker_Terms_Checklist,
	         'taxonomy'              => $taxonomy->name,
	         'checked_ontop'         => false
            );
		    echo '<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">';	 
			echo '<strong>'.$taxonomy->label.'</strong>';
			wp_terms_checklist( 0, $args );
			echo '</ul>';
		  }
?>
		 </td>
	   </tr>
<?php endif; ?>

<?php if ( current_theme_supports( 'post-formats' ) ): ?>

<?php $post_formats = get_theme_support( 'post-formats' );
      if ( is_array( $post_formats[0] ) ) :	 $formatName = get_post_format_strings(); ?>
       <tr>
         <td style="padding:0 0 10px 0;"><?php echo __('Post Format','wp-autopost');  ?>:</td>
         <td style="padding:0 0 10px 0;">
		   <input type="radio" name="post_format" value="" <?php if($config->post_format==''||$config->post_format==null) echo 'checked="true"'; ?> /> <?php echo $formatName['standard']; ?>
    <?php foreach ( $post_formats[0]  as $post_format ) { ?>
		   &nbsp;&nbsp;
		   <input type="radio" name="post_format" value="<?php echo $post_format; ?>" <?php if($config->post_format==$post_format) echo 'checked="true"'; ?> /> <?php echo  $formatName[$post_format]; ?>		   
	 <?php } ?>	   

		 </td>
	   </tr>
      
<?php endif; ?>
   
<?php endif; ?>

	   <tr>
        <td><?php echo __('Author','wp-autopost'); ?>:</td>
        <td>
		<?php
		    $querystr = "SELECT $wpdb->users.ID,$wpdb->users.display_name FROM $wpdb->users";
            $users = $wpdb->get_results($querystr, OBJECT);		   
		?>
         <select name="author" >
		    <option value="0"><?php echo __('Random Author','wp-autopost'); ?></option>
          <?php foreach ($users as $user) { ?> 
		    <option value="<?php echo $user->ID; ?>" <?php if(($user->ID)==($config->author)) echo 'selected' ?> ><?php echo $user->display_name; ?></option>
		  <?php } ?>
		 </select>
		</td> 
	   </tr>
	   <tr>
        <td><?php echo __('Update Interval','wp-autopost'); ?>:</td>
        <td>
		   <input type="text" name="update_interval" id="update_interval" size="2" value="<?php echo $config->update_interval; ?>"> <?php echo __('Minute','wp-autopost'); ?> <span class="gray">( <?php echo __('How long Intervals detect whether there is a new article can be updated','wp-autopost'); ?> )</span>
		</td> 
	   </tr>
	   <tr>
        <td><?php echo __('Published Date Interval','wp-autopost'); ?>:</td>
        <td>
		   <input type="text" name="published_interval" id="published_interval" size="2" value="<?php echo $config->published_interval; ?>"> <?php echo __('Minute','wp-autopost'); ?> <span class="gray">( <?php echo __('The published date interval between each post','wp-autopost'); ?> )</span>
		</td> 
	   </tr>
       
	   <tr>
	   <?php
          $post_scheduled = json_decode($config->post_scheduled);
		  if(!is_array($post_scheduled)){
             $post_scheduled = array();
             $post_scheduled[0] = 0;
             $post_scheduled[1] = 12;
			 $post_scheduled[2] = 0; 
		  }
		?>
        <td><?php echo __('Post Scheduled','wp-autopost'); ?>:</td>
        <td>
		  <select id="post_scheduled" name="post_scheduled">
           <option value="0" <?php if($post_scheduled[0]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if($post_scheduled[0]==1) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		  </select>
		  
		</td> 
	   </tr>
       
       <tr>
         <td></td>
		 <td>
           <div id="post_scheduled_more" <?php if($post_scheduled[0]==0)echo 'style="display:none;"' ?> >
	        <table>
              <tr>
                <td><?php echo __('Start Time','wp-autopost'); ?>:</td>
				<td>
				 <input type="text" name="post_scheduled_hour" id="hh" size="2" maxlength="2"  value="<?php echo ($post_scheduled[1]<10)?'0'.$post_scheduled[1]:$post_scheduled[1];?>" />
			     :
                 <input type="text" name="post_scheduled_minute" id="mn" size="2" maxlength="2" value="<?php echo ($post_scheduled[2]<10)?'0'.$post_scheduled[2]:$post_scheduled[2];?>" />
				</td>
			  </tr>
			  <tr>
               <td><?php echo __('Published Date Interval','wp-autopost'); ?>:</td>
			   <td><input type="text" name="published_interval_1" id="published_interval_1" size="3" value="<?php echo $config->published_interval; ?>"> <?php echo __('Minute','wp-autopost'); ?> <span class="gray">( <?php echo __('The published date interval between each post','wp-autopost'); ?> )</span></td> 
			  </tr>
			  
			  <tr>
               <td colspan="2">
			     <?php 
				 if($post_scheduled[0]==1): 
                   $currentTime = current_time('timestamp');
			       
			       
				   if( ($config->post_scheduled_last_time) > 0){
                     $post_scheduled_last_time = $config->post_scheduled_last_time;
					 if($post_scheduled_last_time < $currentTime){
                       $postTime = mktime($post_scheduled[1],$post_scheduled[2],0,date('m',$currentTime),date('d',$currentTime),date('Y',$currentTime)); 
					 }else{
                       $postTime =  $post_scheduled_last_time + $config->published_interval*60 + rand(0,60);  
					 }
					 
			       }else{
					 $postTime = mktime($post_scheduled[1],$post_scheduled[2],0,date('m',$currentTime),date('d',$currentTime),date('Y',$currentTime));
         
				   }
                   
				   if($postTime<$currentTime){
                     $postTime += 86400; // add one day
                   }	
                   
				   echo __('Expected newest publish date','wp-autopost').': <code>'.date('Y-m-d H:i:s',$postTime).'</code>';


				   if( ($config->post_scheduled_last_time) > 0){ ?> 
                   
				   <a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=edit&id=<?php echo $id;?>&p=<?php echo $_REQUEST['p']; ?>&reset_scheduled=1" ><?php echo __('Reset','wp-autopost'); ?></a>
                 
				 <?php
				   }

                 endif; ?>
			   </td>
			  </tr>

			</table>
			
		   </div>
		 </td>
	   </tr>

       
	   <tr>
	   <?php
          $publish_date = false;
	      if($config->publish_date!=''&&$config->publish_date!=null){
             $publish_date = true;
	      }
		?>
        <td><?php echo __('Specify Publish Date','wp-autopost'); ?>:</td>
        <td>
		  <select id="use_publish_date" name="use_publish_date">
           <option value="0" <?php if($publish_date===false) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if($publish_date===true) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		  </select>
		</td> 
	   </tr>
       
       <tr>
         <td></td>
		 <td>
           <div id="publish_date_more" <?php if($publish_date===false)echo 'style="display:none;"' ?> >
	        <table>
              <tr>
                <td><?php echo __('Date'); ?>:</td>
				<td>
				  <input type="text" name="publish_date" id="publish_date" size="10" value="<?php echo $config->publish_date; ?>" />
				  Example: <em><code><?php echo date('Y-m'); ?></code></em> OR <em><code><?php echo date('Y-m-d'); ?></code></em>
				</td>
			  </tr>
			</table>
		   </div>
		 </td>
	   </tr>


       <tr>
        <td style="height:28px;"><?php echo __('Charset','wp-autopost'); ?>:</td>
        <td>
		   <input class="charset" type="radio" name="charset" value="0"  <?php if($config->page_charset=='0') echo 'checked="true"'; ?>> <?php echo __('Automatic Detection','wp-autopost'); ?> 
		   <input class="charset" type="radio" name="charset" value="1"  <?php if($config->page_charset!='0') echo 'checked="true"'; ?>> <?php echo __('Other','wp-autopost'); ?>
		   <span id="ohterSet" <?php if($config->page_charset=='0') echo 'style="display:none;"' ?>><input type="text" name="page_charset" id="page_charset"  value="<?php if($config->page_charset!='0') echo $config->page_charset; ?>"></span>		   
		</td> 
	   </tr>
	   <tr>
	    <?php
          $download_attachs = json_decode($config->download_img);
		  if(!is_array($download_attachs)){
             $download_attachs = array();
             $download_attachs[0] = $config->download_img;
             $download_attachs[1] = 0;  // Download Remote Attachments
		  }
		?>
        <td><?php echo __('Download Remote Images','wp-autopost'); ?>:</td>
        <td>
         <select id="download_img" name="download_img">
           <option value="0" <?php if($download_attachs[0]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if($download_attachs[0]==1) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		 </select>
		</td> 
	   </tr>
	   <tr>
         <td></td>
         <td>
         <span id="img_insert_attachment_div" <?php if($download_attachs[0]==0)echo 'style="display:none;"' ?> >
		  <?php
			 $img_insert_attachment = json_decode($config->img_insert_attachment);    
			 if(!is_array($img_insert_attachment)){
		        $img_insert_attachment = array();
				$img_insert_attachment[0] = $config->img_insert_attachment; // insert wordpress lib
				$img_insert_attachment[1] = 0;   // set_featured_image
				$img_insert_attachment[2] = 0;   // set_watermark_image
				$img_insert_attachment[3] = 0;   // attach_insert_attachment  insert wordpress lib
			    //$img_insert_attachment[4] = 0;   // 图片源地址属性 
			 }
		     
		  ?>
		   <p>
		     <div>
			   <input type="radio" name="img_insert_attachment"  value="0" <?php if($img_insert_attachment[0]==0)echo 'checked="true"'; ?> /> <?php echo __('Do not save','wp-autopost'); ?>
			   <br/>
			   <input type="radio" name="img_insert_attachment"  value="1" <?php if($img_insert_attachment[0]==1)echo 'checked="true"'; ?> /> <?php echo __('Save the images to wordpress media library','wp-autopost'); ?>
			   <br/>
			   <input type="radio" name="img_insert_attachment"  value="2" <?php if($img_insert_attachment[0]==2)echo 'checked="true"'; ?> /> <?php echo __('Save the images to Flickr','wp-autopost'); ?> <a title='<?php echo __('Automatically upload images to Flickr (1TB Free Storage), save bandwidth and space, speed up your website.','wp-autopost'); ?>'>[?]</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<a href="admin.php?page=wp-autopost-pro/wp-autopost-flickr.php" target="_blank"><?php echo __('Flickr Options','wp-autopost'); ?></a>)
               <br/>
			   <input type="radio" name="img_insert_attachment"  value="3" <?php if($img_insert_attachment[0]==3)echo 'checked="true"'; ?> /> <?php echo __('Save the images to Qiniu','wp-autopost'); ?> <a title='<?php echo __('Automatically upload images to Qiniu (10GB Free Storage), save bandwidth and space, speed up your website.','wp-autopost'); ?>'>[?]</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<a href="admin.php?page=wp-autopost-pro/wp-autopost-qiniu.php" target="_blank"><?php echo __('Qiniu Options','wp-autopost'); ?></a>)
			   <br/>
			   <input type="radio" name="img_insert_attachment"  value="4" <?php if($img_insert_attachment[0]==4)echo 'checked="true"'; ?> /> <?php echo __('Save the images to Upyun','wp-autopost'); ?> <a title='<?php echo __('Automatically upload images to Upyun, save bandwidth and space, speed up your website.','wp-autopost'); ?>'>[?]</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<a href="admin.php?page=wp-autopost-pro/wp-autopost-upyun.php" target="_blank"><?php echo __('Upyun Options','wp-autopost'); ?></a>)
			   <br/><br/>
			 </div>

			 <input type="checkbox" name="set_watermark_image" id="set_watermark_image" <?php if($img_insert_attachment[2]>=1)echo 'checked="true"'; ?> /> <?php echo __('Add a watermark to downloaded images automatically','wp-autopost'); ?> (<a href="admin.php?page=wp-autopost-pro/wp-autopost-watermark.php" target="_blank"><?php echo __('Watermark Options','wp-autopost'); ?></a>)
            <?php $wms = $wpdb->get_results('SELECT id,name FROM '.$t_ap_watermark.' order by id'); ?>
             <br/>
			 <?php echo __('Watermark Name','wp-autopost'); ?> : 
			 <select name="watermark_id" id="watermark_id">
			    <option value="0" ><?php echo __('Please Select','wp-autopost'); ?></option>
			  <?php foreach($wms as $wm){ ?>
			    <option value="<?php echo $wm->id; ?>" <?php selected( $wm->id, $img_insert_attachment[2]); ?> ><?php echo $wm->name; ?></option>
			  <?php } ?>
			 </select>

			 <br/>
			 <br/>
             <?php if(@$img_insert_attachment[4]==null)$attribute='src';else $attribute = $img_insert_attachment[4]; ?> 
			 <?php echo __('The attribute of image\'s URL','wp-autopost'); ?> : <input type="text" name="img_url_attr" value="<?php echo $attribute; ?>" /> <span class="gray"><?php echo __('Usually is " src "','wp-autopost'); ?></span>
		   </p>
		 </span>
		 </td>
	   </tr>

	   <tr>
        <td><?php echo __('Set Featured Image','wp-autopost'); ?>:</td>
		<td>
         <select id="set_featured_image" name="set_featured_image">
           <option value="0" <?php if($img_insert_attachment[1]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if($img_insert_attachment[1]>0) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		 </select>
		 <span id="set_featured_image_div" <?php if($img_insert_attachment[1]==0)echo 'style="display:none;"' ?> >
            <?php echo __('Set images as the featured image automatically','wp-autopost'); ?>
            &nbsp;&nbsp;&nbsp;<?php echo __('Index','wp-autopost'); ?>:<input type="text" size="1" name="set_featured_image_index" value="<?php echo ($img_insert_attachment[1]==0)?'1':$img_insert_attachment[1]; ?>" /><a title='<?php echo __('1:the first image; 2:the second image','wp-autopost'); ?>'>[?]</a>
		 </span>
		</td>
	   </tr>

	   <tr>
         <td><?php echo __('Download Remote Attachments','wp-autopost'); ?>:</td>
         <td>
          <select id="download_attach" name="download_attach">
            <option value="0" <?php if($download_attachs[1]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		    <option value="1" <?php if($download_attachs[1]==1) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		  </select>
		  <span class="download_attach_option" <?php if($download_attachs[1]==0)echo 'style="display:none;"' ?> >
		  (<a href="admin.php?page=wp-autopost-pro/wp-autopost-options.php#RemoteAttachmentDownloadOption" target="_blank"><?php echo __('Remote Attachment Download Option','wp-autopost'); ?></a>)
		 </span>
		 </td>
	   </tr>
	   <tr>
         <td></td>
         <td>
           <span class="download_attach_option" <?php if($download_attachs[1]==0)echo 'style="display:none;"' ?> >
		    <p>
			  <input type="checkbox" name="attach_insert_attachment" id="attach_insert_attachment" <?php if($img_insert_attachment[3]==1)echo 'checked="true"'; ?> /> <?php echo __('Save the attachments to wordpress media library','wp-autopost'); ?>
			</p>      
		   </span>
		 </td>
	   </tr>
       
	   <?php
          $auto_set = json_decode($config->auto_tags);
		  if(!is_array($auto_set)){
             $auto_set = array();
             $auto_set[0] = $config->auto_tags;
             $auto_set[1] = 0; // auto_excerpt
			 $auto_set[2] = 0; // publish_status
             $auto_set[3] = 1; // use_wp_tags
		  }
		?>
	   <tr>
        <td><?php echo __('Auto Tags','wp-autopost'); ?>:</td>
        <td>
         <select id="auto_tags" name="auto_tags">
           <option value="0" <?php if(($auto_set[0])==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if(($auto_set[0])==1) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		 </select>	 
		</td> 
	   </tr>
	   <tr>
         <td></td>
		 <td>
		  <span id="tags_div" <?php if($auto_set[0]==0)echo 'style="display:none;"' ?> >
		   <p>
           <input type="checkbox" name="use_wp_tags" <?php if($auto_set[3]==1)echo 'checked="true"'; ?> /> <?php echo __('Use Wordpress Tags Library','wp-autopost'); ?>
           <br/><br/>
		   <?php echo __('Tags List','wp-autopost'); ?>: <span class="gray">(<?php echo __('Separated with a comma','wp-autopost'); ?>)</span><br/>
		   <textarea style="width:100%" name="tags" id="tags" ><?php echo $config->tags; ?></textarea>
		   <br/><br/>
		   <input type="checkbox" name="whole_word" id="whole_word" <?php if(($config->whole_word)==1)echo 'checked="true"'; ?> /> <?php echo __('Match Whole Word','wp-autopost'); ?> <span class="gray">(<?php echo __('Autotag only a post when terms finded in the content are a the same name','wp-autopost'); ?>)</span></p>
		 </span></td>
       </tr>
       
	   <tr>
        <td><?php echo __('Auto Excerpt','wp-autopost'); ?>:</td>
        <td>
         <select id="auto_excerpt" name="auto_excerpt">
           <option value="0" <?php if($auto_set[1]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if($auto_set[1]>0) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		 </select>
		 <span id="auto_excerpt_div" <?php if($auto_set[1]==0)echo 'style="display:none;"' ?> >
            <?php echo __('Set the paragraph as an excerpt automatically','wp-autopost'); ?>
            &nbsp;&nbsp;&nbsp;<?php echo __('Index','wp-autopost'); ?>:<input type="text" size="1" name="auto_excerpt_index" value="<?php echo ($auto_set[1]==0)?'1':$auto_set[1]; ?>" /><a title='<?php echo __('1:beginning of paragraph 1; 2:beginning of paragraph 2','wp-autopost'); ?>'>[?]</a>
		 </span>
		</td>
	   </tr>

	   <tr>
        <td><?php echo __('Publish Status','wp-autopost'); ?>:</td>
        <td>
         <select id="publish_status" name="publish_status">
           <option value="0" <?php if($auto_set[2]==0) echo 'selected="true"'; ?>><?php echo __('Published'); ?></option>
		   <option value="1" <?php if($auto_set[2]==1) echo 'selected="true"'; ?>><?php echo __('Draft'); ?></option>
		   <option value="2" <?php if($auto_set[2]==2) echo 'selected="true"'; ?>><?php echo __('Pending Review'); ?></option>
		 </select>
		</td>
	   </tr>

	   <tr>
        <td><?php echo __('Manually Selective Extraction','wp-autopost'); ?>:</td>
        <td>
         <select id="manually_extraction" name="manually_extraction">
           <option value="0" <?php if(($config->m_extract)==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if(($config->m_extract)==1) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		 </select>		 
		 <span class="gray">( <?php echo __('Manually select which article can be posted in your site','wp-autopost'); ?> )</span>
		</td>
	   </tr>

	   <tr> 
        <td style="padding:10px 0 10px 0;"><?php echo __('Check Extracted Method','wp-autopost'); ?>:</td>
        <td style="padding:10px 0 10px 0;">
          <input type="radio" name="check_duplicate"  value="0" <?php if(($config->check_duplicate)==0)echo 'checked="true"'; ?> /> <?php echo __('URL','wp-autopost'); ?>
          &nbsp;&nbsp;&nbsp;
          <input type="radio" name="check_duplicate"  value="1" <?php if(($config->check_duplicate)==1)echo 'checked="true"'; ?> /> <?php echo __('URL + Title','wp-autopost'); ?>
		</td>
	   </tr>

	   <tr> 
        <td colspan="2"><hr/></td>
	   </tr>
       
	   <?php
         $proxy = json_decode($config->proxy);
	   ?>
	   <tr> 
        <td><?php echo __('Use Proxy','wp-autopost'); ?>:</td>
        <td>
         <select id="use_proxy" name="use_proxy"> 
           <option value="0" <?php if($proxy[0]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if($proxy[0]==1) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		 </select>
		</td>
	   </tr>

	   <tr> 
        <td><?php echo __('Hide IP','wp-autopost'); ?>:</td>
        <td>
         <select id="hide_ip" name="hide_ip">
           <option value="0" <?php if($proxy[1]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if($proxy[1]==1) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		 </select>
		</td>
	   </tr>

	   <tr> 
        <td><?php echo __('Enable Cookie','wp-autopost'); ?>:</td>
        <td>
         <select id="enable_cookie" name="enable_cookie">
           <option value="0" <?php if($proxy[2]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		   <option value="1" <?php if($proxy[2]==1) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		 </select>
         <span class="gray">( <?php echo __('Some few sites need to open this feature can normal extracted contents','wp-autopost'); ?> )</span>
		</td>
	   </tr>


	   <tr> 
        <td colspan="2"><hr/></td>
	   </tr>
       

	   <tr> 
        <td colspan="2"><?php echo __('When extract error set the status to','wp-autopost'); ?>:
         &nbsp;&nbsp;
         <select id="err_status" name="err_status">
           <option value="1" <?php if($config->err_status==1) echo 'selected="true"'; ?>><?php echo __('Not set','wp-autopost'); ?></option>
		   <option value="0" <?php if($config->err_status==0) echo 'selected="true"'; ?>><?php echo __('Pending Extraction','wp-autopost'); ?></option>
		   <option value="-1" <?php if($config->err_status==-1) echo 'selected="true"'; ?>><?php echo __('Ignored','wp-autopost'); ?></option>
		 </select>
		</td>
	   </tr>


	   <tr>
         <td colspan="2">
		   <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"    onclick="edit()"/>
		 </td>
	   </tr>
    </table>
  </div>
</div>
<div class="clear"></div>
</form>



<form id="myform2"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save2">
<input type="hidden" name="saction2" id="saction2" value="">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">

<?php $urls = $wpdb->get_results('SELECT * FROM '.$t_ap_config_url_list.' WHERE config_id ='.$id.' ORDER BY id' ); ?>
<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Article Source Settings','wp-autopost'); ?></h3>
  <div class="inside" <?php if(@!$showBox2)echo 'style="display:none;"' ?>>
     <table width="100%"> 
	   <tr>
        <td>
		 <input type="hidden" id="source_type" value="<?php echo $config->source_type; ?>" />
		 <input class="source_type" type="radio" name="source_type" value="0" <?php if(($config->source_type)== 0) echo 'checked="true"'; ?> /><?php echo __('Manually specify','wp-autopost'); ?> <b><?php echo __('The URL of Article List','wp-autopost'); ?></b> &nbsp;
		 <input class="source_type" type="radio" name="source_type" value="1" <?php if(($config->source_type)== 1) echo 'checked="true"'; ?> /><?php echo __('Batch generate','wp-autopost'); ?> <b><?php echo __('The URL of Article List','wp-autopost'); ?></b> &nbsp;
		 <input class="source_type" type="radio" name="source_type" value="2" <?php if(($config->source_type)== 2) echo 'checked="true"'; ?> /><?php echo __('Use <b>RSS</b>','wp-autopost'); ?>		 		 
		</td>
	   </tr>
	   <tr> 
		 <td>	 
		   <div id="urlArea1" <?php if(($config->source_type)!=0 && ($config->source_type)!=2 ) echo 'style="display:none;"'; ?> >
		     <textarea name="urls" id="urls" rows="8" style="width:100%"><?php if(($config->source_type)==0 || ($config->source_type)==2){foreach($urls as $url)echo $url->url."\n"; } ?></textarea>
			 <br/>
			 <span class="gray"><?php echo __('You can add multiple URLs, each URL begin at a new line','wp-autopost'); ?></span>
		   </div>
		
		   <div id="urlArea2" <?php if(($config->source_type)!=1) echo 'style="display:none;"'; ?>>
		     <input type="text" name="url" id="url" style="width:100%" value="<?php if(($config->source_type)==1){foreach($urls as $url)echo $url->url."\n"; } ?>" />
			 <br/>
			 <span class="gray"><?php echo __('For example','wp-autopost'); ?>：http://wp-autopost.org/html/test/list_(*).html</span><br/>
			 (*) <?php echo __('From','wp-autopost'); ?> <input type="text" name="start_num" id="start_num" value="<?php echo $config->start_num; ?>" size="1"> <?php echo __('To','wp-autopost'); ?> <input type="text" name="end_num" id="end_num" value="<?php echo $config->end_num; ?>" size="1">
		   </div>	 
		 </td>
	   </tr>
	   <tr><td></td></tr>
	   <tr>
         <td> <input type="checkbox" name="reverse_sort" id="reverse_sort" <?php if(($config->reverse_sort)==1)echo 'checked="true"'; ?> /> <?php echo __('Reverse the sort of articles','wp-autopost'); ?> <span class="gray">(<?php echo __('Click Test to see the difference','wp-autopost'); ?>)</span> </td>
	   </tr>
     </table>
	 <br/>
	 
	 <?php	   
	   $a_match_type = json_decode($config->a_match_type);
	   if(!is_array($a_match_type)){
         $a_match_type=array();
		 $a_match_type[0] = $config->a_match_type;
	   }
       
	   $a_selector = json_decode($config->a_selector);
       if(!is_array($a_selector)){
         $a_selector=array();
		 $a_selector[0] = $config->a_selector;
	   }
	 ?>

<script type="text/javascript">
jQuery(document).ready(function($){   
  <?php 
       $levelNum = count($a_selector);
       for($i=0;$i<$levelNum;$i++){ ?>  
    $('.a_match_type_<?php echo $i; ?>').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == 0){
           $("#a_match_0_<?php echo $i; ?>").show();
	       $("#a_match_1_<?php echo $i; ?>").hide();
		}else{
           $("#a_match_1_<?php echo $i; ?>").show();
	       $("#a_match_0_<?php echo $i; ?>").hide();
		}
	});
	
  <?php } ?>
});
</script>


	 <h4>   
	    <span class="TipRss" <?php if(($config->source_type)!=2) echo 'style="display:none"'; ?> > 
	      <?php echo __('Use RSS do not need this setting','wp-autopost'); ?>	 
	    </span>
		<?php echo __('Article URL matching rules','wp-autopost'); ?> 	
	 </h4>

	 <p>
	   <input type="button" class="button-primary rss_disable"  value="<?php echo __('Automatic Set [Article URL matching rules]','wp-autopost'); ?>"  onclick="autoseturl()"/>
	 </p>
	 
	  
	  <input type="hidden" id="a_match_type" value="<?php echo $a_match_type[0]; ?>" />
	  
	  <input class="a_match_type" type="radio" name="a_match_type" value="0" <?php if($a_match_type[0]== 0) echo 'checked="true"'; ?> /><?php echo __('Use URL wildcards match pattern','wp-autopost'); ?> 
	  &nbsp;&nbsp;
	  <input class="a_match_type" type="radio" name="a_match_type" value="1" <?php if($a_match_type[0]== 1) echo 'checked="true"'; ?> /><?php echo __('Use CSS Selector','wp-autopost'); ?> 
	  
	  <div id="a_match_0" <?php if($a_match_type[0]!=0) echo 'style="display:none;"'; ?> >
	  <?php echo __('Article URL','wp-autopost'); ?>:
	  <input type="text" name="a_selector_0" id="a_selector_0" class="a_selector" size="80" value="<?php if($a_match_type[0]==0){echo $a_selector[0]; }?>"><br/>
	  <span class="gray"><?php echo __('The articles URL, (*) is wildcards','wp-autopost'); ?>, <?php echo __('For example','wp-autopost'); ?>: http://www.domain.com/article/(*)/</span>
	  </div>
      
      <div id="a_match_1" <?php if($a_match_type[0]!=1) echo 'style="display:none;"'; ?> >
	  <?php echo __('The Article URLs CSS Selector','wp-autopost'); ?>:
	  <input type="text" name="a_selector_1" id="a_selector_1" class="a_selector" size="80" value="<?php if($a_match_type[0]==1){echo $a_selector[0]; }?>"><br/>
	  <span class="gray"><?php echo __('Must select to the HTML &lta> tag','wp-autopost'); ?>, <?php echo __('For example','wp-autopost'); ?>: #list a</span>
	  </div>
      
	  <table id="url_levels">
       
	 <?php
       $levelNum = count($a_selector);
       for($i=1;$i<$levelNum;$i++){ 
     ?>	   
	   <tr id="url_level_tr<?php echo $i; ?>">
         <td>
          <div class="apmatchtype">
            <p>
			<input class="a_match_type_<?php echo $i; ?>" type="radio" name="a_match_type_<?php echo $i; ?>" value="0" <?php if($a_match_type[$i]== 0) echo 'checked="true"'; ?> /><?php echo __('Use URL wildcards match pattern','wp-autopost'); ?>&nbsp;&nbsp;<input class="a_match_type_<?php echo $i; ?>" type="radio" name="a_match_type_<?php echo $i; ?>" value="1" <?php if($a_match_type[$i]== 1) echo 'checked="true"'; ?> /><?php echo __('Use CSS Selector','wp-autopost'); ?>
			<a style="float:right;" class="apdelete" title="delete" href="javascript:;" onclick="deleteUrlLevel('url_level_tr<?php echo $i; ?>')" ><?php echo __('Delete'); ?></a>
			</p>
		  </div>

          <div id="a_match_0_<?php echo $i; ?>" <?php if($a_match_type[$i]!=0) echo 'style="display:none;"'; ?> >
	        <?php echo __('Article URL','wp-autopost'); ?>:
	        <input type="text" name="a_selector_0_<?php echo $i; ?>" id="a_selector_0_<?php echo $i; ?>" size="80" value="<?php if($a_match_type[$i]==0){echo $a_selector[$i]; }?>">
	      </div>
      
          <div id="a_match_1_<?php echo $i; ?>" <?php if($a_match_type[$i]!=1) echo 'style="display:none;"'; ?> >
	       <?php echo __('The Article URLs CSS Selector','wp-autopost'); ?>:
	       <input type="text" name="a_selector_1_<?php echo $i; ?>" id="a_selector_1_<?php echo $i; ?>" size="80" value="<?php if($a_match_type[$i]==1){echo $a_selector[$i]; }?>">
	      </div>
		</td>
       </tr>
	   <?php } //end for($i=1;$i<$levelNum;$i++){  ?>
	  </table>
     <!-- 
	  <p>  
	  <a class="button" title="<?php echo __('If [The URL of Article List] contains multiple levels','wp-autopost'); ?>"  onclick="addMoreURLLevel()"/><?php echo __('Add The Next Level Rule','wp-autopost'); ?></a>
	  <?php //增加下一层级匹配规则 Add The Next Level Rule   //如果[文章列表网址]包含多个层级  If [The URL of Article List] contains multiple levels?>
	  <input type="hidden" name="levelNum" id="levelNum"  value="<?php echo $levelNum-1; ?>" />
      <input type="hidden" name="urlLevelTRLastIndex" id="urlLevelTRLastIndex"  value="<?php echo $levelNum; ?>" />  
	  </p>
     -->

	  
     
	  

      <br/>
      <br/>

	  <div>
	    <?php 
           $add_source_url = json_decode($config->add_source_url);   
		?>
	    <input type="checkbox" name="add_source_url" id="add_source_url" <?php if($add_source_url[0]== 1) echo 'checked="true"'; ?> /> <?php echo __('Add the source URL to custom fields','wp-autopost'); ?>
		<a title='<?php echo __('Add the custom fields to each post, can use the get_post_meta() function get the value.','wp-autopost'); ?>'>[?]</a>

        <div id="source_url_custom_fields" <?php if($add_source_url[0] != 1) echo 'style="display:none;"'; ?> >
		  <?php echo __('Custom Fields'); ?>: <input type="text" name="source_url_custom_fields"  size="30" value="<?php echo $add_source_url[1]; ?>" />
		</div>
      </div>

	  <br/>


	<?php 
	  $ArticleFilter = $wpdb->get_var('SELECT content FROM '.$t_ap_more_content.' WHERE config_id ='.$id.' AND option_type=1' ); 
	  if($ArticleFilter==null){
        $af[0]=0;
		$af[1]=-1;
		$af[2]='';
		$af[3]=1;
		$af[4]=1;
	  }else{
	    $af = json_decode($ArticleFilter);
		if($af[3]==''||$af[3]==null)$af[3]=1;
		if($af[4]==''||$af[4]==null)$af[4]=1;
      }
	?>
	  
	  <h4><input type="checkbox" name="post_filter" id="post_filter" <?php if($af[2]!=''&&$af[2]!=null) echo 'checked="true"'; ?> /> <?php echo __('Article Filtering','wp-autopost'); ?> - <?php echo __('Extraction base on keyword','wp-autopost'); ?></h4>
      
	  <div id="post_filter_div" <?php if($af[2]==''&&$af[2]==null) echo 'style="display:none;"'; ?> >



	  <input type="radio" name="af0" value="0" <?php if($af[0]== 0) echo 'checked="true"'; ?> /> <?php echo __('<strong>Only Extract</strong> when Title or Content contains following keywords','wp-autopost'); ?> 
	  &nbsp;
	  <input type="radio" name="af0" value="1" <?php if($af[0]== 1) echo 'checked="true"'; ?> /> <?php echo __('<strong>Do Not Extract</strong> when Title or Content contains following keywords','wp-autopost'); ?> 
      
	  <br/>
	  <br/>

	  <input type="radio" name="af3" value="1" <?php if($af[3]== 1) echo 'checked="true"'; ?> /> <?php echo __('Only Check <strong>Title</strong>','wp-autopost'); ?> 
	  &nbsp;
	  <input type="radio" name="af3" value="2" <?php if($af[3]== 2) echo 'checked="true"'; ?> /> <?php echo __('Only Check <strong>Content</strong>','wp-autopost'); ?> 
	  &nbsp;
	  <input type="radio" name="af3" value="3" <?php if($af[3]== 3) echo 'checked="true"'; ?> /> <?php echo __('Check <strong>Title Or Content</strong>','wp-autopost'); ?> 
      
      <br/>
	  <br/>
      
      <?php echo __('Keyword List','wp-autopost'); ?>: <span class="gray">(<?php echo __('Separated with a comma','wp-autopost'); ?>)</span><br/>
	  <textarea style="width:100%" name="af2" ><?php echo $af[2]; ?></textarea>
	  <br/>
      <?php echo __('Keyword Occurrence Times','wp-autopost'); ?>: <input type="text" name="af4" value="<?php echo $af[4]; ?>" size="1" /> <span class="gray"><?php echo __('[Keyword Occurrence Times] Only effective on check the content','wp-autopost'); ?></span>

	  <br/><br/>
	  <?php echo __('Filtered article status','wp-autopost'); ?>:&nbsp;
	  <input type="radio" name="af1" value="-1" <?php if($af[1] == -1) echo 'checked="true"'; ?> /> <?php echo __('Ignored','wp-autopost'); ?> 
	  &nbsp;
	  <input type="radio" name="af1" value="0"  <?php if($af[1] == 0) echo 'checked="true"'; ?> /> <?php echo __('Pending Extraction','wp-autopost'); ?> 
      
      </div>

	  <br/>
	  <br/>
	  <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"  onclick="save2()"/>
	  <input type="button" class="button"  value="<?php echo __('Test','wp-autopost'); ?>"  onclick="test2()"/>

  </div>
</div>
<div class="clear"></div>
</form>

<form id="myform3"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save3">
<input type="hidden" name="saction3" id="saction3" value="">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">

<div class="postbox">
 <h3 class="hndle" style="cursor:pointer;">  
	<span class="TipRss" <?php if(($config->source_type)!=2) echo 'style="display:none"'; ?> > 
      <?php echo __('Use RSS do not need this setting','wp-autopost'); ?>	 
    </span>
	<?php echo __('Article Extraction Settings','wp-autopost'); ?>
    
	<?php
    // auto set
    if(($config->auto_set)!= '' && ($config->auto_set)!= null){
    ?>
       <span class="TipRss"><?php echo __('Now you are using Automatic Set','wp-autopost'); ?></span> 
	<?php
	}
	?>
 
 </h3> 
 <div class="inside" <?php if(@!$showBox3)echo 'style="display:none;"' ?> >
  
<?php
 // auto set
 if(($config->auto_set)!= '' && ($config->auto_set)!= null){
?> 
  
  <div><code>
  <?php echo __('Use Automatic Set is not always accurate or correct, if you find the results is not accurate or correct, then you need to set by yourself','wp-autopost'); ?>
  </code></div>
  <br/>

  <div>
    <a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=cancelautoset&id=<?php echo $id; ?>"  class="button rss_disable"><?php echo __('Cancel Automatic Set','wp-autopost'); ?></a>
   </form>
  </div>

  <input type="hidden" name="use_auto_set" value="1">

<?php
// auto set
 } // end if(($config->auto_set)!= '' && ($config->auto_set)!= null){ 
?>


<?php
 // auto set
 if(($config->auto_set)== '' || ($config->auto_set)== null){
?>    

  <div style="margin-bottom:12px;">
	<input type="button" id="autoset-button" class="button-primary rss_disable"  value="<?php echo __('Automatic Set [Article Extraction Settings]','wp-autopost'); ?>" />
	<input type="hidden" id="autosetenterurl_status" value="0"/>
	
	<div id="autosetenterurl" style="margin-top:5px;display:none;">
       <?php echo __('Enter a URL of article','wp-autopost'); ?>:<input type="text" name="autoset_url" id="autoset_url" value="<?php echo $_POST['autoset_url']; ?>" size="100" />
       <input type="button" class="button-primary"  value="<?php echo __('Submit'); ?>"  onclick="autosetSettings()"/>
	</div>
  </div>
  
  <table>
   <tr> 
    <td><strong><?php echo __('The Article Title Matching Rules','wp-autopost'); ?></strong></td>
   </tr>
   <tr> 
    <td>
	  <input type="hidden" id="title_match_type" value="<?php echo $config->title_match_type; ?>" />
	  <input class="title_match_type" type="radio" name="title_match_type" value="0" <?php if(($config->title_match_type)== 0) echo 'checked="true"'; ?> /><?php echo __('Use CSS Selector','wp-autopost'); ?>
	  &nbsp;
	  <input class="title_match_type" type="radio" name="title_match_type" value="1" <?php if(($config->title_match_type)== 1) echo 'checked="true"'; ?> /><?php echo __('Use Wildcards Match Pattern','wp-autopost'); ?> <span class="gray"><code><?php echo __('(*) is Wildcards','wp-autopost'); ?></code></span> 
	</td>
   </tr>
   <tr> 
    <td>
	  <div id="title_match_0"  <?php if(($config->title_match_type)!=0) echo 'style="display:none;"'; ?>>
        <?php echo __('CSS Selector','wp-autopost'); ?>: 
	    <input type="text" name="title_selector_0" id="title_selector_0" class="title_selector" size="40" value="<?php if(($config->title_match_type)== 0) echo htmlspecialchars($config->title_selector); ?>">
	    <span class="gray"><?php echo __('For example','wp-autopost'); ?>: #title h1</span>
	  </div>
	  <div id="title_match_1"  <?php if(($config->title_match_type)!=1) echo 'style="display:none;"'; ?>>
	    <?php
		  //兼容前面版本
	      if(strpos($config->title_selector,'WPAPSPLIT')===false){
             $match = explode('(*)',trim($config->title_selector));
		  }else{
             $match = explode('WPAPSPLIT',trim($config->title_selector));
		  }

	    ?>
		
        <table>
          <tr> 
            <td><?php echo __('Starting Unique HTML','wp-autopost'); ?>:</td>
			<td>
			  <input type="text" name="title_selector_1_start" id="title_selector_1_start" class="title_selector" size="40" value="<?php if(($config->title_match_type)== 1) echo htmlspecialchars($match[0]); ?>">
			  <span class="gray"> 
                <?php echo __('For example','wp-autopost'); ?>: &lt;h1 id="(*)" >
			  </span>
			</td>
		  </tr>
		  <tr>
            <td><?php echo __('Ending Unique HTML','wp-autopost'); ?>:</td>
			<td>
			  <input type="text" name="title_selector_1_end" id="title_selector_1_end" class="title_selector" size="40" value="<?php if(($config->title_match_type)== 1) echo htmlspecialchars($match[1]); ?>">
			  <span class="gray"> 
                <?php echo __('For example','wp-autopost'); ?>: &lt;/h1>
			  </span>
			</td>
		  </tr>
		</table>	
		
      </div>
	</td>
   </tr>
  </table>
  <br/>



  <?php
      $content_selector = json_decode($config->content_selector);
      if($content_selector==null){ //Compatible with previous versions
		  $content_selector = array();
		  $content_selector[0] = $config->content_selector;
	  }
      $content_match_types = json_decode($config->content_match_type);
      if(!is_array($content_match_types)){
		  $content_match_type = array();
		  $content_match_type[0] = $config->content_match_type;
		  $outer = array();
		  $outer[0] = 0;
		  $objective = array();
		  $objective[0]=0;
		  $index = array();
          $index[0] = 0;
	  }else{
	    $content_match_type = array();
		$outer = array();
		$objective = array();
		$index = array();
		foreach($content_match_types as $cmts){
          $cmt = explode(',',$cmts);
          $content_match_type[] = $cmt[0];
          $outer[]=$cmt[1];		  
		  if($cmt[2]==NULL||$cmt[2]=='')$objective[]=0;  //Compatible with previous versions
          else $objective[]=$cmt[2];    
		  if($cmt[3]==NULL||$cmt[3]=='')$index[]=0;  //Compatible with previous versions
          else $index[]=$cmt[3];		  
	    }
	  }
  ?>

<script type="text/javascript">


jQuery(document).ready(function($){ 
  
  <?php 
       $cmrNum = count($content_selector);
       for($i=0;$i<$cmrNum;$i++){ ?>  
    $('.content_match_type_<?php echo $i; ?>').change(function(){
	    var sSwitch = $(this).val();
        $("#content_match_type_<?php echo $i; ?>").val(sSwitch);
		if(sSwitch == 0){
           $("#content_match_0_<?php echo $i; ?>").show();
	       $("#content_match_1_<?php echo $i; ?>").hide();
		}else{
           $("#content_match_1_<?php echo $i; ?>").show();
	       $("#content_match_0_<?php echo $i; ?>").hide();
		}
	});
	
	$('#index_<?php echo $i; ?>').click(function(){
	   var s = $('#index_show_<?php echo $i; ?>').val(); 
	   if(s==0){
	     $("#index_num_<?php echo $i; ?>").show();
		 $('#index_show_<?php echo $i; ?>').val('1');
	   }else{
         $("#index_num_<?php echo $i; ?>").hide();
		 $('#index_show_<?php echo $i; ?>').val('0');
	   }
    });
  <?php } ?>

  <?php 
       for($i=1;$i<$cmrNum;$i++){ ?>  
    $('#objective_<?php echo $i; ?>').change(function(){
	    var sSwitch = $(this).val();
		if(sSwitch == -1){
           $("#objective_customfields_<?php echo $i; ?>").show();
		}else{
           $("#objective_customfields_<?php echo $i; ?>").hide();
		}
	});
  <?php } ?>
	 

});
</script>

  <strong><?php echo __('The Article Content Matching Rules','wp-autopost'); ?></strong>
  <table id="cmr" class="autoposttable">
   <tr> 
    <td>
	  <div>
	   <p>
	   <input type="hidden" id="content_match_type_0" value="<?php echo $content_match_type[0]; ?>" />
	   <input class="content_match_type content_match_type_0" type="radio" name="content_match_type_0" value="0" <?php if($content_match_type[0]== 0) echo 'checked="true"'; ?> /><?php echo __('Use CSS Selector','wp-autopost'); ?> 
	  &nbsp;
	   <input class="content_match_type content_match_type_0" type="radio" name="content_match_type_0" value="1" <?php if($content_match_type[0]== 1) echo 'checked="true"'; ?> /><?php echo __('Use Wildcards Match Pattern','wp-autopost'); ?>
	   <span class="gray"><code><?php echo __('(*) is Wildcards','wp-autopost'); ?></code></span>
	   &nbsp;
       <input type="checkbox" name="outer_0" id="outer_0" <?php if($outer[0]==1)echo 'checked="true"'; ?> /> <?php echo __('Contain The Outer HTML Text','wp-autopost'); ?>
	   </p>
	  </div> 
	  
	  <div id="content_match_0_0"  <?php if($content_match_type[0]!=0) echo 'style="display:none;"'; ?>>
       <table>
		<tr>
		<td>
		<?php echo __('CSS Selector','wp-autopost'); ?>: 
	    <input type="text" name="content_selector_0_0" id="content_selector_0_0" class="content_selector" size="40" value="<?php if($content_match_type[0]==0) echo htmlspecialchars($content_selector[0]); ?>" />     
		<span class="clickBold" id="index_0"><?php echo __('Index','wp-autopost'); ?></span><span id="index_num_0" <?php if($index[0]==0) echo 'style="display:none;"'; ?> >: 
		 <a title='<?php echo __('Default is 0:[extraction all matched content], 1:[extraction first matched content], -1:[extraction last matched content]','wp-autopost'); ?>'>[?]</a>
		 <input type="text" name="index_0" size="1" value="<?php echo $index[0]; ?>" />
		 <input type="hidden" id="index_show_0" value="<?php echo ($index[0]==0)?'0':'1'; ?>" />
		</span>
	    <br/><span class="gray"><?php echo __('For example','wp-autopost'); ?>: #entry</span>
	    </td>
		</tr>
		</table>
	  </div>
	  
	  <div id="content_match_1_0"  <?php if($content_match_type[0]!=1) echo 'style="display:none;"'; ?>>
	    
		<?php
		  //兼容前面版本
	      if(strpos($content_selector[0],'WPAPSPLIT')===false){
             $match = explode('(*)',trim($content_selector[0]));
		  }else{
             $match = explode('WPAPSPLIT',trim($content_selector[0]));
		  }

	    ?>
        
		<table>
          <tr> 
            <td><?php echo __('Starting Unique HTML','wp-autopost'); ?>:</td>
			<td>
			  <input type="text" name="content_selector_1_start_0" id="content_selector_1_start_0" class="content_selector" size="40" value="<?php if($content_match_type[0]==1) echo htmlspecialchars($match[0]); ?>">
			  <span class="gray"> 
                <?php echo __('For example','wp-autopost'); ?>: &ltdiv id="entry-(*)">
			  </span>
			  
			</td>
		  </tr>
		  <tr>
            <td><?php echo __('Ending Unique HTML','wp-autopost'); ?>:</td>
			<td>
			  <input type="text" name="content_selector_1_end_0" id="content_selector_1_end_0" class="content_selector" size="40" value="<?php if($content_match_type[0]==1) echo htmlspecialchars($match[1]); ?>">
			  <span class="gray"> 
                <?php echo __('For example','wp-autopost'); ?>: &lt/div>&lt!-- end entry -->
			  </span>
			</td>
		  </tr>
		</table>	
			  
	  </div>
	</td>
   </tr>
   <?php 
      $cmrNum = count($content_selector);
	  if($cmrNum>1){  
   ?>
<?php  for($i=1;$i<$cmrNum;$i++){ ?>
   
   <tr id="cmr<?php echo $i; ?>"> 
    <td>

	  <div class="apmatchtype">
	   <p>
	   <input type="hidden" id="content_match_type_<?php echo $i; ?>" value="<?php echo $content_match_type[$i]; ?>" />
	   <input class="content_match_type content_match_type_<?php echo $i; ?>" type="radio" name="content_match_type_<?php echo $i; ?>" value="0" <?php if($content_match_type[$i]== 0) echo 'checked="true"'; ?> /><?php echo __('Use CSS Selector','wp-autopost'); ?> 
	  &nbsp;
	   <input class="content_match_type content_match_type_<?php echo $i; ?>" type="radio" name="content_match_type_<?php echo $i; ?>" value="1" <?php if($content_match_type[$i]== 1) echo 'checked="true"'; ?> /><?php echo __('Use Wildcards Match Pattern','wp-autopost'); ?>
	  &nbsp;
       <input type="checkbox" name="outer_<?php echo $i; ?>" id="outer_<?php echo $i; ?>" <?php if($outer[$i]==1)echo 'checked="true"'; ?> /> <?php echo __('Contain The Outer HTML Text','wp-autopost'); ?>
	   <a style="float:right;" class="apdelete" title="delete" href="javascript:;" onclick="deleteRowCmr('cmr<?php echo $i; ?>')" ><?php echo __('Delete'); ?></a>
	  </p>

	  </div>
	  
	  <span id="content_match_0_<?php echo $i; ?>"  <?php if($content_match_type[$i]!=0) echo 'style="display:none;"'; ?>>
        <?php echo __('CSS Selector','wp-autopost'); ?>: 
	    <input type="text" name="content_selector_0_<?php echo $i; ?>" id="content_selector_0_<?php echo $i; ?>" class="content_selector" size="40" value="<?php if($content_match_type[$i]==0) echo htmlspecialchars($content_selector[$i]); ?>">
        
		<span class="clickBold" id="index_<?php echo $i; ?>"><?php echo __('Index','wp-autopost'); ?></span><span id="index_num_<?php echo $i; ?>" <?php if($index[$i]==0) echo 'style="display:none;"'; ?> >: 
		 <input type="text" name="index_<?php echo $i; ?>" size="1" value="<?php echo $index[$i]; ?>" />
		 <input type="hidden" id="index_show_<?php echo $i; ?>" value="<?php echo ($index[$i]==0)?'0':'1'; ?>" />
		</span>

	  </span>
	  <span id="content_match_1_<?php echo $i; ?>"  <?php if($content_match_type[$i]!=1) echo 'style="display:none;"'; ?>>
	    
		<?php
		  //兼容前面版本
	      if(strpos($content_selector[$i],'WPAPSPLIT')===false){
             $match = explode('(*)',trim($content_selector[$i]));
		  }else{
             $match = explode('WPAPSPLIT',trim($content_selector[$i]));
		  }

	    ?>

		<table>
          <tr> 
            <td><?php echo __('Starting Unique HTML','wp-autopost'); ?>:</td>
			<td>
			  <input type="text" name="content_selector_1_start_<?php echo $i; ?>" id="content_selector_1_start_<?php echo $i; ?>"  class="content_selector" size="40" value="<?php if($content_match_type[$i]==1) echo htmlspecialchars($match[0]); ?>">
			  
			</td>
		  </tr>
		  <tr>
            <td><?php echo __('Ending Unique HTML','wp-autopost'); ?>:</td>
			<td>
			  <input type="text" name="content_selector_1_end_<?php echo $i; ?>" id="content_selector_1_end_<?php echo $i; ?>" class="content_selector" size="40" value="<?php if($content_match_type[$i]==1) echo htmlspecialchars($match[1]); ?>">
			</td>
		  </tr>
		</table>
      
	  </span>
	  
	  <p>
	  <label><?php echo __('To: ','wp-autopost'); ?></label>
	  <select name="objective_<?php echo $i; ?>" id="objective_<?php echo $i; ?>">
           <option value="0" <?php selected( 0,$objective[$i] ); ?>><?php echo __('Post Content','wp-autopost'); ?></option>
		   <option value="2" <?php selected( 2,$objective[$i] ); ?>><?php echo __('Post Excerpt','wp-autopost'); ?></option>
		   <option value="3" <?php selected( 3,$objective[$i] ); ?>><?php echo __('Post Tags','wp-autopost'); ?></option>
		   <option value="5" <?php selected( 5,$objective[$i] ); ?>><?php echo __('Categories'); ?></option>
		   <option value="4" <?php selected( 4,$objective[$i] ); ?>><?php echo __('Featured Image'); ?></option>
		   <option value="1" <?php selected( 1,$objective[$i] ); ?>><?php echo __('Post Date','wp-autopost'); ?></option>
	       <option value="-1" <?php if($objective[$i]!='0'&&$objective[$i]!='1'&&$objective[$i]!='2'&&$objective[$i]!='3'&&$objective[$i]!='4'&&$objective[$i]!='5')echo 'selected = "true"'; ?>><?php echo __('Custom Fields'); ?></option>

<?php
$taxonomy_names = get_object_taxonomies( $config->post_type,'objects');        
foreach($taxonomy_names as $taxonomy){
  if($taxonomy->name=='category' || $taxonomy->name=='post_tag' || $taxonomy->name =='post_format')continue; 
?>
          <option value="Taxonomy:<?php echo $taxonomy->name; ?>"  <?php selected( 'Taxonomy:'.$taxonomy->name,$objective[$i] ); ?> ><?php echo __('Taxonomy','wp-autopost').' - '.$taxonomy->label; ?></option>;
<?php
}
?>

	  </select>
	  <span>
        <input id="objective_customfields_<?php echo $i; ?>" name="objective_customfields_<?php echo $i; ?>" <?php if($objective[$i]=='0'||$objective[$i]=='1'||$objective[$i]=='2'||$objective[$i]=='3'||$objective[$i]=='4'||$objective[$i]=='5' || !(strpos($objective[$i],'Taxonomy:') === false) )echo 'style="display:none;"';  ?>  type="text" value="<?php if($objective[$i]!='0'&&$objective[$i]!='1'&&$objective[$i]!='2'&&$objective[$i]!='3'&&$objective[$i]!='4'&&$objective[$i]!='5' && strpos($objective[$i],'Taxonomy:') === false) echo $objective[$i];?>" />
	  </span>
	  </p>
	  
	</td>
   </tr>   

<?php  }//end for ?>
<?php }//end if($cmrNum>1) ?>
  </table>

  <p>
  <a class="button" title="<?php echo __('If you also need to extract content on different areas','wp-autopost'); ?>"  onclick="addMoreMR()"/><?php echo __('Add More Matching Rules','wp-autopost'); ?></a>
  <input type="hidden" name="cmrNum" id="cmrNum"  value="<?php echo $cmrNum-1; ?>" />
  <input type="hidden" name="cmrTRLastIndex" id="cmrTRLastIndex"  value="<?php echo $cmrNum; ?>" />
  </p>
  
  <br/>
  
  <?php
     $page_selector = json_decode($config->page_selector);
     if($page_selector==null){ //Compatible with previous versions
		$page_selector = array();
		$page_selector[0] = 0;
		$page_selector[1] = $config->page_selector;
	 }
  ?>
  <input type="checkbox" name="fecth_paged" id="fecth_paged" class="rss_disable" <?php if(($config->fecth_paged)==1)echo 'checked="true"'; ?> /> <?php echo __('Extract The Paginated Content','wp-autopost'); ?> <span class="gray">(<?php echo __('If the article is divided into multiple pages','wp-autopost'); ?>)</span>
  
  <div id="page" <?php if(($config->fecth_paged)==0)echo 'style="display:none;"'; ?> >
  <table>
   <tr><td>  
	 <input class="fecth_paged_type rss_disable" type="radio" name="fecth_paged_type" value="0" <?php if($page_selector[0]== 0) echo 'checked="true"'; ?> /><?php echo __('Use CSS Selector','wp-autopost'); ?> 
	  &nbsp;
	 <input class="fecth_paged_type rss_disable" type="radio" name="fecth_paged_type" value="1" <?php if($page_selector[0]== 1) echo 'checked="true"'; ?> /><?php echo __('Use Wildcards Match Pattern','wp-autopost'); ?>

	<div id="page_match_0" <?php if($page_selector[0]!=0) echo 'style="display:none;"'; ?> >
	   <?php echo __('The Article Pagination URLs CSS Selector','wp-autopost'); ?>: <input type="text" name="page_selector_0" id="page_selector_0" class="rss_disable" size="80" value="<?php if($page_selector[0]==0) echo htmlspecialchars($page_selector[1]); ?>"><br/><span class="gray"><?php echo __('Must select to the HTML &lta> tag','wp-autopost'); ?>, <?php echo __('For example','wp-autopost'); ?>: #page_list a</span>
	</div>

	<div id="page_match_1" <?php if($page_selector[0]!=1) echo 'style="display:none;"'; ?>>
	   <?php echo __('The Article Pagination URLs Matching Rule','wp-autopost'); ?>: <input type="text" name="page_selector_1" id="page_selector_1" class="rss_disable" size="80" value="<?php if($page_selector[0]==1) echo htmlspecialchars($page_selector[1]); ?>"><br/><span class="gray">"<?php echo __('Starting unique HTML(*)End unique HTML','wp-autopost'); ?>"&nbsp;&nbsp;&nbsp;<?php echo __('For example','wp-autopost'); ?>: &ltdiv id="paged">(*)&lt/div></span>
	</div>
   
   </td></tr>
   <tr><td>
    <input type="checkbox" name="same_paged" id="same_paged" class="rss_disable" <?php if(($config->same_paged)==1)echo 'checked="true"'; ?> /> <?php echo __('Use the same pagination when published','wp-autopost'); ?>	 
   <td></tr>
  </table>
  </div>

<?php
 // auto set
 } // end if(($config->auto_set)== '' || ($config->auto_set)== null){
?> 
 
  <div>
    <br/>
	<input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>" <?php if(($config->auto_set)!= '' && ($config->auto_set)!= null){ echo 'disabled="true"'; }  ?> onclick="save3()"/>
    <input type="button" class="button"  value="<?php echo __('Test','wp-autopost'); ?>"  onclick="showTest3()"/>
  </div>
  
  <div id="test3" style="display:none;">
    <?php echo __('Enter the URL of test extraction','wp-autopost'); ?>:<input type="text" name="testUrl" id="testUrl" value="<?php echo $config->content_test_url; ?>" size="100" />
    <input type="button" class="button-primary"  value="<?php echo __('Submit'); ?>"  onclick="test3()"/>
  </div>
 </div>
</div>
<div class="clear"></div>
</form>



<form id="myform17"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php" enctype="multipart/form-data">
<input type="hidden" name="saction" value="save17">
<input type="hidden" name="saction17" id="saction17" value="">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
<input type="hidden" name="attach_id" id="attach_id" value="">


<?php
  $default_image = json_decode($config->default_image);
  if($default_image==null){ //Compatible with previous versions
    $default_image = array();
	$default_image[0] = 0;
	$default_image[1] = array();
  }
  if( $_POST['saction17']=='uploadDefaultImg') $default_image[0]=1;

  $selected_images = $default_image[1];

?>

<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;">
	<?php echo __('Default Featured Image Settings','wp-autopost'); ?>
  </h3>
  <div class="inside" <?php if(@!$showBox17)echo 'style="display:none;"' ?> >
   	
	 <div><?php echo __('If no images in the post OR set featured image failed in the above settings, will use one default featured image','wp-autopost'); ?><br/><br/></div>

	 <table width="100%"> 	         	  
      <tr> 
	    <td width="16%"><?php echo __('Use Default Featured Image','wp-autopost'); ?>:</td>
		<td>
		  <select id="use_default_image" name="use_default_image" >
            <option value="0" <?php if($default_image[0]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
			<option value="1" <?php if($default_image[0]==1) echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
		  </select>
		</td>
	  </tr>
    </table>


	<?php

	  $featuredImages = get_option('wp-autopost-featued-images');
	  $attchIds = array();
	  foreach($featuredImages as $attach_id){
	    if ( wp_attachment_is_image( $attach_id ) ) {	 
           $attchIds[] = $attach_id;
		}
	  }
	  if( count($attchIds) != count($featuredImages) ){
        update_option( 'wp-autopost-featued-images', $attchIds);
	  }
	?>

    <div id="default_image_area" <?php if($default_image[0]!=1 ){?> style="display:none;" <?php } ?> >
     <hr/>
	 
	 <?php
	  if( count($attchIds)==0 ):	 
	 ?>   
	    <p><code><?php echo __('No images now, you can upload some images','wp-autopost'); ?></code></p>
     <?php 
	 else: 
	 ?>
	    <p><?php echo __('You can select one or more images as a default featured image, if you selected more then one image will random use one of them','wp-autopost'); ?></p>

	 <?php 
		foreach($attchIds as $attchId){?>
		 <span>
		   <input type="hidden" name="selectedImgs[]" id="img_<?php echo $attchId; ?>"  value="<?php if(in_array($attchId,$selected_images)) echo $attchId; else echo '0';  ?>" />
		   <img class="default_imgs  <?php if(in_array($attchId,$selected_images)) echo 'selectedimg'; else echo 'noselecedimg';  ?>" id="<?php echo $attchId; ?>"  src="<?php echo wp_get_attachment_thumb_url( $attchId ) ?>" width="150" height="150"/>
		   <div class="action">
		     <a href="<?php echo get_permalink($attchId); ?>" target="_blank"><?php echo __('View'); ?></a> | <a href="javascript:void(0)" onclick="deleteDefaultImg(<?php echo $attchId; ?>)" class="apdelete"><?php echo __('Delete'); ?></a>
		   </div>
		 </span>	    
	<?php	
		}// end foreach($attchIds as $attchId){
	 ?>
	 <?php 
	 endif; 
	 ?>
     <div class="clear"></div>
	 <table id="image_type" class="form-table" >
       <tr>
        <th scope="row"><label><?php _e( 'Upload Image', 'wp-autopost' );?>:</label></th>
	    <td>
	      <input type="file" class="button" name="default-image" id="default-image" size="60" />
	      <input type="button" class="button" value="<?php _e( 'Upload Image', 'wp-autopost' );?>"  onclick="uploadDefaultImg()"/>
	    </td>
       </tr>
     </table>

	</div>


    <p>
	 <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"  onclick="save17()"/>
	</p>

  </div>
</div>
<div class="clear"></div>
</form>



<form id="myform15"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save15">
<input type="hidden" name="saction15" id="saction15" value="">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;">
    <span class="TipRss" <?php if(($config->source_type)!=2) echo 'style="display:none"'; ?> > 
      <?php echo __('Use RSS do not need this setting','wp-autopost'); ?>	 
    </span>
	<?php echo __('Site Login Settings','wp-autopost'); ?>
  </h3>
  <div class="inside" <?php if(@!$showBox15)echo 'style="display:none;"' ?> >
    
	<?php
      $loginSets = json_decode($config->cookie,TRUE);
	  
	  if(!is_array($loginSets)){      
		$loginSets['mode']=1;
	  }
	?>
    
	<div><?php echo __('Extract contents that need to login can view','wp-autopost'); ?><br/></div>
	
	<p>
    <input class="login_set_mode" type="radio" name="login_set_mode" value="1" <?php if($loginSets['mode']== 1) echo 'checked="true"'; ?> /><?php echo __('Set Login Detail','wp-autopost'); ?>
	&nbsp;&nbsp;&nbsp;&nbsp;
    <input class="login_set_mode" type="radio" name="login_set_mode" value="2" <?php if($loginSets['mode']== 2) echo 'checked="true"'; ?> /><?php echo __('Set Cookie','wp-autopost'); ?>
	</p>

    <div id="login_mode1" <?php if($loginSets['mode']!=1)echo 'style="display:none;"'; ?>>
      <table > 	         	  
       <tr> 
		 <td><?php echo __('The Login URL','wp-autopost'); //登陆URL  ?>:</td> 
		 <td>
		   <input type="text" name="login_url" id="login_url" value="<?php echo @$loginSets['url']; ?>" size="80" />
           <input type="button" class="button" value="<?php echo __('Extraction Parameters','wp-autopost'); ?>"  onclick="getLoginPara()" />
		 </td>
	   </tr>
       <tr>
         <td><?php echo __('The Submit Parameters','wp-autopost'); //提交参数  ?>:</td>
		 <td> 
		   <?php
		     $loginParas=null;
	         if(isset($loginSets['para']) && $loginSets['para']!=''){
		       $loginParas = explode('&',$loginSets['para']);
	         } 
		   ?>
		   <div id="login_para">
             <table id="login_para_table">		   
               <tr> 
			     <th><?php echo __('Parameter Name','wp-autopost'); ?></th>
				 <th><?php echo __('Parameter Value','wp-autopost'); ?></th>
			   </tr>
			<?php 		 
		     if($loginParas!=null):
			  $num=0; 
		      foreach($loginParas as $loginPara){
				 $num++;
				 $para = explode('=',$loginPara);
			?>
			   <tr id="login_para_table_tr<?php echo $num; ?>" > 
			     <td><input type="text" name="loginParaName[]"  value="<?php echo $para[0]; ?>" /></td>
				 <td><input type="text" name="loginParaValue[]" value="<?php echo $para[1]; ?>" /></td>
				 <td><input type="button" class="button" value="<?php echo __('Delete'); ?>"  onclick="deleteLoginPara('login_para_table_tr<?php echo $num; ?>')" /></td>
			   </tr>
	    <?php }//end foreach($loginParas as $loginPara){  ?>
		<?php
            else:
			  $num=1;
		?>   
              <tr id="login_para_table_tr<?php echo $num; ?>"> 
			    <td><input type="text" name="loginParaName[]" value="" /></td>
				<td><input type="text" name="loginParaValue[]" value="" /></td>
				<td><input type="button" class="button" value="<?php echo __('Delete'); ?>"  onclick="deleteLoginPara('login_para_table_tr<?php echo $num; ?>')" /></td>
			  </tr>  
		<?php
		    endif;
		?>
			 </table>
			 <input type="hidden" name="login_para_tableTRLastIndex" id="login_para_tableTRLastIndex"  value="<?php echo $num+1; ?>" />
		   </div>
		   <input type="button" class="button" value="<?php echo __('Add New','wp-autopost'); ?>"  onclick="AddLoginPara()" />
		 </td>
	   </tr>
	  </table>  
		
	</div>


	<div id="login_mode2" <?php if($loginSets['mode']!=2)echo 'style="display:none;"'; ?>>
      <p>
        <a class='add-new-h2' href='http://wp-autopost.org/zh/manual/how-to-get-cookie/' target='_blank' ><?php echo __('How to get Cookie?','wp-autopost'); ?></a>
	  </p>
	  

	  <table width="100%"> 	         	  
       <tr> 
		 <td>Cookie:</td>
		 <td>
		   <textarea cols="100" rows="12" name="the_cookie"><?php echo @$loginSets['cookie']; ?></textarea>
		 </td>
	   </tr>
	  </table>

	  <p>
       <span class="gray"><code>Tips: <?php echo __('Cookie may expire, then need to update the Cookie.','wp-autopost'); ?></code></span>
	  </p>
		
	</div>

    
	<p>
	 <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"  onclick="save15()"/>
	 <input type="button" class="button"  value="<?php echo __('Test','wp-autopost'); ?>"  onclick="showTestCookie()"/>
	</p>
	
	<div id="testCookie" style="display:none;" >
      <?php echo __('Enter the URL of test extraction','wp-autopost'); ?>:<input type="text" name="testcCookieUrl" id="testcCookieUrl" value="<?php echo $_POST['testcCookieUrl']; ?>" size="100" />
      <input type="button" class="button-primary"  value="<?php echo __('Submit'); ?>"  onclick="testCookie()"/>
    </div>

  </div>
</div>
</form>

<?php if(get_bloginfo('language')=='zh-CN'||get_bloginfo('language')=='zh-TW'): ?> 
<form id="myform16"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save16">
<input type="hidden" name="saction16" id="saction16" value="">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;">中文 简体/繁体 转换</h3>
  <div class="inside" <?php if(@!$showBox16)echo 'style="display:none;"' ?> >
    
	<div>将中文简体文章转换为繁体（或将中文繁体文章转换为简体），获取唯一性和可读性都具备的文章。<br/><br/></div>
    
	<table width="100%"> 	         	  
      <tr> 
	    <td width="16%">转换为:</td>
		<td>
		  <select id="zh_conversion" name="zh_conversion" >
            <option value="" <?php if($config->zh_conversion==null || $config->zh_conversion=='') echo 'selected="true"'; ?>>不转换</option>
			<option value="zh-hans" <?php if($config->zh_conversion=='zh-hans') echo 'selected="true"'; ?>>简体中文</option>
			<option value="zh-hant" <?php if($config->zh_conversion=='zh-hant') echo 'selected="true"'; ?>>繁體中文</option>
			<option value="zh-hk"   <?php if($config->zh_conversion=='zh-hk') echo 'selected="true"'; ?>>港澳繁體</option>
			<option value="zh-tw"   <?php if($config->zh_conversion=='zh-tw') echo 'selected="true"'; ?>>台灣正體</option>
		  </select>
		</td>
	  </tr>
    </table>
    
	
	 <p><input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"  onclick="save16()"/></p>
	

  </div>
</div>
</form>
<?php endif; // end if(get_bloginfo('language')=='zh-CN'||get_bloginfo('language')=='zh-TW'): ?>

<?php
   $rewrite = json_decode($config->use_rewrite);
   if(!is_array($rewrite)){
      $rewrite = array();
	  $rewrite[0]=0;
   }
?>

<?php
  $MicroTransOptions = get_option('wp-autopost-micro-trans-options');
  $transSetOk=false;
  if($MicroTransOptions!=null)foreach($MicroTransOptions as $k => $v){ 
	if($v['clientID']!=null&&$v['clientSecret']!=null){
	  $transSetOk=true;
	  break;
	}
  }
  
  $BaiduTransOptions = get_option('wp-autopost-baidu-trans-options');
  $transSetOk_Baidu=false;
  if($BaiduTransOptions['api_key']!=null&&$BaiduTransOptions['api_key']!=''){
    $transSetOk_Baidu=true;
  }
?>
<form id="myform5"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save5">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Rewrite (Spinning)','wp-autopost'); ?></h3>
  <div class="inside" <?php if(@!$showBox13)echo 'style="display:none;"' ?> >
    
	<table width="100%"> 	         	  
      <tr> 
	    <td width="16%"><?php echo __('Use Rewriter','wp-autopost'); ?>:</td>
		<td>
		  <select id="use_rewriter" name="use_rewriter" >
            <option value="0" <?php if($rewrite[0]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
			<option value="1" <?php if($rewrite[0]==1) echo 'selected="true"'; ?>><?php echo __('Microsoft Translator','wp-autopost'); ?></option>
			<option value="4" <?php if($rewrite[0]==4) echo 'selected="true"'; ?>><?php echo __('Baidu Translator','wp-autopost'); ?></option>
		    <option value="2" <?php if($rewrite[0]==2) echo 'selected="true"'; ?>><?php echo __('WordAi'); ?></option>
			<option value="3" <?php if($rewrite[0]==3) echo 'selected="true"'; ?>><?php echo __('Spin Rewriter'); ?></option>
		  </select>
		</td>
	  </tr>
    </table>

   <div id="MicrosoftTranslator" <?php if($rewrite[0]!=1){?> style="display:none;" <?php } ?> >
<?php
  if(!$transSetOk){ ?>
    <div style="background-color:#ffebe8;border-color:#cc0000;border-style:solid;border-width:1px;padding:10px;">    
	   <?php echo __('To use this feature, please set up correctly in','wp-autopost'); ?>
       <a href="admin.php?page=wp-autopost-pro/wp-autopost-translator.php" > 
	   <?php echo __('Microsoft Translator Options','wp-autopost'); ?>
	  </a>
    </div> 
<?php } ?>
   <p><?php echo __("Use Microsoft Translator can get very unique article, <strong>and is absolutely free</strong>.",'wp-autopost'); ?></p>
   
   <?php
   if($rewrite[0]!=1){
     if(get_bloginfo('language')=='zh-CN'||get_bloginfo('language')=='zh-TW'):
       $rewrite_origi_language = 'zh-CHS';
       $rewrite_trans_language = 'en';
	 else:
       $rewrite_origi_language = 'en';
       $rewrite_trans_language = 'zh-CHS';
	 endif;	 
   }else{
     $rewrite_origi_language = $rewrite[1];
	 $rewrite_trans_language = $rewrite[2];
   }

   ?>

    <p><code><?php echo __('Will first translate into','wp-autopost'); ?> <strong><span id="rewrite_trans_language_span"><?php echo autopostMicrosoftTranslator::get_lang_by_code($rewrite_trans_language); ?></span></strong> <?php echo __('and then translated back to','wp-autopost'); ?> <strong><span id="rewrite_origi_language_span"><?php echo autopostMicrosoftTranslator::get_lang_by_code($rewrite_origi_language); ?></span></strong></code></p>
   
    <table width="100%">
	  <tr> 
	    <td width="16%"><?php echo __('Original Language','wp-autopost'); ?>:</td>
		<td>
		  <select id="rewrite_origi_language" name="rewrite_origi_language" >
            <?php echo autopostMicrosoftTranslator::bulid_lang_options($rewrite_origi_language); ?>
		  </select>
		</td>
	  </tr>

	  <tr> 
	    <td width="16%"><?php echo __('Translate Language','wp-autopost'); ?>:</td>
		<td>
		  <select id="rewrite_trans_language" name="rewrite_trans_language" >
            <?php echo autopostMicrosoftTranslator::bulid_lang_options($rewrite_trans_language); ?>
		  </select>
		</td>
	  </tr>
    </table>

	<table width="100%">
	  <tr>
        <td colspan="2" style="height:36px;"><input type="checkbox" name="rewrite_title_1" <?php if(isset($rewrite[3])&&$rewrite[3]==1)echo 'checked="true"'; ?> /> <?php echo __('Also Rewrite The Title','wp-autopost'); ?></td>
	  </tr>
	</table>

	<table width="100%">
	  <tr>
        <td colspan="2" style="height:36px;"><input type="checkbox" name="rewrite_failure_1" <?php if(isset($rewrite[4])&&$rewrite[4]==1)echo 'checked="true"'; ?> /> <?php echo __('When Rewrite Failure Will Not Publish','wp-autopost'); ?></td>
	  </tr>
	</table>
    
   

   </div><!-- end <div id="MicrosoftTranslator" -->



     <div id="baiduTranslator" <?php if($rewrite[0]!=4){?> style="display:none;" <?php } ?> >
<?php
  if(!$transSetOk_Baidu){ ?>
    <div style="background-color:#ffebe8;border-color:#cc0000;border-style:solid;border-width:1px;padding:10px;">    
	   <?php echo __('To use this feature, please set up correctly in','wp-autopost'); ?>
       <a href="admin.php?page=wp-autopost-pro/wp-autopost-translator-baidu.php" > 
	   <?php echo __('Baidu Translator Options','wp-autopost'); ?>
	  </a>
    </div> 
<?php } ?>
   <p><?php echo __("Use Baidu Translator can get very unique article, <strong>and is absolutely free</strong>.",'wp-autopost'); ?></p>
   
   <?php
   if($rewrite[0]!=4){
     if(get_bloginfo('language')=='zh-CN'||get_bloginfo('language')=='zh-TW'):
       $rewrite_origi_language = 'zh';
       $rewrite_trans_language = 'en';
	 else:
       $rewrite_origi_language = 'en';
       $rewrite_trans_language = 'zh';
	 endif;	 
   }else{
     $rewrite_origi_language = $rewrite[1];
	 $rewrite_trans_language = $rewrite[2];
   }

   ?>

   <p><code><?php echo __('Will first translate into','wp-autopost'); ?> <strong><span id="rewrite_trans_language_span_baidu"><?php echo autopostBaiduTranslator::get_lang_by_code($rewrite_trans_language); ?></span></strong> <?php echo __('and then translated back to','wp-autopost'); ?> <strong><span id="rewrite_origi_language_span_baidu"><?php echo autopostBaiduTranslator::get_lang_by_code($rewrite_origi_language); ?></span></strong></code></p>
   
    <table width="100%">
	  <tr> 
	    <td width="16%"><?php echo __('Original Language','wp-autopost'); ?>:</td>
		<td>
		  <select id="rewrite_origi_language_baidu" name="rewrite_origi_language_baidu" >
            <?php echo autopostBaiduTranslator::bulid_lang_options($rewrite_origi_language); ?>
		  </select>
		</td>
	  </tr>

	  <tr> 
	    <td width="16%"><?php echo __('Translate Language','wp-autopost'); ?>:</td>
		<td>
		  <select id="rewrite_trans_language_baidu" name="rewrite_trans_language_baidu" >
            <?php echo autopostBaiduTranslator::bulid_lang_options($rewrite_trans_language); ?>
		  </select>
		</td>
	  </tr>
      
	   <tr>
        <td>
		 <?php echo __('Protected Words','wp-autopost'); ?>:
		</td>
		<td>
           <span class="gray"><span class="gray"><?php echo __('Protected Word will not rewrite','wp-autopost'); ?> </span> (<?php echo __('Separated with a comma','wp-autopost'); ?>)</span><br/>
		   
		   <textarea style="width:100%" name="rewrite_protected_words_baidu" id="rewrite_protected_words_baidu" ><?php echo @$rewrite[5];?></textarea>
           
		   <code><?php echo __('Tips: support use variables : ','wp-autopost'); ?>  <strong>{<?php echo __('custom_field_name','wp-autopost');?>}</strong> </code>
		</td>
	  </tr>

    </table>

	<table width="100%">
	  <tr>
        <td colspan="2" style="height:36px;"><input type="checkbox" name="rewrite_title_4" <?php if(isset($rewrite[3])&&$rewrite[3]==1)echo 'checked="true"'; ?> /> <?php echo __('Also Rewrite The Title','wp-autopost'); ?></td>
	  </tr>
	</table>

	<table width="100%">
	  <tr>
        <td colspan="2" style="height:36px;"><input type="checkbox" name="rewrite_failure_4" <?php if(isset($rewrite[4])&&$rewrite[4]==1)echo 'checked="true"'; ?> /> <?php echo __('When Rewrite Failure Will Not Publish','wp-autopost'); ?></td>
	  </tr>
	</table>
    
    

   </div><!-- end <div id="baiduTranslator" -->







   <div id="WordAi" <?php if($rewrite[0]!=2){?> style="display:none;" <?php } ?> >
    <p><?php echo __("Use WordAi can get unique and readable article.",'wp-autopost'); ?></p>
	<p><?php echo __("Has no <strong>WordAi</strong> account, <a class='add-new-h2' href='http://wp-autopost.org/go/?site=WordAi' target='_blank' >visit WordAi for service</a>",'wp-autopost'); ?></p>
	<table width="100%">
	  <tr> 
	    <td width="16%"><?php echo __('User Email','wp-autopost'); ?>:</td>
		<td>
		  <input type="text" name="wordai_user_email"  value="<?php if(isset($rewrite[1]))echo $rewrite[1]; ?>" />
		</td>
	  </tr>
	  <tr> 
	    <td width="16%"><?php echo __('User Password','wp-autopost'); ?>:</td>
		<td>
		  <input type="text" name="wordai_user_password"  value="<?php if(isset($rewrite[2]))echo $rewrite[2]; ?>" />
		</td>
	  </tr>
      <tr> 
	    <td width="16%"><?php echo __('Spinner','wp-autopost'); ?>:</td>
		<td>
		  <select name="wordai_spinner" id="wordai_spinner">
            <option value="1" <?php if(isset($rewrite[3])&&$rewrite[3]==1) echo 'selected="true"'; ?>><?php echo __('Standard Spinner'); ?></option>
		    <option value="2" <?php if(isset($rewrite[3])&&$rewrite[3]==2) echo 'selected="true"'; ?>><?php echo __('Turing Spinner'); ?></option>
		  </select>
		</td>
	  </tr>
	  <tr> 
	    <td width="16%"><?php echo __('Spinning Quality','wp-autopost'); ?>:</td>
		<td>
		   <select name="standard_quality" id="standard_quality" <?php if(@($rewrite[3]!=1&&$rewrite[3]!=null))echo 'style="display:none;"'; ?> >
			   <option value="0" <?php if(isset($rewrite[4])&&$rewrite[4]==0) echo 'selected="true"'; ?> >Extremely Unique</option>
               <option value="25" <?php if(isset($rewrite[4])&&$rewrite[4]==25) echo 'selected="true"'; ?>>Very Unique</option>
               <option value="50" <?php if(isset($rewrite[4])&&$rewrite[4]==50) echo 'selected="true"'; ?>>Unique</option>
               <option value="75" <?php if(isset($rewrite[4])&&$rewrite[4]==75) echo 'selected="true"'; ?>>Regular</option>
               <option value="100" <?php if(!isset($rewrite[4]) || ($rewrite[4]==100||$rewrite[4]==null)) echo 'selected="true"'; ?>>Readable</option>
               <option value="150" <?php if(isset($rewrite[4])&&$rewrite[4]==150) echo 'selected="true"'; ?>>Very Readable</option>
               <option value="200" <?php if(isset($rewrite[4])&&$rewrite[4]==200) echo 'selected="true"'; ?>>Extremely Readable</option>
           </select>

		   <select name="turing_quality" id="turing_quality" <?php if(@($rewrite[3]!=2))echo 'style="display:none;"'; ?> >
              <option value="Very Unique" <?php if(isset($rewrite[4])&& $rewrite[4]=='Very Unique') echo 'selected="true"'; ?> >Very Unique</option>
              <option value="Unique" <?php if(isset($rewrite[4])&& $rewrite[4]=='Unique') echo 'selected="true"'; ?>>Unique</option>
			  <option value="Normal" <?php if(isset($rewrite[4])&& $rewrite[4]=='Normal') echo 'selected="true"'; ?>>Regular</option>
			  <option value="Readable" <?php if(!isset($rewrite[4]) || ($rewrite[4]=='Readable'||$rewrite[4]==null)) echo 'selected="true"'; ?>>Readable</option>
			  <option value="Very Readable" <?php if(isset($rewrite[4])&& $rewrite[4]=='Very Readable') echo 'selected="true"'; ?>>Very Readable</option>
		  </select>

		</td>
	  </tr>
      
	  <tr> 
	    <td width="16%"></td>
		<td>
          <select name="standard_nonested" id="standard_nonested" <?php if(@($rewrite[3]!=1&&$rewrite[3]!=null))echo 'style="display:none;"'; ?>>
            <option value="off" <?php if(isset($rewrite[5])&&$rewrite[5]=='off') echo 'selected="true"'; ?>>Automatically Rewrite Sentences (Nested Spintax)</option>
            <option value="on" <?php if(isset($rewrite[5])&&$rewrite[5]=='on') echo 'selected="true"'; ?>>Don't Automatically Rewrite Sentences</option>
          </select>

		  <select name="turing_nonested" id="turing_nonested" <?php if(@($rewrite[3]!=2))echo 'style="display:none;"'; ?>>
            <option value="on" <?php if(isset($rewrite[5])&&$rewrite[5]=='on') echo 'selected="true"'; ?>>Automatically Rewrite Sentences (Nested Spintax)</option>
            <option value="off" <?php if(isset($rewrite[5])&&$rewrite[5]=='off') echo 'selected="true"'; ?>>Don't Automatically Rewrite Sentences</option>
          </select>
		</td>
      </tr>

	  <tr> 
	    <td width="16%"></td>
		<td>
          <select name="wordai_sentence">
            <option value="on" <?php if(isset($rewrite[6])&&$rewrite[6]=='on') echo 'selected="true"'; ?>>Automatically Add/Remove Sentences (Nested Spintax)</option>
            <option value="off" <?php if(isset($rewrite[6])&&$rewrite[6]=='off') echo 'selected="true"'; ?>>Don't Automatically Add/Remove Sentences</option>
          </select>
		</td>
      </tr>

	  <tr> 
	    <td width="16%"></td>
		<td>
          <select name="wordai_paragraph">
            <option value="on" <?php if(isset($rewrite[7])&&$rewrite[7]=='on') echo 'selected="true"'; ?>>Automatically Spin Paragraphs and Lists (Nested Spintax)</option>
            <option value="off" <?php if(isset($rewrite[7])&&$rewrite[7]=='off') echo 'selected="true"'; ?>>Don't Automatically Spin Paragraphs and Lists</option>
          </select>
		</td>
      </tr>
    </table>

	<table width="100%">
	  <tr>
        <td colspan="2" style="height:36px;"><input type="checkbox" name="rewrite_title_2" <?php if(isset($rewrite[8])&&$rewrite[8]==1)echo 'checked="true"'; ?> /> <?php echo __('Also Rewrite The Title','wp-autopost'); ?></td>
	  </tr>
	</table>

	<table width="100%">
	  <tr>
        <td colspan="2" style="height:36px;"><input type="checkbox" name="rewrite_failure_2" <?php if(isset($rewrite[9])&&$rewrite[9]==1)echo 'checked="true"'; ?> /> <?php echo __('When Rewrite Failure Will Not Publish','wp-autopost'); ?></td>
	  </tr>
	</table>

   </div><!-- end  id="WordAi" -->


   <div id="SpinRewriter" <?php if($rewrite[0]!=3){?> style="display:none;" <?php } ?> >
    <p><?php echo __("Use Spin Rewriter can get unique and readable article.",'wp-autopost'); ?></p>
	<p><?php echo __("Has no <strong>Spin Rewriter</strong> account, <a class='add-new-h2' href='http://wp-autopost.org/go/?site=SpinRewriter' target='_blank' >visit Spin Rewriter for service</a>",'wp-autopost'); ?></p>
	<table width="100%">
	  <tr> 
	    <td width="16%"><?php echo __('User Email','wp-autopost'); ?>:</td>
		<td>
		  <input type="text" name="spin_rewriter_user_email"  value="<?php if(isset($rewrite[1]))echo $rewrite[1]; ?>" />
		</td>
	  </tr>
	  <tr> 
	    <td width="16%"><?php echo __('Your Unique API Key','wp-autopost'); ?>:</td>
		<td>
		  <input type="text" name="spin_rewriter_api_key"  value="<?php if(isset($rewrite[2]))echo $rewrite[2]; ?>" />
		</td>
	  </tr>
	</table>
    
	<table width="100%">
	  <tr> 
	    <td colspan="2">
		  <input type="checkbox" name="spin_rewriter_auto_sentences" <?php if(isset($rewrite[3])&&$rewrite[3]==1)echo 'checked="true"'; ?> /> I want Spin Rewriter to automatically rewrite complete sentences.
		</td>
	  </tr>
	  <tr> 
	    <td colspan="2">
		  <input type="checkbox" name="spin_rewriter_auto_paragraphs" <?php if(isset($rewrite[4])&&$rewrite[4]==1)echo 'checked="true"'; ?> /> I want Spin Rewriter to automatically rewrite entire paragraphs.
		</td>
	  </tr>
	  <tr> 
	    <td colspan="2">
		  <input type="checkbox" name="spin_rewriter_auto_new_paragraphs" <?php if(isset($rewrite[5])&&$rewrite[5]==1)echo 'checked="true"'; ?> /> I want Spin Rewriter to automatically write additional paragraphs on its own.
		</td>
	  </tr>
	  <tr> 
	    <td colspan="2">
		  <input type="checkbox" name="spin_rewriter_auto_sentence_trees" <?php if(isset($rewrite[6])&&$rewrite[6]==1)echo 'checked="true"'; ?> /> I want Spin Rewriter to automatically change the entire structure of phrases and sentences.
		</td>
	  </tr>
	</table>

	<table width="100%">
	  <tr> 
	    <td width="23%">How Adventurous Are You Feeling?</td>
		<td>
		  <select name="spin_rewriter_confidence_level" >
		    <option value="high" <?php if(isset($rewrite[7])&& $rewrite[7]=='high') echo 'selected="true"'; ?> >generate as many suggestions as possible (high risk)</option>
			<option value="medium" <?php if(!isset($rewrite[7])||($rewrite[7]=='medium'||$rewrite[7]==null)) echo 'selected="true"'; ?>>use suggestions that you believe are correct (recommended)</option>
			<option value="low" <?php if(isset($rewrite[7])&& $rewrite[7]=='low') echo 'selected="true"'; ?> >only use suggestions that you're really confident about (low risk)</option>
		  </select>
		</td>
	  </tr>

	  <tr> 
	    <td colspan="2">
		  <input type="checkbox" name="spin_rewriter_nested_spintax" <?php if(isset($rewrite[8])&& $rewrite[8]==1)echo 'checked="true"'; ?> /> find synonyms for single words inside spun phrases as well (multi-level nested spinning)
		</td>
	  </tr>

	  <tr> 
	    <td colspan="2">
		  <input type="checkbox" name="spin_rewriter_auto_protected_terms" <?php if(isset($rewrite[9])&& $rewrite[9]==1)echo 'checked="true"'; ?> /> automatically protect Capitalized Words (except in the title of the article)
		</td>
	  </tr>

	</table>


    
	<table width="100%">
	  <tr>
        <td colspan="2" style="height:36px;"><input type="checkbox" name="rewrite_title_3" <?php if(isset($rewrite[10])&& $rewrite[10]==1)echo 'checked="true"'; ?> /> <?php echo __('Also Rewrite The Title','wp-autopost'); ?></td>
	  </tr>
	</table>

	<table width="100%">
	  <tr>
        <td colspan="2" style="height:36px;"><input type="checkbox" name="rewrite_failure_3" <?php if(isset($rewrite[11])&&$rewrite[11]==1)echo 'checked="true"'; ?> /> <?php echo __('When Rewrite Failure Will Not Publish','wp-autopost'); ?></td>
	  </tr>
	</table>


   </div> <!-- end  id="SpinRewriter" -->
   

   <p><input type="submit" class="button-primary"  value="<?php echo __('Save Changes'); ?>" /></p>
	
  </div>
</div>


</form>



<form id="myform4"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save4">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">

<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Translate','wp-autopost'); ?></h3> 
  <div class="inside" <?php if(@!$showBox10)echo 'style="display:none;"' ?> >

<?php
   $use_trans = json_decode($config->use_trans);
   if(!is_array($use_trans)){
      $use_trans = array();
	  $use_trans[0]=0;
	  $use_trans[1]='';
	  $use_trans[2]='';
	  $use_trans[3]=-1;
   }
?>
    <table width="100%"> 	         	  
      <tr> 
	    <td width="16%"><?php echo __('Use Translator','wp-autopost'); ?>:</td>
		<td>
		  <select id="use_trans" name="use_trans" >
            <option value="0" <?php if($use_trans[0]==0) echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
		    <option value="1" <?php if($use_trans[0]==1) echo 'selected="true"'; ?>><?php echo __('Microsoft Translator','wp-autopost'); ?></option>
			<option value="2" <?php if($use_trans[0]==2) echo 'selected="true"'; ?>><?php echo __('Baidu Translator','wp-autopost'); ?></option>
		  </select>
		</td>
	  </tr>
	 </table>

  <div id="Translator1" <?php if($use_trans[0]!=1){?> style="display:none;" <?php } ?> > 
	
<?php
  if(!$transSetOk){ ?>
    <div style="background-color:#ffebe8;border-color:#cc0000;border-style:solid;border-width:1px;padding:10px;">    
	   <?php echo __('To use this feature, please set up correctly in','wp-autopost'); ?>
       <a href="admin.php?page=wp-autopost-pro/wp-autopost-translator.php" > 
	   <?php echo __('Microsoft Translator Options','wp-autopost'); ?>
	  </a>
    </div> 
<?php } ?>
	
	<table width="100%"> 
      <tr> 
	    <td width="16%"><?php echo __('Original Language','wp-autopost'); ?>:</td>
		<td>
		  <select id="translator1_from_Language" name="translator1_from_Language" >
            <?php echo autopostMicrosoftTranslator::bulid_lang_options($use_trans[1]); ?>
		  </select>
		</td>
	  </tr>

	  <tr> 
	    <td width="16%"><?php echo __('Translated into','wp-autopost'); ?>:</td>
		<td>
		  <select id="translator1_to_Language" name="translator1_to_Language" >
            <?php echo autopostMicrosoftTranslator::bulid_lang_options($use_trans[2]); ?>
		  </select>
		</td>
	  </tr>
    </table>
  </div>
  

  <div id="Translator2" <?php if($use_trans[0]!=2){?> style="display:none;" <?php } ?> >

<?php
  if(!$transSetOk_Baidu){ ?>
    <div style="background-color:#ffebe8;border-color:#cc0000;border-style:solid;border-width:1px;padding:10px;">    
	   <?php echo __('To use this feature, please set up correctly in','wp-autopost'); ?>
       <a href="admin.php?page=wp-autopost-pro/wp-autopost-translator-baidu.php" > 
	   <?php echo __('Baidu Translator Options','wp-autopost'); ?>
	  </a>
    </div> 
<?php } ?>    
	
	<table width="100%"> 
      <tr> 
	    <td width="16%"><?php echo __('Original Language','wp-autopost'); ?>:</td>
		<td>
		  <select id="translator2_from_Language" name="translator2_from_Language" >
            <?php echo autopostBaiduTranslator::bulid_lang_options($use_trans[1]); ?>
		  </select>
		</td>
	  </tr>

	  <tr> 
	    <td width="16%"><?php echo __('Translated into','wp-autopost'); ?>:</td>
		<td>
		  <select id="translator2_to_Language" name="translator2_to_Language" >
            <?php echo autopostBaiduTranslator::bulid_lang_options($use_trans[2]); ?>
		  </select>
		</td>
	  </tr>
    </table>
  </div>

  <div id="use_translator" <?php if($use_trans[0]==0){?> style="display:none;" <?php } ?> >
    <table width="100%">
	  <tr> 
	    <td width="16%"><?php echo __('Post Method','wp-autopost'); ?>:</td>
		<td>
		  <select id="post_method" name="post_method" >
            <option value="-1" <?php if($use_trans[3]==-1) echo 'selected="true"'; ?>><?php echo __('Only Post The Translation','wp-autopost'); ?></option>
			<option value="-2" <?php if($use_trans[3]==-2) echo 'selected="true"'; ?>><?php echo __('Post Original And Translation (Mode 1)','wp-autopost'); ?></option>
            <option value="-3" <?php if($use_trans[3]==-3) echo 'selected="true"'; ?>><?php echo __('Post Original And Translation (Mode 2)','wp-autopost'); ?></option>
			<option value="0" <?php if($use_trans[3]!=-1 && $use_trans[3]!=-2 && $use_trans[3]!=-3) echo 'selected="true"'; ?>><?php echo __('Post Original And Translation (Mode 3)','wp-autopost'); ?></option>		
		  </select>
		</td>
	  </tr>


	  <tr id="translated_cat1" <?php if($use_trans[3]!=-2) echo 'style="display:none;"'; ?> >
       <td> 
       </td>
	   <td>
	      <p><?php echo __('Original and the translation in the same article.','wp-autopost'); ?></p> 
		  <p><strong><?php echo __('In front of the full original, followed by full translation.','wp-autopost'); ?></strong></p>
          <table border="1" style="width:300px;">
			 <tr>
              <td style="text-align:center;">
			    <div style="height:100px;line-height:100px;"><?php echo __('The full original article.','wp-autopost'); ?></div> 
				<hr/>
                <div style="height:100px;line-height:100px;"><?php echo __('The full translation article.','wp-autopost'); ?> </div>
			  </td>
			 </tr>
		  </table>
	   </td>
	  </tr>


	  <tr id="translated_cat2" <?php if($use_trans[3]!=-3) echo 'style="display:none;"'; ?> >
       <td> 
       </td>
	   <td>
         <p><?php echo __('Original and the translation in the same article.','wp-autopost'); ?></p> 
		 <p><strong><?php echo __('One paragraph is original, the following paragraph is translation, and so on.','wp-autopost'); ?></strong></p>
          <table border="1" style="width:300px;">
			 <tr>
              <td style="text-align:center;">
			    <div style="margin-top:15px;"><?php echo __('The first original paragraph.','wp-autopost'); ?><br/><?php echo __('The translated text.','wp-autopost'); ?></div> 
                <div style="margin-top:15px;"><?php echo __('The second original paragraph.','wp-autopost'); ?><br/><?php echo __('The translated text.','wp-autopost'); ?></div>
				<div style="margin-top:15px;margin-bottom:15px;"><?php echo __('And so on','wp-autopost'); ?></div>
			  </td>
			 </tr>
		  </table>
	   </td>
	  </tr>

	  <tr id="translated_cat3" <?php if($use_trans[3]==-1 || $use_trans[3]==-2 || $use_trans[3]==-3) echo 'style="display:none;"'; ?> >
       <td> 
	     <?php echo __('Translated Categories','wp-autopost'); ?>:
       </td>
	   <td>
	     <p><?php echo __('Original in one article, the translation in another article.','wp-autopost'); ?></p>
         <?php $selected_cats = explode(',',$use_trans[3]);   ?>
         <ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
            <?php @wp_category_checklist( $post_id, $descendants_and_self, $selected_cats, $popular_cats, $walker, $checked_ontop);?>
         </ul> 
	   </td>
	  </tr>

     
	  <tr>
        <td>
		 <?php echo __('Protected Words','wp-autopost'); ?>:
		</td>
		<td>
           <span class="gray"><span class="gray"><?php echo __('Protected Word will not translated','wp-autopost'); ?> </span> (<?php echo __('Separated with a comma','wp-autopost'); ?>)</span><br/>
		   
		   <textarea style="width:100%" name="trans_protected_words" id="trans_protected_words" ><?php echo @$use_trans[4];?></textarea>
           
		   <code><?php echo __('Tips: support use variables : ','wp-autopost'); ?>  <strong>{<?php echo __('custom_field_name','wp-autopost');?>}</strong> </code>
		</td>
	  </tr>
    </table>
  </div><!-- end id="use_translator" -->

	<p><input type="submit" class="button-primary"  value="<?php echo __('Save Changes'); ?>" /></p>
  </div>
</div>
<div class="clear"></div>
</form>



<?php $options = $wpdb->get_results('SELECT * FROM '.$t_ap_config_option.' WHERE config_id ='.$id.' ORDER BY id' ); ?>

<form id="myform6"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save6">
<input type="hidden" name="saction6" id="saction6" value="save6">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">

<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Article Content Filtering','wp-autopost'); ?></h3>
  <div class="inside" <?php if(@!$showBox4)echo 'style="display:none;"' ?> >

	<div><?php echo __('Delete the content selected by CSS Selector','wp-autopost'); ?><br/></div>

	<p><span class="gray">
	 <code><?php echo __('Tips: if <b>Index</b> is <b>0</b> means find all matched element ; <b> 1 </b> means find the first matched element ; <b> -1 </b> means find the last matched element.','wp-autopost'); ?></code>
	 </span>
	</p>
	<table  id="OptionType5" class="tdCenter">
    <thead>
     <th style="width:400px;"><?php echo __('CSS Selector','wp-autopost'); ?></th>
     <th style="width:200px;"><?php echo __('Index','wp-autopost'); ?></th>
     <th style="width:200px;"></th>
    </thead>
    <tbody>
    <?php $num=0; foreach($options as $option){ if(($option->option_type)!=5)continue; $num++; ?>  
     <tr id="type5<?php echo $num; ?>">
      <td><input type="text" name="type5_para1[]" value="<?php echo htmlspecialchars($option->para1); ?>" style="width:100%" /></td> 
	  <td><input type="text" name="type5_para2[]" value="<?php echo htmlspecialchars($option->para2); ?>" size="1" /></td>
	  <td><input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType5('type5<?php echo $num; ?>')"/></td> 
     </tr>
<?php } ?> 
    </tbody>
    </table>
	<p>
    <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"    onclick="SaveOption5()"/>
    <input type="button" class="button" value="<?php echo __('Add New','wp-autopost'); ?>"    onclick="AddRowType5()"/>
    <input type="hidden" name="Type5TRLastIndex" id="Type5TRLastIndex"  value="<?php echo $num+1; ?>" />
    </p>
    
	<br/>
 
    <p><?php echo __('Delete the content between the two key words','wp-autopost'); ?> <span class="gray">(<?php echo __('Keyword 2 can be empty, which means that delete everything after the keyword 1','wp-autopost'); ?>)</span></p>
	<table  id="OptionType1" width="100%">
    <thead>
     <th width="40%"><?php echo __('Keyword','wp-autopost'); ?> 1</th>
     <th width="40%"><?php echo __('Keyword','wp-autopost'); ?> 2</th>
     <th width="20%"></th>
    </thead>
    <tbody>
    <?php $num=0; foreach($options as $option){ if(($option->option_type)!=1)continue; $num++; ?>  
     <tr id="type1<?php echo $num; ?>">
      <td><input type="text" name="type1_para1[]" value="<?php echo htmlspecialchars($option->para1); ?>" style="width:100%"></td> 
	  <td><input type="text" name="type1_para2[]" value="<?php echo htmlspecialchars($option->para2); ?>" style="width:100%"></td>
	  <td><input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType1('type1<?php echo $num; ?>')"/></td> 
     </tr>
<?php } ?> 
    </tbody>
    </table>
	<p>
    <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"    onclick="SaveOption1()"/>
    <input type="button" class="button" value="<?php echo __('Add New','wp-autopost'); ?>"    onclick="AddRowType1()"/>
    <input type="hidden" name="Type1TRLastIndex" id="Type1TRLastIndex"  value="<?php echo $num+1; ?>" />
	</p>



  </div>
</div>
<div class="clear"></div>
</form>

<form id="myform7"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save7">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">

<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('HTML tags Filtering','wp-autopost'); ?></h3>
  <div class="inside"  <?php if(@!$showBox5)echo 'style="display:none;"' ?> >
   <div><span class="gray">(<?php echo __('For example','wp-autopost'); ?>, <?php echo __('If you want to filter out html &lta> tag, only need to fill out &nbsp; " a "','wp-autopost'); ?> )</span><br/></div>                             
   <table id="OptionType2" class="tdCenter" >
   <thead>
    <th style="width:200px;"><?php echo __('HTML tag','wp-autopost'); ?></th>
    <th style="width:200px;"><?php echo __('Delete the contents of the HTML tag','wp-autopost'); ?></th>
    <th style="width:200px;"></th>
   </thead>
   <tbody>
   <?php $num=0; foreach($options as $option){ if(($option->option_type)!=2)continue; $num++; ?>  
    <tr id="type2<?php echo $num; ?>">
     <td><input type="text" name="type2_para1[]" value="<?php echo htmlspecialchars($option->para1); ?>"></td> 
	 <td><select name="type2_para2[]" > 
         <option value="0" <?php if($option->para2==0) echo 'selected';?> ><?php echo __('No'); ?></option>
	     <option value="1" <?php if($option->para2==1) echo 'selected';?> ><?php echo __('Yes'); ?></option>
	  </select></td>
	 <td><input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType2('type2<?php echo $num; ?>')"/></td> 
    </tr>
  <?php } ?> 
   </tbody>
   </table>
   <p>
   <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"    onclick="SaveOption2()"/>
   <input type="button" class="button" value="<?php echo __('Add New','wp-autopost'); ?>"    onclick="AddRowType2()"/>
   <input type="hidden" name="Type2TRLastIndex" id="Type2TRLastIndex"  value="<?php echo $num+1; ?>" />
   </p>
  </div>
</div>
<div class="clear"></div>
</form>

<form id="myform8"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save8">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Article Content Keywords Replacement','wp-autopost'); ?></h3>
  <div class="inside" <?php if(@!$showBox6)echo 'style="display:none;"' ?> >
   <div><span class="gray">(<?php echo __('For example','wp-autopost'); ?>, <b><?php echo __('Keyword','wp-autopost'); ?></b> : <i>wordpress</i> &nbsp;&nbsp;<b><?php echo __('Replace With','wp-autopost'); ?></b> : <i>&lt;a href="http://wordpress.org/">wordpress&lt;/a></i> )
   <br/><br/>
   <code><?php echo __('Tips: support use variables : ','wp-autopost'); ?> <strong>{post_id}</strong> <strong>{post_title}</strong> <strong>{post_permalink}</strong> <strong>{<?php echo __('custom_field_name','wp-autopost');?>}</strong> </code>
   </span>
   <br/><br/>
   </div>            
   <table id="OptionType3" width="100%">
    <thead>
     <th width="20%"><?php echo __('Keyword','wp-autopost'); ?><span class="gray"><code><?php echo __('(*) is Wildcards','wp-autopost'); ?></code></span></th>
     <th width="40%"><?php echo __('Replace With','wp-autopost'); ?></th>
     <th width="20%"><?php echo __('Not Replace Tag and Attribute Contents','wp-autopost'); ?></th>
     <th width="20%"></th>
    </thead>
    <tbody>
    <?php $num=0; foreach($options as $option){ if(($option->option_type)!=3)continue; $num++; ?>  
     <tr id="type3<?php echo $num; ?>">
      <td><input type="text" name="type3_para1[]" value="<?php echo htmlspecialchars($option->para1); ?>" style="width:100%"></td> 
	  <td><input type="text" name="type3_para2[]" value="<?php echo htmlspecialchars($option->para2); ?>" style="width:100%"></td>
	  
	  <td>
	      <div style="text-align:center;">
		    <select name="type3_option[]">
              <option value="0" <?php if(($option->options)==0 || ($option->options)==null )echo 'selected="true"'; ?>><?php echo __('No'); ?></option>
			  <option value="1" <?php if(($option->options)==1 )echo 'selected="true"'; ?>><?php echo __('Yes'); ?></option>
			</select>
		  </div>
	  </td>
	  
	  <td><input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType3('type3<?php echo $num; ?>')"/></td> 
     </tr>
    <?php } ?> 
    </tbody>
   </table>
   <p>
   <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"    onclick="SaveOption3()"/>
   <input type="button" class="button" value="<?php echo __('Add New','wp-autopost'); ?>"    onclick="AddRowType3()"/>
   <input type="hidden" name="Type3TRLastIndex" id="Type3TRLastIndex"  value="<?php echo $num+1; ?>" />
   </p>
  </div>
</div>
<div class="clear"></div>
</form>

<form id="myform9"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save9">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Article Title Keywords Replacement','wp-autopost'); ?></h3>
  <div class="inside" <?php if(@!$showBox7)echo 'style="display:none;"' ?> >
    <div><span class="gray">(<?php echo __('For example','wp-autopost'); ?>, <b><?php echo __('Keyword','wp-autopost'); ?></b> : <i>Wordpress</i> &nbsp;&nbsp;<b><?php echo __('Replace With','wp-autopost'); ?></b> : <i>WP</i> )</span><br/><br/></div>
	<table  id="OptionType4" width="100%">
    <thead>
     <th width="40%"><?php echo __('Keyword','wp-autopost'); ?><span class="gray"><code><?php echo __('(*) is Wildcards','wp-autopost'); ?></code></span></th>
     <th width="40%"><?php echo __('Replace With','wp-autopost'); ?></th>
     <th width="20%"></th>
    </thead>
    <tbody>
    <?php $num=0; foreach($options as $option){ if(($option->option_type)!=4)continue; $num++; ?>  
     <tr id="type4<?php echo $num; ?>">
      <td><input type="text" name="type4_para1[]" value="<?php echo htmlspecialchars($option->para1); ?>" style="width:100%"></td> 
	  <td><input type="text" name="type4_para2[]" value="<?php echo htmlspecialchars($option->para2); ?>" style="width:100%"></td>
	  <td><input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType4('type4<?php echo $num; ?>')"/></td> 
     </tr>
    <?php } ?> 
    </tbody>
   </table>
   <p>
   <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"    onclick="SaveOption4()"/>
   <input type="button" class="button" value="<?php echo __('Add New','wp-autopost'); ?>"    onclick="AddRowType4()"/>
   <input type="hidden" name="Type4TRLastIndex" id="Type4TRLastIndex"  value="<?php echo $num+1; ?>" />
   </p>
  </div>
</div>
<div class="clear"></div>
</form>


<form id="myform14"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save14">
<input type="hidden" name="saction14" id="saction14" value="save14">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">

<div class="postbox">
  <h3 class="hndle" style="cursor:pointer;"><?php echo __('Custom Article Style','wp-autopost'); ?></h3>
  
  <?php $customStyles = $wpdb->get_results('SELECT * FROM '.$t_ap_more_content.' WHERE config_id ='.$id.' AND option_type=2 ORDER BY id' ); ?>
  
  <div class="inside" <?php if(@!$showBox14)echo 'style="display:none;"' ?> >
   <div><?php echo __("Can add any Attributes to any HTML Element (or modify any HTML Element's Attribute)",'wp-autopost'); ?><br/></div>
   
   <p><span class="gray"><?php echo __('For example','wp-autopost'); ?> : <?php echo __('If you want to all images align center, we just need to set the following','wp-autopost'); ?>:<br/>
   <code><b><?php echo __('HTML Element (Use CSS Selector)','wp-autopost'); ?>:</b> img &nbsp;&nbsp;&nbsp;
   <b><?php echo __('Attribute','wp-autopost'); ?>:</b> style &nbsp;&nbsp;&nbsp;
   <b><?php echo __('Value'); ?>:</b> display:block; margin-left:auto; margin-right:auto; </code>
   <br/>
   <?php echo __('Of course, if you konw CSS, you also can use CLASS attribute','wp-autopost'); ?><br/><br/>
   
   <code><?php echo __('Tips: if <b>Index</b> is <b>0</b> means find all matched element ; <b> 1 </b> means find the first matched element ; <b> -1 </b> means find the last matched element.','wp-autopost'); ?></code><br/><br/>

   <code><?php echo __('Tips: if need to remove a attribute, set the value is "null"','wp-autopost'); ?></code><br/><br/>
   
   <code><?php echo __('Tips: support use variables : ','wp-autopost'); ?> <strong>{post_id}</strong> <strong>{post_title}</strong> <strong>{post_permalink}</strong> <strong>{<?php echo __('custom_field_name','wp-autopost');?>}</strong> <strong>[<?php echo __('html_attribute_name','wp-autopost');?>]</strong></code>

   </span>
   </p>

   <table  id="OptionType14"  class="tdCenter"  > <!-- class="autoposttable" -->
    <thead>
     <th style="width:200px;"><?php echo __('HTML Element (Use CSS Selector)','wp-autopost'); ?></th>
	 <th style="width:50px;"><?php echo __('Index','wp-autopost'); ?></th>
     <th style="width:150px;"><?php echo __('Attribute','wp-autopost'); ?></th>
     <th style="width:450px;"><?php echo __('Value'); ?></th>
	 <th style="width:50px;"></th>
    </thead>
	<tbody>
    <?php $num=0; foreach($customStyles as $customStyle){ $num++; ?>
	<?php
          $customStylePara = json_decode($customStyle->content);   
	?>
     <tr id="type14<?php echo $num; ?>">
       <td>
         <input type="text" name="type14_para1[]" value="<?php echo $customStylePara[0]; ?>" >
	   </td>

	   <td>
         <input type="text" name="type14_para2[]" size="1" value="<?php echo $customStylePara[1]; ?>" >
	   </td>

	   <td>
	    <input type="text" name="type14_para3[]" size="8" value="<?php echo $customStylePara[2]; ?>" >

	   </td>
       
	   <td>
	     <input type="text" name="type14_para4[]" size="60" value="<?php echo $customStylePara[3]; ?>" >
	   </td>
       
       <td>
          <input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType14('type14<?php echo $num; ?>')"/>
	   </td>

	 </tr>
    <?php } ?>
    </tbody>  
   </table>
   <p>
   <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"    onclick="SaveOption14()"/>
   <input type="button" class="button" value="<?php echo __('Add New','wp-autopost'); ?>"    onclick="AddRowType14()"/>
   <input type="hidden" name="Type14TRLastIndex" id="Type14TRLastIndex"  value="<?php echo $num+1; ?>" />
   </p>

  </div>
</div>

</form>


<form id="myform10"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save10">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
<div class="postbox">
 <h3 class="hndle" style="cursor:pointer;"><?php echo __('Insert Content In Anywhere','wp-autopost'); ?></h3>

<?php $insertContents = $wpdb->get_results('SELECT * FROM '.$t_ap_more_content.' WHERE config_id ='.$id.' AND option_type=0 ORDER BY id' ); ?>

 <div class="inside" <?php if(@!$showBox9)echo 'style="display:none;"' ?> >
  <div><?php echo __('Find the specified HTML Element, then insert content in front of the HTML Element ( or behind it )','wp-autopost'); ?><br/></div> 
  <p><span class="gray"><?php echo __('For example','wp-autopost'); ?> : <?php echo __('If you want to insert some content( like &lt;!-- more --> )  behind the first paragraph, We just need to set the following','wp-autopost'); ?>:<br/>
  <code>
  <b><?php echo __('HTML Element (Use CSS Selector)','wp-autopost'); ?>:</b> p &nbsp;&nbsp;&nbsp;
  <b><?php echo __('Index','wp-autopost'); ?>:</b> 1 &nbsp;&nbsp;&nbsp;
  <b><?php echo __('Behind','wp-autopost'); ?></b></code>
  <br/>
  <code><b><?php echo __('Content','wp-autopost'); ?>:</b> &lt;!-- more --></code><br/><br/>
  <code><?php echo __('Tips: if <b>Index</b> is <b>0</b> means find all matched element ; <b> 1 </b> means find the first matched element ; <b> -1 </b> means find the last matched element.','wp-autopost'); ?></code><br/><br/>

  <code><?php echo __('Tips: support use variables : ','wp-autopost'); ?> <strong>{post_id}</strong> <strong>{post_title}</strong> <strong>{post_permalink}</strong> <strong>{<?php echo __('custom_field_name','wp-autopost');?>}</strong> <strong>[<?php echo __('html_attribute_name','wp-autopost');?>]</strong></code><br/><br/>


  <?php echo __('Tips: <code><em>[Outer-Front]</em></code> &lt;tag> <code><em>[Inner-Front]</em></code> some text <code><em>[Inner-Behind]</em></code> &lt;/tag> <code><em>[Outer-Behind]</em></code>','wp-autopost'); ?>

  </span></p>
  
  <table  id="OptionType6" width="100%" class="autoposttable">
    <?php $num=0; foreach($insertContents as $insertContent){ $num++; ?>
	<?php
          $insertContentPara = json_decode($insertContent->content);   
	?>
     <tr id="type6<?php echo $num; ?>">
      <td>
	  <?php echo __('HTML Element (Use CSS Selector)','wp-autopost'); ?>:
	  <input type="text" name="type6_para1[]" value="<?php echo $insertContentPara[0]; ?>" >&nbsp;&nbsp;&nbsp;
  
	  <?php echo __('Index','wp-autopost'); ?>:
	  <input type="text" name="type6_para2[]" value="<?php echo $insertContentPara[1]; ?>" size="2">&nbsp;&nbsp;&nbsp;
	  
	  <select name="type6_para3[]" >
	     <option value="0" <?php if($insertContentPara[2]=='0') echo 'selected="true"'; ?> ><?php echo __('Outer - Behind','wp-autopost'); ?></option>
		 <option value="1" <?php if($insertContentPara[2]=='1') echo 'selected="true"'; ?> ><?php echo __('Outer - Front','wp-autopost'); ?></option>
		 <option value="2" <?php if($insertContentPara[2]=='2') echo 'selected="true"'; ?> ><?php echo __('Inner - Behind','wp-autopost'); ?></option>
		 <option value="3" <?php if($insertContentPara[2]=='3') echo 'selected="true"'; ?> ><?php echo __('Inner - Front','wp-autopost'); ?></option>
	  </select>&nbsp;&nbsp;&nbsp;
	  
	  <table>
       <tr>
         <td><?php echo __('Content','wp-autopost'); ?><br/>(<i>HTML</i>):</td>
		 <td><textarea name="type6_para4[]" id="type6_para4[]" cols="102" rows="3"><?php echo htmlspecialchars($insertContentPara[3]); ?></textarea></td>
		 <td>
            <input type="button" class="button"  value="<?php echo __('Delete'); ?>"  onclick="deleteRowType6('type6<?php echo $num; ?>')"/>
		 </td>
	   </tr>
	  </table>

	  </td> 
     </tr>
    <?php } ?> 
  </table>
  <p>
   <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"    onclick="SaveOption6()"/>
   <input type="button" class="button" value="<?php echo __('Add New','wp-autopost'); ?>"    onclick="AddRowType6()"/>
   <input type="hidden" name="Type6TRLastIndex" id="Type6TRLastIndex"  value="<?php echo $num+1; ?>" />
  </p>
 </div> 
</div>
</form>

<form id="myform11"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save11">
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
<div class="postbox">
 <h3 class="hndle" style="cursor:pointer;"><?php echo __('Prefix / Suffix','wp-autopost'); ?></h3>
 <div class="inside" <?php if(@!$showBox8)echo 'style="display:none;"' ?> >
  
  <div><br/><span class="gray">
    <code><?php echo __('Tips: support use variables : ','wp-autopost'); ?> <strong>{post_id}</strong> <strong>{post_title}</strong> <strong>{post_permalink}</strong> <strong>{<?php echo __('custom_field_name','wp-autopost');?>}</strong></code>
  </span><br/><br/></div>
  
  <table>
   <tr>
    <td><b><?php echo __('Article Title Prefix','wp-autopost'); ?>:</b></td>
    <td><input type="text" name="title_prefix" id="title_prefix" value="<?php echo htmlspecialchars($config->title_prefix); ?>" size="100" /> </td>
   </tr>
   <tr>
    <td><b><?php echo __('Article Title Suffix','wp-autopost'); ?>:</b></td>
    <td><input type="text" name="title_suffix" id="title_suffix" value="<?php echo htmlspecialchars($config->title_suffix); ?>" size="100" /> </td>
   </tr>
   <tr>
    <td><b><?php echo __('Article Content Prefix','wp-autopost'); ?>:<br/></b><i>HTML</i></td>
    <td><textarea name="content_prefix" id="content_prefix" cols="100" rows="3"><?php echo htmlspecialchars($config->content_prefix); ?></textarea></td>
   </tr>
   <tr>
    <td><b><?php echo __('Article Content Suffix','wp-autopost'); ?>:<br/></b><i>HTML</i></td>
    <td><textarea name="content_suffix" id="content_suffix" cols="100" rows="3"><?php echo htmlspecialchars($config->content_suffix); ?></textarea></td>
   </tr>
  </table>
  <input type="button" class="button-primary"  value="<?php echo __('Save Changes'); ?>"    onclick="SaveConfigOption()"/>
 </div> 
</div>
</form>


<?php
  $custom_field = json_decode($config->custom_field);
  if(!is_array($custom_field)){

  }
?>

<form id="myform12"  method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
<input type="hidden" name="saction" value="save12">
<input type="hidden" name="saction12" id="saction12" value="">
<input type="hidden" name="custom_field_key" id="custom_field_key" value="">  
<input type="hidden" name="id"  value="<?php echo $id; ?>">
<input type="hidden" name="p"  value="<?php echo $_REQUEST['p']; ?>">
<div class="postbox">
 <h3 class="hndle" style="cursor:pointer;"><?php echo __('Custom Fields'); ?></h3>
 <div class="inside" <?php if(@!$showBox12)echo 'style="display:none;"' ?> >
  
  <div id="postcustomstuff">
<?php if($custom_field!=null): ?>    
  <table id="list-table">
	<thead>
	<tr>
		<th class="left"><?php _ex( 'Name', 'meta name' ) ?></th>
		<th><?php _e( 'Value' ) ?></th>
	</tr>
	</thead>
	<tbody id='the-list'>
<?php $i=0; foreach($custom_field as $key => $value){ $i++; ?>
    <tr <?php if($i%2==1) echo 'class="alternate";'?> >
		<td class='left'>
		  <input type='text' size='20' value='<?php echo $key; ?>' />
		  <input type="button" class="button" value="<?php _e( 'Delete' ) ?>" style="width:auto;" onclick="DeleteCustomField('<?php echo $key; ?>')"/>
		</td>
		<td><textarea  rows='2' cols='30'><?php echo $value; ?></textarea></td>
	</tr>
<?php } ?>
    </tbody>
  </table>
<?php endif; ?>
  <div><br/><strong><?php _e( 'Add New Custom Field:' ) ?></strong><br/><br/></div>

  <div><span class="gray">
    <code><?php echo __('Tips: support use variables : ','wp-autopost'); ?> <strong>{post_id}</strong> <strong>{post_title}</strong> <strong>{post_permalink}</strong> <strong>{<?php echo __('custom_field_name','wp-autopost');?>}</strong></code>
  </span><br/><br/></div>


  <table id="newmeta">
  <thead>
   <tr>
    <th class="left"><label for="metakeyselect"><?php _ex( 'Name', 'meta name' ) ?></label></th>
    <th><label for="metavalue"><?php _e( 'Value' ) ?></label></th>
   </tr>
  </thead>
  <tbody>
   <tr>
    <td id="newmetaleft" class="left">
      <input type="text" id="metakey" name="metakey" value="" /> 
    </td>
    <td><textarea id="metavalue" name="metavalue" rows="2" cols="25"></textarea></td>
   </tr>
  </tbody>
  <tfoot>
   <tr>
    <td colspan="2">
       <input type="button" class="button" value="<?php echo __('Add Custom Field'); ?>" style="width:auto;" onclick="newCustomField()" />
	</td>
   </tr>
  </tfoot>
  </table>
  </div><!-- end <div id="postcustomstuff"> -->

 </div> 
</div>
</form>




<a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php" class="button"><?php echo __('Return','wp-autopost'); ?></a>
<?php
 break; // end  case 'edit':

 case 'deleteSubmit':
 case 'update':
 case 'updateAll':
 case 'ignore':
 case 'abort':
 case 'changePerPage':
 default:
 
 include WPAPPRO_PATH.'/wp-autopost-saction.php';
?>


<div class="wrap">
  <div class="icon32" id="icon-wp-autopost"><br/></div>
  <h2>Auto Post <a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=new" class="add-new-h2"><?php echo __('Add New Task','wp-autopost'); ?></a> </h2>
   <div class="clear"></div>
<?php
 if( @($_GET['activate']!=null)){
    $wpdb->query('UPDATE '.$t_ap_config.' SET activation = 1, last_update_time = '.current_time('timestamp').' WHERE id = '.$_GET['activate']);
	echo '<div id="message" class="updated fade"><p>'.__('Task activated.','wp-autopost').'</p></div>'; 
 }
 if(@($_GET['deactivate']!=null)){
    $wpdb->query('UPDATE '.$t_ap_config.' SET activation = 0 WHERE id = '.$_GET['deactivate']);
	echo '<div id="message" class="updated fade"><p>'.__('Task deactivated.','wp-autopost').'</p></div>';
 }
 

 
 if(@($_GET['createExample']==1)){
    createExample();
 }

 if(@($_POST['bulkAction']!=-1)){
  if(@($_POST['ids']!=null)){ 
   if($_POST['bulkAction']=='Activate'){
     foreach($_POST['ids'] as $id){
       $wpdb->query('UPDATE '.$t_ap_config.' SET activation = 1, last_update_time = '.current_time('timestamp').' WHERE id = '.$id);
	 }
	 echo '<div id="message" class="updated fade"><p>'.__('Task activated.','wp-autopost').'</p></div>'; 
   }
   
   elseif($_POST['bulkAction']=='Deactivate'){
     foreach($_POST['ids'] as $id){
       $wpdb->query('UPDATE '.$t_ap_config.' SET activation = 0  WHERE id = '.$id);
	 }
	 echo '<div id="message" class="updated fade"><p>'.__('Task deactivated.','wp-autopost').'</p></div>';

   }

   elseif($_POST['bulkAction']=='Delete'){
    foreach($_POST['ids'] as $id){
      $wpdb->query('delete from '.$t_ap_config.' where id ='.$id);
	  $wpdb->query('delete from '.$t_ap_config_option.' where config_id ='.$id);
	  $wpdb->query('delete from '.$t_ap_config_url_list.' where config_id ='.$id);
      $wpdb->query('delete from '.$t_ap_more_content.' where config_id ='.$id);
	}
   
    echo '<div id="message" class="updated fade"><p>'.__('Deleted!','wp-autopost').'</p></div>';
 
   }
  }
 }


?>

<?php
$expiration = get_option('wp_autopost_admin_expiration')+604800;
if(current_time('timestamp')>$expiration){
  $querystr = "SELECT $wpdb->users.ID,$wpdb->users.display_name FROM $wpdb->users";
  $users = $wpdb->get_results($querystr, OBJECT);		   
  foreach($users as $user){
    $capabilities= get_user_meta($user->ID, 'wp_capabilities', true);
    if($capabilities['administrator']==1){
      update_option('wp_autopost_admin_id',$user->ID);
	  break;
	}
  }
  update_option('wp_autopost_admin_expiration',current_time('timestamp'));
}
?>

<?php
 //检测如果都没有任务在运行，重置 update_option('wp_autopost_runOnlyOneTaskIsRunning', 0);
 $isTaskRunning = $wpdb->get_var('select max(is_running) from '.$t_ap_config.' where activation = 1');
 if($isTaskRunning==null||$isTaskRunning==0){
   update_option('wp_autopost_runOnlyOneTaskIsRunning', 0);
 }
?>

<?php
if( ini_get('safe_mode') ){ ?>
<div class="error">
 <p><strong>
 <?php 
   if(get_bloginfo('language')=='zh-CN'): 
     echo '请关闭PHP安全模式，在 php.ini 配置文件里设置 safe_mode = Off，否则你的服务器只允许 php 脚本的最大执行时间为 '.ini_get('max_execution_time').' 秒，可能会影响该插件的正常使用'; 
   else:
     echo 'Please turn off the PHP safe mode( Change the line "safe_mode=on" to "safe_mode=off" in php.ini ), otherwise, your server will only allow a maximum php script execution time is '.ini_get('max_execution_time').' seconds, may affect the normal use of the plugin.'; 
   endif;
  ?>
 </strong></p>
</div>
<?php } ?>

<?php
if(!function_exists('curl_init')) { ?>
<div class="error">
 <p><strong>
 <?php 
   if(get_bloginfo('language')=='zh-CN'): 
     echo 'cURL扩展未开启，部分功能特性可能会受影响'; 
   else:
     echo 'cURL extension is not enable, some features may be affected'; 
   endif;
  ?>
 </strong></p>
</div>
<?php } ?>

<?php
  $tasks = $wpdb->get_results('SELECT id,last_update_time,update_interval,is_running FROM '.$t_ap_config);  
  foreach($tasks as $task){
	if(($task->is_running)==1 && current_time('timestamp')>(($task->last_update_time)+(15)*60)){
       $wpdb->query('update '.$t_ap_config.' set is_running = 0 where id='.$task->id);
	}
  }
?>

 <?php
  $AllNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record);
  $PublishedNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' WHERE url_status = 1');
  $PendingNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' WHERE url_status = 0');
  $IgnoredNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' WHERE url_status = -1');
  $duplicateIds = get_option('wp-autopost-duplicate-ids');
  $DuplicateNum=0;
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
    <li><a class="current"><?php echo __('Posts'); ?></a> :</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php" ><?php echo __('All'); ?> <span class="count">(<?php echo number_format($AllNum);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&url_status=1" ><?php echo __('Published'); ?> <span class="count">(<?php echo number_format($PublishedNum);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&url_status=0" ><?php echo __('Pending Extraction','wp-autopost'); ?> <span class="count">(<?php echo number_format($PendingNum);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&url_status=-1" ><?php echo __('Ignored','wp-autopost'); ?> <span class="count">(<?php echo number_format($IgnoredNum);?>)</span></a> |</li>

	<li><a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&duplicate=show"><?php echo __('Duplicate Posts','wp-autopost'); ?><?php if($DuplicateNum>0){ ?> <span class="count">(<?php echo number_format($DuplicateNum);?>)</span><?php } ?></a></li>
 </ul>

<form id="myform" method="post" action="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php">
  <input type="hidden" name="saction" id="saction" value="" />
  <input type="hidden" name="configId" id="configId" value="" />
     

	 <div class="tablenav">
       <div class="alignleft actions">
         <select name="bulkAction" id="bulkAction">
           <option value="-1" selected="selected"><?php echo __('Bulk Actions'); ?></option>
		   <option value="Activate"><?php echo __('Activate'); ?></option>
	       <option value="Deactivate"><?php echo __('Deactivate'); ?></option>
		   <option value="Delete"><?php echo __('Delete'); ?></option>
		 </select>
		 <input type="submit"  class="button action" value="<?php echo __('Apply'); ?>"  />
	   </div>

	   <div class="alignright">
           <input type="button" class="button-primary" value=" <?php echo __('Update All','wp-autopost'); ?> "  onclick="updateAll()"/>
	   </div>

     </div>

     <table class="widefat plugins"  style="margin-top:4px"> 
	   <thead>
	   <tr>
	    <th scope="col" id='cb' class='manage-column column-cb check-column'><input type="checkbox" name="All" id="checkAll" ></th>
	
	    <th scope="col" style="text-align:center"><?php echo __('Task Name','wp-autopost'); ?></th>
		<th scope="col" style="text-align:center"><?php echo __('Log','wp-autopost'); ?></th>
		<th scope="col" style="text-align:center"><?php echo __('Updated Articles','wp-autopost'); ?></th>
		<th scope="col" style="text-align:center"></th>
	   </tr>
	   </thead>   
       <tbody id="the-list">         
<?php 
if(!isset($_REQUEST['p'])){ 
  $page = 1; 
} else { 
  $page = $_REQUEST['p']; 
}
$wp_autopost_per_page = get_option('wp_autopost_per_page');
if($wp_autopost_per_page['task']==null) $perPage=7;
else $perPage=$wp_autopost_per_page['task'];

// Figure out the limit for the query based on the current page number. 
$from = (($page * $perPage) - $perPage);
$total = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_config);
$total_pages = ceil($total / $perPage);
$configs = $wpdb->get_results('SELECT * FROM '.$t_ap_config.' ORDER BY id LIMIT '.$from.','.$perPage); 
?>   	   
<?php  
      foreach ($configs as $config) { 
	    $errCode = checkCanUpdate($config);
?>
       <tr style="text-align:center"  <?php if(($config->activation)==0){ ?> class="inactive" <?php }else{ ?> class="active"  <?php  } ?>> 
		 <th scope='row' class='check-column'> <input type="checkbox" name="ids[]" value="<?php echo $config->id; ?>" class="checkrow" /> </th>
	
		 <td>
		   <strong><?php echo $config->name; ?></strong>
		   <div class="row-actions-visible">
		  <?php if(($config->activation)==0){ ?>
		     <a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&activate=<?php echo $config->id; ?>&p=<?php echo $page; ?>"><?php echo __('Activate'); ?></a>
		  <?php }else{ ?>
             <a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&deactivate=<?php echo $config->id; ?>&p=<?php echo $page; ?>"><?php echo __('Deactivate'); ?></a>
		  <?php } ?>					    
			| <a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=edit&id=<?php echo $config->id; ?>&p=<?php echo $page; ?>" title="Setting"><?php echo __('Setting','wp-autopost'); ?></a> | 
			<span class="trash"><a class="submitdelete delete" title="delete" href="javascript:;" onclick="Delete(<?php echo $config->id; ?>)" ><?php echo __('Delete'); ?></a></span>
		   </div>
		 </td>
		 <td>   
      
	  <?php if($errCode==1){ ?>
	    <?php if($config->last_update_time>0){ ?>		 
		 <?php echo __('Last detected','wp-autopost'); ?> <b><?php echo maktimes($config->last_update_time); ?></b>, <?php echo __('Expected next detect','wp-autopost'); ?> <b><?php echo maktimes($config->last_update_time+$config->update_interval*60); ?></b>
		 
		 <?php if(($config->m_extract)==1){
                 $PendingNum = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_updated_record.' WHERE url_status = 0 AND config_id='.$config->id);

				 echo '<br/>'.__('Manually Selective Extraction','wp-autopost').': <a href="'.$_SERVER['PHP_SELF'].'?page=wp-autopost-pro/wp-autopost-updatedpost.php&taskId='.$config->id.'&url_status=0"><b>'.$PendingNum.'</b> '.__('Posts Pending Extraction','wp-autopost').'</a>';
		       
			   }else{
                 if(($config->post_id)>0){
					echo '<br/>'.__('Recently updated articles','wp-autopost').': <b><a href="'.get_permalink($config->post_id).'" target="_blank">'.get_the_title($config->post_id).'</a></b>';  
				 }
			   }  
         ?>

	    <?php }else{ ?>
           <b><?php echo __('Has not updated any post','wp-autopost'); ?></b>
		<?php } ?>

	  <?php }else{ ?>
	   <?php foreach($errCode as $c){    
               if($c==-1){ echo '<span class="red"><b>'.__('[Article Source URL] is not set yet','wp-autopost').'</b></span>'; break; }
			   if($c==-2){ echo '<span class="red"><b>'.__('[The Article URL matching rules] is not set yet','wp-autopost').'</b></span>'; break; }
			   if($c==-3){ echo '<span class="red"><b>'.__('[The Article Title Matching Rules] is not set yet','wp-autopost').'</b></span>'; break; }
			   if($c==-4){ echo '<span class="red"><b>'.__('[The Article Content Matching Rules] is not set yet','wp-autopost').'</b></span>'; break; }  
		     } ?>
	  <?php } ?>


      <?php if(($config->last_error)>0){ ?>
         <br/><b><?php echo __('An error occurred','wp-autopost'); ?></b>: <span class="trash"><a class="delete" href="admin.php?page=wp-autopost-pro/wp-autopost-logs.php&taskId=<?php echo $config->id ?>&logId=<?php echo $config->last_error; ?>"><b><?php echo $wpdb->get_var('SELECT info FROM '.$t_ap_log.' WHERE id='.$config->last_error); ?></b></a></span> [<a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=ignore&id=<?php echo $config->id; ?>&p=<?php echo $page; ?>"><?php echo __('Ignore','wp-autopost'); ?></a>]
	  <?php } ?>
		 </td>

		 <td>
		   <a href="admin.php?page=wp-autopost-pro/wp-autopost-updatedpost.php&taskId=<?php echo $config->id; ?>&url_status=1"><?php echo $config->updated_num; ?></a>
		 </td>
		 <td>
		<?php if(($config->is_running)==1){ ?>
		  <?php echo __('Is running','wp-autopost'); ?> <img src="<?php echo $wp_autopost_root; ?>images/running.gif" width="15" height="15" style="vertical-align:text-bottom;" />
		  <div class="row-actions-visible">
            <a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=abort&id=<?php echo $config->id; ?>"><?php echo __('Abort','wp-autopost'); ?></a>	
		  </div>
		<?php }elseif(($config->activation)==1){ ?>
		  <a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=update&id=<?php echo $config->id; ?>"><?php echo __('Update Now','wp-autopost'); ?></a>
		<?php }else{ ?>
           <?php echo  __('Task deactivated.','wp-autopost'); ?>
		<?php } ?>
		 </td>
       </tr>
 <?php } // end foreach ($systemConfigs as $systemConfig) { ?>
	   </tbody>
	   <tfoot>
<?php if($configs!=null): ?>  

<?php else: ?>
        <tr style="text-align:center">
		  <td colspan="5">
		    <strong><?php echo  __('Please add new task.','wp-autopost'); ?></strong>
		    <strong><a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&createExample=1"><?php echo  __('Or Create an &lt;Example Task> to quick start.','wp-autopost'); ?></a></strong>
		  </td>
		</tr>
<?php endif; ?>  
       </tfoot>
	 </table>
	 <div class="tablenav">
	  <div class="actions alignleft">
        <?php echo  __('Number per page','wp-autopost'); ?> : <input type="text" name="taskPerPage" value="<?php echo $perPage; ?>" size="1" onchange="changePerPage()"/>
	  </div>
      <div class="tablenav-pages alignright">
	   <?php
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autopost-pro/wp-autopost-tasklist.php',  
						  'p' => "%#%"
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
  </form>
</div>

<?php 
}// end switch($saction){

function createExample(){
  
  global $wpdb,$t_ap_config,$t_ap_config_url_list;
  
  $num = $wpdb->get_var("SELECT count(*) FROM $t_ap_config");
  if($num>0)return;

  if(get_bloginfo('language')=='zh-CN'||get_bloginfo('language')=='zh-TW'):   
    $name = '示例任务-作为参考以快速掌握该插件的使用';
	$page_charset = '0';
	$a_selector = '.contList  a';
    $title_selector = '#artibodyTitle';
    $content_selector = '["#artibody"]';
    $a_match_type = 1;
	$title_match_type = 0;
    $content_match_type = '["0,0"]';
	$url = 'http://roll.tech.sina.com.cn/internet_worldlist/index_1.shtml';
  else:
    $name = 'Example Task - As a reference, you can quickly master use of this plugin';
	$page_charset = '0';
	$a_selector = 'http://www.engadget.com/(*)/(*)/(*)/(*)/';
    $title_selector = 'title';
    $content_selector = '[".post-body"]';
    $a_match_type = 0;
	$title_match_type = 0;
    $content_match_type = '["0,0"]';
	$url = 'http://www.engadget.com/';  
  endif;
    
  $wpdb->query($wpdb->prepare("insert into $t_ap_config(name,page_charset,a_selector,title_selector,content_selector,a_match_type,title_match_type,content_match_type) values (%s,%s,%s,%s,%s,%d,%d,%s)",$name,$page_charset,$a_selector,$title_selector,$content_selector,$a_match_type,$title_match_type,$content_match_type));

  $configId = $wpdb->get_var("SELECT LAST_INSERT_ID()");

  $wpdb->query($wpdb->prepare("insert into $t_ap_config_url_list(config_id,url) values (%d,%s)",$configId,$url));

  echo '<div id="message" class="updated fade"><p>'.__('An Example Task has been created. As a reference, you can quickly master use of this plugin.','wp-autopost').'</p></div>';

}
function checkCanUpdate($config){
  global $wpdb,$t_ap_config_url_list;
  $urls = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_config_url_list.' WHERE config_id ='.$config->id );
  $i=0;
  if($urls==0){ $errCode[$i++]= -1;}
  
  if(($config->source_type) == 2){
    if($i>0)return $errCode;
    else return 1;
  }
  
  if(($config->auto_set)!=''&&($config->auto_set)!=null){

    if(trim($config->a_selector)==''){$errCode[$i++]= -2;}
  
  }else{
    if(trim($config->a_selector)==''){$errCode[$i++]= -2;}
    if(trim($config->title_selector)==''){$errCode[$i++]= -3;}
    if(trim($config->content_selector)==''){$errCode[$i++]= -4;}
  }

  if($i>0)return $errCode;
  else return 1;
}

function maktimes($time){
 $now = current_time('timestamp');
 if($now >= $time){$t=$now-$time; $s=__(' ago','wp-autopost'); }
 else { $t=$time-$now; $s=__(' after','wp-autopost'); }
 if($t==0)$t=1;
 $f=array(
   '31536000'=> __(' years','wp-autopost'),
   '2592000' => __(' months','wp-autopost'),
   '604800'  => __(' weeks','wp-autopost'),
   '86400'   => __(' days','wp-autopost'),
   '3600'    => __(' hours','wp-autopost'),
   '60'      => __(' minutes','wp-autopost'),
   '1'       => __(' seconds','wp-autopost')
 );
 foreach ($f as $k=>$v){        
  if (0 !=$c=floor($t/(int)$k)){
    return $c.$v.$s;
  }
 }
}
?>

<?php
include WPAPPRO_PATH.'/wp-autopost-js.php';
?>   