<?PHP

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if($action == ""){
include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
$row = $db->super_query( "SELECT enable as enb, css_incl as ci, cats_list as cl, animation_r as ad, ani_duration as adr FROM " . PREFIX . "_comments_preview WHERE main = 'main'" );
$cat_list = $row['cl'] == '' ? $row['cl'] : explode( ',', $row['cl'] );
$categories_list = CategoryNewsSelection( $cat_list, 0 );
$ani_dir = $row['ad'] != NULL ? intval($row['ad']) : 1;
$adi_duration = $row['adr'] != NULL ? intval($row['adr']) : 4;
$css_incl = $row['ci'];

if($row['enb'] == "off"){
$enable_label = <<<HTML
<div class="form-group">
	<div class="col-md-12"><span class="text-muted text-size-small"> <i class="fa fa-exclamation-triangle position-left"></i>Модуль отключен</span></div>
</div>
HTML;
} else $enable_label = "";


echoheader("Предпросмотр комментариев", "Админпанель модуля предпросмотр комментариев");

echo<<<HTML
<script>
	$( document ).ready(function() {
		$("input[name=ani_direction][value={$ani_dir}]").attr('checked', 'checked');
		$("input[name=ani_duration][value={$adi_duration}]").attr('checked', 'checked');
		$("input[name=css_incl][value={$css_incl}]").attr('checked', 'checked');
	});

	$(function(){
		$('.categoryselect').chosen({no_results_text: 'Ничего не найдено'});

	});
</script>
<style>
.an_dir_radio_block label{
	background: #77777747;
	padding: 5px;
	border: 1px solid #777777;
	transition: all .2s linear;
	color: #3a3a3a;

}
.an_dir_radio_block label:hover {
	border-color: #0095ff;
	background-color: #d1d1d1;
	transition: all .2s linear;
	color: black;
}
.an_dir_radio_block label input{
	vertical-align: sub;
	transition: all .2s linear;
}
</style>

<form action="" method="POST" name="set_settings_cmnts_mod" id="set_settings_cmnts_mod" class="form-horizontal">
<div class="panel panel-default">
<div class="panel-body">
	{$enable_label}
	
	<div class="form-group">
	  <label class="control-label col-sm-2">Свой CSS:</label>
	  <div class="col-sm-10 an_dir_radio_block">
	  	<label>Выкл <input type="radio" name="css_incl" value="off"></label>
	  	<label>Вкл <input type="radio" name="css_incl" value="on"></label>
	  </div>
	</div>
	<div class="form-group">
	  <label class="control-label col-sm-2">Направление анимации:</label>
	  <div class="col-sm-10 an_dir_radio_block">
	  	<label>Вниз <input type="radio" name="ani_direction" value="1"></label>
	  	<label>Вверх <input type="radio" name="ani_direction" value="2"></label>
	  	<label>Затухание <input type="radio" name="ani_direction" value="3"></label>
	  </div>
	</div>
	<div class="form-group">
	  <label class="control-label col-sm-2">Время показа:</label>
	  <div class="col-sm-10 an_dir_radio_block">
	  	<label>2s <input type="radio" name="ani_duration" value="3"></label>
	  	<label>3s <input type="radio" name="ani_duration" value="4"></label>
	  	<label>4s <input type="radio" name="ani_duration" value="5"></label>
	  	<label>5s <input type="radio" name="ani_duration" value="6"></label>
	  	<label>7s <input type="radio" name="ani_duration" value="8"></label>
	  	<label>10s <input type="radio" name="ani_duration" value="11"></label>
	  </div>
	</div>
	<div class="form-group">
	  <label class="control-label col-sm-2">Категории:</label>
	  <div class="col-sm-10">
		<select data-placeholder="Все категории" title="Выбор категорий" name="category[]" id="category" class="categoryselect" style="width:100%;max-width:350px;" multiple>{$categories_list}</select><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="right" data-content="Оставить пустым для всех категорий" ></i>
	  </div>
	</div>
<input type="hidden" name="action" value="save_sets">
</div>
<div class="panel-footer">
	<input type="submit" class="btn bg-teal btn-sm btn-raised" value="Сохранить">
</div>
</div>
</form>
HTML;
echofooter();

} else if($action == "save_sets"){
$arr_cats = [];
$arr_cats = $_POST['category'];

if(is_array($arr_cats)) {
	if(count($arr_cats) > 0) {
		$cats_value = '';
		foreach ($arr_cats as $key => $value) {
			$cats_value.=$value.',';
		}

		$cats_value = rtrim($cats_value,',');
		$db->query('UPDATE '.PREFIX.'_comments_preview SET cats_list="'.$cats_value.'" WHERE main="main"');
	} else {
		$db->query('UPDATE '.PREFIX.'_comments_preview SET cats_list="" WHERE main="main"');
	}
} else {
	$db->query('UPDATE '.PREFIX.'_comments_preview SET cats_list="" WHERE main="main"');
}

$db->query('UPDATE '.PREFIX.'_comments_preview SET css_incl="'.$_POST['css_incl'].'", animation_r="'.$_POST['ani_direction'].'", ani_duration="'.$_POST['ani_duration'].'" WHERE main="main"');

msg( "success", 
	"Предпросмотр комментариев",
	 "Настройки модуля успешно сохранены.",
	  array('?mod=a_mod_preview' => "Вернуться назад" ) );

}
?>
