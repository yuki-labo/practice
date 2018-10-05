<?php
include_once(dirname(__FILE__)."/common/config.ini.php");
include_once(dirname(__FILE__)."/common/common.php");
include_once(dirname(__FILE__)."/common/Validate.php");
include_once(dirname(__FILE__)."/FX/FX.php");

// 出願数がなし、またはログイン状態の場合はトップへ
if(empty($_SESSION['nums']) || !empty($_SESSION["mail"])) {
	header("Location: top.php");
	exit;
}

// 初期化
$post = array();

$error = array();

// 確認ページから戻ったときの処理
if(!empty($_SESSION['info'])) {
	$post = $_SESSION['info'];
}


// 確認ボタンが押された際の処理
if(!empty($_POST)) {
	$post = $_POST;
	//2016.08.22　服部　郵便番号、電話番号を結合
	$post['tel'] = $post['tel_01'].'-'.$post['tel_02'].'-'.$post['tel_03'];
	$post['add_zip_code'] = $post['add_zip_code_01'].'-'.$post['add_zip_code_02'];
	if ( empty($post['grd_add_zip_code_01']) && empty($post['grd_add_zip_code_02']) ){ $post['grd_add_zip_code'] = ''; }else{ $post['grd_add_zip_code'] = $post['grd_add_zip_code_01'].'-'.$post['grd_add_zip_code_02']; }
	$post['old_sch_tel'] = $post['old_sch_tel_01'].'-'.$post['old_sch_tel_02'].'-'.$post['old_sch_tel_03'];

	// 必須項目確認処理
	if(empty($post['name_sei'])) $error['name_sei'] = '「氏名(姓)」を入力してください。';
	if(empty($post['name_mei'])) $error['name_mei'] = '「氏名(名)」を入力してください。';
	if(empty($post['name_sei_read'])) $error['name_sei_read'] = '「氏名ふりがな(せい)」を入力してください。';
	if(empty($post['name_mei_read'])) $error['name_mei_read'] = '「氏名ふりがな(めい)」を入力してください。';
	if(empty($post['date_of_birth'])) $error['date_of_birth'] = '「生年月日」を入力してください。';

	//2016.12.13　服部　氏名フリガナの全角ひらがなチェックを追加
	//▽▽▽
	if ( !isset($error['name_sei_read']) ){
		if ( preg_match("/^[ぁ-ん]+$/u", $post['name_sei_read']) ) {
		}else{
			$error['name_sei_read'] = '「氏名ふりがな(せい)」は全角ひらがなで入力してください。';
		}
	}
	if ( !isset($error['name_mei_read']) ){
		if ( preg_match("/^[ぁ-ん]+$/u", $post['name_mei_read']) ) {
		}else{
			$error['name_mei_read'] = '「氏名ふりがな(めい)」は全角ひらがなで入力してください。';
		}
	}
	//△△△

	//2016.08．22　服部　電話番号のエラーチェックを変更
	if( empty($post['tel_01']) || empty($post['tel_02']) || empty($post['tel_03']) ){
		$error['tel'] = '「電話番号」を入力してください。';
		$error['tel_01'] = 'no_disp';
		$error['tel_02'] = 'no_disp';
		$error['tel_03'] = 'no_disp';
	}
	if ( !isset($error['tel']) && (!preg_match("/^[0-9-]+$/", $post['tel_01'])||!preg_match("/^[0-9-]+$/", $post['tel_02'])||!preg_match("/^[0-9-]+$/", $post['tel_03'])) )  $error['tel'] = '「電話番号」を半角数字で入力した下さい。';
	if( empty($post['add_zip_code_01']) || empty($post['add_zip_code_02']) ){
		$error['add_zip_code'] = '「郵便番号」を入力してください。';
		$error['add_zip_code_01'] = 'no_disp';
		$error['add_zip_code_02'] = 'no_disp';
	}
	if ( !isset($error['add_zip_code']) && (!preg_match("/^[0-9-]+$/", $post['add_zip_code_01'])||!preg_match("/^[0-9-]+$/", $post['add_zip_code_02'])) )  $error['add_zip_code'] = '「郵便番号」を半角数字で入力した下さい。';

	if(empty($post['add_prefecture'])) $error['add_prefecture'] = '「都道府県」を入力してください。';
	if(empty($post['add_city'])) $error['add_city'] = '「市区町村」を入力してください。';
	if(empty($post['add_street'])) $error['add_street'] = '「町名番地等」を入力してください。';
	if ( empty($post['old__kp_Sch_data']) && empty($post['name_old_school_sub']) ) {
		$error['old_school'] = '「中学校」を選択してください。';
		$error['old_sch_add_prefecture'] = 'no_disp';
		$error['old_sch_add_city'] = 'no_disp';
		$error['old__kp_Sch_data'] = 'no_disp';
	}

	if( empty($post['old_sch_tel_01']) || empty($post['old_sch_tel_02']) || empty($post['old_sch_tel_03']) ){
		$error['old_sch_tel'] = '「中学校電話番号」を入力してください。';
		$error['old_sch_tel_01'] = 'no_disp';
		$error['old_sch_tel_02'] = 'no_disp';
		$error['old_sch_tel_03'] = 'no_disp';
	}
	if ( !isset($error['old_sch_tel']) && (!preg_match("/^[0-9-]+$/", $post['old_sch_tel_01'])||!preg_match("/^[0-9-]+$/", $post['old_sch_tel_02'])||!preg_match("/^[0-9-]+$/", $post['old_sch_tel_03'])) )  $error['old_sch_tel'] = '「中学校電話番号」を半角数字で入力して下さい。';

	if(empty($post['name_grd_sei'])) $error['name_grd_sei'] = '「保護者氏名(姓)」を入力してください。';
	if(empty($post['name_grd_mei'])) $error['name_grd_mei'] = '「保護者氏名(名)」を入力してください。';
	if(empty($post['name_grd_sei_read'])) $error['name_grd_sei_read'] = '「保護者氏名ふりがな(せい)」を入力してください。';
	if(empty($post['name_grd_mei_read'])) $error['name_grd_mei_read'] = '「保護者氏名ふりがな(めい)」を入力してください。';
	if(empty($post['family_relationship'])) $error['family_relationship'] = '「続柄」を入力してください。';
	if ( !empty($post['grd_add_zip_code']) && (!preg_match("/^[0-9-]+$/", $post['grd_add_zip_code_01'])||!preg_match("/^[0-9-]+$/", $post['grd_add_zip_code_02'])) )  $error['grd_add_zip_code'] = '「保護者郵便番号」を半角数字で入力して下さい。';
	//2016.12.13　服部　氏名フリガナの全角カタカナチェックを追加
	//▽▽▽
	if ( !isset($error['name_grd_sei_read']) ){
		if ( preg_match("/^[ぁ-ん]+$/u", $post['name_grd_sei_read']) ) {
		}else{
			$error['name_grd_sei_read'] = '「保護者氏名ふりがな(せい)」は全角ひらがなで入力してください。';
		}
	}
	if ( !isset($error['name_grd_mei_read']) ){
		if ( preg_match("/^[ぁ-ん]+$/u", $post['name_grd_mei_read']) ) {
		}else{
			$error['name_grd_mei_read'] = '「保護者氏名ふりがな(めい)」は全角ひらがなで入力してください。';
		}
	}
	//△△△


	// // 2017.05.05 平井 第一志望日程・第一志望追加・・・17.11.16 平井 仕様変更のため日程は必須項目としない。
	// if(empty($post['other_school_exam_date_01'])) $error['other_school_exam_date_01 '] = '「第一志望日程」を入力してください。';
	// if(empty($post['other_sch_prefecture_01']) || empty($post['other_sch_section_01']) || empty($post['MA_school_01'])) $error['other_sch_form'] =
	// '「第一志望」を入力してください。';

	//2016.08.23　服部　メールの受信確認をエラーチェックに追加
	if( $post['mail_chk'] !== $post['mail'] ){ $error['mail_test_error'] = '必ずメールの受信確認を行なってください。'; }

	if(empty($post['mail'])) $error['mail'] = '「メールアドレス」を入力してください。';
	if(empty($post['password'])) $error['password'] = '「パスワード」を入力してください。';
	if(!empty($post['password']) && !preg_match("/^[a-zA-Z0-9]{6,}+$/", $post['password'])) $error['password'] = '「パスワード」を半角英数字６文字以上で入力してください。';
	if(empty($post['password_cfm'])) $error['password_cfm'] = '「パスワード(確認)」を入力してください。';
	if(empty($post['payment_type'])) $error['payment_type'] = '「支払方法」を選択してください。';

	// 必須以外のエラーチェック
	if(empty($error)) {
		// メールアドレスの文字列チェック
		if($er = Validate::EMail($post['mail'], "「メールアドレス」")) {
			$error['mail'] = $er;
		} else {
			// メールアドレスの既存チェック --- 2017.05.23 平井 「Web_AP→z_XM_Sy」ファイルへ変更
			$fx = new FX(FX_IP, FX_PORT, FX_VER);
			$fx->SetDBData('z_XM_Sy','web_Xm_data_main');
			$fx->SetDBUserPass(FX_ID, FX_PASS);
			$fx->SetCharacterEncoding('utf8');
			$fx->SetDataParamsEncoding('utf8');

			// 変数の格納
			$mail = $post["mail"];
			$exam_year = JYear::GetJYear_exam();

			// 検索条件の指定 --- 2017.05.22 平井修正
			// 検索条件の指定
			$fx->AddDBParam('mail',"==\"$mail\"");
			$fx->AddDBParam('year',"==\"$exam_year\"");
			$er_res = $fx->FMFind();
			if(!empty($er_res['data'])) $error['mail'] = '既に登録されている「メールアドレス」です。';
		}

		// パスワードの一致チェック
		if($post['password'] != $post['password_cfm']) $error['password'] = '「パスワード」が一致しません。';
	}

	// 日付チェック(yy/mm/dd)
	// 生年月日
	if($er = Validate::Date($post['date_of_birth'], "「生年月日」", "/")) $error['date_of_birth'] = $er;

	// 併願校受験日程
	// if(!empty($post['other_sch_exam_date_01']) && $er = Validate::Date($post['other_sch_exam_date_01'], "「併願校受験日程1」", "/")) $error['other_sch_exam_date_01'] = $er;
	// if(!empty($post['other_sch_exam_date_02']) && $er = Validate::Date($post['other_sch_exam_date_02'], "「併願校受験日程2」", "/")) $error['other_sch_exam_date_02'] = $er;
	// if(!empty($post['other_sch_exam_date_03']) && $er = Validate::Date($post['other_sch_exam_date_03'], "「併願校受験日程3」", "/")) $error['other_sch_exam_date_03'] = $er;
	// if(!empty($post['other_sch_exam_date_04']) && $er = Validate::Date($post['other_sch_exam_date_04'], "「併願校受験日程4」", "/")) $error['other_sch_exam_date_04'] = $er;
	// if(!empty($post['other_sch_exam_date_05']) && $er = Validate::Date($post['other_sch_exam_date_05'], "「併願校受験日程5」", "/")) $error['other_sch_exam_date_05'] = $er;

	// エラーがない場合確認ページへ
	if(empty($error)) {
		//出身校名を取得
		if( !empty($post['old__kp_Sch_data']) ){
			$old_school = new FX(FX_IP, FX_PORT, FX_VER);
			$old_school->SetDBData('z_XM_Sy','web_Sch_data');
			$old_school->SetDBUserPass(FX_ID, FX_PASS);
			$old_school->SetCharacterEncoding('utf8');
			$old_school->SetDataParamsEncoding('utf8');
			$old_school->AddDBParam('__kp_Sch_data', '=='.$post['old__kp_Sch_data'] );
			$shu_res = $old_school->FMFind();
			$key = key($shu_res['data']);
			$post['old_school'] = $shu_res['data'][$key]['sch_name'][0];
		}

		$_SESSION['info'] = $post;
		header("Location:cfm.php");
		exit();
	}
}

