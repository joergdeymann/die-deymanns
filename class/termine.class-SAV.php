<?php

/*
$object 
"r40" = JPG right 40%
"l40" = JPG left  40%
"c50" = JPG normal 50%
"text" = Text 
"clear" Clear für Bilder
*/
/*
			$haystack="<xyz><article><abc<td>test</td>def</td></article><article>";
			// $haystack="abc<td>test</td>";
			$suche="/<article>(.*?)<\/article>/i";
			$offset=0;
			$i = preg_match($suche,$haystack,$matches,PREG_OFFSET_CAPTURE,$offset);
			echo $i."<br>";
			echo "0:Pos ". $matches[0][1] . ", Text: " . htmlspecialchars($matches[0][0]) . "<br>";
			echo "1:Pos ". $matches[1][1] . ", Text: " . htmlspecialchars($matches[1][0]) . "<br>";
			echo "<br>";
			
			
			
			// suche naechstes TAG
			// regzlaerer Ausdruck
			$haystack="abc<td>test</td>def</td>";
			// $haystack="abc<td>test</td>";
			$suche="/<(.*?)>(.*?)<(.*?)>/i";
			$offset=0;
			
			
			$i = preg_match($suche,$haystack,$matches,PREG_OFFSET_CAPTURE,$offset);
			echo $i."<br>";
			echo "0:Pos ". $matches[0][1] . ", Text: " . htmlspecialchars($matches[0][0]) . "<br>";
			echo "1:Pos ". $matches[1][1] . ", Text: " . htmlspecialchars($matches[1][0]) . "<br>";
			echo "2:Pos ". $matches[2][1] . ", Text: " . htmlspecialchars($matches[2][0]) . "<br>";
			echo "3:Pos ". $matches[3][1] . ", Text: " . htmlspecialchars($matches[3][0]) . "<br>";
			// echo "1:". htmlspecialchars($matches[1])."<br>";
			// echo "2:". htmlspecialchars($matches[2])."<br>";
			exit;
			
*/
		
class format {
	public $format;  // zB: IMG.LEFT40
	public $tag;     // img id="LEFT40"
	public $content; // leer oder bei <p> = text  	
	function __construct($f="",$t="",$c="") {
		$this->format=$f;
		$this->tag=$t;
		$this->content=$t;		
	}		
}

class article {
	public $nr;         // noch nicht benutzt
	public $date;       // Eingestelltes Datum
	public $object;     // Liste mit Objekten siehe oben
	public $article_start;  // Arcticle Startposition, inclusive <article>
	public $article_size;   // Article groesse inclusive <article> und </article>
	public $html;           // Text zwischen <article> und </article>
	
	
	
	
	private $id=0;  // Zähler für die Objekte, zum ablesen von aussen
	
	function __construct() {
		$this->init();
	}
	
	public function init() {
		$this->id=0;
		$this->date = new DateTime(); 
		$this->output = array(); // Format,TAG,Content 
		$this->article_start=0;
		$this->article_size=0;
		$this->html="";
	}
	
	/*
		Datum setzen
	*/
	public function setDate($date) {
		$this->date= new DateTime($date);
	}
	
	/*
		Neues Objekt hinzufügen
	*/
	private function addObject($format,$tag,$content) {
		$this->output[]=new format($format, $tag, $content);		
	}
	
	
	public function findNextTag($string) {
	}
	
	
	public function getHTMLCode() {
		return $this->html;	
	}
	
