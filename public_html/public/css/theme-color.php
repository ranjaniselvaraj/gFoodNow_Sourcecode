<?php header("Content-type: text/css"); 
	require_once '../settings.php';
	require_once '../application-top.php';

	$themeObj = new Themes();

		if (!empty(Settings::getSetting("CONF_FRONT_THEME")) && (Settings::getSetting("CONF_FRONT_THEME")!="theme-0"))

			$selected_admin_theme=Settings::getSetting("CONF_FRONT_THEME");

			$selected_theme = !empty($_COOKIE['visitor_theme_cookie'])?$_COOKIE['visitor_theme_cookie']:$selected_admin_theme;

		if (!empty($_SESSION['preview_theme']) && isset($_SESSION['preview_theme'])) {

			$selected_theme=$_SESSION['preview_theme'];

		}

		$theme_detail = $themeObj->getThemeById($selected_theme);

		if (!$theme_detail){

			$theme_detail = $themeObj->getThemeById(1);

		}

		

		$theme_css_body = file_get_contents('theme-color.css');

		$replace_arr=array(

					"var(--main-theme-color-1)"=>$theme_detail['theme_primary_color'],

					"var(--main-theme-color-2)"=>$theme_detail['theme_secondary_color'],

					"var(--main-theme-color-3)"=>$theme_detail['theme_product_box_icon_price_color'],

					"var(--main-theme-color-4)"=>$theme_detail['theme_top_nav_text_color'],

					"var(--main-theme-color-5)"=>$theme_detail['theme_secondary_button_text_color'],

					"var(--main-theme-color-6)"=>$theme_detail['theme_top_bar_color'],

					"var(--main-theme-color-7)"=>$theme_detail['theme_left_box_color'],

					"var(--main-theme-color-8)"=>$theme_detail['theme_top_nav_hover_color'],
					
					"var(--main-theme-color-9)"=>$theme_detail['theme_top_bar_text_color'],

					);

		//printArray($theme_detail);

		//die();	

/*		$replace_arr=array(

				"var(--main-theme-color-1)"=>"ff3a58",

				"var(--main-theme-color-2)"=>"79dbf6",

				"var(--main-theme-color-3)"=>"f9dc5c",

				"var(--main-theme-color-4)"=>"f54337",

				"var(--main-theme-color-5)"=>"ea1e63",

				"var(--main-theme-color-6)"=>"673bb7",

				"var(--main-theme-color-7)"=>"dc0028"

				);

		printArray($replace_arr);

		die();		

*/		foreach ($replace_arr as $key => $val) {

			$theme_css_body = str_replace($key, "#".$val, $theme_css_body);

		}

		die($theme_css_body);



?>

