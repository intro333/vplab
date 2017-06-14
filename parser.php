<?php

include_once("./library/htmlSQL/snoopy.class.php");
include_once("./library/htmlSQL/htmlsql.class.php");
include_once("./library/simplehtmldom_1_5/simple_html_dom.php");

$url = 'http://sportexpress.org';
$login = 'ceo@fitmepro.ru';
$passwd = 'Fitme321';
$countForCounts = isset($_POST["counts"]) ? $_POST["counts"] : 0;

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

$AllData = [];

foreach($links as $linkKey => $link){
//    var_dump($linkKey);
//    exit;
    curl_setopt($curl, CURLOPT_URL, $url . $link);
    $ItemLinks = curl_exec($curl);
    //Формируем table
    $wsql2 = new htmlsql();
    if (!$wsql2->connect('string', $ItemLinks)){
        print 'Error while connecting: ' . $wsql2->error;
        exit;
    }
    if (!$wsql2->query('SELECT id, text FROM table')){
        print "Query error: " . $wsql2->error;
        exit;
    }
//    if ($link !== '/shop.php?op=price&vid=200') {
    if ($link == '/shop.php?op=price&vid=45') {
        foreach($wsql2->fetch_array() as $obj) {
            if ($obj['id']) {

                $html = new simple_html_dom();
                $html->load($obj['text']);

                //Наименование
                $arrayForDescriptionElements = [];
                foreach ($html->find('tbody tr[id] td[class=descr_good] a') as $key => $element) {
                    $arrayForDescriptionElements[] = preg_replace("/ {2,}/", " ", str_replace(array('Go', '&quot;', 'Hard', 'Home', 'or', '&amp;'), '', $element->innertext));
                }
//                var_dump($arrayForDescriptionElements);
//                exit;
                //Артикул
                $arrayForArticleElements = [];
                foreach ($html->find('tr[id]') as $key => $tr) {
                    $htmlTd = new simple_html_dom();
                    $htmlTd->load($tr);
                    foreach ($htmlTd->find('td') as $index => $td) {
                        if ($index == '2')
                            $arrayForArticleElements[] = $td->innertext;
                    }
                }
                //Количество(Запас)
                $arrayForCountElements = [];
                foreach ($html->find('tr[id]') as $key => $tr) {
                    $htmlTd = new simple_html_dom();
                    $htmlTd->load($tr);
                    foreach ($htmlTd->find('td') as $index => $td) {
                        if ($index == '3') {
                            $htmlDiv = new simple_html_dom();
                            $htmlDiv->load($td);
                            if ($htmlDiv->find('div')) {
                                foreach ($htmlDiv->find('div') as $indexDiv => $div) {
                                    if ($indexDiv == '0') {
                                        $arrayForCountElements[] = preg_replace("/[^0-9]/", '', $div->innertext);
                                    }
                                    if ($indexDiv == '1') {
                                        $arrayForCountElements[count($arrayForCountElements) - 1] = preg_replace("/[^0-9]/", '', $div->innertext);
                                    }
                                }
                            } else {
                                $arrayForCountElements[] = 'Нет данных';
                            }
                        }
                    }
                }
                //Цена
                $arrayForPriceElements = [];
                foreach ($html->find('tr[id] td[class=opt_sale_box] div') as $key => $td) {
                    $arrayForPriceElements[] = preg_replace("/[^0-9]/", '', trim($td->innertext));
                }

                //Структурируем данные для Excel
                foreach ($arrayForDescriptionElements as $key => $item) {
                    if(array_key_exists($key, $arrayForCountElements)) {
                        if ($arrayForCountElements[$key] >= $countForCounts) {
                            $AllData[] = [
                                'description' => $item,
                                'article' => array_key_exists($key, $arrayForArticleElements) ? $arrayForArticleElements[$key] : 'Нет данных',
                                'count' => array_key_exists($key, $arrayForCountElements) ? $arrayForCountElements[$key] : 'Нет данных',
                                'price' => array_key_exists($key, $arrayForPriceElements) ? $arrayForPriceElements[$key] : 'Нет данных'
                            ];
                        }
                    } else {
                        $AllData[] = [
                            'description' => $item,
                            'article' => array_key_exists($key, $arrayForArticleElements) ? $arrayForArticleElements[$key] : 'Нет данных',
                            'count' => array_key_exists($key, $arrayForCountElements) ? $arrayForCountElements[$key] : 'Нет данных',
                            'price' => array_key_exists($key, $arrayForPriceElements) ? $arrayForPriceElements[$key] : 'Нет данных'
                        ];
                    }
                }
            }
        }
    }
}

//Сохранение в excel
require_once './library/PHPExcel-1.8.1/Classes/PHPExcel.php';

$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Description');
$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Article');
$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Count');
$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Price');

foreach ($AllData as $key => $item) {
    $objPHPExcel->getActiveSheet()->SetCellValue('A'.($key+2), $item['description']);
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.($key+2), $item['article']);
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.($key+2), $item['count']);
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.($key+2), $item['price']);
}

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('vplab.xlsx');

curl_close($curl);
/*
//Отправка письма
$to = $_POST["email"];
$from = "ra@fitmepro.ru";
$subject = "VPLAB excel info";
$message = "VPLAB excel info.";

$attachment = chunk_split(base64_encode(file_get_contents('vplab.xlsx')));
$filename = 'vplab';
$filetype = 'xlsx';

$boundary = md5(date('r', time())); // рандомное число

$headers = "From: " . $from . "\r\n";
$headers .= "Reply-To: " . $from . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"_1_$boundary\"";

$message="
--_1_$boundary
Content-Type: multipart/alternative; boundary=\"_2_$boundary\"

--_2_$boundary
Content-Type: text/plain; charset=\"utf-8\"
Content-Transfer-Encoding: 7bit

$message

--_2_$boundary--
--_1_$boundary
Content-Type: \"$filetype\"; name=\"$filename\"
Content-Transfer-Encoding: base64
Content-Disposition: attachment // содержимое является вложенным

$attachment
--_1_$boundary--";

mail($to, $subject, $message, $headers);*/
echo '<a href="/index.html">Back</a>';

?>