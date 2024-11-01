<?php
/*
Plugin Name: WPG Cool Gallery
Plugin URI: http://wordpress.org/plugins/wpg-cool-gallery/
Description: A cool gallery  gallery with auto loading posts with image and video 
Version: 1.0
Author: IndiaNIC
Author URI: http://profiles.wordpress.org/indianic
*/

$gallery_version = '1.0.0';

class WPG_cool_gallery {

  var $pluginPath;
  var $pluginUrl;
  var $rootPath;
  var $wpdb;
  

  public function __construct() {
  	
	  	global $wpdb;
	    $this->wpdb = $wpdb;
	    $this->ds = DIRECTORY_SEPARATOR;
	    $this->pluginPath = dirname(__FILE__) . $this->ds;
	    $this->rootPath = dirname(dirname(dirname(dirname(__FILE__))));
	    $this->pluginUrl = WP_PLUGIN_URL . '/wpg-cool-gallery/';
	    
	    add_action('admin_menu', array($this, 'wpg_photo_gallery_admin_menu'));
	    add_action('admin_init', array($this,'wpg_photo_gallery_add_admin_JS_CSS'));    
	    add_action('admin_init', array($this, 'wpg_cool_gallery_Add_custom_size'));
	    
	    
	    add_action('add_meta_boxes', array($this,'wpg_cool_gallery_meta_box_add' ));	    	    
	    add_action('admin_footer', array($this,'wpg_cool_gallery_modify_form'));
	    add_action('save_post', array($this,'wpg_cool_gallery_updated_custom_meta') );
	    	    
	    add_action( 'wp_enqueue_scripts', array($this,'wpg_photo_gallery_front_JS_CSS'));	    
	    add_shortcode('wpg-cool-gallery-front', array($this, 'wpg_cool_gallery_front_Handler'));
	    
	    add_action( 'wp_ajax_nopriv_Cool_image_load', array($this, 'wpg_ajaxCool_image_load'));
		add_action( 'wp_ajax_Cool_image_load', array($this, 'wpg_ajaxCool_image_load'));
		add_action( 'wp_ajax_nopriv_getVidImage', array($this, 'wpg_ajaxgetVidImage'));
		add_action( 'wp_ajax_getVidImage', array($this, 'wpg_ajaxgetVidImage'));		
		
		add_filter('image_size_names_choose', array($this, 'wpg_cool_gallery_filter_custom_image_sizes'));
	}
		
	
	public function wpg_cool_gallery_Add_custom_size()
	{
		add_image_size( 'new-cool-size', 750, 600, true ); //cropped
	}
	
	public function wpg_cool_gallery_filter_custom_image_sizes($sizes) {
	       $addsizes = array(
                "new-cool-size" => __( "WPG Cool Gallery Best")
                );
        	$newsizes = array_merge($sizes, $addsizes);
       	 	return $newsizes;
	}
	
	/**
	 * update all the meta value for post
	 * 
	 */
	function wpg_cool_gallery_updated_custom_meta() {
		global $post;
		
		if ( isset( $_POST['wpg_meta_cool_select'] ) && $_POST['wpg_meta_cool_select'] != '' ) 
			update_post_meta($post->ID,'wpg_meta_cool_select',trim($_POST['wpg_meta_cool_select']));
		
		if ( isset( $_POST['wpg_meta_cool_video_link'] ) && $_POST['wpg_meta_cool_video_link'] != '' ) 
			update_post_meta($post->ID,'wpg_meta_cool_video_link',trim($_POST['wpg_meta_cool_video_link']));
			
		if ( isset( $_POST['upload_cool_image'] ) && $_POST['upload_cool_image'] != '' ) 
			update_post_meta($post->ID,'upload_cool_image',trim($_POST['upload_cool_image']));			
	}	
	
	/**
	 * form to select and update image from popup
	 * 
	 */
	
	 function wpg_cool_gallery_modify_form(){
		echo  '<script type="text/javascript">				 								
				jQuery("#wpg_meta_cool_image").click(function() {
					window.send_to_editor = function(html) {
					 imgurl = jQuery("img",html).attr("src");	
					 jQuery("#upload_cool_image").val(imgurl);					 
					 jQuery("#view_cool_gallery_img").attr("src", imgurl);
					 jQuery("#view_cool_gallery_img").css("display", "block");
					 tb_remove();
				}								
				 tb_show("", "media-upload.php?post_id=1&type=image&TB_iframe=true");
				 return false;
				});
		      	jQuery("#postdiv, #postdivrich").prependTo("#custom_editor .inside");
		        </script>
		  ';
	}
	
	
	/**
	 * add metabox to custom post type	 
	 * 
	 */
	
