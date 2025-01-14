<?php

/**
 * This file contains the Frontend user editor.
 *
 * @package    Plugin
 * @subpackage Newsletter
 * @author     Bjoern Behrens
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * @var cAuth $auth
 * @var cPermission $perm
 * @var cSession $sess
 * @var array $cfg
 * @var string $area
 * @var int $client
 * @var int $lang
 * @var int $frame
 */

$oPage = new cGuiPage("recipients_edit", "newsletter");
$oRecipients = new NewsletterRecipientCollection();

if (cHasPlugins('recipients')) {
    cIncludePlugins('recipients');
}

$requestIdRecipient = (isset($_REQUEST['idrecipient'])) ? cSecurity::toInteger($_REQUEST['idrecipient']) : 0;
$requestConfirmed = (isset($_REQUEST['confirmed'])) ? cSecurity::toInteger($_REQUEST['confirmed']) : 0;
$requestDeactivated = (isset($_REQUEST['deactivated'])) ? cSecurity::toInteger($_REQUEST['deactivated']) : 0;
$requestNewstype = (isset($_REQUEST['newstype'])) ? cSecurity::toInteger($_REQUEST['newstype']) : 0;
$requestName = (isset($_REQUEST['name'])) ? cSecurity::toString($_REQUEST['name']) : '';
$requestEmail = (isset($_REQUEST['email'])) ? cSecurity::toString($_REQUEST['email']) : '';
$action = $action ?? '';

// Note, that the object name has to be $recipient for plugins
if ($action == "recipients_create" && $perm->have_perm_area_action($area, $action)) {
    $recipient = $oRecipients->create("mail@domain.tld"," ".i18n("-- New recipient --", 'newsletter'));
    $oPage->reloadLeftBottomFrame(['idrecipient' => $recipient->get('idnewsrcp')]);
} elseif ($action == "recipients_delete" && $perm->have_perm_area_action($area, $action)) {
    $oRecipients->delete($requestIdRecipient);
    $recipient = new NewsletterRecipient();
    $oPage->reloadLeftBottomFrame(['idrecipient' => null]);
    $oPage->abortRendering();
} elseif ($action == "recipients_purge" && $perm->have_perm_area_action($area, "recipients_delete")) {
    $oClient = new cApiClient($client);
    $timeframe = $oClient->getProperty("newsletter", "purgetimeframe");
    if (!$timeframe) {
        $timeframe = 30;
    }
    $purgedrecipients = $oRecipients->purge($timeframe);
    /* backslashdollar: There is a problem translating \$ - it is either not recognized or translated correctly (using poEdit) */
    if ($purgedrecipients > 0) {
        $oPage->displayOk(sprintf(str_replace("backslashdollar", "\$", i18n("%1backslashdollard recipients, which hasn't been confirmed since more than %2backslashdollard days has been removed.", 'newsletter')),$purgedrecipients,$timeframe));
    } else {
        $oPage->displayInfo(sprintf(str_replace("backslashdollar", "\$", i18n("There are no recipients, which hasn't been confirmed since more than %2backslashdollard days has been removed.", 'newsletter')), 0, $timeframe));
    }

    $recipient = new NewsletterRecipient;
    $oPage->reloadLeftBottomFrame(['idrecipient' => null]);
} else {
    $recipient = new NewsletterRecipient($requestIdRecipient);
}

