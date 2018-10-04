<?php

include_once(dirname(__FILE__)."/common/config.ini.php");
include_once(dirname(__FILE__)."/common/common.php");
include_once(dirname(__FILE__)."/FX/FX.php");

// セッションが空の場合トップへ戻る
if(empty($_SESSION['mail'])) {
	header("Location: top.php");
	exit;
}

// 初期化
$kp_WebAp_main = '';
$error = array();

// 変数の格納
$mail = $_SESSION['mail'];
$password = $_SESSION['password'];

// FX用初期処理(受験者情報)
$main = new FX(FX_IP, FX_PORT, FX_VER);
$main->SetDBData('z_Xm_Sy','web_Xm_data_main');
$main->SetDBUserPass(FX_ID, FX_PASS);
$main->SetCharacterEncoding('utf8');
$main->SetDataParamsEncoding('utf8');

// 検索条件の指定
$main->AddDBParam('mail',"==\"$mail\"");
$main->AddDBParam('password','=='.$password);

// 受験者情報の格納
$stu_res = $main->FMFind();

//受験管理の主キーを変数に格納
$key = key($stu_res['data']);
$__kp_Xm_data_main = $stu_res['data'][$key]['__kp_Xm_data_main'][0];
$payment_type = $stu_res['data'][$key]['payment_type'][0];

//表示用データを変数に格納
$print_info = $stu_res['data'][$key];
if( !empty($print_info['payment_link_procedure'][0]) ){
	//手続URLの設定があるか
	$flg_pro = 1;
}

