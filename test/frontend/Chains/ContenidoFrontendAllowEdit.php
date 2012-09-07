<?php
/**
 * Unittest for Contenido chain Contenido.Frontend.AllowEdit.
 *
 * @author      Murat Purc <murat@purc.de>
 * @date        30.12.2009
 * @category    Testing
 * @package     Contenido_Frontend
 * @subpackage  Chains
 */


/**
 * 1. chain function to check if the user has permission to edit articles in this category
 */
function chain_ContenidoFrontendAllowEdit_Test($lang, $idcat, $idart, $uid)
{
    return true;
}

/**
 * 2. chain function to check if the user has permission to edit articles in this category
 */
function chain_ContenidoFrontendAllowEdit_Test2($lang, $idcat, $idart, $uid)
{
    return false;
}

/**
 * 3. chain function to check if the user has permission to edit articles in this category
 */
function chain_ContenidoFrontendAllowEdit_Test3($lang, $idcat, $idart, $uid)
{
    return true;
}


/**
 * Class to test Contenido chain Contenido.Frontend.AllowEdit.
 *
 * @author      Murat Purc <murat@purc.de>
 * @date        30.12.2009
 * @category    Testing
 * @package     Contenido_Frontend
 * @subpackage  Chains
 */
class ContenidoFrontendAllowEditTest extends PHPUnit_Framework_TestCase
{
    private $_chain = 'Contenido.Frontend.AllowEdit';
    private $_lang;
    private $_idcat = 10; // Hauptnavigation/Features-dieser-Website/Geschlossener-Bereich/Vertraulich/
    private $_idart = 17; // idart from above
    private $_uid   = null;


    protected function setUp()
    {
        $this->_lang = $GLOBALS['lang'];

        if (!$user = ContenidoTestHelper::getUserByUsername('admin')) {
            $this->fail('Couldn\'t get user_id of user "admin".');
            return;
        }
        $this->_uid = $user->user_id;
    }


    /**
     * Test Contenido.Frontend.AllowEdit chain
     */
    public function testNoChain()
    {
        // set n' execute chain
        CEC_Hook::setBreakCondition(false, true); // break at "false", default value "true"
        $allow = CEC_Hook::executeWhileBreakCondition($this->_chain, $this->_lang, $this->_idcat, $this->_idart, $this->_uid);

        $this->assertEquals(true, $allow);
    }


    /**
     * Test Contenido.Frontend.AllowEdit chain
     */
    public function testOneChain()
    {
        // get cec registry instance
        $cecReg = cApiCECRegistry::getInstance();

        // add chain functions
        $cecReg->addChainFunction($this->_chain, 'chain_ContenidoFrontendAllowEdit_Test');

        // set n' execute chain
        CEC_Hook::setBreakCondition(false, true); // break at "false", default value "true"
        $allow = CEC_Hook::executeWhileBreakCondition($this->_chain, $this->_lang, $this->_idcat, $this->_idart, $this->_uid);

        // remove chain functions
        $cecReg->removeChainFunction($this->_chain, 'chain_ContenidoFrontendAllowEdit_Test');

        $this->assertEquals(true, $allow);
    }


    /**
     * Test Contenido.Frontend.AllowEdit chain
     */
    public function testTwoChains()
    {
        // get cec registry instance
        $cecReg = cApiCECRegistry::getInstance();

        // add chain functions
        $cecReg->addChainFunction($this->_chain, 'chain_ContenidoFrontendAllowEdit_Test');
        $cecReg->addChainFunction($this->_chain, 'chain_ContenidoFrontendAllowEdit_Test2');
        $cecReg->addChainFunction($this->_chain, 'chain_ContenidoFrontendAllowEdit_Test3');

        // set n' execute chain
        CEC_Hook::setBreakCondition(false, true); // break at "false", default value "true"
        $allow = CEC_Hook::executeWhileBreakCondition($this->_chain, $this->_lang, $this->_idcat, $this->_idart, $this->_uid);

        // remove chain functions
        $cecReg->removeChainFunction($this->_chain, 'chain_ContenidoFrontendAllowEdit_Test');
        $cecReg->removeChainFunction($this->_chain, 'chain_ContenidoFrontendAllowEdit_Test2');
        $cecReg->removeChainFunction($this->_chain, 'chain_ContenidoFrontendAllowEdit_Test2');

        $this->assertEquals(false, $allow);
    }

}