	function wpg_cool_gallery_meta_box_add(){  
	   add_meta_box( 'meta_add_cool_gallery', __('Image Information', 'coolgallery'),  array( $this, 'wpg_cool_gallery_info_meta_box' ), 'niccoolgallery', 'normal', 'high' );	   	   
	} 
	
	
	function wpg_cool_gallery_info_meta_box(){
		global $post;	
		$values = get_post_meta($post->ID);
		$wpg_meta_cool_select = isset( $values['wpg_meta_cool_select'] ) ? esc_attr( $values['wpg_meta_cool_select'][0] ) :  '' ; 	
		$wpg_meta_cool_video_link = isset( $values['wpg_meta_cool_video_link'] ) ? esc_attr( $values['wpg_meta_cool_video_link'][0] ) :  '' ; 
		$wpg_meta_cool_image = isset( $values['upload_cool_image'] ) ? esc_attr( $values['upload_cool_image'][0] ) :  '' ; '' ;
		
		if($wpg_meta_cool_select=="video_link")
		{
			update_post_meta($post->ID,'upload_cool_image','');
			$wpg_meta_cool_image = '';
		}		
		else 
		{
			update_post_meta($post->ID,'wpg_meta_cool_video_link','');
			$wpg_meta_cool_video_link = '';
		}
		
		?>

<table width="700">
		<tr>
				<td><div style="padding:4px;float:left;width:650px;">
								<div style="float:left;width:310px;">
										<label for="wpg_meta_cool_select"> <?php echo __('Select Option', 'coolgallery'); ?></label>
								</div>
								<div style="float:left;width:310px;">
										<select class="postform" id="wpg_meta_cool_select" name="wpg_meta_cool_select" title="Select Option" onchange="return hideandshowOption(this);">
												<option>----Select----</option>
												<option <?php if($wpg_meta_cool_select=="image") {?>selected="selected" <?php } ?> value="image" class="level-0">Image</option>
												<option <?php if($wpg_meta_cool_select=="video_link") {?>selected="selected" <?php } ?> value="video_link" class="level-0">Video</option>
										</select>
								</div>
						</div>
						<div style="padding:4px;float:left;width:650px;<?php if($wpg_meta_cool_select=="video_link") {?>display:block;<?php }else {?>display:none;<?php }?>" id="wpg_link_field">
								<div style="float:left;width:310px;">
										<label for="wpg_meta_cool_video_link"> <?php echo __('Enter youtube or vemio video Link', 'coolgallery'); ?></label>
								</div>
								<div style="float:left;width:310px;">
										<input title="Enter youtube or vemio video link" type="text" name="wpg_meta_cool_video_link" class="wpg_meta_cool_video_link" id="wpg_meta_cool_video_link" value="<?php echo $wpg_meta_cool_video_link; ?>"  size="70"/>
								</div>
						</div>
						<div style="padding:4px;float:left;width:650px;<?php if($wpg_meta_cool_select=="image") {?>display:block;<?php }else {?>display:none;<?php }?>" id="wpg_image_field">
								<div style="float:left;width:310px;">
										<label for="wpg_meta_cool_image"> <?php echo __('Upload Image', 'coolgallery'); ?></label>
								</div>
								<div style="float:left;width:310px;">
										<input type="hidden" name="upload_cool_image" class="upload_cool_image" id="upload_cool_image" value="<?php echo $meta_coupon_image_code; ?>" />
										<input type="button" name="wpg_meta_cool_image" id="wpg_meta_cool_image" value="Add Media File" title="Click here.." class="button button-primary button-large"/>
										<span> <!--(180px X 66px)--></span> </div>
						</div>
						<div style="padding:4px;float:left;width:650px;<?php if($wpg_meta_cool_select=="video_link") {?>display:none;<?php }else {?>display:block;<?php }?>" id="wpg_image_field_image" >
								<div style="float:left;width:310px;"> &nbsp; </div>
								<div style="float:left;width:310px;">
										<?php if($wpg_meta_cool_image){?>
										<img src="<?php echo $wpg_meta_cool_image; ?>" id="view_cool_gallery_img" width="100" height="100" />
										<?php } else {?>
										<img src="<?php echo $wpg_meta_cool_image; ?>" id="view_cool_gallery_img" width="100" height="100" style="display:none;"/>
										<?php } ?>
								</div>
						</div></td>
		</tr>
		<tr>
				<td><input type="hidden" name="site_name" id="site_name" value="<?php echo site_url(); ?>"  /></td>
		</tr>
</table>
<?php
	}
				
	/**
	 * funtion to get datas in popup
	 * 
	 */
	
