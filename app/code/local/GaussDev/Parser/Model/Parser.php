<?php

class GaussDev_Parser_Model_Parser extends Mage_Core_Model_Abstract
{
    const VALID_FORMATS = 'jpg|jpeg|png|gif';

    public $title = '';
    private $fullTitle = '';

    private $names = array();
    private $prices = array();
    private $uniquePrices = array();
    private $uniqueNames = array();
    private $images = array();
    private $url;

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @param $url
     *
     * @return $this
     * @throws Exception
     */
    public function parse($url)
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL provided.');
        }
        $this->setData('brand', '');
        $this->setData('description', '');
        $this->setData('images', array());
        $name = '';
        $price = '';
        $this->url = $url;
        $html = Mage::helper('gaussdev_parser')->getUrlContents($url);
        $hash = sha1($html);
        $product = Mage::getModel('catalog/product')->loadByAttribute('product_origin_page_hash', $hash)
            ?: Mage::getModel('catalog/product')->loadByAttribute('product_origin_url', $url);
        if ($product !== false) {
            Mage::register('parsed_product', $product);
            throw new Exception('Product already exists.');
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html);
        libxml_clear_errors();
        $dom->preserveWhiteSpace = false;
        if (!$dom) {
            throw new Exception('Invalid HTML.');
        }
        $this->dom = $dom;

        $hostname = parse_url($url, PHP_URL_HOST);
        $hosts = Mage::getModel('gaussdev_parser/host')->getCollection()->addFieldToFilter('hostname', $hostname);
        foreach ($hosts as $host) {
            /** @var GaussDev_Parser_Model_Host $host */
            $possiblePrice = $this->getXPathPrice($host->getData('price_xpath'));
            if (preg_match('/^\d.*\d$/', $possiblePrice)) {
                $price = $possiblePrice;
                break;
            }
        }
        $this->parseMetaTags($dom);
        $this->dom_walk($dom->documentElement);
        uksort($this->names, 'strnatcmp');
        $this->names = array_reverse($this->names);
        usort($this->prices, function ($a, $b) {
            return $b['price'] * 100 - $a['price'] * 100;
        });
        $this->setData('name', strip_tags($name ?: $this->getData('name')));
        $this->setData('price', strip_tags($price ?: $this->getData('price')));
        $this->setData('other_names', array_values($this->names));
        $this->setData('other_prices', array_values($this->prices));

        $this->getLinksByImgTag($dom);

        $this->downloadImages();

        $this->filterDownloadedImages();
        if ($this->images) {
            array_walk($this->images, function (&$image) {
                $image = str_replace(Mage::getBaseDir() . DS, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
                    $image);
            });
            $this->setData('images', array_values($this->images));
        }

        $this->setData('url', $this->getData('url') ?: $this->url);
        $attributeCode = 'brand';
        $brands = Mage::getResourceModel('catalog/product_collection')
                      ->addAttributeToFilter($attributeCode, array('notnull' => true))
                      ->addAttributeToFilter($attributeCode, array('neq' => ''))
                      ->addAttributeToSelect($attributeCode)
                      ->distinct(true)
                      ->getColumnValues($attributeCode);
        foreach ($brands as $brand) {
            if ($this->fullTitle) {
                $nameSearch = $this->fullTitle;
                if (strpos($nameSearch, $brand) !== false) {
                    $this->setData('brand', $brand);
                    break;
                }
            }
        }
        if (!$this->getData('brand')) {
            $brand = '';
            $pieces = parse_url($url);
            $domain = isset($pieces['host']) ? $pieces['host'] : '';
            if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63})\.[a-z\.]{2,6}$/i', $domain, $regs)) {
                $brand = isset($regs['domain']) ? $regs['domain'] : '';
            }
            $this->setData('brand', ucwords($brand));
        }
        if ($this->getData('price') >= 100) {
            $this->setData('price', '');
        }

        Mage::getSingleton('core/session')->setData('price_xpath_' . md5($url),
            base64_encode(serialize($this->getData('other_prices'))));

        return $this;
    }

    protected function filterName(DOMNode $node)
    {
        $value = explode("\n", trim($node->textContent));
        $val = preg_replace('/[^\p{L}\p{N}]+$/u', '', trim(current(preg_grep('/^[\p{L}\p{N}]/u', $value))));

        return $val;
    }

    protected function getXPathPrice($path)
    {
        try {
            $xpath = new DOMXpath($this->dom);
            $DOMNodeList = $xpath->query($path);
            if ($DOMNodeList->length) {
                return $this->filterPrice($DOMNodeList->item(0));
            }
        } catch (Exception $e) {
        }

        return null;
    }

    protected function filterPrice(DOMNode $node)
    {
        $value = explode("\n", trim($node->textContent));
        $valPrice = current(preg_grep('/\d+[.,]\d+/', $value));
        preg_match('/(?!0+[\.,]*)(?<price>\d+(?:[\.,]\d{1,2})?)/', $valPrice, $price);
        if (isset($price['price'])) {
            return $price['price'];
        }

        return null;
    }

    protected function dom_walk($e)
    {
        if (!$e) {
            throw new Exception('Invalid HTML.');
        }
        foreach ($e->childNodes as $node) {
            if ($node instanceof DOMElement) {
                $this->extractImagesFromNode($node);
                $this->extractPricesFromNode($node);
                $this->extractNamesFromNode($node);
            }

            if ($node->childNodes !== null) {
                $this->dom_walk($node);
            }
        }
    }

    protected function extractImagesFromNode(DOMElement $node)
    {
        if ($node->attributes) {
            foreach ($node->attributes as $a) {
                if (stripos($a->nodeName, 'href') !== false
                    && preg_match('/(' . $this::VALID_FORMATS . ')$/i', $a->nodeValue)
                    && filter_var($a->nodeValue, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)
                ) {
                    $this->images[] = $a->nodeValue;
                }
            }

        }
    }

    protected function extractPricesFromNode(DOMElement $node)
    {
        $valPrice = $this->filterPrice($node);
        if ($valPrice
            && $node->attributes
            && (stripos($node->getAttribute('class'), 'price') !== false
                || stripos($node->getAttribute('itemprop'), 'price') !== false
                || stripos($node->getAttribute('id'), 'price') !== false
                || stripos($node->getAttribute('class'), 'amount') !== false
                || stripos($node->getAttribute('itemprop'), 'amount') !== false
                || stripos($node->getAttribute('id'), 'amount') !== false
                || stripos($node->getAttribute('class'), 'value') !== false
                || stripos($node->getAttribute('itemprop'), 'value') !== false
                || stripos($node->getAttribute('id'), 'value') !== false)
            && filter_var($valPrice, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND)
            && (bool)$valPrice
            && !in_array($valPrice, $this->uniquePrices)
            && $valPrice > 0
            && $valPrice < 100
        ) {
            $this->uniquePrices[] = $valPrice;
            $this->prices[] = array('price' => $valPrice, 'xpath' => $node->getNodePath());
        }

        return null;
    }

    protected function extractNamesFromNode(DOMElement $node)
    {

        $val = $this->filterName($node);
        $strlen = strlen($val);
        if ($val && $this->isValidNamePath($node) && $strlen > 2 && $strlen < 200) {
            similar_text(metaphone($this->translit($val)), metaphone($this->translit($this->getTitle())), $s);
            if ($s > 50) {
                if (!in_array($val, $this->uniqueNames)
                ) {
                    $this->uniqueNames[] = $val;
                    $data = array('name' => $val, 'xpath' => $node->getNodePath());
                    $s = (preg_replace('/[\D]/', '', (int)($s * 10000)));
                    $i = 10;
                    while (array_key_exists($s, $this->names) && $i < 100) {
                        $s = substr($s, 0, -2) . $i;
                        $i++;
                    }
                    $this->names[$s] = $data;
                }
            }
        }
    }

    protected function isValidNamePath(DOMElement $node)
    {
        return (bool)preg_match('/\/body\//i', $node->getNodePath());
    }

    protected function translit($val)
    {
        $val = strtr($val, array(
            "0" => " a ",
            "1" => " b ",
            "2" => " c ",
            "3" => " d ",
            "4" => " e ",
            "5" => " f ",
            "6" => " g ",
            "7" => " h ",
            "8" => " i ",
            "9" => " j "
        ));

        $val = preg_replace('/[^\p{L}\p{N}]++/u', ' ', $val);

        return iconv('UTF-8', 'ASCII//TRANSLIT', $val);
    }

    protected function getTitle()
    {
        if ($this->title) {
            return $this->title;
        } else {
            $list = $this->dom->getElementsByTagName("title");
            if ($list->length > 0) {
                $this->fullTitle = $list->item(0)->textContent;
                $titles = preg_split('#[\s\|\-\\\/\–]{3,}#', $this->fullTitle);
                $titl = preg_grep('/^[\p{L}\p{N}\p{P}\r ]*\b/u', $titles);
            }
            $t = strip_tags(trim(current($titl)));
            $this->title = $t;

            return $t;
        }
    }

    protected function getLinksByImgTag(DOMDocument $dom)
    {
        /* @var DOMElement $nodeSlike */
        $sveSlike = $dom->getElementsByTagName('img');
        foreach ($sveSlike as $nodeSlike) {
            foreach ($nodeSlike->attributes as $a) {
                $data = $a->nodeValue;
                $data = preg_replace('#^//#', 'http://', $data);
                $data = preg_match('#^/\w+#', $data) ? 'http://' . parse_url($this->url, PHP_URL_HOST) . $data : $data;
                $this->images[] = preg_replace('#^//#', 'http://', $data);
            }
        }
    }

    protected function parseMetaTags(DOMDocument $dom)
    {
        /* @var DOMElement $meta */
        $tags = $dom->getElementsByTagName('meta');
        foreach ($tags as $meta) {
            switch ($meta->getAttribute('property')) {
                case 'og:image':
                    $this->images[] = $meta->getAttribute('content');
                    break;
                case 'og:description':
                    $this->setData('description', strip_tags($meta->getAttribute('content')));
                    break;
                case 'og:price:amount':
                    $this->setData('price', strip_tags($meta->getAttribute('content')));
                    break;
            }
        }
    }

    protected function downloadImages()
    {
        $downloaded = array();
        $this->images = array_unique(array_filter($this->images));
        foreach ($this->images as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                $pass[] = $url;
                $downloaded[] = Mage::helper('gaussdev_parser')->downloadImage($this->url, $url);
            }
        }
        $this->images = array_filter($downloaded);
    }

    protected function filterDownloadedImages()
    {
        $this->images = array_unique($this->images);
        $smallImages = array();
        foreach ($this->images as $key => $path) {
            $imageSizes = getimagesize($path);
            $resolution = ($imageSizes[0] * $imageSizes[1]) / 1000; // kilopixels
            if ($resolution <= 130) {
                $smallImages[$key] = true;
            }
        }
        $this->images = array_diff_key($this->images, $smallImages);
    }

    protected function _construct()
    {
        $this->_init('gaussdev_parser/parser');
    }

}