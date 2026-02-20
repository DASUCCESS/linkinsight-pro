{{-- resources/views/themes/modern/layouts/public-styles.blade.php --}}
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

    body.modern-body, .modern-body {
        background: radial-gradient(circle at top, rgba(148,163,253,0.18), transparent 55%) var(--color-background);
        color: var(--color-text-primary);
    }

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

    .modern-nav-top {
        background: linear-gradient(90deg,
            color-mix(in srgb, var(--color-primary) 78%, #000) 0%,
            color-mix(in srgb, var(--color-secondary) 65%, #000) 60%,
            color-mix(in srgb, var(--color-accent) 55%, #000) 100%);
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
    }
    .modern-nav-link {
        color: rgb(100,116,139);
        cursor: pointer;
        transition: color .18s ease;
    }
    .modern-nav-link:hover {
        color: rgb(15,23,42);
    }

    .modern-mobile-link {
        display: block;
        padding: 0.6rem 0.75rem;
        border-radius: 0.9rem;
        color: rgb(51,65,85);
        background-color: rgba(148,163,184,0.04);
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease;
    }
    .modern-mobile-link:hover {
        background-color: rgba(148,163,184,0.12);
        color: rgb(15,23,42);
    }

    .modern-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.55rem 1.15rem;
        border-radius: var(--btn-radius);
        font-weight: 600;
        background: linear-gradient(135deg, var(--color-primary), color-mix(in srgb, var(--color-primary) 60%, var(--color-accent)));
        color: #fff;
        box-shadow: 0 18px 55px color-mix(in srgb, var(--color-primary) 40%, rgba(15,23,42,0.55));
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        font-size: 0.8rem;
    }
    .modern-btn-primary:hover {
        transform: translateY(-1px) scale(var(--hover-scale));
        filter: saturate(1.05);
        box-shadow: 0 26px 80px color-mix(in srgb, var(--color-primary) 45%, rgba(15,23,42,0.65));
    }

    .modern-btn-outline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1.05rem;
        border-radius: var(--btn-radius);
        font-weight: 500;
        border: 1px solid rgba(148,163,184,0.75);
        background: rgba(255,255,255,0.85);
        color: rgb(30,64,175);
        cursor: pointer;
        font-size: 0.8rem;
        box-shadow: 0 10px 30px rgba(15,23,42,0.08);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .modern-btn-outline:hover {
        transform: translateY(-1px) scale(var(--hover-scale));
        border-color: color-mix(in srgb, var(--color-primary) 55%, rgba(148,163,184,0.75));
        box-shadow: 0 18px 55px rgba(15,23,42,0.18);
    }

    .modern-btn-invert {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.6rem 1.4rem;
        border-radius: 999px;
        background-color: #f9fafb;
        color: #020617;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 16px 48px rgba(15,23,42,0.55);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .modern-btn-invert:hover {
        transform: translateY(-1px) scale(var(--hover-scale));
        box-shadow: 0 22px 70px rgba(15,23,42,0.75);
    }

    .modern-btn-ghost {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.6rem 1.3rem;
        border-radius: 999px;
        border: 1px solid rgba(226,232,240,0.75);
        background-color: transparent;
        color: #e5e7eb;
        font-weight: 500;
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, transform .18s ease;
    }
    .modern-btn-ghost:hover {
        background-color: rgba(15,23,42,0.35);
        border-color: rgba(226,232,240,1);
        transform: translateY(-1px) scale(var(--hover-scale));
    }

    .modern-hero-shell {
        border-radius: 1.8rem;
        background: linear-gradient(180deg,
            color-mix(in srgb, var(--color-card) 70%, rgba(255,255,255,0.75)) 0%,
            color-mix(in srgb, var(--color-background) 92%, transparent) 100%);
        border: 1px solid color-mix(in srgb, var(--color-border) 80%, rgba(148,163,184,0.4));
        box-shadow: 0 26px 85px rgba(15,23,42,0.22);
        padding: 2.2rem 1.8rem;
        position: relative;
        overflow: hidden;
    }

    .modern-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        background: rgba(129,140,248,0.09);
        border: 1px solid rgba(129,140,248,0.45);
        font-size: 0.68rem;
        font-weight: 500;
        color: rgb(79,70,229);
    }

    .modern-hero-image-card {
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 34px 90px rgba(15,23,42,0.45);
        background-color: #020617;
    }

    .modern-trust-row {
        display: grid;
        grid-template-columns: repeat(2,minmax(0,1fr));
        gap: 0.75rem;
        font-size: 0.72rem;
        margin-top: 1.5rem;
    }
    @media (min-width: 768px) {
        .modern-trust-row {
            grid-template-columns: repeat(3,minmax(0,1fr));
        }
    }
    .modern-trust-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        background: rgba(15,23,42,0.02);
        border: 1px dashed rgba(148,163,184,0.55);
        color: rgb(100,116,139);
    }

    .modern-section-muted {
        background-color: rgba(129,140,248,0.045);
        border-top: 1px solid rgba(148,163,184,0.25);
        border-bottom: 1px solid rgba(148,163,184,0.25);
    }

    .modern-feature-card {
        border-radius: 1.4rem;
        background: #f9fafb;
        border: 1px solid rgba(209,213,219,0.8);
        padding: 1.4rem 1.3rem;
        box-shadow: 0 18px 60px rgba(15,23,42,0.12);
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .modern-feature-card:hover {
        transform: translateY(-4px) scale(var(--hover-scale));
        box-shadow: 0 26px 80px rgba(15,23,42,0.2);
        border-color: color-mix(in srgb, var(--color-primary) 50%, rgba(209,213,219,0.8));
    }

    .modern-footer-top {
        background: linear-gradient(135deg,
            color-mix(in srgb, var(--color-primary) 78%, #000) 0%,
            color-mix(in srgb, var(--color-secondary) 68%, #000) 60%,
            color-mix(in srgb, var(--color-accent) 40%, #000) 100%);
    }

    .modern-footer-link {
        cursor: pointer;
        transition: color .18s ease;
    }
    .modern-footer-link:hover {
        color: #e5e7eb;
    }

    .cms-faq-2 details{
        border-radius: 1.25rem;
        border: 1px solid var(--color-border);
        background: color-mix(in srgb, var(--color-card) 65%, rgba(255,255,255,0.20));
        padding: .9rem 1rem;
        margin-bottom: .9rem;
        box-shadow: 0 16px 55px rgba(15,23,42,0.16);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
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
        width: 30px; height: 30px;
        border-radius: 999px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        background: var(--color-secondary);
        color: #fff;
        flex-shrink: 0;
        font-size: 0.85rem;
    }
    .cms-faq-2 details[open] summary::after{
        content:'−';
        background: var(--color-primary);
    }
    .cms-faq-2 p{ margin-top: .6rem; color: var(--color-text-secondary); }
</style>
