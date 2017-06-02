<?php

include_once("./library/htmlSQL/snoopy.class.php");
include_once("./library/htmlSQL/htmlsql.class.php");
include_once("./library/simplehtmldom_1_5/simple_html_dom.php");

$url = 'http://sportexpress.org';
$login = 'ceo@fitmepro.ru';
$passwd = 'Fitme321';
$countForCounts = isset($argv[1]) ? $argv[1] : 0;

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
    $wsql = new htmlsql();
    if (!$wsql->connect('string', $ItemLinks)){
        print 'Error while connecting: ' . $wsql->error;
        exit;
    }
    if (!$wsql->query('SELECT id, text FROM table')){
        print "Query error: " . $wsql->error;
        exit;
    }
    if ($link !== '/shop.php?op=price&vid=200') {
        foreach($wsql->fetch_objects() as $keyObg => $obj) {
            if ($obj->id) {
                $sub_wsql = new htmlsql();
                $sub_wsql->connect('string', $obj->text);

                if (!$sub_wsql->query('SELECT * FROM *')) {
                    print "Query error: " . $wsql->error;
                    exit;
                }

                $sub_wsql->convert_tagname_to_key();
                $item = $sub_wsql->fetch_array();
                $html = new simple_html_dom();
                $html->load($item["tbody"]["text"]);

                //Наименование
                $arrayForDescriptionElements = [];
                foreach ($html->find('tr[id] td[class=descr_good] a') as $key => $element) {
                    $arrayForDescriptionElements[] = preg_replace("/ {2,}/", " ", str_replace(array('Go', '&quot;', 'Hard', 'Home', 'or', '&amp;'), '', $element->innertext));
                }
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

//Отправка письма
//mail("intro333@ya.ru", "VPLAB", "Line 1\nLine 2\nLine 3");

//var_dump($AllData);
curl_close($curl);
?>