function isProfilePage(url) {
    return /^https:\/\/www\.linkedin\.com\/in\//.test(url);
}

function isActivityPage(url) {
    return /^https:\/\/www\.linkedin\.com\/in\/[^/]+\/recent-activity/.test(url);
}

function parseLinkedinCount(value) {
    if (value === null || value === undefined) {
        return null;
    }

    const raw = String(value)
        .replace(/,/g, '')
        .replace(/\s+/g, '')
        .toLowerCase();

    if (!raw) {
        return null;
    }

    const match = raw.match(/(\d+(?:\.\d+)?)([kmb])?\+?/i);
    if (!match) {
        return null;
    }

    const base = Number(match[1]);
    if (Number.isNaN(base)) {
        return null;
    }

    const suffix = (match[2] || '').toLowerCase();
    const multiplier = suffix === 'k'
        ? 1000
        : suffix === 'm'
            ? 1000000
            : suffix === 'b'
                ? 1000000000
                : 1;

    return Math.round(base * multiplier);
}

function normalizeLinkedinUrl(url) {
    if (!url) {
        return window.location.href;
    }

    try {
        const parsed = new URL(url, window.location.origin);
        parsed.search = '';
        parsed.hash = '';
        return parsed.toString().replace(/\/+$/, '');
    } catch (e) {
        return String(url).split('?')[0].split('#')[0].replace(/\/+$/, '');
    }
}

function scrapeProfile() {
    const result = {
        linkedin_id: null,
        public_url: normalizeLinkedinUrl(window.location.href),
        name: null,
        headline: null,
        location: null,
        connections: null,
        followers: null,
        profile_image_url: null
    };

    try {
        const urlMatch = window.location.pathname.match(/\/in\/([^/]+)/);
        if (urlMatch) {
            result.linkedin_id = urlMatch[1];
        }

        const canonicalEl = document.querySelector('link[rel="canonical"]');
        if (canonicalEl && canonicalEl.href) {
            const canonical = normalizeLinkedinUrl(canonicalEl.href);
            if (/linkedin\.com\/in\//.test(canonical)) {
                result.public_url = canonical;
            }
        }

        const nameEl =
            document.querySelector('.pv-text-details__left-panel h1') ||
            document.querySelector('main h1') ||
            document.querySelector('h1');
        if (nameEl) {
            result.name = nameEl.innerText.trim();
        }

        const headlineEl =
            document.querySelector('.pv-text-details__left-panel .text-body-medium') ||
            document.querySelector('.pv-text-details__left-panel span[dir="ltr"]') ||
            document.querySelector('.pv-text-details__left-panel div.text-body-medium') ||
            document.querySelector('section div.text-body-medium');
        if (headlineEl) {
            result.headline = headlineEl.innerText.trim();
        }

        const locationCandidates = document.querySelectorAll(
            '.pv-text-details__left-panel span.text-body-small, .pv-text-details__left-panel span.inline-block, main .text-body-small'
        );

        if (locationCandidates.length) {
            const loc = Array.from(locationCandidates)
                .map(e => e.innerText.trim())
                .filter(t => t && !/followers|connections/i.test(t))[0];

            if (loc) {
                result.location = loc;
            }
        }

        const statSpans = Array.from(document.querySelectorAll('main span, main li, main a'));

        statSpans
            .map(e => e.innerText.trim())
            .filter(Boolean)
            .forEach(t => {
                if (/connections/i.test(t)) {
                    const value = parseLinkedinCount(t);
                    if (value !== null) {
                        result.connections = value;
                    }
                } else if (/followers/i.test(t)) {
                    const value = parseLinkedinCount(t);
                    if (value !== null) {
                        result.followers = value;
                    }
                }
            });

        const imgEl =
            document.querySelector('.pv-top-card-profile-picture__image') ||
            document.querySelector('img.pv-top-card-profile-picture__image') ||
            document.querySelector('img.profile-photo-edit__preview') ||
            document.querySelector('main img[alt*="photo"]') ||
            document.querySelector('main img');

        if (imgEl && imgEl.src) {
            result.profile_image_url = imgEl.src;
        }
    } catch (e) {
        console.warn('LinkInsight profile scraping error', e);
        return { error: 'SCRAPE_ERROR', message: 'Error while reading profile DOM.' };
    }

    if (!result.linkedin_id) {
        return { error: 'NO_PROFILE_ID', message: 'Could not detect LinkedIn profile ID.' };
    }

    return { data: result };
}

