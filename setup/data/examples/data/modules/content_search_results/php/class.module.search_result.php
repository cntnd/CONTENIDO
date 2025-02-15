 <?php

/**
 *
 * @package    Module
 * @subpackage ContentSearchResult
 * @author     marcus.gnass@4fb.de
 * @copyright  four for business AG <www.4fb.de>
 * @license    https://www.contenido.org/license/LIZENZ.txt
 * @link       https://www.4fb.de
 * @link       https://www.contenido.org
 */

/**
 *
 * @property array $_cfg
 * @property array $_artSpecs
 * @property cDb $_db
 * @property int $_client
 * @property int $_lang
 * @property int $_idcat
 * @property int $_idart
 * @property cSession $_sess
 * @property string $_combine
 * @property string $_templateName
 *
 * @author marcus.gnass
 */
class SearchResultModule {

    /**
     *
     * @var int
     */
    protected $_countValues;

    /**
     *
     * @var int
     */
    protected $_searchResultCount = 0;

    /**
     *
     * @var array
     */
    protected $_label;

    /**
     *
     * @var int
     */
    protected $_itemsPerPage;

    /**
     *
     * @var int
     */
    protected $_maxTeaserTextLen;

    /**
     *
     * @var string
     */
    protected $_searchTerm;

    /**
     *
     * @var string
     */
    protected $_dispSearchTerm = NULL;

    /**
     *
     * @var string
     */
    protected $_prepSearchTerm = NULL;

    /**
     *
     * @var array
     */
    protected $_searchResultData = NULL;

    /**
     *
     * @var cSearchResult
     */
    protected $_searchResults = NULL;

    /**
     *
     * @var int
     */
    protected $_numberOfPages = NULL;

    /**
     * @var int
     */
    protected $_page = 0;

    /**
     * @var string
     */
    protected $_msgResult;

    /**
     * @var string
     */
    protected $_msgRange;

    /**
     *
     * @param array $options
     */
    public function __construct(array $options = NULL) {
        global $sArtSpecs;

        // generic way to set options
        if (NULL !== $options) {
            foreach ($options as $name => $value) {
                $name = '_' . $name;
                $this->$name = $value;
            }
        }

        // get global variables from registry (the nice way)
        $this->_cfg = cRegistry::getConfig();
        $this->_db = cRegistry::getDb();
        $this->_client = cRegistry::getClientId();
        $this->_lang = cRegistry::getLanguageId();
        $this->_idcat = cRegistry::getCategoryId();
        $this->_idart = cRegistry::getArticleId();
        $this->_sess = cRegistry::getSession();
        $this->_combine = '';
        $this->_msgResult = '';
        $this->_msgRange = '';

        // get global variables (the ugly way)
        $this->_artSpecs = $sArtSpecs;

        // perform first preparation of searchterm
        $searchTerm = $this->_searchTerm;
        if (true === cRegistry::getConfigValue('simulate_magic_quotes')) {
            $searchTerm = stripslashes($searchTerm);
        }

        // assume search term is always url encoded
        // default enctype for a form is application/x-www-form-urlencoded
        $searchTerm = urldecode($searchTerm);

        $searchTerm = str_replace(' + ', ' AND ', $searchTerm);
        $searchTerm = str_replace(' - ', ' NOT ', $searchTerm);

        // escape all entities for search because content types save its values
        // using escaping of special chars
        $searchTerm = conHtmlentities($searchTerm);

        // that's the search term suitable for display
        $this->_dispSearchTerm = $searchTerm;

        // now parse search term and set search options
        if (cString::getStringLength(trim($searchTerm)) > 0) {
            $searchTerm = conHtmlEntityDecode($searchTerm);
            if (false === cString::findFirstOccurrenceCI($searchTerm, ' or ')) {
                $this->_combine = 'and';
            } else {
                $this->_combine = 'or';
            }

            // force converting string to UTF-8
            $searchTerm = htmlentities($searchTerm, ENT_COMPAT, 'UTF-8');
            // remove superfluous white space and convert search term to lowercase
            $searchTerm = (trim(cString::toLowerCase($searchTerm)));
            $searchTerm = html_entity_decode($searchTerm, ENT_COMPAT, 'UTF-8');

            $searchTerm = str_replace(' and ', ' ', $searchTerm);
            $searchTerm = str_replace(' or ', ' ', $searchTerm);
        }

        // that's the search term suitable for the search itself
        $this->_prepSearchTerm = $searchTerm;

        // perform search
        $this->_performSearch();
    }

