<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/** Curated public navigation; move to a database only if it needs CMS editing. */
class SiteNavigation extends BaseConfig
{
    public array $groups = [
        [
            'eyebrow' => '01 / SOLUTIONS',
            'links' => [
                ['label' => 'Residential solar', 'description' => 'A system planned around your home and daily use.', 'href' => '#services'],
                ['label' => 'Commercial solar', 'description' => 'Start a conversation about a business property.', 'href' => '#contact'],
            ],
        ],
        [
            'eyebrow' => '02 / THE SYSTEM',
            'links' => [
                ['label' => 'Solar panels', 'description' => 'Explore rooftop solar and system design.', 'href' => '#products'],
                ['label' => 'Battery storage', 'description' => 'See how storage can complement solar.', 'href' => '#products'],
                ['label' => 'Racking & wiring', 'description' => 'See the components behind a complete solar system.', 'href' => '#products'],
            ],
        ],
        [
            'eyebrow' => '03 / HERE TO HELP',
            'links' => [
                ['label' => 'About our approach', 'description' => 'Get to know how we plan each solar project.', 'href' => '#about-us'],
                ['label' => 'Support & FAQs', 'description' => 'Find answers and talk with our team.', 'href' => '#questions'],
            ],
        ],
    ];
}
