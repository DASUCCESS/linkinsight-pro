function getActiveTab(callback) {
  chrome.tabs.query({ active: true, currentWindow: true }, tabs => {
    if (!tabs || !tabs.length) {
      callback(null);
      return;
    }
    callback(tabs[0]);
  });
}

/**
 * Keep query string, remove hash only.
 * Needed because LinkedIn demographic pages rely on ?metricType=...
 */
function stripHash(url) {
  return (url || '').split('#')[0];
}

/**
 * Remove both query and hash.
 * Useful when you want a stable "page" URL without params.
 */
function stripQueryAndHash(url) {
  return (url || '').split(/[?#]/)[0];
}

/**
 * Normalize LinkedIn URLs while preserving query string (default).
 * - forces https
 * - normalizes host to www.linkedin.com
 * - removes trailing slashes on pathname (except root "/")
 */
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
    // fallback (best-effort)
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

function detectPageType(url) {
  const u = normalizeLinkedInUrl(url, { keepQuery: true });
  const pathOnly = getPathOnly(u);

  // Profile root: https://www.linkedin.com/in/<id>/
  if (/^https:\/\/www\.linkedin\.com\/in\/[^/]+\/?$/.test(pathOnly + '/')) return 'Profile';

  // Any profile activity route: /in/<id>/recent-activity/<category>/
  if (/^https:\/\/www\.linkedin\.com\/in\/[^/]+\/recent-activity\/[^/]+\/?$/.test(pathOnly + '/')) return 'Profile activity';

  // Demographic detail (followers)
  if (/^https:\/\/www\.linkedin\.com\/analytics\/demographic-detail\/urn:li:fsd_profile:profile\/?$/.test(pathOnly)) {
    const sp = getSearchParams(u);
    const metricType = (sp.get('metricType') || '').toUpperCase();
    if (metricType === 'MEMBER_FOLLOWERS') return 'Followers demographics';
    return 'Demographic detail';
  }

  // Connections list
  if (/^https:\/\/www\.linkedin\.com\/mynetwork\/invite-connect\/connections\/?$/.test(pathOnly)) return 'Connections';

  if (/^https:\/\/www\.linkedin\.com\//.test(u)) return 'LinkedIn page';

  return 'Not LinkedIn';
}

document.addEventListener('DOMContentLoaded', () => {
  const currentUrlText = document.getElementById('currentUrlText');
  const pageTypeBadge = document.getElementById('pageTypeBadge');
  const apiStatusText = document.getElementById('apiStatusText');
  const statusEl = document.getElementById('status');

  const refreshBtn = document.getElementById('refreshBtn');

  const openProfileBtn = document.getElementById('openProfileBtn');
  const openActivityBtn = document.getElementById('openActivityBtn');
  const openDemographicsBtn = document.getElementById('openDemographicsBtn');
  const openConnectionsBtn = document.getElementById('openConnectionsBtn');

  const syncProfileBtn = document.getElementById('syncProfileBtn');
  const syncPostsBtn = document.getElementById('syncPostsBtn');
  const syncDemographicsBtn = document.getElementById('syncDemographicsBtn');
  const syncConnectionsBtn = document.getElementById('syncConnectionsBtn');

  const openOptionsLink = document.getElementById('openOptions');
  const autoRefreshOnOpenEl = document.getElementById('autoRefreshOnOpen');
  const connectionsAutoScrollEl = document.getElementById('connectionsAutoScroll');

  const profileSummaryDiv = document.getElementById('profileSummary');
  const postsSummaryDiv = document.getElementById('postsSummary');
  const extraSummaryDiv = document.getElementById('extraSummary');

  let currentPageType = 'Unknown';

  function setStatus(text, type) {
    statusEl.textContent = text || '';
    statusEl.classList.remove('error', 'success', 'info');
    if (type) statusEl.classList.add(type);
  }

  function withApiConfig(cb) {
    chrome.storage.sync.get(['li_api_base_url', 'li_api_token'], cfg => {
      const baseUrl = (cfg.li_api_base_url || '').trim().replace(/\/+$/, '');
      const token = (cfg.li_api_token || '').trim();

      if (!baseUrl) {
        setStatus('Set your API base URL in “API settings” first.', 'error');
        apiStatusText.textContent = 'API: missing URL';
        apiStatusText.classList.add('error');
        return;
      }
      if (!token) {
        setStatus('Fetch an API token in “API settings” first.', 'error');
        apiStatusText.textContent = 'API: missing token';
        apiStatusText.classList.add('error');
        return;
      }

      apiStatusText.textContent = 'API: ready';
      apiStatusText.classList.remove('error');
      cb({ baseUrl, token });
    });
  }

  function postToApi(path, payload) {
    return new Promise((resolve, reject) => {
      withApiConfig(({ baseUrl, token }) => {
        fetch(baseUrl + path, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: 'Bearer ' + token
          },
          body: JSON.stringify(payload)
        })
          .then(async resp => {
            let data = null;
            try {
              data = await resp.json();
            } catch {
              data = null;
            }
            if (!resp.ok) {
              const msg = (data && (data.message || data.error)) || `API error (status ${resp.status})`;
              reject(msg);
            } else {
              resolve(data || {});
            }
          })
          .catch(err => reject(err.message || 'Network error'));
      });
    });
  }

  function ensureProfileUrlSavedFromTab(tabUrl) {
    const raw = stripQueryAndHash(tabUrl);
    const match = raw.match(/https:\/\/www\.linkedin\.com\/in\/[^/]+\/?/);
    if (match) {
      let profileUrl = match[0];
      if (!profileUrl.endsWith('/')) profileUrl += '/';
      chrome.storage.sync.set({ li_profile_url: profileUrl });
      return profileUrl;
    }
    return null;
  }

  function getSavedProfileUrl(cb) {
    chrome.storage.sync.get(['li_profile_url'], cfg => {
      const u = (cfg.li_profile_url || '').trim();
      cb(u);
    });
  }

  function getProfileUrlForPayload(tabUrl, cb) {
    getSavedProfileUrl(saved => {
      if (saved) {
        cb(saved);
        return;
      }
      const derived = ensureProfileUrlSavedFromTab(tabUrl || '');
      cb(derived || null);
    });
  }

  // Initial API status
  chrome.storage.sync.get(['li_api_base_url', 'li_api_token'], cfg => {
    if (cfg.li_api_base_url && cfg.li_api_token) {
      apiStatusText.textContent = 'API: ready';
    } else {
      apiStatusText.textContent = 'API: configure';
      apiStatusText.classList.add('error');
    }
  });

  function setButtonStatesByPageType(pt) {
    syncProfileBtn.disabled = true;
    syncPostsBtn.disabled = true;
    syncDemographicsBtn.disabled = true;
    syncConnectionsBtn.disabled = true;

    if (pt === 'Profile') syncProfileBtn.disabled = false;
    if (pt === 'Profile activity') syncPostsBtn.disabled = false;
    if (pt === 'Followers demographics' || pt === 'Demographic detail') syncDemographicsBtn.disabled = false;
    if (pt === 'Connections') syncConnectionsBtn.disabled = false;
  }

  function updateContextFromActiveTab() {
    getActiveTab(tab => {
      if (!tab) {
        currentUrlText.textContent = 'No active tab';
        pageTypeBadge.textContent = 'No tab';
        setButtonStatesByPageType('None');
        return;
      }

      const url = tab.url || '';
      currentUrlText.textContent = url;

      currentPageType = detectPageType(url);
      pageTypeBadge.textContent = currentPageType;

      ensureProfileUrlSavedFromTab(url);
      setButtonStatesByPageType(currentPageType);
    });
  }

  function openOrUpdateTab(targetUrl) {
    chrome.storage.sync.get(['li_auto_refresh_on_open'], cfg => {
      const autoRefresh = !!cfg.li_auto_refresh_on_open;

      getActiveTab(tab => {
        const done = (tabId) => {
          if (!autoRefresh || !tabId) return;
          setTimeout(() => {
            chrome.tabs.reload(tabId, {}, () => setTimeout(updateContextFromActiveTab, 700));
          }, 900);
        };

        if (!tab) {
          chrome.tabs.create({ url: targetUrl }, (newTab) => done(newTab?.id));
        } else {
          chrome.tabs.update(tab.id, { url: targetUrl }, (updated) => done(updated?.id || tab.id));
        }
      });
    });
  }

  chrome.storage.sync.get(['li_auto_refresh_on_open', 'li_connections_auto_scroll'], cfg => {
    if (autoRefreshOnOpenEl) autoRefreshOnOpenEl.checked = !!cfg.li_auto_refresh_on_open;
    if (connectionsAutoScrollEl) connectionsAutoScrollEl.checked = cfg.li_connections_auto_scroll !== false;
  });

  if (autoRefreshOnOpenEl) autoRefreshOnOpenEl.addEventListener('change', () => chrome.storage.sync.set({ li_auto_refresh_on_open: !!autoRefreshOnOpenEl.checked }));
  if (connectionsAutoScrollEl) connectionsAutoScrollEl.addEventListener('change', () => chrome.storage.sync.set({ li_connections_auto_scroll: !!connectionsAutoScrollEl.checked }));

  // Detect current tab (initial load)
  updateContextFromActiveTab();

  // Refresh tab
  if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
      setStatus('Refreshing tab...', 'info');
      refreshBtn.disabled = true;

      getActiveTab(tab => {
        if (!tab || !tab.id) {
          setStatus('No active tab to refresh.', 'error');
          refreshBtn.disabled = false;
          return;
        }

        chrome.tabs.reload(tab.id, {}, () => {
          // Give LinkedIn a moment to re-render before we re-detect
          setTimeout(() => {
            updateContextFromActiveTab();
            setStatus('Tab refreshed. If LinkedIn finished loading, sync now.', 'success');
            refreshBtn.disabled = false;
          }, 900);
        });
      });
    });
  }

  // Open options
  if (openOptionsLink) {
    openOptionsLink.addEventListener('click', e => {
      e.preventDefault();
      if (chrome.runtime.openOptionsPage) chrome.runtime.openOptionsPage();
      else window.open(chrome.runtime.getURL('options.html'));
    });
  }

  // Open profile
  if (openProfileBtn) {
    openProfileBtn.addEventListener('click', () => {
      getSavedProfileUrl(profileUrl => {
        const targetUrl = profileUrl || 'https://www.linkedin.com/in/me/';
        openOrUpdateTab(targetUrl);
      });
    });
  }

  // Open activity
  if (openActivityBtn) {
    openActivityBtn.addEventListener('click', () => {
      getSavedProfileUrl(profileUrl => {
        const base = profileUrl || 'https://www.linkedin.com/in/';
        const activityUrl = base.endsWith('/') ? base + 'recent-activity/all/' : base + '/recent-activity/all/';
        openOrUpdateTab(activityUrl);
      });
    });
  }
  // Open followers demographics detail
  if (openDemographicsBtn) {
    openDemographicsBtn.addEventListener('click', () => {
      const targetUrl =
        'https://www.linkedin.com/analytics/demographic-detail/urn:li:fsd_profile:profile/?metricType=MEMBER_FOLLOWERS';
      openOrUpdateTab(targetUrl);
    });
  }

  // Open connections
  if (openConnectionsBtn) {
    openConnectionsBtn.addEventListener('click', () => {
      const targetUrl = 'https://www.linkedin.com/mynetwork/invite-connect/connections/';
      openOrUpdateTab(targetUrl);
    });
  }

  function showExtraSummary(lines) {
    extraSummaryDiv.innerHTML = '';
    (lines || []).forEach(t => {
      const p = document.createElement('p');
      p.textContent = t;
      extraSummaryDiv.appendChild(p);
    });
    extraSummaryDiv.style.display = 'block';
  }

  // Sync profile
  if (syncProfileBtn) {
    syncProfileBtn.addEventListener('click', () => {
      setStatus('Reading profile from the current tab...', 'info');
      syncProfileBtn.disabled = true;

      getActiveTab(tab => {
        if (!tab) {
          setStatus('No active tab. Open your LinkedIn profile first.', 'error');
          syncProfileBtn.disabled = false;
          return;
        }

        ensureProfileUrlSavedFromTab(tab.url || '');

        chrome.tabs.sendMessage(tab.id, { action: 'scrapeProfile' }, response => {
          if (chrome.runtime.lastError) {
            setStatus('Could not read this tab. Refresh the page and try again.', 'error');
            syncProfileBtn.disabled = false;
            return;
          }

          if (!response || !response.success) {
            const code = response && response.error;
            let friendly = 'Could not read your LinkedIn profile. Open your profile page and try again.';
            if (code === 'NOT_PROFILE_PAGE') friendly = 'Open your LinkedIn profile (linkedin.com/in/...) then try again.';
            if (code === 'SCRAPE_ERROR') friendly = 'LinkedIn changed layout or did not load fully. Refresh and try again.';
            setStatus(friendly, 'error');
            syncProfileBtn.disabled = false;
            return;
          }

          const profile = response.profile || {};
          document.getElementById('profileName').innerText = profile.name || '';
          document.getElementById('profileHeadline').innerText = profile.headline || '';
          document.getElementById('profileLocation').innerText = profile.location || '';
          document.getElementById('profileConnections').innerText = profile.connections_count ?? '0';
          document.getElementById('profileFollowers').innerText = profile.followers_count ?? '0';
          document.getElementById('profileViews').innerText = profile.profile_views ?? '0';
          document.getElementById('profileSearchAppearances').innerText = profile.search_appearances ?? '0';
          profileSummaryDiv.style.display = 'block';

          setStatus('Sending profile to LinkInsight Pro...', 'info');

          postToApi('/api/linkedin/sync/profile', profile)
            .then(() => setStatus('Profile synced successfully.', 'success'))
            .catch(err => setStatus('Profile sync failed: ' + err, 'error'))
            .finally(() => {
              syncProfileBtn.disabled = false;
            });
        });
      });
    });
  }

  // Sync posts/activity
  if (syncPostsBtn) {
    syncPostsBtn.addEventListener('click', () => {
      setStatus('Reading activity from the current tab...', 'info');
      syncPostsBtn.disabled = true;

      getActiveTab(tab => {
        if (!tab) {
          setStatus('No active tab. Open your Activity page first.', 'error');
          syncPostsBtn.disabled = false;
          return;
        }

        chrome.tabs.sendMessage(tab.id, { action: 'scrapeActivity' }, response => {
          if (chrome.runtime.lastError) {
            setStatus('The extension could not read this tab. Refresh and try again.', 'error');
            syncPostsBtn.disabled = false;
            return;
          }

          if (!response || !response.success) {
            const code = response && response.error;
            let friendly = 'Could not read your LinkedIn activity. Open your profile → Activity page and try again.';
            if (code === 'NOT_ACTIVITY_PAGE') friendly = 'Open a /recent-activity/ page then try again.';
            if (code === 'NO_POSTS') friendly = 'No items were detected. Scroll to load, then try again.';
            if (code === 'SCRAPE_ERROR') friendly = 'LinkedIn changed layout or did not load fully. Refresh and try again.';
            setStatus(friendly, 'error');
            syncPostsBtn.disabled = false;
            return;
          }

          const posts = response.posts || [];
          if (!posts.length) {
            setStatus('No items detected. Scroll to load more, then try again.', 'error');
            syncPostsBtn.disabled = false;
            return;
          }

          postsSummaryDiv.innerHTML = '';
          posts.slice(0, 10).forEach(post => {
            const item = document.createElement('div');
            item.className = 'postSummaryItem';
            const preview = post.content && post.content.length > 140 ? post.content.slice(0, 140) + '…' : post.content || '(no text)';
            item.innerHTML = `
              <p><strong>${post.posted_at_human || 'Item'}:</strong> ${preview}</p>
              <p>👍 ${post.reactions ?? 0} &nbsp; 💬 ${post.comments ?? 0} &nbsp; 🔁 ${post.reposts ?? 0} ${post.impressions != null ? `&nbsp; 👁️ ${post.impressions}` : ''}</p>
              <hr />
            `;
            postsSummaryDiv.appendChild(item);
          });
          postsSummaryDiv.style.display = 'block';

          setStatus(`Collected ${posts.length} items. Sending to LinkInsight Pro...`, 'info');

          const activityCategory = response.activity_category || 'all';
          postToApi('/api/linkedin/sync/posts', {
            posts,
            activity_category: activityCategory,
            public_url: response.public_url || null
          })
            .then(() => setStatus(`Activity synced successfully (${posts.length} items).`, 'success'))
            .catch(err => setStatus('Activity sync failed: ' + err, 'error'))
            .finally(() => {
              syncPostsBtn.disabled = false;
            });
        });
      });
    });
  }
  // Sync followers demographics -> /linkedin/sync/audience-demographics
  if (syncDemographicsBtn) {
    syncDemographicsBtn.addEventListener('click', () => {
      setStatus('Reading followers demographics from this tab...', 'info');
      syncDemographicsBtn.disabled = true;

      getActiveTab(tab => {
        if (!tab) {
          setStatus('No active tab. Open the demographics page first.', 'error');
          syncDemographicsBtn.disabled = false;
          return;
        }

        chrome.tabs.sendMessage(tab.id, { action: 'scrapeFollowersDemographics' }, response => {
          if (chrome.runtime.lastError) {
            setStatus('Could not read this tab. Refresh and try again.', 'error');
            syncDemographicsBtn.disabled = false;
            return;
          }

          if (!response || !response.success) {
            const code = response && response.error;
            let friendly = 'Could not read demographics. Refresh and try again.';
            if (code === 'NOT_DEMOGRAPHICS_PAGE') friendly = 'Open the demographic-detail page with metricType=MEMBER_FOLLOWERS, then try again.';
            if (code === 'NO_DATA') friendly = 'No demographics blocks detected. Scroll a bit and try again.';
            setStatus(friendly, 'error');
            syncDemographicsBtn.disabled = false;
            return;
          }

          const payload = response.payload || {};
          const cats = payload.demographics ? Object.keys(payload.demographics) : [];

          showExtraSummary([`Snapshot date: ${payload.snapshot_date}`, `Categories: ${cats.join(', ') || '(none)'}`]);

          getProfileUrlForPayload(tab.url || '', publicUrl => {
            setStatus('Sending demographics to LinkInsight Pro...', 'info');

            postToApi('/api/linkedin/sync/audience-demographics', {
              public_url: publicUrl,
              snapshot_date: payload.snapshot_date,
              followers_count: payload.followers_count || 0,
              demographics: payload.demographics || {}
            })
              .then(() => setStatus('Demographics synced successfully.', 'success'))
              .catch(err => setStatus('Demographics sync failed: ' + err, 'error'))
              .finally(() => {
                syncDemographicsBtn.disabled = false;
              });
          });
        });
      });
    });
  }

  // Sync connections -> /linkedin/sync/connections
  if (syncConnectionsBtn) {
    syncConnectionsBtn.addEventListener('click', () => {
      setStatus('Reading connections from this tab...', 'info');
      syncConnectionsBtn.disabled = true;

      getActiveTab(tab => {
        if (!tab) {
          setStatus('No active tab. Open your connections page first.', 'error');
          syncConnectionsBtn.disabled = false;
          return;
        }

        chrome.storage.sync.get(['li_connections_auto_scroll'], cfg => {
          const options = {
            autoPaginate: false,
            autoScrollEnabled: cfg.li_connections_auto_scroll !== false,
            maxPages: 1,
          };

          chrome.tabs.sendMessage(tab.id, { action: 'scrapeConnections', options }, response => {
          if (chrome.runtime.lastError) {
            setStatus('Could not read this tab. Refresh and try again.', 'error');
            syncConnectionsBtn.disabled = false;
            return;
          }

          if (!response || !response.success) {
            const code = response && response.error;
            let friendly = 'Could not read connections. Refresh and try again.';
            if (code === 'NOT_CONNECTIONS_PAGE') friendly = 'Open https://www.linkedin.com/mynetwork/invite-connect/connections/ then try again.';
            if (code === 'NO_DATA') friendly = 'No connections detected. Scroll to load more, then try again.';
            setStatus(friendly, 'error');
            syncConnectionsBtn.disabled = false;
            return;
          }

          const payload = response.payload || {};
          const count = payload.connections ? payload.connections.length : 0;
          showExtraSummary([`Detected connections: ${count}`]);

          getProfileUrlForPayload(tab.url || '', publicUrl => {
            setStatus(`Sending ${count} connections to LinkInsight Pro...`, 'info');

            postToApi('/api/linkedin/sync/connections', {
              public_url: publicUrl,
              connections: payload.connections || []
            })
              .then(() => setStatus(`Connections synced successfully (${count}).`, 'success'))
              .catch(err => setStatus('Connections sync failed: ' + err, 'error'))
              .finally(() => {
                syncConnectionsBtn.disabled = false;
              });
          });
          });
        });
      });
    });
  }
});
