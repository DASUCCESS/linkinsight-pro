function stripHash(url) {
  return (url || '').split('#')[0];
}

function stripQueryAndHash(url) {
  return (url || '').split(/[?#]/)[0];
}

function normalizeLinkedInUrl(url, { keepQuery = true } = {}) {
  let raw = (url || '').trim();
  if (!raw) return '';

  raw = raw.replace(/^http:\/\//i, 'https://');

  try {
    const u = new URL(raw);

    const host = u.hostname.toLowerCase();
    if (host === 'm.linkedin.com' || host === 'mobile.linkedin.com' || host === 'linkedin.com') {
      u.hostname = 'www.linkedin.com';
    }

    u.hash = '';

    if (u.pathname && u.pathname !== '/') {
      u.pathname = u.pathname.replace(/\/+$/, '');
    }

    if (!keepQuery) u.search = '';

    return u.toString();
  } catch {
    let out = stripHash(raw);
    out = out.replace(/^https:\/\/(m|mobile)\.linkedin\.com/i, 'https://www.linkedin.com');
    out = out.replace(/^https:\/\/linkedin\.com/i, 'https://www.linkedin.com');
    if (!keepQuery) out = stripQueryAndHash(out);
    out = out.replace(/\/+$/, '');
    return out;
  }
}

function getPathOnly(url) {
  const u = normalizeLinkedInUrl(url, { keepQuery: true });
  try {
    const x = new URL(u);
    return `${x.origin}${x.pathname}`.replace(/\/+$/, '');
  } catch {
    return stripQueryAndHash(u).replace(/\/+$/, '');
  }
}

function getSearchParams(url) {
  const u = normalizeLinkedInUrl(url, { keepQuery: true });
  try {
    return new URL(u).searchParams;
  } catch {
    return new URLSearchParams((u.split('?')[1] || '').split('#')[0]);
  }
}

function cleanUrl(url) {
  return stripQueryAndHash(url);
}

function isProfilePage(url) {
  const pathOnly = getPathOnly(url);
  return /^https:\/\/www\.linkedin\.com\/in\/[^/?#]+\/?$/.test(pathOnly + '/');
}

function getActivityCategory(url) {
  const pathOnly = getPathOnly(url);
  const m = (pathOnly + '/').match(/\/recent-activity\/([^/]+)\/$/);
  return m ? m[1].toLowerCase() : null;
}

function isAnyActivityPage(url) {
  const pathOnly = getPathOnly(url);
  return /^https:\/\/www\.linkedin\.com\/in\/[^/]+\/recent-activity\/[^/]+\/?$/.test(pathOnly + '/');
}

function isCreatorContentPage(url) {
  const pathOnly = getPathOnly(url);
  return /^https:\/\/www\.linkedin\.com\/analytics\/creator\/content\/?$/.test(pathOnly);
}

function isCreatorAudiencePage(url) {
  const pathOnly = getPathOnly(url);
  return /^https:\/\/www\.linkedin\.com\/analytics\/creator\/audience\/?$/.test(pathOnly);
}

function isFollowersDemographicsPage(url) {
  const pathOnly = getPathOnly(url);
  if (!/^https:\/\/www\.linkedin\.com\/analytics\/demographic-detail\/urn:li:fsd_profile:profile\/?$/.test(pathOnly)) return false;
  const sp = getSearchParams(url);
  return (sp.get('metricType') || '').toUpperCase() === 'MEMBER_FOLLOWERS';
}

function isConnectionsPage(url) {
  const pathOnly = getPathOnly(url);
  return /^https:\/\/www\.linkedin\.com\/mynetwork\/invite-connect\/connections\/?$/.test(pathOnly);
}

function safeText(el) {
  return el ? (el.innerText || '').trim() : '';
}

function normalizeText(s) {
  return (s || '').replace(/\s+/g, ' ').trim();
}

function getMetaContent(selector) {
  const el = document.querySelector(selector);
  if (!el) return '';
  const v = el.getAttribute('content') || '';
  return normalizeText(v);
}

function pickText(selectors) {
  for (const sel of selectors) {
    const el = document.querySelector(sel);
    const txt = normalizeText(safeText(el));
    if (txt) return txt;
  }
  return '';
}

function pickAttr(selectors, attr) {
  for (const sel of selectors) {
    const el = document.querySelector(sel);
    if (!el) continue;
    const v = el.getAttribute(attr) || (attr === 'src' ? el.src : null);
    if (v) return v;
  }
  return '';
}

function parseCountFromText(text) {
  if (!text) return null;
  const t = text.replace(/,/g, '').trim();

  const plus = t.match(/(\d+)\+/);
  if (plus) return parseInt(plus[1], 10);

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

function parsePercent(text) {
  const m = (text || '').replace(',', '.').match(/(\d+(?:\.\d+)?)\s*%/);
  if (!m) return null;
  return parseFloat(m[1]);
}

async function autoScroll(maxScrolls = 10, delayMs = 1000) {
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
    if (t === 'see more' || t.includes('see more')) btn.click();
  });
}

/**
 * Scrape profile data, with robust fallbacks for name, headline and location.
 */
function scrapeProfileData() {
  const profile = {
    linkedin_id: '',
    public_url: '',
    name: '',
    headline: '',
    location: '',
    profile_image_url: '',
    industry: '',
    connections_count: 0,
    followers_count: 0,
    profile_views: 0,
    search_appearances: 0
  };

  try {
    const pathOnly = getPathOnly(window.location.href);
    const clean = (pathOnly + '/').replace(/\/+$/, '/');
    profile.public_url = clean;

    const idMatch = clean.match(/\/in\/([^/]+)/);
    if (idMatch) profile.linkedin_id = idMatch[1];

    // NAME
    let nameEl =
      document.querySelector('h1.text-heading-xlarge') ||
      document.querySelector('.pv-text-details__left-panel h1') ||
      document.querySelector('main h1');

    if (!nameEl) {
      nameEl =
        document.querySelector('main h1 span[dir="ltr"]') ||
        document.querySelector('h1 span[dir="ltr"]');
    }

    profile.name = normalizeText(safeText(nameEl));

    if (!profile.name) {
      const ogTitle = getMetaContent('meta[property="og:title"]');
      if (ogTitle) {
        profile.name = ogTitle.split('|')[0].trim();
      }
    }

    if (!profile.name && document.title) {
      profile.name = document.title.split('|')[0].trim();
    }

    // HEADLINE
    let headlineEl =
      document.querySelector('[data-test-id="profile-hero-headline"]') ||
      document.querySelector('.pv-text-details__left-panel .text-body-medium') ||
      document.querySelector('div.text-body-medium.break-words') ||
      document.querySelector('section div.text-body-medium');

    profile.headline = normalizeText(safeText(headlineEl));

    if (!profile.headline) {
      const ogDesc =
        getMetaContent('meta[property="og:description"]') ||
        getMetaContent('meta[name="description"]');

      if (ogDesc) {
        const parts = ogDesc.split('|').map(s => s.trim()).filter(Boolean);
        if (parts.length) {
          profile.headline = parts[0];
        } else {
          profile.headline = ogDesc;
        }
      }
    }

    // LOCATION
    let locEl =
      document.querySelector('[data-test-id="profile-hero-primary-location"]') ||
      document.querySelector('[data-test-id="profile-location"]') ||
      document.querySelector('.pv-text-details__left-panel span.text-body-small.inline.t-black--light.break-words') ||
      document.querySelector('.pv-text-details__left-panel span.text-body-small.inline.break-words') ||
      document.querySelector('.pv-text-details__left-panel p.text-body-small.inline.break-words') ||
      document.querySelector('section div span.text-body-small.inline.t-black--light.break-words') ||
      document.querySelector('section div p.text-body-small.inline.break-words');

    profile.location = normalizeText(safeText(locEl));

    if (!profile.location) {
      const ogDesc =
        getMetaContent('meta[property="og:description"]') ||
        getMetaContent('meta[name="description"]');

      if (ogDesc) {
        const parts = ogDesc.split('|').map(s => s.trim()).filter(Boolean);
        if (parts.length > 1) {
          const candidate = parts[parts.length - 1];
          if (candidate && /[A-Za-z]/.test(candidate)) {
            profile.location = candidate;
          }
        }
      }
    }

    // INDUSTRY
    const industryEl =
      document.querySelector('li[data-test-id="profile-industry"] span') ||
      document.querySelector('.pv-text-details__right-panel li span') ||
      document.querySelector('.pv-top-card__list-item span') ||
      null;
    profile.industry = normalizeText(safeText(industryEl));

    // PROFILE IMAGE
    const imgEl =
      document.querySelector('.pv-top-card-profile-picture__image') ||
      document.querySelector('.pv-top-card__photo img') ||
      document.querySelector('img.profile-photo-edit__preview') ||
      document.querySelector('img.pv-top-card-profile-picture__image');

    if (imgEl) {
      profile.profile_image_url = imgEl.src || imgEl.getAttribute('src') || '';
    }

    // CONNECTIONS / FOLLOWERS
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

    if (!profile.connections_count || !profile.followers_count) {
      const bodyText = document.body ? document.body.innerText : '';
      const conn = bodyText.match(/(\d[\d,\.KMB\+]+)\s+connections?/i);
      const fol = bodyText.match(/(\d[\d,\.KMB\+]+)\s+followers?/i);
      if (conn) profile.connections_count = parseCountFromText(conn[1]) || profile.connections_count;
      if (fol) profile.followers_count = parseCountFromText(fol[1]) || profile.followers_count;
    }

    // PROFILE VIEWS / SEARCH APPEARANCES
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
  const any = postEl.querySelector('[data-urn*="urn:li:"]');
  return any ? any.getAttribute('data-urn') : '';
}

function extractPermalink(postEl, externalId) {
  const link =
    postEl.querySelector('a[href*="/feed/update/"]') ||
    postEl.querySelector('a[href*="/posts/"]') ||
    postEl.querySelector('a[href*="/activity/"]');

  if (link && link.href) return cleanUrl(link.href);

  if (externalId && externalId.startsWith('urn:')) {
    return `https://www.linkedin.com/feed/update/${encodeURIComponent(externalId)}/`;
  }

  return cleanUrl(window.location.href);
}

function extractRelativeTime(postEl) {
  const hidden = postEl.querySelector('.visually-hidden') || postEl.querySelector('span.visually-hidden');
  const hiddenText = normalizeText(safeText(hidden));
  if (hiddenText && /ago$/i.test(hiddenText)) return hiddenText;

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

  const reactionsBtn =
    postEl.querySelector('button[aria-label*="reaction"]') ||
    postEl.querySelector('span[aria-label*="reaction"]');

  if (reactionsBtn) {
    const label = reactionsBtn.getAttribute('aria-label') || '';
    const m = label.match(/([\d,]+)\s+reaction/i);
    if (m) reactions = parseInt(m[1].replace(/,/g, ''), 10) || 0;
  }

  const commentsBtn =
    postEl.querySelector('button[aria-label*="comment"]') ||
    postEl.querySelector('span[aria-label*="comment"]');

  if (commentsBtn) {
    const label = commentsBtn.getAttribute('aria-label') || '';
    const m = label.match(/([\d,]+)\s+comment/i);
    if (m) comments = parseInt(m[1].replace(/,/g, ''), 10) || 0;
  }

  const repostsBtn =
    postEl.querySelector('button[aria-label*="repost"]') ||
    postEl.querySelector('span[aria-label*="repost"]');

  if (repostsBtn) {
    const label = repostsBtn.getAttribute('aria-label') || '';
    const m = label.match(/([\d,]+)\s+repost/i);
    if (m) reposts = parseInt(m[1].replace(/,/g, ''), 10) || 0;
  }

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

    let targetPermalink = null;
    if (activityCategory === 'comments' || activityCategory === 'reactions') {
      const targetLink =
        postEl.querySelector('a[href*="/feed/update/"]') ||
        postEl.querySelector('a[href*="/posts/"]') ||
        postEl.querySelector('a[href*="/activity/"]');
      if (targetLink && targetLink.href) targetPermalink = cleanUrl(targetLink.href);
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

  const pathOnly = getPathOnly(window.location.href);
  const m = (pathOnly + '/').match(/https:\/\/www\.linkedin\.com\/in\/[^/]+\/?/);
  const publicUrl = m ? (m[0].endsWith('/') ? m[0] : m[0] + '/') : null;

  return { public_url: publicUrl, activity_category: activityCategory || 'all', posts: items };
}

function guessTodayDateISO() {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
}

function textToIsoDateMaybe(text) {
  const t = normalizeText(text);
  const d = new Date(t);
  if (!isNaN(d.getTime())) return d.toISOString();
  return null;
}

function extractFirstLinkedInPostUrlFromRow(row) {
  const a =
    row.querySelector('a[href*="/feed/update/"]') ||
    row.querySelector('a[href*="/posts/"]') ||
    row.querySelector('a[href*="/activity/"]');
  return a && a.href ? cleanUrl(a.href) : null;
}

function extractLinkedInPostIdFromUrl(url) {
  if (!url) return null;
  const m = url.match(/\/feed\/update\/([^/]+)\//);
  if (m) return decodeURIComponent(m[1]);
  return url;
}

function parseMetricFromAnyText(text, keyHints) {
  const t = (text || '').toLowerCase();
  const hit = keyHints.some(h => t.includes(h));
  if (!hit) return null;
  const n = parseCountFromText(text);
  return n == null ? null : n;
}

async function scrapeCreatorContentAnalytics() {
  await autoScroll(6, 900);

  const metricDate = guessTodayDateISO();

  const rows = Array.from(document.querySelectorAll('[role="row"]')).filter(r => r.querySelector('[role="cell"]'));
  const posts = [];

  for (const row of rows) {
    const rowText = normalizeText(row.innerText || '');
    if (!rowText) continue;

    const permalink = extractFirstLinkedInPostUrlFromRow(row);
    if (!permalink) continue;

    const cells = Array.from(row.querySelectorAll('[role="cell"]'));
    const cellTexts = cells.map(c => normalizeText(c.innerText || '')).filter(Boolean);

    const impressions = cellTexts.map(t => parseCountFromText(t)).find(n => n != null) ?? 0;

    let clicks = 0,
      reactions = 0,
      comments = 0,
      reposts = 0,
      saves = 0,
      videoViews = 0;

    const chunks = cellTexts.length ? cellTexts : [rowText];

    chunks.forEach(t => {
      const vClicks = parseMetricFromAnyText(t, ['click']);
      if (vClicks != null) clicks = Math.max(clicks, vClicks);

      const vReactions = parseMetricFromAnyText(t, ['reaction', 'like']);
      if (vReactions != null) reactions = Math.max(reactions, vReactions);

      const vComments = parseMetricFromAnyText(t, ['comment']);
      if (vComments != null) comments = Math.max(comments, vComments);

      const vReposts = parseMetricFromAnyText(t, ['repost', 'share']);
      if (vReposts != null) reposts = Math.max(reposts, vReposts);

      const vSaves = parseMetricFromAnyText(t, ['save']);
      if (vSaves != null) saves = Math.max(saves, vSaves);

      const vVV = parseMetricFromAnyText(t, ['video view', 'views']);
      if (vVV != null) videoViews = Math.max(videoViews, vVV);
    });

    let postedAtIso = null;
    for (const t of chunks) {
      const iso = textToIsoDateMaybe(t);
      if (iso) {
        postedAtIso = iso;
        break;
      }
    }
    if (!postedAtIso) postedAtIso = new Date().toISOString();

    const linkedinPostId = extractLinkedInPostIdFromUrl(permalink) || permalink;
    const excerpt = rowText.length > 400 ? rowText.slice(0, 400) : rowText;

    posts.push({
      linkedin_post_id: String(linkedinPostId).slice(0, 191),
      permalink,
      posted_at: postedAtIso,
      post_type: 'post',
      is_reshare: false,
      is_sponsored: false,
      content_excerpt: excerpt,
      metrics: {
        metric_date: metricDate,
        impressions: impressions || 0,
        unique_impressions: 0,
        clicks: clicks || 0,
        reactions: reactions || 0,
        comments: comments || 0,
        reposts: reposts || 0,
        saves: saves || 0,
        video_views: videoViews || 0,
        follows_from_post: 0,
        profile_visits_from_post: 0,
        engagement_rate: 0
      }
    });
  }

  return {
    metric_date: metricDate,
    posts: posts.slice(0, 80)
  };
}

async function scrapeCreatorAudienceAnalytics() {
  await autoScroll(4, 900);

  const metricDate = guessTodayDateISO();
  const data = {};

  const kpiCandidates = Array.from(document.querySelectorAll('section, div'))
    .filter(el => {
      const t = normalizeText(el.innerText || '');
      return t.length > 0 && t.length < 200 && (t.includes('%') || /\b\d[\d,\.KMB]*\b/.test(t));
    })
    .slice(0, 200);

  kpiCandidates.forEach(el => {
    const t = normalizeText(el.innerText || '');
    if (!t) return;

    const lines = t.split('\n').map(normalizeText).filter(Boolean);
    if (!lines.length) return;

    const joined = lines.join(' ');
    const num = parseCountFromText(joined);
    const pct = parsePercent(joined);

    const keyBase = joined
      .replace(/[\d,\.KMB\+]+/gi, '')
      .replace(/%/g, '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '_')
      .slice(0, 50);

    if (!keyBase || keyBase.length < 3) return;

    if (pct != null) data[keyBase + '_pct'] = pct;
    if (num != null) data[keyBase] = num;
  });

  return { metric_date: metricDate, data };
}

function normalizeCategoryKey(label) {
  const t = (label || '').toLowerCase();
  if (t.includes('job title')) return 'job_title';
  if (t.includes('location')) return 'location';
  if (t.includes('industry')) return 'industry';
  if (t.includes('seniority')) return 'seniority';
  if (t.includes('company size')) return 'company_size';
  if (t === 'company' || t.includes('company')) return 'company';
  return t.replace(/\s+/g, '_').replace(/[^\w_]/g, '').slice(0, 40) || 'unknown';
}

function extractDemographicsFromJson() {
  const html = document.documentElement.innerHTML || '';
  if (!html) return {};

  const text = html
    .replace(/&quot;/g, '"')
    .replace(/&amp;/g, '&');

  const demographics = {};

  const chartRe = /{"dataPoints":\[(.+?)\],"title":\{"textDirection":"USER_LOCALE","text":"([^"]+)"[\s\S]*?},"category":/g;

  let match;
  while ((match = chartRe.exec(text)) !== null) {
    const pointsRaw = match[1];
    const headingText = match[2];

    let points;
    try {
      points = JSON.parse(`[${pointsRaw}]`);
    } catch (e) {
      continue;
    }

    const key = normalizeCategoryKey(headingText);
    const items = [];

    for (const p of points) {
      if (!p || !p.xLabel || !p.xLabel.text) continue;

      const label = String(p.xLabel.text).trim();
      if (!label) continue;

      let percent = null;

      if (typeof p.yPercent === 'number') {
        percent = Math.round(p.yPercent * 1000) / 10;
      } else if (p.yFormattedValue && typeof p.yFormattedValue.text === 'string') {
        percent = parsePercent(p.yFormattedValue.text);
      }

      if (percent == null || isNaN(percent)) continue;

      items.push({ label, percent });
    }

    if (items.length) {
      demographics[key] = items;
    }
  }

  return demographics;
}

function collectLinesToItems(lines, maxItems = 25) {
  const items = [];

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const pct = parsePercent(line);

    if (pct != null) {
      let name = line.replace(/(\d+(?:\.\d+)?)\s*%/, '').trim();
      if (name && name.length > 1 && name !== '<' && name !== '-') {
        items.push({ label: name, percent: pct });
      }
      continue;
    }

    const next = lines[i + 1] || '';
    const pct2 = parsePercent(next);
    if (pct2 != null) {
      const name = line.trim();
      if (name && name.length > 1 && name !== '<' && name !== '-') {
        items.push({ label: name, percent: pct2 });
      }
      i++;
    }
  }

  return items.slice(0, maxItems);
}

async function scrapeFollowersDemographics() {
  await autoScroll(6, 900);

  const snapshotDate = guessTodayDateISO();
  let demographics = {};

  demographics = extractDemographicsFromJson();

  if (!Object.keys(demographics).length) {
    const headings = Array.from(document.querySelectorAll('h1,h2,h3,div,span'))
      .filter(el => {
        const t = normalizeText(el.innerText || '');
        return ['Job title', 'Location', 'Industry', 'Seniority', 'Company size', 'Company'].includes(t);
      })
      .slice(0, 50);

    function collectItemsAround(el) {
      const container = el.closest('section') || el.parentElement || document.body;
      const lines = (container.innerText || '')
        .split('\n')
        .map(normalizeText)
        .filter(Boolean);

      return collectLinesToItems(lines, 25);
    }

    headings.forEach(h => {
      const key = normalizeCategoryKey(normalizeText(h.innerText || ''));
      const items = collectItemsAround(h);
      if (items.length) demographics[key] = items;
    });
  }

  if (!Object.keys(demographics).length) {
    const lines = (document.body.innerText || '')
      .split('\n')
      .map(normalizeText)
      .filter(Boolean);

    const items = collectLinesToItems(lines, 50);
    if (items.length) demographics.unknown = items;
  }

  let followersCount = 0;
  const bodyText = document.body ? document.body.innerText : '';
  const m = bodyText.match(/(\d[\d,\.KMB\+]+)\s+followers?/i);
  if (m) followersCount = parseCountFromText(m[1]) || 0;

  return {
    snapshot_date: snapshotDate,
    followers_count: followersCount,
    demographics
  };
}

function extractPublicIdentifierFromProfileUrl(url) {
  if (!url) return null;
  const u = cleanUrl(url);
  const m = u.match(/\/in\/([^/]+)/);
  return m ? m[1] : null;
}

async function scrapeConnectionsDirectory() {
  await autoScroll(12, 900);

  const profileLinks = Array.from(document.querySelectorAll('a[href*="linkedin.com/in/"]'))
    .map(a => a.href)
    .filter(Boolean);

  const uniqueLinks = Array.from(new Set(profileLinks.map(cleanUrl))).slice(0, 1200);

  const connections = [];
  const seen = new Set();

  uniqueLinks.forEach(url => {
    if (seen.has(url)) return;
    seen.add(url);

    const a = Array.from(document.querySelectorAll(`a[href="${url}"], a[href="${url}/"]`))[0];
    const card = a ? (a.closest('li') || a.closest('div')) : null;

    let fullName = null;
    let headline = null;
    let location = null;
    let img = null;

    if (card) {
      const txt = (card.innerText || '')
        .split('\n')
        .map(normalizeText)
        .filter(Boolean);

      if (txt.length) fullName = txt[0];
      if (txt.length > 1) headline = txt[1];

      if (txt.length > 2) {
        location = txt[2];
      }

      if (!location) {
        location = txt.find((t, idx) => idx > 0 && /,\s*\S+/.test(t)) || null;
      }

      const imgEl = card.querySelector('img');
      if (imgEl) img = imgEl.src || imgEl.getAttribute('src') || null;
    }

    const publicId = extractPublicIdentifierFromProfileUrl(url);

    connections.push({
      linkedin_connection_id: null,
      public_identifier: publicId,
      profile_url: url,
      full_name: fullName,
      headline: headline,
      location: location,
      industry: null,
      profile_image_url: img,
      degree: null,
      mutual_connections_count: null,
      connected_at: null,
      last_seen_at: null
    });
  });

  return connections;
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
        if (!data.posts.length) sendResponse({ success: false, error: 'NO_POSTS' });
        else sendResponse({ success: true, ...data });
      } catch (e) {
        console.error('Activity scraping error:', e);
        sendResponse({ success: false, error: 'SCRAPE_ERROR' });
      }
    })();

    return true;
  }

  if (msg.action === 'scrapeCreatorContent') {
    if (!isCreatorContentPage(url)) {
      sendResponse({ success: false, error: 'NOT_CREATOR_CONTENT_PAGE' });
      return true;
    }

    (async () => {
      try {
        const payload = await scrapeCreatorContentAnalytics();
        if (!payload.posts || !payload.posts.length) {
          sendResponse({ success: false, error: 'NO_DATA' });
          return;
        }

        sendResponse({
          success: true,
          payload: {
            public_url: null,
            metric_date: payload.metric_date,
            posts: payload.posts
          }
        });
      } catch (e) {
        console.error('Creator content scraping error:', e);
        sendResponse({ success: false, error: 'SCRAPE_ERROR' });
      }
    })();

    return true;
  }

  if (msg.action === 'scrapeCreatorAudience') {
    if (!isCreatorAudiencePage(url)) {
      sendResponse({ success: false, error: 'NOT_CREATOR_AUDIENCE_PAGE' });
      return true;
    }

    (async () => {
      try {
        const out = await scrapeCreatorAudienceAnalytics();
        const has = out.data && Object.keys(out.data).length;
        if (!has) {
          sendResponse({ success: false, error: 'NO_DATA' });
          return;
        }
        sendResponse({
          success: true,
          payload: {
            public_url: null,
            metrics: {
              metric_date: out.metric_date,
              data: out.data
            }
          }
        });
      } catch (e) {
        console.error('Creator audience scraping error:', e);
        sendResponse({ success: false, error: 'SCRAPE_ERROR' });
      }
    })();

    return true;
  }

  if (msg.action === 'scrapeFollowersDemographics') {
    if (!isFollowersDemographicsPage(url)) {
      sendResponse({ success: false, error: 'NOT_DEMOGRAPHICS_PAGE' });
      return true;
    }

    (async () => {
      try {
        const out = await scrapeFollowersDemographics();
        const has = out.demographics && Object.keys(out.demographics).length;
        if (!has) {
          sendResponse({ success: false, error: 'NO_DATA' });
          return;
        }
        sendResponse({
          success: true,
          payload: {
            public_url: null,
            snapshot_date: out.snapshot_date,
            followers_count: out.followers_count || 0,
            demographics: out.demographics
          }
        });
      } catch (e) {
        console.error('Demographics scraping error:', e);
        sendResponse({ success: false, error: 'SCRAPE_ERROR' });
      }
    })();

    return true;
  }

  if (msg.action === 'scrapeConnections') {
    if (!isConnectionsPage(url)) {
      sendResponse({ success: false, error: 'NOT_CONNECTIONS_PAGE' });
      return true;
    }

    (async () => {
      try {
        const connections = await scrapeConnectionsDirectory();
        if (!connections.length) {
          sendResponse({ success: false, error: 'NO_DATA' });
          return;
        }
        sendResponse({
          success: true,
          payload: {
            public_url: null,
            connections
          }
        });
      } catch (e) {
        console.error('Connections scraping error:', e);
        sendResponse({ success: false, error: 'SCRAPE_ERROR' });
      }
    })();

    return true;
  }

  return false;
});
