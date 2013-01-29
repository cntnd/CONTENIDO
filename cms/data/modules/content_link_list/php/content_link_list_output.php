<?php
    /**
     * description: standard link list
     *
     * @package Module
     * @subpackage content_list
     * @author timo.trautmann@4fb.de
     * @copyright four for business AG <www.4fb.de>
     * @license http://www.contenido.org/license/LIZENZ.txt
     * @link http://www.4fb.de
     * @link http://www.contenido.org
     */

    // When in backend edit mode add a label so the author
    // knows what to type in the shown field.
    // When not in backend edit mode any tags are removed
    // for the template is responsible for displaying the
    // given text as a header.
    if (cRegistry::isBackendEditMode()) {
        $label = mi18n("LABEL_HEADER_LINKLIST");
    } else {
        $label = NULL;
    }

    // use smarty template to output header text
    $tpl = Contenido_SmartyWrapper::getInstance();


    global $force;
    if (1 == $force) {
        $tpl->clearAllCache();
    }

    //TODO IMPLEMENTATION OF LOGIC
    $tpl->assign('label', $label);
    $tpl->display('content_link_list/template/get.tpl');
?>