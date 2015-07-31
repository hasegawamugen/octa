<?php
session_start();

if($_POST["site"]){

	$site = $_POST["site"];
	$url = $_POST["url"];
	$rss = $_POST["rss"];
	$html = $_POST["html"];

	add($site,$url,$rss,$html);

}else if(($_SESSION["user"]) && ($_SESSION["pass"])){
echo <<< EOM
	<html>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<head>
	<title></title>
	</head>
	<body>
	<form action="site_add.php" method="POST">
	サイト名：<br><input type="text" name="site" size="40"><br>
	URL：<br><input type="text" name="url" size="40"><br>
	RSS：<br><input type="text" name="rss" size="40"><br>
	HTML：<br><input type="text" name="html" size="40"><br>
	<input type="submit" value="add">
	</form>
	</body>
	</html>
EOM;

}else if(empty($_SERVER["PHP_AUTH_USER"])){
	header('WWW-Authenticate: Basic realm="Privare"');
	header("HTTP/1.0 401 Unauthorizad");
	header('Content-Type: text/html; charset=UTF-8');
	echo "このページを見るにはログインが必要です";
}else if($_SERVER["PHP_AUTH_USER"] == "mugen"){
	if($_SERVER["PHP_AUTH_PW"] == "mugen19260327"){
		$_SESSION["user"] = $_SERVER["PHP_AUTH_USER"];
		$_SESSION["pass"] = $_SERVER["PHP_AUTH_PW"];
		header('Location: ./site_add.php');
	}else{
		header('WWW-Authenticate: Basic realm="Privare"');
		header("HTTP/1.0 401 Unauthorizad");
		header('Content-Type: text/html; charset=UTF-8');
		echo "ユーザー名またはパスワードが正しくありません";
	}
}else{
	header('WWW-Authenticate: Basic realm="Privare"');
	header("HTTP/1.0 401 Unauthorizad");
	header('Content-Type: text/html; charset=UTF-8');
	echo "ユーザー名またはパスワードが正しくありません";
}


?>

<?php
function add($site,$url,$rss,$html) {
	$user = "mugen";
	$pass = "mugen19260327";
	$db = "octa_admin";
	$host = "localhost";
	$pod = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
	$pod->query('SET NAMES utf8');


	if($rss){
		foreach($pod->query("SELECT no FROM it_crowl_rss WHERE rss LIKE \"$rss\" LIMIT 1") as $row) {
			$flag = $row[no];
		}
		if(!$flag){
			$pod->query("INSERT INTO it_crowl_rss(name,rss) VALUE(\"$site\",\"$rss\")");
			$crowl = "rss";
			foreach($pod->query("SELECT no FROM it_crowl_rss WHERE rss LIKE \"$rss\" LIMIT 1") as $row) {
				$no = $row[no];
			}
			foreach($pod->query("SELECT no FROM it_site_all WHERE url LIKE \"$url\" LIMIT 1") as $row) {
				$flag2 = $row[no];
			}
			if(!$flag2){
				$pod->query("INSERT INTO it_site_all(name,url,crowl,crowl_num) VALUE(\"$site\",\"$url\",\"$crowl\",\"$no\")");
			}
			foreach($pod->query("SELECT no FROM it_site_all WHERE url LIKE \"$url\" LIMIT 1") as $row) {
				$all_num = $row[no];
			}
			$pod->query("UPDATE it_crowl_rss SET all_num=\"$all_num\" WHERE no=\"$no\"");
		}
	}else if($html){
		foreach($pod->query("SELECT no FROM it_crowl_html WHERE html LIKE \"$html\" LIMIT 1") as $row) {
			$flag = $row[no];
		}
		if(!$flag){
			$pod->query("INSERT INTO it_crowl_html(name,html) VALUE(\"$site\",\"$html\")");
			$crowl = "html";
			foreach($pod->query("SELECT no FROM it_crowl_html WHERE html LIKE \"$html\" LIMIT 1") as $row) {
				$no = $row[no];
			}
			foreach($pod->query("SELECT no FROM it_site_all WHERE url LIKE \"$url\" LIMIT 1") as $row) {
				$flag2 = $row[no];
			}
			if(!$flag2){
				$pod->query("INSERT INTO it_site_all(name,url,crowl,crowl_num) VALUE(\"$site\",\"$url\",\"$crowl\",\"$no\")");
			}
			foreach($pod->query("SELECT no FROM it_site_all WHERE url LIKE \"$url\" LIMIT 1") as $row) {
				$all_num = $row[no];
			}
			$pod->query("UPDATE it_crowl_html SET all_num=\"$all_num\" WHERE no=\"$no\"");
		}
	}

	echo $site . "<br>" . $url . "<br>" . $rss . "<br>" . $html . "<br>";

	if($flag){
		echo "既に登録されてるよ";
	}

}

?>
