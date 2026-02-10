function isProfilePage(url) {
  return /^https:\/\/www\.linkedin\.com\/in\/[^/?#]+\/?$/.test(url.split(/[?#]/)[0]);
}

function getActivityCategory(url) {
  const clean = url.split(/[?#]/)[0];
  const m = clean.match(/\/recent-activity\/([^/]+)\/?$/);
  return m ? m[1].toLowerCase() : null; // all, comments, videos, images, reactions
}

function isAnyActivityPage(url) {
  return /^https:\/\/www\.linkedin\.com\/in\/[^/]+\/recent-activity\/[^/]+\/?/.test(
    url.split(/[?#]/)[0]
  );
}

function safeText(el) {
  return el ? (el.innerText || '').trim() : '';
}

function normalizeText(s) {
  return (s || '').replace(/\s+/g, ' ').trim();
}

function parseCountFromText(text) {
  if (!text) return null;
  const t = text.replace(/,/g, '').trim();

  // e.g. "500+ connections"
  const plus = t.match(/(\d+)\+/);
  if (plus) return parseInt(plus[1], 10);

  // e.g. "1.2K"
  const km = t.match(/(\d+(?:\.\d+)?)([KMB])/i);
  if (km) {
    const n = parseFloat(km[1]);
    const u = km[2].toUpperCase();
    const mul = u === 'K' ? 1000 : u === 'M' ? 1000000 : 1000000000;
    return Math.round(n * mul);
  }

  const n = t.match(/(\d+)/);
  return n ? parseInt(n[1], 10) : null;
}

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
    search_appearances: 0
  };

  try {
    const cleanUrl = window.location.href.split(/[?#]/)[0].replace(/\/+$/, '/');
    profile.public_url = cleanUrl;

    const idMatch = cleanUrl.match(/\/in\/([^/]+)/);
    if (idMatch) profile.linkedin_id = idMatch[1];

    // Name
    const nameEl =
      document.querySelector('h1.text-heading-xlarge') ||
      document.querySelector('.pv-text-details__left-panel h1') ||
      document.querySelector('main h1');

    profile.name = normalizeText(safeText(nameEl));

    // Headline
    const headlineEl =
      document.querySelector('.pv-text-details__left-panel .text-body-medium') ||
      document.querySelector('div.text-body-medium.break-words') ||
      document.querySelector('section div.text-body-medium');

    profile.headline = normalizeText(safeText(headlineEl));

    // Location
    const locEl =
      document.querySelector('.pv-text-details__left-panel span.text-body-small.inline.t-black--light.break-words') ||
      document.querySelector('span.text-body-small.inline.t-black--light.break-words') ||
      document.querySelector('span.text-body-small.inline.t-black--light');

    profile.location = normalizeText(safeText(locEl));

    // Profile image
    const imgEl =
      document.querySelector('.pv-top-card-profile-picture__image') ||
      document.querySelector('.pv-top-card__photo img') ||
      document.querySelector('img.profile-photo-edit__preview') ||
      document.querySelector('img.pv-top-card-profile-picture__image');

    if (imgEl) {
      profile.profile_image_url = imgEl.src || imgEl.getAttribute('src') || '';
    }

    // Connections / followers: use top card link list
    const topStats = Array.from(
      document.querySelectorAll(
        'a[data-field="topcard_connection"], a[data-field="topcard_followers"], .pv-top-card--list li, .pv-top-card--list-bullet li'
      )
    )
      .map(el => normalizeText(safeText(el)))
      .filter(Boolean);

    topStats.forEach(text => {
      const val = parseCountFromText(text);
      if (val == null) return;

      if (/connection/i.test(text)) profile.connections_count = Math.max(profile.connections_count, val);
      if (/follower/i.test(text)) profile.followers_count = Math.max(profile.followers_count, val);
    });

    // Fallback: scan visible text snippets
    if (!profile.connections_count || !profile.followers_count) {
      const bodyText = document.body ? document.body.innerText : '';
      const conn = bodyText.match(/(\d[\d,\.KMB\+]+)\s+connections?/i);
      const fol = bodyText.match(/(\d[\d,\.KMB\+]+)\s+followers?/i);
      if (conn) profile.connections_count = parseCountFromText(conn[1]) || profile.connections_count;
      if (fol) profile.followers_count = parseCountFromText(fol[1]) || profile.followers_count;
    }

    // Profile views & search appearances: best effort (LinkedIn often hides these)
    const bodyText = document.body ? document.body.innerText : '';
    const viewsMatch = bodyText.match(/([\d,]+)\s+profile views?/i);
    if (viewsMatch) profile.profile_views = parseInt(viewsMatch[1].replace(/,/g, ''), 10) || 0;

    const searchMatch = bodyText.match(/([\d,]+)\s+search appearances?/i);
    if (searchMatch) profile.search_appearances = parseInt(searchMatch[1].replace(/,/g, ''), 10) || 0;
  } catch (err) {
    console.error('LinkInsight profile scraping error:', err);
  }

  return profile;
}

async function autoScroll(maxScrolls = 8, delayMs = 1000) {
  let lastHeight = document.body.scrollHeight;
  for (let i = 0; i < maxScrolls; i++) {
    window.scrollTo(0, document.body.scrollHeight);
    await new Promise(r => setTimeout(r, delayMs));
    const newHeight = document.body.scrollHeight;
    if (newHeight === lastHeight) break;
    lastHeight = newHeight;
  }
}

function clickSeeMore() {
  const buttons = Array.from(document.querySelectorAll('button'));
  buttons.forEach(btn => {
    const t = (btn.innerText || '').trim().toLowerCase();
    if (t === 'see more' || t.includes('see more')) {
      btn.click();
    }
  });
}

function detectMediaType(postEl) {
  const hasVideo = !!postEl.querySelector('video, .update-components-video, .feed-shared-video');
  const hasImg = !!postEl.querySelector('img[alt], .feed-shared-image, .update-components-image');
  if (hasVideo && hasImg) return 'mixed';
  if (hasVideo) return 'video';
  if (hasImg) return 'image';
  return 'text';
}

function extractExternalId(postEl) {
  const urn = postEl.getAttribute('data-urn') || postEl.getAttribute('data-id') || '';
  if (urn) return urn;

  // fallback: try to locate any element carrying an urn
  const any = postEl.querySelector('[data-urn*="urn:li:"]');
  return any ? any.getAttribute('data-urn') : '';
}

function extractPermalink(postEl, externalId) {
  // Try explicit links first
  const link =
    postEl.querySelector('a[href*="/feed/update/"]') ||
    postEl.querySelector('a[href*="/posts/"]') ||
    postEl.querySelector('a[href*="/activity/"]');

  if (link && link.href) return link.href.split(/[?#]/)[0];

  // Fallback build from URN
  if (externalId && externalId.startsWith('urn:')) {
    return `https://www.linkedin.com/feed/update/${encodeURIComponent(externalId)}/`;
  }

  return window.location.href.split(/[?#]/)[0];
}

function extractRelativeTime(postEl) {
  // LinkedIn often keeps full time in visually-hidden spans
  const hidden =
    postEl.querySelector('.visually-hidden') ||
    postEl.querySelector('span.visually-hidden');

  const hiddenText = normalizeText(safeText(hidden));
  if (hiddenText && /ago$/i.test(hiddenText)) return hiddenText;

  // fallback: try common containers
  const timeEl =
    postEl.querySelector('span.update-components-actor__sub-description') ||
    postEl.querySelector('.update-components-actor__sub-description') ||
    postEl.querySelector('time');

  return normalizeText(safeText(timeEl));
}

function extractCounts(postEl) {
  let reactions = 0;
  let comments = 0;
  let reposts = 0;
  let impressions = null;

  // Reactions
  const reactionsBtn =
    postEl.querySelector('button[aria-label*="reaction"]') ||
    postEl.querySelector('span[aria-label*="reaction"]');

  if (reactionsBtn) {
    const label = reactionsBtn.getAttribute('aria-label') || '';
    const m = label.match(/([\d,]+)\s+reaction/i);
    if (m) reactions = parseInt(m[1].replace(/,/g, ''), 10) || 0;
  }

  // Comments
  const commentsBtn =
    postEl.querySelector('button[aria-label*="comment"]') ||
    postEl.querySelector('span[aria-label*="comment"]');

  if (commentsBtn) {
    const label = commentsBtn.getAttribute('aria-label') || '';
    const m = label.match(/([\d,]+)\s+comment/i);
    if (m) comments = parseInt(m[1].replace(/,/g, ''), 10) || 0;
  }

  // Reposts
  const repostsBtn =
    postEl.querySelector('button[aria-label*="repost"]') ||
    postEl.querySelector('span[aria-label*="repost"]');

  if (repostsBtn) {
    const label = repostsBtn.getAttribute('aria-label') || '';
    const m = label.match(/([\d,]+)\s+repost/i);
    if (m) reposts = parseInt(m[1].replace(/,/g, ''), 10) || 0;
  }

  // Impressions/views sometimes show as "123 views" in spans
  const spans = postEl.querySelectorAll('span[aria-hidden="true"], span');
  Array.from(spans).some(sp => {
    const t = (sp.innerText || '').trim().toLowerCase();
    if (!t) return false;
    if (t.includes('view') || t.includes('impression')) {
      const m = sp.innerText.match(/([\d,]+)\s+(view|views|impression|impressions)/i);
      if (m) {
        impressions = parseInt(m[1].replace(/,/g, ''), 10) || 0;
        return true;
      }
    }
    return false;
  });

  return { reactions, comments, reposts, impressions };
}

function extractContent(postEl) {
  const contentContainer =
    postEl.querySelector('.feed-shared-inline-show-more-text') ||
    postEl.querySelector('.update-components-text') ||
    postEl.querySelector('.feed-shared-update-v2__commentary') ||
    postEl.querySelector('[data-test-id="main-feed-activity-card__commentary"]');

  return normalizeText(safeText(contentContainer));
}

function detectPostTypeFromCategory(activityCategory, postEl) {
  if (!activityCategory) return 'post';

  if (activityCategory === 'comments') return 'comment';
  if (activityCategory === 'reactions') return 'reaction';
  if (activityCategory === 'videos') return 'video';
  if (activityCategory === 'images') return 'image';

  // all: infer from media
  const media = detectMediaType(postEl);
  if (media === 'video') return 'video';
  if (media === 'image') return 'image';
  return 'post';
}

async function scrapeActivityData() {
  await autoScroll(10, 1200);
  clickSeeMore();

  const activityCategory = getActivityCategory(window.location.href);
  const items = [];

  // Activity feed containers typically include data-urn activities
  const candidates = document.querySelectorAll(
    '[data-urn*="urn:li:activity:"], div[role="region"][data-urn*="urn:li:activity:"]'
  );

  const seen = new Set();

  candidates.forEach(postEl => {
    const externalId = extractExternalId(postEl);
    if (!externalId || seen.has(externalId)) return;
    seen.add(externalId);

    const content = extractContent(postEl);
    const postedAtHuman = extractRelativeTime(postEl);
    const mediaType = detectMediaType(postEl);
    const postType = detectPostTypeFromCategory(activityCategory, postEl);

    const { reactions, comments, reposts, impressions } = extractCounts(postEl);

    const permalink = extractPermalink(postEl, externalId);

    // For comments/reactions pages, often there is a “target” post link. Best-effort.
    let targetPermalink = null;
    if (activityCategory === 'comments' || activityCategory === 'reactions') {
      const targetLink =
        postEl.querySelector('a[href*="/feed/update/"]') ||
        postEl.querySelector('a[href*="/posts/"]') ||
        postEl.querySelector('a[href*="/activity/"]');
      if (targetLink && targetLink.href) targetPermalink = targetLink.href.split(/[?#]/)[0];
    }

    items.push({
      external_id: externalId,
      post_type: postType,
      media_type: mediaType,
      content,
      posted_at_human: postedAtHuman,
      impressions,
      reactions,
      comments,
      reposts,
      permalink,
      target_permalink: targetPermalink
    });
  });

  return { activity_category: activityCategory || 'all', posts: items };
}

chrome.runtime.onMessage.addListener((msg, sender, sendResponse) => {
  if (!msg || !msg.action) return false;

  const url = window.location.href;

  if (msg.action === 'scrapeProfile') {
    if (!isProfilePage(url)) {
      sendResponse({ success: false, error: 'NOT_PROFILE_PAGE' });
      return true;
    }

    try {
      const profile = scrapeProfileData();
      sendResponse({ success: true, profile });
    } catch (e) {
      console.error('Profile scraping error:', e);
      sendResponse({ success: false, error: 'SCRAPE_ERROR' });
    }
    return true;
  }

  if (msg.action === 'scrapeActivity') {
    if (!isAnyActivityPage(url)) {
      sendResponse({ success: false, error: 'NOT_ACTIVITY_PAGE' });
      return true;
    }

    (async () => {
      try {
        const data = await scrapeActivityData();
        if (!data.posts.length) {
          sendResponse({ success: false, error: 'NO_POSTS' });
        } else {
          sendResponse({ success: true, ...data });
        }
      } catch (e) {
        console.error('Activity scraping error:', e);
        sendResponse({ success: false, error: 'SCRAPE_ERROR' });
      }
    })();

    return true;
  }

  return false;
});
