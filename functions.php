<?php


//グロナビ用（引数に与えられた文字列がURLに含まれる場合on、入らない場合はoffを返す）
function checkCurrentURL($txt) {
	$url = $_SERVER["REQUEST_URI"];
	if(strpos($url,$txt . '/') != 0) return 'on';
	else return 'off';
}


//iframe
// フィルタの登録
add_filter('content_save_pre','test_save_pre');

function test_save_pre($content){
    global $allowedposttags;

    // iframeとiframeで使える属性を指定する
    $allowedposttags['iframe'] = array('class' => array () , 'src'=>array() , 'width'=>array(),
    'height'=>array() , 'frameborder' => array() , 'scrolling'=>array(),'marginheight'=>array(),
    'marginwidth'=>array());

    return $content;
}


add_action('admin_print_styles', 'admin_css_custom');
function admin_css_custom() {
   echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/style-admin.css" type="text/css" />';
   $tmp = <<<EOT
   <script>
	window.onload = function() {
		var _name = document.getElementsByClassName('display-name')[0].innerText;
		document.body.className += ' ' + _name;
	}
	</script>
EOT;
echo $tmp;
}


//祝日を取得
$holidays = array();
$file_array = file(dirname(__FILE__) . '/holiday.log');
for($i=0;$i<count($file_array);$i++){
	if($file_array[$i]){
		$file_array[$i] = str_replace(array("\r\n","\r","\n"),'',$file_array[$i]);
		$val = explode(',',$file_array[$i]);
		$holidays["$val[1]"] = $val[0];
	}
}

function getSyuku($day) {
	global $holidays;
	if (array_key_exists($day, $holidays)) {
		return '・祝';
	}
}


//日付から曜日を取得
function getYoubi($day) {
	$datetime = new DateTime($day);
	$week = array("日", "月", "火", "水", "木", "金", "土");
	$w = (int)$datetime->format('w');
	return $week[$w];
}

//今日より後かどうか
function chkToday($day) {
	$dt = new DateTime();
	$dt->setTimeZone(new DateTimeZone('Asia/Tokyo'));
	$today = $dt->format('Y-m-d');

	// 比較する日付を設定
	$target_day = $day;

	// 日付を比較
	if (strtotime($target_day) >= strtotime($today)) {
	    return true;
	}
	else {
	    return false;
	}
}

/**
 * 固定ページ名/index.htmlでもアクセスできるようにします。
 * (※設定 < パーマリンク設定で「変更を保存」することで有効になります)
 */
function custom_init() {
	global $wp_rewrite;
	$wp_rewrite->add_permastruct( 'page_index', '/%pagename%/index.html', false );
}
add_action( 'init', 'custom_init' );

/**
 * /%pagename%/index.html でアクセスされた時、末尾にスラッシュがつかないようにします。
 */
function no_index_html_slash( $string, $type ) {
	// ※実際には末尾がindex.html/の場合に末尾のスラッシュを取り除きます。
	if ( preg_match( '#/index.html/$#', $string ) ) {
		return untrailingslashit( $string );
	}
	return $string;
}
add_filter( 'user_trailingslashit', 'no_index_html_slash', 66, 2 );


//固定ページの拡張子を.htmlに
add_action( 'init', 'mytheme_init' );
if ( ! function_exists( 'mytheme_init' ) ) {
	function mytheme_init() {
		global $wp_rewrite;
		$wp_rewrite->use_trailing_slashes = false;
		$wp_rewrite->page_structure = $wp_rewrite->root . '%pagename%.html';
		flush_rewrite_rules( false );
	}
}


//bodyClassにページスラッグ表示
function pagename_class($classes = '') {
	if (is_page()) {
		$page = get_page(get_the_ID());
		$parent_slug = get_page_uri($page->post_parent);
		if ($parent_slug == 'form') $classes[] = 'form';
		else if ($parent_slug == 'news') $classes[] = 'news';
		else if ($parent_slug == 'sitemap') $classes[] = 'sitemap';
		else if ($parent_slug != '' && $page->post_name != 'index') {
			$classes[] = $parent_slug . '-' . $page->post_name;
		}
		else $classes[] = $parent_slug;
	}
	if (is_front_page()) $classes[] = 'home';
	if(is_page('company') || is_page('studio') || is_page('instructors') || is_page('contact') || is_page('biz') || is_page('news') || is_page('info') || is_page('audition') || is_page('classes') || is_page('shopping') || is_page('career') || is_page('biz') || is_page('fees') || is_page('english') || is_page('privacy') || is_search()) $classes[] = 'index';
	if (!is_front_page()) $classes[] = 'sub';
	return $classes;
}
add_filter('body_class','pagename_class');