if (true === $recipient->isLoaded() && $recipient->get("idclient") == $client && $recipient->get("idlang") == $lang) {
    $aMessages = [];
    if ($action == "recipients_save" && $perm->have_perm_area_action($area, $action)) {
        $oPage->reloadLeftBottomFrame(['idrecipient' => $recipient->get('idnewsrcp')]);

        $name = stripslashes($requestName);
        $email = stripslashes($requestEmail);
        $confirmed = $requestConfirmed;
        $deactivated = $requestDeactivated;
        $newstype = $requestNewstype;

        $recipient->set("name", $name);

        if (!isValidMail($email)) {
            $aMessages[] = i18n("Please specify a valid e-mail address", 'newsletter');
        } else {
            $email = cString::toLowerCase($email); // e-mail always in lower case
            if ($recipient->get("email") != $email) {
                $oRecipients->resetQuery();
                $oRecipients->setWhere("email", $email);
                $oRecipients->setWhere("idclient", $client);
                $oRecipients->setWhere("idlang", $lang);
                $oRecipients->setWhere($recipient->getPrimaryKeyName(), $recipient->get($recipient->getPrimaryKeyName()), "!=");
                $oRecipients->query();

                if ($oRecipients->next()) {
                    $aMessages[] = i18n("Could not set new e-mail address: Other recipient with same e-mail address already exists", 'newsletter');
                } else {
                    $recipient->set("email", $email);
                }
            }
        }

        if ($recipient->get("confirmed") != $confirmed && $confirmed) {
            $recipient->set("confirmeddate", date("Y-m-d H:i:s"), false);
        } elseif (!$confirmed) {
            $recipient->set("confirmeddate", "0000-00-00 00:00:00", false);
        }
        $recipient->set("confirmed",   $confirmed);
        $recipient->set("deactivated", $deactivated);
        $recipient->set("news_type",   $newstype);

        // Check out if there are any plugins
        if (cHasPlugins('recipients')) {
            cCallPluginStore('recipients');
        }

        $recipient->store();

        // Remove group associations
        if (isset($_REQUEST["ckbRemove"])) {
            $oGroupMembers = new NewsletterRecipientGroupMemberCollection();

            foreach ($_REQUEST["ckbRemove"] as $iGroupMemberID) {
                if (is_numeric($iGroupMemberID)) {
                    $oGroupMembers->delete($iGroupMemberID);
                }
            }
        }
    }

    if (count($aMessages) > 0) {
        $oPage->displayWarning(implode("<br>", $aMessages));
    }

    $oForm = new cGuiTableForm("properties");
    $oForm->setVar("frame",  $frame);
    $oForm->setVar("area",   $area);
    $oForm->setVar("action", "recipients_save");
    $oForm->setVar("idrecipient", $recipient->get("idnewsrcp"));

    $oForm->setHeader(i18n("Edit recipient", 'newsletter'));

    $oTxtName = new cHTMLTextbox("name",     $recipient->get("name"), 40);
    $oTxtEMail = new cHTMLTextbox("email", $recipient->get("email"), 40);
    $oCkbConfirmed = new cHTMLCheckbox("confirmed", "1");
    $oCkbConfirmed->setChecked($recipient->get("confirmed"));
    $oCkbDeactivated = new cHTMLCheckbox("deactivated", "1");
    $oCkbDeactivated->setChecked($recipient->get("deactivated"));

    $oSelNewsType = new cHTMLSelectElement("newstype");
    $oOption = new cHTMLOptionElement(i18n("Text only", 'newsletter'), "0");
    $oSelNewsType->appendOptionElement($oOption);
    $oOption = new cHTMLOptionElement(i18n("HTML and text", 'newsletter'), "1");
    $oSelNewsType->appendOptionElement($oOption);
    $oSelNewsType->setDefault($recipient->get("news_type"));

    $oForm->add(i18n("Name", 'newsletter'), $oTxtName->render());
    $oForm->add(i18n("E-Mail"), $oTxtEMail->render());
    $oForm->add(i18n("Confirmed", 'newsletter'), $oCkbConfirmed->toHtml(false) . " (" . $recipient->get("confirmeddate") . ")");
    $oForm->add(i18n("Deactivated", 'newsletter'),  $oCkbDeactivated->toHtml(false));
    $oForm->add(i18n("Message type", 'newsletter'), $oSelNewsType->render());

    $aPluginOrder = cArray::trim(explode(',', getSystemProperty('plugin', 'recipients-pluginorder')));

    // Check out if there are any plugins
    if (is_array($aPluginOrder)) {
        foreach ($aPluginOrder as $sPlugin) {
            if (function_exists("recipients_".$sPlugin."_getTitle") &&
                function_exists("recipients_".$sPlugin."_display")) {
                    $aPluginTitle = call_user_func("recipients_".$sPlugin."_getTitle");
                    $aPluginDisplay = call_user_func("recipients_".$sPlugin."_display", $recipient);

                    if (is_array($aPluginTitle) && is_array($aPluginDisplay)) {
                        foreach ($aPluginTitle as $sKey => $sValue) {
                            $oForm->add($sValue, $aPluginDisplay[$sKey]);
                        }
                    } else {
                        if (is_array($aPluginTitle) || is_array($aPluginDisplay)) {
                            $oForm->add(i18n("WARNING", 'newsletter'), sprintf(i18n("The plugin %s delivered an array for the displayed titles, but did not return an array for the contents.", 'newsletter'), $sPlugin));
                        } else {
                            $oForm->add($aPluginTitle, $aPluginDisplay);
                    }
                }
            }
        }
    }

    $oGroupList = new cGuiList();

    $oAssocGroups = new NewsletterRecipientGroupMemberCollection();
    $oAssocGroups->link("NewsletterRecipientGroupCollection");
    $oAssocGroups->setWhere("idnewsrcp", $recipient->get("idnewsrcp"));
    $oAssocGroups->setOrder("groupname");
    $oAssocGroups->query();

    if ($oAssocGroups->count() == 0) {
        $oGroupList->setCell(0, 1, i18n("Recipient is not member of any group", 'newsletter'));
    } else {
        // Headline
        $oGroupList->setCell(0, 1, "<strong>".i18n("Groupname", 'newsletter')."</strong>");
        $oImgDel = new cHTMLImage("images/delete.gif");
        $oGroupList->setCell(0, 2, $oImgDel->render());

        // Data
        while ($oAssocGroup = $oAssocGroups->next()) {
            $oGroup = $oAssocGroups->fetchObject("NewsletterRecipientGroupCollection");

            $oCkbRemove = new cHTMLCheckbox("ckbRemove[]", $oAssocGroup->get("idnewsgroupmember"));
            $oGroupList->setCell($oAssocGroup->get("idnewsgroupmember"), 1, $oGroup->get("groupname"));
            $oGroupList->setCell($oAssocGroup->get("idnewsgroupmember"), 2, $oCkbRemove->toHtml(false));
        }
    }

    $oForm->add(i18n("Associated groups", 'newsletter'), $oGroupList->render());

    $oUser = new cApiUser($recipient->get("author"));
    $oForm->add(i18n("Author", 'newsletter'), $oUser->get('username') . " (". $recipient->get("created").")");
    $oUser = new cApiUser($recipient->get("modifiedby"));
    $oForm->add(i18n("Last modified by", 'newsletter'), $oUser->get('username') . " (". $recipient->get("lastmodified").")");

    $oPage->setContent($oForm);
}

$oPage->render();
