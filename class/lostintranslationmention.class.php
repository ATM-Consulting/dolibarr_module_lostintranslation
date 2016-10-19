<?php

class LostInTranslationMention {
	public $entity;
	public $type;
	public $lang;
	public $content;
	private $db;
	
	/**
	 * On construit l'objet de base
	 * 
	 * @var entity		entité active
	 * @var	db			db général pour save en final
	 * 
	 */
	function __construct($entity, $db) {
		$this->entity = $entity;
		$this->db = $db;
	}
	
	/**
	 * On charge l'objet avec ses infos
	 * 
	 * @var type 	euro / hors euro / france
	 * 
	 */
	function load() {
		global $conf;
		$this->content = unserialize($conf->global->LOSTINTRANSLATION_MENTION);
	}
	
	/**
	 * On change le contenu d'un type complet
	 * Cette fonction neccessite un load pour fonctionner
	 * 
	 * @var type				Zone de langue
	 * @var lang				Langue dans laquelle on rentre la mention légale
	 * @var newcontenttoadd		Nouveau contenu à enregistrer pour la lang
	 * 
	 * @return	false (ko) true (ok)
	 * 
	 */
	function changeContent($type,$lang,$newcontenttoadd) {
		if(!empty($type)) {
			if(!empty($this->content)) {
				// on se fiche de savoir si la langue existe déjà ou pas. On modifie dans tous les cas
				$this->content[$type][$lang] = $newcontenttoadd;
			} else {
				// Cas Type et lang n'existe pas (premier add)
				$this->content = array($type=>array($lang=>$newcontenttoadd));
			}
			return $this->save();
		} else {
			return false;
		}
	}
	
	/**
	 * On sauvegarde le contenu en conf pour un type et une langue
	 * 
	 * @var lang				Langue dans laquelle on rentre la mention légale
	 * 
	 * @return	false (ko) true (ok)
	 * 
	 */
	function save() {
		if (dolibarr_set_const($this->db, 'LOSTINTRANSLATION_MENTION', serialize($this->content), 'chaine', 0, '', $this->entity) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * On change le contenu d'un type complet
	 * Cette fonction neccessite un load pour fonctionner
	 * 
	 * @var type				Zone de langue
	 * @var lang				Langue dans laquelle on rentre la mention légale
	 * @var newcontenttoadd		Nouveau contenu à enregistrer pour la lang
	 * 
	 * @return	content pour une type et pour une langue
	 * 
	 */
	function getTradFor($type,$lang) {
		if(!empty($this->content[$type][$lang])) {
			return $this->content[$type][$lang];
		} else {
			return null;
		}
	}
	
	/**
	 * On change le contenu d'un type complet
	 * Cette fonction neccessite un load pour fonctionner
	 * 
	 * @var type				Zone de langue
	 * @var lang				Langue dans laquelle on rentre la mention légale
	 * 
	 * @return	false(ko) true(ok)
	 * 
	 */
	function delete($type,$lang) {
		unset($this->content[$type][$lang]);
		return $this->save();
	}
	
}
