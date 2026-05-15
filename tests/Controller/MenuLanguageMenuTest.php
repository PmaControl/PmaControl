<?php

declare(strict_types=1);

use App\Controller\Menu;
use PHPUnit\Framework\TestCase;

final class MenuLanguageMenuTest extends TestCase
{
    public function testLanguageMenuKeepsCurrentRouteAndQueryString(): void
    {
        $menu = Menu::buildLanguageMenu(
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
        $this->assertSame('/pmacontrol/en/home/index?client=12&filter=slow', $menu['items'][1]['url']);
        $this->assertSame('/pmacontrol/ar/home/index?client=12&filter=slow', $menu['items'][2]['url']);
        $this->assertTrue($menu['items'][0]['active']);
        $this->assertFalse($menu['items'][1]['active']);
    }

    public function testLanguageMenuSupportsMultiPartLanguagePrefixes(): void
    {
        $menu = Menu::buildLanguageMenu(
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

        $this->assertSame('/pmacontrol/fr/slave/show/17/channel:primary', $menu['items'][0]['url']);
        $this->assertSame('/pmacontrol/zh-cn/slave/show/17/channel:primary', $menu['items'][1]['url']);
        $this->assertTrue($menu['items'][1]['active']);
    }

    public function testLanguageMenuKeepsOnlyRequestedLanguagesInConfiguredOrder(): void
    {
        $menu = Menu::buildLanguageMenu(
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

        $this->assertSame(['ar', 'ru', 'pl', 'fr', 'en', 'zh-cn'], array_column($menu['items'], 'code'));
        $this->assertSame(['🇸🇦', '🇷🇺', '🇵🇱', '🇫🇷', '🇬🇧', '🇨🇳'], array_column($menu['items'], 'flag'));
        $this->assertSame(['AR', 'RU', 'PL', 'FR', 'EN', 'ZH'], array_column($menu['items'], 'short_code'));
    }
}