	public function convertHTML2var() {
		$article = $this->object;
		// suche naechstes TAG
		$suche="/<(.*?)>(.*?)<(.*?)>/is";
		$offset=0;
		
		echo "ANFANG CONVERT ARTICLE<br>";
		while(preg_match($suche,$this->html,$matches,PREG_OFFSET_CAPTURE,$offset)) {
			echo "<br>NEW TAG<br>";
			// echo "Offset=$offset<br>";
			/* 
				<p> </p> komplett nehmen hier nicht auf Tags achten
			*/
			if ($matches[1][0] == "p") {
				// echo "TAGS-1=#".$matches[1][0]."#<br>";
				
				$suchep="/<(p)>(.*?)<(\/p)>/is";
				preg_match($suchep,$this->html,$matches,PREG_OFFSET_CAPTURE,$offset);
				// echo "TAGS=#".$matches[1][0]."# und=#",$matches[3][0]."#<br>";
				
			}
			/* 
				Offset correction:
				<a></a> = normal weitersuchen
				<img><a> = die 3 stellen von <a> wieder zurueck
			*/
			
			if ("/".$matches[1][0] != $matches[3][0]) {
				$offsetcorrection = strlen($matches[3][0])+2;
				// echo "*C=1*M=".$matches[3][0]."*O=".$offsetcorrection."*<br>";
				// echo "Match=".$matches[1][0]."-und=",$matches[3][0]."<br>";
				
			} else {
				$offsetcorrection = 0;
				// echo "*C=0*<br>";
			}
			// echo "Offset Alt:$offset<br>";
			$offset= $matches[0][1] + strlen($matches[0][0]) - $offsetcorrection;
			
			// echo "Offset Neu:$offset<br>";
			// echo "Offset Korrektur:$offsetcorrection<br>";
			// echo "Complete Content len:" . strlen($matches[0][0])."<br>"; 
			// echo "Complete Content:" . htmlspecialchars($matches[0][0])."<br>"; 
			// echo "Rest:" . htmlspecialchars(substr($article->html,$offset))."<br>"; 
			
			
			$this->object[] = new format("",$matches[1][0],$matches[2][0]);
			echo "Tag = ##" . $matches[1][0] . "##<br>";
			echo "Content = ##" . $matches[2][0] . "##<br>";
						
			
		}
		/* 
			Am ende des Artikels steht kein Tag ?
			also auf Rest checken ?
			
		*/
		$suche="/<(.*?)>(.*)/is";
		// $suche="/<(.*?)$/i";
		echo "XX:" . substr($this->html,$offset,5)."<br>";
		
		if (preg_match($suche,$this->html,$matches,PREG_OFFSET_CAPTURE,$offset)) {
			echo "XTag = " . $matches[1][0] . "<br>";
			echo "Content = " . $matches[2][0] . "<br>";
			$this->object[] = new format("",$matches[1][0],$matches[2][0]);
			
			$offset= $matches[0][1] + strlen($matches[0][0]);
		}
		

		
		echo "Offset am ende = $offset <br>";
		echo "Länge des Artikels = ".strlen($article->html)." <br>";
		echo "ENDE CONVERT<br>";
		
			
		/*
		echo $i."<br>";
		echo "0:Pos ". $matches[0][1] . ", Text: " . htmlspecialchars($matches[0][0]) . "<br>";
		echo "1:Pos ". $matches[1][1] . ", Text: " . htmlspecialchars($matches[1][0]) . "<br>";
		echo "2:Pos ". $matches[2][1] . ", Text: " . htmlspecialchars($matches[2][0]) . "<br>";
		echo "3:Pos ". $matches[3][1] . ", Text: " . htmlspecialchars($matches[3][0]) . "<br>";
		*/
	}
	
	
}
		
		
		
class termine {

	private $html = "";
	private $htmlfile= 'termine.html';
	private $content_start=0;
	private $content_ende=0;

	private $article_list=array();
	private $article_start=0;
	private $article_end=0;
	
	/* 
		HTML Laden
	*/
	public function load() {
		$this->html = file_get_contents($this->htmlfile);
		// echo $this->html;
		// echo nl2br($zitate);
		// $pcre = sprintf('/%s/i', preg_quote('</article>'));
		// $pcre = sprintf('/%s/i', preg_quote('<article>'));
		// $num = preg_match_all($pcre, $html, $hits);
		// $this->datasets = $num-1;
		// $this->html = $html;
		$this->content_start=0;
		$this->content_ende=0;

		$this->article_list=array();
		$this->article_start=0;
		$this->article_end=0;	
	}
	
