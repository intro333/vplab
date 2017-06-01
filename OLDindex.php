<?php
//подгружаем библиотеку
require_once 'library/simplehtmldom_1_5/simple_html_dom.php';

$username='ceo@fitmepro.ru';
$password='Fitme321';
$URL='http://sportexpress.org/shop.php?op=price&vid=45';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$URL);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
$result=curl_exec ($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
curl_close ($ch);
echo $status_code;
//создаём новый объект
$html = new simple_html_dom();
//загружаем в него данные
//$html = file_get_html('http://sportexpress.org/vendors/vp_Laboratory/');
$html = str_get_html($result);
echo $html;
//находим все ссылки на странице и...
//if($html->innertext!='') {
//    foreach($html->find('div[id=outbox_unit_p]') as $a){
//        echo $a;
//    }
//}
//освобождаем ресурсы
$html->clear();
unset($html);
?>