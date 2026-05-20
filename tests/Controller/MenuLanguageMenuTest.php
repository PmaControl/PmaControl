<?php

declare(strict_types=1);

use App\Controller\Menu;
use App\Library\LanguageMenuBuilder;
use PHPUnit\Framework\TestCase;

final class MenuLanguageMenuTest extends TestCase
{
    public function testLanguageMenuKeepsCurrentRouteAndQueryString(): void
    {
        $menu = LanguageMenuBuilder::build(
            'fr/home/index',
            'glial_path=fr/home/index&client=12&filter=slow',
            'fr',
            'fr,en,ar,zh-cn',
            '/pmacontrol/',
            [
                'fr' => 'French',
                'en' => 'English',
                'ar' => 'Arabic',
                'zh-cn' => 'Chinese',
            ]
        );

        $this->assertSame('French', $menu['current_label']);
        $this->assertSame('🇫🇷', $menu['current_flag']);
        $this->assertSame('FR', $menu['current_short_code']);
        $itemsByCode = array_column($menu['items'], null, 'code');
        $this->assertSame('/pmacontrol/en/home/index?client=12&filter=slow', $itemsByCode['en']['url']);
        $this->assertSame('/pmacontrol/ar/home/index?client=12&filter=slow', $itemsByCode['ar']['url']);
        $this->assertTrue($itemsByCode['fr']['active']);
        $this->assertFalse($itemsByCode['en']['active']);
    }

    public function testLanguageMenuSupportsMultiPartLanguagePrefixes(): void
    {
        $menu = LanguageMenuBuilder::build(
            'zh-cn/slave/show/17/channel:primary',
            'glial_path=zh-cn/slave/show/17/channel:primary',
            'zh-cn',
            'fr,zh-cn',
            '/pmacontrol',
            [
                'fr' => 'French',
                'zh-cn' => 'Chinese',
            ]
        );

        $itemsByCode = array_column($menu['items'], null, 'code');
        $this->assertSame('/pmacontrol/fr/slave/show/17/channel:primary', $itemsByCode['fr']['url']);
        $this->assertSame('/pmacontrol/zh-cn/slave/show/17/channel:primary', $itemsByCode['zh-cn']['url']);
        $this->assertTrue($itemsByCode['zh-cn']['active']);
    }

    public function testLanguageMenuKeepsOnlyRequestedLanguagesInAlphabeticalOrder(): void
    {
        $menu = LanguageMenuBuilder::build(
            'fr/home/index',
            '',
            'fr',
            'ar,ru,pl,fr,en,zh-cn',
            '/pmacontrol/',
            [
                'ar' => 'Arabic',
                'ru' => 'Russian',
                'pl' => 'Polish',
                'fr' => 'French',
                'en' => 'English',
                'zh-cn' => 'Chinese',
            ]
        );

        $this->assertSame(['ar', 'zh-cn', 'en', 'fr', 'pl', 'ru'], array_column($menu['items'], 'code'));
        $this->assertSame(['🇸🇦', '🇨🇳', '🇬🇧', '🇫🇷', '🇵🇱', '🇷🇺'], array_column($menu['items'], 'flag'));
        $this->assertSame(['AR', 'ZH', 'EN', 'FR', 'PL', 'RU'], array_column($menu['items'], 'short_code'));
    }

    public function testLanguageMenuBuilderIsNotPublicControllerAction(): void
    {
        $this->assertFalse(method_exists(Menu::class, 'buildLanguageMenu'));
    }

    public function testLanguageMenuEncodesUnsafeRouteSegments(): void
    {
        $menu = LanguageMenuBuilder::build(
            'fr/foo?bar/a b/c#d/channel:primary',
            'glial_path=fr/foo%3Fbar/a%20b/c%23d/channel:primary&client=12',
            'fr',
            'fr,en',
            '/pmacontrol/',
            [
                'fr' => 'French',
                'en' => 'English',
            ]
        );

        $itemsByCode = array_column($menu['items'], null, 'code');
        $this->assertSame('/pmacontrol/en/foo%3Fbar/a%20b/c%23d/channel:primary?client=12', $itemsByCode['en']['url']);
    }
}
