function isProfilePage(url) {
  return /^https:\/\/www\.linkedin\.com\/in\//.test(url);
}

function isActivityPage(url) {
  // works for ".../detail/recent-activity/" and ".../recent-activity/"
  return /^https:\/\/www\.linkedin\.com\/in\/.+\/.*recent-activity/.test(url);
}

<<<<<<< Updated upstream
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
=======
function scrapeProfileData() {
  const profile = {
    linkedin_id: '',
    public_url: '',
    name: '',
    headline: '',
    location: '',
    profile_image_url: '',
    connections_count: 0,
    followers_count: 0,
    profile_views: 0,
    search_appearances: 0,
    posts_count: 0 // will be updated from posts sync if needed
  };

  try {
    const cleanUrl = window.location.href.split(/[?#]/)[0];
    profile.public_url = cleanUrl;

    const idMatch = cleanUrl.match(/\/in\/([^/]+)/);
    if (idMatch) {
      profile.linkedin_id = idMatch[1];
    }

    // Name
    const nameEl =
      document.querySelector('h1.text-heading-xlarge') ||
      document.querySelector('.pv-text-details__left-panel h1') ||
      document.querySelector('main h1');

    if (nameEl) {
      profile.name = nameEl.innerText.trim();
    }

    // Headline
    const headlineEl =
      document.querySelector('.pv-text-details__left-panel .text-body-medium') ||
      document.querySelector('div.text-body-medium.break-words') ||
      document.querySelector('section div.text-body-medium');

    if (headlineEl) {
      profile.headline = headlineEl.innerText.trim();
    }

    // Location
    const locEl =
      document.querySelector(
        '.pv-text-details__left-panel span.text-body-small.inline.t-black--light.break-words'
      ) ||
      document.querySelector('span.text-body-small.inline.t-black--light.break-words');

    if (locEl) {
      profile.location = locEl.innerText.trim();
    }

    // Profile image
    const imgEl =
      document.querySelector('.pv-top-card-profile-picture__image') ||
      document.querySelector('.pv-top-card__photo img') ||
      document.querySelector('img.profile-photo-edit__preview');

    if (imgEl) {
      profile.profile_image_url = imgEl.src || imgEl.getAttribute('src') || '';
    }

    // Connections / followers
    const statTextCandidates = Array.from(
      document.querySelectorAll(
        '.pv-top-card--list li, .pv-top-card--list-bullet li, section.pv-top-card span'
      )
    )
      .map(el => el.innerText.trim())
      .filter(Boolean);

    statTextCandidates.forEach(text => {
      const numMatch = text.replace(/,/g, '').match(/(\d+)\+?/);
      if (!numMatch) return;
      const value = parseInt(numMatch[1], 10);

      if (/connection/i.test(text)) {
        profile.connections_count = Math.max(profile.connections_count, value);
      } else if (/follower/i.test(text)) {
        profile.followers_count = Math.max(profile.followers_count, value);
      }
    });

    // Profile views & search appearances (if visible on page)
    const bodyText = document.body.innerText;
    const viewsMatch = bodyText.match(/([\d,]+)\s+profile view/i);
    if (viewsMatch) {
      profile.profile_views = parseInt(viewsMatch[1].replace(/,/g, ''), 10) || 0;
    }

    const searchMatch = bodyText.match(/([\d,]+)\s+search appearance/i);
    if (searchMatch) {
      profile.search_appearances = parseInt(searchMatch[1].replace(/,/g, ''), 10) || 0;
    }
  } catch (err) {
    console.error('LinkInsight profile scraping error:', err);
  }

  return profile;
}

async function scrapePostsData() {
  // Auto-scroll to load more posts
  let lastHeight = document.body.scrollHeight;
  for (let i = 0; i < 5; i++) {
    window.scrollTo(0, document.body.scrollHeight);
    await new Promise(r => setTimeout(r, 1000));
    const newHeight = document.body.scrollHeight;
    if (newHeight === lastHeight) break;
    lastHeight = newHeight;
  }

  // Expand "see more" buttons
  document.querySelectorAll('button').forEach(btn => {
    if (btn.innerText && btn.innerText.trim().toLowerCase().includes('see more')) {
      btn.click();
    }
  });

  const posts = [];

  // LinkedIn activity posts are often in regions with data-urn containing "urn:li:activity"
  const postElements = document.querySelectorAll(
    'div[role="region"][data-urn*="urn:li:activity:"]'
  );

  postElements.forEach(postEl => {
    // Content text
    let contentText = '';
    const contentContainer =
      postEl.querySelector('.feed-shared-inline-show-more-text') ||
      postEl.querySelector('.update-components-text') ||
      postEl.querySelector('.feed-shared-update-v2__commentary');

    if (contentContainer) {
      contentText = contentContainer.innerText.trim();
    }

    // Timestamp (prefer visually-hidden full text)
    let timeText = '';
    const subDesc = postEl.querySelector('.update-components-actor__sub-description');
    if (subDesc) {
      const hiddenTime = subDesc.querySelector('.visually-hidden');
      if (hiddenTime && /ago$/i.test(hiddenTime.innerText.trim())) {
        timeText = hiddenTime.innerText.trim();
      } else {
        timeText = subDesc.innerText.trim();
      }
    }

    // Reactions count
    let reactionsCount = 0;
    const reactionsBtn = postEl.querySelector('button[aria-label*="reaction"]');
    if (reactionsBtn) {
      const match = reactionsBtn
        .getAttribute('aria-label')
        .match(/([\d,]+)\s+reaction/i);
      if (match) {
        reactionsCount = parseInt(match[1].replace(/,/g, ''), 10) || 0;
      }
    }

    // Comments count
    let commentsCount = 0;
    const commentsBtn = postEl.querySelector('button[aria-label*="comment"]');
    if (commentsBtn) {
      const match = commentsBtn
        .getAttribute('aria-label')
        .match(/([\d,]+)\s+comment/i);
      if (match) {
        commentsCount = parseInt(match[1].replace(/,/g, ''), 10) || 0;
      }
    }

    // Reposts count
    let repostsCount = 0;
    const repostsBtn = postEl.querySelector('button[aria-label*="repost"]');
    if (repostsBtn) {
      const match = repostsBtn
        .getAttribute('aria-label')
        .match(/([\d,]+)\s+repost/i);
      if (match) {
        repostsCount = parseInt(match[1].replace(/,/g, ''), 10) || 0;
      }
    }

    // Impressions / views (if visible inline)
    let impressionsCount = null;
    const spans = postEl.querySelectorAll('span[aria-hidden="true"]');
    Array.from(spans).forEach(sp => {
      const t = sp.innerText.trim().toLowerCase();
      if (t.includes('view')) {
        const m = sp.innerText.match(/([\d,]+)\s+view/i);
        if (m) {
          impressionsCount = parseInt(m[1].replace(/,/g, ''), 10) || 0;
        }
      }
    });

    // URN ID -> external_id / permalink
    let externalId = '';
    const urnAttr = postEl.getAttribute('data-urn'); // "urn:li:activity:XXXXXXXX"
    if (urnAttr) {
      externalId = urnAttr;
    }

    const permalink =
      externalId && externalId.startsWith('urn:')
        ? `https://www.linkedin.com/feed/update/${encodeURIComponent(externalId)}/`
        : window.location.href.split(/[?#]/)[0];

    posts.push({
      external_id: externalId,
      post_type: 'post',
      content: contentText,
      posted_at_human: timeText,
      impressions: impressionsCount,
      reactions: reactionsCount,
      comments: commentsCount,
      permalink: permalink,
      reposts: repostsCount
    });
  });

  return posts;
}

chrome.runtime.onMessage.addListener((msg, sender, sendResponse) => {
  if (!msg || !msg.action) return false;

  const url = window.location.href;

  // Profile scraping
  if (msg.action === 'scrapeProfile') {
    if (!isProfilePage(url)) {
      sendResponse({ success: false, error: 'NOT_PROFILE_PAGE' });
      return true;
    }

    try {
      const profile = scrapeProfileData();
      sendResponse({ success: true, profile });
>>>>>>> Stashed changes
    } catch (e) {
      console.error('Profile scraping error:', e);
      sendResponse({ success: false, error: 'SCRAPE_ERROR' });
    }
    return true;
  }

  // Posts scraping (async)
  if (msg.action === 'scrapePosts') {
    if (!isActivityPage(url)) {
      sendResponse({ success: false, error: 'NOT_ACTIVITY_PAGE' });
      return true;
    }

<<<<<<< Updated upstream
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
=======
    (async () => {
      try {
        const posts = await scrapePostsData();
        if (!posts.length) {
          sendResponse({ success: false, error: 'NO_POSTS' });
        } else {
          sendResponse({ success: true, posts });
        }
      } catch (e) {
        console.error('Posts scraping error:', e);
        sendResponse({ success: false, error: 'SCRAPE_ERROR' });
      }
    })();

    return true; // keep channel open for async
  }

  return false;
>>>>>>> Stashed changes
});
