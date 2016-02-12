<?php

class LostInTranslation {
	var $tabWord = array();
	var $nbTerms = 0;
	var $nbTrans = 0;
	
	// Constructeur qui charge toutes les traductions de la langue passée en paramètre dans tabWord
	function __construct($langtoload='en_US') {
		global $langs;
		
		foreach($langs->dir as $dir) {
			$langdir = $dir . '/langs/' . $langtoload . '/';
			if(!is_dir($langdir)) continue;

			$dirHandle = opendir($langdir);
			// Pour chaque fichier de langue trouvé
			while ($fname = readdir($dirHandle)) {
				if(substr($fname, -4) == 'lang') {
					$fileContent = file($langdir . $fname);
					
					// Pour chaque ligne du fichier de langs
					foreach($fileContent as $line) {
						if(substr($line,0,1) == '#') continue; // On ne prend pas en compte les ligne de commentaires
						
						$keyval = explode('=', $line);
						$key = trim($keyval[0]);
						$val = trim($keyval[1]);

						// Si on a déjà rencontré la clé, c'est qu'une traduction perso existe
						if(isset($this->tabWord[$fname][$key])) {
							$this->tabWord[$fname.'OLD'][$key] = $val;
							$this->nbTrans++;
						} else {
							$this->tabWord[$fname][$key] = $val;
							$this->nbTerms++;
						}
					}
				}
			}
			closedir($dirHandle);
		}
	}
	
	function searchWordInLangFiles($word) {
		global $langs;
		
		foreach($this->tabWord as $langfile => $trads) {
			foreach($trads as $key => $val) {
				if(strpos($val, $word) !== FALSE) {
					$this->searchRes[$langfile][$key] = $val;
				}
			}
		}
	}
}
