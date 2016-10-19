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
require_once '../class/lostintranslationmention.class.php';

//WYSIWYG Editor
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

// Translations
$langs->load('main');
$langs->load('admin');
$langs->load('lostintranslation@lostintranslation');

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

// Load de l'objet courant
$lostMention = new LostInTranslationMention($conf->entity,$db);
$lostMention->load();
$TZone = array('Fr'=>'France','Eu'=>'Europe','NoEu'=>'Hors Europe');
$Tlanguages = $langs->get_available_languages(DOL_DOCUMENT_ROOT,12);

/*
 * Actions
 */
if($action == 'add_mention_translate') {
	$type = GETPOST('new_type');
	$lang = GETPOST('new_lang');
	$content = GETPOST('new_value');
	$saved = $lostMention->changeContent($type,$lang,$content);
	
	if ($saved)
	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
} else if ($action == 'update_mention') {
	$oldtype = GETPOST('typecode');
	$oldlang = GETPOST('langcode');
	$deleted = $lostMention->delete($oldtype,$oldlang);
	$type = GETPOST('edit_type');
	$lang = GETPOST('edit_lang');
	$content = GETPOST('edit_value');
	$saved = $lostMention->changeContent($type,$lang,$content);
	if ($saved)
	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
} else if ($action == 'delete') {
	$type = GETPOST('typecode');
	$lang = GETPOST('langcode');
	$deleted = $lostMention->delete($type,$lang);
	if ($deleted)
	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}



$page_name = "LostInTranslationSetup";
llxHeader('', $langs->trans($page_name),'','',0,0,array('/lostintranslation/js/lostintranslation.js'),array('/lostintranslation/css/lostintranslation.css'));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = lostintranslationAdminPrepareHead();
dol_fiche_head(
    $head,
    'mention',
    $langs->trans("Module104855Name"),
    0,
    "lostintranslation@lostintranslation"
);


// Setup page goes here
$form=new Form($db);
$formadmin=new FormAdmin($db);
$var=false;

// Liste des traductions 
print '<h2>'.$langs->trans('ListeTradMentions').'</h2>';

if($action == 'edit') {
	$gettype = GETPOST('typecode');
	$getlang = GETPOST('langcode');
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update_mention">';
	print '<input type="hidden" name="langcode" value="'.$getlang.'">';
	print '<input type="hidden" name="typecode" value="'.$gettype.'">';
}

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="100px">'.$langs->trans("Zone").'</td>'."\n";
print '<td width="250px">'.$langs->trans("Lang").'</td>'."\n";
print '<td>'.$langs->trans("Content").'</td>'."\n";
print '<td width="80px" align="right">'.$langs->trans("Actions").'</td>'."\n";
print '</tr>';

// On boucle sur toutes les données
if(!empty($lostMention->content)) {
	foreach($lostMention->content as $code_type => $TLang) {
		if(!empty($TLang)) {
			foreach($TLang as $code_lang => $mention) {
				$edit = '<a href="'.dol_buildpath('lostintranslation/admin/lostintranslation_mentions_setup.php?action=edit&typecode='.$code_type.'&langcode='.$code_lang.'">'.img_edit().'</a>',2);
				$delete = '<a href="'.dol_buildpath('lostintranslation/admin/lostintranslation_mentions_setup.php?action=delete&typecode='.$code_type.'&langcode='.$code_lang.'">'.img_delete().'</a>',2);
				
				$var=!$var;
				print '<tr '.$bc[$var].'>';
				
				if($action == 'edit' && $gettype == $code_type && $getlang == $code_lang) {
					
					print '<tr '.$bc[$var].'>';
					print '<td>';
					print Form::selectarray('edit_type', $TZone, $code_type, 0, 0, 0, '', 0, 0, 0, '', '', 1);
					print '</td>';
					print '<td>';
					print $formadmin->select_language($code_lang,'edit_lang');
					print '</td>';
					print '<td>';
					$doleditor = new DolEditor('edit_value', $mention, '', 142, 'new_value', 'In', false, true, true, ROWS_4, '90%');
					$doleditor->Create();
					print '</td>';
					print '<td align="right"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
					print '</tr>';
				} else {
					print '<td>'.$TZone[$code_type].'</td>';
					print '<td>'.$Tlanguages[$code_lang].'</td>';
					print '<td>'.$mention.'</td>';
					print '<td align="right">'.$edit.'&nbsp;'.$delete.'</td>';
				}
				print '</tr>';
			}
		}
	}
} else {
	print '<tr><td colspan="3">'.$langs->trans('noparam').'</td></tr>';
}


print '</table>';
if($action == 'edit') {
	print '</form>';
}


// Ajouter une trad
print '<h2>'.$langs->trans('AddTrad').'</h2>';
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="add_mention_translate">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Zone").'</td>'."\n";
print '<td>'.$langs->trans("Lang").'</td>'."\n";
print '<td>'.$langs->trans("Content").'</td>'."\n";
print '</tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>';
print Form::selectarray('new_type', $TZone, '', 0, 0, 0, '', 0, 0, 0, '', '', 1);
print '</td>';
print '<td>';
print $formadmin->select_language('','new_lang');
print '</td>';
print '<td>';
$doleditor = new DolEditor('new_value', '', '', 142, 'new_value', 'In', false, true, true, ROWS_4, '90%');
$doleditor->Create();
print '</td>';
print '</tr>';

$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td colspan="3" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
print '</tr>';

print '</table>';

print '</form>';

llxFooter();

$db->close();