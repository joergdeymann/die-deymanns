<?php
// include "debug.class.php";

/*
$object 
"r40" = JPG right 40%
"l40" = JPG left  40%
"c50" = JPG normal 50%
"text" = Text 
"clear" Clear für Bilder
*/
		
class format {
	public $tagonly;  // zB: IMG
	public $tag;     // img id="LEFT40"
	public $content; // leer oder bei <p> = text  	
	function __construct($f="",$t="",$c="") {
		$this->tagonly=$f;
		$this->tag=$t;
		$this->content=$c;		
	}		
}

class article {
	public $id;         // Reihenfolgen id
	public $date;       // Eingestelltes Datum als Datetime
	public $object;     // Liste mit Objekten siehe oben
	public $article_start;  // Arcticle Startposition, inclusive <article>
	public $article_size;   // Article groesse inclusive <article> und </article>
	public $html;           // Text zwischen <article> und </article>
	
	
	// nicht beutzt: private $id=0;  // Zähler für die Objekte, zum ablesen von aussen
	
	function __construct($id=0) {
		$this->init();
		$this->id = $id;
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
		
		$suche="/<h2>(.*?)<\/h2>/is";
		preg_match($suche,$this->html,$matches,PREG_OFFSET_CAPTURE);
		$this->date=new DateTime($matches[1][0]);
		// echo $this->date->format('d.m.Y');	
	
		
		
		
		// suche naechstes TAG
		$suche="/<(.*?)>(.*?)<(.*?)>/is";
		$offset=0;
		
		while(preg_match($suche,$this->html,$matches,PREG_OFFSET_CAPTURE,$offset)) {
			$tags=explode(" ",$matches[1][0],2);
			$tag = $tags[0];
			
			/* 
				<p> </p> komplett nehmen hier nicht auf Tags achten
			*/
			if ($tag == "p") {
				$suchep="/<(p.*?)>(.*?)<(\/p)>/is";
				preg_match($suchep,$this->html,$matches,PREG_OFFSET_CAPTURE,$offset);
			} else
			if ($tag == "video") { # NEU
				$suchep="/<(video.*?)>(.*?)<(\/video)>/is";
				preg_match($suchep,$this->html,$matches,PREG_OFFSET_CAPTURE,$offset);
			}
			/* 
				Offset correction:
				<a></a> = normal weitersuchen
				<img><a> = die 3 stellen von <a> wieder zurueck
			*/			
/*
			if ("/".$matches[1][0] != $matches[3][0]) {
				$offsetcorrection = strlen($matches[3][0])+2;
			} else {
				$offsetcorrection = 0;
			}
*/
			/* 
				<video width=""> </video>
			*/
			
			// /img id="abc"  -> img = Falsche
			if ('/'.$tag != $matches[3][0]) { #NEU
				$offsetcorrection = strlen($matches[3][0])+2;
			} else {
				$offsetcorrection = 0;
			}
			
			$offset= $matches[0][1] + strlen($matches[0][0]) - $offsetcorrection;
			$this->object[] = new format($tag,$matches[1][0],$matches[2][0]);
		}
		/* 
			Am ende des Artikels steht kein Tag ?
			also auf Rest checken 			
		*/
		$suche="/<(.*?)>(.*)/is";		
		if (preg_match($suche,$this->html,$matches,PREG_OFFSET_CAPTURE,$offset)) {
			$tags=explode(" ",$matches[1][0],2);
			$tag = $tags[0];
			$this->object[] = new format($tag,$matches[1][0],$matches[2][0]);			
			$offset= $matches[0][1] + strlen($matches[0][0]);
		}
					
	}
	
