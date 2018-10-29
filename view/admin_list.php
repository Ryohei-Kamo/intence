<?php

// 登録データ取得
$post_datas = $action->getDbPostData();

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>管理モードやんなー</title>
</head>
<body>
<h1>管理モードやんなーー</h1>
<!-- 投稿表示エリア -->
<?php if (!empty($post_datas)) {?>
	<table width="100%" border="1">
		<tr>
			<th>名前</th>
			<th>内容</th>
			<th>日付</th>
			<th>編集</th>
			<th>削除</th>
		</tr>
		<?php foreach ($post_datas as $post) { ?>
			<tr>
				<td><?php if (!empty($post["email"])) {?><a href="mailto:<?php echo $post["email"];?>"><?php } ?><?php echo $post["name"];?><?php if (!empty($post["email"])) {?></a><?php } ?></td>
				<td><?php echo mb_substr($post["body"], 0,  15);?>..</td>
				<td><?php echo $post["created_at"];?></td>
				<td align="center" valign="middle">
					<form action="./index.php" method="post">
						<input type="hidden" name="eventId" value="edit">
						<input type="hidden" name="id" value="<?php echo $post["id"];?>">
						<input type="submit" value="変更">
					</form>
				</td>
				<td align="center" valign="middle">
					<form action="./index.php" method="post">
						<input type="hidden" name="eventId" value="delete">
						<input type="hidden" name="id" value="<?php echo $post["id"];?>">
						<input type="submit" value="削除">
					</form>
				</td>
			</tr>
		<?php } ?>
	</table>
<?php } ?>
<!-- // 投稿表示エリア -->
<hr>
<p><a href="./">掲示板に戻る</a></p>
</body>
</html>