///////////////
//出身中学校リスト
//////////////
$year = date("Y");
$_key_sch_div = 4 ;

//市区町村を検索
$old_school = new FX(FX_IP, FX_PORT, FX_VER);
$old_school->SetDBData('z_Xm_Sy','web_Sch_data');
$old_school->SetDBUserPass(FX_ID, FX_PASS);
$old_school->SetCharacterEncoding('utf8');
$old_school->SetDataParamsEncoding('utf8');

$old_school->AddDBParam('z_record_number', '==1');
//市区町村リストをグローバルフィールドに格納するFMスクリプトを実行
$old_school->PerformFMScriptPrefind('WebAp_出身校_都道府県取得');
$shu_res = $old_school->FMFind();

$key = key($shu_res['data']);
$area_list = $shu_res['data'][$key]['_g_temp'][0];
$old_sch_add_prefecture = explode(',',$area_list);

//都道府県が選択済みの場合は市区町村リストを取得しておく
if ( !empty( $post['old_sch_add_prefecture'] ) ){
	//市区町村を検索
	$old_sch_add_city = new FX(FX_IP, FX_PORT, FX_VER);
	$old_sch_add_city->SetDBData('z_Xm_Sy','web_Sch_data');
	$old_sch_add_city->SetDBUserPass(FX_ID, FX_PASS);
	$old_sch_add_city->SetCharacterEncoding('utf8');
	$old_sch_add_city->SetDataParamsEncoding('utf8');

	$old_sch_add_city->AddDBParam('z_record_number', '==1');
	//市区町村リストをグローバルフィールドに格納するFMスクリプトを実行
	$param = $post['old_sch_add_prefecture'];
	$old_sch_add_city->AddDBParam('-script.prefind.param',$param );
	$old_sch_add_city->PerformFMScriptPrefind('WebAp_出身校_市区町村取得');
	$shu_city_res = $old_sch_add_city->FMFind();

	$key = key($shu_city_res['data']);
	$area_list = $shu_city_res['data'][$key]['_g_temp'][0];
	$old_sch_add_city = explode(',',$area_list);
}

