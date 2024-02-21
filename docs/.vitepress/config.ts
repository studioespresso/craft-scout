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
                        {text: 'Usage', link: '/general'},
                        {text: 'Field & settings', link: '/field'},
                        {text: 'Templating', link: '/templating'},
                        {text: 'Settings', link: '/settings'},

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