<style>
    :root {
        --color-primary: {{ app_color('primary_color') }};
        --color-secondary: {{ app_color('secondary_color') }};
        --color-accent: {{ app_color('accent_color') }};
        --color-background: {{ app_color('background_color') }};
        --color-card: {{ app_color('card_color') }};
        --color-border: {{ app_color('border_color') }};
        --color-text-primary: {{ app_color('text_primary') }};
        --color-text-secondary: {{ app_color('text_secondary') }};
        --btn-radius: {{ app_color('button_radius', '0.85rem') }};
        --hover-scale: {{ app_color('hover_scale', '1.05') }};
    }

    body {
        background-color: var(--color-background);
        color: var(--color-text-primary);
    }

    /* Generic CMS content */
    .cms-content { font-size: 0.875rem; line-height: 1.65; }
    .cms-content h1, .cms-content h2, .cms-content h3, .cms-content h4 {
        margin-top: 1.25rem;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--color-text-primary);
    }
    .cms-content p { margin-top: 0.5rem; margin-bottom: 0.5rem; }
    .cms-content ul, .cms-content ol {
        margin-top: 0.5rem;
        margin-bottom: 0.75rem;
        margin-left: 1.5rem;
        padding-left: 0.75rem;
    }
    .cms-content ul { list-style: disc outside; }
    .cms-content ol { list-style: decimal outside; }
    .cms-content li { margin-top: 0.25rem; margin-bottom: 0.25rem; }
    .cms-content a {
        color: var(--color-primary);
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .cms-content strong { font-weight: 600; }

    /* Buttons */
    .li-btn{
        box-shadow: 0 18px 55px color-mix(in srgb, var(--color-primary) 35%, rgba(15,23,42,0.35));
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }
    .li-btn:hover{
        box-shadow: 0 28px 80px color-mix(in srgb, var(--color-primary) 40%, rgba(15,23,42,0.45));
        filter: saturate(1.05);
    }
    .li-btn-outline{
        background: color-mix(in srgb, var(--color-card) 60%, transparent);
        border: 1px solid var(--color-border);
        color: var(--color-text-primary);
        box-shadow: 0 14px 45px rgba(15,23,42,0.18);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .li-btn-outline:hover{
        border-color: var(--color-primary);
        box-shadow: 0 22px 70px rgba(15,23,42,0.28);
    }

    /* Reveal / float animations */
    @keyframes liFloatA { 0%,100% { transform: translate3d(0,0,0); } 50% { transform: translate3d(0,-14px,0); } }
    @keyframes liFloatB { 0%,100% { transform: translate3d(0,0,0); } 50% { transform: translate3d(0,-18px,0); } }
    .li-blob{ animation: liFloatA 8s ease-in-out infinite; }
    .li-blob-2{ animation: liFloatB 9.5s ease-in-out infinite; }
    .li-float{ animation: liFloatA 7.5s ease-in-out infinite; }
    .li-float-delayed{ animation-delay: .8s; }

    @keyframes liReveal { from { opacity: 0; transform: translate3d(0,14px,0); } to { opacity: 1; transform: translate3d(0,0,0); } }
    .li-reveal{ opacity: 0; animation: liReveal .8s ease forwards; }
    .li-reveal-1{ animation-delay: .05s; }
    .li-reveal-2{ animation-delay: .18s; }
    .li-reveal-3{ animation-delay: .30s; }
    .li-reveal-4{ animation-delay: .42s; }

    /* Hero / cards / widgets / scroll hint / feature grid / split cards / testimonials / CTA / FAQ
       (all exactly as in your current layout) */

    .li-glass-card{
        background: color-mix(in srgb, var(--color-card) 26%, rgba(255,255,255,0.75));
        border: 1px solid var(--color-border);
        box-shadow: 0 28px 90px rgba(15,23,42,0.30);
        backdrop-filter: blur(10px);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .li-glass-card:hover{
        transform: translateY(-6px) scale(var(--hover-scale));
        border-color: var(--color-primary);
        box-shadow: 0 36px 120px rgba(15,23,42,0.45);
    }
    .li-image-sheen{
        background: linear-gradient(110deg, transparent 0%, rgba(255,255,255,0.25) 35%, transparent 60%);
        transform: translateX(-40%);
        animation: liSheen 3.6s ease-in-out infinite;
        mix-blend-mode: overlay;
    }
    @keyframes liSheen{ 0%{ transform: translateX(-60%);} 50%{ transform: translateX(40%);} 100%{ transform: translateX(60%);} }

    .li-mini-widget{
        border-radius: 1rem;
        background: color-mix(in srgb, var(--color-background) 55%, transparent);
        border: 1px solid color-mix(in srgb, var(--color-border) 85%, transparent);
        box-shadow: 0 14px 45px rgba(15,23,42,0.12);
        padding: .75rem .9rem;
        transition: transform .2s ease, box-shadow .2s ease;
        cursor: pointer;
    }
    .li-mini-widget:hover{
        transform: translateY(-3px) scale(var(--hover-scale));
        box-shadow: 0 22px 70px rgba(15,23,42,0.20);
    }

    .li-float-card{
        background: color-mix(in srgb, var(--color-card) 75%, rgba(255,255,255,0.25));
        border: 1px solid var(--color-border);
        box-shadow: 0 26px 75px rgba(15,23,42,0.35);
        backdrop-filter: blur(10px);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        cursor: pointer;
    }
    .li-float-card:hover{
        transform: translateY(-4px) scale(var(--hover-scale));
        border-color: var(--color-primary);
        box-shadow: 0 34px 95px rgba(15,23,42,0.50);
    }

    .li-micro-card{
        border-radius: 1.25rem;
        border: 1px solid var(--color-border);
        background: color-mix(in srgb, var(--color-card) 55%, rgba(255,255,255,0.35));
        box-shadow: 0 18px 55px rgba(15,23,42,0.18);
        padding: .9rem 1rem;
        transition: transform .2s ease, box-shadow .2s ease;
        cursor: pointer;
    }
    .li-micro-card:hover{
        transform: translateY(-4px) scale(var(--hover-scale));
        box-shadow: 0 26px 80px rgba(15,23,42,0.30);
    }

    .li-scroll-hint{
        width: 34px; height: 54px;
        border-radius: 999px;
        border: 1px solid color-mix(in srgb, var(--color-border) 80%, transparent);
        background: color-mix(in srgb, var(--color-card) 45%, transparent);
        display: inline-flex;
        align-items: flex-start;
        justify-content: center;
        padding-top: 10px;
        box-shadow: 0 14px 45px rgba(15,23,42,0.15);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .li-scroll-hint:hover{
        transform: translateY(-3px) scale(var(--hover-scale));
        border-color: var(--color-primary);
        box-shadow: 0 22px 70px rgba(15,23,42,0.25);
    }
    .li-scroll-dot{
        width: 7px; height: 7px;
        border-radius: 999px;
        background: var(--color-accent);
        animation: liDot 1.6s ease-in-out infinite;
    }
    @keyframes liDot{ 0%,100%{ transform: translateY(0);} 50%{ transform: translateY(16px);} }

    .li-section-card{
        border-radius: 1.75rem;
        border: 1px solid var(--color-border);
        background: color-mix(in srgb, var(--color-card) 58%, rgba(255,255,255,0.20));
        box-shadow: 0 22px 70px rgba(15,23,42,0.22);
        padding: 1.5rem;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .li-section-card:hover{
        transform: translateY(-6px) scale(var(--hover-scale));
        border-color: var(--color-primary);
        box-shadow: 0 34px 105px rgba(15,23,42,0.35);
    }
    .li-card-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:.7rem;
        font-weight:600;
        padding:0.3rem 0.7rem;
        border-radius:999px;
        color:#fff;
    }

    .cms-feature-grid-2 ul{
        display:grid;
        grid-template-columns: minmax(0,1fr);
        gap: 1rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    @media (min-width: 768px){
        .cms-feature-grid-2 ul{
            grid-template-columns: repeat(2, minmax(0,1fr));
            gap: 1.25rem;
        }
    }
    @media (min-width: 1024px){
        .cms-feature-grid-2 ul{
            grid-template-columns: repeat(4, minmax(0,1fr));
            gap: 1.25rem;
        }
    }
    .cms-feature-grid-2 li{
        position: relative;
        border-radius: 1.5rem;
        border: 1px solid var(--color-border);
        background: linear-gradient(180deg,
                    color-mix(in srgb, var(--color-card) 70%, rgba(255,255,255,0.25)) 0%,
                    color-mix(in srgb, var(--color-background) 88%, transparent) 100%);
        box-shadow: 0 18px 60px rgba(15,23,42,0.18);
        padding: 1.1rem 1.2rem;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .cms-feature-grid-2 li:hover{
        transform: translateY(-6px) scale(var(--hover-scale));
        border-color: var(--color-primary);
        box-shadow: 0 30px 95px rgba(15,23,42,0.35);
    }
    .cms-feature-grid-2 li strong{
        display:block;
        margin-top: .6rem;
        margin-bottom: .25rem;
        font-size: .9rem;
        color: var(--color-text-primary);
    }

    .li-split-card{
        position: relative;
        border-radius: 1.75rem;
        border: 1px solid var(--color-border);
        background: color-mix(in srgb, var(--color-card) 58%, rgba(255,255,255,0.22));
        box-shadow: 0 22px 75px rgba(15,23,42,0.22);
        padding: 1.5rem;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        cursor: pointer;
        overflow: hidden;
    }
    .li-split-card:hover{
        transform: translateY(-6px) scale(var(--hover-scale));
        border-color: var(--color-primary);
        box-shadow: 0 34px 110px rgba(15,23,42,0.35);
    }

    .li-split-accent{
        position:absolute;
        left:0; top:0; bottom:0;
        width: 8px;
    }
    .li-split-dark{
        background: linear-gradient(135deg,
                color-mix(in srgb, var(--color-primary) 78%, #000) 0%,
                color-mix(in srgb, var(--color-secondary) 72%, #000) 55%,
                color-mix(in srgb, var(--color-accent) 18%, #000) 100%);
        border-color: color-mix(in srgb, var(--color-primary) 65%, var(--color-border));
        box-shadow: 0 28px 95px rgba(0,0,0,0.55);
    }
    .li-split-dark:hover{
        box-shadow: 0 36px 120px rgba(0,0,0,0.65);
    }

    .li-testimonial-shell{
        border-radius: 1.75rem;
        border: 1px solid var(--color-border);
        background: linear-gradient(180deg,
            color-mix(in srgb, var(--color-card) 70%, rgba(255,255,255,0.25)) 0%,
            color-mix(in srgb, var(--color-background) 92%, transparent) 100%);
        box-shadow: 0 22px 75px rgba(15,23,42,0.20);
        padding: 1.5rem;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        cursor: pointer;
    }
    .li-testimonial-shell:hover{
        transform: translateY(-6px) scale(var(--hover-scale));
        border-color: var(--color-primary);
        box-shadow: 0 34px 110px rgba(15,23,42,0.32);
    }

    .li-cta-panel{
        position: relative;
        border-radius: 2rem;
        border: 1px solid var(--color-border);
        background: color-mix(in srgb, var(--color-card) 58%, rgba(255, 255, 255, 0.75));
        box-shadow: 0 26px 95px rgba(15,23,42,0.25);
        padding: 2rem 1.5rem;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .li-cta-panel:hover{
        transform: translateY(-6px) scale(var(--hover-scale));
        border-color: var(--color-primary);
        box-shadow: 0 36px 120px rgba(15,23,42,0.35);
    }

    .cms-faq-2 details{
        border-radius: 1.25rem;
        border: 1px solid var(--color-border);
        background: color-mix(in srgb, var(--color-card) 65%, rgba(255,255,255,0.20));
        padding: .9rem 1rem;
        margin-bottom: .9rem;
        box-shadow: 0 16px 55px rgba(15,23,42,0.16);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .cms-faq-2 details:hover{
        transform: translateY(-4px) scale(var(--hover-scale));
        box-shadow: 0 24px 80px rgba(15,23,42,0.25);
    }
    .cms-faq-2 details[open]{
        border-color: var(--color-primary);
        box-shadow: 0 28px 95px rgba(15,23,42,0.30);
    }
    .cms-faq-2 summary{
        list-style: none;
        cursor: pointer;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 1rem;
        font-weight: 600;
        color: var(--color-text-primary);
    }
    .cms-faq-2 summary::-webkit-details-marker{ display:none; }
    .cms-faq-2 summary::after{
        content:'+';
        width: 34px; height: 34px;
        border-radius: 999px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        background: var(--color-secondary);
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    }
    .cms-faq-2 details[open] summary::after{
        content:'−';
        background: var(--color-primary);
    }
    .cms-faq-2 p{ margin-top: .75rem; color: var(--color-text-secondary); }

    .cms-faq-2 ul{
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: minmax(0,1fr);
        gap: .75rem;
    }
    .cms-faq-2 ul li{
        border-radius: 1.25rem;
        border: 1px solid var(--color-border);
        background: color-mix(in srgb, var(--color-card) 60%, rgba(255,255,255,0.20));
        padding: 1rem;
        box-shadow: 0 16px 55px rgba(15,23,42,0.14);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        cursor: pointer;
    }
    .cms-faq-2 ul li:hover{
        transform: translateY(-4px) scale(var(--hover-scale));
        border-color: var(--color-primary);
        box-shadow: 0 24px 85px rgba(15,23,42,0.25);
    }

</style>
