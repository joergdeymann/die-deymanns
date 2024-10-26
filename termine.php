<?php
include "class/login.php";
include "class/termine.class.php";	
$msg="";
$err=0;
$errcolor=array('green','red');
$outputhtml="termine.html";


/* 
	Artikel vorbereiten
*/
$t=new termine();
$a=new article();
$a=$t->getNextArticleHTML(); // ignore
$a=$t->getNextArticleHTML(); // ignore 

while(($a=$t->getNextArticleHTML()) == true) {
	$a->convertHTML2var();
	$t->addArticle($a);
}


$ahtmls = ""; // Arcticle Start
$ahtmle = ""; // Arcticle End
$ahtmlc = ""; // Acrticle New content
$article = ""; // Article Object

if (isset($_POST['insert'])) {	
	if (isset($_POST['datebutton']) && !empty($_POST['datebutton'])) {
		// echo "Hier";exit();
		// echo "Insert:";
		// echo $_POST['insert'];
		// echo ",COUNT:";
		// echo count($t->getArticleList());
		
		// if ($_POST['insert'] == count($t->getArticleList()) ) {
		// 	echo "Hier";exit;
		//	$_POST['insert'] == 0;
		// }
		
		if ($_POST['insert'] == 0) {
			$suchep="/(.*)\<\/article>\r\n/is";
			preg_match($suchep,$t->html,$matches,PREG_OFFSET_CAPTURE);
			$break=strlen($matches[0][0]);
			// echo "Insert=0";exit;
		} else {
			$_POST['insert']--;
			$article=$t->getArticleList($_POST['insert']);
			$break = $article->article_start;                // Position wo der Artikel Anfängt
			// $article_size  = $article->article_size;   // Artikellänge inclusive TAGS
		}
	} else {
		if ($_POST['insert'] == 0) {
			$_POST['insert'] = count($t->getArticleList())-1;
		}
		$_POST['insert']--;
		
		$article=$t->getArticleList($_POST['insert']);

		$break = $article->article_start+$article->article_size-strlen("</Article>");                
		
		// Position wo der Artikel Anfängt

		$ahtmls = "";
		$ahtmle = "";

		$phtmla = substr($t->html,0,$break);
		$phtmle = substr($t->html,$break);
		
	}
	
}

if (isset($_POST['datebutton']) && !empty($_POST['datebutton'])) {
	$dt = new DateTime($_POST['date']);
	$ahtmls='<article>'."\r\n";
	$ahtmle='</article>'."\r\n";
	$ahtmlc ="<h2>".$dt->format('d.m.Y')."</h2>"."\r\n";
	
	// Was wenn insert = 0 ??
	// $article=$t->getArticleList($_POST['insert']);
	// $break = $article->article_start;                // Position wo der Artikel Anfängt
	
	$phtmla = substr($t->html,0,$break);
	$phtmle = substr($t->html,$break);
	
	
	
	$dhtml= $phtmla.$ahtmls.$ahtmlc.$ahtmle.$phtmle;
	// file_put_contents($outputhtml,$dhtml);
	
	
}	

/* 
	https://www.php.net/manual/de/features.file-upload.post-method.php
*/

if (isset($_FILES['userfile']['name']) && empty($_FILES['userfile']['name'])) {
	if (isset($_POST['filebutton']) && !empty($_POST['filebutton'])) {
	   $msg="Datei wurde nicht angeggeben !";
	   $err=1;
	}
}
if (isset($_FILES['userfile']['name']) && !empty($_FILES['userfile']['name'])) {
	
	$uploaddir = 'upload/';	
	// $uploaddir = '/var/www/uploads/';
	$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		$msg= "Datei ist valide und wurde erfolgreich hochgeladen.";
		$err=0;
		if (stripos($_FILES['userfile']['type'],"image") !== false) {
			$ahtmlc.= '<img id="'.$_POST['pos'].'" src="'.$uploadfile.'">'."\r\n";
		} else 
		if (stripos($_FILES['userfile']['type'],"video") !== false) {
			$ahtmlc.= '<video width="100%" controls><source src="'.$uploadfile.'" type="'.$_FILES['userfile']['type'].'"></source></video>'."\r\n";;
		} else {
			$msg.= "Unbekanntes Format!";
			$err=1;
		}		
		
	} else {
		$msg= "Datei wurde nicht hochgeladen!";
		$err=1;
	}
	
}