//市区町村が選択済みの場合は学校リストを取得しておく
if ( !empty( $post['old_sch_add_prefecture'] ) && !empty( $post['old_sch_add_city'] ) ){
	//市区町村を検索
	$old_school = new FX(FX_IP, FX_PORT, FX_VER);
	$old_school->SetDBData('z_XM_Sy','web_Sch_data','All');
	$old_school->SetDBUserPass(FX_ID, FX_PASS);
	$old_school->SetCharacterEncoding('utf8');
	$old_school->SetDataParamsEncoding('utf8');

	// $old_school->AddDBParam('FLG_web_non_disp', '=');
	$_key_sch_div = 3;
	$old_school->AddDBParam('_key_mst_div', $_key_sch_div);
	$old_school->AddDBParam('sch_add_prefecture', '=='.$post['old_sch_add_prefecture']);
	$old_school->AddDBParam('sch_add_city', '=='.$post['old_sch_add_city']);

	$old_school->AddSortParam('sch_name_read','ascend',1);
	$old_school_res = $old_school->FMFind();

	foreach ($old_school_res['data'] as $key => $value) {
	$old_sch_name[] = $value['sch_name'][0];
	$__kp_Sch_data[] = $value['__kp_Sch_data'][0];
	}

}

////////////
//併願校リスト
////////////
/*
$year_heigan = JYear::GetJYear_exam();
$_key_sch_div = 4;
//都道府県
$heigan_pref = new FX(FX_IP, FX_PORT, FX_VER);
$heigan_pref->SetDBData('z_XM_Sy','web_Sch_data', 'All');
$heigan_pref->SetDBUserPass(FX_ID, FX_PASS);
$heigan_pref->SetCharacterEncoding('utf8');
$heigan_pref->SetDataParamsEncoding('utf8');

$heigan_pref->AddDBParam('z_record_number', '==1');

//検索前に実行するスクリプトの設定
$heigan_pref->PerformFMScriptPrefind('WebAp_併願校都道府県設定');
$param = $_key_sch_div;
$heigan_pref->AddDBParam('-script.prefind.param',$param );

$hei_pref_res = $heigan_pref->FMFind();

$key = key($hei_pref_res['data']);
$kubun_list = $hei_pref_res['data'][$key]['_g_temp'][0];
$heigan_prefecture = explode(',',$kubun_list);

//併願校区分リスト作成用関数
function getlist_heigan_kubun($pref,$self_value){
	$year = JYear::GetJYear_exam();
	//$year = date("Y");
	$_key_sch_div = 4;

	$fx = new FX(FX_IP, FX_PORT, FX_VER);
	$fx->SetDBData('z_XM_Sy','web_Sch_data', 'All');
	$fx->SetDBUserPass(FX_ID, FX_PASS);
	$fx->SetCharacterEncoding('utf8');
	$fx->SetDataParamsEncoding('utf8');

	$fx->AddDBParam('z_record_number', '==1');

	//検索前に実行するスクリプトの設定
	$fx->PerformFMScriptPrefind('WebAp_併願校区分設定');
	$param = $_key_sch_div.'|'.$pref;
	$fx->AddDBParam('-script.prefind.param',$param );

	$fx_res = $fx->FMFind();
	$key = key($fx_res['data']);
	$kubun_list = $fx_res['data'][$key]['_g_temp'][0];
	$heigan_kubun = explode(',',$kubun_list);

	$max = count($heigan_kubun);
	for( $i = 0 ; $i < $max ; $i++ ) {
		$t_kubun = $heigan_kubun[$i];
		if($self_value == $t_kubun){
			echo '<option value="'.$t_kubun.'" selected="selected">'.$t_kubun.'</option>';
		}else{
			echo '<option value="'.$t_kubun.'">'.$t_kubun.'</option>';
		}
	}
}

//併願校名（キー）リスト作成用関数
function getlist_heigan_key($pref,$kubun,$self_value){
	$year = JYear::GetJYear_exam();
	$_key_sch_div = 4;

	$fx = new FX(FX_IP, FX_PORT, FX_VER);
	$fx->SetDBData('z_XM_Sy','web_Sch_data', 'All');
	$fx->SetDBUserPass(FX_ID, FX_PASS);
	$fx->SetCharacterEncoding('utf8');
	$fx->SetDataParamsEncoding('utf8');

	$fx->AddDBParam('_key_mst_div', $_key_sch_div);
	$fx->AddDBParam('sch_add_prefecture', '=='.$pref);
	$fx->AddDBParam('sch_installation_personnel', '=='.$kubun);
	$fx->AddSortParam('sch_name_read','ascend',1);

	$fx_res = $fx->FMFind();

	foreach ($fx_res['data'] as $key => $value) {
		$t_key = $value['__kp_Sch_data'][0];
		$t_name = $value['sch_name'][0];
		if($self_value == $t_key){
			echo '<option value="'.$t_key.'" selected="selected">'.$t_name.'</option>';
		}else{
			echo '<option value="'.$t_key.'">'.$t_name.'</option>';
		}
	}
}
 */ ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">

<head>
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<title>受験者情報入力</title>

<script src="js/jquery-1.11.3.min.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
<script src="js/jquery.corner.js" type="text/javascript"></script>
<script src="js/jquery.ui.datepicker-ja.min.js" type="text/javascript"></script>
<script type="text/javascript" src="https://ajaxzip3.github.io/ajaxzip3.js" charset="utf-8"></script>
<link rel="stylesheet" href="css/jquery-ui-1.10.4.custom.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/reset.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/base.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/form.css" media="all" type="text/css" />

