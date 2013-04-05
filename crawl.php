<?php
/*
	First  :    SYNC using the bash script
	Second :    php lang.php webstore_flagfingerbooks it_it_fingerbooks es_es_fingerbooks
	Third  :    get the results txt files

	php lang.php webstore_flagfingerbooks it_it_fingerbooks es_es_fingerbooks
	php lang.php webstore_imagazine us_us_gossipsalad_eng mx_mx_muchgossip mx_mx_igossip uk_uk_igossip
	php lang.php webstore_playplanet us_us_playplanet_eng ca_ca_playplanet_eng
	php lang.php webstore_mobile15 ca_ca_cellybean_eng

	## goldgamifive
	// ca_ca_igossip_eng
	// uk_uk_igossip
	// es_es_igames

	## webstore_imagazine
	// it_it_igossip
	// ca_ca_igossip_eng
	// uk_uk_igossip
	// ca_ca_muchgossip_eng
	// mx_mx_muchgossip
	// us_us_gossipsalad_eng

	## wap_iphonemotime
	// en_ww_iphone
	// fr_fr_blinko

	## webstore_flagfingerbooks
	// it_it_fingerbooks
	// ca_ca_fingerbooks_eng
	// es_es_fingerbooks

	## webstore_playplanet
	// ca_ca_playplanet_eng
	
	## webstore_mobile15
	// ca_ca_cellybean_eng
*/

$webstore = $argv[1];
$file_keys_name = $webstore.'_keys.txt';
$file_values_name = $webstore.'_values.txt';
$file_json = $webstore.'_json.json';

$lingue = array();
foreach($argv as $ln) {
	array_push($lingue, $ln);
}
$lingue = array_slice($lingue, 2);
$file_values = "Type\tKey\t".implode("\t",$lingue)."\n";

$url = '../sites/'.$webstore;
$nn = strlen($url);
$ignore = array('CVS', '.htaccess', 'error_log', 'cgi-bin', 'php.ini', '.ftpquota', 'lang.php');
$dirTree = getDirectory($url, $ignore);

$i = 0;
$keys = '';
$chiave = array();
$file_keys = "Row\tFile\tType\tKey\n";
echo $file_keys;
foreach($dirTree as $k=>$p){
	foreach($p as $v=>$a){
		if(preg_match('/(swf|png|db|gif|jpg|wav)/',substr($a,-4))) continue;
		if(preg_match('/(tmpl~)/',substr($a,-5))) continue;
		$i++;
		$v = false;
		unset($lines);

		$lines = file($k.'/'.$a);
		foreach ($lines as $line_num => $line) {
				unset($dizionario);
				preg_match_all('/(<TMPL_VAR NAME=DIZIONARIO_(.*)>)/isU',$line,$dizionario);
				#preg_match_all('/(<TMPL_VAR NAME=(.*)>)/isU',$line,$dizionario);
				if(!empty($dizionario[2])){
					$d = explode(' ', $dizionario[2][0], 2);
					$dizionario[2][0] = $d[0];
					$file_keys_partial = $line_num."\t".substr($k, $nn).'/'.$a."\t".'DIZ'."\t".$dizionario[2][0]."\n";
					echo $file_keys_partial;
					$file_keys .= $file_keys_partial;
					$chiave[] = 'DIZ'."\t".$dizionario[2][0];
				}
				unset($ln10);
				preg_match_all('/(dict\.getkey\(\'(.*)\'\))/isU',$line,$ln10);
				if(!empty($ln10[2])){
					$file_keys_partial = $line_num."\t".substr($k, $nn).'/'.$a."\t".'JSD'."\t".$ln10[2][0]."\n";
					echo $file_keys_partial;
					$file_keys .= $file_keys_partial;
					$chiave[] = 'JSD'."\t".$ln10[2][0];
				}
				unset($ln10);
				preg_match_all('/(dict\.get\(\'(.*)\'\))/isU',$line,$ln10);
				if(!empty($ln10[2])){
					$file_keys_partial = $line_num."\t".substr($k, $nn).'/'.$a."\t".'JSD'."\t".$ln10[2][0]."\n";
					echo $file_keys_partial;
					$file_keys .= $file_keys_partial;
					$chiave[] = 'JSD'."\t".$ln10[2][0];
				}
				unset($ln10);
				preg_match_all('/(getDictionaryKey\("(.*)"\))/isU',$line,$ln10);
				if(!empty($ln10[2])){
					$file_keys_partial = $line_num."\t".substr($k, $nn).'/'.$a."\t".'DIZ'."\t".$ln10[2][0]."\n";
					echo $file_keys_partial;
					$file_keys .= $file_keys_partial;
					$chiave[] = 'DIZ'."\t".$ln10[2][0];
				}
		}
		//if($v) echo "\n\n";
	}
}

file_put_contents($file_keys_name, $file_keys);
echo "\n\n\n";

$chiave = array_unique($chiave);
echo $file_values;
$json_out_data = array();
 
foreach ($chiave as $key => $value) {
	$file_values_partial = $value."\t";
	echo $file_values_partial;
	$file_values .= $file_values_partial;
	$value = substr($value, 4);
	$json_out_data[] = $value;
	reset($lingue);
	foreach ($lingue as $lang) {
		$submit_url = 'https://admin.dadanet.it/dictionary/index.php/main/browse/general/'.$lang.'?phrase=&key='.$value.'&notes=';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
		curl_setopt($curl, CURLOPT_USERPWD, $_ENV["DADAUSER"].':'.$_ENV["DADAPWD"]);
		curl_setopt($curl, CURLOPT_SSLVERSION,3);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		//curl_setopt($curl, CURLOPT_POSTFIELDS, $params );

		curl_setopt($curl, CURLOPT_URL, $submit_url);
		$html = curl_exec($curl);
		$htmlinfo = curl_getinfo($curl);
 		#var_dump($html); var_dump($htmlinfo); die;

		$pattern = '|<textarea (.*) id="translation:'. preg_quote($value, '|') .':'.$lang.'">(.*)</textarea>|ismU';
		preg_match($pattern, $html, $match);
		#var_dump($match);
		$string = preg_replace("/\n/", " ", $match[2]);
		$string = trim($string);
		$file_values_partial = $string."\t";
		echo $file_values_partial;
		$file_values .= $file_values_partial;
		curl_close($curl);
	}
		$file_values_partial = "\n";
		echo $file_values_partial;
		$file_values .= $file_values_partial;
}
$json_out_file = json_encode($json_out_data);

file_put_contents($file_values_name, $file_values);
file_put_contents($file_json, $json_out_file);

echo "\n\n\n";

function getDirectory($path = '.', $ignore = array() ) {
	$dirTree = array();
	$dirTreeTemp = array();
	$ignore[] = '.';
	$ignore[] = '..';
	$dh = @opendir($path);
	while (false !== ($file = readdir($dh))) {
		if (!in_array($file, $ignore)) {
			if (!is_dir("$path/$file")) {
				$dirTree["$path"][] = $file;
			}
			else {
				$dirTreeTemp = getDirectory("$path/$file", $ignore);
				if (is_array($dirTreeTemp))$dirTree = array_merge($dirTree, $dirTreeTemp);
			}
		}
	}
	closedir($dh);
	return $dirTree;
}
