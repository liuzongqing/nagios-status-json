<?php

/**
* zongqing.liu@funplus.com
* 2014-03-04
*/
class NagiosJSON {
	
	function __construct($statusFile){
		$this->fileContent = file_get_contents($statusFile);
	}

	public function getJson($info){
		$pattern="/$info\ \{(.*?)\}/si";
		// 匹配模式;s ~ 代表以整篇匹配，而不是以行匹配; i ~ 不区分大小写
		$file = $this->fileContent;
		$num = preg_match_all($pattern,$file,$matchs);
		// 成功返回整个模式匹配的次数
		// matchs[0]储存整个模式的内容;matchs[1]储存匹配括号中的内容
		$json = array('parameter' => $info,'num' => $num,'detail' => array());

		if ($num == 0 || empty($info)) {
			return json_encode($json);
		}else{
			foreach ($matchs[1] as $item) {
				// print_r($item);
				$parameter = array();
				if (preg_match('/define/', $info)) {
					// define host / define service以是tab分隔key/value
					$pattern2 = "\t";
				}else{
					// 其他以"="分隔key/value
					$pattern2 = "=";
				}
				preg_match_all("/(.*)".$pattern2."(.*)/", $item, $lines);

				foreach ($lines[0] as $line) {

					// 分离出key/value，再重新组装value,防止value中有特殊用于分隔的字符
					$KeyValue = explode($pattern2, trim($line));
					$count = count($KeyValue);
					$key = trim($KeyValue[0]);
					if ($count < 2) {
						continue;
					} elseif ($count == 2) {
						$value = $KeyValue[1];
					} else {
						$value = "";
						for ($i=1; $i < $count-1; $i++) { 
							$value .= $KeyValue[$i].$pattern2;
						}
						$value .= $KeyValue[$count-1];
					}
					$parameter[$key] = trim($value);
				}
				array_push($json['detail'], $parameter);
			}
			return json_encode($json);
		}
	}

}

$nagiosDataFile = "../var/nagios.data";
// nagios data file.一般 ../var/nagios.data
if(file_exists($nagiosDataFile) && is_readable($nagiosDataFile)){
	$nagiosJson = new NagiosJSON($nagiosDataFile);
}else{
	echo "$nagiosDataFile is not exist or not readable";
	exit;
}

// parameter如：info, define host, define service, hoststatus, servicestatus
$para = $_GET['para'];
// $para = "info";

if (empty($para)) {
	echo "The parameter is required and can not be empty";
	exit;
}else{
	$res = $nagiosJson->getJson($para);
	// print_r(json_decode($res));
	print_r($res);
}
?>