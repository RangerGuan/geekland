<?php
@$saction = $_REQUEST['saction'];

if($saction=='editSubmit'&&$_POST['saction1']=='changePostType'){
  $wpdb->query($wpdb->prepare("update  $t_ap_config  set
               post_type = %s 
			   WHERE id = %d",$_POST['post_type'],$_POST['id'])
			   );
  $showBox1=true;
}
elseif($saction=='editSubmit'){
 @$post_category = $_POST['post_category'];
 if($post_category!=null){
  foreach($post_category as $cate){
     @$cat.= $cate.',';
  }
 }
 @$cat = substr($cat,0,-1);
 
 if($_POST['post_type']=='page'){
   $cat=null;
 }


 $charset = $_POST['charset'];
 if($charset==0)$page_charset='0';
 else $page_charset= $_POST['page_charset'];
 if(trim($page_charset)=='')$page_charset='UTF-8';
 
 $img_insert_attachment = array();

 $img_insert_attachment[0]=$_POST['img_insert_attachment'];
 if($img_insert_attachment[0]=='')$img_insert_attachment[0]=0;
 if($img_insert_attachment[0]==2){ // Flickr
    $flickrOptions = get_option( 'wp-autopost-flickr-options');
	if($flickrOptions['oauth_token']==''){
	  $msg = '<div class="error"><p><a href="admin.php?page=wp-autopost-pro/wp-autopost-flickr.php">'.__( 'Save the images to Flickr requires login to your Flickr account and authorize the plugin to connect to your account!', 'wp-autopost' ).'</a></p></div>';
	  $img_insert_attachment[0] = 0;
	}else{
      $msg = '<div class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
	}
 }elseif($img_insert_attachment[0]==3){
    $qiniuOptions = get_option( 'wp-autopost-qiniu-options');
	if($qiniuOptions['set_ok']!=1){
      $msg = '<div class="error"><p><a href="admin.php?page=wp-autopost-pro/wp-autopost-qiniu.php">'.__( 'Save the images to Qiniu requires set correctly in Qiniu Options!', 'wp-autopost' ).'</a></p></div>';
	  $img_insert_attachment[0] = 0;
	}else{
      $msg = '<div class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
	}
 }elseif($img_insert_attachment[0]==4){
    $upyunOptions = get_option( 'wp-autopost-upyun-options');
	if($upyunOptions['set_ok']!=1){
      $msg = '<div class="error"><p><a href="admin.php?page=wp-autopost-pro/wp-autopost-upyun.php">'.__( 'Save the images to Upyun requires set correctly in Upyun Options!', 'wp-autopost' ).'</a></p></div>';
	  $img_insert_attachment[0] = 0;
	}else{
      $msg = '<div class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
	}
 }else{
   $msg = '<div class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
 }
 
 if($_POST['set_featured_image']=='1'){
	$set_featured_image_index = $_POST['set_featured_image_index'];
	if($set_featured_image_index<1)$set_featured_image_index=1;
	$img_insert_attachment[1]=$set_featured_image_index;
 }else{
	$img_insert_attachment[1]=0;
 }
 if(isset($_POST['set_watermark_image']) && $_POST['set_watermark_image']=='on'){
   $img_insert_attachment[2]=intval($_POST['watermark_id']);
 }else{
   $img_insert_attachment[2]=0;
 }
 if(isset($_POST['attach_insert_attachment']) && $_POST['attach_insert_attachment']=='on')$img_insert_attachment[3]=1;else $img_insert_attachment[3]=0;
 if($_POST['img_url_attr']!='src')$img_insert_attachment[4]=$_POST['img_url_attr'];
 
 if(isset($_POST['whole_word']) && $_POST['whole_word']=='on')$whole_word=1;else $whole_word=0;
 
 $downloads = array();
 $downloads[0] = $_POST['download_img'];
 $downloads[1] = $_POST['download_attach'];

 $auto_sets = array(); 
 $auto_sets[0] = intval($_POST['auto_tags']);
 
 $auto_excerpt = intval($_POST['auto_excerpt']);
 if($auto_excerpt==1){
   $auto_excerpt_index = intval($_POST['auto_excerpt_index']);
   if($auto_excerpt_index<1)$auto_excerpt_index=1;
   $auto_sets[1] = $auto_excerpt_index;
 }else{
   $auto_sets[1] = 0;
 }
 $auto_sets[2] = intval($_POST['publish_status']);

 if($_POST['use_wp_tags']=='on')$auto_sets[3]=1;else $auto_sets[3]=0;
 
 
 $proxy = array();
 if($_POST['use_proxy']==1){
   $proxyOptions = get_option('wp-autopost-proxy');
   if($proxyOptions['ip']==''||$proxyOptions['ip']==null){
     $msg = '<div class="error"><p><a href="admin.php?page=wp-autopost-pro/wp-autopost-proxy.php">'.__( 'Use proxy please set Proxy Options first!', 'wp-autopost' ).'</a></p></div>';
	 $proxy[0]=0;
   }else{
     $proxy[0]=1;
   }
 }else{
   $proxy[0]=0;
 }
 $proxy[1]=intval($_POST['hide_ip']);
 $proxy[2]=intval($_POST['enable_cookie']);
 

 $post_format = $_POST['post_format'];
 
 $post_scheduled = array();
 $post_scheduled[0] = $_POST['post_scheduled'];
 $post_scheduled[1] = intval($_POST['post_scheduled_hour']);
 if($post_scheduled[1]<0)$post_scheduled[1]=0;
 if($post_scheduled[1]>23)$post_scheduled[1]=23;
 $post_scheduled[2] = intval($_POST['post_scheduled_minute']);
 if($post_scheduled[2]<0)$post_scheduled[1]=0;
 if($post_scheduled[2]>59)$post_scheduled[1]=59;
 
 
 $publish_date='';
 if($_POST['publish_date']!=''){
   $publish_date = $_POST['publish_date'];
 }
 
 if($_POST['use_publish_date']==0){
   $publish_date='';  
 }

 //$check_duplicate=$_POST['check_duplicate'];
 
 $wpdb->query($wpdb->prepare("update  ".$t_ap_config."  set
               m_extract = %d,
               name = %s,
			   page_charset = %s,
			   cat = %s,
			   author = %d,
			   download_img = %s,
			   img_insert_attachment = %s,
			   update_interval = %d,
			   published_interval = %d,
			   post_scheduled = %s,
			   auto_tags = %s,
			   tags = %s,
			   whole_word = %d,
			   proxy = %s,
			   post_format = %s,
			   check_duplicate = %d,
			   err_status = %d,
			   publish_date = %s
			   WHERE id = %d",$_POST['manually_extraction'],$_POST['config_name'],$page_charset,$cat,$_POST['author'],json_encode($downloads),json_encode($img_insert_attachment),$_POST['update_interval'],$_POST['published_interval'],json_encode($post_scheduled),json_encode($auto_sets),$_POST['tags'],$whole_word,json_encode($proxy),$_POST['post_format'],$_POST['check_duplicate'],$_POST['err_status'],$publish_date,$_POST['id'])
			   );


 $showBox1=true;
}// end if($saction=='editSubmit'&&$_POST['saction1']=='changePostType'){


if($saction=='save2'&&$_POST['saction2']!='autoseturl'){	

 if($_POST['a_match_type']==0){ 
   $a_match_type = 0;
   $a_selector = trim($_POST['a_selector_0']);
 }elseif($_POST['a_match_type']==1){
   $a_match_type = 1;
   $a_selector = trim($_POST['a_selector_1']);
 }

 
 if(isset($_POST['reverse_sort']) && $_POST['reverse_sort']=='on')$reverse_sort=1;else $reverse_sort=0;
 
 

 $wpdb->query($wpdb->prepare("update $t_ap_config set
               a_match_type = %s,
			   a_selector = %s,
			   source_type = %d,
			   reverse_sort = %d,
			   start_num = %d, 
			   end_num =  %d  WHERE id = %d",$a_match_type,$a_selector,$_POST['source_type'],$reverse_sort,$_POST['start_num'],$_POST['end_num'],$_POST['id']
			   ));
			   
 
 
 if($_POST['source_type']==0 || $_POST['source_type']==2){
  $wpdb->query('delete from '.$t_ap_config_url_list.' where config_id ='.$_POST['id']);
  $urls = explode("\n",$_POST['urls']);  
  foreach($urls as $url){
    $url=trim($url);
	if($url!='')$wpdb->query('insert into '.$t_ap_config_url_list.'(config_id,url) values ('.$_POST['id'].',"'.$url.'")');
  }
 }

 if($_POST['source_type']==1){
  $wpdb->query('delete from '.$t_ap_config_url_list.' where config_id ='.$_POST['id']);
  $url=trim($_POST['url']);
  if($url!='')$wpdb->query('insert into '.$t_ap_config_url_list.'(config_id,url) values ('.$_POST['id'].',"'.$url.'")');
 }

 $add_source_url = array();
 if(isset($_POST['add_source_url']) && $_POST['add_source_url']=='on')$add_source_url[0]=1;else $add_source_url[0]=0;
 $source_url_custom_fields = trim($_POST['source_url_custom_fields']);
 if($source_url_custom_fields=='')$add_source_url[0]=0;
 $add_source_url[1]=$source_url_custom_fields;

 $wpdb->query($wpdb->prepare("update $t_ap_config set           
			   add_source_url = %s WHERE id = %d",json_encode($add_source_url),$_POST['id'])
			  );

 //±£´æ Article Filtering
 if(!isset($_POST['post_filter']) || $_POST['post_filter']!='on'){
    $_POST['af2'] = '';
 }

 if(trim($_POST['af2'])!=''){
   $af = array();
   $af[0] = $_POST['af0'];
   $af[1] = $_POST['af1'];
   $af[2] = $_POST['af2'];
   $af[3] = $_POST['af3'];
   $af[4] = $_POST['af4'];
   
   $count = $wpdb->get_var('SELECT count(*) FROM '.$t_ap_more_content.' WHERE config_id ='.$_POST['id'].' AND option_type=1' );
   if($count==0){
     $wpdb->query($wpdb->prepare("insert into $t_ap_more_content(config_id,option_type,content) values (%d,1,%s)",$_POST['id'],json_encode($af)));
   }elseif($count==1){
     $wpdb->query($wpdb->prepare("update $t_ap_more_content set content = %s WHERE config_id = %d and option_type=1 ",json_encode($af),$_POST['id']));
   }
 }else{
   $wpdb->query('delete from '.$t_ap_more_content.' where config_id ='.$_POST['id'].' and option_type=1');
 }


 $saction2 = $_POST['saction2'];  
 if($saction2=='test2'){ 
   test2($_POST['id']);
 }else{ 
   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
 }
 $showBox2=true;
}// end if($saction=='save2'){

if($saction=='updateAll'){
   echo '<div class="updated fade"><p><b>'.__('Being processed, the processing may take some time, you can close the page','wp-autopost').'</b></p></div>';ob_flush();flush();
   //$m1=getMemUsage();
   fetchAll();
   //$m2=getMemPUsage();
   //echo '<div class="updated fade"><p>This update max used <strong>', getUsedMemory($m2-$m1) ,'</strong> memorys</p></div>';
}

if($saction=='save3'&& $_POST['saction3']!='autosetSettings' && !isset($_POST['use_auto_set']) ){
 
 if(isset($_POST['fecth_paged'])&&$_POST['fecth_paged']=='on')$fecth_paged=1;else $fecth_paged=0;
 if(isset($_POST['same_paged'])&&$_POST['same_paged']=='on')$same_paged=1;else $same_paged=0;
 
 if($_POST['title_match_type']==0)$title_selector = stripslashes(trim($_POST['title_selector_0']));
 elseif($_POST['title_match_type']==1){
    $title_selector = stripslashes(trim($_POST['title_selector_1_start'])).'WPAPSPLIT'.stripslashes(trim($_POST['title_selector_1_end']));
 }

 
 $content_match_type = array();
 $content_selector = array();
 
 if( isset($_POST['outer_0']) &&  $_POST['outer_0']=='on')$outer = 1;else $outer = 0;
 $objective=0; 
 $content_match_type[] = $_POST['content_match_type_0'].','.$outer.','.$objective.','.$_POST['index_0'];

 if($_POST['content_match_type_0']==0){   
	$content_selector[] = stripslashes(trim($_POST['content_selector_0_0']));
 }
 elseif($_POST['content_match_type_0']==1){
	$content_selector[] = stripslashes(trim($_POST['content_selector_1_start_0'])).'WPAPSPLIT'.stripslashes(trim($_POST['content_selector_1_end_0']));
 }
 
 if($_POST['cmrNum']>=1){
   for($i=1;$i<=$_POST['cmrNum'];$i++){
	  if(@$_POST['content_match_type_'.$i]==0){
		 if( !isset($_POST['content_selector_0_'.$i])||trim($_POST['content_selector_0_'.$i])=='')continue;
         
		 if(isset($_POST['outer_'.$i]) && $_POST['outer_'.$i]=='on')$outer = 1;else $outer = 0;
         
		 $objective = $_POST['objective_'.$i];
		 if($objective=='-1'){
            $objective = $_POST['objective_customfields_'.$i];
		 }
		 $content_match_type[] = $_POST['content_match_type_'.$i].','.$outer.','.$objective.','.$_POST['index_'.$i];
	     
		 $content_selector[] = stripslashes(trim($_POST['content_selector_0_'.$i]));
	  }elseif($_POST['content_match_type_'.$i]==1){
		 if($_POST['content_selector_1_start_'.$i]==null||trim($_POST['content_selector_1_start_'.$i])=='')continue;
		 if($_POST['content_selector_1_end_'.$i]==null||trim($_POST['content_selector_1_end_'.$i])=='')continue;
         
         if(isset($_POST['outer_'.$i])&&$_POST['outer_'.$i]=='on')$outer = 1;else $outer = 0;
         
         $objective = $_POST['objective_'.$i];
		 if($objective=='-1'){
            $objective = $_POST['objective_customfields_'.$i];
		 }

		 $content_match_type[] = $_POST['content_match_type_'.$i].','.$outer.','.$objective.','.$_POST['index_'.$i];
         
		 $content_selector[] = stripslashes(trim($_POST['content_selector_1_start_'.$i])).'WPAPSPLIT'.stripslashes(trim($_POST['content_selector_1_end_'.$i]));

	  }
	  
   }
 }
 
 $page_selector = array();
 if($_POST['fecth_paged_type']==0){
    $page_selector[0] = 0;
	$page_selector[1] = stripslashes(trim($_POST['page_selector_0']));
	if(trim($_POST['page_selector_0'])==''){
       $fecth_paged = 0;
	}
 }else{
    $page_selector[0] = 1;
	$page_selector[1] = stripslashes(trim($_POST['page_selector_1']));
	if(trim($_POST['page_selector_1'])==''){
       $fecth_paged = 0;
	}
 }

 $wpdb->query($wpdb->prepare("update $t_ap_config set           
			   content_test_url = %s,
			   title_match_type = %d,
			   title_selector = %s,
			   content_match_type = %s,
			   content_selector = %s, 
			   page_selector = %s,
			   fecth_paged = %d,
			   same_paged = %d  WHERE id = %d",$_POST['testUrl'],$_POST['title_match_type'],$title_selector,json_encode($content_match_type),json_encode($content_selector),json_encode($page_selector),$fecth_paged,$same_paged,$_POST['id'])
			  );
 
 $saction3 = $_POST['saction3']; 
 if($saction3=='test3'){
   test3($_POST['id'],$_POST['testUrl']);
 }else{ 
   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
 }
 $showBox3=true;
}// end if($saction=='save3'){



if($saction=='save3' && isset($_POST['use_auto_set']) ){
  test3($_POST['id'],$_POST['testUrl']);
}


if($saction=='save5'){ 
  $rewrite = array();
  switch($_POST['use_rewriter']){
   case '0':
     $wpdb->query($wpdb->prepare("update $t_ap_config set           
			   use_rewrite = %s WHERE id = %d",'0',$_POST['id'])
			  );
	 echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
   break;

   case '1':  // Microsoft Translator
     $MicroTransOptions = get_option('wp-autopost-micro-trans-options');
	 $transSetOk = false;
     foreach($MicroTransOptions as $k => $v){ 
	  if($v['clientID']!=null&&$v['clientSecret']!=null){
	    $transSetOk=true;
	    break;
	  }
     }
     if(!$transSetOk){
       $wpdb->query($wpdb->prepare("update $t_ap_config set           
			   use_rewrite = %s WHERE id = %d",'0',$_POST['id'])
			  );
     }else{   
	   $rewrite[0]=1;    
       $rewrite[1]=$_POST['rewrite_origi_language'];
	   $rewrite[2]=$_POST['rewrite_trans_language'];

       if(isset($_POST['rewrite_title_1']) && $_POST['rewrite_title_1']=='on')$rewrite[3]=1;else $rewrite[3]=0;
	   if(isset($_POST['rewrite_failure_1']) && $_POST['rewrite_failure_1']=='on')$rewrite[4]=1;else $rewrite[4]=0;

	   $wpdb->query($wpdb->prepare("update $t_ap_config set           
			   use_rewrite = %s WHERE id = %d",json_encode($rewrite),$_POST['id'])
			  );
	   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
     }

   break;

   case '4':  // Baidu Translator
     $BaiduTransOptions = get_option('wp-autopost-baidu-trans-options');
	 $transSetOk_Baidu = false;  
	 if($BaiduTransOptions['api_key']!=null&&$BaiduTransOptions['api_key']!=''){
	    $transSetOk_Baidu=true;
	 }
     if(!$transSetOk_Baidu){
       $wpdb->query($wpdb->prepare("update $t_ap_config set           
			   use_rewrite = %s WHERE id = %d",'0',$_POST['id'])
			  );
     }else{   
	   $rewrite[0]=4;    
       $rewrite[1]=$_POST['rewrite_origi_language_baidu'];
	   $rewrite[2]=$_POST['rewrite_trans_language_baidu'];

       if(isset($_POST['rewrite_title_4']) && $_POST['rewrite_title_4']=='on')$rewrite[3]=1;else $rewrite[3]=0;
	   if(isset($_POST['rewrite_failure_4']) && $_POST['rewrite_failure_4']=='on')$rewrite[4]=1;else $rewrite[4]=0;
       
	   $rewrite[5]=trim($_POST['rewrite_protected_words_baidu']);

	   $wpdb->query($wpdb->prepare("update $t_ap_config set           
			   use_rewrite = %s WHERE id = %d",json_encode($rewrite),$_POST['id'])
			  );
	   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
     }

   break;

   case '2':  // WordAi
     $rewrite[0]=2;
	 $rewrite[1]=$_POST['wordai_user_email'];
	 $rewrite[2]=$_POST['wordai_user_password'];
	 $rewrite[3]=intval($_POST['wordai_spinner']);
     if($rewrite[3]==1){
       $rewrite[4]=intval($_POST['standard_quality']);
	   $rewrite[5]=$_POST['standard_nonested'];
	 }else{
       $rewrite[4]=$_POST['turing_quality'];
	   $rewrite[5]=$_POST['turing_nonested'];
	 }
	 $rewrite[6]=$_POST['wordai_sentence'];
	 $rewrite[7]=$_POST['wordai_paragraph'];

	 if(isset($_POST['rewrite_title_2'])&&$_POST['rewrite_title_2']=='on')$rewrite[8]=1;else $rewrite[8]=0;
	 if(isset($_POST['rewrite_failure_2']) && $_POST['rewrite_failure_2']=='on')$rewrite[9]=1;else $rewrite[9]=0;
      
	 $wpdb->query($wpdb->prepare("update $t_ap_config set           
			   use_rewrite = %s WHERE id = %d",json_encode($rewrite),$_POST['id'])
			  );
	 echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';   
   
   break;

   case '3':  // SpinRewriter
    $rewrite[0]=3;
	$rewrite[1]=$_POST['spin_rewriter_user_email'];
	$rewrite[2]=$_POST['spin_rewriter_api_key'];

	if(isset($_POST['spin_rewriter_auto_sentences']) && $_POST['spin_rewriter_auto_sentences']=='on')$rewrite[3]=1;else $rewrite[3]=0;  
	if(isset($_POST['spin_rewriter_auto_paragraphs']) && $_POST['spin_rewriter_auto_paragraphs']=='on')$rewrite[4]=1;else $rewrite[4]=0; 
	if(isset($_POST['spin_rewriter_auto_new_paragraphs']) && $_POST['spin_rewriter_auto_new_paragraphs']=='on')$rewrite[5]=1;else $rewrite[5]=0;
	if(isset($_POST['spin_rewriter_auto_sentence_trees']) && $_POST['spin_rewriter_auto_sentence_trees']=='on')$rewrite[6]=1;else $rewrite[6]=0; 

	$rewrite[7]=$_POST['spin_rewriter_confidence_level'];

	if(isset($_POST['spin_rewriter_nested_spintax']) && $_POST['spin_rewriter_nested_spintax']=='on')$rewrite[8]=1;else $rewrite[8]=0;  
	if(isset($_POST['spin_rewriter_auto_protected_terms']) && $_POST['spin_rewriter_auto_protected_terms']=='on')$rewrite[9]=1;else $rewrite[9]=0;


	if(isset($_POST['rewrite_title_3']) && $_POST['rewrite_title_3']=='on')$rewrite[10]=1;else $rewrite[10]=0;
	if(isset($_POST['rewrite_failure_3']) && $_POST['rewrite_failure_3']=='on')$rewrite[11]=1;else $rewrite[11]=0;

	$wpdb->query($wpdb->prepare("update $t_ap_config set           
			   use_rewrite = %s WHERE id = %d",json_encode($rewrite),$_POST['id'])
			  );
	
	echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';   

   break;

  }//end  switch($_POST['use_rewriter']){
  $showBox13=true;
}


if($saction=='save4'){ 	
  $use_trans = array();
  $use_trans[0] = $_POST['use_trans'];
  
  if($use_trans[0]==1){
    $use_trans[1] = $_POST['translator1_from_Language'];
    $use_trans[2] = $_POST['translator1_to_Language'];
  }elseif($use_trans[0]==2){
    $use_trans[1] = $_POST['translator2_from_Language'];
    $use_trans[2] = $_POST['translator2_to_Language'];
  }else{
    $use_trans[1] = '';
    $use_trans[2] = '';
  }

  $post_method = $_POST['post_method'];
  if($post_method==-1){
    $use_trans[3] = -1;
  }elseif($post_method==-2){
    $use_trans[3] = -2;
  }elseif($post_method==-3){
    $use_trans[3] = -3;
  }else{
    $post_category = @$_POST['post_category'];
    if($post_category!=null){
      $cat='';
	  foreach($post_category as $cate){
        $cat.= $cate.',';
      }
	  $cat = substr($cat,0,-1);
    }else{
      $cat = 0;
	}
	$use_trans[3] = $cat;
  }
  
  $use_trans[4] = trim($_POST['trans_protected_words']);


  /*$MicroTransOptions = get_option('wp-autopost-micro-trans-options');
  $transSetOk = false;
  foreach($MicroTransOptions as $k => $v){ 
	if($v['clientID']!=null&&$v['clientSecret']!=null){
	  $transSetOk=true;
	  break;
	}
  }
  if(!$transSetOk){
     $use_trans[0]='0';
  }*/

  $wpdb->query($wpdb->prepare("update $t_ap_config  set
               use_trans = %s 
			   WHERE id = %d",json_encode($use_trans),$_POST['id'])
			   );

  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  $showBox10=true;
}// end if($saction=='save4'){ 

if($saction=='update'){
   
   $configs = $wpdb->get_row('SELECT * FROM '.$t_ap_config.' WHERE id ='.$_GET['id'] );	  
   
   $config = getConfig($_GET['id']);

   if( ($configs->source_type) ==1 ){
      
	  $list_url = $wpdb->get_var('SELECT url FROM '.$t_ap_config_url_list.' WHERE config_id ='.$_GET['id'].' ORDER BY id' );

	  if(($configs->reverse_sort)==0){
        if(isset($_GET['n'])){
          $n = $_GET['n'];
	    }else{
          $n = $configs->end_num;
	    }
	  }else{
        if(isset($_GET['n'])){
          $n = $_GET['n'];
	    }else{
          $n = $configs->start_num;
	    }  
	  }
      
	  if(($configs->reverse_sort)==0){
        if($n>=($configs->start_num)){
           
		   fetchSingleList($_GET['id'],$list_url,$n,$config); 
           $n--;	
		
		   echo '<p>'.__("If your browser doesn't start loading the next page automatically click this link:", 'wp-autopost').'<a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=update&id='.$_GET['id'].'&n='.$n.'">'.__('Next content', 'wp-autopost').'</a></p>';
	       echo '</div>';

		   echo '<script type="text/javascript"> function nextPage() { location.href = "admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=update&id='.$_GET['id'].'&n='.$n.'"; } window.setTimeout( "nextPage()", 300 );  </script> ';


		
		}
	  }else{     
        if($n<=($configs->end_num)){
           fetchSingleList($_GET['id'],$list_url,$n,$config); 
           $n++;
		   

		   echo '<p>'.__("If your browser doesn't start loading the next page automatically click this link:", 'wp-autopost').'<a href="admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=update&id='.$_GET['id'].'&n='.$n.'">'.__('Next content', 'wp-autopost').'</a></p>';
	       echo '</div>';

		   echo '<script type="text/javascript"> function nextPage() { location.href = "admin.php?page=wp-autopost-pro/wp-autopost-tasklist.php&saction=update&id='.$_GET['id'].'&n='.$n.'"; } window.setTimeout( "nextPage()", 300 );  </script> ';
		
		
		}
	  }
   
   
   }else{
     fetch($_GET['id']);
   }
}

if($saction=='save11'){ 
  
  $wpdb->query($wpdb->prepare("update $t_ap_config set 
               title_prefix = %s,
			   title_suffix = %s,
			   content_prefix = %s,
			   content_suffix = %s  WHERE id = %d",stripslashes($_POST['title_prefix']),stripslashes($_POST['title_suffix']),stripslashes($_POST['content_prefix']),stripslashes($_POST['content_suffix']),$_POST['id']
			   ));    
			
  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';

  $showBox8=true;
}// end if($saction=='save11'){ 

if($saction=='save6'){
 $saction6 = $_POST['saction6'];
 if($saction6=='SaveOption1'){
   $wpdb->query('delete from '.$t_ap_config_option.' where option_type =1 and config_id ='.$_POST['id']);
  
   @$para1 = $_POST['type1_para1'];
   @$para2 = $_POST['type1_para2'];
  
   for($i=0,$max=count($para1);$i<$max;$i++){
     if(trim($para1[$i])==''||trim($para1[$i])==null)continue;
     $wpdb->query('insert into '.$t_ap_config_option.'(config_id,option_type,para1,para2) values ('.$_POST['id'].',1,"'.trim($para1[$i]).'","'.trim($para2[$i]).'" )');
   }
   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
   $showBox4=true;
  }
  if($saction6=='SaveOption5'){
    $wpdb->query('delete from '.$t_ap_config_option.' where option_type =5 and config_id ='.$_POST['id']);
  
    @$para1 = $_POST['type5_para1'];
	@$para2 = $_POST['type5_para2'];
  
    for($i=0,$max=count($para1);$i<$max;$i++){
     if(trim($para1[$i])==''||trim($para1[$i])==null)continue;
     
	 if(trim($para2[$i])==''||trim($para2[$i])==null){ $para2[$i] = 0; }

     $wpdb->query('insert into '.$t_ap_config_option.'(config_id,option_type,para1,para2) values ('.$_POST['id'].',5,"'.trim($para1[$i]).'","'.trim($para2[$i]).'")');
    }
    echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
   $showBox4=true;
  }
} // if($saction=='save6'){



if($saction=='save7'){
  $wpdb->query('delete from '.$t_ap_config_option.' where option_type = 2 and config_id ='.$_POST['id']);
  
  @$para1 = $_POST['type2_para1'];
  @$para2 = $_POST['type2_para2'];
  
  for($i=0,$max=count($para1);$i<$max;$i++){
   if(trim($para1[$i])==''||trim($para1[$i])==null)continue;
   $wpdb->query('insert into '.$t_ap_config_option.'(config_id,option_type,para1,para2) values ('.$_POST['id'].',2,"'.trim(strtolower($para1[$i])).'","'.trim($para2[$i]).'" )');
  }
  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  $showBox5=true;
}// end if($saction=='save7'){


if($saction=='save8'){
  $wpdb->query('delete from '.$t_ap_config_option.' where option_type = 3 and config_id ='.$_POST['id']);
  
  @$para1 = $_POST['type3_para1'];
  @$para2 = $_POST['type3_para2'];
  @$options = $_POST['type3_option'];


  //if(isset($_POST['rewrite_title_2'])&&$_POST['rewrite_title_2']=='on')$rewrite[8]=1;else $rewrite[8]=0;
  
  for($i=0,$max=count($para1);$i<$max;$i++){
   if(trim($para1[$i])==''||trim($para1[$i])==null)continue;
   

   
   $wpdb->query('insert into '.$t_ap_config_option.'(config_id,option_type,para1,para2,options) values ('.$_POST['id'].',3,"'.trim($para1[$i]).'","'.trim($para2[$i]).'","'.trim($options[$i]).'" )');
  }
  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  $showBox6=true;
}


if($saction=='save9'){
  $wpdb->query('delete from '.$t_ap_config_option.' where option_type = 4 and config_id ='.$_POST['id']);
  
  @$para1 = $_POST['type4_para1'];
  @$para2 = $_POST['type4_para2'];
  
  for($i=0,$max=count($para1);$i<$max;$i++){
   if(trim($para1[$i])==''||trim($para1[$i])==null)continue;
   $wpdb->query('insert into '.$t_ap_config_option.'(config_id,option_type,para1,para2) values ('.$_POST['id'].',4,"'.trim($para1[$i]).'","'.trim($para2[$i]).'" )');
  }
  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  $showBox7=true;
}

if($saction=='save10'){
  $wpdb->query('delete from '.$t_ap_more_content.' where config_id ='.$_POST['id'].' and option_type=0');
  
  @$para1 = $_POST['type6_para1'];
  @$para2 = $_POST['type6_para2'];
  @$para3 = $_POST['type6_para3'];
  @$para4 = $_POST['type6_para4'];
  
  
  for($i=0,$max=count($para1);$i<$max;$i++){
   if(trim($para1[$i])==''||trim($para1[$i])==null)continue;
   if(trim($para2[$i])==''||trim($para2[$i])==null)continue;
   if(trim($para4[$i])==''||trim($para4[$i])==null)continue;
   
   //if($para2[$i]==0)$para2[$i]=1;
   
   $content = array();
   $content[] = $para1[$i];
   $content[] = $para2[$i];
   $content[] = $para3[$i];
   $content[] = stripslashes($para4[$i]);

   $wpdb->query($wpdb->prepare("insert into $t_ap_more_content (config_id,content) values (%d,%s)",$_POST['id'],json_encode($content)));
  }
  
  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  $showBox9=true;
}

if($saction=='save14'){
  $wpdb->query('delete from '.$t_ap_more_content.' where config_id ='.$_POST['id'].' and option_type=2');
  
  @$para1 = $_POST['type14_para1'];
  @$para2 = $_POST['type14_para2'];
  @$para3 = $_POST['type14_para3'];
  @$para4 = $_POST['type14_para4']; 
  
  for($i=0,$max=count($para1);$i<$max;$i++){
   if(trim($para1[$i])==''||trim($para1[$i])==null)continue;
   if(trim($para2[$i])==''||trim($para2[$i])==null)continue;
   if(trim($para4[$i])==''||trim($para4[$i])==null)continue;
   
   $content = array();
   $content[] = $para1[$i];
   $content[] = $para2[$i];
   $content[] = $para3[$i];
   $content[] = stripslashes($para4[$i]);

   $wpdb->query($wpdb->prepare("insert into $t_ap_more_content (config_id,option_type,content) values (%d,%d,%s)",$_POST['id'],2,json_encode($content)));
  }
  
  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  $showBox14=true;
}

if($saction=='save12'){
  $saction12 = $_POST['saction12'];
  if($saction12=='newCustomField'){

	 $var = $wpdb->get_var($wpdb->prepare("select custom_field from $t_ap_config where id = %d",$_POST['id']));
     
	 $custom_field = array();
	 if($var!=null&&$var!=''){
       $old_custom_field = json_decode($var);
	   foreach($old_custom_field as $key => $value){
            $custom_field[$key] = $value;
	   }
	 }

     $custom_field[$_POST['metakey']]=$_POST['metavalue'];
	 
	 $wpdb->query($wpdb->prepare("update $t_ap_config set custom_field = %s  WHERE id = %d",json_encode($custom_field),$_POST['id']));    
     
  }
  
  if($saction12=='DeleteCustomField'){
	 
	 $var = $wpdb->get_var($wpdb->prepare("select custom_field from $t_ap_config where id = %d",$_POST['id']));
     
	 $custom_field = array();
	 if($var!=null&&$var!=''){
       $old_custom_field = json_decode($var);
	   foreach($old_custom_field as $key => $value){
            $custom_field[$key] = $value;
	   }
	 }

	 unset($custom_field[$_POST['custom_field_key']]);

     $wpdb->query($wpdb->prepare("update $t_ap_config set custom_field = %s  WHERE id = %d",json_encode($custom_field),$_POST['id']));
  }

  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  $showBox12=true;
}// end if($saction=='save12'){

if($saction=='testFetch'){	
  testFetch($_POST['id']);
}


if($saction=='deleteSubmit'){
   
   $wpdb->query('delete from '.$t_ap_config.' where id ='.$_POST['configId']);
   $wpdb->query('delete from '.$t_ap_config_option.' where config_id ='.$_POST['configId']);
   $wpdb->query('delete from '.$t_ap_config_url_list.' where config_id ='.$_POST['configId']);
   $wpdb->query('delete from '.$t_ap_more_content.' where config_id ='.$_POST['configId']);
   
   echo '<div id="message" class="updated fade"><p>'.__('Deleted!','wp-autopost').'</p></div>';
}

if($saction=='ignore'){
    $wpdb->query('UPDATE '.$t_ap_config.' SET last_error = 0 WHERE id = '.$_GET['id'] );
}





if($saction=='abort'){
  $wpdb->query('UPDATE '.$t_ap_config.' SET is_running = 0 WHERE id = '.$_GET['id'] ); 
}

if($saction=='changePerPage'){
   $wp_autopost_per_page = get_option('wp_autopost_per_page');
   $wp_autopost_per_page['task'] = $_POST['taskPerPage'];
   update_option('wp_autopost_per_page',$wp_autopost_per_page);   
}


if($saction=='save15'){
 
 //$the_cookie = stripslashes(trim($_POST['the_cookie']));

 //$wpdb->query($wpdb->prepare("update $t_ap_config set cookie = %s  WHERE id = %d",$the_cookie,$_POST['id']));
 
 if($_POST['login_set_mode']==1){
   $loginSets['mode'] = 1;
   $loginSets['url'] = $_POST['login_url'];
   
   $paraName = $_POST['loginParaName'];
   $paraValue = $_POST['loginParaValue']; 
   
   $paraString='';
   for($i=0,$max=count($paraName);$i<$max;$i++){
     if(trim($paraName[$i])==''||$paraName[$i]==null)continue;
	 if(trim($paraValue[$i])==''||$paraValue[$i]==null)continue;
	 
	 $paraString.=stripslashes(trim($paraName[$i])).'='.stripslashes(trim($paraValue[$i])).'&';
   }
   if($paraString!='')$paraString = substr($paraString,0,-1);
   $loginSets['para'] = $paraString;
   
   if($loginSets['url']==''||$paraString==''){
     $wpdb->query($wpdb->prepare("update $t_ap_config set cookie = ''  WHERE id = %d",$_POST['id']));
   }else{
     $wpdb->query($wpdb->prepare("update $t_ap_config set cookie = %s  WHERE id = %d",json_encode($loginSets),$_POST['id']));
   }
   
 }else{
   $loginSets['mode'] = 2;
   $loginSets['cookie'] = stripslashes(trim($_POST['the_cookie']));
   
   if($loginSets['cookie']==''){
     $wpdb->query($wpdb->prepare("update $t_ap_config set cookie = ''  WHERE id = %d",$_POST['id']));
   }else{
     $wpdb->query($wpdb->prepare("update $t_ap_config set cookie = %s  WHERE id = %d",json_encode($loginSets),$_POST['id']));
   }
   
 }
 

 
 $saction15 = $_POST['saction15']; 
 if($saction15=='testCookie'){
   testCookieUrl($_POST['testcCookieUrl'],$loginSets);   
 }else{ 
   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
 }
 $showBox15=true;

}// end if($saction=='save15'){


if($saction=='save16'){
 
 $wpdb->query($wpdb->prepare("update $t_ap_config set zh_conversion = %s  WHERE id = %d",$_POST['zh_conversion'],$_POST['id']));
 echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
 $showBox16=true;

}// end if($saction=='save15'){


/*
if ( !function_exists('wp_generate_attachment_metadata') ) {
  include ABSPATH.'wp-admin/includes/image.php';
}
*/


if($saction=='save17'&& $_POST['saction17']=='deleteDefaultImg'){
  wp_delete_attachment( $_POST['attach_id'], true);
  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  $showBox17 = true;
}

if($saction=='save17'&& $_POST['saction17']=='uploadDefaultImg'){
  $fileinfo = $_FILES['default-image']; 
  if ($fileinfo["error"] > 0){     
    echo '<div id="message" class="error fade"><p>'."Upload Error: " . $fileinfo["error"] .'</p></div>';
  }else{    
	$file_path = $fileinfo['tmp_name'];
	$filename= $fileinfo["name"];
	$mime_type = $fileinfo["type"];

	$uploads = wp_upload_dir ( $time );
	$path=$uploads ['path'];
	
	$filename = wp_unique_filename ($path, $filename, null);
	$new_file = $path . "/$filename";

	$res = move_uploaded_file( $file_path, $new_file);
	if( $res ){     
	  $url = $uploads ['url'] . "/$filename";
	  $attachment = array (
		  'post_mime_type' => $mime_type,
		  'guid' => $url,
		  'post_title' => $filename,
		  'post_content' => '' 
	  );
	  $attach_id = wp_insert_attachment ( $attachment, $new_file, 0 );

	  $attach_data = wp_generate_attachment_metadata( $attach_id, $new_file );
	  wp_update_attachment_metadata( $attach_id,  $attach_data );
      
	  $featuredImages = get_option('wp-autopost-featued-images');
      
	  if($featuredImages==null||$featuredImages==''){
        $featuredImages = array();
        $featuredImages[] = $attach_id;
	  }else{
        $featuredImages[] = $attach_id;
	  }

	  update_option( 'wp-autopost-featued-images', $featuredImages);
	   
	  echo '<div id="message" class="updated fade"><p>'.__('Already Uploaded!','wp-autopost').'</p></div>';

	}else{      
	    echo '<div id="message" class="error fade"><p>Upload Error</p></div>';
	}
  }
  $showBox17 = true;
}

if($saction=='save17'&& $_POST['saction17']=='save17'){
  
  $default_image = array();

  if($_POST['use_default_image']==1){
    $default_image[0] = 1;
    $selected_images = array();
    foreach($_POST['selectedImgs'] as $selectedimg){
      if($selectedimg>0){
        $selected_images[] = $selectedimg;
	  }
	}
    
	if(count($selected_images)>0){
	  $default_image[1] = $selected_images;
	  $wpdb->query($wpdb->prepare("update $t_ap_config set default_image = %s  WHERE id = %d",json_encode($default_image),$_POST['id']));

	  echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
	}else{
      echo '<div id="message" class="error fade"><p>'.__('Please at least selected one image','wp-autopost').'</p></div>';
	}
  }else{
    $default_image[0] = 0;
	$default_image[1] = array();
	$wpdb->query($wpdb->prepare("update $t_ap_config set default_image = %s  WHERE id = %d",json_encode($default_image),$_POST['id']));
	echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autopost').'</p></div>';
  }

  $showBox17 = true;
}



function testCookieUrl($url,$loginSets){
  if(!function_exists('curl_init')) {
    echo '<div class="error"><p>cURL extension is not enable, can not use Cookie</p></div>';
	return;
  }

  if($loginSets['mode']==1):
    $cookie_jar = get_cookie_jar_ap($loginSets['url'],$loginSets['para']);
    //echo 'file:'.$cookie_jar;
    $result = curl_get_contents_ap($url, 0, null, 0, 30, null,$cookie_jar);
    unlink($cookie_jar);
  else: //$loginSets['mode']==2
    $cookie = $loginSets['cookie'];
   
    $result = curl_get_contents_ap($url, 0, null, 0, 30, $cookie);
  endif;
  
  $dom = str_get_html_ap($result);
  $fcs = $dom->find('script');
  foreach($fcs as $fc){
    $fc->outertext = '';
  }
  $result = $dom->save();
  $dom->clear(); 
  unset($dom);
  
  
  $file = dirname(__FILE__).'/test_cookie_temp.html';
  $fileUrl=plugins_url('/test_cookie_temp.html', __FILE__ );
    
  file_put_contents ( $file, $result );
  echo '<div id="message" class="updated fade"><p>'.__('The Cookie test result, if Cookie set right you can see the contents that need to login','wp-autopost').'</p></div>';
  echo '<div style="border-width:2px;border-style:solid;border-color:#dfdfdf; margin-bottom:20px;">';
  echo '<iframe src="'.$fileUrl.'"  width="100%" height="600" frameborder="0"  ></iframe></div>';
  


}