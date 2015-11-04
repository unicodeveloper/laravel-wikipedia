<?php

namespace Unicodeveloper\Larapedia;

use Unicodeveloper\Larapedia\Exception\EmptyArticleException;
use Unicodeveloper\Larapedia\Exception\EngineNotSupportedException;
use Unicodeveloper\Larapedia\Exception\LanguageNotSupportedException;

class WikiRand
{
    /**
     * Language Supported
     * @var array
     */
    private $language_supported = ['de', 'en', 'es', 'fr', 'it', 'nl', 'pl', 'ru', 'ceb', 'sv', 'vi', 'war'];

    /**
     * Engine Supported
     * @var array
     */
    private $engine_supported = [
        'pedia' => 'wikipedia.org',
        'quote' => 'wikiquote.org',
    ];

    /**
     * Placeholder for base api
     * @var string
     */
    private $base_api;

    /**
     * Placeholder for default language supported
     * @var string
     */
    private $language = 'en';

    /**
     * Placeholder for default engine supported
     * @var string
     */
    private $engine = 'pedia';

    /**
     * Placeholder for different article ids
     * @var array
     */
    private $article_ids = [];

    /**
     * WikiRand Constructor
     * @param string  $language
     * @param integer $number
     * @param string  $engine
     */
    public function __construct( $settings = [])
    {
        $number = 1;
        $this->setLanguage( $settings['language'] );
        $this->setEngine( $settings['engine'] );

        if ($number > 0) {
            $this->getNewRandomArticle( $number );
        }
    }

    /**
     *  Get list of articles or items
     * @param  array $data
     * @param  string $field
     * @param  integer $i
     * @param  string $default
     * @return mixed
     */
    private function get_list_or_item($data, $field, $i = null, $default = '')
    {
        $i = ($i === false || count($this->article_ids) > 1 ) ? $i : 0;

        if ($i === null || $i === false) {
            $result = [];
            foreach ($data as $page_id => $item) {
                $result[$page_id] = isset($item[$field]) ? $item[$field] : $default;
            }
            return $result;
        }

        return isset($data[$this->article_ids[$i]][$field]) ? $data[$this->article_ids[$i]][$field] : $default;
    }

    /**
     * Get the language the api should use
     * @return string
     */
    public function getApiLanguage()
    {
        return $this->language;
    }

    /**
     *  Get the list of supported languages
     * @return array
     */
    public function getSupportedLanguages()
    {
        return $this->language_supported;
    }

    /**
     * Get the names of the engines supported, pedia and quote
     * @return array
     */
    public function getSupportedEngines()
    {
        return array_keys($this->engine_supported);
    }

    /**
     * Set the language the api should use
     * @param string $language
     */
    public function setLanguage($language)
    {
        $language = $language ?: $this->language;
        if (!in_array($language, $this->getSupportedLanguages())) {
            throw new LanguageNotSupportedException(sprintf('Language [%s] is not supported', $language));
        }

        $this->language = $language;
        $this->changeBaseApi();
        return true;
    }

    /**
     * Set Engine to use, whethere wikipedia or wikiquote
     * @param string $engine
     */
    public function setEngine($engine) {
        if (!in_array($engine, $this->getSupportedEngines())) {
            throw new EngineNotSupportedException(sprintf('Engine [%s] is not supported', $engine));
        }

        $this->engine = $engine;
        $this->changeBaseApi();
    }

    /**
     * Change the base api to fetch quotes from
     * @return void
     */
    protected function changeBaseApi() {
        $domain = $this->language.'.'.$this->engine_supported[$this->engine];
        $this->base_api = sprintf('http://%s/w/api.php?format=json&rawcontinue=1&', $domain);
    }

    /**
     * Get a new random article
     * @param  integer $number
     * @return array
     */
    public function getNewRandomArticle($number = 1)
    {
        $wiki_api = $this->base_api . 'action=query&list=random&rnnamespace=0&rnlimit=' . $number;
        $json_wapi = json_decode(file_get_contents($wiki_api), true);
        $this->article_ids = [];
        foreach ($json_wapi['query']['random'] as $item) {
            $this->article_ids[] = $item['id'];
        }

        return $this->article_ids;
    }

    /**
     * Get the ids of the articles concatenated with |
     * @return string
     */
    public function getId()
    {
        if (empty($this->article_ids)) {
            throw new EmptyArticleException('Empty article_ids variable');
        }

        return implode('|', $this->article_ids);
    }

    /**
     * Get an array of articled ids
     * @return array
     */
    public function getIds()
    {
        return $this->article_ids;
    }

    /**
     * Get the title of the articles
     * @param  integer $i
     * @return mixed
     */
    public function getTitle($i = null)
    {
        $wiki_api = $this->base_api . 'action=query&prop=info&pageids=' . $this->getId();
        $json_wapi = json_decode(file_get_contents($wiki_api), true);

        return $this->get_list_or_item($json_wapi['query']['pages'], 'title', $i);
    }

    /**
     * Get the links of the articles
     * @param  integer $i
     * @return mixed
     */
    public function getLink($i = null)
    {
        $wiki_api = $this->base_api . 'action=query&prop=info&inprop=url&pageids=' . $this->getId();
        $json_wapi = json_decode(file_get_contents($wiki_api), true);

        return $this->get_list_or_item($json_wapi['query']['pages'], 'fullurl', $i);
    }