if (isset($_POST['text']) && !empty($_POST['text'])) {
	$ahtmlc.= "<p>".nl2br($_POST['text'],false)."</p>"."\r\n";
}

if (isset($_POST['imgbr']) && !empty($_POST['imgbr'])) {	
	$ahtmlc.= '<br id="clear"><br>'."\r\n";
}


if (!empty($ahtmlc)) {
	$dhtml= $phtmla.$ahtmls.$ahtmlc.$ahtmle.$phtmle;
	file_put_contents($outputhtml,$dhtml);
}


/*
	Artikel komplett löschen
*/
if (isset($_POST['delete']) && !empty($_POST['delete'])) {
		// neue aederungen des spezifischen Artikels aus den POST holen
	$id=$_POST['id']-1;
	$a=$t->getArticleList($id);

	$dhtml = substr($t->html,0,($a->article_start-1));
	$dhtml.= substr($t->html,($a->article_start+$a->article_size));

	file_put_contents($outputhtml,$dhtml);
}

/*
	Behandle das Aendern eines Artikeles
*/
if (isset($_POST['change']) && !empty($_POST['change'])) {
		// neue aederungen des spezifischen Artikels aus den POST holen
	$id=$_POST['id']-1;
	$a=$t->getArticleList($id);
	
	
	$aHTML="<article>\r\n";
	$i=0;
	
	$tagid="";
	foreach($_POST as $key => $value) {
		$i++;
		// echo "Key=$key, i=$i<br>";	
		/*
			Infotext
			1. text
			2. Delete Checkbox
		*/
		if ($key == "text$i") {
			$k="deletetext$i";
			if (isset($_POST[$k]) && !empty($_POST[$k])) {
				$i--; // 
			} else {
				$aHTML.="<p>".nl2br($value,false)."</p>"."\r\n";
			}
			continue;
		} 
		
		
		/*
			Video hat einen 2. tag 
			1. Pos (left right delete)
			2. Link
		*/		
		if ($key == "videolink$i") {
			$k="deletevideo$i";
			if (isset($_POST[$k]) && !empty($_POST[$k])) {
				$i--; // 
			} else {
				$aHTML.='<video width="100%"  controls>'."\r\n";
				$aHTML.=$value."\r\n";
				$aHTML.='</video>'."\r\n";
			}
			continue;
		} 
		
		/*
			Image hat einen 2. tag 
			1. Pos (left right delete)
			2. Link
		*/		
		if ($key == "pos$i") {
			if ($value != "delete") {
				$k="link$i";
				$link=$_POST[$k];
				$aHTML.='<img id="'.$value.'" src="'.$link.'">'."\r\n";			
			}				

			$i--;
			continue;
		} 
		
		/*
			Umbruch wieder normalisieren
			1. Umbruch
			2. Delete checkbox
		*/
		
		if ($key == "imgbr$i") {
			// Echo "IMGBR<br>";
			$k="deleteimgbr$i";
			if (isset($_POST[$k]) && !empty($_POST[$k])) {
			// Echo "LÖSCHEN IMGBR<br>";
				$i--; // 
			} else {
				$aHTML.='<br id="clear">'."\r\n";			
			}
			continue;
		}

		/*
			Standart umbruch
		*/
		if ($key == "br$i") {
			$aHTML.='<br>'."\r\n";
		}

		/*
			Datum am anfang
		*/
		if ($key == "datum$i") {
			$dt = new DateTime($value);
			$aHTML.='<h2>'.$dt->format("d.m.Y").'</h2>'."\r\n";			
			// echo htmlspecialchars('<h2>'.$dt->format("d.m.Y").'</h2>\r\n').'<br>';			
		}
				
	}
	$aHTML.="</article>";

	$dhtml = substr($t->html,0,($a->article_start-1));
	$dhtml.= $aHTML;
	$dhtml.= substr($t->html,($a->article_start+$a->article_size));

	file_put_contents($outputhtml,$dhtml);
	header("Location:termine.php");
	exit;
	// $a->articleHTML=$aHTML;
	// $a->convertHTML2var();
	
	
	
}

