<?php
    $user = "mugen";
    $pass = "19860107mugen19530126";
    $db = "octa_admin";
    $host = "localhost";
    $table = "sites";
    $pod = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pod->query('SET NAMES utf8');
     
    $user = "root";
    $pass = "19860107mugen19530126";
    $db = "octa";
    $host = "133.242.236.103";
    $pod_mugen = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pod_mugen->query('SET NAMES utf8');
 
    #$row = $pod->query("SELECT rss from $table WHERE test = 0 LIMIT 1");
    require_once('/home/mugen/octa/SimpleHTMLDOMParser/simple_html_dom.php');
    mb_language("Japanese");  
     foreach($pod->query("SELECT name,url,rss FROM sites WHERE flag = 0") as $row) {
        $name = $row[name];
        $url = $row[url];
        $rss = $row[rss];

        $source = simplexml_load_file($rss);


        foreach($source->channel->item as $item){
            $title = $item->title;
            $title = str_replace("\"", "&quot;", $title);
            $title = str_replace("\'", "&#39;", $title);
            $link = $item->link;
            $pubDate = $item->pubDate;

            $date = date('Y-m-d H:i:s', strtotime("$pubDate"));

            $day = date('Ymd', strtotime("$pubDate"));
            $ym = date('Ym', strtotime("$pubDate"));
            echo "$title<br>$link<br>";


 
            $check = str_get_html($link);
            $html = file_get_html($link);
            if (strpos($html, "og:image") === FALSE){
                continue;
            }
            foreach( $html->find( 'meta[property=og:image]' ) as $meta ){
                $image = $meta->content;
            } 
            
            foreach( $html->find( 'meta[property=og:description]' ) as $meta ){
                $description = $meta->content;
            }  
           
            
            echo "$image<br>$description<br>$date<br><br>";
            if($link){
                foreach($pod->query("SELECT no FROM science WHERE link LIKE \"$link\" LIMIT 1") as $row) {
                     $no = $row[no];
                }
             }
             if(!$no){
                #$pod_mugen->query("INSERT INTO main(name,url,title,link,image,date) VALUES (\"$name\",\"$url\",\"$title\",\"$link\",\"$image\",\"$date\")");
                $pod->query("INSERT INTO science(name,url,title,link,description,image,date) VALUES (\"$name\",\"$url\",\"$title\",\"$link\",\"$description\",\"$image\",\"$date\")");
            }
            $no = "";
        }
        #echo "$name";


    }

    $mecab = new MeCab_Tagger();
    $str = "「ゴミ収集のUber」が、ゴミビジネスに変革を起こす";
    $nodes = $mecab->parseToNode($str);
    foreach($nodes as $node) {
        if ($node->getStat() == 2 || $node->getStat() == 3) continue;
        echo "id=".$node->getId()."<br />";
        echo "surface=".$node->getSurface()."<br />";
        echo "stat=".$node->getStat()."<br />";
        echo "length=".$node->getLength()."<br />";
        echo "feature=".$node->getFeature()."<br />";
    }

    $json_str = '[[{"title":"title1"},{"link":"link1"},{"image":"image1"}],[{"title":"title2"},{"link":"link2"},{"image":"image2"}]]';
         

    var_dump(json_decode($json_str));        
 #       echo $source;
?>