<script>
////////////////
//併願校の入力補助
////////////////
function set_other_school(target){
	$(function(){
		//初期設定
		//データ取得部分を関数化しておく
		function GetData(p_params){
			return $.ajax({
				type: "POST",
				url: "get_ajax.php",
				data: p_params
			})
		}

		//選択したリストを取得
		if (target.indexOf('prefecture') > 0) {
			var select_val = 'pref';
		}else{
			var select_val = 'sch_installation_personnel';
		}
		//併願校1～5を取得(01～05)
		var num = target.slice(-2);
		var id_pref = 'other_sch_prefecture_'+num;
		var val_pref = $('#'+id_pref).val();
		var id_kubun = 'other_sch_section_'+num
		var val_kubun = $('#'+id_kubun).val();
		var id_heigan_key = '__kp_Sch_data_'+num;

		//都道府県の値
		var val_prefecture = $('#other_sch_prefecture').val();
		//市区町村の値
		var val_city = $('#other_sch_city').val();

		////////////
		//リストの取得
		///////////
		if ( select_val == 'pref' ) {
			//区分の初期化
			$('select#'+id_kubun+' option').remove();
			//学校名の初期化
			$('select#'+id_heigan_key+' option').remove();
			var op = '<option value="" selected>選択</option>';
			$('#'+id_heigan_key).html(op);

			if( val_pref ){
				$_key_sch_div = 4;
				//併願校区分の取得
				var param_01 = '<?php echo $_key_sch_div; ?>';
				var param_02 = val_pref;
				var params = "category=sch_installation_personnel&param_01="+param_01+"&"+"param_02="+param_02;
				GetData(params).done(function(result) {
					var res = JSON.parse(result||"null");
					var vl_list = res.sch_installation_personnel;
					//optionの作成
					var max = Object.keys(vl_list).length;
					var op = '<option value="" selected>選択</option>';
					for( var i = 0 ; i < max ; i++ ){
						op += '<option value="'+vl_list[i]+'">'+vl_list[i]+'</option>';
					}
					$('#'+id_kubun).html(op);
				})
			}else{
				var op = '<option value="" selected>選択</option>';
				$('#'+id_kubun).html(op);
			}
		}

		//学校名の取得
		if ( select_val == 'sch_installation_personnel' ) {
			$('select#'+id_heigan_key+' option').remove();
			if( val_kubun ){
				var param_01 = '<?php echo $_key_sch_div; ?>';
				var param_02 = val_pref;
				var param_03 = val_kubun;
				var params = "category=other_school&param_01="+param_01+"&"+"param_02="+param_02+"&"+"param_03="+param_03;
				GetData(params).done(function(result) {
					var res = JSON.parse(result||"null");
					var name_list = res.sch_name;
					var key_list = res.__kp_Sch_data;
					//optionの作成
					var max = Object.keys(key_list).length;
					var op = '<option value="" selected>選択</option>';
					for( var i = 0 ; i < max ; i++ ){
						op += '<option value="'+key_list[i]+'">'+name_list[i]+'</option>';
					}
					$('#'+id_heigan_key).html(op);
				})
			}else{
				var op = '<option value="" selected>選択</option>';
				$('#'+id_heigan_key).html(op);
			}
		}
	});
}

////////////////
//出身校の入力補助
////////////////
function set_old_school(target_val,target_id){
	$(function(){

		//初期設定
		//データ取得部分を関数化しておく
		function GetData(p_param,p_category){
			return $.ajax({
				type: "POST",
				url: "get_ajax.php",
				data: "category="+p_category+"&param_01="+p_param
			})
		}

		//都道府県の値
		var val_prefecture = $('#old_sch_add_prefecture').val();
		//市区町村の値
		var val_city = $('#old_sch_add_city').val();

		////////////
		//リストの取得
		///////////
		//市区町村の取得
		if ( target_id == 'old_sch_add_prefecture' ) {
			//市区町村の初期化
			$('select#old_sch_add_city option').remove();
			//学校名の初期化
			$('select#__kp_Sch_data option').remove();
			var op = '<option value="" selected>選択</option>';
			$('#old__kp_Sch_data').html(op);

			if( val_prefecture ){
				//市区町村内の学校リストを取得
				var param = target_val;
				var category = 'old_sch_add_city';
				GetData(param,category).done(function(result) {
					var res = JSON.parse(result||"null");
					var vl_list = res.city;
					//optionの作成
					var max = Object.keys(vl_list).length;
					var op = '<option value="" selected>選択</option>';
					for( var i = 0 ; i < max ; i++ ){
						op += '<option value="'+vl_list[i]+'">'+vl_list[i]+'</option>';
					}
					$('#old_sch_add_city').html(op);
				})
			}else{
				var op = '<option value="" selected>選択</option>';
				$('#old_sch_add_city').html(op);
			}
		}

		//学校名の取得
		if ( target_id == 'old_sch_add_city' ) {
			$('select#old__kp_Sch_data option').remove();
			if( val_city ){
				//市区町村内の学校リストを取得
				var param = target_val;
				var category = 'old_school';
				GetData(param,category).done(function(result) {
					var res = JSON.parse(result||"null");
					var name_list = res.old_school_name;
					var key_list = res.__kp_Sch_data;
					//optionの作成
					var max = Object.keys(key_list).length;
					var op = '<option value="" selected>選択</option>';
					for( var i = 0 ; i < max ; i++ ){
						op += '<option value="'+key_list[i]+'">'+name_list[i]+'</option>';
					}
					$('#old__kp_Sch_data').html(op);
				})
			}else{
				var op = '<option value="" selected>選択</option>';
				$('#old__kp_Sch_data').html(op);
			}
		}
	});
}

	//$post['mail_chk']に値がある場合は#mail_chkに値をセットしておく
$(function(){
	var mail_chk = '<?php echo $post['mail_chk']; ?>';
	if( mail_chk ){
		$('#mail_chk').val(mail_chk);
	}
});