    /**
     * Get the first sentence of the article
     * @param  integer $number
     * @param  integer  $i
     * @return mixed
     */
    public function getFirstSentence($number = 1, $i = null)
    {
        $wiki_api = sprintf($this->base_api . 'action=query&prop=extracts&exsentences=%d&explaintext=&exsectionformat=plain&pageids=%s', intval($number), $this->getId());
        $json_wapi = json_decode(file_get_contents($wiki_api), true);

        return $this->get_list_or_item($json_wapi['query']['pages'], 'extract', $i);
    }

    /**
     * Get plain text from the Article
     * @param  integer $i
     * @return mixed
     */
    public function getPlainTextArticle($i = null)
    {
        $wiki_api = $this->base_api . 'action=query&prop=extracts&exlimit=1&explaintext=&exsectionformat=plain&pageids=' . $this->getId();
        $json_wapi = json_decode(file_get_contents($wiki_api), true);

        return $this->get_list_or_item($json_wapi['query']['pages'], 'extract', $i);
    }

    /**
     * Get the number of characters
     * @param  integer $number
     * @param  integer  $i
     * @return mixed
     */
    public function getNChar($number = 200, $i = null)
    {
        $wiki_api = sprintf($this->base_api . 'action=query&prop=extracts&exchars=%d&pageids=%s', intval($number), $this->getId());
        $json_wapi = json_decode(file_get_contents($wiki_api), true);

        return $this->get_list_or_item($json_wapi['query']['pages'], 'extract', $i);
    }

    /**
     * Get related categories for the articles
     * @param  integer $i
     * @return array
     */
    public function getCategoriesRelated($i = null)
    {
        $wiki_api = $this->base_api . 'action=query&prop=categories&pageids=' . $this->getId();
        $json_wapi = json_decode(file_get_contents($wiki_api), true);

        $result = $this->get_list_or_item($json_wapi['query']['pages'], 'categories', false, []);
        $categories = [];

        foreach ($result as $page_id => $items) {
            $categories[$page_id] = [];
            foreach ($items as $item) {
                $categories[$page_id][] = ltrim(strstr($item['title'], ':'), ':');
            }
        }

        return ($i === null) ? $categories : $categories[$this->article_ids[$i]];
    }

    /**
     * Get Images of the Articles
     * @param  integer  $i
     * @param  integer $image_min_size
     * @return array
     */
    public function getArticleImages($i = null, $image_min_size = 0)
    {
        $result = [];

        $i = count($this->article_ids) > 1 ? $i : 0;
        $article_ids = ($i === null) ? $this->article_ids : [$this->article_ids[$i]];

        foreach ($article_ids as $page_id) {
            $result[$page_id] = [];
            $wiki_api = $this->base_api . 'action=query&generator=images&prop=imageinfo&iiprop=url|size&pageids=' . $page_id;
            $json_wapi = json_decode(file_get_contents($wiki_api), true);

            if (!$json_wapi) {
                return [];
            }

            foreach ($json_wapi['query']['pages'] as $item) {
                $imageinfo = $item['imageinfo'][0];
                if ($image_min_size > 0 && $imageinfo['size'] < $image_min_size) {
                    continue;
                }

                $result[$page_id][] = $imageinfo['url'];
            }
        }

        return ($i === null) ? $result : $result[$this->article_ids[$i]];
    }

    /**
     * Get other language links
     * @param  integer $i
     * @return mixed
     */
    public function getOtherLangLinks($i = null)
    {
        $wiki_api = $this->base_api . 'action=query&prop=langlinks&llprop=url&pageids=' . $this->getId();
        $json_wapi = json_decode(file_get_contents($wiki_api), true);

        return $this->get_list_or_item($json_wapi['query']['pages'], 'langlinks', $i, []);
    }

    /**
     * Get Bulk Data
     * @param  integer $limit
     * @param  integer $sentences
     * @param  integer $chars
     * @param  boolean $with_images
     * @param  integer $image_min_size
     * @return array
     */
    public function getBulkData($limit = 1, $sentences = 5, $chars = 200, $with_images = false, $image_min_size = 102400)
    {
        $wiki_api = $this->base_api . 'action=query&generator=random&grnnamespace=0&grnlimit=' . $limit . '&prop=info|extracts&inprop=url&explaintext=&exsectionformat=plain';

        if ($sentences > 0) {
            $wiki_api .= '&exsentences=' . $sentences;
        } elseif ($chars) {
            $wiki_api .= '&exchars=' . $chars;
        }

        $json_wapi = json_decode(file_get_contents($wiki_api), true);
        $result = [];

        if (!$json_wapi) {
            return $result;
        }

        foreach ($json_wapi['query']['pages'] as $page_id => $item) {
            $result[$page_id] = [
                'page_id' => $page_id,
                'title'   => $item['title'],
                'length'  => $item['length'],
                'url'     => $item['fullurl'],
                'text'    => isset($item['extract']) ? $item['extract'] : '',
            ];
        }

        $this->article_ids = array_keys($result);
        if ($with_images) {
            foreach ($this->article_ids as $page_id) {
                $wiki_api = $this->base_api . 'action=query&generator=images&prop=imageinfo&iiprop=url|size&pageids=' . $page_id;
                $json_wapi = json_decode(file_get_contents($wiki_api), true);
                $images = array();
                if ($json_wapi) {
                    foreach ($json_wapi['query']['pages'] as $item) {
                        $imageinfo = $item['imageinfo'][0];
                        if ($image_min_size > 0 && $imageinfo['size'] < $image_min_size) {
                            continue;
                        }
                        $images[] = $imageinfo['url'];
                    }
                }
                $result[$page_id]['images'] = $images;
            }
        }

        return array_values($result);
    }
}