// ログインIDが不正だった場合エラー表示
if(empty($stu_res['data'])) {
	$error['data'] = "不正なログイン情報です。";
	exit;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">

<head>
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<title>確認/印刷</title>
<script src="js/jquery-1.11.3.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/reset.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/base.css" media="all" type="text/css" />
<link rel="stylesheet" href="css/history.css" media="all" type="text/css" />
</head>

<body>
<!-- header ▼ -->
<?php include ('common/header.php'); ?>
<!-- header ▲ -->

<!-- title ▼ -->
<div id="title">
	<p id="sub_title">Confirmation and Print</p>
	<h2>確認/印刷</h2>
</div>
<!-- title ▲ -->

<!-- main_contents ▼ -->
<div id="main_contents">
	<div id="contents_inner">
		<div id="error">
		<?php
			if(!empty($error)) {
				echo "<ul>";
				foreach($error as $er) {
					echo "<li>・".$er."</li>";
				}
				echo "</ul>";
			}
		?>
		</div>

		<div class="form_history">
			<div class="form_title">
				<dl>
					<dt>試験情報</dt>
				</dl>
			</div>
			<?php
				if(empty($error)&&!empty($__kp_Xm_data_main)) {
					// master_juken_number探索用
					$sub = new FX(FX_IP, FX_PORT, FX_VER);
					$sub->SetDBData('z_Xm_Sy','web_Xm_data_sub');
					$sub->SetDBUserPass(FX_ID, FX_PASS);
					$sub->SetCharacterEncoding('utf8');
					$sub->SetDataParamsEncoding('utf8');

					// 検索条件の指定
					$sub->AddDBParam('_kf_Xm_data_main','=='.$__kp_Xm_data_main);
					$sub->AddSortParam('z_make_date','ascend',1);

					// 結果
					$num_res = $sub->FMFind();
					// 受験明細の数
					$exam_num = (int) $num_res['foundCount'];
					//レコードナンバーを取得
					$key_sub = key($num_res['data']);

					// 受験明細のキーを取得
					$i = 0;
					foreach ( $num_res['data'] as $val ){
						$_kf_Xm_mst_clt[$i] = $val['_kf_Xm_mst_clt'][0];
						$i++;
					}

					// 受験明細の取得
					$mjn = new FX(FX_IP, FX_PORT, FX_VER);
					$mjn->SetDBData('z_Xm_Sy','web_mst_clt');
					$mjn->SetDBUserPass(FX_ID, FX_PASS);
					$mjn->SetCharacterEncoding('utf8');
					$mjn->SetDataParamsEncoding('utf8');

					for($i = 0; $i < $exam_num; $i++) {
						$mjn->AddDBParam('__kp_Xm_mst_clt',"==$_kf_Xm_mst_clt[$i]");
						$exam_res = $mjn->FMFind();

						foreach($exam_res['data'] as $val) {
							//2016.10.25　服部　自己推薦書,推薦書FLGを取得
							$FLG_jikosuisensho = $val['FLG_jikosuisensho'][0];
							$FLG_suisensho = $val['FLG_suisensho'][0];
							echo '<dl class="cf">';
							echo '<dt>試験日</dt>';
							echo '<dd>'.$val['date_exam_disp'][0].'</dd>';
							echo '</dl>';
							echo '<dl class="cf">';
							echo '<dt>入試区分</dt>';
							echo '<dd>'.$val['exam_name'][0].'&nbsp;'.$val['sub_name'][0].'</dd>';
							echo '</dl>';
							if( $flg_pro == 1 ){
								echo '<dl class="cf">';
								echo '<dt>入学金</dt>';
								echo '<dd>&yen;'.number_format($num_res['data'][$key_sub]['fee_entrance_self'][0]).'</dd>';
								echo '</dl>';
								//手数料
								echo '<dl class="cf">';
								echo '<dt>手数料(手続)</dt>';
								echo '<dd>&yen;'.number_format(CHARGE_TETSUZUKI).'</dd>';
								echo '</dl>';
								echo '<dl class="cf">';
								echo '<dt>合計金額(手続)</dt>';
								echo '<dd>&yen;'.number_format($num_res['data'][$key_sub]['fee_entrance_self'][0]+CHARGE_TETSUZUKI).'</dd>';
								echo '</dl>';
								echo '<dl class="cf">';
								echo '<dt>決済URL(手続)</dt>';
								echo '<dd><a href="'.$print_info['payment_link_procedure'][0].'" target="_blank">決済ページはこちら</a></dd>';
								echo '</dl>';
							}else{
								echo '<dl class="cf">';
								echo '<dt>受験料</dt>';
								echo '<dd>&yen;'.number_format($val['fee'][0]).'</dd>';
								echo '</dl>';
								//手数料
								//$charge = $val['fee'][0] * CHARGE + SYSTEM_FEE;
								if( $payment_type == 'クレジットカード' ){
									$charge = $common_charge['クレジットカード'];
								}else{
									$charge = $common_charge['コンビニ/ペイジー'];
								}
								echo '<dl class="cf">';
								echo '<dt>手数料</dt>';
								echo '<dd>&yen;'.number_format($charge).'</dd>';
								echo '</dl>';
								echo '<dl class="cf">';
								echo '<dt>合計金額</dt>';
								echo '<dd>&yen;'.number_format($val['fee'][0]+$charge).'</dd>';
								echo '</dl>';
								echo '<dl class="cf">';
								echo '<dt>決済URL</dt>';
								echo '<dd><a href="'.$num_res['data'][$key_sub]['payment_link'][0].'" target="_blank">決済ページはこちら</a></dd>';
								echo '</dl>';
							}
						}
					}
				}
			?>
			</div>

			<!--<p>【印刷】</p>-->
			<div class="form_history">
			<div class="form_title">
				<dl>
					<dt>印刷</dt>
				</dl>
			</div>
			<!-- <dl class="cf">
				<dt>入学願書</dt>
				<dd><a href="P_gansho.php"><img  class="dl" src="img/demo/ic_system_update_alt_black_24dp_2x.png" alt="ダウンロード"></a></dd>
				</dl> -->
				<?php
				if( date("Y/m/d",strtotime($date_start)) <= date("Y/m/d") ){
					echo '<dl class="cf">';
					echo '<dt>入学願書</dt>';
					echo '<dd><a href="P_gansho.php"><img  class="dl" src="img/demo/ic_system_update_alt_black_24dp_2x.png" alt="ダウンロード"></a></dd>';
					echo '</dl>';
				}
				if( $FLG_suisensho == 1 ){
					echo '<dl class="cf">';
					echo '<dt>推薦書</dt>';
					echo '<dd><a href="P_suisensho.php"><img  class="dl" src="img/demo/ic_system_update_alt_black_24dp_2x.png" alt="ダウンロード"></a></dd>';
					echo '</dl>';
				}
				if( $FLG_jikosuisensho == 1 ){
					echo '<dl class="cf">';
					echo '<dt>自己推薦書</dt>';
					echo '<dd><a href="P_jikosuisensho.php"><img  class="dl" src="img/demo/ic_system_update_alt_black_24dp_2x.png" alt="ダウンロード"></a></dd>';
					echo '</dl>';
				}
				if( $num_res['data'][$key_sub]['_flg_receipt'][0] == 1 ){
					echo '<dl class="cf">';
					echo '<dt>受験票/調査書受領書</dt>';
					echo '<dd><a href="P_jukenhyo.php"><img class="dl" src="img/demo/ic_system_update_alt_black_24dp_2x.png" alt="ダウンロード"></a></dd>';
					echo '</dl>';
				}

				?>
			</dl>
		</div>
		<div class="buttons cf">
			<!--2016.07.25　服部　マイページメニューに戻るよう修正-->
			<input type="button" class="lf back_btn" value="戻る"  onclick="location.href='mymenu.php'" />
			<!-- <input type="button" class="lf back_btn" value="戻る"  onclick="location.href='top.php'" /> -->
		</div>
<!-- ////////////////////////////////////////////////////////////////////////////// -->
		<div class="form_history">
			<div class="form_title">
				<dl>
					<dt>受験者情報</dt>
				</dl>
			</div>
			<dl class="cf">
				<dt>氏名(姓・名)</dt>
				<dd><?php echo $print_info['name'][0];?></dd>
			</dl>
			<dl class="cf">
				<dt>氏名フリガナ(セイ・メイ)</dt>
				<dd><?php echo $print_info['name_read'][0];?></dd>
			</dl>

			<dl class="cf">
				<dt>生年月日</dt>
				<dd><?php echo DateUSToENG($print_info['date_of_birth'][0]); ?></dd>
			</dl>
			<dl class="cf">
				<dt>電話番号</dt>
				<dd><?php echo $print_info['tel'][0]; ?></dd>
			</dl>

			<dl class="cf">
				<dt>郵便番号</dt>
				<dd><?php echo '〒'.$print_info['add_zip_code'][0]; ?></dd>
			</dl>
			<dl class="cf">
				<dt>都道府県</dt>
				<dd><?php echo $print_info['add_prefecture'][0]; ?></dd>
			</dl>
			<dl class="cf">
				<dt>市区町村</dt>
				<dd><?php echo $print_info['add_city'][0]; ?></dd>
			</dl>
			<dl class="cf">
				<dt>町名番地等</dt>
				<dd id="add_street"><?php echo $print_info['add_street'][0]; ?></dd>
			</dl>

			<!-- 平井 マンション名追加 -->
			<dl class="cf">
				<dt>マンション名</dt>
				<dd id="add_apartment"><?php echo $print_info['add_apartment'][0]; ?></dd>
			</dl>
			<!-- 平井 マンション名追加 -->


			<dl class="cf">
				<dt>中学校</dt>
				<dd><?php echo $print_info['old_school'][0]; ?></dd>
			</dl>
			<dl class="cf">
				<dt>卒業見込み</dt>
				<dd>
				<?php
				echo $print_info['graduate'][0];
				?>
				</dd>
			</dl>
			<dl class="cf">
				<dt>中学校電話番号</dt>
				<dd><?php echo $print_info['old_school_tel'][0]; ?></dd>
			</dl>
			<dl class="cf">
				<dt>保護者氏名(姓・名)</dt>
				<dd><?php echo $print_info['name_grd'][0];?></dd>
			</dl>
			<dl class="cf">
				<dt>保護者フリガナ(セイ・メイ)</dt>
				<dd><?php echo $print_info['name_grd_read'][0];?></dd>
			</dl>
			<dl class="cf">
				<dt>続柄</dt>
				<dd><?php echo $print_info['family_relationship'][0]; ?></dd>
			</dl>


			<?php
			if ( empty($print_info['grd_add_prefecture'][0]) && empty($print_info['grd_add_city'][0]) && empty($print_info['grd_add_street'][0]) ) {
				echo '<dl class="cf"><dt>保護者住所</dt><dd>志願者に同じ</dd></dl>';
			}else {
				echo '<dl class="cf"><dt>保護者　郵便番号</dt><dd>'.'〒'.$print_info['grd_add_zip_code'][0].'</dd></dl>';
				echo '<dl class="cf"><dt>保護者　都道府県</dt><dd>'.$print_info['grd_add_prefecture'][0].'</dd></dl>';
				echo '<dl class="cf"><dt>保護者　市区町村</dt><dd>'.$print_info['grd_add_city'][0].'</dd></dl>';
				echo '<dl class="cf"><dt>保護者　町名番地</dt><dd id='.'"grd_add_street"'.'>'.$print_info['grd_add_street'][0].'</dd></dl>';

				// 2017.4.27 平井追加
				echo '<dl class="cf"><dt>保護者　マンション名</dt><dd>'.$print_info['grd_add_apartment'][0].'</dd></dl>';
				// 2017.4.27 平井追加

			}
			?>

			<!-- <dl class="cf">
				<dt>第1志望日程</dt>
				<dd><?php // echo $print_info['MA_school_exam_date_01'][0]; ?></dd>
			</dl> -->

			<!-- <dl class="cf">
				<dt>第1志望校名</dt>
					<dd><?php // echo $print_info['MA_school_01'][0]; ?></dd>
			</dl> -->

			<!-- <dl class="cf">
				<dt>第2志望日程</dt>
				<dd><?php // echo $print_info['MA_school_exam_date_02'][0]; ?></dd>
			</dl> -->

			<!-- <dl class="cf">
				<dt>第2志望校名</dt>
					<dd><?php // echo $print_info['MA_school_02'][0]; ?></dd>
			</dl> -->


			<dl class="cf">
				<dt>メールアドレス</dt>
				<dd><?php echo $print_info['mail'][0]; ?></dd>
			</dl>
		</div>



<!-- ////////////////////////////////////////////////////////////////////////////// -->
		<div class="buttons cf">
			<!--2016.07.25　服部　マイページメニューに戻るよう修正-->
			<input type="button" class="lf back_btn" value="戻る"  onclick="location.href='mymenu.php'" />
			<!-- <input type="button" class="lf back_btn" value="戻る"  onclick="location.href='top.php'" /> -->
		</div>

	</div>
</div>
<!-- main_contents ▲ -->

<!-- footer ▼ -->
<?php include ('common/footer.php'); ?>
<!-- footer ▲ -->
<script>
(function (){
	$(function(){
		//住所(町名番地)の長さに応じて文字サイズを変更
		var len = $("#add_street").text().length;
		if( len > 20 ){
			$("#add_street").css('font-size', "14px");
		}
		//保護者住所(町名番地)の長さに応じて文字サイズを変更
		if($("#grd_add_street").length){
			var len = $("#grd_add_street").text().length;
			if(len > 20){
				$("#grd_add_street").css('font-size', "14px");
			}
		}
	});
})()
</script>
</body>
</html>