////////////////
//メール受信テスト
////////////////
function mail_test(){
	$(function(){
		//メールの送信部分を関数化しておく
		function mail_send(param){
			//テスト送信
			return $.ajax({
				type: "POST",
				url: "mail_test.php",
				data: "mail="+param
			})
		}

		//メッセージ表示部分を関数化しておく
		function show_dialog(p_title,p_message,p_width){
			//メッセージボックスの生成
			var el = $('<div class="mail_test_dialog"></div>').dialog({autoOpen:false});
			el.dialog("option", {
				title: p_title,
				width: p_width,
				buttons: {
					"OK": function() { $(this).dialog("close"); }
				}
			});
			el.html(p_message);
			el.dialog("open");
		}

		//ウィンドウ幅によって文字サイズを変更
		var screen_width = $(window).width();
		if( screen_width < 479 ){
			$('.mail_test_dialog').css('font-size','0.6em');
			var dialog_width = 'auto';
		}else{
			var dialog_width = '400px';
		}
		//var id_mail = document.getElementById('mail');
		var val_mail = $("#mail").val();
		var title;
		var message;
		//「@」が含まれるかの判定用
		var chk_at = val_mail.indexOf('@');
		//メールアドレスが空欄の場合はエラーメッセージを表示する
		//空欄の場合
		if ( !val_mail ) {
			title = "エラー";
			message = "メールアドレスを入力してください。";
			show_dialog(title,message,dialog_width);

		//「@」が含まれていない場合
		}else if ( chk_at == -1 ){
			title = "エラー";
			message = "メールアドレスに「@」が含まれていません。";
			show_dialog(title,message,dialog_width);

		//メール送信
		}else{
			//メールの送信
			mail_send(val_mail).done(function(result) {
				if( result > 0 ){
					title = "エラー";
					message = "既に登録されているメールアドレスです。";
				}else{
					title = "確認";
					message = "受信確認用のメールを送信しました。<br>必ずメールが届くことを確認してください。";
					//メールの受信確認チェックを設定
					$('#mail_chk').val(val_mail);
				}
				show_dialog(title,message,dialog_width);
			});
		}
	});
}

function funcErrorBack(id) {
	$('#'+id).css('background-color', '#FCD8D9');
}

$(function(){
	$(".pankuzu").corner("14px");
	<?php if(!empty($error)): ?>
	<?php foreach($error as $key=>$val): ?>
	funcErrorBack("<?php echo $key; ?>");
	<?php endforeach; ?>
	<?php endif; ?>
});

$(function() {
	$.datepicker.setDefaults($.datepicker.regional["ja"]);
	$(".date").datepicker({
		dateFormat: 'yy/mm/dd',
		changeMonth: true,
		changeYear: true,
		yearRange: 'c-20:c+1'
	});
});

//2016.08.05　服部　生年月日用のdatepickerを作成
$(function() {
	//var now = new Date();
	//var current_year = now.getFullYear();
	var current_year = '<?php echo JYear::GetJYear_exam(); ?>';
	var default_year = Number(current_year) - 16;
	var default_date = String(default_year)+"/04/01";
	$.datepicker.setDefaults($.datepicker.regional["ja"]);
	$(".date_of_birth").datepicker({
		dateFormat: 'yy/mm/dd',
		changeMonth: true,
		changeYear: true,
		defaultDate: default_date,
		yearRange: 'c-20:c'
	});
});

//2017.04.24　平井　東洋女子FLAG作成


$(function(){

	$('input[name="checkbox_first"]').change(function(){

		// checkbox_first()でチェックの状態を取得
		var chk_first = $('#checkbox_first').prop('checked');

		// 変数設定(都道府県 / 区分 / 学校名)
		var id_date = 'other_sch_exam_date_01';
		var id_pref = 'other_sch_prefecture_01';
		var id_kubun = 'other_sch_section_01';
		var id_heigan_key = 'MA_school_01';

		if(chk_first){



			//第1志望日程の初期化 / 設定
			$(function(){

					//初期設定
					//データ取得部分を関数化しておく
					function GetData_02(p_param,p_category){
						return $.ajax({
							type: "POST",
							url: "get_ajax.php",
							data: "category="+p_category+"&param_01="+p_param
						})
					}

					// 日付の設定
					var param_01 = '<?php echo $_SESSION['REF_master_juken_number'][0]; ?>';
					var category = 'date_wish';
					GetData_02(param_01,category).done(function(result) {
						var res = JSON.parse(result||"null");
						var t_date = res.date;
						$('#'+id_date).val("");
						// var op_date = '<input type="text" value='+t_date+'/>';
						$('#'+id_date).val(t_date);
					})


					// 日付変更 入力制御
					var target_id_date = document.getElementById(id_date);
					target_id_date.readOnly = true;
					$('#'+id_date).datepicker("destroy");

		});

			//都道府県の初期化 / 設定
			$('select#'+id_pref+' option').remove();
			var op01 = '<option value="東京" selected>選択</option>';
				$('#'+id_pref).html(op01);

			//区分の初期化 / 設定
			$('select#'+id_kubun+' option').remove();
			var op02 = '<option value="私立" selected>選択</option>';
				$('#'+id_kubun).html(op02);

			//学校名の初期化 / 設定
			$('select#'+id_heigan_key+' option').remove();

			var op03 = '<option value="1278" selected>選択</option>';
				$('#'+id_heigan_key).html(op03);

		}else{

			// 第1志望の初期化 / datepicker設定
			var target_id_date = document.getElementById(id_date);
			$('#'+id_date).val("");
			target_id_date.readOnly = false;
			$.datepicker.setDefaults($.datepicker.regional["ja"]);
			$(".date").datepicker({
				dateFormat: 'yy/mm/dd',
				changeMonth: true,
				changeYear: true,
				yearRange: 'c-20:c+1'
			});


			//都道府県の初期化 / 設定
			$('select#'+id_pref+' option').remove();

			// 都道府県の配列から受け取るためにjson形式で加工
			<?php $json_pref =json_encode($heigan_prefecture)?>

			// 都道府県の配列から受け取る
			var pref_list = JSON.parse('<?php echo $json_pref ; ?>');
			// pref_list.trim();
			// var pref_list = json_pref.split(" ");
			var max = Object.keys(pref_list).length;
			var op01 = '<option value="" selected>選択</option>';
					for (var i = 0; i < max ; i++) {
				 	op01 += '<option value="'+pref_list[i]+'">'+pref_list[i]+'</option>'
					}
				$('#'+id_pref).html(op01);

			//区分の初期化 / 設定
			$('select#'+id_kubun+' option').remove();
				var op02 = '<option value="" selected>選択</option>';
				$('#'+id_kubun).html(op02);

			//学校名の初期化 / 設定
			$('select#'+id_heigan_key+' option').remove();
     		var op03 = '<option value="" selected>選択</option>';
				$('#'+id_heigan_key).html(op03);

		}
	});
});

//2017.04.24　平井　東洋女子FLAG作成


</script>
</head>

