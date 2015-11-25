<?php
namespace Unicodeveloper\Larapedia\Test;

use Unicodeveloper\Larapedia\WikiRand;
use Unicodeveloper\Larapedia\Exception\EngineNotSupportedException;
use Unicodeveloper\Larapedia\Exception\LanguageNotSupportedException;

class LarapediaTest extends \PHPUnit_Framework_TestCase
{
    private $wikiRand;

    public function setUp()
    {
        $this->wikiRand = new WikiRand(['language' => 'en', 'engine' => 'pedia']);
    }

    public function tearDown()
    {
        $this->wikiRand = null;
    }

    public function testGetApiLanguage()
    {
        $this->assertEquals('en', $this->wikiRand->getApiLanguage());
    }

    public function testGetSupportedLanguages()
    {
        $supportedLanguages = ['de', 'en', 'es', 'fr', 'it', 'nl', 'pl', 'ru', 'ceb', 'sv', 'vi', 'war'];

        $this->assertEquals($supportedLanguages, $this->wikiRand->getSupportedLanguages());
    }

    public function testGetSupportedEngines()
    {
        $this->assertEquals(['pedia', 'quote'], $this->wikiRand->getSupportedEngines());
    }

    public function testGetNewRandomArticle()
    {
        $result = $this->wikiRand->getNewRandomArticle();
        $this->assertTrue(is_array($result) && is_int($result[0]));
    }

    public function testGetId()
    {
        $this->assertTrue(is_int((int) $this->wikiRand->getId()));
    }

    public function testGetIds()
    {
        $result = $this->wikiRand->getIds();
        $this->assertTrue(is_array($result) && is_int($result[0]));
    }

    public function testGetTitle()
    {
        $this->assertTrue(is_string($this->wikiRand->getTitle()));
    }

    public function testGetLink()
    {
        $result = $this->wikiRand->getLink();
        $this->assertEquals('https://en.wikipedia.org/wiki/', substr($result, 0, 30));
    }

    public function testGetFirstSentence()
    {
        $this->assertTrue(is_string($this->wikiRand->getFirstSentence()));
    }

    public function testGetPlainTextArticle()
    {
        $this->assertTrue(is_string($this->wikiRand->getPlainTextArticle()));
    }

    public function testGetNChar()
    {
        $this->assertTrue(is_string($this->wikiRand->getNChar()));
    }

    public function testGetCategoriesRelated()
    {
        $result = $this->wikiRand->getCategoriesRelated();
        $this->assertTrue(is_array($result));
    }

    public function testGetArticleImages()
    {
        $result = $this->wikiRand->getArticleImages();
        $this->assertTrue(is_array($result));
    }

    public function testGetOtherLangLinks()
    {
        $result = $this->wikiRand->getOtherLangLinks();

        if (empty($result)) {
            $this->assertTrue(is_array($result));
        } else {
            $this->assertTrue(is_array($result) && is_array($result[0]));
            $this->assertArrayHasKey('lang', $result[0]);
            $this->assertArrayHasKey('url', $result[0]);
            $this->assertArrayHasKey('*', $result[0]);
        }
    }

    public function testGetBulkData()
    {
        $result = $this->wikiRand->getBulkData();

        if (empty($result)) {
            $this->assertTrue(is_array($result));
        } else {
            $this->assertTrue(is_array($result) && is_array($result[0]));
            $this->assertArrayHasKey('page_id', $result[0]);
            $this->assertArrayHasKey('title', $result[0]);
            $this->assertArrayHasKey('length', $result[0]);
            $this->assertArrayHasKey('url', $result[0]);
            $this->assertArrayHasKey('text', $result[0]);
        }
    }

//	--------------------------------------Make provision for these tests to pass-------------------------------------

	public function supportedLanguages()
	{
		return [['de'], ['en'], ['es'], ['fr'], ['it'], ['nl'], ['pl'], ['ru'], ['ceb'], ['sv'], ['vi'], ['war']];
	}

	/**
	 * @dataProvider supportedLanguages
	 */
	public function testSetLanguage($language)
	{
		if ($language !== 'en') {
			$this->assertNotEquals($language, $this->wikiRand->getLanguage());
		}

		$this->wikiRand->setLanguage($language);
		$this->assertEquals($language, $this->wikiRand->getLanguage());
	}

	public function testSetLanguageForException()
	{
		$this->setExpectedException('\Unicodeveloper\Larapedia\Exception\LanguageNotSupportedException', sprintf('Language [%s] is not supported', 'ar'));
		$this->wikiRand->setLanguage('ar');
	}

	public function supportedEngines()
	{
		return [['pedia'], ['quote']];
	}

	/**
	 * @dataProvider supportedEngines
	 */
	public function testSetEngines($engine)
	{
		if ($engine !== 'pedia') {
			$this->assertNotEquals($engine, $this->wikiRand->getEngine());
		}

		$this->wikiRand->setEngine($engine);
		$this->assertEquals($engine, $this->wikiRand->getEngine());
	}

	public function testSetEngineForException()
	{
		$this->setExpectedException('\Unicodeveloper\Larapedia\Exception\EngineNotSupportedException', sprintf('Engine [%s] is not supported', 'rhyme'));
		$this->wikiRand->seEngine('rhyme');
	}

}
