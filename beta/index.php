<?
	$beginTime = GetCurTime();
	
	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
	$PATH_DELIMETR = "/";
	$URL_DIR_DELIMETR = "|";
	$OPERA_FILES_SRORE = "_files";
	$DIRINFO_FILE = "dirinfo";
	$DIR_TAG_SKIP = "skip";
	$DIR_TAG_DEFAULT = "default";
	$DIR_TAG_POS_SKIP = 0;
	$DIR_TAG_POS_DEF = 1;	
	$DIR_TAG_POS_DEF_DESCR = 2;
	$DIR_TAG_POS_EXCL_FILES = 3;
	$ITEM_ICONS = array(
				'HTM' => "HTML.png",
				'PDF' => "pdf.png",
				'AVI' => "avi.png",
				'MP3' => "mp3.png",
				'DIR' => "folder.png",
				'PPT' => "ppt.png",
				'MP4' => "mp4.png",
				'DOC' => "doc.png",
				'MOV' => "mov.png",
				'XLS' => "xls.png",
				'FLV' => "flv.png",
				'MPG' => "mpeg.png",
				'MHT' => "mht.png");

	include('scanner.php');
	
	$dir = $_GET['dir'];
    		
	if($dir == '') 
	{
		$dir = $DOCUMENT_ROOT;
	}
	else
	{
		$dir = $DOCUMENT_ROOT.$PATH_DELIMETR.DecodeDirPath($dir);
	}
	
	//PrintRow("DocRoot is ".$DOCUMENT_ROOT);
	
	$MEDIA_FILE_PATERN = "mp3|flv|avi|mp4|pdf|mov|doc|xls|mht|mpg"; 
	$HTML_FILE_PATERN = "htm"; 
		
	$SEARCH_FILE_PATERN = '/'.$HTML_FILE_PATERN.'|'.$MEDIA_FILE_PATERN.'/i';
	$SEARCH_HTM_FILE_PATERN = '/'.$HTML_FILE_PATERN.'/i';	
					
	$dirs = array();
	$files = array();
			
	GetDirectoryListing($dir, $dirs, $files);	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<title>Каталог ресурсов - <?echo basename($dir);?></title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
<link rel="stylesheet" href="styles.css" type="text/css">     
<body>
<table>
	<tr>
	<td>Тип</td>
	<td>Наименование</td>
	<td>Дата последнего изменения</td>
	</tr>
	<tr><td colspan="3"><hr color="Black" size="2px"></td></tr>
<? 						
	PrintWorkTime($beginTime);
	PrintTableRows($dirs);
	PrintTableRows($files);						
?>
</table>
<?	
	function PrintWorkTime($startTime)
	{
		$suffix = "";
		$worktime = GetCurTime()-$startTime;
				
		if(($worktime > 0 && $worktime < 5) || $worktime > 20)
		{
			switch($worktime)
			{
				case "1":
					$suffix = "a";
					break;
				case "2":
				case "3":
				case "4":
					$suffix = "ы";
					break;
				default:
					break;
			}
		}
	
		PrintRow("Время обработки запроса: ".$worktime." секунд".$suffix);	
	}
	
	function PrintTableRows($itemsArr)
	{
		global $ITEM_ICONS;
		
		foreach($itemsArr as $item)
		{
			print("<tr>".GetCellCode('<img width="40" height="40" src="/media/images/file_types/'.$ITEM_ICONS[$item->LinkIconType].'">').
						 GetCellCode($item->LinkCode).
						 GetCellCode($item->LinkModificationDate)."</tr>\n");
		}
	}
			
	function GetCellCode($content)
	{
		return "<td>".$content."</td>";
	}
	
	function GetCurTime()
	{
		$time_delim = ":";
		
		$parts = explode($time_delim, date("H".$time_delim."i".$time_delim."s", time()));
						
		return $parts[0]*3600+$parts[1]*60+$parts[2]; //date("U", time());
	}
?>
</body>
</html>