	public function wpg_ajaxgetVidImage()
	{
		$type     = $_REQUEST['type'];
		$source   = $_REQUEST['source'];
		$postname = $_REQUEST['postname'];
?>
<div id="popup_head" style="width:100%;float:left;">
		<div  class="popup_title" > <?php echo $postname;?> </div>
		<div style="float:right;">
				<?php if($type=="youtube"){ echo "Youtube Video";}elseif ($type=="vimeo"){  echo "Vimeo Video";}else{  echo "Image"; }?>
		</div>
</div>
<div style="width:100%;float:left;">
		<hr class="popuphori_line" />
</div>
<?php		if($type=="youtube"){
			$unique_id =  uniqid('youtube_');
?>
<div class="youvideo">
		<div id="gplayer<?php echo $unique_id;?>"> </div>
</div>
<script type="text/javascript">		
				 gplayer<?php echo $unique_id;?> = new YT.Player("gplayer<?php echo $unique_id;?>", {
			       videoId: "<?php echo $source;?>",
				   playerVars: { "autoplay": 0,"html5": 1 }
			      });
	</script>
<?php
			
		}
		elseif ($type=="vimeo")	{
			
			$iframeurl = 'http://player.vimeo.com/video/'.$source.'?title=1&byline=1&portrait=0&fullscreen=1';
			echo $ifamedata = '<div class="youvideo"><iframe class="vimeo_palyer" src="'.$iframeurl.'"></iframe></div>';
					
		}
		elseif ($type=="image")	{
			echo  '<div class="youimage"><img style="" src="'.$source.'" /></div>';
		}
		else{
			echo '<div class="youimage"><img  src="'.$this->pluginUrl.'/images/no-record.png" /></div>';
		}
		
		die;
	}

