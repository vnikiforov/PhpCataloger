<?
	class ItemLink
	{
		var $LinkCode;		
		var $LinkIconType;
		var $LinkModificationDate;
		
		function ItemLink($_linkCode, $_linkIconType, $_linkModificationDate)
		{
			$this->LinkCode = $_linkCode;
			$this->LinkIconType = $_linkIconType;
			$this->LinkModificationDate = $_linkModificationDate;
		}
	}

	$TITLE_TAG_BEGIN = "<title";
	$TITLE_TEXT_BEGIN = ">";
	$TITLE_TEXT_END = "</title";	
		
	function GetDirectoryListing($dirPath, &$dirs, &$files)
	{																
		global $SEARCH_FILE_PATERN;			
		global $DOCUMENT_ROOT;					
		global $SEARCH_HTM_FILE_PATERN;
				
		$docRootLen = strlen($DOCUMENT_ROOT)+1;
				
		if ($handle = @opendir($dirPath)) 
		{																		
			// —писок файлов директории, запрещенных дл€ отображени€
		    // $excludeFiles = array();
			
			$dirFiles = array();
			
			while (false !== ($file = readdir($handle))) 
			{	
				if ($file != "." && $file != "..")
				{					
					array_push($dirFiles, $file);
				}
			}
			
			closedir($handle);		
			
			// TODO: ”дал€ем из списка файлы, запрещенные дл€ отображени€
			
			// Sort files
			sort($dirFiles);
			
			foreach($dirFiles as $file)
			{
				$needAddLink = false;					
				$fulPath = $dirPath.'/'.$file;					
				$linkCaption = $file;
				$icon_type = 'HTM';				
				
				if(is_file($fulPath))
				{																											
					if(@preg_match($SEARCH_FILE_PATERN, GetFileExtension($file)))
					{																					
						$needAddLink = true;							
						$linkCollection = &$files;
						$fileExtension = GetFileExtension($file);
						
						if(@preg_match($SEARCH_HTM_FILE_PATERN, $fileExtension)) 
						{
							$linkCaption = GetHtmlFileTitle($fulPath);
						}
						else
						{								
							$icon_type = substr(strtoupper($fileExtension),1);		
							$linkCaption = Utf8ToWin($linkCaption);										
						}
						
						$linkUrl = 'http://'.$_SERVER['SERVER_NAME'].'/'.substr($fulPath, $docRootLen);
						$linkClass = 'FileUrl';		
						$linkUrl = Utf8ToWin($linkUrl);							
					}
				}
				else
				{													
					if(ParseDirInfo($fulPath, $files, $linkCaption, $excludeFiles))
					{
						$needAddLink = NeedDirectoryShow($fulPath);
						if($needAddLink)
						{
							$linkCollection = &$dirs;
							$linkUrl = '?dir='.EncodeDirPath(substr($fulPath, $docRootLen));
							$linkClass = 'DirUrl';
							$icon_type = 'DIR';								
						}
					}
				}
				
				if($needAddLink)
				{
					StoreLink($linkCollection, $linkUrl, $linkCaption, $linkClass, $icon_type, $fulPath);
				}
			}			
		}		
		else
		{
			echo "ƒиректори€ не найдена";
		}
	}		
			
	function FindTaggedText($filePath, $beginTag, $beginTextTag, $endTextTag)
	{			
		$TITLE_BEGIN_LEN = strlen($beginTag);
		
		$result = '';

		$startTitleFound = false;		

		$file = fopen ($filePath, "r");
		
		while (!feof ($file)) 
		{
			$line = fgets ($file, 1024);
					
			if($startTitleFound)
			{		
				$pos = FindSubstring($line, $endTextTag);
	
				if ($pos === false) 
				{		
					$result .= $line;				
				}
				else
				{				
					$result .= substr($line, 0, $pos);
					break; // ћногострочный заголовок полностью прочитан.	
				}			
			}
			else
			{
				$pos = FindSubstring($line, $beginTag);
							
				if ($pos !== false) 
				{		
					// »щем начало текста заголовка.
					$pos = FindSubstring($line, $beginTextTag, $pos + $TITLE_BEGIN_LEN);
				
					if ($pos !== false) 
					{
						$startTitleFound = true;
	
						$pos += strlen($beginTextTag);
		
						$end = FindSubstring($line, $endTextTag, $pos);

						if($end === false)
						{
							$result = substr($line, $pos); 						
						}
						else	
						{
							$result = substr($line, $pos, $end - $pos);
							break;	// «аголовок полностью размещен на одной строке.
						}					
					}		
				}							
			}			
		}
		
		fclose ($file);								
		
		return trim($result);
	}
	
	// ќсуществл€ет поиск в строке без учета регистра.
	function FindSubstring($srcString, $searchString, $startPos = 0)	
	{		
		return strpos(strtolower($srcString), $searchString, $startPos);
	}
	
	function GetHtmlFileTitle($filePath)
	{
		global $TITLE_TAG_BEGIN;
		global $TITLE_TEXT_BEGIN;
		global $TITLE_TEXT_END;	
								
		$result = FindTaggedText($filePath, $TITLE_TAG_BEGIN, $TITLE_TEXT_BEGIN, $TITLE_TEXT_END);
		
		if(strlen($result) == 0) 
		{
			$result = basename($filePath);
		}
		else
		{
			$fileEncoding = strtolower(GetFileEncodingID($filePath));
			
			switch($fileEncoding)
			{
				case 'koi8-r': 
					$result = convert_cyr_string($result, "k", "w");					
					break;
					
				case 'utf-8':
				case 'utf8':
				case 'utf-8;utf-8':
				case 'unicode':
				
					$result = Utf8ToWin($result);
					break;
					
				default:
					break; //  одировкой по умолчанию считаем Windows-1251 и ничего в таком случае не делаем.
			}
		}
		
		return $result;
	}
	
	function Utf8ToWin($fcontents) 
	{
		$out = $c1 = '';
		$byte2 = false;
		for ($c = 0; $c < strlen($fcontents); $c++) 
		{						
			$i = ord($fcontents[$c]);
									
			if ($i <= 127) 
			{
				$out .= $fcontents[$c];
			}
			if ($byte2) 
			{
				$new_c2 = ($c1 & 3) * 64 + ($i & 63);
				$new_c1 = ($c1 >> 2) & 5;
				$new_i = $new_c1 * 256 + $new_c2;
				
				if ($new_i == 1025) 
				{
					$out_i = 168;
				} 
				else 
				{
					if ($new_i == 1105) 
					{
						$out_i = 184;
					}
					else 
					{
						$out_i = $new_i - 848;
						
						// ƒл€ знаков припинани€
						if($out_i < 0) $out_i = $new_i;
					}
				}
				
				$out .= chr($out_i);
				
				$byte2 = false;
			}
			
			if (( $i >> 5) == 6) 
			{
				$c1 = $i;
				$byte2 = true;
			}
		}
		return $out;
	}
		
	function GetFileEncodingID($filePath)
	{												
		$result = FindTaggedText($filePath, "charset", "=", "\"");					
							
		if(strlen($result) != 0) 
		{
			$pos = strpos($result, ";", 1);
												
			if($pos != false)
		    {
				$result = substr($result, 0, $pos);	
			}
		}
		else // —лучай, когда кодировка уже забрана в кавычки
		{			
			$t = FindTaggedText($filePath, "<head", ">", "</head>");
			
			$pos = strpos($t, "charset");
			
			$t = substr($t, $pos, strpos($t, ">", $pos) - $pos);	
			
			$t = substr($t, strpos($t, "\""));	
			
			// ¬нутри кавчек несколько блоков определени€ кодировки.
			$pos = strpos($t, ";", 1);
			
			if($pos != false)
		    {
				$result = substr($t, 1, $pos);	
			}
			else
			{								
				$len  = strpos($t, "\"", 1);							
				$result = substr($t, 1, strpos($t, "\"", 1)-1);	
			}							
		}
							
		if(strlen($result) == 0) $result = "NO_ENCODING!";
		
		return $result;
	}
	
	// »ще и читает файл dirinfo в по указанному пути, если директори€ €вл€етс€ адресом - он заноситс€ в массив ссылок.
	// “ак же из файла dirinfo директории читаетс€ описание содержимого директории. 
	// ¬озвращает true если путь может быть добавлен в каталог как директори€
	function ParseDirInfo($dirPath, &$fileCollection, &$dirCaption, &$excludes)
	{
		global $DOCUMENT_ROOT;		
		global $DIRINFO_FILE;
		global $PATH_DELIMETR;		
		global $DIR_TAG_SKIP;
		global $DIR_TAG_DEFAULT;
		global $DIR_TAG_POS_SKIP;
		global $DIR_TAG_POS_DEF;	
		global $DIR_TAG_POS_DEF_DESCR;
	
		$result = true;
		
		if ($handle = @opendir($dirPath)) 
		{																		
			while (false !== ($file = readdir($handle))) 
			{	
				if ($file == $DIRINFO_FILE)
				{																
					$dirInfo = array();
					$fd = fopen ($dirPath.$PATH_DELIMETR.$DIRINFO_FILE, "r");				
					while (!feof ($fd)) 
					{
						array_push($dirInfo,fgets($fd));						
					}
					fclose ($fd);														
																							
					if(trim($dirInfo[$DIR_TAG_POS_SKIP]) == $DIR_TAG_SKIP) 
					{
						$result = false; // ѕринудительное исключение директории из каталога.
					}
					else
					{																							
						// «апоминаем описание директории, если оно присутсвует	
						if($DIR_TAG_POS_DEF_DESCR < count($dirInfo))
						{
							$dirDescription = trim($dirInfo[$DIR_TAG_POS_DEF_DESCR]);
							if(strlen($dirDescription) > 0)	$dirCaption = $dirDescription;
						}
						
						$defUrlInfo = explode(' ', $dirInfo[$DIR_TAG_POS_DEF]);
																					
						if(trim($defUrlInfo[0]) == $DIR_TAG_DEFAULT)
						{							
							$url = 'http://'.$_SERVER['SERVER_NAME'].'/'.substr($dirPath, strlen($DOCUMENT_ROOT)+1).'/';
							
							if(count($defUrlInfo) == 2) 
							{ 
								$fileLinkName = trim($defUrlInfo[1]);							
								if(strlen($fileLinkName) > 0) $url = $url.$fileLinkName;								 								
							}														
							
							StoreLink($fileCollection, $url, $dirCaption, 'FileUrl', 'HTM', $dirPath);							
							$result = false;
						}
					}
					break;
				}
			}
			closedir($handle);
		}	

		return $result;
	}
		
	/// ѕровер€ет нужно ли отображать директорию, пустые либо содержащие не отображаемые типы файлов не показываютс€.
	function NeedDirectoryShow($dirPath)
	{																				
		global $SEARCH_FILE_PATERN;	
		global $OPERA_FILES_SRORE;
		global $LEN_OPERA_FILE_STORE_SUFFIX;
		$result = false;
				
		// ѕропускаем директории с файлами сохраненными ќперой						
		if(strpos($dirPath, $OPERA_FILES_SRORE, (strlen($dirPath)-strlen($OPERA_FILES_SRORE))) === false)
		{				
			if ($handle = @opendir($dirPath)) 
			{																		
				while (false !== ($file = readdir($handle))) 
				{	
					if ($file != "." && $file != "..")
					{					
						$fulPath = $dirPath.'/'.$file;
						$filePath = substr($fulPath, strlen($DOCUMENT_ROOT)+1);
						
						if(is_file($fulPath))
						{											
							$result = (@preg_match($SEARCH_FILE_PATERN, GetFileExtension($file)) > 0);
						}
						else
						{																																			
							$result = NeedDirectoryShow($fulPath);								
						}
						
						if($result) break;
					}
				}
				closedir($handle);
			}				
		}
		return $result;
	}
	
	function StoreLink(&$fileCollection, $urlText, $urlCaption, $linkClass, $linkIconType, $fulFilePath)
	{		
		array_push($fileCollection, new ItemLink(CreateLinkTag($urlText, $urlCaption, $linkClass), 
				   $linkIconType, date("d.m.y",filemtime($fulFilePath))));
	}
	
	function EncodeDirPath($path)
	{					
		global $PATH_DELIMETR;		
		global $URL_DIR_DELIMETR;					
		return TranslateDirPath($path, $PATH_DELIMETR, $URL_DIR_DELIMETR);		
	}

	function DecodeDirPath($path)
	{
		global $PATH_DELIMETR;		
		global $URL_DIR_DELIMETR;
		return TranslateDirPath($path, $URL_DIR_DELIMETR, $PATH_DELIMETR);		
	}
	
	function TranslateDirPath($path, $inDelim, $outDelim)
	{			
		$result = (implode($outDelim, explode($inDelim, $path)));
		
		return $result;
	}
		
	function PrintArray($arr)
	{
		foreach($arr as $item)
		{
			PrintRow($item);
		}
	}
	
	function GetFileExtension($fileName)
	{		
		return substr($fileName, strrpos($fileName, '.'));
	}
			
	function PrintDirURL($dirPath)
	{						
		global $DOCUMENT_ROOT;		
		PrintRow('<a href="?dir='.$dirPath.'">'.substr($dirPath, strlen($DOCUMENT_ROOT)+1).'</a>');
	}
	
	function PrintURL($htmlFileName)
	{				
		$url = 'http://'.$_SERVER['SERVER_NAME'].'/'.$htmlFileName;
		PrintRow('<a href="'.$url.'">'.$url.'</a>');
	}
	
	function CreateLinkTag($url, $caption, $class)
	{
		return '<a href="'.$url.'" class="'.$class.'">'.$caption.'</a>';
	}
	
	function PrintRow($text)
	{
		print($text."<br>\n");
	}
?>