	private function reset() {
		$this->content_start=0;
		$this->content_ende=0;

		$this->article_start=0;
		$this->article_end=0;	
	}

	
	public function addArticle($content) {
		$this->article_list[]=$content;
		
		echo "Pos ". $content->article_start . ", Text: " . htmlspecialchars($content->html) . "<br>";
		echo "<br>";
	}

	
	public function getNextArticle() {
		// $haystack="<xyz><article><abc<td>test</td>def</td></article><article>";
		// $haystack="abc<td>test</td>";
		// $this->html="<article>abcdef\r\n</article>";
		$suche="/<article>(.*?)<\/article>/is";
		$i = preg_match($suche,$this->html,$matches,PREG_OFFSET_CAPTURE,$this->article_end);
		// echo $i."<br>";
		if ($i == false) {
			return false;
		}
		
		$content = new article();
		$content->article_start=$matches[0][1];
		$content->article_size=strlen($matches[0][0]);
		$content->html = $matches[1][0];
		
		$this->article_start = $content->article_start;
		$this->article_end   = $content->article_start + $content->article_size;
		return $content;		
	}
			

	public function convertHTML2var($article) {
		$article->convertHTML2var();
	}
	
	public function convertHTML2var($article) {
		// suche naechstes TAG
		$suche="/<(.*?)>(.*?)<(.*?)>/is";
		$offset=0;
		
		echo "ANFANG CONVERT<br>";
		while(preg_match($suche,$article->html,$matches,PREG_OFFSET_CAPTURE,$offset)) {
			echo "<br>NEW TAG<br>";
			// echo "Offset=$offset<br>";
			/* 
				<p> </p> komplett nehmen hier nicht auf Tags achten
			*/
			if ($matches[1][0] == "p") {
				// echo "TAGS-1=#".$matches[1][0]."#<br>";
				
				$suchep="/<(p)>(.*?)<(\/p)>/is";
				preg_match($suchep,$article->html,$matches,PREG_OFFSET_CAPTURE,$offset);
				// echo "TAGS=#".$matches[1][0]."# und=#",$matches[3][0]."#<br>";
				
			}
			/* 
				Offset correction:
				<a></a> = normal weitersuchen
				<img><a> = die 3 stellen von <a> wieder zurueck
			*/
			
			if ("/".$matches[1][0] != $matches[3][0]) {
				$offsetcorrection = strlen($matches[3][0])+2;
				// echo "*C=1*M=".$matches[3][0]."*O=".$offsetcorrection."*<br>";
				// echo "Match=".$matches[1][0]."-und=",$matches[3][0]."<br>";
				
			} else {
				$offsetcorrection = 0;
				// echo "*C=0*<br>";
			}
			// echo "Offset Alt:$offset<br>";
			$offset= $matches[0][1] + strlen($matches[0][0]) - $offsetcorrection;
			
			// echo "Offset Neu:$offset<br>";
			// echo "Offset Korrektur:$offsetcorrection<br>";
			// echo "Complete Content len:" . strlen($matches[0][0])."<br>"; 
			// echo "Complete Content:" . htmlspecialchars($matches[0][0])."<br>"; 
			// echo "Rest:" . htmlspecialchars(substr($article->html,$offset))."<br>"; 
			
			
			$article->format[] = new format("",$matches[1][0],$matches[2][0]);
			echo "Tag = ##" . $matches[1][0] . "##<br>";
			echo "Content = ##" . $matches[2][0] . "##<br>";
						
			
		}
		/* 
			Am ende des Artikels steht kein Tag ?
			also auf Rest checken ?
			
		*/
		$suche="/<(.*?)>(.*)/is";
		// $suche="/<(.*?)$/i";
		echo "XX:" . substr($article->html,$offset,5)."<br>";
		
		if (preg_match($suche,$article->html,$matches,PREG_OFFSET_CAPTURE,$offset)) {
			echo "XTag = " . $matches[1][0] . "<br>";
			echo "Content = " . $matches[2][0] . "<br>";
			$article->format[] = new format("",$matches[1][0],$matches[2][0]);
			
			$offset= $matches[0][1] + strlen($matches[0][0]);
		}
		

		
		echo "Offset am ende = $offset <br>";
		echo "Länge des Artikels = ".strlen($article->html)." <br>";
		echo "ENDE CONVERT<br>";
		
			
		/*
		echo $i."<br>";
		echo "0:Pos ". $matches[0][1] . ", Text: " . htmlspecialchars($matches[0][0]) . "<br>";
		echo "1:Pos ". $matches[1][1] . ", Text: " . htmlspecialchars($matches[1][0]) . "<br>";
		echo "2:Pos ". $matches[2][1] . ", Text: " . htmlspecialchars($matches[2][0]) . "<br>";
		echo "3:Pos ". $matches[3][1] . ", Text: " . htmlspecialchars($matches[3][0]) . "<br>";
		*/
	}
		
/*
		$needle="<article>\r\n";
		// 2 Artikel gehören nicht dazu
		$p=strpos($this->html, $needle) + strlen($needle);
		$p=strpos($this->html, $needle,$p) + strlen($needle);
		while (($p=strpos($this->html, $needle,$p) + strlen($needle)) === true) {
			$oe = new oneEvent();
			$oe->init();
			
			// Datum
			$ps=strpos($this->html, "<h2>",$p) + 4;
			$pe=strpos($this->html, "</h2>",$p);
			$oe->setDate(substr($this->html,$ps,$pe-$ps));
			$pe+=6;
			
			// suche naechstes TAG
			// regzlaerer Ausdruck
			$haystack="abc<td>test</td>def";
			$suche="<.*>.?*</.*>";
			$i=preg_match($haystack,$suche);
			echo $i;
			exit;
			
			
			
		}
*/		
	
	}
	
	
	
	/* 
		Datensatz ARTIICLE generieren
	*/
	private function getArticleHTML($datum,$string) {
		$dt = new DateTime($datum);

		$ds ="\n\r<article>\r\n";
		$ds.="<h2>".$dt->format('d.m.Y')."</h2>\r\n";
		$ds.="<p>".nl2br($string)."</p>\r\n";
		$ds.="<br id=\"clear\"><br>\r\n";
		$ds.="</article>\r\n";
		
		return $ds;
		
	}
	
	/* 
		Datensatz hinzufügen
	*/
	public function add($datum,$string) {
		
		$ds = $this->getArticleHTML($datum,$string); 
		
		$p=strrpos($this->html, "article>\r\n")+10;
		
		$str=substr($this->html,0,$p).$ds.substr($this->html,$p);
		// echo htmlspecialchars($str);
		$this->html=$str;
		
	}
	
	
	/* 
		HTML erstellen
	*/
	public function save() {
		$html = file_put_contents($this->htmlfile,$this->html);
	}

	/* 
		Position loeschen
	*/
	public function delete($nr) {
		
		// echo "<i style='color:white'>";
		// echo "Delete $nr<br>";
		// echo "</i>";
		
		$id=1;
		$find1="<article>\r\n";
		$len=strlen($find1);
		$find2="</article>\r\n";
		$len2=strlen($find2);
		$p=strpos($this->html, $find1)+$len; // 1. Artikel uebergehen
		$p=strpos($this->html, $find1,$p)+$len; // 2. Artikel uebergehen

		while (($p=strpos($this->html, $find1,$p)) !== false ) {	
			// echo "ID=$id<br>";
			if ($id == $nr) {
				if (($p2=strpos($this->html, $find2,$p)) !== false) {
					$p2+=$len2;
					// echo "Found P=$p, p2=$p2";
					$html =substr($this->html,0,($p-1));
					$html.=substr($this->html,$p2);
					$this->html=$html;
					//$this->save();
					return;
				}
				
			}
			$id++;
			$p+=$len;
			
		}
		
	}
	
	public function display() {
		$id=1;
		$find="</article>\r\n";
		$len=strlen($find);
		$p=strpos($this->html, $find)+$len; // 1. Artikel uebergehen
		$p=strpos($this->html, $find,$p)+$len; // 2. Artikel uebergehen
		$ps=0; // erste Startposition der eingelesenen HTML
		$dhtml="";  // Anzeige HTML			
		
/*		
		cd (2)
		
		0123456789
		abcdefghij
		$p = 2 gefunden 
		$p = 4 (2+2) 
		länge = 4-0 = 4
		string = abcd
		$ps = 4 -> OK 
		
		hi (2)
		ab stelle $p = 4 suchen stimmt
		$p = 7
		$p = 9 (+2)
		länge = 9 - 4 = 5
		ab 4 und 5 stellen efghi
		$ps=9
*/		
		
		
		
		
		while (($p=strpos($this->html, $find,$p)) !== false ) {		
			$p+=$len;

			/*
			// Datum ermitteln
			$str=substr($this->html,$ps,($p-$ps));
			$datumende=strrpos($str, "</h2>");
			$datumanfang=strrpos($str, "<h2>")+4;
			$datum=substr($str,$datumanfang,$datumende-$datumanfang);
			echo "*".$datum."*<br>";			
			*/			
			
			// Eingabe
			$dhtml.=substr($this->html,$ps,($p-$ps));
			$dhtml.='<form action="news.php" method="POST"><input type="hidden" name="pos" value="'.$id.'">';
			// $dhtml.='<input type="hidden" value="'.$datum.'" name="date">';
			$dhtml.='<input type="submit" value="Löschen" name="delete">&nbsp;<input type="submit" value="Bearbeiten" name="edit">';
			$dhtml.='</form>';
		

			$ps=$p;
			$id++;
			
		}
		
		$dt = new DateTime();
		// $dt = new DateTime('16.10.2022');
		$dhtml.='<form action="news.php" method="POST"><input type="hidden" name="pos" value=0>';
		$dhtml.='<input type="date" name="date" value="'. $dt->format('Y-m-d') .'" style="font-size:24px"><br><br>';
		$dhtml.='<textarea name="text" rows=20 cols=100></textarea><br><br>';
		$dhtml.='<input type="submit" value="OK" name="add" style="font-size:24px">';
		$dhtml.='</form>';
		
		$dhtml.=substr($this->html,$ps);
		// chdir('..');
		// echo "P=" . $ps ."<br>";
		echo $dhtml;
	}		
	/*
		Aenderung eines Eintrags
	*/
	public function displayEdit() {
		// Datum ermitteln
		$str=$this->html;
		$nr=1;
		$datumanfang=0;
		
		while($nr <= $_POST['pos']) {
			$datumanfang=strpos($str, "<h2>",$datumanfang)+4;
			$datumende=strpos($str, "</h2>",$datumanfang);
			$datum=substr($str,$datumanfang,$datumende-$datumanfang);

			$textanfang=strpos($str, "<p>",$datumende)+3;
			$textende=strpos($str, "</p>",$textanfang);
			$text=substr($str,$textanfang,$textende-$textanfang);
			
			$nr++;
		}
	

		$dt = new DateTime($datum);

		$dhtml ="<center>";
		$dhtml.='<form action="news.php" method="POST"><input type="hidden" name="pos" value="'.$_POST['pos'].'">';
		$dhtml.='<input type="date" name="date" value="'. $dt->format('Y-m-d') .'" style="font-size:24px"><br><br>';
		$dhtml.='<textarea name="text" rows=20 cols=100>'.$text.'</textarea><br><br>';
		$dhtml.='<input type="submit" value="Update" name="update" style="font-size:24px"> &nbsp;';
		$dhtml.='<input type="submit" value="Abbruch" name="cancel" style="font-size:24px">';
		$dhtml.='</form>';
		$dhtml.='</center>';

		$this->htmlhead();
		echo $dhtml;		
		$this->htmlfoot();
	}

	public function updateEdit() {
		$nr=1-2;
		$needle="<article>";
		$needle_size=strlen($needle);
		$article_start=0;
		$content_start=0;
		while($nr <= $_POST['pos']) {
			$article_start = strpos($this->html,$needle,$content_start);
			if ($article_start === false) {
				return false;
			}
			$content_start = $article_start+$needle_size;

			// $article_start+=$needle_size;
			$nr++;
		}
		
		$needle="</article>\r\n";
		$needle_size=strlen($needle);
		$content_end = strpos($this->html,$needle,$article_start);			
		if ($content_end === false) {
			return false;
		}
		$article_end = $content_end+$needle_size;
		
		$html = substr($this->html,0,$article_start);
		$html.= $this->getArticleHTML($_POST['date'],$_POST['text']);
		$html.= substr($this->html,$article_end);
		$this->html = $html;
	}
	
	private function update() {
	
	}
	
	/*
	private function getContent($suchstr_start,$suchstring_ende,$pos) {
		$c = 1;
		while ($c <= $pos) {
			
		}
	}
	*/
	
	private function htmlhead() {
		echo '
			<!DOCTYPE HTML>
			<html lang="de">

			<head>
				<title>Die Deymann\'s - Veranstaltungstechnik - Galerie</title>
				<meta charset="UTF-8">
				<meta name="keywords" lang="de" content="Veranstaltungstechnik,Freizeit,VA,Mischpult,Lichtpult,Tonpult,Mischer,Linearray">

				<meta name="author"     content="Jörg Deymann">
				<meta name="copyright"  content="Jörg Deymann">	
				<meta name="viewport"   content="width=device-width, initial-scale=1.0">    

				<link rel="stylesheet"    href="css/screen.css" >
				<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> 
			</head>		


				
			<body>
			<header>
				<nobr>Die Deymann\'s</nobr> Veranstaltungstechnik<hr>
				<div id="right">... das rockt !!!</div>
			</header>

			<nav>
				<a  href="leistungen.html">Leistungen</a>
				<a  href="technik.html">Technik</a>
				<a  href="wir.html">Wir</a>
				<a  href="news.html">Galerie</a>
				<a  href="termine.html">Termine</a>
				<a  href="referenz.html">Referenzen</a>
			</nav>

			<main>
		';
	}
	function htmlfoot() {
		echo '
			</main>
			<footer>
			<div id="grey">
			<b style="font-size:2vh;font-weight:1000;">Impressum</b><br>
			Die Deymann\'s<br>
			Lipperring 36<br>
			49733 Haren<br>
			<br>
			<b>Inhaber: </b>Jörg Deymann<br>
			<b>Mail: </b><a href="mailto:mail@die-deymanns.de">mail@die-deymanns.de</a><br>
			<b>Telefon: </b> +49 1515 69 39 313<br>
			<b>USt-Nr: </b>DE 338249165<br>
			<br>
			<b>&copy; Copyright 2021 Jörg Deymann</b>
			<br><br>
					<a href="impressum.html">Impressum</a><br>
					<a href="datenschutz.html">Datenschutz</a><br>
					<a href="agb.html">AGB</a><br>
			</div>
			<div style="text-align:center;vertical-align:center;">
			<b style="font-size:2vh;font-weight:1000;">Facebook</b><br>
			<a href="https://www.facebook.com/Die-Deymanns-101591522150694" target="_blank"> <img src="img/facebook.png" width="300px">
			</div>

			</footer>



			</body></html>
		';
	}
	
	
	
} 	
?>
