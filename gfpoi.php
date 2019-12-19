#!/usr/bin/php
<?php
/* */
$optind = null;
$options=getopt('h::e::',['help','ext::'],$optind);
$pos_args = array_slice($argv, $optind);
//print_r($argv); print_r($options); print_r($pos_args); 
if(isset($options['help']) OR isset($options['h']) OR !isset($pos_args[0]) OR !isset($pos_args[1])) {
?>
Create csv in POI format for GooglePhoto shared album photos 
from directory with photos having spatial info

Usage:
$ ./gfpoi.php [parms] https://youGoogleGhotoGharedLink /dir/to/photos/with/spatial/info/ [/output/file.csv]
or
$ ./gfpoi.php [parms] /path/to/file/with/namesandurls.csv /dir/to/photos/with/spatial/info/ [/output/file.csv]

parms:
-h --help - this help
-e=ext --ext=ext - extension of the image files with spatial info, if it is not same as GooglePhoto file extension
/path/to/file/with/namesandurls.csv must have at least 2 columns: name and link
Other columns may be:
name description type link
 - in that order

<?php
	exit;
}
$photosPath = filter_var($pos_args[1],FILTER_SANITIZE_STRING);
$outputFileName = ''; $outputFile = NULL;
if(isset($pos_args[2])) $outputFileName = filter_var($pos_args[2],FILTER_SANITIZE_STRING);

$ext='';
if(isset($options['e'])) {
	$ext = substr(trim(filter_var($options['e'],FILTER_SANITIZE_STRING)),0,5);
}
if(isset($options['ext'])) {
	$ext = substr(trim(filter_var($options['ext'],FILTER_SANITIZE_STRING)),0,5);
}
//echo "$photosPath,$ext\n";
$columnNumberName = 'number';
$columnNameName = 'name'; 	// наименование колонки в файле csv,в которой содержатся имена файлов фотографий
$columnDescrName = 'description';
$columnTypeName = 'type';
$columnURIName = 'link';
$columnLatName = 'latitude';
$columnLonName = 'longitude';

$photoTypeName = 'photography';

if($albumPath = filter_var(filter_var($pos_args[0],FILTER_SANITIZE_URL),FILTER_VALIDATE_URL)) { 	// get names and urls from GooglePhoto
	$path_parts = pathinfo($_SERVER['SCRIPT_FILENAME']); // определяем каталог скрипта
	require_once($path_parts['dirname']."/GooglePhotosURLs.php"); // 
	$googlePhotos = GooglePhotosURLs($albumPath,NULL,NULL,'csv'); 	// get from GooglePhoto names and urls
	//print_r($googlePhotos); 
}
else { 	// get names and urls from file
	$albumPath = filter_var($pos_args[0],FILTER_SANITIZE_STRING);
	$googlePhotos = file($albumPath,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) or exit("Open input File $albumPath Error\n");
	array_walk($googlePhotos, function(&$val){$val=str_getcsv($val);});
	//print_r($googlePhotos); 
}
if($outputFileName) $outputFile = fopen($outputFileName,'w') or exit("Open output File $outputFileName Error\n");

//$outStr = "$columnNumberName,$columnNameName,$columnDescrName,$columnTypeName,$columnURIName,$columnLatName,$columnLonName\n";
$outStr = "\"$columnNameName\",\"$columnDescrName\",\"$columnTypeName\",\"$columnURIName\",\"$columnLatName\",\"$columnLonName\"\n";
if($outputFile) fwrite($outputFile,$outStr);
else echo $outStr;
foreach($googlePhotos as $num => $photo) {
	//print_r($photo);
	if(!filter_var(end($photo),FILTER_VALIDATE_URL)) continue;
	if($ext) {
		$imgName = pathinfo($photo[0], PATHINFO_FILENAME) . ".$ext";
	}
	else $imgName = $photo[0];
	if(!file_exists($photosPath.'/'.$imgName)) continue;
	$exif = exif_read_data($photosPath.'/'.$imgName); 	// ,'EXIF' может и не быть, а координаты - быть, поэтому не указываем
	//print_r($exif);
	if(!@$exif['GPSLatitude'] OR !@$exif['GPSLongitude']) {
		if($outputFile) echo "file $imgName no have spatial info\n";
		continue;
	}
	//$latitude = (string)(eval('return '.$exif['GPSLatitude'][0].';')).'°'.(string)(eval('return '.$exif['GPSLatitude'][1].';'))."'".(string)(eval('return '.$exif['GPSLatitude'][2].';')).'"'.$exif['GPSLatitudeRef'];
	$latitude = (string)((eval('return '.$exif['GPSLatitude'][0].';'))+((eval('return '.$exif['GPSLatitude'][1].';'))/60)+((eval('return '.$exif['GPSLatitude'][2].';'))/3600));
	if($exif['GPSLatitudeRef']=='S') $latitude = "-$latitude";
	//$longitude = (string)(eval('return '.$exif['GPSLongitude'][0].';')).'°'.(string)(eval('return '.$exif['GPSLongitude'][1].';'))."'".(string)(eval('return '.$exif['GPSLongitude'][2].';')).'"'.$exif['GPSLongitudeRef'];
	$longitude = (string)((eval('return '.$exif['GPSLongitude'][0].';'))+((eval('return '.$exif['GPSLongitude'][1].';'))/60)+((eval('return '.$exif['GPSLongitude'][2].';'))/3600));
	if($exif['GPSLongitudeRef']=='W') $longitude = "-$longitude";
	switch( count($photo)) { 	// 
	case 2:
		//$outStr = "$num,".'"'.pathinfo($imgName, PATHINFO_BASENAME).'",,"'."$photoTypeName".'","'.$photo[1]."\",$latitude,$longitude\n";
		$outStr = '"'.pathinfo($imgName, PATHINFO_BASENAME).'",,"'.$photoTypeName.'","'.end($photo)."\",\"$latitude\",\"$longitude\"\n";
		break;
	case 3: 	// считаем, что вторая колонка - описание
		$outStr = '"'.pathinfo($imgName, PATHINFO_BASENAME).'","'.$photo[1].'","'.$photoTypeName.'","'.end($photo)."\",\"$latitude\",\"$longitude\"\n";
		break;
	default: 	// считаем, что третья колонка - тип, а остальные игнорируем
		$outStr = '"'.pathinfo($imgName, PATHINFO_BASENAME).'","'.$photo[1].'","'.$photo[2].'","'.end($photo)."\",\"$latitude\",\"$longitude\"\n";
	}
	if($outputFile) fwrite($outputFile,$outStr);
	else echo $outStr;
}
if($outputFile) fclose($outputFile);
?>
