<?php

// 登録データ取得
$post_datas = $action->getPhysicalDataList($user_id);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>フィジカルデータ</title>
</head>
<body>
<h1>フィジカルデーター</h1>
<!-- 入力エリア -->
<div class="input_area">
	<form action="./index.php" method="post" id="contact_form">
		<dl class="weight">
			<dt>体重</dt>
			<dd><input type="text" name="weight" value=""></dd>
		</dl>
		<dl class="fat_percentage">
			<dt>体脂肪</dt>
			<dd><input type="text" name="fat_percentage" value=""></dd>
		</dl>
		<dl class="muscle_mass">
			<dt>筋肉量</dt>
			<dd><textarea name="muscle_mass"></textarea></dd>
		</dl>
		<dl class="water_content">
			<dt>体水分量</dt>
			<dd><input type="text" name="water_content" value=""></dd>
		</dl>
		<dl class="visceral_fat">
			<dt>内臓脂肪</dt>
			<dd><input type="text" name="visceral_fat" value=""></dd>
		</dl>
		<dl class="basal_metabolic_rate">
			<dt>基礎代謝量</dt>
			<dd><input type="text" name="basal_metabolic_rate" value=""></dd>
		</dl>
		<dl class="bmi">
			<dt>BMI</dt>
			<dd><input type="text" name="bmi" value=""></dd>
		</dl>
		<input type="hidden" name="user_Id" value="save">
		<input type="submit" value="送信">
	</form>
</div>
<!-- //入力エリア -->
<hr>
<!-- 投稿表示エリア -->
<?php if (!empty($post_datas)) {?>
	<div class="list">
		<?php foreach ($post_datas as $post) { ?>
			<div class="item">
				<div class="weight"><?php echo nl2br($post["weight"]);?></div>
				<div class="fat_percentage"><?php echo nl2br($post["fat_percentage"]);?></div>
				<div class="muscle_mass"><?php echo nl2br($post["muscle_mass"]);?></div>
				<div class="water_content"><?php echo nl2br($post["water_content"]);?></div>
				<div class="visceral_fat"><?php echo nl2br($post["visceral_fat"]);?></div>
				<div class="basal_metabolic_rate"><?php echo nl2br($post["basal_metabolic_rate"]);?></div>
				<div class="bmi"><?php echo nl2br($post["bmi"]);?></div>
				<div class="date"><?php echo $post["created_at"];?></div>
			</div>
		<?php } ?>
	</div>
<?php } ?>
<!-- // 投稿表示エリア -->
<hr>
<p>ログインモード</p>
<!-- エラーエリア -->
<?php if (!empty($errm)) {?>
	<div class="error">
		<?php foreach($errm as $key => $value) {
			echo $value;
		}?>
	</div>
<?php }?>
<form action="./index.php" method="post">
	<input type="hidden" name="user_Id" value="login">
	<input type="user_name" name="user_name">
	<input type="password" name="password">
	<input type="submit" value="送信">
</form>
</body>
</html>
