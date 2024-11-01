<?php
/*
Plugin Name: WPMU Theme Select
Plugin URI: http://www.online-solution.biz/
Description: Let the wpmu users select a theme when registering
Version: 0.1
Author: Online Solution
Plugin URI: http://www.online-solution.biz
Author URI: http://de.online-solution.biz/
Min WP Version: 1.5
Max WP Version: 2.8
*/
add_action("signup_blogform","wpmu_themes_overview",1);
add_action("wpmu_new_blog","wpmu_onactivate",10,1);
add_filter("add_signup_meta","wpmu_save_theme",500);
add_filter("bp_signup_usermeta","wpmu_save_theme",500);//buddypress in use?
function wpmu_themes_overview()
{
	$all_themes=get_themes();
 	$permitted=get_site_option("allowedthemes");
	$permitted=maybe_unserialize($permitted);
	$allowed_themes=array();
	$themes=array();
	if(is_array($all_themes))
	foreach($all_themes as $theme)
	{
		if(array_key_exists($theme['Stylesheet'],$permitted))
			$themes[]=$theme;
	}
	$n_per_line=3;
	$n_rows=3;
	echo "<div class='theme-lists'>";
	echo "<label>Theme</label><p>Choose the theme for your blog:</p><div id=\"view-themes-navi\">&nbsp;</div><div id=\"view-themes-n\">0</div></div><br style='clear:both;' />";
	$z=0;
	echo '<script><!--
	var arr_view_themes=Array(';
	foreach($themes as $theme)
	{
		$z++;
		$chk='0';//set to 1 for default selection/theme
		$src_view_themes_n.='<div class="theme-info" style="float:left;margin-left:10px;width:250px;"><p><input style="margin:0;padding:0;width:25px;" type="radio" value="'.$theme['Name'].'" id="template-'.$theme['Stylesheet'].'" name="seltheme" checked="'.$chk.'"  />&nbsp;'.str_replace("'",'',$theme['Title']).'</span> <img style="margin-left:40px;display:block;" width="185" height="126" alt="" src="'.WP_CONTENT_URL.$theme['Template Dir']."/".$theme['Screenshot'].'" /></p></div>';
		if(($z%$n_per_line)==0)
			$src_view_themes_n.="<br style=\"clear:both;\" />";
		if((($z%($n_rows*$n_per_line))==0 AND $z!=0) OR $z==COUNT($themes))
		{
			if($z>($n_rows*$n_per_line))
				echo ',';
			echo '\''.$src_view_themes_n.'\'';
			$src_view_themes_n='';
		}
	}
	echo ');
	--></script>';
	?>
	<script><!--
	var i_view_themes=0;
	view_themes_init(0);
	function view_themes_init(i)
	{
		src_navi='';
		if(i!=0)
			src_navi+='<input type="button" value="&lt;" onclick="view_themes_init(i_view_themes-1);" id="view-themes-previous" />';
		if(i+1<arr_view_themes.length)
			src_navi+='<input type="button" value="&gt;" onclick="view_themes_init(i_view_themes+1);" id="view-themes-next" />';
		document.getElementById("view-themes-n").innerHTML=arr_view_themes[i];
		document.getElementById("view-themes-navi").innerHTML=src_navi;
		i_view_themes=i;
	}
	--></script>
	<?php
}
function wpmu_onactivate($blog)
{
	//direct activation
	$theme=@$_REQUEST['seltheme'];
	if(empty($theme))//usermeta available
	{
		GLOBAL $usermeta;
		$theme=$usermeta['seltheme'];
	}
	if(empty($theme))//meta available
	{
		GLOBAL $meta;
		$theme=$meta['seltheme'];
	}
	if(empty($theme))//email confirmation
	{
		GLOBAL $signup;
		$meta = unserialize($signup->meta);
		$theme=$meta->seltheme;
	}
	if(empty($theme))//email confirmation2
	{
		GLOBAL $wpdb,$key;
		$signup = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->signups WHERE activation_key = %s", $key) );
		$meta = unserialize($signup->meta);
		$theme=$meta['seltheme'];
	}
	$theme=get_theme($theme);
	if(is_array(@$theme))
	{
		update_blog_option($blog,'template',$theme['Template']);
		update_blog_option($blog,'stylesheet',$theme['Stylesheet']);
	}
	else
		die("theme info not found");
}
function wpmu_save_theme($meta)
{
	if(@$_REQUEST["seltheme"])
		$meta["seltheme"]=$_REQUEST["seltheme"];
	return $meta;
}
?>