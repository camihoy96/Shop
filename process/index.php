<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="https://gmpg.org/xfn/11">
        <title>St4nger Devs</title>
        <style>
            #wpadminbar #wp-admin-bar-wccp_free_top_button .ab-icon:before {
                content: "\f160";
                color: #02CA02;
                top: 3px;
            }

            #wpadminbar #wp-admin-bar-wccp_free_top_button .ab-icon {
                transform: rotate(45deg);
            }
        </style>
        <meta name='robots' content='max-image-preview:large'/>
        <style>
            img:is([sizes="auto" i], [sizes^="auto," i]) {
                contain-intrinsic-size: 3000px 1500px
            }
        </style>
        <link rel='dns-prefetch' href='//fonts.googleapis.com'/>
        <link rel="alternate" type="application/rss+xml" title="GetMe &raquo; Feed" href="https://getme.com.my/feed/"/>
        <link rel="alternate" type="application/rss+xml" title="GetMe &raquo; Comments Feed" href="https://getme.com.my/comments/feed/"/>
        <script type="text/javascript">
            /* <![CDATA[ */
            window._wpemojiSettings = {
                "baseUrl": "https:\/\/s.w.org\/images\/core\/emoji\/16.0.1\/72x72\/",
                "ext": ".png",
                "svgUrl": "https:\/\/s.w.org\/images\/core\/emoji\/16.0.1\/svg\/",
                "svgExt": ".svg",
                "source": {
                    "concatemoji": "https:\/\/getme.com.my\/wp-includes\/js\/wp-emoji-release.min.js?ver=6.8.3"
                }
            };
            /*! This file is auto-generated */
            !function(s, n) {
                var o, i, e;
                function c(e) {
                    try {
                        var t = {
                            supportTests: e,
                            timestamp: (new Date).valueOf()
                        };
                        sessionStorage.setItem(o, JSON.stringify(t))
                    } catch (e) {}
                }
                function p(e, t, n) {
                    e.clearRect(0, 0, e.canvas.width, e.canvas.height),
                    e.fillText(t, 0, 0);
                    var t = new Uint32Array(e.getImageData(0, 0, e.canvas.width, e.canvas.height).data)
                      , a = (e.clearRect(0, 0, e.canvas.width, e.canvas.height),
                    e.fillText(n, 0, 0),
                    new Uint32Array(e.getImageData(0, 0, e.canvas.width, e.canvas.height).data));
                    return t.every(function(e, t) {
                        return e === a[t]
                    })
                }
                function u(e, t) {
                    e.clearRect(0, 0, e.canvas.width, e.canvas.height),
                    e.fillText(t, 0, 0);
                    for (var n = e.getImageData(16, 16, 1, 1), a = 0; a < n.data.length; a++)
                        if (0 !== n.data[a])
                            return !1;
                    return !0
                }
                function f(e, t, n, a) {
                    switch (t) {
                    case "flag":
                        return n(e, "\ud83c\udff3\ufe0f\u200d\u26a7\ufe0f", "\ud83c\udff3\ufe0f\u200b\u26a7\ufe0f") ? !1 : !n(e, "\ud83c\udde8\ud83c\uddf6", "\ud83c\udde8\u200b\ud83c\uddf6") && !n(e, "\ud83c\udff4\udb40\udc67\udb40\udc62\udb40\udc65\udb40\udc6e\udb40\udc67\udb40\udc7f", "\ud83c\udff4\u200b\udb40\udc67\u200b\udb40\udc62\u200b\udb40\udc65\u200b\udb40\udc6e\u200b\udb40\udc67\u200b\udb40\udc7f");
                    case "emoji":
                        return !a(e, "\ud83e\udedf")
                    }
                    return !1
                }
                function g(e, t, n, a) {
                    var r = "undefined" != typeof WorkerGlobalScope && self instanceof WorkerGlobalScope ? new OffscreenCanvas(300,150) : s.createElement("canvas")
                      , o = r.getContext("2d", {
                        willReadFrequently: !0
                    })
                      , i = (o.textBaseline = "top",
                    o.font = "600 32px Arial",
                    {});
                    return e.forEach(function(e) {
                        i[e] = t(o, e, n, a)
                    }),
                    i
                }
                function t(e) {
                    var t = s.createElement("script");
                    t.src = e,
                    t.defer = !0,
                    s.head.appendChild(t)
                }
                "undefined" != typeof Promise && (o = "wpEmojiSettingsSupports",
                i = ["flag", "emoji"],
                n.supports = {
                    everything: !0,
                    everythingExceptFlag: !0
                },
                e = new Promise(function(e) {
                    s.addEventListener("DOMContentLoaded", e, {
                        once: !0
                    })
                }
                ),
                new Promise(function(t) {
                    var n = function() {
                        try {
                            var e = JSON.parse(sessionStorage.getItem(o));
                            if ("object" == typeof e && "number" == typeof e.timestamp && (new Date).valueOf() < e.timestamp + 604800 && "object" == typeof e.supportTests)
                                return e.supportTests
                        } catch (e) {}
                        return null
                    }();
                    if (!n) {
                        if ("undefined" != typeof Worker && "undefined" != typeof OffscreenCanvas && "undefined" != typeof URL && URL.createObjectURL && "undefined" != typeof Blob)
                            try {
                                var e = "postMessage(" + g.toString() + "(" + [JSON.stringify(i), f.toString(), p.toString(), u.toString()].join(",") + "));"
                                  , a = new Blob([e],{
                                    type: "text/javascript"
                                })
                                  , r = new Worker(URL.createObjectURL(a),{
                                    name: "wpTestEmojiSupports"
                                });
                                return void (r.onmessage = function(e) {
                                    c(n = e.data),
                                    r.terminate(),
                                    t(n)
                                }
                                )
                            } catch (e) {}
                        c(n = g(i, f, p, u))
                    }
                    t(n)
                }
                ).then(function(e) {
                    for (var t in e)
                        n.supports[t] = e[t],
                        n.supports.everything = n.supports.everything && n.supports[t],
                        "flag" !== t && (n.supports.everythingExceptFlag = n.supports.everythingExceptFlag && n.supports[t]);
                    n.supports.everythingExceptFlag = n.supports.everythingExceptFlag && !n.supports.flag,
                    n.DOMReady = !1,
                    n.readyCallback = function() {
                        n.DOMReady = !0
                    }
                }).then(function() {
                    return e
                }).then(function() {
                    var e;
                    n.supports.everything || (n.readyCallback(),
                    (e = n.source || {}).concatemoji ? t(e.concatemoji) : e.wpemoji && e.twemoji && (t(e.twemoji),
                    t(e.wpemoji)))
                }))
            }((window,
            document), window._wpemojiSettings);
            /* ]]> */
        </script>
        <link rel='stylesheet' id='astra-theme-css-css' href='https://getme.com.my/wp-content/themes/astra/assets/css/minified/style.min.css?ver=2.0.1' type='text/css' media='all'/>
        <style id='astra-theme-css-inline-css' type='text/css'>
            html {
                font-size: 81.25%;
            }

            a,.page-title {
                color: #075aae;
            }

            a:hover,a:focus {
                color: #000000;
            }

            body,button,input,select,textarea {
                font-family: 'Montserrat',sans-serif;
                font-weight: 400;
                font-size: 13px;
                font-size: 1rem;
            }

            blockquote {
                color: #000000;
            }

            h1,.entry-content h1,.entry-content h1 a,h2,.entry-content h2,.entry-content h2 a,h3,.entry-content h3,.entry-content h3 a,h4,.entry-content h4,.entry-content h4 a,h5,.entry-content h5,.entry-content h5 a,h6,.entry-content h6,.entry-content h6 a,.site-title,.site-title a {
                font-family: 'Montserrat',sans-serif;
                font-weight: 300;
            }

            .site-title {
                font-size: 35px;
                font-size: 2.6923076923077rem;
            }

            header .site-logo-img .custom-logo-link img {
                max-width: 100px;
            }

            .astra-logo-svg {
                width: 100px;
            }

            .ast-archive-description .ast-archive-title {
                font-size: 40px;
                font-size: 3.0769230769231rem;
            }

            .site-header .site-description {
                font-size: 15px;
                font-size: 1.1538461538462rem;
            }

            .entry-title {
                font-size: 30px;
                font-size: 2.3076923076923rem;
            }

            .comment-reply-title {
                font-size: 21px;
                font-size: 1.6153846153846rem;
            }

            .ast-comment-list #cancel-comment-reply-link {
                font-size: 13px;
                font-size: 1rem;
            }

            h1,.entry-content h1,.entry-content h1 a {
                font-size: 60px;
                font-size: 4.6153846153846rem;
            }

            h2,.entry-content h2,.entry-content h2 a {
                font-size: 40px;
                font-size: 3.0769230769231rem;
            }

            h3,.entry-content h3,.entry-content h3 a {
                font-size: 21px;
                font-size: 1.6153846153846rem;
            }

            h4,.entry-content h4,.entry-content h4 a {
                font-size: 20px;
                font-size: 1.5384615384615rem;
            }

            h5,.entry-content h5,.entry-content h5 a {
                font-size: 15px;
                font-size: 1.1538461538462rem;
            }

            h6,.entry-content h6,.entry-content h6 a {
                font-size: 12px;
                font-size: 0.92307692307692rem;
            }

            .ast-single-post .entry-title,.page-title {
                font-size: 30px;
                font-size: 2.3076923076923rem;
            }

            #secondary,#secondary button,#secondary input,#secondary select,#secondary textarea {
                font-size: 13px;
                font-size: 1rem;
            }

            ::selection {
                background-color: #075aae;
                color: #ffffff;
            }

            body,h1,.entry-title a,.entry-content h1,.entry-content h1 a,h2,.entry-content h2,.entry-content h2 a,h3,.entry-content h3,.entry-content h3 a,h4,.entry-content h4,.entry-content h4 a,h5,.entry-content h5,.entry-content h5 a,h6,.entry-content h6,.entry-content h6 a {
                color: #000000;
            }

            .tagcloud a:hover,.tagcloud a:focus,.tagcloud a.current-item {
                color: #ffffff;
                border-color: #075aae;
                background-color: #075aae;
            }

            .main-header-menu a,.ast-header-custom-item a {
                color: #000000;
            }

            .main-header-menu li:hover > a,.main-header-menu li:hover > .ast-menu-toggle,.main-header-menu .ast-masthead-custom-menu-items a:hover,.main-header-menu li.focus > a,.main-header-menu li.focus > .ast-menu-toggle,.main-header-menu .current-menu-item > a,.main-header-menu .current-menu-ancestor > a,.main-header-menu .current_page_item > a,.main-header-menu .current-menu-item > .ast-menu-toggle,.main-header-menu .current-menu-ancestor > .ast-menu-toggle,.main-header-menu .current_page_item > .ast-menu-toggle {
                color: #075aae;
            }

            input:focus,input[type="text"]:focus,input[type="email"]:focus,input[type="url"]:focus,input[type="password"]:focus,input[type="reset"]:focus,input[type="search"]:focus,textarea:focus {
                border-color: #075aae;
            }

            input[type="radio"]:checked,input[type=reset],input[type="checkbox"]:checked,input[type="checkbox"]:hover:checked,input[type="checkbox"]:focus:checked,input[type=range]::-webkit-slider-thumb {
                border-color: #075aae;
                background-color: #075aae;
                box-shadow: none;
            }

            .site-footer a:hover + .post-count,.site-footer a:focus + .post-count {
                background: #075aae;
                border-color: #075aae;
            }

            .ast-small-footer {
                color: #000000;
            }

            .ast-small-footer > .ast-footer-overlay {
                background-color: #ffffff;
            }

            .ast-small-footer a {
                color: #075aae;
            }

            .ast-small-footer a:hover {
                color: #000000;
            }

            .footer-adv .footer-adv-overlay {
                border-top-style: solid;
                border-top-color: #7a7a7a;
            }

            .ast-comment-meta {
                line-height: 1.666666667;
                font-size: 11px;
                font-size: 0.84615384615385rem;
            }

            .single .nav-links .nav-previous,.single .nav-links .nav-next,.single .ast-author-details .author-title,.ast-comment-meta {
                color: #075aae;
            }

            .menu-toggle,button,.ast-button,.button,input#submit,input[type="button"],input[type="submit"],input[type="reset"] {
                border-radius: 0;
                padding: 10px 40px;
                color: #ffffff;
                border-color: #075aae;
                background-color: #075aae;
            }

            button:focus,.menu-toggle:hover,button:hover,.ast-button:hover,.button:hover,input[type=reset]:hover,input[type=reset]:focus,input#submit:hover,input#submit:focus,input[type="button"]:hover,input[type="button"]:focus,input[type="submit"]:hover,input[type="submit"]:focus {
                color: #ffffff;
                border-color: #075aae;
                background-color: #075aae;
            }

            .entry-meta,.entry-meta * {
                line-height: 1.45;
                color: #075aae;
            }

            .entry-meta a:hover,.entry-meta a:hover *,.entry-meta a:focus,.entry-meta a:focus * {
                color: #000000;
            }

            .ast-404-layout-1 .ast-404-text {
                font-size: 200px;
                font-size: 15.384615384615rem;
            }

            .widget-title {
                font-size: 18px;
                font-size: 1.3846153846154rem;
                color: #000000;
            }

            #cat option,.secondary .calendar_wrap thead a,.secondary .calendar_wrap thead a:visited {
                color: #075aae;
            }

            .secondary .calendar_wrap #today,.ast-progress-val span {
                background: #075aae;
            }

            .secondary a:hover + .post-count,.secondary a:focus + .post-count {
                background: #075aae;
                border-color: #075aae;
            }

            .calendar_wrap #today > a {
                color: #ffffff;
            }

            .ast-pagination a,.page-links .page-link,.single .post-navigation a {
                color: #075aae;
            }

            .ast-pagination a:hover,.ast-pagination a:focus,.ast-pagination > span:hover:not(.dots),.ast-pagination > span.current,.page-links > .page-link,.page-links .page-link:hover,.post-navigation a:hover {
                color: #000000;
            }

            .ast-header-break-point .ast-mobile-menu-buttons-minimal.menu-toggle {
                background: transparent;
                color: #075aae;
            }

            .ast-header-break-point .ast-mobile-menu-buttons-outline.menu-toggle {
                background: transparent;
                border: 1px solid #075aae;
                color: #075aae;
            }

            .ast-header-break-point .ast-mobile-menu-buttons-fill.menu-toggle {
                background: #075aae;
                color: #ffffff;
            }

            @media (min-width: 545px) {
                .ast-page-builder-template .comments-area,.single.ast-page-builder-template .entry-header,.single.ast-page-builder-template .post-navigation {
                    max-width:1240px;
                    margin-left: auto;
                    margin-right: auto;
                }
            }

            @media (max-width: 768px) {
                .ast-archive-description .ast-archive-title {
                    font-size:40px;
                }

                .entry-title {
                    font-size: 30px;
                }

                h1,.entry-content h1,.entry-content h1 a {
                    font-size: 35px;
                }

                h2,.entry-content h2,.entry-content h2 a {
                    font-size: 25px;
                }

                h3,.entry-content h3,.entry-content h3 a {
                    font-size: 20px;
                }

                .ast-single-post .entry-title,.page-title {
                    font-size: 30px;
                }
            }

            @media (max-width: 544px) {
                .ast-archive-description .ast-archive-title {
                    font-size:40px;
                }

                .site-header .site-description {
                    font-size: 12px;
                    font-size: 0.92307692307692rem;
                }

                .entry-title {
                    font-size: 30px;
                }

                h1,.entry-content h1,.entry-content h1 a {
                    font-size: 30px;
                }

                h2,.entry-content h2,.entry-content h2 a {
                    font-size: 25px;
                }

                h3,.entry-content h3,.entry-content h3 a {
                    font-size: 20px;
                }

                .ast-single-post .entry-title,.page-title {
                    font-size: 30px;
                }
            }

            @media (max-width: 768px) {
                html {
                    font-size:74.1%;
                }
            }

            @media (max-width: 544px) {
                html {
                    font-size:74.1%;
                }
            }

            @media (min-width: 769px) {
                .ast-container {
                    max-width:1240px;
                }
            }

            @font-face {
                font-family: "Astra";
                src: url( https://getme.com.my/wp-content/themes/astra/assets/fonts/astra.woff) format("woff"),url( https://getme.com.my/wp-content/themes/astra/assets/fonts/astra.ttf) format("truetype"),url( https://getme.com.my/wp-content/themes/astra/assets/fonts/astra.svg#astra) format("svg");
                font-weight: normal;
                font-style: normal;
                font-display: fallback;
            }

            @media (max-width: 921px) {
                .main-header-bar .main-header-bar-navigation {
                    display:none;
                }
            }

            @media (min-width: 769px) {
                .single-post .site-content > .ast-container {
                    max-width:1100px;
                }
            }

            .ast-desktop .main-header-menu.submenu-with-border .sub-menu,.ast-desktop .main-header-menu.submenu-with-border .children,.ast-desktop .main-header-menu.submenu-with-border .astra-full-megamenu-wrapper {
                border-color: #eaeaea;
            }

            .ast-desktop .main-header-menu.submenu-with-border .sub-menu,.ast-desktop .main-header-menu.submenu-with-border .children {
                border-top-width: 1px;
                border-right-width: 1px;
                border-left-width: 1px;
                border-bottom-width: 1px;
                border-style: solid;
            }

            .ast-desktop .main-header-menu.submenu-with-border .sub-menu .sub-menu,.ast-desktop .main-header-menu.submenu-with-border .children .children {
                top: -1px;
            }

            .ast-desktop .main-header-menu.submenu-with-border .sub-menu a,.ast-desktop .main-header-menu.submenu-with-border .children a {
                border-bottom-width: 1px;
                border-style: solid;
                border-color: #eaeaea;
            }

            @media (min-width: 769px) {
                .main-header-menu .sub-menu li.ast-left-align-sub-menu:hover > ul,.main-header-menu .sub-menu li.ast-left-align-sub-menu.focus > ul {
                    margin-left:-2px;
                }
            }

            .ast-small-footer {
                border-top-style: solid;
                border-top-width: 1px;
                border-top-color: #f2f2f2;
            }

            @media (max-width: 920px) {
                .ast-404-layout-1 .ast-404-text {
                    font-size:100px;
                    font-size: 7.6923076923077rem;
                }
            }

            .ast-header-break-point .site-header {
                border-bottom-width: 1px;
                border-bottom-color: #f2f2f2;
            }

            @media (min-width: 769px) {
                .main-header-bar {
                    border-bottom-width:1px;
                    border-bottom-color: #f2f2f2;
                }
            }

            .ast-flex {
                -webkit-align-content: center;
                -ms-flex-line-pack: center;
                align-content: center;
                -webkit-box-align: center;
                -webkit-align-items: center;
                -moz-box-align: center;
                -ms-flex-align: center;
                align-items: center;
            }

            .main-header-bar {
                padding: 1em 0;
            }

            .ast-site-identity {
                padding: 0;
            }

            @media (min-width: 769px) {
                .ast-theme-transparent-header #masthead {
                    position:absolute;
                    left: 0;
                    right: 0;
                }

                .ast-theme-transparent-header .main-header-bar, .ast-theme-transparent-header.ast-header-break-point .main-header-bar {
                    background: none;
                }

                body.elementor-editor-active.ast-theme-transparent-header #masthead, .fl-builder-edit .ast-theme-transparent-header #masthead, body.vc_editor.ast-theme-transparent-header #masthead {
                    z-index: 0;
                }

                .ast-header-break-point.ast-replace-site-logo-transparent.ast-theme-transparent-header .custom-mobile-logo-link {
                    display: none;
                }

                .ast-header-break-point.ast-replace-site-logo-transparent.ast-theme-transparent-header .transparent-custom-logo {
                    display: inline-block;
                }

                .ast-theme-transparent-header .ast-above-header {
                    background-image: none;
                    background-color: transparent;
                }

                .ast-theme-transparent-header .ast-below-header {
                    background-image: none;
                    background-color: transparent;
                }
            }

            @media (max-width: 768px) {
                .ast-theme-transparent-header #masthead {
                    position:absolute;
                    left: 0;
                    right: 0;
                }

                .ast-theme-transparent-header .main-header-bar, .ast-theme-transparent-header.ast-header-break-point .main-header-bar {
                    background: none;
                }

                body.elementor-editor-active.ast-theme-transparent-header #masthead, .fl-builder-edit .ast-theme-transparent-header #masthead, body.vc_editor.ast-theme-transparent-header #masthead {
                    z-index: 0;
                }

                .ast-header-break-point.ast-replace-site-logo-transparent.ast-theme-transparent-header .custom-mobile-logo-link {
                    display: none;
                }

                .ast-header-break-point.ast-replace-site-logo-transparent.ast-theme-transparent-header .transparent-custom-logo {
                    display: inline-block;
                }

                .ast-theme-transparent-header .ast-above-header {
                    background-image: none;
                    background-color: transparent;
                }

                .ast-theme-transparent-header .ast-below-header {
                    background-image: none;
                    background-color: transparent;
                }
            }

            .ast-theme-transparent-header .main-header-bar, .ast-theme-transparent-header .site-header {
                border-bottom-width: 0;
            }

            .ast-breadcrumbs .trail-browse, .ast-breadcrumbs .trail-items, .ast-breadcrumbs .trail-items li {
                display: inline-block;
                margin: 0;
                padding: 0;
                border: none;
                background: inherit;
                text-indent: 0;
            }

            .ast-breadcrumbs .trail-browse {
                font-size: inherit;
                font-style: inherit;
                font-weight: inherit;
                color: inherit;
            }

            .ast-breadcrumbs .trail-items {
                list-style: none;
            }

            .trail-items li::after {
                padding: 0 0.3em;
                content: "»";
            }

            .trail-items li:last-of-type::after {
                display: none;
            }
        </style>
        <link rel='stylesheet' id='astra-google-fonts-css' href='//fonts.googleapis.com/css?family=Montserrat%3A400%2C300&#038;display=fallback&#038;ver=2.0.1' type='text/css' media='all'/>
        <link rel='stylesheet' id='wp-event-manager-frontend-css' href='https://getme.com.my/wp-content/plugins/wp-event-manager/assets/css/frontend.min.css?ver=6.8.3' type='text/css' media='all'/>
        <link rel='stylesheet' id='wp-event-manager-jquery-ui-daterangepicker-css' href='https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/jquery-ui-daterangepicker/jquery.comiseo.daterangepicker.css?ver=6.8.3' type='text/css' media='all'/>
        <link rel='stylesheet' id='wp-event-manager-jquery-ui-daterangepicker-style-css' href='https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/jquery-ui-daterangepicker/styles.css?ver=6.8.3' type='text/css' media='all'/>
        <link rel='stylesheet' id='wp-event-manager-jquery-ui-css-css' href='https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/jquery-ui/jquery-ui.css?ver=6.8.3' type='text/css' media='all'/>
        <link rel='stylesheet' id='wp-event-manager-grid-style-css' href='https://getme.com.my/wp-content/plugins/wp-event-manager/assets/css/wpem-grid.min.css?ver=6.8.3' type='text/css' media='all'/>
        <link rel='stylesheet' id='wp-event-manager-font-style-css' href='https://getme.com.my/wp-content/plugins/wp-event-manager/assets/fonts/style.css?ver=6.8.3' type='text/css' media='all'/>
        <style id='wp-emoji-styles-inline-css' type='text/css'>
            img.wp-smiley, img.emoji {
                display: inline !important;
                border: none !important;
                box-shadow: none !important;
                height: 1em !important;
                width: 1em !important;
                margin: 0 0.07em !important;
                vertical-align: -0.1em !important;
                background: none !important;
                padding: 0 !important;
            }
        </style>
        <link rel='stylesheet' id='wp-block-library-css' href='https://getme.com.my/wp-includes/css/dist/block-library/style.min.css?ver=6.8.3' type='text/css' media='all'/>
        <style id='classic-theme-styles-inline-css' type='text/css'>
            /*! This file is auto-generated */
            .wp-block-button__link {
                color: #fff;
                background-color: #32373c;
                border-radius: 9999px;
                box-shadow: none;
                text-decoration: none;
                padding: calc(.667em + 2px) calc(1.333em + 2px);
                font-size: 1.125em
            }

            .wp-block-file__button {
                background: #32373c;
                color: #fff;
                text-decoration: none
            }
        </style>
        <style id='global-styles-inline-css' type='text/css'>
            :root {
                --wp--preset--aspect-ratio--square: 1;
                --wp--preset--aspect-ratio--4-3: 4/3;
                --wp--preset--aspect-ratio--3-4: 3/4;
                --wp--preset--aspect-ratio--3-2: 3/2;
                --wp--preset--aspect-ratio--2-3: 2/3;
                --wp--preset--aspect-ratio--16-9: 16/9;
                --wp--preset--aspect-ratio--9-16: 9/16;
                --wp--preset--color--black: #000000;
                --wp--preset--color--cyan-bluish-gray: #abb8c3;
                --wp--preset--color--white: #ffffff;
                --wp--preset--color--pale-pink: #f78da7;
                --wp--preset--color--vivid-red: #cf2e2e;
                --wp--preset--color--luminous-vivid-orange: #ff6900;
                --wp--preset--color--luminous-vivid-amber: #fcb900;
                --wp--preset--color--light-green-cyan: #7bdcb5;
                --wp--preset--color--vivid-green-cyan: #00d084;
                --wp--preset--color--pale-cyan-blue: #8ed1fc;
                --wp--preset--color--vivid-cyan-blue: #0693e3;
                --wp--preset--color--vivid-purple: #9b51e0;
                --wp--preset--gradient--vivid-cyan-blue-to-vivid-purple: linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%);
                --wp--preset--gradient--light-green-cyan-to-vivid-green-cyan: linear-gradient(135deg,rgb(122,220,180) 0%,rgb(0,208,130) 100%);
                --wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange: linear-gradient(135deg,rgba(252,185,0,1) 0%,rgba(255,105,0,1) 100%);
                --wp--preset--gradient--luminous-vivid-orange-to-vivid-red: linear-gradient(135deg,rgba(255,105,0,1) 0%,rgb(207,46,46) 100%);
                --wp--preset--gradient--very-light-gray-to-cyan-bluish-gray: linear-gradient(135deg,rgb(238,238,238) 0%,rgb(169,184,195) 100%);
                --wp--preset--gradient--cool-to-warm-spectrum: linear-gradient(135deg,rgb(74,234,220) 0%,rgb(151,120,209) 20%,rgb(207,42,186) 40%,rgb(238,44,130) 60%,rgb(251,105,98) 80%,rgb(254,248,76) 100%);
                --wp--preset--gradient--blush-light-purple: linear-gradient(135deg,rgb(255,206,236) 0%,rgb(152,150,240) 100%);
                --wp--preset--gradient--blush-bordeaux: linear-gradient(135deg,rgb(254,205,165) 0%,rgb(254,45,45) 50%,rgb(107,0,62) 100%);
                --wp--preset--gradient--luminous-dusk: linear-gradient(135deg,rgb(255,203,112) 0%,rgb(199,81,192) 50%,rgb(65,88,208) 100%);
                --wp--preset--gradient--pale-ocean: linear-gradient(135deg,rgb(255,245,203) 0%,rgb(182,227,212) 50%,rgb(51,167,181) 100%);
                --wp--preset--gradient--electric-grass: linear-gradient(135deg,rgb(202,248,128) 0%,rgb(113,206,126) 100%);
                --wp--preset--gradient--midnight: linear-gradient(135deg,rgb(2,3,129) 0%,rgb(40,116,252) 100%);
                --wp--preset--font-size--small: 13px;
                --wp--preset--font-size--medium: 20px;
                --wp--preset--font-size--large: 36px;
                --wp--preset--font-size--x-large: 42px;
                --wp--preset--spacing--20: 0.44rem;
                --wp--preset--spacing--30: 0.67rem;
                --wp--preset--spacing--40: 1rem;
                --wp--preset--spacing--50: 1.5rem;
                --wp--preset--spacing--60: 2.25rem;
                --wp--preset--spacing--70: 3.38rem;
                --wp--preset--spacing--80: 5.06rem;
                --wp--preset--shadow--natural: 6px 6px 9px rgba(0, 0, 0, 0.2);
                --wp--preset--shadow--deep: 12px 12px 50px rgba(0, 0, 0, 0.4);
                --wp--preset--shadow--sharp: 6px 6px 0px rgba(0, 0, 0, 0.2);
                --wp--preset--shadow--outlined: 6px 6px 0px -3px rgba(255, 255, 255, 1), 6px 6px rgba(0, 0, 0, 1);
                --wp--preset--shadow--crisp: 6px 6px 0px rgba(0, 0, 0, 1);
            }

            :where(.is-layout-flex) {
                gap: 0.5em;
            }

            :where(.is-layout-grid) {
                gap: 0.5em;
            }

            body .is-layout-flex {
                display: flex;
            }

            .is-layout-flex {
                flex-wrap: wrap;
                align-items: center;
            }

            .is-layout-flex > :is(*, div) {
                margin: 0;
            }

            body .is-layout-grid {
                display: grid;
            }

            .is-layout-grid > :is(*, div) {
                margin: 0;
            }

            :where(.wp-block-columns.is-layout-flex) {
                gap: 2em;
            }

            :where(.wp-block-columns.is-layout-grid) {
                gap: 2em;
            }

            :where(.wp-block-post-template.is-layout-flex) {
                gap: 1.25em;
            }

            :where(.wp-block-post-template.is-layout-grid) {
                gap: 1.25em;
            }

            .has-black-color {
                color: var(--wp--preset--color--black) !important;
            }

            .has-cyan-bluish-gray-color {
                color: var(--wp--preset--color--cyan-bluish-gray) !important;
            }

            .has-white-color {
                color: var(--wp--preset--color--white) !important;
            }

            .has-pale-pink-color {
                color: var(--wp--preset--color--pale-pink) !important;
            }

            .has-vivid-red-color {
                color: var(--wp--preset--color--vivid-red) !important;
            }

            .has-luminous-vivid-orange-color {
                color: var(--wp--preset--color--luminous-vivid-orange) !important;
            }

            .has-luminous-vivid-amber-color {
                color: var(--wp--preset--color--luminous-vivid-amber) !important;
            }

            .has-light-green-cyan-color {
                color: var(--wp--preset--color--light-green-cyan) !important;
            }

            .has-vivid-green-cyan-color {
                color: var(--wp--preset--color--vivid-green-cyan) !important;
            }

            .has-pale-cyan-blue-color {
                color: var(--wp--preset--color--pale-cyan-blue) !important;
            }

            .has-vivid-cyan-blue-color {
                color: var(--wp--preset--color--vivid-cyan-blue) !important;
            }

            .has-vivid-purple-color {
                color: var(--wp--preset--color--vivid-purple) !important;
            }

            .has-black-background-color {
                background-color: var(--wp--preset--color--black) !important;
            }

            .has-cyan-bluish-gray-background-color {
                background-color: var(--wp--preset--color--cyan-bluish-gray) !important;
            }

            .has-white-background-color {
                background-color: var(--wp--preset--color--white) !important;
            }

            .has-pale-pink-background-color {
                background-color: var(--wp--preset--color--pale-pink) !important;
            }

            .has-vivid-red-background-color {
                background-color: var(--wp--preset--color--vivid-red) !important;
            }

            .has-luminous-vivid-orange-background-color {
                background-color: var(--wp--preset--color--luminous-vivid-orange) !important;
            }

            .has-luminous-vivid-amber-background-color {
                background-color: var(--wp--preset--color--luminous-vivid-amber) !important;
            }

            .has-light-green-cyan-background-color {
                background-color: var(--wp--preset--color--light-green-cyan) !important;
            }

            .has-vivid-green-cyan-background-color {
                background-color: var(--wp--preset--color--vivid-green-cyan) !important;
            }

            .has-pale-cyan-blue-background-color {
                background-color: var(--wp--preset--color--pale-cyan-blue) !important;
            }

            .has-vivid-cyan-blue-background-color {
                background-color: var(--wp--preset--color--vivid-cyan-blue) !important;
            }

            .has-vivid-purple-background-color {
                background-color: var(--wp--preset--color--vivid-purple) !important;
            }

            .has-black-border-color {
                border-color: var(--wp--preset--color--black) !important;
            }

            .has-cyan-bluish-gray-border-color {
                border-color: var(--wp--preset--color--cyan-bluish-gray) !important;
            }

            .has-white-border-color {
                border-color: var(--wp--preset--color--white) !important;
            }

            .has-pale-pink-border-color {
                border-color: var(--wp--preset--color--pale-pink) !important;
            }

            .has-vivid-red-border-color {
                border-color: var(--wp--preset--color--vivid-red) !important;
            }

            .has-luminous-vivid-orange-border-color {
                border-color: var(--wp--preset--color--luminous-vivid-orange) !important;
            }

            .has-luminous-vivid-amber-border-color {
                border-color: var(--wp--preset--color--luminous-vivid-amber) !important;
            }

            .has-light-green-cyan-border-color {
                border-color: var(--wp--preset--color--light-green-cyan) !important;
            }

            .has-vivid-green-cyan-border-color {
                border-color: var(--wp--preset--color--vivid-green-cyan) !important;
            }

            .has-pale-cyan-blue-border-color {
                border-color: var(--wp--preset--color--pale-cyan-blue) !important;
            }

            .has-vivid-cyan-blue-border-color {
                border-color: var(--wp--preset--color--vivid-cyan-blue) !important;
            }

            .has-vivid-purple-border-color {
                border-color: var(--wp--preset--color--vivid-purple) !important;
            }

            .has-vivid-cyan-blue-to-vivid-purple-gradient-background {
                background: var(--wp--preset--gradient--vivid-cyan-blue-to-vivid-purple) !important;
            }

            .has-light-green-cyan-to-vivid-green-cyan-gradient-background {
                background: var(--wp--preset--gradient--light-green-cyan-to-vivid-green-cyan) !important;
            }

            .has-luminous-vivid-amber-to-luminous-vivid-orange-gradient-background {
                background: var(--wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange) !important;
            }

            .has-luminous-vivid-orange-to-vivid-red-gradient-background {
                background: var(--wp--preset--gradient--luminous-vivid-orange-to-vivid-red) !important;
            }

            .has-very-light-gray-to-cyan-bluish-gray-gradient-background {
                background: var(--wp--preset--gradient--very-light-gray-to-cyan-bluish-gray) !important;
            }

            .has-cool-to-warm-spectrum-gradient-background {
                background: var(--wp--preset--gradient--cool-to-warm-spectrum) !important;
            }

            .has-blush-light-purple-gradient-background {
                background: var(--wp--preset--gradient--blush-light-purple) !important;
            }

            .has-blush-bordeaux-gradient-background {
                background: var(--wp--preset--gradient--blush-bordeaux) !important;
            }

            .has-luminous-dusk-gradient-background {
                background: var(--wp--preset--gradient--luminous-dusk) !important;
            }

            .has-pale-ocean-gradient-background {
                background: var(--wp--preset--gradient--pale-ocean) !important;
            }

            .has-electric-grass-gradient-background {
                background: var(--wp--preset--gradient--electric-grass) !important;
            }

            .has-midnight-gradient-background {
                background: var(--wp--preset--gradient--midnight) !important;
            }

            .has-small-font-size {
                font-size: var(--wp--preset--font-size--small) !important;
            }

            .has-medium-font-size {
                font-size: var(--wp--preset--font-size--medium) !important;
            }

            .has-large-font-size {
                font-size: var(--wp--preset--font-size--large) !important;
            }

            .has-x-large-font-size {
                font-size: var(--wp--preset--font-size--x-large) !important;
            }

            :where(.wp-block-post-template.is-layout-flex) {
                gap: 1.25em;
            }

            :where(.wp-block-post-template.is-layout-grid) {
                gap: 1.25em;
            }

            :where(.wp-block-columns.is-layout-flex) {
                gap: 2em;
            }

            :where(.wp-block-columns.is-layout-grid) {
                gap: 2em;
            }

            :root :where(.wp-block-pullquote) {
                font-size: 1.5em;
                line-height: 1.6;
            }
        </style>
        <link rel='stylesheet' id='google-fonts-css' href='https://fonts.googleapis.com/css?family=Lato%3A400%2C500%2C600%2C700&#038;ver=2.2.8' type='text/css' media='all'/>
        <link rel='stylesheet' id='wp-event-manager-jquery-timepicker-css-css' href='https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/jquery-timepicker/jquery.timepicker.min.css?ver=6.8.3' type='text/css' media='all'/>
        <link rel='stylesheet' id='elementor-icons-css' href='https://getme.com.my/wp-content/plugins/elementor/assets/lib/eicons/css/elementor-icons.min.css?ver=5.6.2' type='text/css' media='all'/>
        <link rel='stylesheet' id='elementor-animations-css' href='https://getme.com.my/wp-content/plugins/elementor/assets/lib/animations/animations.min.css?ver=2.9.8' type='text/css' media='all'/>
        <link rel='stylesheet' id='elementor-frontend-css' href='https://getme.com.my/wp-content/plugins/elementor/assets/css/frontend.min.css?ver=2.9.8' type='text/css' media='all'/>
        <link rel='stylesheet' id='elementor-global-css' href='https://getme.com.my/wp-content/uploads/elementor/css/global.css?ver=1605838957' type='text/css' media='all'/>
        <link rel='stylesheet' id='elementor-post-246-css' href='https://getme.com.my/wp-content/uploads/elementor/css/post-246.css?ver=1613186504' type='text/css' media='all'/>
        <link rel='stylesheet' id='elementor-icons-shared-0-css' href='https://getme.com.my/wp-content/plugins/elementor/assets/lib/font-awesome/css/fontawesome.min.css?ver=5.12.0' type='text/css' media='all'/>
        <link rel='stylesheet' id='elementor-icons-fa-solid-css' href='https://getme.com.my/wp-content/plugins/elementor/assets/lib/font-awesome/css/solid.min.css?ver=5.12.0' type='text/css' media='all'/>
        <link rel='stylesheet' id='elementor-icons-fa-brands-css' href='https://getme.com.my/wp-content/plugins/elementor/assets/lib/font-awesome/css/brands.min.css?ver=5.12.0' type='text/css' media='all'/>
        <!--[if IE]>
