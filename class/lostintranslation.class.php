<?php

class LostInTranslation {
		
	var $customLangDir = '/lostintranslation/customlangs/langs/';
	var $lang = '';
	var $tabWord = array();
	var $nbTerms = 0;
	var $nbTrans = 0;
	
	var $searchRes = array();
	var $nbRes = 0;
	
	// Constructeur qui charge dans tabWord toutes les traductions de la langue passée en paramètre
	function __construct($langtoload='en_US') {
		global $langs;
		
		$this->lang = $langtoload;
		
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
						if(empty($line)) continue;
						if(substr($line,0,1) == '#') continue; // On ne prend pas en compte les ligne de commentaires
						if(strpos($line, '=') === FALSE) continue;
						
						$keyval = explode('=', $line);
						$key = trim($keyval[0]);
						$val = trim($keyval[1]);

						// Si on a déjà rencontré la clé, c'est qu'une traduction perso existe, on stocke l'ancienne dans 'official'
						if(isset($this->tabWord[$fname][$key])) {
							$this->tabWord[$fname][$key]['official'] = $val;
							$this->nbTrans++;
						} else {
							$this->tabWord[$fname][$key]['key'] = $key; // Pour faciliter la recherche
							$this->tabWord[$fname][$key]['custom'] = $val;
							$this->tabWord[$fname][$key]['official'] = $val;
							$this->nbTerms++;
						}
					}
					
					// Tri des traductions par clé
					if(!empty($this->tabWord[$fname])) ksort($this->tabWord[$fname]);
				}
			}
			closedir($dirHandle);
		}

		ksort($this->tabWord);
	}
	
	function searchWordInLangFiles($word, $search_option, $other_options) {
		global $langs;
		
		$func = 'strpos';
		if(!empty($other_options['case_insensitive'])) $func = 'stripos';
		
		foreach($this->tabWord as $langfile => $trads) {
			foreach($trads as $key => $val) {
				if($func($val[$search_option], $word) !== FALSE) {
					$this->searchRes[$langfile][$key] = $val;
					$this->nbRes++;
				}
			}
		}
	}
	
	function saveNewTranslation($langfile,$key,$newTranslation) {
		global $langs;
		
		// On vérifie si la traduction perso est différente de l'officielle
		if($this->tabWord[$langfile][$key]['official'] === $newTranslation
			|| $this->tabWord[$langfile][$key]['custom'] === $newTranslation) {
			setEventMessages($langs->trans('TranslationUnchanged'), array(), 'warnings');
			return;
		}
		
		if(!empty($newTranslation)) {
			$this->tabWord[$langfile][$key]['custom'] = $newTranslation;
			$newTranslation = $key . '=' . $newTranslation . "\n";
			$msg = 'NewTranslationSaved';
		} else { // Si la traduction passée est vide c'est qu'on veut remettre la traduction officielle
			$this->tabWord[$langfile][$key]['custom'] = $this->tabWord[$langfile][$key]['official'];
			$msg = 'OfficialTranslationRestored';
		}
		
		$pattern = '/' .$key. "=.*\n". '/';
		
		// Chemin du fichier de langs perso
		$customLangDir = dol_buildpath($this->customLangDir);
		$customLangPath = $customLangDir . $this->lang . '/';
		$customLangFile = $customLangPath . $langfile;
		
		dol_mkdir($customLangPath);
		
		// On remplace directement dans le fichier la traduction s'il existe
		if(is_file($customLangFile)) {
			$f = file_get_contents($customLangFile);
			$newfile = preg_replace($pattern, $newTranslation, $f);
			
			// Si la traduction n'existait pas avant, on l'ajoute dans le fichier
			if($newfile == $f) {
				$newfile = $f . $newTranslation;
			}
		} else {
			$newfile = $newTranslation;
		}
		
		file_put_contents($customLangFile, $newfile);
		
		setEventMessages($langs->trans($msg), array());
	}
	
	// Tells if custom folder needed for custom translation files is writable or not
	function isFolderWriteable() {
		$customLangDir = dol_buildpath($this->customLangDir);
		$customLangDir.= 'test/';
		
		$test = dol_mkdir($customLangDir);
		
		if($test < 0) {
			return false;
		} else {
			require_once DOL_DOCUMENT_ROOT . "/core/lib/files.lib.php";
			dol_delete_dir($customLangDir);
			return true;
		}
	}
}
