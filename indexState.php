<?php

require_once 'library/simplehtmldom_1_5/simple_html_dom.php';

$url = 'http://sportexpress.org';
$login = 'ceo@fitmepro.ru';
$passwd = 'Fitme321';
// ниже авторизуемся на сайте со значением authenticity_token в переменной $token
$curl = curl_init(); // инициализируем cURL
/*Дальше устанавливаем опции запроса в любом порядке*/
//Здесь устанавливаем URL к которому нужно обращаться
curl_setopt($curl, CURLOPT_URL, $url . '/login.php');
//Настойка опций cookie
curl_setopt($curl, CURLOPT_COOKIEJAR, 'cook.txt');//сохранить куки в файл
curl_setopt($curl, CURLOPT_COOKIEFILE, 'cook.txt');//считать куки из файла
//устанавливаем наш вариат клиента (браузера) и вид ОС
curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36");
//Установите эту опцию в ненулевое значение, если вы хотите, чтобы PHP завершал работу скрыто, если возвращаемый HTTP-код имеет значение выше 300. По умолчанию страница возвращается нормально с игнорированием кода.
curl_setopt($curl, CURLOPT_FAILONERROR, 1);
//Устанавливаем значение referer - адрес последней активной страницы
curl_setopt($curl, CURLOPT_REFERER, $url . '/');
//Максимальное время в секундах, которое вы отводите для работы CURL-функций.
curl_setopt($curl, CURLOPT_TIMEOUT, 3);
curl_setopt($curl, CURLOPT_POST, 1); // устанавливаем метод POST
//ответственный момент здесь мы передаем наши переменные
//замените значения your_name и your_pass на соответственные значения Вашей учетной записи
curl_setopt($curl, CURLOPT_POSTFIELDS, $url . '/login.php?posted2=true&login=' . $login . '&password=' . $passwd . '&submit=1&formid=authfotm2');
//Установите эту опцию в ненулевое значение, если вы хотите, чтобы шапка/header ответа включалась в вывод.
curl_setopt($curl, CURLOPT_HEADER, 1);
//Внимание, важный момент, сертификатов, естественно, у нас нет, так что все отключаем
curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);// не проверять SSL сертификат
curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);// не проверять Host SSL сертификата
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);// разрешаем редиректы
$result = curl_exec($curl); // выполняем запрос и записываем в переменную

curl_setopt($curl, CURLOPT_URL, 'http://sportexpress.org/shop.php?op=price');
$animal = curl_exec($curl);

curl_close($curl); // заканчиваем работу curl

//подключаем PHP Simple HTML DOM Parser с сайта http://simplehtmldom.sourceforge.net
//require_once 'library/simplehtmldom_1_5/simple_html_dom.php';
//
//$html = str_get_html($result);

?>