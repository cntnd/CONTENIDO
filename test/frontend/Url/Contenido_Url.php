<?php
/**
 * Unittest for Contenido_Url class.
 *
 * @author      Murat Purc <murat@purc.de>
 * @date        26.12.2008
 * @category    Testing
 * @package     Contenido_Frontend
 * @subpackage  Url
 */


/**
 * Class to test Contenido_Url
 *
 * @author      Murat Purc <murat@purc.de>
 * @date        26.12.2008
 * @category    Testing
 * @package     Contenido_Frontend
 * @subpackage  Url
 */
class Contenido_UrlTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test url creation to error page
     */
    public function testErrorPageUrlCreation()
    {
        // error page
        $aParams = array (
            'client' => $GLOBALS['client'],
            'idcat'  => $GLOBALS['errsite_idcat'][$GLOBALS['client']],
            'idart'  => $GLOBALS['errsite_idart'][$GLOBALS['client']],
            'lang'   => $GLOBALS['lang'],
            'error'  => '1'
        );

        $url = Contenido_Url::getInstance()->buildRedirect($aParams);

        $isToBeUrl = $GLOBALS['cfgClient'][$GLOBALS['client']]['path']['htmlpath']
                   . 'front_content.php?idcat=31&idart=36&client=1&lang=1&error=1';

        $this->assertEquals($isToBeUrl, $url);
    }


    /**
     * Test url creation to internal pages (article redirects)
     */
    public function testInternalRedirectUrlCreation()
    {
        // internal redirect
        $redirectUrl = 'front_content.php?idart=12&param=value';
        $redirectUrl = $this->_createArticleRedirectUrl($redirectUrl);

        $isToBeUrl = $GLOBALS['cfgClient'][$GLOBALS['client']]['path']['htmlpath']
                  . 'front_content.php?idart=12&param=value&lang=' . $GLOBALS['lang'];

        $this->assertEquals($isToBeUrl, $redirectUrl);
    }


    /**
     * Test url creation to internal homepage without existing parameter idart/idcat.
     *
     * Tests following article redirect settings:
     * - front_content.php
     * - /
     * - /cms/
     * - /cms/front_content.php
     */
    public function testInternalRedirectUrlToHomepageCreation()
    {
        // get idcat to homepage
        $idcatHome = getEffectiveSetting('navigation', 'idcat-home', 1);

        // result should be following url
        $isToBeUrl = $GLOBALS['cfgClient'][$GLOBALS['client']]['path']['htmlpath']
                  . 'front_content.php?idcat=' . $idcatHome . '&lang=' . $GLOBALS['lang'];

        // internal redirect with 'front_content.php'
        $redirectUrl = $this->_createArticleRedirectUrl('front_content.php');
        $this->assertEquals($isToBeUrl, $redirectUrl);

        // internal redirect with '/'
        $redirectUrl = $this->_createArticleRedirectUrl('/');
        $this->assertEquals($isToBeUrl, $redirectUrl);

        // internal redirect with '/cms/' (note: 'cms' is hard coded but the default client folder)
        $redirectUrl = $this->_createArticleRedirectUrl('/cms/');
        $this->assertEquals($isToBeUrl, $redirectUrl);

        // internal redirect with '/cms/front_content.php'
        $redirectUrl = $this->_createArticleRedirectUrl('/cms/front_content.php');
        $this->assertEquals($isToBeUrl, $redirectUrl);
    }


    /**
     * Test url creation to internal pages (article redirects)
     */
    public function testInternalRedirectFullUrlCreation()
    {
        // internal redirect with full url
        $redirectUrl = $GLOBALS['cfgClient'][$GLOBALS['client']]['path']['htmlpath']
                     . 'front_content.php?idart=12&param=value';
        $redirectUrl = $this->_createArticleRedirectUrl($redirectUrl);

        $isToBeUrl = $GLOBALS['cfgClient'][$GLOBALS['client']]['path']['htmlpath']
                   . 'front_content.php?idart=12&param=value&lang=' . $GLOBALS['lang'];

        $this->assertEquals($isToBeUrl, $redirectUrl);
    }


    /**
     * Test url creation to external pages (article redirects)
     */
    public function testExternalRedirectUrlCreation()
    {
        // external redirect
        $redirectUrl = 'http://www.contenido.de/?id=12321';
        $redirectUrl = $this->_createArticleRedirectUrl($redirectUrl);

        $isToBeUrl = 'http://www.contenido.de/?id=12321';

        $this->assertEquals($isToBeUrl, $redirectUrl);
    }


    /**
     * Test url creation to unidentifiable internal pages (article redirects)
     */
    public function testUnidentifiableInternalRedirectUrlCreation()
    {
        // unidentifiable internal
        $redirectUrl = '/unknown/path/to/some/page.html';
        $redirectUrl = $this->_createArticleRedirectUrl($redirectUrl);

        $isToBeUrl = '/unknown/path/to/some/page.html';

        $this->assertEquals($isToBeUrl, $redirectUrl);
    }


    private function _createArticleRedirectUrl($redirectUrl)
    {
        $oUrl = Contenido_Url::getInstance();
        if ($oUrl->isIdentifiableFrontContentUrl($redirectUrl)) {
            // perform urlbuilding only for identified internal urls
            $aUrl = $oUrl->parse($redirectUrl);
            if (!isset($aUrl['params']['lang'])) {
                $aUrl['params']['lang'] = $GLOBALS['lang'];
            }
            $redirectUrl = $oUrl->buildRedirect($aUrl['params']);
        }
        return $redirectUrl;
    }

}
