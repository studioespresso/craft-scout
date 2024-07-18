module.exports = {
    title: 'Scout for Craft CMS',
    description: "Scout for Craft CMS",
    base: '/craft-scout',
    head: [
        ['meta', {content: 'https://github.com/studioespresso', property: 'og:see_also',}],
        [
            'script',
            {
                defer: '',
                'data-domain': 'studioespresso.github.io',
                src: 'https://stats.studioespresso.co/js/script.tagged-events.outbound-links.js'
            }
        ],
    ],
    themeConfig: {
        logo: '/img/plugin-logo.svg',
        sidebar: [
            {
                text: 'General',
                items:
                    [
                        {text: 'Getting started', link: '/getting-started'},
                        {text: 'Indices', link: '/indices'},
                        {text: 'Templating', link: '/templating'},
                        {text: 'Configuration', link: '/configuration'},
                        {text: 'Console commands', link: '/console'},

                    ]
            },
            {
                text: 'Extending Scout',
                items:
                    [
                        {text: 'Events', link: '/events'},
                        {text: 'Splitting elements', link: '/splitting-elements'},
                        {text: 'Multiple element types', link: '/multiple-element-types'},
                        {text: 'Replicas', link: '/replicas'},
                    ]
            },
        ],
        nav: [
            {
                text: 'Buy now',
                link: 'https://plugins.craftcms.com/scout',
            },
            {
                text: 'Report an issue',
                link: 'https://github.com/studioespresso/craft-scout/issues'
            },
            {
                text: 'GitHub',
                link: 'https://github.com/studioespresso/craft-scout'
            }
        ]

    }
}
;