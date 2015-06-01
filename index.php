<?
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
				'PDF' => "PDF.png",
				'AVI' => "avi.png",
				'MP3' => "mp3.png",
				'DIR' => "folder.png",
				'PPT' => "ppt.png",
				'MP4' => "mp4.png",
				'DOC' => "doc.png",
				'MOV' => "mov.png",
				'XLS' => "xls.png",
				'FLV' => "flv.png",
				'MHT' => "mht.png",
				'FB2' => "Document.png");

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
	
	$MEDIA_FILE_PATERN = "mp3|flv|avi|mp4|pdf|mov|doc|xls|mht|fb2"; 
	$HTML_FILE_PATERN = "htm|php"; 
		
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
<table width="100%">
	<tr>
	<td>Тип</td>
	<td>Наименование</td>
	<td>Дата последнего изменения</td>
	</tr>	
<? 					
	PrintTableRows($dirs);
	PrintTableRows($files);
		
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
?>
</table>
</body>
</html>