function scrapePosts() {
    const posts = [];

    try {
        const articleNodes = document.querySelectorAll('article');

        articleNodes.forEach((article, index) => {
            const textEl =
                article.querySelector('div.feed-shared-update-v2__commentary') ||
                article.querySelector('span.break-words') ||
                article.querySelector('div[dir="ltr"]');

            const text = textEl ? textEl.innerText.trim() : '';

            let impressions = null;
            let reactions = null;
            let comments = null;

            Array.from(article.querySelectorAll('span, button')).forEach(el => {
                const t = el.innerText.trim();
                if (!t) return;

                if (/impression/i.test(t)) {
                    const value = parseLinkedinCount(t);
                    if (value !== null) impressions = value;
                } else if (/reaction/i.test(t) || /like/i.test(t)) {
                    const value = parseLinkedinCount(t);
                    if (value !== null) reactions = value;
                } else if (/comment/i.test(t)) {
                    const value = parseLinkedinCount(t);
                    if (value !== null) comments = value;
                }
            });

            const permalinkEl =
                article.querySelector('a[href*="/feed/update/"]') ||
                article.querySelector('a[href*="/posts/"]') ||
                article.querySelector('a[href*="activity-"]') ||
                article.querySelector('a[href*="activity"]');

            const permalink = permalinkEl
                ? normalizeLinkedinUrl(permalinkEl.href)
                : normalizeLinkedinUrl(window.location.href + '#post-' + index);

            const externalIdMatch = permalink.match(/(urn:li:[^/?#]+|activity-\d+|ugcPost-\d+|share-\d+)/i);
            const externalId = externalIdMatch ? externalIdMatch[1] : permalink;

            const timeEl =
                article.querySelector('time') ||
                article.querySelector('span.visually-hidden');

            const postedAtText = timeEl
                ? (timeEl.getAttribute('datetime') || timeEl.innerText.trim())
                : null;

            posts.push({
                external_id: externalId,
                post_type: 'post',
                content: text,
                posted_at_human: postedAtText,
                impressions,
                reactions,
                comments,
                permalink
            });
        });
    } catch (e) {
        console.warn('LinkInsight posts scraping error', e);
        return { error: 'SCRAPE_ERROR', message: 'Error while reading posts DOM.' };
    }

    if (!posts.length) {
        return { error: 'NO_POSTS', message: 'No posts found on this page.' };
    }

    return { data: posts };
}

chrome.runtime.onMessage.addListener((message, _sender, sendResponse) => {
    try {
        if (!message || !message.type) {
            sendResponse({ success: false, error: 'Invalid message.' });
            return;
        }

        if (message.type === 'SCRAPE_PROFILE') {
            if (!isProfilePage(window.location.href)) {
                sendResponse({ success: false, error: 'Open a LinkedIn profile page first.' });
                return;
            }

            const result = scrapeProfile();
            if (result.error) {
                sendResponse({ success: false, error: result.message || result.error });
                return;
            }

            sendResponse({ success: true, data: result.data });
            return;
        }

        if (message.type === 'SCRAPE_POSTS') {
            if (!isActivityPage(window.location.href)) {
                sendResponse({ success: false, error: 'Open recent activity page first.' });
                return;
            }

            const result = scrapePosts();
            if (result.error) {
                sendResponse({ success: false, error: result.message || result.error });
                return;
            }

            sendResponse({ success: true, data: result.data });
            return;
        }

        sendResponse({ success: false, error: 'Unsupported message type.' });
    } catch (e) {
        console.warn('LinkInsight onMessage error', e);
        sendResponse({ success: false, error: 'Unexpected content script error.' });
    }

    return true;
});
