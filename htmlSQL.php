<?php

include_once("./library/htmlSQL/snoopy.class.php");
include_once("./library/htmlSQL/htmlsql.class.php");
include_once("./library/simplehtmldom_1_5/simple_html_dom.php");

$url = 'http://sportexpress.org';
$login = 'ceo@fitmepro.ru';
$passwd = 'Fitme321';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url . '/login.php');
curl_setopt($curl, CURLOPT_COOKIEJAR, 'cook.txt');
curl_setopt($curl, CURLOPT_COOKIEFILE, 'cook.txt');
curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36");
curl_setopt($curl, CURLOPT_FAILONERROR, 1);
curl_setopt($curl, CURLOPT_REFERER, $url . '/');
curl_setopt($curl, CURLOPT_TIMEOUT, 3);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $url . '/login.php?posted2=true&login=' . $login . '&password=' . $passwd . '&submit=1&formid=authfotm2');
curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

$result = curl_exec($curl);

curl_setopt($curl, CURLOPT_URL, $url . '/shop.php?op=price');
$allLinks = curl_exec($curl);

//HTMLSQL
$wsql = new htmlsql();
if (!$wsql->connect('string', $allLinks)){
    print 'Error while connecting: ' . $wsql->error;
    exit;
}
//Получаем все ссылки брендов
if (!$wsql->query('SELECT href FROM a WHERE $class == "ancLinks2"')){
    print "Query error: " . $wsql->error;
    exit;
}
//file_put_contents('1.txt', $animal);
$links = [];
foreach($wsql->fetch_array() as $row){
    $links[] = $row['href'];
}

foreach($links as $link){
    curl_setopt($curl, CURLOPT_URL, $url . $link);
    $animal = curl_exec($curl);
    //Формируем table
    $wsql = new htmlsql();
    if (!$wsql->connect('string', $animal)){
        print 'Error while connecting: ' . $wsql->error;
        exit;
    }
    if (!$wsql->query('SELECT id, text FROM table')){
        print "Query error: " . $wsql->error;
        exit;
    }
    $arraItem = [];
    foreach($wsql->fetch_objects() as $obj){
        if($obj->id) {
            $sub_wsql = new htmlsql();
            $sub_wsql->connect('string', $obj->text);

            if (!$sub_wsql->query('SELECT * FROM *')){
                print "Query error: " . $wsql->error;
                exit;
            }

            $sub_wsql->convert_tagname_to_key();
            $item = $sub_wsql->fetch_array();
            $html = new simple_html_dom();
            $html->load($item["tbody"]["text"]);
//            var_dump($item["tbody"]["text"]);
            foreach($html->find('tr[id] td[class=descr_good] a') as $element) {
                echo $element->href;
                exit;
            }
        }
    }


    exit;
}
curl_close($curl);
?>