	public function displayArticleEditor() {
		$tags=sizeof($this->object);
		
		$dhtml ='<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
		$dhtml.='<article>';
		
		$c=0;
		foreach ($this->object as $format) {
			$c++;
			$tag=explode(" ",$format->tag,2);



			// debug("Tag=".$format->tag); // echo "Tag=".$format->tag."<br>";
			
			
			if ($tag[0] == "img") {
				preg_match("/src=\"(.*)\"/",$format->tag,$link);
			
				$checked1="";
				$checked2="";
				$checked3="";
				$checked4="";
				
				// echo "**FormatTag=".$format->tag;
				if (stripos($format->tag,"left40")) {
					$checked1="CHECKED";
				}
				if (stripos($format->tag,"norm50")) {
					$checked2="CHECKED";
				}
				if (stripos($format->tag,"right40")) {
					$checked3="CHECKED";
				}

				
				$dhtml.='<' . $format->tag . '><br>';
				$dhtml.='<input type="radio" '.$checked1.' value = "left40"        name="pos'  .$c. '">Links 40%<br>';
				$dhtml.='<input type="radio" '.$checked2.' value = "norm50"        name="pos'  .$c. '">Standart 50%<br>';
				$dhtml.='<input type="radio" '.$checked3.' value = "right40"       name="pos'  .$c. '">Rechts 40%<br>';
				$dhtml.='<input type="radio"               value = "delete"        name="pos'  .$c. '">Entfernen<br>';
				$dhtml.='<input type="hidden"              value = "'.$link[1].'" name="link' .$c. '">';
				// left40"
				$dhtml.='<br id="clear"><br>';
			} else 
			if ($tag[0] == "video") {
				$dhtml.='<' . $format->tag . '>'.$format->content.'</video><br>';
				
				// $dhtml.='<input type="hidden" value="'..'" name="YY3">';
				
				$dhtml.='<input type="hidden"   value = "'.htmlspecialchars($format->content).'"  name="videolink' .$c. '">';
				$dhtml.='<input type="checkbox" value = "delete"                                  name="deletevideo'  .$c. '">Video Entfernen<br>';				
			}

			if ($tag[0] == "h2") {
				$dt = new DateTime($format->content);
				$dhtml.="<h2>".$format->content."</h2>";
				$dhtml.='<h2><input type="date" name="datum' .$c. '" value="'.$dt->format('Y-m-d').'"></h2>';				
			} else 
			if ($tag[0] == "p") {
				$content=preg_replace("/\r\n/i","",$format->content);         // eingelesene \r\n loeschen
				$content=preg_replace('/\<br(\s*)?\/?\>/i', "\r\n", $content); // und br in \r\n umwandeln
				
				// $content=preg_replace('/\<br\>/i', "\r\n", $format->content);

				$dhtml.="<p>".$format->content."</p>";
				// $dhtml.='<p><textarea cols=80 rows=10 name="text' . $c .'">'.$content.'</textarea></p><br>';
				$dhtml.='<textarea cols=80 rows=10 name="text' . $c .'">'.$content.'</textarea>';
				$dhtml.='<input type="checkbox" name="deletetext'.$c.'">Text entfernen<br><br>'; 
			}
			if ($format->tag == 'br id="clear"') {
				$dhtml.="<label>";
				$dhtml.="<".$format->tag.">";
				// $dhtml.='<input type="checkbox" value = "delete" name="imgbr' .$c. '">Zeilenumbruch für Bilder entfernen<br>';

				$dhtml.='<input type="hidden"   value = "imgbr"  name="imgbr' .$c. '">'; 
				$dhtml.='<input type="checkbox" value = "delete" name="deleteimgbr' .$c. '">Zeilenumbruch für Bilder entfernen<br>'; 
				$dhtml.="</label>";
			}
			if ($format->tag == 'br') {
				$dhtml.="<br>";
				$dhtml.='<input type="hidden"  name="br'.$c.'">'; 
			}				
				
		}
		
		$dhtml.='<input style="font-size:2em" type="submit" value = "Änderungen übernehmen" name="change">';
		$dhtml.='<b style="padding-left:20px">';
		$dhtml.='<input style="font-size:2em" type="submit" value = "Artikel entfernen" name="delete">';
		$dhtml.='<input type="hidden" name="id" value="'.$this->id.'">';		
		$dhtml.='</article>';
		
		
		$dhtml.='</form>';
		
		return $dhtml;
		
	}		

	
	
}
		
		
		
class termine {

	public $html = "";
	private $htmlfile= 'termine.html';
	private $content_start=0; // 1. brauchbares auftreten von <article> inclusive TAG
	private $content_ende=0;  // 2. letztes brauchbares auftreten von </article> inclusive TAG

	private $article_list=array();
	private $article_start=0; // temporäre Werte des Aktuelen Artikels
	private $article_end=0;	  // temporäre Werte des Aktuelen Artikels
	
	function __construct() {
		$this->load();
	}
	/* 
		HTML Laden
	*/
	public function load() {
		$this->html = file_get_contents($this->htmlfile);
		$this->reset();
	}
	public function setHTML($html) {
		$this->html = $html;
		$this->reset();		
	}
	
	private function reset() {
		$this->article_list=array();

		$this->content_start=0; // 1. brauchbares auftreten von <article> inclusive TAG
		$this->content_end=0;  // 2. letztes brauchbares auftreten von </article> inclusive TAG

		$this->article_start=0; // temporäre Werte des Aktuelen Artikels
		$this->article_end=0;	// temporäre Werte des Aktuelen Artikels
	}

	public function getHTMLHeader() {
		return substr($this->html,0,($this->content_start-1));		
	}
	
	public function getHTMLFooter() {
		return substr($this->html,($this->content_end+1));
	}
	
	public function getArticleList($id="") {
		if ($id == "") {
			return $this->article_list;
		}
		return $this->article_list[$id];	
	}
	
	/* 
		Article = Beitrag <article></article> 
		$content = Object: Article mit Html und weiteren infos
	*/
	public function addArticle($content) {
		$content->id=count($this->article_list)+1;
		$this->article_list[]=$content;

		if ($this->content_start == 0) {
			$this->content_start = $content->article_start;
		}
		$this->content_end = $content->article_start+$content->article_size;
	}

	/*
		Naechsten Artikel in der HTML suchen 
		Rückgabe:
		Object artcle() oder false
	*/
	public function getNextArticleHTML() {
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
			
	
} 	
?>
