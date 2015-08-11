<?php
    $user = "mugen";
    $pass = "mugen19260327";
    $db = "octa_it_admin";
    $host = "localhost";
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->query('SET NAMES utf8');
     
    require_once('/home/octa/SimpleHTMLDOMParser/simple_html_dom.php');
    mb_language("Japanese");

    $ym = date("Ym");
    $table = "it{$ym}";

    $g = date("G");
    if($g == 0){
        foreach($pdo->query("SELECT no FROM $table ORDER BY no ASC") as $row) {
            $table_flag = $row['no'];
        }
        if(!$table_flag){
            create_table($pdo,$table);
        }
    }

    $crowl_flag = crowl($pdo,$table);

    if(!$crowl_flag){
        $pdo->query("UPDATE it_rss SET flag=0 WHERE flag=1");
        crowl($pdo);
        exit;
    }

    function crowl($pdo,$table){
        $crowl_flag = false;

        foreach($pdo->query("SELECT no,name,url,rss,all_num FROM it_rss WHERE flag = 0") as $row) {
            $crowl_flag = true;
            $no = $name = $url = $rss = $all_num = "";

            $no = $row['no'];
            $name = $row['name'];
            $url = $row['url'];
            $rss = $row['rss'];
            $all_num = $row['all_num'];

            $pdo->query("UPDATE it_rss SET flag=1 WHERE no=\"$no\"");

            $feed = file_get_html($rss);

            if (strpos($feed, '<item')){
                $query = 'item';
            }else if(strpos($feed, '<entry')){
                $query = 'entry';
            }else{
                continue;
            }
            $i=0;
            foreach($feed->find($query) as $item){
                $flag = "";

                $items[$i] = $item->outertext;

                preg_match("/<title>(.*)<\/title>/i",$items[$i],$titleMatcher);
                $title[$i] = $titleMatcher[1];

                $title[$i] = str_replace("\"", "&quot;", $title[$i]);
                $title[$i] = str_replace("\'", "&#39;", $title[$i]);
                $title[$i] = str_replace("<![CDATA[", "", $title[$i]);
                $title[$i] = str_replace("]]>", "", $title[$i]);

                preg_match("/<link>(.*)<\/link>/i",$items[$i],$linkMatcher);
                if($linkMatcher[1]){
                    $rss_link[$i] = $linkMatcher[1];
                }else{
                    preg_match("/<link.*href=\"(.*?)\"/i",$items[$i],$linkMatcher);
                    $rss_link[$i] = $linkMatcher[1];
                }

                preg_match("/^(\/[^\/].*)/i",$rss_link[$i],$urlMatcher);
                if($urlMatcher[1]){
                    $rss_link[$i] = $url . $urlMatcher[1];
                }

                $rss_link[$i] = str_replace("\"", "&quot;", $rss_link[$i]);
                $rss_link[$i] = str_replace("\'", "&#39;", $rss_link[$i]);
                $rss_link[$i] = str_replace("<![CDATA[", "", $rss_link[$i]);
                $rss_link[$i] = str_replace("]]>", "", $rss_link[$i]);

                preg_match("/<pubdate>(.*)<\/pubdate>/i",$items[$i],$pubdateMatcher);
                if($pubdateMatcher[1]){
                    $pubdate[$i] = $pubdateMatcher[1];
                }else{
                    preg_match("/<dc:date>(.*)<\/dc:date>/i",$items[$i],$dcdateMatcher);
                    if($dcdateMatcher[1]){
                        $pubdate[$i] = $dcdateMatcher[1];
                    }else{
                        preg_match("/<published>(.*)<\/published>/i",$items[$i],$publishedMatcher);
                        $pubdate[$i] = $publishedMatcher[1];
                    }
                }

                $today_ymdhi = date("YmdHi");
                $today_ymdhi+=0;
                $one_hour = date('YmdHi', strtotime("- 1 hour"));

                $date = date('Y-m-d H:i:s', strtotime("$pubdate[$i]"));
                $day = date('Ymd', strtotime("$pubdate[$i]"));
                $ym = date('Ym', strtotime("$pubdate[$i]"));
                $ymdhi = date('YmdHi', strtotime("$pubdate[$i]"));
                $ymdhi+=0;

                $html = file_get_html($rss_link[$i]);

                if (strpos($html, 'og:url')){
                    foreach( $html->find( 'meta[property=og:url]' ) as $meta ){
                        $link[$i] = $meta->content;
                    }
                    if(!$link[$i]){
                        foreach( $html->find( 'meta[name=og:url]' ) as $meta ){
                            $link[$i] = $meta->content;
                        }
                    }
                }else{
                    continue;
                }

                if (strpos($html, 'og:image')){
                    foreach( $html->find( 'meta[property=og:image]' ) as $meta ){
                        $image[$i] = $meta->content;
                    }
                    if(!$image[$i]){
                        foreach( $html->find( 'meta[name=og:image]' ) as $meta ){
                            $image[$i] = $meta->content;
                        }
                    }
                }else{
                    continue;
                }

                if (strpos($html, 'og:description')){
                    foreach( $html->find( 'meta[property=og:description]' ) as $meta ){
                        $description[$i] = $meta->content;
                    }
                    if(!$description[$i]){
                        foreach( $html->find( 'meta[name=og:description]' ) as $meta ){
                            $description[$i] = $meta->content;
                        }
                    }
                }else{
                    continue;
                }

                $description[$i] = str_replace("\"", "&quot;", $description[$i]);
                $description[$i] = str_replace("\'", "&#39;", $description[$i]);
                $description[$i] = str_replace("<![CDATA[", "", $description[$i]);
                $description[$i] = str_replace("]]>", "", $description[$i]);
                
                #$pattern = '/[^a-zA-Z0-9]/';
                #$replacement = '';
                #$link_code = preg_replace($pattern, $replacement, $link[$i]);
                #$link_code = mb_convert_encoding($link_code, "UTF-8", "auto");

                if(($title[$i]) && ($link[$i]) && ($pubdate[$i]) && ($image[$i]) && ($description[$i])){
                    foreach($pdo->query("SELECT no FROM $table WHERE title LIKE \"$title[$i]\"") as $row2){
                        $flag = $row2['no'];
                    }
                    if(!$flag){
                        $pdo->query("INSERT INTO $table(name,url,title,link,description,image,pubdate,date) VALUES (\"$name\",\"$url\",\"$title[$i]\",\"$link[$i]\",\"$description[$i]\",\"$image[$i]\",\"$ymdhi\",\"$today_ymdhi\")");
                        $pdo->query("UPDATE it_all SET date=\"$today_ymdhi\" WHERE no=\"$all_num\"");
                        echo $table;
                    }else{
                        break;
                    }
                }
                $i++;
            }#foreach feed
        }#foreach rss
        return $crowl_flag;
    }#function crowl


    function create_table($pdo,$table){
        $pdo->query("CREATE TABLE $table(
            no INT(11) NOT NULL AUTO_INCREMENT ,
            name VARCHAR(100) DEFAULT NULL ,
            url VARCHAR(100) DEFAULT NULL ,
            title VARCHAR(100) DEFAULT NULL ,
            link VARCHAR(100) DEFAULT NULL ,
            description TEXT,
            image VARCHAR(100) DEFAULT NULL ,
            pubdate BIGINT(20) DEFAULT 0,
            date BIGINT(20) DEFAULT 0,
            flag INT(11) DEFAULT 0,
            kw_flag INT(11) DEFAULT 0,
            main_kw VARCHAR(30) DEFAULT NULL ,
            kw_score INT(11) DEFAULT 0,
            tw_flag INT(11) DEFAULT 0,
            tw_count INT(11) DEFAULT 0,
            tw_ffAvr INT(11) DEFAULT 0,
            fb_flag INT(11) DEFAULT 0,
            fb_count INT(11) DEFAULT 0,
            pk_flag INT(11) DEFAULT 0,
            pk_count INT( 11 ) DEFAULT  '0',
            PRIMARY KEY (no))");
    }
    #echo $source;
?>