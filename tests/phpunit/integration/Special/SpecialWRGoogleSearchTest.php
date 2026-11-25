<?php

namespace MediaWiki\Extension\WRGoogleSearch\Tests\Integration\Special;

use Interwiki;
use MediaWiki\Extension\WRGoogleSearch\Special\SpecialWRGoogleSearch;
use MediaWiki\Interwiki\InterwikiLookupAdapter;
use MediaWiki\MainConfigNames;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Site\HashSiteStore;
use MediaWiki\Title\Title;
use SpecialPageTestBase;

/**
 * @group Database
 * @covers \MediaWiki\Extension\WRGoogleSearch\Special\SpecialWRGoogleSearch
 */
class SpecialWRGoogleSearchTest extends SpecialPageTestBase {

	private const EXTERNAL_HOST = 'external.example';

	private ?SpecialWRGoogleSearch $page = null;

	protected function setUp(): void {
		parent::setUp();

		$this->setService( 'InterwikiLookup', new InterwikiLookupAdapter(
			new HashSiteStore(),
			[
				'kzexternal' => new Interwiki(
					'kzexternal',
					'https://' . self::EXTERNAL_HOST . '/wiki/$1',
					'https://' . self::EXTERNAL_HOST . '/w/api.php',
					'unittest_externalwiki',
					0
				),
			]
		) );
		// The title codec caches the interwiki lookup; rebuild it on the new one.
		$services = $this->getServiceContainer();
		$services->resetServiceForTesting( 'TitleFormatter' );
		$services->resetServiceForTesting( 'TitleParser' );
		$services->resetServiceForTesting( '_MediaWikiTitleCodec' );
	}

	/** @inheritDoc */
	protected function newSpecialPage() {
		$this->page = new SpecialWRGoogleSearch();
		return $this->page;
	}

	private function getRedirect(): string {
		return $this->page->getOutput()->getRedirect();
	}

	public static function provideInterwikiInput(): array {
		return [
			'q parameter' => [ '', [ 'q' => 'kzexternal:Some page' ] ],
			'subpage' => [ 'kzexternal:Some page', [] ],
		];
	}

	/**
	 * @dataProvider provideInterwikiInput
	 */
	public function testInterwikiTermGoesThroughGoToInterwiki( string $subPage, array $query ): void {
		$title = Title::newFromText( 'kzexternal:Some page' );
		$this->assertTrue( $title->isExternal(), 'precondition: the prefix is an interwiki' );

		$this->executeSpecialPage( $subPage, new FauxRequest( $query ) );

		$redirect = $this->getRedirect();
		$this->assertSame( $title->getFullUrlForRedirect(), $redirect );
		$this->assertStringContainsString( 'GoToInterwiki', $redirect );
		$this->assertStringNotContainsString( self::EXTERNAL_HOST, $redirect );
	}

	public function testLocalExactMatchRedirectsToArticle(): void {
		$target = $this->getExistingTestPage( 'WRGoogleSearch exact match target' )->getTitle();

		$this->executeSpecialPage( '', new FauxRequest( [ 'q' => $target->getPrefixedText() ] ) );

		$this->assertSame( $target->getFullURL(), $this->getRedirect() );
	}

	public function testUnknownTermShowsResultsPage(): void {
		$this->getNonexistingTestPage( 'WRGoogleSearch no such page' );

		[ $html ] = $this->executeSpecialPage(
			'',
			new FauxRequest( [ 'q' => 'WRGoogleSearch no such page' ] )
		);

		$this->assertSame( '', $this->getRedirect() );
		$this->assertStringContainsString( 'id="googleSearchResults"', $html );
		$this->assertStringContainsString( '(wrgooglesearch-loading)', $html );
	}

	public function testLoadingMessageIsEscaped(): void {
		$this->overrideConfigValue( MainConfigNames::UseDatabaseMessages, true );
		$this->editPage( 'MediaWiki:Wrgooglesearch-loading', '<b id="kz-raw-markup">x</b>' );

		[ $html ] = $this->executeSpecialPage(
			'',
			new FauxRequest( [ 'q' => 'WRGoogleSearch no such page' ] ),
			'en'
		);

		$this->assertStringNotContainsString( '<b id="kz-raw-markup">', $html );
		$this->assertStringContainsString( '&lt;b id=', $html );
	}
}
