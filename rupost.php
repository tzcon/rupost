<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Отслеживание ценных писем Почты России </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>	
<body>
<center>
<h2>Отслеживание ценных писем Почты России</h2>
<h4>О проекте: <a href="http://tzcon.github.com/rupost/" target="_blank">tzcon.github.com/rupost/</a></h4>
</center>
<hr>
<?php 
if ((empty($_GET['pid'])) || ((!preg_match('|^\d{14}$|',$_GET['pid'])) && (!preg_match('|^[A-Z]{2}\d{7}[A-Z]{2}$|',$_GET['pid']))))
	die("<center><h3>Укажите корректный pid почтового отправления!</h3></center></body></html>");
include('simple_html_dom.php');
$param=array(); 
$url='http://www.russianpost.ru/resp_engine.aspx?Path=rp/servise/ru/home/postuslug/trackingpo';
//download ruspost for parsing params 
$curl_opt=array(CURLOPT_URL=>$url,
				CURLOPT_RETURNTRANSFER=>1,
				CURLOPT_HEADER=>0,
				CURLOPT_FOLLOWLOCATION=>1,
				CURLOPT_CONNECTTIMEOUT=>10,				
				CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1)');
$ch=curl_init();
$html = new simple_html_dom();
curl_setopt_array($ch,$curl_opt);
if (!($html_str=curl_exec($ch))) timeout_error();  
$html=str_get_html($html_str);
//$html = file_get_html('./ruspost/ruspost.html');		
$form = $html->find("form",0);  
foreach ($form->children() as $input)   	  
    if ($input->type=='hidden') $param[]=$input->name."=".$input->value; 
$param[]='search1=';
$param[]='BarCode='.$_GET['pid'];
$param[]='searchsign=1';
$param[]='entryBarCode=';
//query result
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,implode('&',$param));
curl_setopt($ch, CURLOPT_REFERER, $url);		
if (!($html_str=curl_exec($ch))) timeout_error(); 
curl_close($ch); 

$html = str_get_html($html_str);
if (($table1 = $html->find("#PRINTBODY table",2)) && ($table2 = $html->find("#PRINTBODY table",3)))
{
	$table1->align="center";
	foreach ($table1->children() as $tr)
	 foreach ($tr->children() as $th)
	  $th->style="border-bottom:1px solid #ccc;";	  
	$table1->innertext='<tr valign="top"><th align="left"  style="border-bottom:1px solid #ccc;">Источник информации:</th><td  style="border-bottom:1px solid #ccc;"><a href="'.$url.'" target="_blank">Почта России</a></td></tr>'.$table1->innertext;   	
	echo $table1."\n";
	$table2->bgcolor='white';
	$table2->align='center';
	foreach ($table2->children() as $tr)
	 $tr->style=($tr->tag=="tbody")?"background-color:#ddd":"background-color:#ccc";
	$table2=preg_replace("|<a href=[^>]*?>|","",$table2);
	$table2=preg_replace("|class=\"[^\"]*?\"|","",$table2);
	echo $table2."\n";
}
else if ($err = $html->find(".red",0)->innertext)	
	echo "<center><h4><font color=red>$err</font></h4></center>";	
else 	
	echo"<center><h4><font color=red>Не удалось получить информацию о почтовом отправлении <b>{$_GET['pid']}</b>, возможно изменился формат вывода данных.</font><br>
	Источник информации:<a href=\"'.$url.'\" target=\"_blank\">Почта России</a></h4></center>";	
$html->clear;  
function timeout_error()
{global $url;
 die("<center><h4><font color=red>Сайт <a href=\"'.$url.'\" target=\"_blank\">Почты России</a> не ответил вовремя. Попробуйте повторить запрос позднее.</font></h4></center></body></html>");}
?>
</body>
</html>