    /**
     */
    public function render() {
        $tpl = cSmartyFrontend::getInstance(true);

        $tpl->assign('label', $this->_label);
        $tpl->assign('searchTerm', $this->_dispSearchTerm);
        $tpl->assign('currentPage', $this->_page);

        $tpl->assign('results', $this->_getResults());
        $tpl->assign('msgResult', $this->_msgResult);
        $tpl->assign('msgRange', $this->_msgRange);
        $tpl->assign('prev', $this->_getPreviousLink());
        $tpl->assign('next', $this->_getNextLink());
        $tpl->assign('pages', $this->_getPageLinks());
        $tpl->assign('method', 'post');

        // determine action & method for search form
        // depends upon if plugin mod_rewrite is enabled
        if (class_exists('ModRewrite') && ModRewrite::isEnabled()) {
            $tpl->assign('action', cUri::getInstance()->build([
                'idart' => cRegistry::getArticleId(),
                'lang' => cRegistry::getLanguageId()
            ]));
        } else {
            $tpl->assign('action', 'front_content.php');
            $tpl->assign('idart', cRegistry::getArticleId());
            $tpl->assign('idlang', cRegistry::getLanguageId());
        }

        $tpl->display($this->_templateName);
    }

    /**
     */
    protected function _performSearch() {
        // build search object
        // only certain content types will be searched
        $search = new cSearch([
            // use db function regexp
            'db' => 'regexp',
            // combine searchterms with and
            'combine' => $this->_combine,
            // => searchrange specified in 'cat_tree', 'categories' and
            // 'articles' is excluded, otherwise included (exclusive)
            'exclude' => false,
            // searchrange
            'cat_tree' => $this->_getSearchableIdcats(),
            // array of article specifications
            // => search only articles with these artspecs
            'artspecs' => $this->_getArticleSpecs(),
            // => do not search articles or articles in categories which are
            // offline or protected
            'protected' => true
        ]);

        $search->setCmsOptions([
            'head',
            'html',
            'htmlhead',
            'htmltext',
            'text'
        ]);

        if (cString::getStringLength($this->_prepSearchTerm) > 1) {
            $searchResultArray = $search->searchIndex($this->_prepSearchTerm, '');

            if (false !== $searchResultArray) {

                $this->_searchResultCount = count($searchResultArray);

                // get search results
                $this->_searchResults = new cSearchResult($searchResultArray, $this->_itemsPerPage);

                // get number of pages
                $this->_numberOfPages = $this->_searchResults->getNumberOfPages();

                // html-tags to emphasize the located searchterms
                $this->_searchResults->setReplacement('<strong>', '</strong>');
            }

            // create message to display
            if (0 === $this->_searchResultCount) {
                $this->_msgResult = sprintf($this->_label['msgNoResultsFound'], $this->_dispSearchTerm);
            } else {
                $this->_msgResult = sprintf($this->_label['msgResultsFound'], $this->_dispSearchTerm, $this->_searchResultCount);
            }
        }
    }

    /**
     *
     * @return string
     */
    protected function _getMsgResult() {
        return $this->_msgResult;
    }

    /**
     *
     * @param int $count
     * @param int $countIdarts
     */
    protected function _setMsgResult($count, $countIdarts) {
        $this->_countValues = $count;

        // $this->_numberOfPages = 1;
        if (0 === $this->_searchResultCount) {
            $this->_msgResult = sprintf($this->_label['msgNoResultsFound'], $this->_dispSearchTerm);
        } else {
            $this->_msgResult = sprintf($this->_label['msgResultsFound'], $this->_dispSearchTerm, $this->_searchResultCount);
        }
    }

    /**
     * Returns IDCATs of setting searchable/idcats as array.
     * Default value is 1.
     *
     * @return array
     * @throws cDbException|cException
     */
    protected function _getSearchableIdcats() {
        $searchableIdcats = getEffectiveSetting('searchable', 'idcats', 1);
        return explode(',', $searchableIdcats);
    }

    /**
     * Get all article specs of current client in current language.
     *
     * TODO use cApiArticleSpecificationCollection instead
     *
     * @return array
     * @throws cDbException|cInvalidArgumentException
     */
    protected function _getArticleSpecs() {
        $sql = "-- getArticleSpecs()
            SELECT
                `idartspec`, `artspec`
            FROM
                `%s`
            WHERE
                `client` = %d AND `lang` = %d AND `online` = 1
            ;";

        $this->_db->query($sql, $this->_cfg['tab']['art_spec'], $this->_client, $this->_lang);

        $aArtSpecs = [];
        while ($this->_db->nextRecord()) {
            $aArtSpecs[] = $this->_db->f('idartspec');
        }
        $aArtSpecs[] = 0;

        return $aArtSpecs;
    }