//カスタム投稿の追加
add_action('init', 'my_custom_init');
function my_custom_init()
{

	//ニュース
	$labels = array(
		'name' => _x('ニュース', 'post type general name'),
		'singular_name' => _x('ニュース一覧', 'post type singular name'),
		'add_new' => _x('新しくニュースを追加する', 'news'),
		'add_new_item' => __('ニュース新規追加'),
		'edit_item' => __('ニュースを編集'),
		'new_item' => __('新しいニュース'),
		'view_item' => __('ニュース一覧を見る'),
		'search_items' => __('ニュースを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'news')
	);
	register_post_type('news',$args);
	//カテゴリータイプ
	$args = array(
	'label' => '種類',
	'public' => true,
	'show_ui' => true,
	'hierarchical' => true
	);
	register_taxonomy('news_cat','news',$args);

	//トレーニングコース
	$labels = array(
		'name' => _x('トレーニングコース', 'post type general name'),
		'singular_name' => _x('トレーニングコース一覧', 'post type singular name'),
		'add_new' => _x('新しくトレーニングコースを追加する', 'training'),
		'add_new_item' => __('トレーニングコース新規追加'),
		'edit_item' => __('トレーニングコースを編集'),
		'new_item' => __('新しいトレーニングコース'),
		'view_item' => __('トレーニングコース一覧を見る'),
		'search_items' => __('トレーニングコースを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true
	);
	register_post_type('training',$args);


	//カテゴリータイプ
	$args = array(
	'label' => '種類',
	'public' => true,
	'show_ui' => true,
	'hierarchical' => true
	);
	register_taxonomy('basic_type',array('basic_training_class', 'school_info_session'),$args);

	//オープン講座スケジュール
	$labels = array(
		'name' => _x('オープン講座スケジュール', 'post type general name'),
		'singular_name' => _x('オープン講座スケジュール一覧', 'post type singular name'),
		'add_new' => _x('新しくオープン講座スケジュールを追加する', 'open_class_schedule'),
		'add_new_item' => __('オープン講座スケジュール新規追加'),
		'edit_item' => __('オープン講座スケジュールを編集'),
		'new_item' => __('新しいオープン講座スケジュール'),
		'view_item' => __('オープン講座スケジュール一覧を見る'),
		'search_items' => __('オープン講座スケジュールを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true
	);
	register_post_type('open_class_schedule',$args);

	//オープン講座
	$labels = array(
		'name' => _x('オープン講座', 'post type general name'),
		'singular_name' => _x('オープン講座一覧', 'post type singular name'),
		'add_new' => _x('新しくオープン講座を追加する', 'open_class'),
		'add_new_item' => __('オープン講座新規追加'),
		'edit_item' => __('オープン講座を編集'),
		'new_item' => __('新しいオープン講座'),
		'view_item' => __('オープン講座一覧を見る'),
		'search_items' => __('オープン講座を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'classes/openclass')
	);
	register_post_type('open_class',$args);



	//ベーシックトレーニングコース
	$labels = array(
		'name' => _x('ベーシックトレーニングコース', 'post type general name'),
		'singular_name' => _x('ベーシックトレーニングコース一覧', 'post type singular name'),
		'add_new' => _x('新しくベーシックトレーニングコースを追加する', 'basic_training_class'),
		'add_new_item' => __('ベーシックトレーニングコース新規追加'),
		'edit_item' => __('ベーシックトレーニングコースを編集'),
		'new_item' => __('新しいベーシックトレーニングコース'),
		'view_item' => __('ベーシックトレーニングコース一覧を見る'),
		'search_items' => __('ベーシックトレーニングコースを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true
	);
	register_post_type('basic_training_class',$args);
	//カテゴリータイプ
	$args = array(
	'label' => '種類',
	'public' => true,
	'show_ui' => true,
	'hierarchical' => true
	);
	register_taxonomy('basic_type',array('basic_training_class', 'school_info_session'),$args);


	//コース説明会
	$labels = array(
		'name' => _x('コース説明会', 'post type general name'),
		'singular_name' => _x('コース説明会一覧', 'post type singular name'),
		'add_new' => _x('新しくコース説明会を追加する', 'school_info_session'),
		'add_new_item' => __('コース説明会新規追加'),
		'edit_item' => __('コース説明会を編集'),
		'new_item' => __('新しいコース説明会'),
		'view_item' => __('コース説明会一覧を見る'),
		'search_items' => __('コース説明会を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true
	);
	register_post_type('school_info_session',$args);


	//レギュラークラス
	$labels = array(
		'name' => _x('レギュラークラス', 'post type general name'),
		'singular_name' => _x('レギュラークラス一覧', 'post type singular name'),
		'add_new' => _x('新しくレギュラークラスを追加する', 'regular_class'),
		'add_new_item' => __('レギュラークラス新規追加'),
		'edit_item' => __('レギュラークラスを編集'),
		'new_item' => __('新しいレギュラークラス'),
		'view_item' => __('レギュラークラス一覧を見る'),
		'search_items' => __('レギュラークラスを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true
	);
	register_post_type('regular_class',$args);


	//ヨギーサークルインストラクターの声
	$labels = array(
		'name' => _x('サークルインストラクターの声', 'post type general name'),
		'singular_name' => _x('サークルインストラクターの声一覧', 'post type singular name'),
		'add_new' => _x('新しくサークルインストラクターの声を追加する', 'circle_interview'),
		'add_new_item' => __('サークルインストラクターの声新規追加'),
		'edit_item' => __('サークルインストラクターの声を編集'),
		'new_item' => __('新しいサークルインストラクターの声'),
		'view_item' => __('サークルインストラクターの声一覧を見る'),
		'search_items' => __('サークルインストラクターの声を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'circle/interview')
	);
	register_post_type('circle_interview',$args);

	//ヨギーサークル開催情報
	$labels = array(
		'name' => _x('サークル開催情報', 'post type general name'),
		'singular_name' => _x('サークル開催情報一覧', 'post type singular name'),
		'add_new' => _x('新しくサークル開催情報を追加する', 'circle'),
		'add_new_item' => __('サークル開催情報新規追加'),
		'edit_item' => __('サークル開催情報を編集'),
		'new_item' => __('新しいサークル開催情報'),
		'view_item' => __('サークル開催情報一覧を見る'),
		'search_items' => __('サークル開催情報を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'classes/circle')
	);
	register_post_type('circle',$args);


	//スペシャルクラス
	$labels = array(
		'name' => _x('スペシャルクラス', 'post type general name'),
		'singular_name' => _x('スペシャルクラス一覧', 'post type singular name'),
		'add_new' => _x('新しくスペシャルクラスを追加する', 'sp_class'),
		'add_new_item' => __('スペシャルクラス新規追加'),
		'edit_item' => __('スペシャルクラスを編集'),
		'new_item' => __('新しいスペシャルクラス'),
		'view_item' => __('スペシャルクラス一覧を見る'),
		'search_items' => __('スペシャルクラスを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'classes/sp')
	);
	register_post_type('sp_class',$args);
	//カテゴリータイプ
	$args = array(
	'label' => 'カテゴリ',
	'public' => true,
	'show_ui' => true,
	'hierarchical' => true
	);
	register_taxonomy('sp_type',array('sp_class', 'open_class', 'circle'),$args);


	//スタジオ地図ページ
	$labels = array(
		'name' => _x('スタジオ地図', 'post type general name'),
		'singular_name' => _x('スタジオ地図一覧', 'post type singular name'),
		'add_new' => _x('新しくスタジオ地図を追加する', 'studio_map'),
		'add_new_item' => __('スタジオ地図新規追加'),
		'edit_item' => __('スタジオ地図を編集'),
		'new_item' => __('新しいスタジオ地図'),
		'view_item' => __('スタジオ地図を見る'),
		'search_items' => __('スタジオ地図を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'studio/map')
	);
	register_post_type('studio_map',$args);

	//スタジオスケジュールページ
	$labels = array(
		'name' => _x('スタジオスケジュール', 'post type general name'),
		'singular_name' => _x('スタジオスケジュール一覧', 'post type singular name'),
		'add_new' => _x('新しくスタジオスケジュールを追加する', 'studio_schedule'),
		'add_new_item' => __('スタジオスケジュール新規追加'),
		'edit_item' => __('スタジオスケジュールを編集'),
		'new_item' => __('新しいスタジオスケジュール'),
		'view_item' => __('スタジオスケジュールを見る'),
		'search_items' => __('スタジオスケジュールを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'studio/schedule')
	);
	register_post_type('studio_schedule',$args);


	//ママクラススケジュール
	$labels = array(
		'name' => _x('ママクラススケジュール', 'post type general name'),
		'singular_name' => _x('ママクラススケジュール一覧', 'post type singular name'),
		'add_new' => _x('新しくママクラススケジュールを追加する', 'maternity_schedule'),
		'add_new_item' => __('ママクラススケジュール新規追加'),
		'edit_item' => __('ママクラススケジュールを編集'),
		'new_item' => __('新しいママクラススケジュール'),
		'view_item' => __('ママクラススケジュール一覧を見る'),
		'search_items' => __('ママクラススケジュールを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'maternity_schedule')
	);
	register_post_type('maternity_schedule',$args);


	//インストラクター紹介
	$labels = array(
		'name' => _x('インストラクター', 'post type general name'),
		'singular_name' => _x('インストラクター一覧', 'post type singular name'),
		'add_new' => _x('新しくインストラクターを追加する', 'instrocutors'),
		'add_new_item' => __('インストラクター新規追加'),
		'edit_item' => __('インストラクターを編集'),
		'new_item' => __('新しいインストラクター'),
		'view_item' => __('インストラクターを見る'),
		'search_items' => __('インストラクターを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'instructors')
	);
	register_post_type('instructors',$args);

	//カテゴリータイプ
	$args = array(
	'label' => '肩書',
	'public' => true,
	'show_ui' => true,
	'hierarchical' => true
	);
	register_taxonomy('instructors_job','instructors',$args);
	//カテゴリータイプ
	$args = array(
	'label' => '地域',
	'public' => true,
	'show_ui' => true,
	'hierarchical' => true
	);
	register_taxonomy('instructors_area','instructors',$args);



	//ニュースリリース
	$labels = array(
		'name' => _x('ニュースリリース', 'post type general name'),
		'singular_name' => _x('ニュースリリース一覧', 'post type singular name'),
		'add_new' => _x('新しくニュースリリースを追加する', 'release'),
		'add_new_item' => __('ニュースリリース新規追加'),
		'edit_item' => __('ニュースリリースを編集'),
		'new_item' => __('新しいニュースリリース'),
		'view_item' => __('ニュースリリース一覧を見る'),
		'search_items' => __('ニュースリリースを探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'company/release')
	);
	register_post_type('release',$args);


	//スタッフ採用
	$labels = array(
		'name' => _x('スタッフ採用', 'post type general name'),
		'singular_name' => _x('スタッフ採用一覧', 'post type singular name'),
		'add_new' => _x('新しくスタッフ採用を追加する', 'recruit'),
		'add_new_item' => __('スタッフ採用新規追加'),
		'edit_item' => __('スタッフ採用を編集'),
		'new_item' => __('新しいスタッフ採用'),
		'view_item' => __('スタッフ採用一覧を見る'),
		'search_items' => __('スタッフ採用を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'company/recruit')
	);
	register_post_type('recruit',$args);


	//オーディション合格者の声
	$labels = array(
		'name' => _x('オーディション合格者の声', 'post type general name'),
		'singular_name' => _x('オーディション合格者の声一覧', 'post type singular name'),
		'add_new' => _x('新しくオーディション合格者の声を追加する', 'auditionvoice'),
		'add_new_item' => __('オーディション合格者の声新規追加'),
		'edit_item' => __('オーディション合格者の声を編集'),
		'new_item' => __('新しいオーディション合格者の声'),
		'view_item' => __('オーディション合格者の声一覧を見る'),
		'search_items' => __('オーディション合格者の声を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'audition/voice')
	);
	register_post_type('auditionvoice',$args);


	//オーディション情報
	$labels = array(
		'name' => _x('オーディション情報', 'post type general name'),
		'singular_name' => _x('オーディション情報一覧', 'post type singular name'),
		'add_new' => _x('新しくオーディション情報を追加する', 'audition'),
		'add_new_item' => __('オーディション情報新規追加'),
		'edit_item' => __('オーディション情報を編集'),
		'new_item' => __('新しいオーディション情報'),
		'view_item' => __('オーディション情報一覧を見る'),
		'search_items' => __('オーディション情報を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'audition')
	);
	register_post_type('audition',$args);


	//各スタジオブログ更新
	$labels = array(
		'name' => _x('スタジオ・ヨギーブログ', 'post type general name'),
		'singular_name' => _x('スタジオ・ヨギーブログ一覧', 'post type singular name'),
		'add_new' => _x('新しく記事を投稿する', 'audition'),
		'add_new_item' => __('新しく記事追加'),
		'edit_item' => __('記事を編集'),
		'new_item' => __('新しい記事'),
		'view_item' => __('記事一覧を見る'),
		'search_items' => __('記事を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array('slug' => 'yoggyblog')
	);
	register_post_type('yoggyblog',$args);
	//カテゴリータイプ
	$args = array(
	'label' => '投稿スタジオ',
	'public' => true,
	'show_ui' => true,
	'has_archive' => true,
	'hierarchical' => true
	);
	register_taxonomy('area','yoggyblog',$args);	


	//メディア掲載情報
	$labels = array(
		'name' => _x('メディア掲載情報', 'post type general name'),
		'singular_name' => _x('メディア掲載情報一覧', 'post type singular name'),
		'add_new' => _x('新しくメディア掲載情報を追加する', 'media'),
		'add_new_item' => __('メディア掲載情報新規追加'),
		'edit_item' => __('メディア掲載情報を編集'),
		'new_item' => __('新しいメディア掲載情報'),
		'view_item' => __('メディア掲載情報一覧を見る'),
		'search_items' => __('メディア掲載情報を探す'),
		'not_found' => __('記事はありません'),
		'not_found_in_trash' => __('ゴミ箱に記事はありません'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'menu_position' => 5,
		'public' => true,
		'supports' => array('title', 'editor'),
		'capability_type' => 'post',
		'hierarchical' => true,
	);
	register_post_type('media',$args);

	flush_rewrite_rules(false);
}


//管理画面の順番変更
function custom_menu_order($menu_ord) {
	if (!$menu_ord) return true;
	return array(
		'index.php', // ダッシュボード
		'separator1', // 隙間
		'edit.php?post_type=news',
		'edit.php?post_type=training',
		'edit.php?post_type=open_class',
		'edit.php?post_type=open_class_schedule',
		'edit.php?post_type=school_info_session',
		'edit.php?post_type=basic_training_class',
		'edit.php?post_type=regular_class',
		'edit.php?post_type=sp_class',
		'edit.php?post_type=circle',
		'edit.php?post_type=circle_interview',
		'edit.php?post_type=studio_map',
		'edit.php?post_type=studio_schedule',
		'edit.php?post_type=maternity_schedule',
		'edit.php?post_type=instructors',
		'edit.php?post_type=release',
		'edit.php?post_type=recruit',
		'edit.php?post_type=audition',
		'edit.php?post_type=auditionvoice',
		'edit.php?post_type=media',
		'separator2', // 隙間
		'edit.php?post_type=page', // 固定ページ
		'options-general.php', // 設定
		'separator3', // 隙間
		'upload.php', // メディア
		'themes.php', // 外観
		'users.php', // ユーザー
		'plugins.php', // プラグイン
		'separator-last', // 隙間
		'edit.php', // 投稿
	);
}
add_filter('custom_menu_order', 'custom_menu_order');
add_filter('menu_order', 'custom_menu_order');


//投稿の名称を変更
function change_post_menu_label() {
	global $menu;
	global $submenu;
	$menu[5][0] = '外部ブログ';
	$submenu['edit.php'][5][0] = '外部ブログ一覧';
	//echo ”;
}
function change_post_object_label() {
	global $wp_post_types;
	$labels = &$wp_post_types['post']->labels;
	$labels->name = 'ニュース';
	$labels->singular_name = 'ニュース';
	$labels->add_new = _x('新規作成', 'ニュース');
	$labels->add_new_item = 'ニュース新規追加';
	$labels->edit_item = 'ニュースの編集';
	$labels->new_item = '新しいニュース';
	$labels->view_item = 'ニュースを表示';
	$labels->search_items = 'ニュース検索';
	$labels->not_found = 'ニュースが見つかりませんでした';
	$labels->not_found_in_trash = 'ゴミ箱に見つかりませんでした';
}
add_action( 'init', 'change_post_object_label' );
add_action( 'admin_menu', 'change_post_menu_label' );



// 検索結果から固定ページを除外
// function search_filter($query) {
//   if (!$query -> is_admin && $query -> is_search) {
//     //$query -> set('post_type', array('news', 'basic_training_class', 'circle', 'sp_class', 'openclass', 'audition', 'recruit', 'training'));
//   }
//   return $query;
// }
// add_filter('pre_get_posts', 'search_filter');


?>