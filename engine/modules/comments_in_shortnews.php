<?PHP
if(!defined('DATALIFEENGINE')) die('Go away!');

global $row;
$news_id = $row['id'];

$module_settings = $db->super_query('SELECT * FROM '.PREFIX.'_comments_preview WHERE main="main"');
if($module_settings['enable'] == "on") {
$settings_cats = explode(',', $module_settings['cats_list']);

$rw = $db->super_query('SELECT category FROM '.PREFIX.'_post WHERE id='.$news_id);
$post_cats_arr = explode(',', $rw['category']);

$result = array_intersect($post_cats_arr, $settings_cats);

if(count($result) > 0 || $result != NULL || $module_settings['cats_list'] == "") {
global $row;
if(!$row['comm_num']) return '';
if(file_exists(TEMPLATE_DIR.'/modules/comments_in_shortnews.tpl')) {
$comments_count = $db->super_query("SELECT COUNT(id) as cnt FROM ".PREFIX."_comments WHERE post_id={$news_id}");
if($comments_count['cnt'] < 2) return;

if($module_settings['animation_r'] == 1) {
	$ani_to = "100%";
	$ani_from = "-100%";
} else if($module_settings['animation_r'] == 2) {
	$ani_to = "-100%";
	$ani_from = "100%";
} else if($module_settings['animation_r'] == 3) {
	$ani_fade = "fade";
	$module_settings['ani_duration'] = $module_settings['ani_duration'] - 1;
} else {
	$ani_to = "100%";
	$ani_from = "-100%";
}

$ani_d = "".$module_settings['ani_duration'].rand(100, 999)."";

$db->query("SELECT c.id,c.autor,c.text,c.date,u.foto FROM ".PREFIX."_comments c LEFT JOIN ".USERPREFIX."_users u ON c.user_id=u.user_id WHERE c.post_id={$news_id} ORDER BY c.date DESC");
while ( $row = $db->get_array() ) {
	if(!isset($tpl)) {
		$tpl = new dle_template();
		$tpl->dir = TEMPLATE_DIR;
	} else {
		$tpl->result['module_result'] = '';
	}
	
	$tpl->load_template('modules/comments_in_shortnews.tpl');

	if(strrpos($row['text'], '<!--dle_media_end--><br>') == true && strrpos($row['text'], '<!--dle_media_end--><br>') > 0){
		$row['text'] = trim(strstr($row['text'], '<!--dle_media_begin', true).substr($row['text'], strrpos($row['text'], '<!--dle_media_end--><br>')+24));
	}
	if(strrpos($row['text'], '<!--dle_uppod_end-->') == true && strrpos($row['text'], '<!--dle_uppod_end-->') > 0){
		$row['text'] = trim(strstr($row['text'], '<!--dle_uppod_begin', true).substr($row['text'], strrpos($row['text'], '<!--dle_uppod_end-->')+20));
	}
	if(strrpos($row['text'], '<!--QuoteEEnd--><br>') == true && strrpos($row['text'], '<!--QuoteEEnd--><br>') > 0){
		$row['text'] = trim(strstr($row['text'], '<!--QuoteBegin', true).substr($row['text'], strrpos($row['text'], '<!--QuoteEEnd--><br>')+20));
	}
	$row['text']=str_replace('<br>', ' ', $row['text']);
	$row['text']=strip_tags($row['text']);

	$row['text']=strlen($row['text'])>80?substr($row['text'], 0, 80):$row['text'];

	if ( count(explode("@", $row['foto'])) == 2 ) {
		$gravatar = $row['foto'];	
		$foto = 'https://www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);
	} else {
		if( $row['foto'] ) {
			if (strpos($row['foto'], "//") === 0) $avatar = "http:".$row['foto']; else $avatar = $row['foto'];
			$avatar = @parse_url ( $avatar );
			if( $avatar['host'] ) {	
				$foto = $row['foto'];	
			} else $foto = $config['http_home_url'] . "uploads/fotos/" . $row['foto'];
		} else $foto = "{THEME}/dleimages/noavatar.png";
	}
	
	$name = stripslashes($row['autor']);
	$text = stripslashes($row['text']);
	$date = stripslashes($row['date']);
	$tpl->set('{avatar}', $foto);
	$tpl->set('{name}', $name);
	$tpl->set('{text}', $text);
	$tpl->set('{cdate}', $date);
	$tpl->set('{nid}', 'lnid'.$news_id);
	$tpl->compile('module_result');
	$module_result .= $tpl->result['module_result'];

	$tpl->clear();
}
}

$styles = <<<HTML
<style>
.ul_sc {
  list-style: none;
  width: 100%;
  height: 100px;
  margin: 0;
  padding: 0;
  overflow: hidden;
  position: relative;
  background: white;
}

.ul_sc li {
  width: 80%;
  left: 10%;
  height: 100%;
  background: rgb(233, 234, 235);
  position: absolute;
  margin: 0;
}

.ul_sc li img {
  height: 80px;
  width: 80px;
  padding: 10px;
  display: inline-block;
  float: left;
}

.c_avatar_div {
  width: 100px;
  height: 100%;
  float: left;
  margin: 0;
}

.c_comment_div {
  height: calc(100% - 20px);
  width: calc(100% - 120px);
  float: right;
  margin: 0;
  padding: 10px;
}

.c_comment_text {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    color: #838688;
}
</style>
HTML;

$script = <<<HTML
<script>
$( document ).ready(function() {
$("#ul$news_id > li.lnid$news_id:gt(0)").hide();

var ani_way = "{$ani_fade}";
if(ani_way == "fade"){

setInterval(function() { 
  $('#ul$news_id > li.lnid$news_id:first')
    .fadeOut("slow")
    .next()
    .fadeIn("slow")
    .end()
    .appendTo('#ul$news_id');
},  {$ani_d});

} else {

setInterval(function() { 
  $('#ul$news_id > li.lnid$news_id:first')
    .animate({bottom: "{$ani_from}"}).fadeOut("fast")
    .next()
    .animate({bottom: "{$ani_to}"}).fadeIn("fast").animate({bottom: '0px'})
    .end()
    .appendTo('#ul$news_id').animate({bottom: "{$ani_from}"});
},  {$ani_d});
}

});
</script>
HTML;
if($module_settings['css_incl'] == 'off') {
	$script.=$styles;
}

echo '<ul id="ul'.$news_id.'" class="ul_sc">'.$module_result.'</ul>'.$script;
}}
?>