/*
	Output
*/
	
	




	/*
		php.ini
		post_max_size = 200M
		upload_max_filesize = 200M
		
		oder htaccess
		
		video:
		<video width="320" height="270" controls autoplay>
		<source src="beispielvideourl.mp4" type="video/mp4“></source>
		</video>
		
		nach upload erkennen ob video oder Bild
		Bilder: jpg, jpeg, gif, png erlaubt
		Videos mov, avi, mp4, wmi
		
	*/
		
	
	$dhtml ='<article><form enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
	if ($msg != "") {
		$dhtml.='<b id="'.$errcolor[$err].'">'.$msg.'</b>';
	}
	// $dhtml.='<input type="hidden" name="MAX_FILE_SIZE" value="45367016">';
	//  Der Name des Eingabefelds bestimmt den Namen im $_FILES-Array 
	$dhtml.='<br><b><center>----> Bitte beachte! <-----</center></b>Bei Positonsänderungen der Bilder kann es sein das der Cache leer gemacht werden muss, um die neue Einstellung einzusehen, oder man benutzt die Tasten [STRG] und [F5] gleichzeitig. Das wirkt sich <b>auch</b> auf die gespeicherten Einstellungen aus.<br></br>';
	$dhtml.='<table id="admin">';

	$dhtml.='<tr><th>';
	$dhtml.='Einfügen: <br>';
	$dhtml.='</th><td>';
	
	$dhtml.='<select name="insert" size="1">';

	$dhtml.='<option value="0">am Ende</option>';
	$dhtml.='<option value="1" selected>am Anfang</option>';
	$id=1;
	foreach ($t->getArticleList() as $a) {
		// echo "Datum:".$a->date->format('d.m.Y')."<br>";
		$dhtml.='<option value="'.$id.'">vor/zu ('.$id.') '.$a->date->format('d.m.Y').'</option>';
		$id++;
	}
	$dhtml.='</select>';
	// $dhtml.='<input type="submit" name="insertbutton" value="Zeilenumbruch einfügen">';
	$dhtml.='</td><tr>';


	$dhtml.='<tr><th>';
	$dhtml.='Text: <br>';
	$dhtml.='</th><td>';
	$dhtml.='<textarea name="text" rows="10" style="width:90%;"></textarea><br>';
	$dhtml.='<input type="submit" name="textbutton" value="Text anhängen">';
	$dhtml.='</td><tr>';
	
	$dhtml.='<tr><th>';
	$dhtml.='Datei: <br>';
	$dhtml.='</th><td>';
	$dhtml.='<input name="userfile" accept="video/*,image/*" type="file"><br>';
	$dhtml.='Bei Bildern: <br>';	
	$dhtml.='<input type="radio" checked value = "left40"        name="pos"><img src="img/Bildlinks.png" width="30%">&nbsp;&nbsp;Links 40%<br>';
	$dhtml.='<input type="radio"         value = "norm50"        name="pos"><img src="img/Bildnorm.png" width="30%">&nbsp;&nbsp;Links Alein 50%<br>';
	$dhtml.='<input type="radio"         value = "right40"       name="pos"><img src="img/bildrechts.png" width="30%">&nbsp;&nbsp;Rechts 40%<br>';
	$dhtml.='Bei Videos: <br>';	
	$dhtml.='&nbsp;<img src="img/Bildvideo.png" width="30%" style="padding-left: 20px;">&nbsp;&nbsp;Video 100%<br>';
	$dhtml.='<input type="submit" value="Anhängen" name="filebutton">';
	
	$dhtml.='</td><tr>';

	$dt = new DateTime();
	$dhtml.='<tr><th>';
	$dhtml.='Neuer Artikel: <br>';
	$dhtml.='</th><td>';
	$dhtml.='<b>Datum:</b><input type="date" name="date" value="'.$dt->format('Y-m-d').'"><br>';				
	$dhtml.='<input type="submit" name="datebutton" value="Artikel anlegen">';
	$dhtml.='</td><tr>';

	$dhtml.='<tr><th>';
	$dhtml.='Neue Zeile: <br>';
	$dhtml.='</th><td>';
	$dhtml.='<input type="submit" name="imgbr" value="Zeilenumbruch einfügen">';
	$dhtml.='</td><tr>';
	
	
	$dhtml.='</table>';

	
	$dhtml.='</form></article>';


	echo $t->getHTMLHeader();
	echo $dhtml;
	foreach ($t->getArticleList() as $a) {
		echo $a->displayArticleEditor();
		echo '<br style="margin-top:100px">';
	}

	echo $t->getHTMLFooter();
	


?>
