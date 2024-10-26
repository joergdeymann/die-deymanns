<?php
class news {
	private int $datasets = 0;
	private $html = "";
	private $htmlfile= 'news.html';
	
	/* 
		HTML Laden
	*/
	public function load() {
		$html = file_get_contents($this->htmlfile);
		// echo nl2br($zitate);
		// $pcre = sprintf('/%s/i', preg_quote('</article>'));
		$pcre = sprintf('/%s/i', preg_quote('<article>'));
		$num = preg_match_all($pcre, $html, $hits);
		$this->datasets = $num-1;
		$this->html = $html;
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
	
	private function getContent($suchstr_start,$suchstring_ende,$pos) {
		$c = 1;
		while ($c <= $pos) {
			
		}
	}
	
	
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
