<?php
/**
 * This file contains the table form GUI class.
 *
 * @package          Core
 * @subpackage       GUI
 * @version          SVN Revision $Rev:$
 *
 * @author           Mischa Holz
 * @copyright        four for business AG <www.4fb.de>
 * @license          http://www.contenido.org/license/LIZENZ.txt
 * @link             http://www.4fb.de
 * @link             http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * Table form GUI class
 *
 * @package    Core
 * @subpackage GUI
 */
class cGuiTableForm {

    public $items;
    public $captions;
    public $id;
    public $rownames;
    public $itemType;
    public $formname;
    public $formmethod;
    public $formaction;
    public $formvars;
    public $tableid;
    public $header;
    public $cancelLink;
    public $submitjs;

    public function __construct($name, $action = "", $method = "post") {
        global $sess;
        $this->formname = $name;

        if ($action == "") {
            $this->formaction = "main.php";
        } else {
            $this->formaction = $action;
        }

        $this->formmethod = $method;

        $this->tableid = "";
        $this->custom = array();

        $this->setActionButton("submit", cRegistry::getBackendUrl() . "images/but_ok.gif", i18n("Save changes"), "s");
    }

    public function setVar($name, $value) {
        $this->formvars[$name] = $value;
    }

    public function add($caption, $field, $rowname = "") {
        $n = "";

        if (is_array($field)) {
            foreach ($field as $value) {
                if (is_object($value) && method_exists($value, "render")) {
                    $n .= $value->render();
                } else {
                    $n .= $value;
                }
            }

            $field = $n;
        }
        if (is_object($field) && method_exists($field, "render")) {
            $n = $field->render();
            $field = $n;
        }
        if ($field == "") {
            $field = "&nbsp;";
        }

        if ($caption == "") {
            $caption = "&nbsp;";
        }

        $this->id++;
        $this->items[$this->id] = $field;
        $this->captions[$this->id] = $caption;

        if ($rowname == "") {
            $rowname = $this->id;
        }

        $this->rownames[$this->id] = $rowname;
    }

    public function addCancel($link) {
        $this->cancelLink = $link;
    }

    public function addHeader($header) {
        $this->header = $header;
    }

    public function addSubHeader($header) {
        $this->id++;
        $this->items[$this->id] = '';
        $this->captions[$this->id] = $header;
        $this->itemType[$this->id] = 'subheader';
    }

    public function setSubmitJS($js) {
        $this->submitjs = $js;
    }

    public function setActionEvent($id, $event) {
        $this->custom[$id]["event"] = $event;
    }

    public function setActionButton($id, $image, $description = "", $accesskey = false, $action = false) {
        $this->custom[$id]["image"] = $image;
        $this->custom[$id]["type"] = "actionsetter";
        $this->custom[$id]["action"] = $action;
        $this->custom[$id]["description"] = $description;
        $this->custom[$id]["accesskey"] = $accesskey;
        $this->custom[$id]["event"] = "";
    }

    public function setConfirm($id, $title, $description) {
        $this->custom[$id]["confirmtitle"] = $title;
        $this->custom[$id]["confirmdescription"] = $description;
    }

    public function setTableID($tableid) {
        $this->tableid = $tableid;
    }

    public function unsetActionButton($id) {
        unset($this->custom[$id]);
    }

    public function render($return = true) {
        global $sess, $cfg;

        $tpl = new cTemplate();

        if ($this->submitjs != "") {
            $tpl->set("s", "JSEXTRA", 'onsubmit="' . $this->submitjs . '"');
        } else {
            $tpl->set("s", "JSEXTRA", '');
        }

        $tpl->set("s", "FORMNAME", $this->formname);
        $tpl->set("s", "METHOD", $this->formmethod);
        $tpl->set("s", "ACTION", $this->formaction);

        $this->formvars[$sess->name] = $sess->id;

        $hidden = "";
        if (is_array($this->formvars)) {
            foreach ($this->formvars as $key => $value) {
                $val = new cHTMLHiddenField($key, $value);
                $hidden .= $val->render() . "\n";
            }
        }

        if (!array_key_exists("action", $this->formvars)) {
            $val = new cHTMLHiddenField("", "");
            $hidden .= $val->render() . "\n";
        }

        $tpl->set("s", "HIDDEN_VALUES", $hidden);

        $tpl->set('s', 'ID', $this->tableid);

        $header = "";
        if ($this->header != "") {
            $tablerow = new cHTMLTableRow();
            $tablehead = new cHTMLTableHead();
            $tablehead->setAttribute("colspan", "2");
            $tablehead->setAttribute("valign", "top");
            $tablehead->setContent($this->header);
            $tablerow->setContent($tablehead);
            $header = $tablerow->render();
        }

        $tpl->set('s', 'HEADER', $header);

        if (is_array($this->items)) {
            foreach ($this->items as $key => $value) {
                if ($this->itemType[$key] == 'subheader') {
                    $tablerow = new cHTMLTableRow();
                    $tabledata = new cHTMLTableData();
                    $tabledata->setAttribute("colspan", "2");
                    $tabledata->setAttribute("valign", "top");
                    $tabledata->setContent($this->captions[$key]);
                    $tablerow->setContent($tablehead);

                    $tpl->set('d', 'SUBHEADER', $tablerow->render());
                } else {
                    $tpl->set('d', 'SUBHEADER', '');
                    $tpl->set('d', 'CATNAME', $this->captions[$key]);
                    $tpl->set('d', 'CATFIELD', $value);
                    $tpl->set('d', 'ROWNAME', $this->rownames[$key]);

                    $tpl->next();
                }
            }
        }

        if ($this->cancelLink != "") {
            $image = new cHTMLImage(cRegistry::getBackendUrl() . 'images/but_cancel.gif');
            $link = new cHTMLLink($this->cancelLink);
            $link->setContent($image);

            $tpl->set('s', 'CANCELLINK', $link->render());
        } else {
            $tpl->set('s', 'CANCELLINK', '');
        }

        $custombuttons = "";

        foreach ($this->custom as $key => $value) {
            if ($value["accesskey"] != "") {
                $accesskey = $value["accesskey"];
            } else {
                $accesskey = "";
            }

            $onclick = "";
            if ($value["action"] !== false) {

                if ($value["confirmtitle"] != "") {
                    $action = 'document.forms["' . $this->formname . '"].elements["action"].value = "' . $value['action'] . '";';
                    $action .= 'document.forms["' . $this->formname . '"].submit()';

                    $onclick = 'showConfirmation("' . $value['confirmdescription'] . '", function() { ' . $action . ' });return false;';
                } else {
                    $onclick = 'document.forms["' . $this->formname . '"].elements["action"].value = "' . $value['action'] . '";';
                }
            }

            if ($value["event"] != "") {
                $onclick .= $value["event"];
            }

            $button = new cHTMLFormElement("submit", "", "", "", "", "image_button");
            $button->setAttribute("type", "image");
            $button->setAttribute("src", $value["image"]);
            $button->setAlt($value['description']);
            $button->setAttribute("accesskey", $accesskey);
            $button->setEvent("onclick", $onclick);
            $custombuttons .= $button->render();
        }

        $tpl->set('s', 'EXTRABUTTONS', $custombuttons);

        $tpl->set('s', 'ROWNAME', $this->id);

        $rendered = $tpl->generate(cRegistry::getBackendPath() . $cfg['path']['templates'] . $cfg['templates']['generic_table_form'], true);

        if ($return == true) {
            return ($rendered);
        } else {
            echo $rendered;
        }
    }

}