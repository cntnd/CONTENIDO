<?php
/**
 * Unittest for Contenido chain Contenido.Category.strCopyCategory
 *
 * @author      Murat Purc <murat@purc.de>
 * @date        30.12.2009
 * @category    Testing
 * @package     Contenido_Frontend
 * @subpackage  Chains
 */


/**
 * 1. chain function
 */
function chain_ContenidoCategoryStrCopyCategory_Test(array $data)
{
    if (isset($data['newcat']) && $data['newcat'] == 2) {
        ContenidoCategoryStrCopyCategoryTest::$invokeCounter++;
    }
}

/**
 * 2. chain function
 */
function chain_ContenidoCategoryStrCopyCategory_Test2(array $data)
{
    if (isset($data['newcat']) && $data['newcat'] == 2) {
        ContenidoCategoryStrCopyCategoryTest::$invokeCounter++;
    }
}


/**
 * Class to test Contenido chain Contenido.Category.strCopyCategory.
 *
 * @author      Murat Purc <murat@purc.de>
 * @date        30.12.2009
 * @category    Testing
 * @package     Contenido_Frontend
 * @subpackage  Chains
 */
class ContenidoCategoryStrCopyCategoryTest extends PHPUnit_Framework_TestCase
{
    private $_chain = 'Contenido.Category.strCopyCategory';
    private $_data  = array('oldcat' => 1, 'newcat' => 2, 'newcatlang2' => 1);

    public static $invokeCounter = 0;


    protected function setUp()
    {
        self::$invokeCounter = 0;
    }


    /**
     * Test Contenido.Category.strCopyCategory chain
     */
    public function testNoChain()
    {
        // execute chain
        CEC_Hook::execute($this->_chain, $this->_data);

        $this->assertEquals(array(0, $this->_data), array(self::$invokeCounter, $this->_data));
    }


    /**
     * Test Contenido.Category.strCopyCategory chain
     */
    public function testOneChain()
    {
        // get cec registry instance
        $cecReg = cApiCECRegistry::getInstance();

        // add chain functions
        $cecReg->addChainFunction($this->_chain, 'chain_ContenidoCategoryStrCopyCategory_Test');

        // execute chain
        CEC_Hook::execute($this->_chain, $this->_data);

        // remove chain functions
        $cecReg->removeChainFunction($this->_chain, 'chain_ContenidoCategoryStrCopyCategory_Test');

        $this->assertEquals(array(1, $this->_data), array(self::$invokeCounter, $this->_data));
    }


    /**
     * Test Contenido.Category.strCopyCategory chain
     */
    public function testTwoChains()
    {
        // get cec registry instance
        $cecReg = cApiCECRegistry::getInstance();

        // add chain functions
        $cecReg->addChainFunction($this->_chain, 'chain_ContenidoCategoryStrCopyCategory_Test');
        $cecReg->addChainFunction($this->_chain, 'chain_ContenidoCategoryStrCopyCategory_Test2');

        // execute chain
        CEC_Hook::execute($this->_chain, $this->_data);

        // remove chain functions
        $cecReg->removeChainFunction($this->_chain, 'chain_ContenidoCategoryStrCopyCategory_Test');
        $cecReg->removeChainFunction($this->_chain, 'chain_ContenidoCategoryStrCopyCategory_Test2');

        $this->assertEquals(array(2, $this->_data), array(self::$invokeCounter, $this->_data));
    }

}
