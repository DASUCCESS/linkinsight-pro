<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Seeder;

class CmsPagesSeeder extends Seeder
{
    public function run(): void
    {
        // Homepage
        $home = Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'title'        => 'Homepage',
                'type'         => 'system',
                'is_home'      => true,
                'is_published' => true,
                'content'      => null,
                'meta_title'   => 'LinkInsight Pro – LinkedIn Analytics & Growth Platform',
                'meta_description' => 'Self-hosted LinkedIn analytics and growth platform with full CMS, SEO and theme engine.',
                'indexable'    => true,
            ]
        );

        // Default homepage sections
        $sections = [
            'hero'         => 'Hero',
            'mission'      => 'Our Mission',
            'vision'       => 'Our Vision',
            'problem'      => 'The Problem',
            'solution'     => 'Our Solution',
            'why_us'       => 'Why LinkInsight Pro',
            'testimonials' => 'Testimonials',
            'cta'          => 'Call to Action',
            'faq'          => 'Frequently Asked Questions',
        ];

        $position = 1;
        foreach ($sections as $key => $label) {
            PageSection::firstOrCreate(
                ['page_id' => $home->id, 'key' => $key],
                [
                    'title'     => $label,
                    'subtitle'  => null,
                    'body'      => null,
                    'position'  => $position++,
                    'is_visible'=> true,
                ]
            );
        }

        // Static public pages
        $staticPages = [
            ['title' => 'About',   'slug' => 'about',   'type' => 'page'],
            ['title' => 'Contact', 'slug' => 'contact', 'type' => 'page'],
            ['title' => 'Terms',   'slug' => 'terms',   'type' => 'page'],
            ['title' => 'Privacy', 'slug' => 'privacy','type' => 'page'],
            ['title' => 'FAQ',     'slug' => 'faq',     'type' => 'page'],
        ];

        foreach ($staticPages as $pageData) {
            Page::firstOrCreate(
                ['slug' => $pageData['slug']],
                [
                    'title'        => $pageData['title'],
                    'type'         => $pageData['type'],
                    'is_home'      => false,
                    'is_published' => true,
                    'content'      => '<p>Content for '.$pageData['title'].' page.</p>',
                    'meta_title'   => $pageData['title'].' – LinkInsight Pro',
                    'meta_description' => 'Information about '.$pageData['title'].' for LinkInsight Pro.',
                    'indexable'    => true,
                ]
            );
        }
    }
}
