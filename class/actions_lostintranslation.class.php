<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_LostInTranslation.class.php
 * \ingroup LostInTranslation
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ActionsLostInTranslation
 */
class ActionsLostInTranslation
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		return 0;
	}

	
	function beforePDFCreation(&$parameters, &$object, &$action, $hookmanager)
	{
		global $conf,$user,$langs,$db,$mysoc;
		if (in_array('pdfgeneration',explode(':',$parameters['context']))) {
			$parameters['outputlangs']->load('lostintranslation@lostintranslation');
			$base_object = $parameters['object'];
			if(isset($base_object) && $base_object->element == 'facture')
			{
				dol_include_once('lostintranslation/class/lostintranslationmention.class.php');
				
				// Gestion du type de client
				$societe = $object->client;
				if($societe->country_code == 'FR') {
					//Cas Français$
					$type = 'Fr';
				} else  if($societe->isInEEC()){
					// Cas zone europe
					$type = 'Eu';
				} else {
					// Cas hors europe
					$type = 'NoEu';
				}
				
				// Récuperation de la traduction
				$lostMention = new LostInTranslationMention($conf->entity,$db);
				$lostMention->load();
				$lang_choose_to_generate = $parameters['outputlangs']->defaultlang;
				$mention = $lostMention->getTradFor($type, $lang_choose_to_generate);
				
				if(!empty($mention)) {
					// Définition de la trad à utiliser pour la conf
					$conf->global->FACTURE_FREE_TEXT = str_replace('<br />', '', html_entity_decode('* '.$mention,ENT_QUOTES)."\n\n");
				}
			}
		}
		return 1;
	}
}