<body>
<!-- header ▼ -->
<?php include ('common/header.php'); ?>
<!-- header ▲ -->



<!-- <?php //echo $_SESSION['__kp_Xm_mst_clt'][0];?> -->



<!-- title ▼ -->
<div id="title">
	<p id="sub_title">Information of examinee</p>
	<h2>受験者情報入力</h2>
</div>
<!-- title ▲ -->

<!-- main_contents ▼ -->
<div id="main_contents">
	<div id="contents_inner">
	<!-- 2016.07.20　服部　パン屑リストを「1.受験日程選択」「2.受験者情報入力」「3.決済前確認」「4.完了」に変更 -->
		<div id="transition" class="pc">
			<ul class="cf">
				<li class="pankuzu">1. 受験日程選択</li>
				<li class="arrow">→</li>
				<li class="act pankuzu">2. 受験者情報入力</li>
				<li class="arrow">→</li>
				<li class="pankuzu">3.出願内容確認</li>
				<li class="arrow">→</li>
				<li class="pankuzu">4. 完了</li>
			</ul>
		</div>

		<div id="transition" class="sp">
			<ul class="cf">
				<li class="pankuzu">1.</li>
				<li class="arrow">&gt;</li>
				<li class="act pankuzu">2. 受験者情報入力</li>
				<li class="arrow">&gt;</li>
				<li class="pankuzu">3.</li>
				<li class="arrow">&gt;</li>
				<li class="pankuzu">4. 完了</li>
			</ul>
		</div>

		<div id="error">
		<?php
			if(!empty($error)) {
				echo "<ul>";
				foreach($error as $er) {
					if( $er !== 'no_disp' ){
						echo "<li>・".$er."</li>";
					}
				}
				echo "</ul>";
			}
		?>
		</div>

		<p class="comment">受験者情報を入力してください。</p>
		<p class="info"><span>*</span>は必須項目です。</p>
		<!--<p class="info"><span>*</span>は必須項目です。</p>-->
		<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
			<div id="form">
				<dl class="cf">
					<dt>氏名(姓・名)<span>*</span></dt>
					<dd><input type="text" id="name_sei" name="name_sei" value="<?php echo funcDefinCheck($post, 'name_sei'); ?>" />&nbsp;<input type="text" id="name_mei" name="name_mei" value="<?php echo funcDefinCheck($post, 'name_mei'); ?>" /><a class="caution">常用漢字で入力</a></dd>
				</dl>
				<dl class="cf">
					<dt>氏名ふりがな(せい・めい)<span>*</span></dt>
					<dd><input type="text" id="name_sei_read" name="name_sei_read" value="<?php echo funcDefinCheck($post, 'name_sei_read'); ?>" />&nbsp;<input type="text" id="name_mei_read" name="name_mei_read" value="<?php echo funcDefinCheck($post, 'name_mei_read'); ?>" />
					<a class="caution">全角かな入力</a>
					</dd>
				</dl>
				<dl class="cf">
					<dt>性別<span>*</span></dt>
					<dd><input type="radio" name="gender" value="男" checked="checked"/>男性<input type="radio" name="gender" value="女"/>女性
					</dd>
				</dl>
				<dl class="cf">
					<dt>生年月日<span>*</span></dt>
					<!--2016.08.05　服部　生年月日のみ別のdatepickerを割り当てるため、classを変更-->
					<dd><input type="text" id="date_of_birth" class="date_of_birth" name="date_of_birth" value="<?php echo funcDefinCheck($post, 'date_of_birth'); ?>" /></dd>
				</dl>
				<!--2016.08.23　服部　電話番号の配置を変更-->
				<dl class="cf">
					<dt>電話番号<span>*</span></dt>
					<!-- 2016.08.22　服部　電話番号を3枠に変更-->
					<dd><input type="text" class="tel" id="tel_01" name="tel_01" value="<?php echo funcDefinCheck($post, 'tel_01'); ?>" /><span class="hyphen">-</span><input type="text" class="tel" id="tel_02" name="tel_02" value="<?php echo funcDefinCheck($post, 'tel_02'); ?>" /><span class="hyphen">-</span><input type="text" class="tel" id="tel_03" name="tel_03" value="<?php echo funcDefinCheck($post, 'tel_03'); ?>" />
					<a class="caution">携帯または固定電話</a>
					</dd>
				</dl>
				<dl class="cf">
					<dt>郵便番号<span>*</span></dt>
					<dd><input type="text" class="add_zip_code" id="add_zip_code_01" name="add_zip_code_01" value="<?php echo funcDefinCheck($post, 'add_zip_code_01'); ?>" /><span class="hyphen">-</span><input type="text" class="add_zip_code" id="add_zip_code_02" name="add_zip_code_02" value="<?php echo funcDefinCheck($post, 'add_zip_code_02'); ?>" onkeyup="AjaxZip3.zip2addr('add_zip_code_01','add_zip_code_02','add_prefecture','add_city','add_street');"/></dd>
				</dl>
				<dl class="cf">
					<dt>都道府県<span>*</span></dt>
					<dd><input type="text" id="add_prefecture" name="add_prefecture" value="<?php echo funcDefinCheck($post, 'add_prefecture'); ?>" /></dd>
				</dl>
				<dl class="cf">
					<dt>市区町村<span>*</span></dt>
					<dd><input type="text" id="add_city" name="add_city" value="<?php echo funcDefinCheck($post, 'add_city'); ?>" /></dd>
				</dl>
				<dl class="cf">
					<dt>町名番地等<span>*</span></dt>
					<dd><input type="text" id="add_street" name="add_street" value="<?php echo funcDefinCheck($post, 'add_street'); ?>" /></dd>
				</dl>

				<!-- テスト<2017.04.16 平井> -->
				<dl class="cf">
					<dt>マンション名</dt>
					<dd><input type="text" id="add_apartment" name="add_apartment" value="<?php echo funcDefinCheck($post, 'add_apartment'); ?>" /></dd>
				</dl>
				<!-- テスト<2017.04.16 平井> -->

				<dl class="cf">
					<dt>中学校<span>*</span></dt>
					<dd>
						<?php
							echo '<select class="w_select_01" name="old_sch_add_prefecture" id="old_sch_add_prefecture" onchange="set_old_school(this.value,this.id)">';
							echo '<option value="">選択</option>';
							foreach($old_sch_add_prefecture as $val) {
								if($post['old_sch_add_prefecture'] == $val){
									echo '<option value="'.$val.'" selected="selected">'.$val.'</option>';
								}else{
									echo '<option value="'.$val.'">'.$val.'</option>';
								}
							}
							echo '</select>';
							//市区町村
							echo '<select class="w_select_02" name="old_sch_add_city" id="old_sch_add_city" onchange="set_old_school(this.value,this.id)"><option value="">選択</option>';
							foreach($old_sch_add_city as $val) {
								if($post['old_sch_add_city'] == $val){
									echo '<option value="'.$val.'" selected="selected">'.$val.'</option>';
								}else{
									echo '<option value="'.$val.'">'.$val.'</option>';
								}
							}
							echo '</select>';
							//学校名
							echo '<select class="w_select_03" name="old__kp_Sch_data" id="old__kp_Sch_data"><option value="">選択</option>';
							if ( !empty($post['old_sch_add_city']) ) {
								$max = count($__kp_Sch_data);
								for( $i = 0 ; $i < $max ; $i++ ) {
									$t_key = $__kp_Sch_data[$i];
									$t_name = $old_sch_name[$i];
									if($post['old__kp_Sch_data'] == $t_key)
										echo '<option value="'.$t_key.'" selected="selected">'.$t_name.'</option>';
									else
										echo '<option value="'.$t_key.'">'.$t_name.'</option>';
								}
							}
							echo '</select>';
							?>
					</dd>
				</dl>
				<dl class="cf">
					<dt>&emsp;※リストにない場合</dt>
					<dd><input type="text" id="name_old_school_sub" name="name_old_school_sub" value="<?php echo funcDefinCheck($post, 'name_old_school_sub'); ?>" /></dd>
				</dl>

				<dl class="cf">
					<dt>卒業見込み<span>*</span></dt>
					<dd>
						<select name="graduate" id="graduate">
							<?php
								if( empty($post['graduate']) || $post['graduate'] == '卒業見込み' ){
									echo '<option value="卒業見込み" selected="selected">卒業見込み</option><option value="卒業">卒業</option>';
								}else{
									echo '<option value="卒業見込み">卒業見込み</option><option value="卒業" selected="selected">卒業</option>';
								}
							?>
						</select>
					</dd>
				</dl>
				<dl class="cf">
					<dt>中学校電話番号<span>*</span></dt>
					<dd><input type="text" class="tel" id="old_sch_tel_01" name="old_sch_tel_01" value="<?php echo funcDefinCheck($post, 'old_sch_tel_01'); ?>" /><span class="hyphen">-</span><input type="text" class="tel" id="old_sch_tel_02" name="old_sch_tel_02" value="<?php echo funcDefinCheck($post, 'old_sch_tel_02'); ?>" /><span class="hyphen">-</span><input type="text" class="tel" id="old_sch_tel_03" name="old_sch_tel_03" value="<?php echo funcDefinCheck($post, 'old_sch_tel_03'); ?>" />
					</dd>
				</dl>
				<dl class="cf">
					<dt>保護者氏名(姓・名)<span>*</span></dt>
					<dd><input type="text" id="name_grd_sei" name="name_grd_sei" value="<?php echo funcDefinCheck($post, 'name_grd_sei'); ?>" />&nbsp;<input type="text" id="name_grd_mei" name="name_grd_mei" value="<?php echo funcDefinCheck($post, 'name_grd_mei'); ?>" /><a class="caution">常用漢字で入力</a></dd>
				</dl>
				<dl class="cf">
					<dt>保護者ふりがな(せい・めい)<span>*</span></dt>
					<dd><input type="text" id="name_grd_sei_read" name="name_grd_sei_read" value="<?php echo funcDefinCheck($post, 'name_grd_sei_read'); ?>" />&nbsp;<input type="text" id="name_grd_mei_read" name="name_grd_mei_read" value="<?php echo funcDefinCheck($post, 'name_grd_mei_read'); ?>" />
					<a class="caution">全角かな入力</a>
					</dd>
				</dl>
				<dl class="cf">
					<dt>続柄<span>*</span></dt>
					<dd><input type="text" id="family_relationship" name="family_relationship" value="<?php echo funcDefinCheck($post, 'family_relationship'); ?>" /></dd>
				</dl>
				<!--2016.08.23　保護者住所を追加-->
				<dl class="cf">
					<dt>郵便番号(保護者)</dt>
					<dd><input type="text" class="add_zip_code" id="grd_add_zip_code_01" name="grd_add_zip_code_01" value="<?php echo funcDefinCheck($post, 'grd_add_zip_code_01'); ?>" /><span class="hyphen">-</span><input type="text" class="add_zip_code" id="grd_add_zip_code_02" name="grd_add_zip_code_02" value="<?php echo funcDefinCheck($post, 'grd_add_zip_code_02'); ?>"  onkeyup="AjaxZip3.zip2addr('grd_add_zip_code_01','grd_add_zip_code_02','grd_add_prefecture','grd_add_city','grd_add_street');"/><a class="caution">志願者と同じ場合は空欄</a>
					</dd>
				</dl>
				<dl class="cf">
					<dt>都道府県(保護者)</dt>
					<dd><input type="text" id="grd_add_prefecture" name="grd_add_prefecture" value="<?php echo funcDefinCheck($post, 'grd_add_prefecture'); ?>" /></dd>
				</dl>
				<dl class="cf">
					<dt>市区町村(保護者)</dt>
					<dd><input type="text" id="grd_add_city" name="grd_add_city" value="<?php echo funcDefinCheck($post, 'grd_add_city'); ?>" /></dd>
				</dl>
				<dl class="cf">
					<dt>町名番地等(保護者)</dt>
					<dd><input type="text" id="grd_add_street" name="grd_add_street" value="<?php echo funcDefinCheck($post, 'grd_add_street'); ?>" /></dd>
				</dl>

				<!-- テスト<2017.04.16 平井> -->
			<dl class="cf">
				<dt>マンション名(保護者)</dt>
				<dd><input type="text" id="grd_add_apartment" name="grd_add_apartment" value="<?php echo funcDefinCheck($post, 'grd_add_apartment'); ?>" /></dd>
			</dl>


			<!-- <dl class="cf">
				<dt>第1志望日程</dt>
				<dd><input type="text" id="other_sch_exam_date_01" class="date" name="other_sch_exam_date_01" value="<?php // echo funcDefinCheck($post, 'other_sch_exam_date_01'); ?>" /></dd>
			</dl>
			<dl class="cf">
				<dt>第1志望<span>*</span></dt>
				<dd> -->
				<?php /*
				//併願校番号を取得
				$hei_num = '01';
				//都道府県
				echo "<select class=\"w_select_01\" name=\"other_sch_prefecture_$hei_num\" id=\"other_sch_prefecture_$hei_num\" onchange=\"set_other_school(this.id)\"><option value=\"\">選択</option>";
				foreach($heigan_prefecture as $val) {
					if($post['other_sch_prefecture_'.$hei_num] == $val){
						echo '<option value="'.$val.'" selected="selected">'.$val.'</option>';
					}else{
						echo '<option value="'.$val.'">'.$val.'</option>';
					}
				}
				echo '</select>';

				//区分
				echo "<select class=\"w_select_02\" name=\"other_sch_section_$hei_num\" id=\"other_sch_section_$hei_num\" onchange=\"set_other_school(this.id)\"><option value=\"\">選択</option>";
				if ( !empty($post['other_sch_prefecture_'.$hei_num]) ) {
					//併願校区分の取得
					getlist_heigan_kubun($post['other_sch_prefecture_'.$hei_num],$post['other_sch_section_'.$hei_num]);
				}
				echo '</select>';

				//学校名
				echo "<select class=\"w_select_03\" name=\"__kp_Sch_data_$hei_num\" id=\"__kp_Sch_data_$hei_num\"><option value=\"\">選択</option>";
				if ( !empty($post['other_sch_section_'.$hei_num]) ) {
					//併願校名/区分の取得
					getlist_heigan_key($post['other_sch_prefecture_'.$hei_num],$post['other_sch_section_'.$hei_num],$post['__kp_Sch_data_'.$hei_num]);
				}
				echo '</select>';
				*/?>
				<!-- </dd>
			</dl>

			<dl class="cf">
				<dt>&emsp;※リストにない場合</dt>
				<dd><input type="text" id="other_sch_sub_01" name="other_sch_sub_01" value="<?php echo funcDefinCheck($post, 'other_sch_sub_01'); ?>" /></dd>
			</dl> -->

			<!-- テスト<2017.04.16 平井> -->
				<!-- <dl class="cf">
					<dt>第2志望日程</dt>
					<dd><input type="text" id="other_sch_exam_date_02" class="date" name="other_sch_exam_date_02" value="<?php // echo funcDefinCheck($post, 'other_sch_exam_date_02'); ?>" /></dd>
				</dl>
				<dl class="cf">
					<dt>第2志望</dt>
					<dd> -->
					<?php /*
					//併願校番号を取得
					$hei_num = '02';
					//都道府県
					echo "<select class=\"w_select_01\" name=\"other_sch_prefecture_$hei_num\" id=\"other_sch_prefecture_$hei_num\" onchange=\"set_heigan(this.id)\"><option value=\"\">選択</option>";
					foreach($other_sch_prefecture as $val) {
						if($post['other_sch_prefecture_'.$hei_num] == $val){
							echo '<option value="'.$val.'" selected="selected">'.$val.'</option>';
						}else{
							echo '<option value="'.$val.'">'.$val.'</option>';
						}
					}
					echo '</select>';

					//区分
					echo "<select class=\"w_select_02\" name=\"other_sch_section_$hei_num\" id=\"other_sch_section_$hei_num\" onchange=\"set_heigan(this.id)\"><option value=\"\">選択</option>";
					if ( !empty($post['other_sch_prefecture_'.$hei_num]) ) {
						//併願校区分の取得
						getlist_heigan_kubun($post['other_sch_prefecture_'.$hei_num],$post['other_sch_section_'.$hei_num]);
					}
					echo '</select>';

					//学校名
					echo "<select class=\"w_select_03\" name=\"__kp_Sch_data_$hei_num\" id=\"__kp_Sch_data_$hei_num\"><option value=\"\">選択</option>";
					if ( !empty($post['other_sch_section_'.$hei_num]) ) {
						//併願校名/区分の取得
						getlist_heigan_key($post['other_sch_prefecture_'.$hei_num],$post['other_sch_section_'.$hei_num],$post['__kp_Sch_data_'.$hei_num]);
					}
					echo '</select>';
					*/?>
					<!-- </dd>
				</dl> -->
				<!-- 2016.07.26　服部　リストにない場合の入力欄を追加 -->
				 <!-- <dl class="cf">
					 <dt>&emsp;※リストにない場合</dt>
					 <dd><input type="text" id="other_sch_sub_02" name="other_sch_sub_02" value="<?php echo funcDefinCheck($post, 'other_sch_sub_02'); ?>" /></dd>
				 </dl> -->

				<dl class="cf">
					<dt>メールアドレス<span>*</span></dt>
					<dd><input type="text" id="mail" name="mail" value="<?php echo funcDefinCheck($post, 'mail'); ?>" /><a href="#" id="mail_test" onclick="mail_test();return false;">受信確認</a><a class="caution">※必須</a></dd>
				</dl>
				<dl class="cf">
					<dt>パスワード<span>*</span></dt>
					<dd><input type="password" id="password" name="password" value=""/><a class="caution">半角英数字６文字以上</a></dd>
				</dl>
				<dl class="cf">
					<dt>パスワード(確認)<span>*</span></dt>
					<dd><input type="password" id="password_cfm" name="password_cfm" value="" /></dd>
				</dl>
				<dl class="cf">
					<dt>支払方法<span>*</span></dt>
					<dd>
						<select name="payment_type"  id="payment_type">
							<option value="">選択</option>
							<!--2016.08.22　服部　「コンビニ」を「コンビニ/ペイジー」に変更-->
							<option value="コンビニ/ペイジー" <?php echo ($post['payment_type'] == 'コンビニ/ペイジー')? 'selected="selected"':''; ?>>コンビニ/ペイジー</option>
							<option value="クレジットカード" <?php echo ($post['payment_type'] == 'クレジットカード')? 'selected="selected"':''; ?>>クレジットカード</option>
						</select>
					</dd>
				</dl>
			</div>
			<div class="buttons cf">
				<!--2016.08.22　服部　戻り先を変更-->
				<input type="button" class="back_btn lf" value="戻る"  onclick="location.href='exam.php'" />
				<!--<input type="button" class="back_btn lf" value="戻る"  onclick="location.href='exam_cfm.php'" />-->
				<input type="submit" class="cfm_btn rg" value="確認" />
				<!--2016.08.05　服部　メールの受信確認の有無-->
				<input type="hidden" id="mail_chk" name="mail_chk" value="" />
			</div>
		</form>
	</div>
</div>
<!-- main_contents ▲ -->
<!-- footer ▼ -->
<?php include ('common/footer.php'); ?>
<!-- footer ▲ -->

</body>
</html>
