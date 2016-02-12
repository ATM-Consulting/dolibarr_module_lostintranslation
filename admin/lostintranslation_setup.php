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
 * 	\file		admin/lostintranslation.php
 * 	\ingroup	lostintranslation
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT . "/core/class/html.formadmin.class.php";
require_once '../lib/lostintranslation.lib.php';
require_once '../class/lostintranslation.class.php';

// Translations
$langs->load("lostintranslation@lostintranslation");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$word = GETPOST('word', 'alpha');
$langtosearch = GETPOST('langtosearch', 'alpha');

/*
 * Actions
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_set_const($db, $code, GETPOST($code), 'chaine', 0, '', $conf->entity) > 0)
	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}
	
if (preg_match('/del_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

if($action == 'search_word' && !empty($word)) {
	$lit = new LostInTranslation($langtosearch);
	$lit->searchWordInLangFiles($word);
}

/*
 * View
 */
$page_name = "LostInTranslationSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = lostintranslationAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module104855Name"),
    0,
    "lostintranslation@lostintranslation"
);

// Setup page goes here
$form=new Form($db);
$formadmin=new FormAdmin($db);
$var=false;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";

// Select lang to customize
$current_lang = $user->conf->MAIN_LANG_DEFAULT;
if(empty($current_lang)) $current_lang = $conf->global->MAIN_LANG_DEFAULT;
if(empty($current_lang)) $current_lang = 'en_US';
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("SelectLangToCustomize").'</td>';
print '<td align="center">&nbsp;</td>';
print '<td align="right">';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="search_word">';
print '<input type="text" name="word" value="'.$word.'">';
print $formadmin->select_language($current_lang,'langtosearch');
print '<input type="submit" class="button" value="'.$langs->trans("Search").'">';
print '</form>';
print '</td></tr>';

print '</table>';

if(!empty($lit->searchRes)) {
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("File").'</td>'."\n";
	print '<td>'.$langs->trans("Key").'</td>'."\n";
	print '<td>'.$langs->trans("Value").'</td>'."\n";
	print '</tr>';
	
	foreach($lit->searchRes as $langfile => $trads) {
		foreach($trads as $key => $val) {
			$val = str_replace($word, '<span style="background-color: yellow;">'.$word.'</span>', $val);
			print '<tr>';
			print '<td>'.$langfile.'</td>'."\n";
			print '<td>'.$key.'</td>'."\n";
			print '<td>'.$val.'</td>'."\n";
			print '</tr>';
		}
	}
}

//echo '<pre>';
//print_r($lit->searchRes);

llxFooter();

$db->close();