<?php

// 登録データ取得
$data_list = $action->getPhysicalDataList($user_id);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>フィジカルデータ一覧</title>
</head>
<body>
<h1>フィジカルデータ一覧</h1>
<!-- 投稿表示エリア -->
<?php if (!empty($data_list)) {?>
	<table width="100%" border="1">
		<tr>
			<th>内容</th>
			<th>日付</th>
			<th>編集</th>
			<th>削除</th>
		</tr>
		<?php foreach ($data_list as $post) { ?>
			<tr>
				<td><?php if (!empty($post["email"])) {?><a href="mailto:<?php echo $post["email"];?>"><?php } ?><?php echo $post["name"];?><?php if (!empty($post["email"])) {?></a><?php } ?></td>
				<td><?php echo mb_substr($post["body"], 0,  15);?>..</td>
				<td><?php echo $post["created_at"];?></td>
				<td align="center" valign="middle">
					<form action="./index.php" method="post">
						<input type="hidden" name="user_id" value="edit">
						<input type="hidden" name="data_id" value="<?php echo $post["data_id"];?>">
						<input type="submit" value="変更">
					</form>
				</td>
				<td align="center" valign="middle">
					<form action="./index.php" method="post">
						<input type="hidden" name="user_id" value="delete">
						<input type="hidden" name="data_id" value="<?php echo $post["data_id"];?>">
						<input type="submit" value="削除">
					</form>
				</td>
			</tr>
		<?php } ?>
	</table>
<?php } ?>
<!-- // 投稿表示エリア -->
<hr>
<p><a href="./">一覧に戻る</a></p>
</body>
</html>
