<?php
// data url string that was uploaded
ini_set('memory_limit', '-1');
include_once('./lib/QrReader.php');

$data_url = $_POST['data'];
// echo $data_url;
// exit();
//add random number for name 
$date= date('Ymdhis');
$name='Temp_'.rand().'_'.rand().'_'.$date;

list($type, $data) = explode(';', $data_url);
list(, $data)      = explode(',', $data);
$data = base64_decode($data);

file_put_contents('./image/'.$name.'.png', $data);

$src = imagecreatefrompng('./image/'.$name.'.png');
// create an image resource of your expected size 30x20
$dest = imagecreatetruecolor(400, 1500);
// Copy the image
imagecopy(
  $dest,
  $src,
  0,    // 0x of your destination
  0,    // 0y of your destination
  0,   // middle x of your source 
  10,   // middle y of your source
  400,  // 30px of width
  1500   // 20px of height
);

// The second parameter should be the path of your destination
imagepng($dest, './croped/'.$name.'.png');

imagedestroy($dest);
imagedestroy($src);
//引入需要的类

// 将我们要识别的二维码放进去
$qrcode2 = new QrReader(dirname(__FILE__) . './croped/'.$name.'.png'); //图片路径
//返回识别后的文本
$path = $qrcode2->text();

// echo "url: ".$path;
// exit();


$data = get_json_data($path,$name);
echo $data;

function get_json_data($path,$name)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $path);
  curl_setopt($ch, CURLOPT_FAILONERROR, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 15);
  $stringXML = curl_exec($ch);
  curl_close($ch);

  $stringXML = str_replace(array("\n", "\r", "\t"), '', $stringXML);
  $stringXML = trim(str_replace('"', "'", $stringXML));
  $stringXML = simplexml_load_string($stringXML);
  $json_string = json_encode($stringXML);
  //delete image from folder
  unlink('./croped/'.$name.'.png');
  unlink('./image/'.$name.'.png');
  return $json_string;
}
