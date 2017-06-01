<?php

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
curl_setopt($curl, CURLOPT_POSTFIELDS, $url . '/login.php?posted2=true&login=' . $login . '&password=' . $passwd . '&submit=1&formid=authfotm2');
curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

$result = curl_exec($curl);

curl_setopt($curl, CURLOPT_URL, 'http://sportexpress.org/shop.php?op=price');
$animal = curl_exec($curl);

//curl_close($curl);
include('library/simplehtmldom_1_5/simple_html_dom.php');

// Create a DOM object
//$dom = new simple_html_dom();
//echo "Список производителей от simple HTML DOM.\n";
//$dom->load($animal);


$html = file_get_html('http://sportexpress.org/');

$articles = $html->find('div[id=header]', 0);

print_r($articles);
?>

?>