    /**
     *
     * @return array
     * @throws cDbException|cException|cInvalidArgumentException
     */
    protected function _getResults() {
        if (NULL === $this->_searchResults) {
            return [];
        }

        // get current result page
        $searchResultPage = $this->_searchResults->getSearchResultPage($this->_page);

        // skip if current page has no results
        if (0 == count($searchResultPage) > 0) {
            return [];
        }

        // build single search result on result page
        $entries = [];
        $i = 0;
        foreach (array_keys($searchResultPage) as $idart) {
            $i++;

            // get absolute number of current search result
            $number = $this->_itemsPerPage * ($this->_page - 1) + $i;

            // get headlines
            $headlines = $this->_searchResults->getSearchContent($idart, 'HTMLHEAD', 1);
            $headline = cString::trimAfterWord($headlines[0], $this->_maxTeaserTextLen);

            // get subheadlines
            $subheadlines = $this->_searchResults->getSearchContent($idart, 'HTMLHEAD', 2);
            $subheadline = cString::trimAfterWord($subheadlines[0], $this->_maxTeaserTextLen);

            // get paragraphs
            $paragraphs = $this->_searchResults->getSearchContent($idart, 'HTML', 1);
            $paragraph = cString::trimAfterWord($paragraphs[0], $this->_maxTeaserTextLen);

            // get similarity
            $similarity = $this->_searchResults->getSimilarity($idart);
            $similarity = sprintf("%.0f", $similarity);

            // build link to that result page
            $href = cUri::getInstance()->build([
                'lang' => cRegistry::getLanguageId(),
                'idcat' => $this->_searchResults->getArtCat($idart),
                'idart' => $idart
            ]);

            // assemble entry
            $entries[] = [
                'number' => $number,
                'headline' => $headline,
                'subheadline' => $subheadline,
                'paragraph' => $paragraph,
                'similarity' => $similarity,
                'href' => $href
            ];
        }

        $lower = ($this->_page - 1) * $this->_itemsPerPage + 1;
        $upper = $lower + count($entries) - 1;
        $total = $this->_searchResults->getNumberOfResults();

        $this->_msgRange = sprintf($this->_label['msgRange'], $lower, $upper, $total);

        return $entries;
    }

    /**
     *
     * @return string
     */
    protected function _getPreviousLink() {
        // skip if there are no previous pages
        if (1 >= $this->_page) {
            return '';
        }

        // build link to previous result page
        return $this->_getPageLink($this->_dispSearchTerm, $this->_page - 1);
    }

    /**
     *
     * @return string
     */
    protected function _getNextLink() {
        // skip if there are no next pages
        if ($this->_page >= $this->_numberOfPages) {
            return '';
        }

        // build link to next result page
        return $this->_getPageLink($this->_dispSearchTerm, $this->_page + 1);
    }

    /**
     * Build links to other result pages.
     *
     * @return array
     */
    protected function _getPageLinks() {
        $pageLinks = [];
        for ($i = 1; $i <= $this->_numberOfPages; $i++) {
            $pageLinks[$i] = $this->_getPageLink($this->_dispSearchTerm, $i);
        }

        return $pageLinks;
    }

    /**
     * This method builds URLs for each result link and the pagination links.
     *
     *
     * @param string $searchTerm
     * @param int $page
     * @return mixed
     * @throws cDbException|cException
     */
    protected function _getPageLink($searchTerm = NULL, $page = NULL) {
        // define standard params
        $params = [
            'lang' => $this->_lang,
            'idcat' => $this->_idcat,
            'idart' => $this->_idart
        ];
        // add optional params if given
        if (NULL !== $searchTerm) {
            $params['search_term'] = conHtmlEntityDecode($searchTerm);
        }
        if (NULL !== $page) {
            $params['page'] = $page . $this->_artSpecs;
        }
        // store unaltered params for later use
        $defaultParams = $params;

        // define special params when 'front_content' or 'MR' url builders are
        // *NOT* used in this case the standard params are wrapped as 'search'
        // and lang, idcat & level are added because they are needed to build the
        // category path
        $url_builder = [
            'front_content',
            'MR'
        ];
        if (false === in_array($this->_cfg['url_builder']['name'], $url_builder)) {
            $params = [
                'search' => $params,
                'lang' => $this->_lang,
                'idcat' => $this->_idcat,
                'level' => 1
            ];
        }

        try {
            $url = cUri::getInstance()->build($params);
        } catch (cInvalidArgumentException $e) {
            $url = $this->_sess->url(implode('?', [
                'front_content.php',
                implode('&', $defaultParams)
            ]));
        }

        return $url;
    }

}