<script type="text/javascript" src="https://getme.com.my/wp-content/themes/astra/assets/js/minified/flexibility.min.js?ver=2.0.1" id="astra-flexibility-js"></script>
<script type="text/javascript" id="astra-flexibility-js-after">
/* <![CDATA[ */
flexibility(document.documentElement);
/* ]]> */
</script>
<![endif]-->
        <script type="text/javascript" src="https://getme.com.my/wp-includes/js/jquery/jquery.min.js?ver=3.7.1" id="jquery-core-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-includes/js/jquery/jquery-migrate.min.js?ver=3.4.1" id="jquery-migrate-js"></script>
        <link rel="https://api.w.org/" href="https://getme.com.my/wp-json/"/>
        <link rel="alternate" title="JSON" type="application/json" href="https://getme.com.my/wp-json/wp/v2/pages/246"/>
        <link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://getme.com.my/xmlrpc.php?rsd"/>
        <meta name="generator" content="WordPress 6.8.3"/>
        <link rel="canonical" href="https://getme.com.my/"/>
        <link rel='shortlink' href='https://getme.com.my/'/>
        <link rel="alternate" title="oEmbed (JSON)" type="application/json+oembed" href="https://getme.com.my/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fgetme.com.my%2F"/>
        <link rel="alternate" title="oEmbed (XML)" type="text/xml+oembed" href="https://getme.com.my/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fgetme.com.my%2F&#038;format=xml"/>
        <style id="mystickymenu" type="text/css">
            #mysticky-nav {
                width: 100%;
                position: static;
            }

            #mysticky-nav.wrapfixed {
                position: fixed;
                left: 0px;
                margin-top: 0px;
                z-index: 99990;
                -webkit-transition: 0.3s;
                -moz-transition: 0.3s;
                -o-transition: 0.3s;
                transition: 0.3s;
                -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";
                filter: alpha(opacity=90);
                opacity: 0.9;
                background-color: #f7f5e7;
            }

            #mysticky-nav .myfixed {
                margin: 0 auto;
                float: none;
                border: 0px;
                background: none;
                max-width: 100%;
            }
        </style>
        <style type="text/css"></style>
        <script id="wpcp_disable_selection" type="text/javascript">
            var image_save_msg = 'You are not allowed to save images!';
            var no_menu_msg = 'Context Menu disabled!';
            var smessage = "Content is protected !!";

            function disableEnterKey(e) {
                var elemtype = e.target.tagName;

                elemtype = elemtype.toUpperCase();

                if (elemtype == "TEXT" || elemtype == "TEXTAREA" || elemtype == "INPUT" || elemtype == "PASSWORD" || elemtype == "SELECT" || elemtype == "OPTION" || elemtype == "EMBED") {
                    elemtype = 'TEXT';
                }

                if (e.ctrlKey) {
                    var key;
                    if (window.event)
                        key = window.event.keyCode;
                        //IE
                    else
                        key = e.which;
                    //firefox (97)
                    //if (key != 17) alert(key);
                    if (elemtype != 'TEXT' && (key == 97 || key == 65 || key == 67 || key == 99 || key == 88 || key == 120 || key == 26 || key == 85 || key == 86 || key == 83 || key == 43 || key == 73)) {
                        if (wccp_free_iscontenteditable(e))
                            return true;
                        show_wpcp_message('You are not allowed to copy content or view source');
                        return false;
                    } else
                        return true;
                }
            }

            /*For contenteditable tags*/
            function wccp_free_iscontenteditable(e) {
                var e = e || window.event;
                // also there is no e.target property in IE. instead IE uses window.event.srcElement

                var target = e.target || e.srcElement;

                var elemtype = e.target.nodeName;

                elemtype = elemtype.toUpperCase();

                var iscontenteditable = "false";

                if (typeof target.getAttribute != "undefined")
                    iscontenteditable = target.getAttribute("contenteditable");
                // Return true or false as string

                var iscontenteditable2 = false;

                if (typeof target.isContentEditable != "undefined")
                    iscontenteditable2 = target.isContentEditable;
                // Return true or false as boolean

                if (target.parentElement.isContentEditable)
                    iscontenteditable2 = true;

                if (iscontenteditable == "true" || iscontenteditable2 == true) {
                    if (typeof target.style != "undefined")
                        target.style.cursor = "text";

                    return true;
                }
            }

            ////////////////////////////////////
            function disable_copy(e) {
                var e = e || window.event;
                // also there is no e.target property in IE. instead IE uses window.event.srcElement

                var elemtype = e.target.tagName;

                elemtype = elemtype.toUpperCase();

                if (elemtype == "TEXT" || elemtype == "TEXTAREA" || elemtype == "INPUT" || elemtype == "PASSWORD" || elemtype == "SELECT" || elemtype == "OPTION" || elemtype == "EMBED") {
                    elemtype = 'TEXT';
                }

                if (wccp_free_iscontenteditable(e))
                    return true;

                var isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);

                var checker_IMG = '';
                if (elemtype == "IMG" && checker_IMG == 'checked' && e.detail >= 2) {
                    show_wpcp_message(alertMsg_IMG);
                    return false;
                }
                if (elemtype != "TEXT") {
                    if (smessage !== "" && e.detail == 2)
                        show_wpcp_message(smessage);

                    if (isSafari)
                        return true;
                    else
                        return false;
                }
            }

            //////////////////////////////////////////
            function disable_copy_ie() {
                var e = e || window.event;
                var elemtype = window.event.srcElement.nodeName;
                elemtype = elemtype.toUpperCase();
                if (wccp_free_iscontenteditable(e))
                    return true;
                if (elemtype == "IMG") {
                    show_wpcp_message(alertMsg_IMG);
                    return false;
                }
                if (elemtype != "TEXT" && elemtype != "TEXTAREA" && elemtype != "INPUT" && elemtype != "PASSWORD" && elemtype != "SELECT" && elemtype != "OPTION" && elemtype != "EMBED") {
                    return false;
                }
            }
            function reEnable() {
                return true;
            }
            document.onkeydown = disableEnterKey;
            document.onselectstart = disable_copy_ie;
            if (navigator.userAgent.indexOf('MSIE') == -1) {
                document.onmousedown = disable_copy;
                document.onclick = reEnable;
            }
            function disableSelection(target) {
                //For IE This code will work
                if (typeof target.onselectstart != "undefined")
                    target.onselectstart = disable_copy_ie;

                    //For Firefox This code will work
                else if (typeof target.style.MozUserSelect != "undefined") {
                    target.style.MozUserSelect = "none";
                }
                //All other  (ie: Opera) This code will work
                else
                    target.onmousedown = function() {
                        return false
                    }
                target.style.cursor = "default";
            }
            //Calling the JS function directly just after body load
            window.onload = function() {
                disableSelection(document.body);
            }
            ;

            //////////////////special for safari Start////////////////
            var onlongtouch;
            var timer;
            var touchduration = 1000;
            //length of time we want the user to touch before we do something

            var elemtype = "";
            function touchstart(e) {
                var e = e || window.event;
                // also there is no e.target property in IE.
                // instead IE uses window.event.srcElement
                var target = e.target || e.srcElement;

                elemtype = window.event.srcElement.nodeName;

                elemtype = elemtype.toUpperCase();

                if (!wccp_pro_is_passive())
                    e.preventDefault();
                if (!timer) {
                    timer = setTimeout(onlongtouch, touchduration);
                }
            }

            function touchend() {
                //stops short touches from firing the event
                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }
                onlongtouch();
            }

            onlongtouch = function(e) {
                //this will clear the current selection if anything selected

                if (elemtype != "TEXT" && elemtype != "TEXTAREA" && elemtype != "INPUT" && elemtype != "PASSWORD" && elemtype != "SELECT" && elemtype != "EMBED" && elemtype != "OPTION") {
                    if (window.getSelection) {
                        if (window.getSelection().empty) {
                            // Chrome
                            window.getSelection().empty();
                        } else if (window.getSelection().removeAllRanges) {
                            // Firefox
                            window.getSelection().removeAllRanges();
                        }
                    } else if (document.selection) {
                        // IE?
                        document.selection.empty();
                    }
                    return false;
                }
            }
            ;

            document.addEventListener("DOMContentLoaded", function(event) {
                window.addEventListener("touchstart", touchstart, false);
                window.addEventListener("touchend", touchend, false);
            });

            function wccp_pro_is_passive() {

                var cold = false
                  , hike = function() {};

                try {
                    const object1 = {};
                    var aid = Object.defineProperty(object1, 'passive', {
                        get() {
                            cold = true
                        }
                    });
                    window.addEventListener('test', hike, aid);
                    window.removeEventListener('test', hike, aid);
                } catch (e) {}

                return cold;
            }
            /*special for safari End*/
        </script>
        <script id="wpcp_disable_Right_Click" type="text/javascript">
            document.ondragstart = function() {
                return false;
            }
            function nocontext(e) {
                return false;
            }
            document.oncontextmenu = nocontext;
        </script>
        <style>
            .unselectable {
                -moz-user-select: none;
                -webkit-user-select: none;
                cursor: default;
            }

            html {
                -webkit-touch-callout: none;
                -webkit-user-select: none;
                -khtml-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                -webkit-tap-highlight-color: rgba(0,0,0,0);
            }
        </style>
        <script id="wpcp_css_disable_selection" type="text/javascript">
            var e = document.getElementsByTagName('body')[0];
            if (e) {
                e.setAttribute('unselectable', on);
            }
        </script>
        <style type="text/css">
            .recentcomments a {
                display: inline !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        </style>
        <link rel="icon" href="https://getme.com.my/wp-content/uploads/2020/11/cropped-Hired2-1-32x32.png" sizes="32x32"/>
        <link rel="icon" href="https://getme.com.my/wp-content/uploads/2020/11/cropped-Hired2-1-192x192.png" sizes="192x192"/>
        <link rel="apple-touch-icon" href="https://getme.com.my/wp-content/uploads/2020/11/cropped-Hired2-1-180x180.png"/>
        <meta name="msapplication-TileImage" content="https://getme.com.my/wp-content/uploads/2020/11/cropped-Hired2-1-270x270.png"/>
    </head>
    <body itemtype='https://schema.org/WebPage' itemscope='itemscope' class="home wp-singular page-template-default page page-id-246 wp-custom-logo wp-theme-astra unselectable ast-desktop ast-page-builder-template ast-no-sidebar astra-2.0.1 ast-header-custom-item-inside ast-single-post ast-inherit-site-logo-transparent astra elementor-default elementor-kit-662 elementor-page elementor-page-246">
        <div class="hfeed site" id="page">
            <a class="skip-link screen-reader-text" href="#content">Skip to content</a>
            <header itemtype="https://schema.org/WPHeader" itemscope="itemscope" id="masthead" class="site-header header-main-layout-1 ast-primary-menu-enabled ast-logo-title-inline ast-hide-custom-menu-mobile ast-menu-toggle-icon ast-mobile-header-inline" role="banner">
                <div class="main-header-bar-wrap">
                    <div class="main-header-bar">
                        <div class="ast-container">
                            <div class="ast-flex main-header-container">
                                <div class="site-branding">
                                    <div class="ast-site-identity" itemscope="itemscope" itemtype="https://schema.org/Organization">
                                        <span class="site-logo-img">
                                            <a href="https://getme.com.my/" class="custom-logo-link" rel="home" aria-current="page">
                                                <img width="100" height="77" src="https://getme.com.my/wp-content/uploads/2020/11/cropped-Hired2-100x77.png" class="custom-logo" alt="GetMe" decoding="async" srcset="https://getme.com.my/wp-content/uploads/2020/11/cropped-Hired2-100x77.png 1x, https://getme.com.my/wp-content/uploads/2020/11/Hired2.png 2x" sizes="(max-width: 100px) 100vw, 100px"/>
                                            </a>
                                        </span>
                                        <div class="ast-site-title-wrap">
                                            <h1 class="site-title" itemprop="name">
                                                <a href="https://getme.com.my/" rel="home" itemprop="url">St4nger
					</a>
                                            </h1>
                                            <p class="site-description" itemprop="description">Web &amp; Development Services
				</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- .site-branding -->
                                <div class="ast-mobile-menu-buttons">
                                    <div class="ast-button-wrap">
                                        <button type="button" class="menu-toggle main-header-menu-toggle  ast-mobile-menu-buttons-fill " aria-controls='primary-menu' aria-expanded='false'>
                                            <span class="screen-reader-text">Main Menu</span>
                                            <span class="menu-toggle-icon"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="ast-main-header-bar-alignment">
                                    <div class="main-header-bar-navigation">
                                        <nav itemtype="https://schema.org/SiteNavigationElement" itemscope="itemscope" id="site-navigation" class="ast-flex-grow-1 navigation-accessibility" aria-label="Site Navigation">
                                            <div class="main-navigation">
                                                <ul id="primary-menu" class="main-header-menu ast-nav-menu ast-flex ast-justify-content-flex-end  submenu-with-border">
                                                    <li id="menu-item-618" class="menu-item menu-item-type-custom menu-item-object-custom current-menu-item current_page_item menu-item-home menu-item-618">
                                                        <a href="https://getme.com.my/" aria-current="page">Home</a>
                                                    </li>
                                                    <li id="menu-item-615" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-615">
                                                        <a href="#about">About</a>
                                                    </li>
                                                    <li id="menu-item-616" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-616">
                                                        <a href="#services">Services</a>
                                                    </li>
                                                    <li id="menu-item-617" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-617">
                                                        <a href="#contact">Contact</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                            <!-- Main Header Container -->
                        </div>
                        <!-- ast-row -->
                    </div>
                    <!-- Main Header Bar -->
                </div>
                <!-- Main Header Bar Wrap -->
            </header>
            <!-- #masthead -->
            <div id="content" class="site-content">
                <div class="ast-container">
                    <div id="primary" class="content-area primary">
                        <main id="main" class="site-main">
                            <article class="post-246 page type-page status-publish ast-article-single" itemtype="https://schema.org/CreativeWork" itemscope="itemscope" id="post-246">
                                <header class="entry-header ast-header-without-markup"></header>
                                <!-- .entry-header -->
                                <div class="entry-content clear" itemprop="text">
                                    <div data-elementor-type="wp-page" data-elementor-id="246" class="elementor elementor-246" data-elementor-settings="[]">
                                        <div class="elementor-inner">
                                            <div class="elementor-section-wrap">
                                                <section class="elementor-element elementor-element-812aa30 elementor-section-content-middle elementor-section-full_width elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="812aa30" data-element_type="section" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
                                                    <div class="elementor-background-overlay"></div>
                                                    <div class="elementor-container elementor-column-gap-extended">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-aac1b78 elementor-column elementor-col-100 elementor-top-column" data-id="aac1b78" data-element_type="column" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-66f380d elementor-widget elementor-widget-spacer" data-id="66f380d" data-element_type="widget" data-widget_type="spacer.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-spacer">
                                                                                    <div class="elementor-spacer-inner"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-74aef9a4 elementor-widget elementor-widget-heading" data-id="74aef9a4" data-element_type="widget" data-widget_type="heading.default">
                                                                            <div class="elementor-widget-container">
                                                                                <h1 class="elementor-heading-title elementor-size-default">Let us help you achieve your goals to be more productive in your business.</h1>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-4b41ce4 elementor-widget elementor-widget-text-editor" data-id="4b41ce4" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <h3 class="elementor-heading-title elementor-size-default elementor-inline-editing pen" contenteditable="true" data-elementor-setting-key="title" data-pen-placeholder="Type Here...">
                                                                                        <strong>
                                                                                            <span style="color: #ffffff;">We Offers great services just for you.</span>
                                                                                        </strong>
                                                                                    </h3>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <section class="elementor-element elementor-element-9173e8e elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="9173e8e" data-element_type="section">
                                                    <div class="elementor-container elementor-column-gap-default">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-eb3862f elementor-column elementor-col-100 elementor-top-column" data-id="eb3862f" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-d35cff8 elementor-widget elementor-widget-menu-anchor" data-id="d35cff8" data-element_type="widget" data-widget_type="menu-anchor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div id="about" class="elementor-menu-anchor"></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-beeb938 elementor-widget elementor-widget-heading" data-id="beeb938" data-element_type="widget" data-widget_type="heading.default">
                                                                            <div class="elementor-widget-container">
                                                                                <h2 class="elementor-heading-title elementor-size-default">About Us</h2>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-aafd411 elementor-widget elementor-widget-divider" data-id="aafd411" data-element_type="widget" data-widget_type="divider.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-divider">
                                                                                    <span class="elementor-divider-separator"></span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <section class="elementor-element elementor-element-5859752 elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="5859752" data-element_type="section">
                                                    <div class="elementor-container elementor-column-gap-default">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-f92609a elementor-column elementor-col-33 elementor-top-column" data-id="f92609a" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-27f358e elementor-widget elementor-widget-text-editor" data-id="27f358e" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <p>A platform to bring together existing business segments.</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-aed382c elementor-widget elementor-widget-text-editor" data-id="aed382c" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <p>Focus on the strength of community support for business development.</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="elementor-element elementor-element-036c79c elementor-column elementor-col-33 elementor-top-column" data-id="036c79c" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-6ccb149 elementor-widget elementor-widget-text-editor" data-id="6ccb149" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <p>Work exclusively with business owners and provide existing business marketing support.</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-4f35717 elementor-widget elementor-widget-text-editor" data-id="4f35717" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <p>Take a fresh breath of the existing market to be more aggressive in marketing.</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="elementor-element elementor-element-e0e270e elementor-column elementor-col-33 elementor-top-column" data-id="e0e270e" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-cf3ec22 elementor-widget elementor-widget-text-editor" data-id="cf3ec22" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <p>An Exclusive Of Business Networks By Using This Medium To Upgrade Business From Conventional To The Digital Market</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <section class="elementor-element elementor-element-177b850 elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="177b850" data-element_type="section">
                                                    <div class="elementor-container elementor-column-gap-no">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-7224653 elementor-column elementor-col-100 elementor-top-column" data-id="7224653" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <section class="elementor-element elementor-element-bdcae8f elementor-section-full_width elementor-section-height-min-height elementor-section-height-default elementor-section elementor-inner-section" data-id="bdcae8f" data-element_type="section" data-settings="{&quot;background_background&quot;:&quot;classic&quot;,&quot;shape_divider_top&quot;:&quot;drops&quot;}">
                                                                            <div class="elementor-shape elementor-shape-top" data-negative="false">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" height="100%" viewBox="0 0 283.5 27.8" preserveAspectRatio="xMidYMax slice">
                                                                                    <path class="elementor-shape-fill" d="M0 0v1.4c.6.7 1.1 1.4 1.4 2 2 3.8 2.2 6.6 1.8 10.8-.3 3.3-2.4 9.4 0 12.3 1.7 2 3.7 1.4 4.6-.9 1.4-3.8-.7-8.2-.6-12 .1-3.7 3.2-5.5 6.9-4.9 4 .6 4.8 4 4.9 7.4.1 1.8-1.1 7 0 8.5.6.8 1.6 1.2 2.4.5 1.4-1.1.1-5.4.1-6.9.1-3.7.3-8.6 4.1-10.5 5-2.5 6.2 1.6 5.4 5.6-.4 1.7-1 9.2 2.9 6.3 1.5-1.1.7-3.5.5-4.9-.4-2.4-.4-4.3 1-6.5.9-1.4 2.4-3.1 4.2-3 2.4.1 2.7 2.2 4 3.7 1.5 1.8 1.8 2.2 3 .1 1.1-1.9 1.2-2.8 3.6-3.3 1.3-.3 4.8-1.4 5.9-.5 1.5 1.1.6 2.8.4 4.3-.2 1.1-.6 4 1.8 3.4 1.7-.4-.3-4.1.6-5.6 1.3-2.2 5.8-1.4 7 .5 1.3 2.1.5 5.8.1 8.1s-1.2 5-.6 7.4c1.3 5.1 4.4.9 4.3-2.4-.1-4.4-2-8.8-.5-13 .9-2.4 4.6-6.6 7.7-4.5 2.7 1.8.5 7.8.2 10.3-.2 1.7-.8 4.6.2 6.2.9 1.4 2 1.5 2.6-.3.5-1.5-.9-4.5-1-6.1-.2-1.7-.4-3.7.2-5.4 1.8-5.6 3.5 2.4 6.3.6 1.4-.9 4.3-9.4 6.1-3.1.6 2.2-1.3 7.8.7 8.9 4.2 2.3 1.5-7.1 2.2-8 3.1-4 4.7 3.8 6.1 4.1 3.1.7 2.8-7.9 8.1-4.5 1.7 1.1 2.9 3.3 3.2 5.2.4 2.2-1 4.5-.6 6.6 1 4.3 4.4 1.5 4.4-1.7 0-2.7-3-8.3 1.4-9.1 4.4-.9 7.3 3.5 7.8 6.9.3 2-1.5 10.9 1.3 11.3 4.1.6-3.2-15.7 4.8-15.8 4.7-.1 2.8 4.1 3.9 6.6 1 2.4 2.1 1 2.3-.8.3-1.9-.9-3.2 1.3-4.3 5.9-2.9 5.9 5.4 5.5 8.5-.3 2-1.7 8.4 2 8.1 6.9-.5-2.8-16.9 4.8-18.7 4.7-1.2 6.1 3.6 6.3 7.1.1 1.7-1.2 8.1.6 9.1 3.5 2 1.9-7 2-8.4.2-4 1.2-9.6 6.4-9.8 4.7-.2 3.2 4.6 2.7 7.5-.4 2.2 1.3 8.6 3.8 4.4 1.1-1.9-.3-4.1-.3-6 0-1.7.4-3.2 1.3-4.6 1-1.6 2.9-3.5 5.1-2.9 2.5.6 2.3 4.1 4.1 4.9 1.9.8 1.6-.9 2.3-2.1 1.2-2.1 2.1-2.1 4.4-2.4 1.4-.2 3.6-1.5 4.9-.5 2.3 1.7-.7 4.4.1 6.5.6 1.5 2.1 1.7 2.8.3.7-1.4-1.1-3.4-.3-4.8 1.4-2.5 6.2-1.2 7.2 1 2.3 4.8-3.3 12-.2 16.3 3 4.1 3.9-2.8 3.8-4.8-.4-4.3-2.1-8.9 0-13.1 1.3-2.5 5.9-5.7 7.9-2.4 2 3.2-1.3 9.8-.8 13.4.5 4.4 3.5 3.3 2.7-.8-.4-1.9-2.4-10 .6-11.1 3.7-1.4 2.8 7.2 6.5.4 2.2-4.1 4.9-3.1 5.2 1.2.1 1.5-.6 3.1-.4 4.6.2 1.9 1.8 3.7 3.3 1.3 1-1.6-2.6-10.4 2.9-7.3 2.6 1.5 1.6 6.5 4.8 2.7 1.3-1.5 1.7-3.6 4-3.7 2.2-.1 4 2.3 4.8 4.1 1.3 2.9-1.5 8.4.9 10.3 4.2 3.3 3-5.5 2.7-6.9-.6-3.9 1-7.2 5.5-5 4.1 2.1 4.3 7.7 4.1 11.6 0 .8-.6 9.5 2.5 5.2 1.2-1.7-.1-7.7.1-9.6.3-2.9 1.2-5.5 4.3-6.2 4.5-1 7.7 1.5 7.4 5.8-.2 3.5-1.8 7.7-.5 11.1 1 2.7 3.6 2.8 5 .2 1.6-3.1 0-8.3-.4-11.6-.4-4.2-.2-7 1.8-10.8 0 0-.1.1-.1.2-.2.4-.3.7-.4.8v.1c-.1.2-.1.2 0 0v-.1l.4-.8c0-.1.1-.1.1-.2.2-.4.5-.8.8-1.2V0H0zM282.7 3.4z"/>
                                                                                </svg>
                                                                            </div>
                                                                            <div class="elementor-container elementor-column-gap-no">
                                                                                <div class="elementor-row">
                                                                                    <div class="elementor-element elementor-element-82cde2e elementor-column elementor-col-100 elementor-inner-column" data-id="82cde2e" data-element_type="column" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
                                                                                        <div class="elementor-column-wrap  elementor-element-populated">
                                                                                            <div class="elementor-widget-wrap">
                                                                                                <div class="elementor-element elementor-element-d89fc1c elementor-widget elementor-widget-heading" data-id="d89fc1c" data-element_type="widget" data-widget_type="heading.default">
                                                                                                    <div class="elementor-widget-container">
                                                                                                        <h3 class="elementor-heading-title elementor-size-large">Why GET ME ?</h3>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="elementor-element elementor-element-d551131 elementor-invisible elementor-widget elementor-widget-text-editor" data-id="d551131" data-element_type="widget" data-settings="{&quot;_animation&quot;:&quot;fadeInDown&quot;}" data-widget_type="text-editor.default">
                                                                                                    <div class="elementor-widget-container">
                                                                                                        <div class="elementor-text-editor elementor-clearfix">
                                                                                                            <p style="text-align: center;">Impact engagement</p>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="elementor-element elementor-element-3a8218c elementor-invisible elementor-widget elementor-widget-text-editor" data-id="3a8218c" data-element_type="widget" data-settings="{&quot;_animation&quot;:&quot;fadeInDown&quot;}" data-widget_type="text-editor.default">
                                                                                                    <div class="elementor-widget-container">
                                                                                                        <div class="elementor-text-editor elementor-clearfix">
                                                                                                            <p style="text-align: center;">Get product to the market</p>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="elementor-element elementor-element-422ca77 elementor-invisible elementor-widget elementor-widget-text-editor" data-id="422ca77" data-element_type="widget" data-settings="{&quot;_animation&quot;:&quot;fadeInDown&quot;}" data-widget_type="text-editor.default">
                                                                                                    <div class="elementor-widget-container">
                                                                                                        <div class="elementor-text-editor elementor-clearfix">
                                                                                                            <p style="text-align: center;">Share ideas and knowledge</p>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="elementor-element elementor-element-cd2b076 elementor-invisible elementor-widget elementor-widget-text-editor" data-id="cd2b076" data-element_type="widget" data-settings="{&quot;_animation&quot;:&quot;fadeInDown&quot;}" data-widget_type="text-editor.default">
                                                                                                    <div class="elementor-widget-container">
                                                                                                        <div class="elementor-text-editor elementor-clearfix">
                                                                                                            <p style="text-align: center;">Opportunities to work with different levels of business partners</p>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </section>
                                                                        <div class="elementor-element elementor-element-939ef4f elementor-widget elementor-widget-spacer" data-id="939ef4f" data-element_type="widget" data-widget_type="spacer.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-spacer">
                                                                                    <div class="elementor-spacer-inner"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <section class="elementor-element elementor-element-e736e27 elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="e736e27" data-element_type="section">
                                                    <div class="elementor-container elementor-column-gap-default">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-4737cd6 elementor-column elementor-col-100 elementor-top-column" data-id="4737cd6" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-d875660 elementor-widget elementor-widget-menu-anchor" data-id="d875660" data-element_type="widget" data-widget_type="menu-anchor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div id="services" class="elementor-menu-anchor"></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-9dec760 elementor-widget elementor-widget-heading" data-id="9dec760" data-element_type="widget" data-widget_type="heading.default">
                                                                            <div class="elementor-widget-container">
                                                                                <h2 class="elementor-heading-title elementor-size-xl">Get ME - Business Network</h2>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-96bc43e elementor-widget elementor-widget-divider" data-id="96bc43e" data-element_type="widget" data-widget_type="divider.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-divider">
                                                                                    <span class="elementor-divider-separator"></span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <section class="elementor-element elementor-element-e4eb3fe elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="e4eb3fe" data-element_type="section">
                                                    <div class="elementor-container elementor-column-gap-extended">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-4d27244 animated-slow elementor-invisible elementor-column elementor-col-25 elementor-top-column" data-id="4d27244" data-element_type="column" data-settings="{&quot;animation&quot;:&quot;fadeIn&quot;}">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-26cfb8c elementor-invisible elementor-widget elementor-widget-image" data-id="26cfb8c" data-element_type="widget" data-settings="{&quot;_animation&quot;:&quot;none&quot;}" data-widget_type="image.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-image">
                                                                                    <figure class="wp-caption">
                                                                                        <img fetchpriority="high" decoding="async" width="1024" height="725" src="https://getme.com.my/wp-content/uploads/2020/11/21207-1024x725.jpg" class="attachment-large size-large" alt="" srcset="https://getme.com.my/wp-content/uploads/2020/11/21207-1024x725.jpg 1024w, https://getme.com.my/wp-content/uploads/2020/11/21207-300x212.jpg 300w, https://getme.com.my/wp-content/uploads/2020/11/21207-768x544.jpg 768w, https://getme.com.my/wp-content/uploads/2020/11/21207-1536x1087.jpg 1536w, https://getme.com.my/wp-content/uploads/2020/11/21207-2048x1450.jpg 2048w" sizes="(max-width: 1024px) 100vw, 1024px"/>
                                                                                        <figcaption class="widget-image-caption wp-caption-text">
                                                                                            GetMe<br>Hired
                                                                                        </figcaption>
                                                                                    </figure>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-437f7dd elementor-align-center elementor-widget elementor-widget-button" data-id="437f7dd" data-element_type="widget" data-widget_type="button.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-button-wrapper">
                                                                                    <a href="https://getmehired.com.my/" class="elementor-button-link elementor-button elementor-size-xs" role="button">
                                                                                        <span class="elementor-button-content-wrapper">
                                                                                            <span class="elementor-button-text">Click here</span>
                                                                                        </span>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-5a15b60 elementor-widget elementor-widget-text-editor" data-id="5a15b60" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <h5 style="text-align: center;">We provide solutions that can save your time and start your career with simple ways and help you stand out from the crowd and maximize your chances of getting through to the interview stage.</h5>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="elementor-element elementor-element-f76d531 animated-slow elementor-invisible elementor-column elementor-col-25 elementor-top-column" data-id="f76d531" data-element_type="column" data-settings="{&quot;animation&quot;:&quot;fadeIn&quot;}">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-f594eb8 elementor-invisible elementor-widget elementor-widget-image" data-id="f594eb8" data-element_type="widget" data-settings="{&quot;_animation&quot;:&quot;none&quot;}" data-widget_type="image.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-image">
                                                                                    <figure class="wp-caption">
                                                                                        <img decoding="async" width="1024" height="683" src="https://getme.com.my/wp-content/uploads/2020/11/19198948-1024x683.jpg" class="attachment-large size-large" alt="" srcset="https://getme.com.my/wp-content/uploads/2020/11/19198948-1024x683.jpg 1024w, https://getme.com.my/wp-content/uploads/2020/11/19198948-300x200.jpg 300w, https://getme.com.my/wp-content/uploads/2020/11/19198948-768x512.jpg 768w, https://getme.com.my/wp-content/uploads/2020/11/19198948-1536x1024.jpg 1536w, https://getme.com.my/wp-content/uploads/2020/11/19198948-2048x1366.jpg 2048w" sizes="(max-width: 1024px) 100vw, 1024px"/>
                                                                                        <figcaption class="widget-image-caption wp-caption-text">
                                                                                            Web <br>Development
                                                                                        </figcaption>
                                                                                    </figure>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-4640d21 elementor-align-center elementor-widget elementor-widget-button" data-id="4640d21" data-element_type="widget" data-widget_type="button.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-button-wrapper">
                                                                                    <a href="https://nisolution.com.my/web/" class="elementor-button-link elementor-button elementor-size-xs" role="button">
                                                                                        <span class="elementor-button-content-wrapper">
                                                                                            <span class="elementor-button-text">Click here</span>
                                                                                        </span>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-bb2ef97 elementor-widget elementor-widget-text-editor" data-id="bb2ef97" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <h5 style="text-align: center;">Bring your organisation to the digital world. We provide website development, design and maintenance. If you have a website but no time for maintenance, don &#8217;t worry we help you.</h5>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="elementor-element elementor-element-c46f98d animated-slow elementor-invisible elementor-column elementor-col-25 elementor-top-column" data-id="c46f98d" data-element_type="column" data-settings="{&quot;animation&quot;:&quot;fadeIn&quot;}">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-8093048 elementor-invisible elementor-widget elementor-widget-image" data-id="8093048" data-element_type="widget" data-settings="{&quot;_animation&quot;:&quot;none&quot;}" data-widget_type="image.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-image">
                                                                                    <figure class="wp-caption">
                                                                                        <img decoding="async" width="1024" height="640" src="https://getme.com.my/wp-content/uploads/2020/11/10141-1024x640.jpg" class="attachment-large size-large" alt="" srcset="https://getme.com.my/wp-content/uploads/2020/11/10141-1024x640.jpg 1024w, https://getme.com.my/wp-content/uploads/2020/11/10141-300x188.jpg 300w, https://getme.com.my/wp-content/uploads/2020/11/10141-768x480.jpg 768w, https://getme.com.my/wp-content/uploads/2020/11/10141-1536x960.jpg 1536w, https://getme.com.my/wp-content/uploads/2020/11/10141-2048x1280.jpg 2048w" sizes="(max-width: 1024px) 100vw, 1024px"/>
                                                                                        <figcaption class="widget-image-caption wp-caption-text">
                                                                                            Photography<br>Videography
                                                                                        </figcaption>
                                                                                    </figure>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-54a3dc1 elementor-align-center elementor-widget elementor-widget-button" data-id="54a3dc1" data-element_type="widget" data-widget_type="button.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-button-wrapper">
                                                                                    <a href="http://localpv.getme.com.my/" class="elementor-button-link elementor-button elementor-size-xs" role="button">
                                                                                        <span class="elementor-button-content-wrapper">
                                                                                            <span class="elementor-button-text">Click here</span>
                                                                                        </span>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-dcac533 elementor-widget elementor-widget-text-editor" data-id="dcac533" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <h5 style="text-align: center;">Meet our specialist photographer and videographer (PV). Easy access to get professional PV. Set your place, time, date and book it!</h5>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="elementor-element elementor-element-81af2fa animated-slow elementor-invisible elementor-column elementor-col-25 elementor-top-column" data-id="81af2fa" data-element_type="column" data-settings="{&quot;animation&quot;:&quot;fadeIn&quot;}">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-8b07d09 elementor-invisible elementor-widget elementor-widget-image" data-id="8b07d09" data-element_type="widget" data-settings="{&quot;_animation&quot;:&quot;none&quot;}" data-widget_type="image.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-image">
                                                                                    <figure class="wp-caption">
                                                                                        <img loading="lazy" decoding="async" width="1024" height="683" src="https://getme.com.my/wp-content/uploads/2020/11/19362653-1024x683.jpg" class="attachment-large size-large" alt="" srcset="https://getme.com.my/wp-content/uploads/2020/11/19362653-1024x683.jpg 1024w, https://getme.com.my/wp-content/uploads/2020/11/19362653-300x200.jpg 300w, https://getme.com.my/wp-content/uploads/2020/11/19362653-768x512.jpg 768w, https://getme.com.my/wp-content/uploads/2020/11/19362653-1536x1024.jpg 1536w, https://getme.com.my/wp-content/uploads/2020/11/19362653-2048x1365.jpg 2048w" sizes="auto, (max-width: 1024px) 100vw, 1024px"/>
                                                                                        <figcaption class="widget-image-caption wp-caption-text">
                                                                                            Computer <br>Services Specialist
                                                                                        </figcaption>
                                                                                    </figure>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-346c569 elementor-align-center elementor-widget elementor-widget-button" data-id="346c569" data-element_type="widget" data-widget_type="button.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-button-wrapper">
                                                                                    <a href="https://pc.getme.com.my/" class="elementor-button-link elementor-button elementor-size-xs" role="button">
                                                                                        <span class="elementor-button-content-wrapper">
                                                                                            <span class="elementor-button-text">Click here</span>
                                                                                        </span>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-f6b3cdf elementor-widget elementor-widget-text-editor" data-id="f6b3cdf" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <h5 style="text-align: center;">Repair &amp;upgrade computer or laptop, backup &amp;restore your data, take care of those pesky updates, save you from the blue/ white/black screen of death.</h5>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <section class="elementor-element elementor-element-35b3090 elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="35b3090" data-element_type="section">
                                                    <div class="elementor-container elementor-column-gap-default">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-ffeae9d elementor-column elementor-col-100 elementor-top-column" data-id="ffeae9d" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-7679877 elementor-widget elementor-widget-spacer" data-id="7679877" data-element_type="widget" data-widget_type="spacer.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-spacer">
                                                                                    <div class="elementor-spacer-inner"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <section class="elementor-element elementor-element-aadf0aa elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="aadf0aa" data-element_type="section" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
                                                    <div class="elementor-container elementor-column-gap-default">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-5bcfd77 elementor-column elementor-col-100 elementor-top-column" data-id="5bcfd77" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-9094e97 elementor-widget elementor-widget-spacer" data-id="9094e97" data-element_type="widget" data-widget_type="spacer.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-spacer">
                                                                                    <div class="elementor-spacer-inner"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-ae28539 elementor-widget elementor-widget-menu-anchor" data-id="ae28539" data-element_type="widget" data-widget_type="menu-anchor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div id="contact" class="elementor-menu-anchor"></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-6f2c33d elementor-widget elementor-widget-heading" data-id="6f2c33d" data-element_type="widget" data-widget_type="heading.default">
                                                                            <div class="elementor-widget-container">
                                                                                <h2 class="elementor-heading-title elementor-size-xl">Contact</h2>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-413cb26 elementor-widget elementor-widget-divider" data-id="413cb26" data-element_type="widget" data-widget_type="divider.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-divider">
                                                                                    <span class="elementor-divider-separator"></span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-e5b9af9 elementor-widget elementor-widget-heading" data-id="e5b9af9" data-element_type="widget" data-widget_type="heading.default">
                                                                            <div class="elementor-widget-container">
                                                                                <h3 class="elementor-heading-title elementor-size-large">Get in touch with us today</h3>
                                                                            </div>
                                                                        </div>
                                                                        <div class="elementor-element elementor-element-d68a16e elementor-widget elementor-widget-text-editor" data-id="d68a16e" data-element_type="widget" data-widget_type="text-editor.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-text-editor elementor-clearfix">
                                                                                    <p>Have question or required more information ? Get in touch with us through information below</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <section class="elementor-element elementor-element-fb40759 elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-inner-section" data-id="fb40759" data-element_type="section">
                                                                            <div class="elementor-container elementor-column-gap-default">
                                                                                <div class="elementor-row">
                                                                                    <div class="elementor-element elementor-element-56761cf elementor-column elementor-col-50 elementor-inner-column" data-id="56761cf" data-element_type="column">
                                                                                        <div class="elementor-column-wrap  elementor-element-populated">
                                                                                            <div class="elementor-widget-wrap">
                                                                                                <div class="elementor-element elementor-element-beb73a2 elementor-widget elementor-widget-google_maps" data-id="beb73a2" data-element_type="widget" data-widget_type="google_maps.default">
                                                                                                    <div class="elementor-widget-container">
                                                                                                        <div class="elementor-custom-embed">
                                                                                                            <iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=GETME%20TECHNOLOGY%20PLT&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near" aria-label="GETME TECHNOLOGY PLT"></iframe>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="elementor-element elementor-element-a1ff7e9 elementor-column elementor-col-50 elementor-inner-column" data-id="a1ff7e9" data-element_type="column">
                                                                                        <div class="elementor-column-wrap  elementor-element-populated">
                                                                                            <div class="elementor-widget-wrap">
                                                                                                <div class="elementor-element elementor-element-aff1036 elementor-icon-list--layout-traditional elementor-widget elementor-widget-icon-list" data-id="aff1036" data-element_type="widget" data-widget_type="icon-list.default">
                                                                                                    <div class="elementor-widget-container">
                                                                                                        <ul class="elementor-icon-list-items">
                                                                                                            <li class="elementor-icon-list-item">
                                                                                                                <span class="elementor-icon-list-icon">
                                                                                                                    <i aria-hidden="true" class="fas fa-map-marker-alt"></i>
                                                                                                                </span>
                                                                                                                <span class="elementor-icon-list-text">No. 208 Industry Center, Technovation Park, UTM Skudai, 81300 Johor Bahru, Johor</span>
                                                                                                            </li>
                                                                                                            <li class="elementor-icon-list-item">
                                                                                                                <span class="elementor-icon-list-icon">
                                                                                                                    <i aria-hidden="true" class="fas fa-phone"></i>
                                                                                                                </span>
                                                                                                                <span class="elementor-icon-list-text">+60 19 727 8389</span>
                                                                                                            </li>
                                                                                                            <li class="elementor-icon-list-item">
                                                                                                                <span class="elementor-icon-list-icon">
                                                                                                                    <i aria-hidden="true" class="fas fa-envelope"></i>
                                                                                                                </span>
                                                                                                                <span class="elementor-icon-list-text">support@getme.com.my</span>
                                                                                                            </li>
                                                                                                        </ul>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="elementor-element elementor-element-84a8091 elementor-shape-circle elementor-widget elementor-widget-social-icons" data-id="84a8091" data-element_type="widget" data-widget_type="social-icons.default">
                                                                                                    <div class="elementor-widget-container">
                                                                                                        <div class="elementor-social-icons-wrapper">
                                                                                                            <a class="elementor-icon elementor-social-icon elementor-social-icon-facebook-f elementor-animation-grow elementor-repeater-item-1fc929f" target="_blank">
                                                                                                                <span class="elementor-screen-only">Facebook-f</span>
                                                                                                                <i class="fab fa-facebook-f"></i>
                                                                                                            </a>
                                                                                                            <a class="elementor-icon elementor-social-icon elementor-social-icon-twitter elementor-animation-grow elementor-repeater-item-07b1945" target="_blank">
                                                                                                                <span class="elementor-screen-only">Twitter</span>
                                                                                                                <i class="fab fa-twitter"></i>
                                                                                                            </a>
                                                                                                            <a class="elementor-icon elementor-social-icon elementor-social-icon-google-plus-g elementor-animation-grow elementor-repeater-item-deadf5e" target="_blank">
                                                                                                                <span class="elementor-screen-only">Google-plus-g</span>
                                                                                                                <i class="fab fa-google-plus-g"></i>
                                                                                                            </a>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </section>
                                                                        <div class="elementor-element elementor-element-1397b23 elementor-widget elementor-widget-spacer" data-id="1397b23" data-element_type="widget" data-widget_type="spacer.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-spacer">
                                                                                    <div class="elementor-spacer-inner"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <section class="elementor-element elementor-element-7fc63abd elementor-section-content-middle elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="7fc63abd" data-element_type="section">
                                                    <div class="elementor-container elementor-column-gap-no">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-6884000a elementor-column elementor-col-100 elementor-top-column" data-id="6884000a" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-5c53d1e2 elementor-widget elementor-widget-heading" data-id="5c53d1e2" data-element_type="widget" data-widget_type="heading.default">
                                                                            <div class="elementor-widget-container">
                                                                                <h4 class="elementor-heading-title elementor-size-default">Our Collaboration</h4>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <section class="elementor-element elementor-element-1a3ee52 elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="1a3ee52" data-element_type="section">
                                                    <div class="elementor-container elementor-column-gap-default">
                                                        <div class="elementor-row">
                                                            <div class="elementor-element elementor-element-c6ef52a elementor-column elementor-col-100 elementor-top-column" data-id="c6ef52a" data-element_type="column">
                                                                <div class="elementor-column-wrap  elementor-element-populated">
                                                                    <div class="elementor-widget-wrap">
                                                                        <div class="elementor-element elementor-element-766b453 elementor-arrows-position-inside elementor-pagination-position-outside elementor-widget elementor-widget-image-carousel" data-id="766b453" data-element_type="widget" data-settings="{&quot;slides_to_show&quot;:&quot;2&quot;,&quot;navigation&quot;:&quot;both&quot;,&quot;autoplay&quot;:&quot;yes&quot;,&quot;pause_on_hover&quot;:&quot;yes&quot;,&quot;pause_on_interaction&quot;:&quot;yes&quot;,&quot;autoplay_speed&quot;:5000,&quot;infinite&quot;:&quot;yes&quot;,&quot;speed&quot;:500,&quot;direction&quot;:&quot;ltr&quot;}" data-widget_type="image-carousel.default">
                                                                            <div class="elementor-widget-container">
                                                                                <div class="elementor-image-carousel-wrapper swiper-container" dir="ltr">
                                                                                    <div class="elementor-image-carousel swiper-wrapper">
                                                                                        <div class="swiper-slide">
                                                                                            <figure class="swiper-slide-inner">
                                                                                                <img decoding="async" class="swiper-slide-image" src="https://getme.com.my/wp-content/uploads/2020/11/LogoNis2019.png" alt="NI SOLUTION"/>
                                                                                                <figcaption class="elementor-image-carousel-caption">NI SOLUTION</figcaption>
                                                                                            </figure>
                                                                                        </div>
                                                                                        <div class="swiper-slide">
                                                                                            <figure class="swiper-slide-inner">
                                                                                                <img decoding="async" class="swiper-slide-image" src="https://getme.com.my/wp-content/uploads/2020/11/Logo-Micro-Semiconductor-2018-150x99.png" alt="MICRO SEMICONDUCTOR SDN BHD"/>
                                                                                                <figcaption class="elementor-image-carousel-caption">MICRO SEMICONDUCTOR SDN BHD</figcaption>
                                                                                            </figure>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="swiper-pagination"></div>
                                                                                    <div class="elementor-swiper-button elementor-swiper-button-prev">
                                                                                        <i class="eicon-chevron-left" aria-hidden="true"></i>
                                                                                        <span class="elementor-screen-only">Previous</span>
                                                                                    </div>
                                                                                    <div class="elementor-swiper-button elementor-swiper-button-next">
                                                                                        <i class="eicon-chevron-right" aria-hidden="true"></i>
                                                                                        <span class="elementor-screen-only">Next</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- .entry-content .clear -->
                            </article>
                            <!-- #post-## -->
                        </main>
                        <!-- #main -->
                    </div>
                    <!-- #primary -->
                </div>
                <!-- ast-container -->
            </div>
            <!-- #content -->
            <footer itemtype="https://schema.org/WPFooter" itemscope="itemscope" id="colophon" class="site-footer" role="contentinfo">
                <div class="ast-small-footer footer-sml-layout-2">
                    <div class="ast-footer-overlay">
                        <div class="ast-container">
                            <div class="ast-small-footer-wrap">
                                <div class="ast-row ast-flex">
                                    <div class="ast-small-footer-section ast-small-footer-section-1 ast-small-footer-section-equally ast-col-md-6 ast-col-xs-12">
                                        Copyright © 2025 <span class="ast-footer-site-title">GetMe</span>
                                        | <a href="#">Credits</a>
                                    </div>
                                    <div class="ast-small-footer-section ast-small-footer-section-2 ast-small-footer-section-equally ast-col-md-6 ast-col-xs-12">
                                        Powered by <span class="ast-footer-site-title">GetMe</span>
                                    </div>
                                </div>
                                <!-- .ast-row.ast-flex -->
                            </div>
                            <!-- .ast-small-footer-wrap -->
                        </div>
                        <!-- .ast-container -->
                    </div>
                    <!-- .ast-footer-overlay -->
                </div>
                <!-- .ast-small-footer-->
            </footer>
            <!-- #colophon -->
        </div>
        <!-- #page -->
        <script type="speculationrules">
            {
                "prefetch": [
                    {
                        "source": "document",
                        "where": {
                            "and": [
                                {
                                    "href_matches": "\/*"
                                },
                                {
                                    "not": {
                                        "href_matches": [
                                            "\/wp-*.php",
                                            "\/wp-admin\/*",
                                            "\/wp-content\/uploads\/*",
                                            "\/wp-content\/*",
                                            "\/wp-content\/plugins\/*",
                                            "\/wp-content\/themes\/astra\/*",
                                            "\/*\\?(.+)"
                                        ]
                                    }
                                },
                                {
                                    "not": {
                                        "selector_matches": "a[rel~=\"nofollow\"]"
                                    }
                                },
                                {
                                    "not": {
                                        "selector_matches": ".no-prefetch, .no-prefetch a"
                                    }
                                }
                            ]
                        },
                        "eagerness": "conservative"
                    }
                ]
            }</script>
        <div id="wpcp-error-message" class="msgmsg-box-wpcp hideme">
            <span>error: </span>
            Content is protected !!
        </div>
        <script>
            var timeout_result;
            function show_wpcp_message(smessage) {
                if (smessage !== "") {
                    var smessage_text = '<span>Alert: </span>' + smessage;
                    document.getElementById("wpcp-error-message").innerHTML = smessage_text;
                    document.getElementById("wpcp-error-message").className = "msgmsg-box-wpcp warning-wpcp showme";
                    clearTimeout(timeout_result);
                    timeout_result = setTimeout(hide_message, 3000);
                }
            }
            function hide_message() {
                document.getElementById("wpcp-error-message").className = "msgmsg-box-wpcp warning-wpcp hideme";
            }
        </script>
        <style>
            @media print {
                body * {
                    display: none !important;
                }

                body:after {
                    content: "You are not allowed to print preview this page, Thank you";
                }
            }
        </style>
        <style type="text/css">
            #wpcp-error-message {
                direction: ltr;
                text-align: center;
                transition: opacity 900ms ease 0s;
                z-index: 99999999;
            }

            .hideme {
                opacity: 0;
                visibility: hidden;
            }

            .showme {
                opacity: 1;
                visibility: visible;
            }

            .msgmsg-box-wpcp {
                border: 1px solid #f5aca6;
                border-radius: 10px;
                color: #555;
                font-family: Tahoma;
                font-size: 11px;
                margin: 10px;
                padding: 10px 36px;
                position: fixed;
                width: 255px;
                top: 50%;
                left: 50%;
                margin-top: -10px;
                margin-left: -130px;
                -webkit-box-shadow: 0px 0px 34px 2px rgba(242,191,191,1);
                -moz-box-shadow: 0px 0px 34px 2px rgba(242,191,191,1);
                box-shadow: 0px 0px 34px 2px rgba(242,191,191,1);
            }

            .msgmsg-box-wpcp span {
                font-weight: bold;
                text-transform: uppercase;
            }

            .warning-wpcp {
                background: #ffecec url('https://getme.com.my/wp-content/plugins/wp-content-copy-protector/images/warning.png') no-repeat 10px 50%;
            }
        </style>
        <script type="text/javascript" id="astra-theme-js-js-extra">
            /* <![CDATA[ */
            var astra = {
                "break_point": "921",
                "isRtl": ""
            };
            /* ]]> */
        </script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/themes/astra/assets/js/minified/style.min.js?ver=2.0.1" id="astra-theme-js-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/common.min.js?ver=3.1.17" id="wp-event-manager-common-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-includes/js/jquery/ui/core.min.js?ver=1.13.3" id="jquery-ui-core-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-includes/js/jquery/ui/controlgroup.min.js?ver=1.13.3" id="jquery-ui-controlgroup-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-includes/js/jquery/ui/checkboxradio.min.js?ver=1.13.3" id="jquery-ui-checkboxradio-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-includes/js/jquery/ui/button.min.js?ver=1.13.3" id="jquery-ui-button-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-includes/js/jquery/ui/datepicker.min.js?ver=1.13.3" id="jquery-ui-datepicker-js"></script>
        <script type="text/javascript" id="jquery-ui-datepicker-js-after">
            /* <![CDATA[ */
            jQuery(function(jQuery) {
                jQuery.datepicker.setDefaults({
                    "closeText": "Close",
                    "currentText": "Today",
                    "monthNames": ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                    "monthNamesShort": ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    "nextText": "Next",
                    "prevText": "Previous",
                    "dayNames": ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                    "dayNamesShort": ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
                    "dayNamesMin": ["S", "M", "T", "W", "T", "F", "S"],
                    "dateFormat": "MM d, yy",
                    "firstDay": 1,
                    "isRTL": false
                });
            });
            /* ]]> */
        </script>
        <script type="text/javascript" src="https://getme.com.my/wp-includes/js/jquery/ui/menu.min.js?ver=1.13.3" id="jquery-ui-menu-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-includes/js/dist/vendor/moment.min.js?ver=2.30.1" id="moment-js"></script>
        <script type="text/javascript" id="moment-js-after">
            /* <![CDATA[ */
            moment.updateLocale('en_US', {
                "months": ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                "monthsShort": ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                "weekdays": ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                "weekdaysShort": ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
                "week": {
                    "dow": 1
                },
                "longDateFormat": {
                    "LT": "g:i a",
                    "LTS": null,
                    "L": null,
                    "LL": "F j, Y",
                    "LLL": "F j, Y g:i a",
                    "LLLL": null
                }
            });
            /* ]]> */
        </script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/jquery-ui-daterangepicker/jquery.comiseo.daterangepicker.js?ver=3.1.17" id="wp-event-manager-jquery-ui-daterangepicker-js"></script>
        <script type="text/javascript" id="wp-event-manager-content-event-listing-js-extra">
            /* <![CDATA[ */
            var event_manager_content_event_listing = {
                "i18n_initialText": "Select date range",
                "i18n_applyButtonText": "Apply",
                "i18n_clearButtonText": "Clear",
                "i18n_cancelButtonText": "Cancel",
                "i18n_today": "Today",
                "i18n_tomorrow": "Tomorrow",
                "i18n_thisWeek": "This Week",
                "i18n_nextWeek": "Next Week",
                "i18n_thisMonth": "This Month",
                "i18n_nextMonth": "Next Month",
                "i18n_thisYear": "This Year",
                "i18n_nextYear": "Next Month"
            };
            var event_manager_content_event_listing = {
                "i18n_datepicker_format": "yy-mm-dd",
                "i18n_initialText": "Select Date Range",
                "i18n_applyButtonText": "Apply",
                "i18n_clearButtonText": "Clear",
                "i18n_cancelButtonText": "Cancel",
                "i18n_today": "Today",
                "i18n_tomorrow": "Tomorrow",
                "i18n_thisWeek": "This Week",
                "i18n_nextWeek": "Next Week",
                "i18n_thisMonth": "This Month",
                "i18n_nextMonth": "Next Month",
                "i18n_thisYear": "This Year",
                "i18n_nextYear": "Next Year"
            };
            /* ]]> */
        </script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/content-event-listing.js?ver=3.1.17" id="wp-event-manager-content-event-listing-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/jquery-deserialize/jquery.deserialize.js?ver=1.2.1" id="jquery-deserialize-js"></script>
        <script type="text/javascript" id="wp-event-manager-ajax-filters-js-extra">
            /* <![CDATA[ */
            var event_manager_ajax_filters = {
                "ajax_url": "https:\/\/getme.com.my\/em-ajax\/get_listings\/",
                "is_rtl": "0",
                "lang": null
            };
            var event_manager_ajax_filters = {
                "ajax_url": "\/em-ajax\/%%endpoint%%\/",
                "is_rtl": "0",
                "lang": null
            };
            /* ]]> */
        </script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/event-ajax-filters.min.js?ver=3.1.17" id="wp-event-manager-ajax-filters-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/mystickymenu/js/detectmobilebrowser.js?ver=2.2.8" id="detectmobilebrowser-js"></script>
        <script type="text/javascript" id="mystickymenu-js-extra">
            /* <![CDATA[ */
            var option = {
                "mystickyClass": ".main-header-bar-wrap",
                "activationHeight": "0",
                "disableWidth": "0",
                "disableLargeWidth": "0",
                "adminBar": "false",
                "device_desktop": "1",
                "device_mobile": "1",
                "mystickyTransition": "fade",
                "mysticky_disable_down": "false"
            };
            /* ]]> */
        </script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/mystickymenu/js/mystickymenu.min.js?ver=2.2.8" id="mystickymenu-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/wp-event-manager/assets/js/jquery-timepicker/jquery.timepicker.min.js?ver=3.1.17" id="wp-event-manager-jquery-timepicker-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/elementor/assets/js/frontend-modules.min.js?ver=2.9.8" id="elementor-frontend-modules-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/elementor/assets/lib/dialog/dialog.min.js?ver=4.7.6" id="elementor-dialog-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/elementor/assets/lib/waypoints/waypoints.min.js?ver=4.0.2" id="elementor-waypoints-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/elementor/assets/lib/swiper/swiper.min.js?ver=5.3.6" id="swiper-js"></script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/elementor/assets/lib/share-link/share-link.min.js?ver=2.9.8" id="share-link-js"></script>
        <script type="text/javascript" id="elementor-frontend-js-before">
            /* <![CDATA[ */
            var elementorFrontendConfig = {
                "environmentMode": {
                    "edit": false,
                    "wpPreview": false
                },
                "i18n": {
                    "shareOnFacebook": "Share on Facebook",
                    "shareOnTwitter": "Share on Twitter",
                    "pinIt": "Pin it",
                    "downloadImage": "Download image"
                },
                "is_rtl": false,
                "breakpoints": {
                    "xs": 0,
                    "sm": 480,
                    "md": 768,
                    "lg": 1025,
                    "xl": 1440,
                    "xxl": 1600
                },
                "version": "2.9.8",
                "urls": {
                    "assets": "https:\/\/getme.com.my\/wp-content\/plugins\/elementor\/assets\/"
                },
                "settings": {
                    "page": [],
                    "general": {
                        "elementor_global_image_lightbox": "yes",
                        "elementor_lightbox_enable_counter": "yes",
                        "elementor_lightbox_enable_fullscreen": "yes",
                        "elementor_lightbox_enable_zoom": "yes",
                        "elementor_lightbox_enable_share": "yes",
                        "elementor_lightbox_title_src": "title",
                        "elementor_lightbox_description_src": "description"
                    },
                    "editorPreferences": []
                },
                "post": {
                    "id": 246,
                    "title": "GetMe%20%E2%80%93%20Online%20%26%20Offline%20%20Market%20Service%20Provider",
                    "excerpt": "",
                    "featuredImage": false
                }
            };
            /* ]]> */
        </script>
        <script type="text/javascript" src="https://getme.com.my/wp-content/plugins/elementor/assets/js/frontend.min.js?ver=2.9.8" id="elementor-frontend-js"></script>
        <script>
            /(trident|msie)/i.test(navigator.userAgent) && document.getElementById && window.addEventListener && window.addEventListener("hashchange", function() {
                var t, e = location.hash.substring(1);
                /^[A-z0-9_-]+$/.test(e) && (t = document.getElementById(e)) && (/^(?:a|select|input|button|textarea)$/i.test(t.tagName) || (t.tabIndex = -1),
                t.focus())
            }, !1);
        </script>
    </body>
</html>
