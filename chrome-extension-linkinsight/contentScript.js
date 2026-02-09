function isProfilePage(url) {
    return /^https:\/\/www\.linkedin\.com\/in\//.test(url);
}

function isActivityPage(url) {
    return /^https:\/\/www\.linkedin\.com\/in\/[^/]+\/recent-activity/.test(url);
}

function scrapeProfile() {
    const result = {
        linkedin_id: null,
        public_url: window.location.href,
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
            '.pv-text-details__left-panel span.text-body-small, .pv-text-details__left-panel span.inline-block'
        );

        if (locationCandidates.length) {
            const loc = Array.from(locationCandidates)
                .map(e => e.innerText.trim())
                .filter(t => t && !/followers|connections/i.test(t))[0];

            if (loc) {
                result.location = loc;
            }
        }

        const statsRoots = [
            '.pv-top-card--list',
            '.pv-top-card--list-bullet',
            '.pv-top-card__list',
            'section.pv-top-card'
        ];

        let statSpans = [];
        for (const selector of statsRoots) {
            const node = document.querySelector(selector);
            if (node) {
                statSpans = Array.from(node.querySelectorAll('span'));
                if (statSpans.length) break;
            }
        }

        statSpans
            .map(e => e.innerText.trim())
            .filter(Boolean)
            .forEach(t => {
                const clean = t.replace(/,/g, '');
                const numMatch = clean.match(/(\d+\+?)/);
                if (!numMatch) return;
                const numStr = numMatch[1];

                if (/connections/i.test(t)) {
                    result.connections = numStr;
                } else if (/followers/i.test(t)) {
                    result.followers = numStr;
                }
            });

        const imgEl =
            document.querySelector('.pv-top-card-profile-picture__image') ||
            document.querySelector('img.pv-top-card-profile-picture__image') ||
            document.querySelector('img.profile-photo-edit__preview') ||
            document.querySelector('img[alt*="profile"]') ||
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

            const metaTextEls = article.querySelectorAll('span');

            let impressions = null;
            let reactions = null;
            let comments = null;

            Array.from(metaTextEls).forEach(el => {
                const t = el.innerText.trim();
                if (!t) return;

                const numberMatch = t.replace(/,/g, '').match(/^(\d+)\s/);
                if (!numberMatch) return;
                const num = parseInt(numberMatch[1], 10);

                if (/impression/i.test(t)) {
                    impressions = num;
                } else if (/reaction/i.test(t) || /like/i.test(t)) {
                    reactions = num;
                } else if (/comment/i.test(t)) {
                    comments = num;
                }
            });

            const timeEl = article.querySelector('span.visually-hidden');
            const postedAtText = timeEl ? timeEl.innerText.trim() : null;

            const linkEl = article.querySelector('a[href*="activity"]');
            const permalink = linkEl ? linkEl.href : window.location.href + '#post-' + index;

            posts.push({
                external_id: permalink,
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

chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    if (!message || !message.type) {
        return false;
    }

    const url = window.location.href;

    if (message.type === 'SCRAPE_PROFILE') {
        if (!isProfilePage(url)) {
            sendResponse({ success: false, error: 'NOT_PROFILE_PAGE' });
            return true;
        }
        const result = scrapeProfile();
        if (result.error) {
            sendResponse({ success: false, error: result.error, message: result.message });
        } else {
            sendResponse({ success: true, data: result.data });
        }
        return true;
    }

    if (message.type === 'SCRAPE_POSTS') {
        if (!isActivityPage(url)) {
            sendResponse({ success: false, error: 'NOT_ACTIVITY_PAGE' });
            return true;
        }
        const result = scrapePosts();
        if (result.error) {
            sendResponse({ success: false, error: result.error, message: result.message });
        } else {
            sendResponse({ success: true, data: result.data });
        }
        return true;
    }

    return false;
});