	/**
	 * function get the more posts of category
	 * 
	 */ 
	public function wpg_ajaxCool_image_load(){
		
			$tot_show_posts = $_REQUEST['tot_show_posts'];		
			$tot_api_count  = $_REQUEST['tot_api_count'];						
			$vcat_id = $_REQUEST['vcat_id'];		
			
			$vids = '';
			$tot_api = $tot_api_count;
			$arr_youtube = array();						
			
			if(isset($_REQUEST['types']) && $_REQUEST['types']=="onchangeselect")
			{
				$tot_show_posts = 0;
			}
			else
			{
				$tot_show_posts = $tot_show_posts + 10;
			}
			 
			
		$cat_id = $vcat_id;
					
		$taxonomies = 'niccoolcategory';		
		$categories = get_terms($taxonomies, 'orderby=count&order=DESC&hide_empty=1');
		
		
		if($cat_id=="-1"){$cat_id="all";}
			
		if($cat_id!="all")
		{
			foreach( $categories as $category ){
				
					$term_id = $category->term_id;
					if($term_id==$cat_id)
					{	
						$taxnomy = $category->taxonomy;
						$term = $category->slug;					
					}
			}
		}
		else
		{
			$term ='';
		}
		
		$posts_array = new WP_Query( array(
		        'post_type' => 'niccoolgallery',
				'taxonomy' => 'niccoolcategory',
				'term' => $term,
		        'showposts' => 10,
		        'offset'    => $tot_show_posts,
		        'orderby'   => 'ID',
				'order'     => 'DESC',
				'post_status'     => 'publish'
		    ));
			
		    				
			$three_count=$tot_show_posts+1;			
			$checkrec = 0;
			
			while ( $posts_array->have_posts() ) 
			{ 
				$posts_array->the_post();
								
				$post_id     = get_the_ID();				
				$post_name   = get_the_title();
				$post_guid 	 = get_the_guid();
				
				$len = strlen($post_name);
				if($len>30)
				{
					$post_name = substr($post_name, 0, 27);
					$post_name .="...";
				}
								
				$values = get_post_meta($post_id);				
				$wpg_meta_cool_select = isset( $values['wpg_meta_cool_select'] ) ? esc_attr( $values['wpg_meta_cool_select'][0] ) :  '' ; 		
				$wpg_meta_cool_video_link = isset( $values['wpg_meta_cool_video_link'] ) ? esc_attr( $values['wpg_meta_cool_video_link'][0] ) :  '' ; 
				$wpg_meta_cool_image = isset( $values['upload_cool_image'] ) ? esc_attr( $values['upload_cool_image'][0] ) :  '' ; '' ;		
				
				$image_url = '';
				$video_type = '';
		
														
				if($wpg_meta_cool_select == "video_link")
				{			
						$video_link = $wpg_meta_cool_video_link;
						
						$youtubeid =  $this->wpg_youtube_id_from_url($video_link);
						
						if(!$youtubeid && $video_link!='')
						{
						$url = $video_link;
							if(preg_match('/http:\/\/(www\.)*vimeo\.com\/.*/',$url,$matches)){					
								// do vimeo stuff				   
								preg_match_all('#(http://vimeo.com)/([0-9]+)#i',$url,$output);
								$vimeo_id = $output[2][0];
								$video_type="vimeo";
							}			
						}
						else if($youtubeid)
						{
							$video_type="youtube";
						}					
				}
				elseif ($wpg_meta_cool_select == "image")
				{
					$video_type = '';
					$image_url = $wpg_meta_cool_image;
				}
				
				if($video_type=="youtube")
				{
					$ele_type = "youtube";
					$tot_api++;
					$arr_youtube[$tot_api]=	$youtubeid;
					$vids.=	'<li  onclick="return get_the_information(\''.$ele_type.'\',\''.$youtubeid.'\',\''.$post_name.'\');" >
					<div class="post_title_class">'.$post_name.'</div>
					<img id="light_box_pop'.$three_count.'"src="'.$this->wpg_get_video_image($video_link).'"  />
					<span class="zoomImg"></span>
					</li>';				
				}
				else if($video_type=="vimeo")
				{
					
					$ele_type = "vimeo";	
							
					$iframeurl = 'http://player.vimeo.com/video/'.$vimeo_id.'?title=1&byline=1&portrait=0&fullscreen=1';
					$vids.=	'<li  onclick="return get_the_information(\''.$ele_type.'\',\''.$vimeo_id.'\',\''.$post_name.'\');" >
						
					    <div class="post_title_class">'.$post_name.'</div>
						<img id="light_box_pop'.$three_count.'" src="'.$this->wpg_get_video_image($video_link).'"  />
						<span class="zoomImg"></span>
						</li>';				
			
				}
				elseif ($image_url)
				{
					$ele_type = "image";	
					$vids.=	'<li  onclick="return get_the_information(\''.$ele_type.'\',\''.$image_url.'\',\''.$post_name.'\');">
					 <div class="post_title_class">'.$post_name.'</div>
					<img id="light_box_pop'.$three_count.'"  src="'.$image_url.'" />
					<span class="zoomImg"></span>
					</li>';
				}
				else
				{
					$ele_type = "no-image";
					$nul_bva = 'noval';
					//$vids.=	'<li><img  onclick="return get_the_information(\''.$ele_type.'\',\''.$nul_bva.'\',\''.$post_name.'\');"  src="'.$this->pluginUrl.'/images/no-record.png" ></li>';
				}			 
				
					$checkrec++;
					$three_count++;
			}			
			
		$str_youtube = implode(",", $arr_youtube);		
		if($checkrec>0)
		{
			echo $vids;
		}
		else{
			// no -records
		}
			
		die;
	}	
	
		
	/**
	 * get youtube video ID from URL
	 *
	 * @param string $url
	 * @return string Youtube video id or FALSE if none found. 
	 */
	public function wpg_youtube_id_from_url($url) {
	    $pattern = 
	        '%^# Match any youtube URL
	        (?:https?://)?  				# Optional scheme. Either http or https
	        (?:www\.)?      				# Optional www subdomain
	        (?:            					# Group host alternatives
	          youtu\.be/    				# Either youtu.be,
	        | youtube\.com 					# or youtube.com
	          (?:           				# Group path alternatives
	            /embed/     				# Either /embed/
	          | /v/         				# or /v/
	          | /watch\?v=  				# or /watch\?v=          
	          | /movie\?v=  				# or /movie\?v=           
	          )             				# End path alternatives.
	        )               				# End host alternatives.
	        ([\w-]{10,12})  			    # Allow 10-12 for 11 char youtube id.
	        $%x'
	        ;
	    $result = preg_match($pattern, $url, $matches);
	    if (false !== $result) {
	        return $matches[1];
	    }
	    return false;    
	}
	
	
	/**
	 * get the youtube and vemio image...
	 *
	 */
	
	public function wpg_get_video_image($url)
	{
		$image_url = parse_url($url);
		if($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com' || $image_url['host'] =='youtu.be')
		{
			$ids =  $this->wpg_youtube_id_from_url($url);
			$array = explode("&", $image_url['query']);
			if($ids!= '')
				return "http://img.youtube.com/vi/".$ids."/0.jpg";
			else
				return "http://img.youtube.com/vi/0.jpg";
			 	
		} 
		else if($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com')
		{
			$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".substr($image_url['path'], 1).".php"));
			return $hash[0]["thumbnail_large"];		
		}
	}
	
	public function wpg_cool_gallery_front_Handler($atts)
	{
		$vcat_id = @$atts['id'];												
		$vids = '';
		$tot_api = 0;
		$arr_youtube = array();
		
		$cat_id = $vcat_id;						
		if ( !is_admin() && $cat_id)
		{	
			if($cat_id!='all')
			{
				$taxonomies = 'niccoolcategory';		
				$categories = get_terms($taxonomies, 'orderby=count&order=DESC&hide_empty=1');		
			
				foreach( $categories as $category )
				{			
					$term_id = $category->term_id;
					if($term_id==$cat_id)
					{	
						$taxnomy = $category->taxonomy;
						$term = $category->slug;					
					}
				}
			}
			else
			{
				$term='';
			} 	
						
			$posts_array = new WP_Query( array(
		        'post_type'   => 'niccoolgallery',
				'taxonomy'    => 'niccoolcategory',
				'term'        => $term,
		        'showposts'   => 10,
		        'offset'      => 0,
		        'orderby'     => 'ID',
				'order'       => 'DESC',
				'post_status' => 'publish'
		    ));
		
			
			$three_count=1;												
			$vids.= '<div id="wpg_cool_container"><ul class="post_listing">';
			$cnt = 0;
			$all_three_count = 0;
			
			while ( $posts_array->have_posts() ) 
			{ 
				$posts_array->the_post();
								
				$post_id     = get_the_ID();
				$post_name   = get_the_title();
				$post_guid 	 = get_the_guid();
			
				$len = strlen($post_name);
				if($len>30)
				{
					$post_name = substr($post_name, 0, 27);
					$post_name .="...";
				}
				
				
		$values = get_post_meta($post_id);
				
		$wpg_meta_cool_select = isset( $values['wpg_meta_cool_select'] ) ? esc_attr( $values['wpg_meta_cool_select'][0] ) :  '' ; 		
		$wpg_meta_cool_video_link = isset( $values['wpg_meta_cool_video_link'] ) ? esc_attr( $values['wpg_meta_cool_video_link'][0] ) :  '' ; 
		$wpg_meta_cool_image = isset( $values['upload_cool_image'] ) ? esc_attr( $values['upload_cool_image'][0] ) :  '' ; '' ;		
		
		$image_url = '';
		$video_type = '';
		
					
		if($wpg_meta_cool_select == "video_link")
		{			
				$video_link = $wpg_meta_cool_video_link;				
				$youtubeid =  $this->wpg_youtube_id_from_url($video_link);
				
				if(!$youtubeid && $video_link!='')
				{
				$url = $video_link;
					if(preg_match('/http:\/\/(www\.)*vimeo\.com\/.*/',$url,$matches)){					
						// do vimeo stuff				   
						preg_match_all('#(http://vimeo.com)/([0-9]+)#i',$url,$output);
						$vimeo_id = $output[2][0];
						$video_type="vimeo";
					}			
				}
				else if($youtubeid)
				{
					$video_type="youtube";
				}					
		}
		elseif ($wpg_meta_cool_select == "image")
		{
			$video_type = '';
			$image_url = $wpg_meta_cool_image;
		}
		
		
				
				if($video_type=="youtube")
				{
					$ele_type = "youtube";				
					$tot_api++;
					$arr_youtube[$tot_api]=	$youtubeid;
					$vids.=	'<li  onclick="return get_the_information(\''.$ele_type.'\',\''.$youtubeid.'\',\''.$post_name.'\');"  >					
					<div class="post_title_class">'.$post_name.'</div>
					<img id="light_box_pop'.$three_count.'" src="'.$this->wpg_get_video_image($video_link).'"  />										
					<span class="zoomImg"></span>
					</li>';				
					$all_three_count++;
				}
				elseif ($video_type=="vimeo")
				{	
					$ele_type = "vimeo";
					$iframeurl = 'http://player.vimeo.com/video/'.$vimeo_id.'?title=1&byline=1&portrait=0&fullscreen=1';
					
					$vids.=	'<li  onclick="return get_the_information(\''.$ele_type.'\',\''.$vimeo_id.'\',\''.$post_name.'\');">					
					<div class="post_title_class">'.$post_name.'</div>
					<img id="light_box_pop'.$three_count.'"  src="'.$this->wpg_get_video_image($video_link).'"  />					
					<span  class="zoomImg"></span>
					</li>';
					$all_three_count++;
				
				}
				elseif ($image_url)
				{
					$ele_type = "image";
					$vids.=	'<li  onclick="return get_the_information(\''.$ele_type.'\',\''.$image_url.'\',\''.$post_name.'\');" >					
					<div class="post_title_class">'.$post_name.'</div>
					<img  id="light_box_pop'.$three_count.'" src="'.$image_url.'" />
					<span class="zoomImg"></span>
					</li>';
					$all_three_count++;
				}
				else
				{
					$ele_type = "no-image";
					$nul_bva = 'noval';
					$image = "";
					/* $vids.=	'<li><img onclick="return get_the_information(\''.$ele_type.'\',\''.$nul_bva.'\');"  src="' . $this->pluginUrl.'/images/no-record.png" ></li>';		*/
				}
				
				
				$three_count++;	
			
			}
						
		
		$vids.=  '</ul></div>';
		
		
		
		/**
		 *  AJAX request to get more video/image
		 */
	
		$vids .= '<script type="text/javascript">		
			function get_the_information(element_type,element_source,post_name)
			{
				jQuery("#Cool_ajax_modal").html("");			
				jQuery.ajax({
				url: "'.admin_url( 'admin-ajax.php' ).'",
				type: "POST",
				data: "action=getVidImage&type="+element_type+"&source="+element_source+"&postname="+post_name,
				success: function(res){	
						
						jQuery("#Cool_ajax_modal").html(res);
						
						jQuery("#Cool_ajax_modal").modal({			
							zIndex:9999,
							onOpen: function (dialog) {
								dialog.overlay.fadeIn("fast", function () {
									dialog.data.hide();
									dialog.container.fadeIn("fast", function () {
										dialog.data.slideDown("fast");										
									});
								});
							},
																						
							onClose: function (dialog) {
								dialog.data.fadeOut("fast", function () {
									dialog.container.hide("fast", function () {
										dialog.overlay.slideUp("fast", function () {
											jQuery.modal.close();
											jQuery("#Cool_ajax_modal").html("");
										});
									});
								});
							}				
						});
				        			
					}
				});
				
			}
		</script>';		
				
		/**
		 * code to run youtube video with iframe
		 */
		$vids.=  '<script type="text/javascript">
		var tag = document.createElement("script");	  
		tag.src = "https://www.youtube.com/iframe_api";
		var firstScriptTag = document.getElementsByTagName("script")[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		</script>';
			
		$vids.=  '<input type="hidden" id="tot_api_count" value="'.$tot_api.'" />';
		$vids.=  '<input type="hidden" id="tot_show_posts" value="0" />';
		$vids.=  '<input type="hidden" id="tot_show_onchnage_id" value="0" />';
		$post_last = "post_last";
	$vids.=  '<script>;		
	jQuery(document).ready(function() {
		
	jQuery("#tot_show_posts").val(0);
							
	jQuery("#wpg_cool_container").append( "<div id=\''.$post_last.'\' ></div>" );
	doMouseWheel = 1 ;   
	
	jQuery(window).scroll(function() {
					
		if (!doMouseWheel)  {			
			return ;
		};
										
		var distanceTop = jQuery("#post_last").offset().top - jQuery(window).height();		
		var tot_count = jQuery("#tot_api_count").val();
		var tot_posts = jQuery("#tot_show_posts").val();
		suc_posts = parseInt(tot_posts)+parseInt(10);
		jQuery("#tot_show_posts").val(suc_posts);		
		
		
		if(jQuery(window).scrollTop() > distanceTop){
			doMouseWheel = 0;
			
			var cat_chanage = jQuery("#tot_show_onchnage_id").val();
			if(cat_chanage!=0 && cat_chanage!="")
			{
				var vcats_id = cat_chanage;				
			}
			else
			{
				var vcats_id = "'.$vcat_id.'";				
			}
									
			jQuery.ajax({
				url: "'.admin_url( 'admin-ajax.php' ).'",
				type: "POST",			
				data: "action=Cool_image_load&tot_api_count="+tot_count+"&tot_show_posts="+tot_posts+"&vcat_id="+vcats_id,
				success: function(html){			
					//alert(html);
					doMouseWheel = 1 ;															
					if(html){

						jQuery(".post_listing").append(html);
											
						var mtexts = "<div id=\''.$post_last.'\' ></div>";
						jQuery("#post_last").remove();
						jQuery("#wpg_cool_container").append( mtexts );											
						jQuery("#tot_api_count").val(tot_count);
						jQuery("#tot_show_posts").val(suc_posts);												
						
					}
					else
					{
						suc_posts = parseInt(suc_posts)-parseInt(10);		
						jQuery("#tot_show_posts").val(suc_posts);
						doMouseWheel = 0;
					}
				}
			});
		  }
		
		});
	});
		
	</script>';			
	
		$vids.=  '<div id="Cool_ajax_modal" style="display:none;"> </div>';	
		
		$cool_cat_drpdown = get_option('cool_cat_drdown');
		
		if($cool_cat_drpdown==1 && $vcat_id=="all" && $all_three_count>0)
		{
			$drpdown = '';
			$args = array(
								'show_option_all'    => '',
								'show_option_none'   => __('---Select Category---'),
								'orderby'            => 'ID', 
								'order'              => 'ASC',
								'show_count'         => 0,
								'hide_empty'         => 1, 
								'child_of'           => 0,
								'exclude'            => '',
								'echo'               => 0,
								'selected'           => '',
								'hierarchical'       => 0, 
								'name'               => 'wpg_coolBycat_view',
								'id'                 => '',
								'class'              => 'wpg_coolPostby_cate_view',
								'depth'              => 0,
								'tab_index'          => 0,
								'taxonomy'           => 'niccoolcategory',
								'hide_if_empty'      => false
							);


								
			$drpdown = '<div class="wpg_cool_all_cat">'.wp_dropdown_categories( $args ).'</div>';						
			$vids .= '<script>									
						 jQuery("#wpg_coolBycat_view").change(function() {
						 	
						 	var cat_val = this.value;
						 	jQuery("#tot_show_posts").val(0);
							var tot_posts = 0;
							var tot_count = 0;
						 				 			
						 	jQuery.ajax({
									url: "'.admin_url( 'admin-ajax.php' ).'",
									type: "POST",			
									data: "action=Cool_image_load&tot_api_count="+tot_count+"&tot_show_posts="+tot_posts+"&vcat_id="+cat_val+"&types=onchangeselect",
									success: function(html){			
																				
										if(html){					
											jQuery(".post_listing").html("");
											jQuery(".post_listing").hide();																																							
											jQuery(".post_listing").html(html);
											jQuery(".post_listing").fadeIn("slow");											
											
											var mtexts = "<div id=\''.$post_last.'\' ></div>";
											jQuery("#post_last").remove();
											jQuery("#wpg_cool_container").append( mtexts );
																						
											jQuery("#tot_show_posts").val(0);
											jQuery("#tot_show_onchnage_id").val(cat_val);
											
											jQuery("html, body").animate({ scrollTop: 0 }, 0);
											doMouseWheel=1;
										}
										else
										{		
											jQuery(".post_listing").html("");
											jQuery("#tot_show_posts").val(0);											
										}
									}
								});
						 			
							});
			
				</script>';
			$vids = $drpdown.$vids;
		}
				
		return $vids;
					
		}		
	}
		
	public function wpg_gallery_custom_init()
	{
		
    	$labels = array(
	    'name' => __("WPG Cool Gallery", "coolgallery"),
	    'singular_name' => __("niccoolgallery", "coolgallery"),
	    'add_new' => __("Add Image", "coolgallery"),
	    'add_new_item' => __("Add New Image", "coolgallery"),
	    'edit_item' => __("Edit Image", "coolgallery"),
	    'new_item' => __("New Image", "coolgallery"),
	    'all_items' => __("All Images", "coolgallery"),
	    'view_item' => __("View Image", "coolgallery"),
	    'search_items' => __("Search Image", "coolgallery"),
	    'not_found' =>  __("No Image found", "coolgallery"),
	    'not_found_in_trash' => __("No Image found in Trash", "coolgallery"), 
	    'parent_item_colon' => '',
	    'menu_name' => __("WP Cool Gallery", "WP Cool Gallery")
	   
	  );
	  	
	  $args = array(
	    'labels' => $labels,
	    'public' => true,
	    'publicly_queryable' => true,
	    'show_ui' => true, 
	    'show_in_menu' => true, 
	    'query_var' => true,
		'show_in_admin_bar' => true,
	    'rewrite' => array( 'slug' => 'coolgallery', 'with_front' => true ),	
	    'capability_type' => 'page',
	    'has_archive' => true, 
	    'hierarchical' => false,
	    'menu_position' => null,
	    'menu_icon' => $this->pluginUrl . "/images/icon.png", // 16px16
	    'supports' => array( 'title','thumbnail')	     
	  );
	  
	 register_post_type( 'niccoolgallery', $args );
	 	  
	 register_taxonomy(
	      'niccoolcategory',
	      'niccoolgallery',
	      array(
	         'label' => __( 'Categories' ),
	         'rewrite' => array( 'slug' => 'niccoolcategory' ),
	         'hierarchical' => true
	      )
	   );	    
	}
	
	public function wpg_photo_gallery_add_admin_JS_CSS()
	{
		wp_enqueue_style('thickbox');		
		wp_enqueue_script('wpg-script', plugins_url( '/js/wpg_script.js' , __FILE__ ));	
		wp_enqueue_script('jquery');
	    wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');		
	}
	
	public function wpg_photo_gallery_front_JS_CSS()
	{
		wp_enqueue_style('modal-popup-css', plugin_dir_url( __FILE__ ) . 'css/basic.css','');
		wp_enqueue_script('jquery');
		wp_enqueue_script('wpg_simple_popup', plugins_url( '/js/wpg_simple_popup.js' , __FILE__ ),array( 'jquery' ));		
	}
	
	public function wpg_photo_gallery_admin_menu()
	{
		add_submenu_page('edit.php?post_type=niccoolgallery'
			,__('All Image','all-image')
			,'Settings'
			,'manage_options'
			,'wpg_photo_gallery_options'
			,array($this,'wpg_photo_gallery_options')
		);
	}
	     
     function wpg_photo_gallery_options() 
	{
		if (!current_user_can('manage_options'))  
		{
			wp_die("You do not have sufficient permissions to access this page.");
		}
						
		echo '<div class="wrap">';
	    echo '<h2 style="color:lightslategrey;">WPG Cool Gallery</h2>';
		echo '<h3 style="font-size:16px;"> Use shortcode <span style="color:blue;"><input readonly onclick="this.select();"  type="text" style="text-align:center;font-size:15px;" size="30" value="[wpg-cool-gallery-front id=all]"></span> in post or page content area.</h3>';
		echo '<h3 style="font-size:16px;"> Use below shortcode to show cool gallery as per Category</h3>';
		echo '</div>';
										
		$taxonomy = 'niccoolcategory';
?>
<ul>
<div>
		<form method="post" action="options.php">
				<?php wp_nonce_field('update-options'); ?>
				<table width="80%">				
				<?php
						$cool_cat_drdown = get_option('cool_cat_drdown');
						
						if($cool_cat_drdown=='')
							$cool_cat_drdown=0;
				?>
						<tr valign="top">
								<th width="40%" ><span style="color:#ff0000;">* </span>Show category dropdown for all </th>
								<td width="60%"><input type="checkbox" id="cool_cats_drd" name="cool_cat_drdown" onclick="return checkuncheckval(this);" <?php if($cool_cat_drdown==1){ echo " checked=checked ";}?> value="<?php echo $cool_cat_drdown;?>" /></td>
						</tr>
						
						<tr valign="top">
								<th width="40%" scope="row">&nbsp;</th>
								<td width="60%">
									<p>
										<input type="submit" class="button button-primary button-large" value="<?php _e('Save') ?>" />
									</p>
								</td>
						</tr>
				</table>
				<input type="hidden" name="action" value="update" />				
				<input type="hidden" name="page_options" value="cool_cat_drdown" />
		</form>		
		
		<table class="widefat post fixed eg-table" style="width:800px !important;">
    	<thead>
        <tr>
        	<th>Category</th>
            <th>Category wise ShortCode</th>            
        </tr>
        </thead> 
        <tbody>        					
		<?php
			$taxonomies = 'niccoolcategory';		
			$categories = get_terms($taxonomies, 'orderby=count&order=DESC&hide_empty=1');		
			
			foreach( $categories as $category )
			{			
				
				$cat_name = $category->name;
				$term_id   = $category->term_id;
				$shortcode = "[wpg-cool-gallery-front id=".$term_id."]";				
		?>
			<tr>
            	<td><?php echo $cat_name;?></td>
                <td><input readonly onclick="this.select();"  type="text" style="text-align:center;font-size:14px;" size="30" value="<?php echo $shortcode;?>"></td>                
            </tr>            
		<?php	
			}
		?>
	  </tbody>
     </table>		
</div>
	<script>
	function checkuncheckval(data){
		
		if(document.getElementById("cool_cats_drd").checked==true){
			document.getElementById("cool_cats_drd").value=1;
		} else{
			document.getElementById("cool_cats_drd").value=0;
		}
	}
	</script>

<?php		
	}
		
}


add_action("init", "register_wpg_gallery_plugin");

function register_wpg_gallery_plugin() {
   global $wpg_cool_gallery,$post;
   $wpg_cool_gallery = new WPG_cool_gallery();
   $wpg_cool_gallery->wpg_gallery_custom_init();
      
}

register_activation_hook(__FILE__, 'wpg_Cool_GalleryInstall');

global $jal_db_version;
$jal_db_version = "1.1";


function wpg_Cool_GalleryInstall() {

  global $wpdb;
  global $jal_db_version;
  add_option("cool_cat_drdown", '', '', 'yes');

}  

$installed_ver = get_option("jal_db_version");
if ($installed_ver != $jal_db_version) {
  wpg_Cool_GalleryInstall();
}

?>