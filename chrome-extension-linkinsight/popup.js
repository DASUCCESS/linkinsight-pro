function getActiveTab(callback) {
  chrome.tabs.query({ active: true, currentWindow: true }, tabs => {
    if (!tabs || !tabs.length) {
      callback(null);
      return;
    }
    callback(tabs[0]);
  });
}

function detectPageType(url) {
  const clean = (url || '').split(/[?#]/)[0];

  // Profile root: https://www.linkedin.com/in/<handle>/
  if (/^https:\/\/www\.linkedin\.com\/in\/[^/]+\/?$/.test(clean)) {
    return 'Profile';
  }

  // Any activity route: https://www.linkedin.com/in/<handle>/recent-activity/<category>/
  // category examples: all, posts, comments, reactions, videos, images
  if (/^https:\/\/www\.linkedin\.com\/in\/[^/]+\/recent-activity\/[^/]+\/?/.test(clean)) {
    return 'Profile activity';
  }

  if (/^https:\/\/www\.linkedin\.com\//.test(clean)) {
    return 'LinkedIn page';
  }

  return 'Not LinkedIn';
}

document.addEventListener('DOMContentLoaded', () => {
  // UI references
  const currentUrlText = document.getElementById('currentUrlText');
  const pageTypeBadge = document.getElementById('pageTypeBadge');
  const apiStatusText = document.getElementById('apiStatusText');
  const statusEl = document.getElementById('status');

  const openProfileBtn = document.getElementById('openProfileBtn');
  const openActivityBtn = document.getElementById('openActivityBtn');
  const syncProfileBtn = document.getElementById('syncProfileBtn');
  const syncPostsBtn = document.getElementById('syncPostsBtn');
  const openOptionsLink = document.getElementById('openOptions');

  const profileSummaryDiv = document.getElementById('profileSummary');
  const postsSummaryDiv = document.getElementById('postsSummary');

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
              const msg =
                (data && (data.message || data.error)) || `API error (status ${resp.status})`;
              reject(msg);
            } else {
              resolve(data || {});
            }
          })
          .catch(err => reject(err.message || 'Network error'));
      });
    });
  }

  // Initial API config status
  chrome.storage.sync.get(['li_api_base_url', 'li_api_token'], cfg => {
    if (cfg.li_api_base_url && cfg.li_api_token) {
      apiStatusText.textContent = 'API: ready';
    } else {
      apiStatusText.textContent = 'API: configure';
      apiStatusText.classList.add('error');
    }
  });

  // Detect current tab + context (UPDATED enable/disable logic for "Profile activity")
  getActiveTab(tab => {
    if (!tab) {
      currentUrlText.textContent = 'No active tab';
      pageTypeBadge.textContent = 'No tab';
      syncProfileBtn.disabled = true;
      syncPostsBtn.disabled = true;
      return;
    }

    const url = tab.url || '';
    currentUrlText.textContent = url;
    currentPageType = detectPageType(url);
    pageTypeBadge.textContent = currentPageType;

    if (currentPageType === 'Profile') {
      syncProfileBtn.disabled = false;
      syncPostsBtn.disabled = true;
    } else if (currentPageType === 'Profile activity') {
      syncProfileBtn.disabled = true;
      syncPostsBtn.disabled = false;
    } else {
      syncProfileBtn.disabled = true;
      syncPostsBtn.disabled = true;
    }
  });

  // Open LinkedIn / Activity buttons
  if (openProfileBtn) {
    openProfileBtn.addEventListener('click', () => {
      getActiveTab(tab => {
        if (!tab) return;
        const url = tab.url || '';
        const type = detectPageType(url);

        if (type === 'Profile') {
          return;
        }
        if (type === 'Profile activity') {
          const profileUrl = url.split('/detail/')[0].split('/recent-activity')[0];
          chrome.tabs.update(tab.id, { url: profileUrl });
          return;
        }
        chrome.tabs.update(tab.id, { url: 'https://www.linkedin.com/feed/' });
      });
    });
  }

  // UPDATED Open activity button to your real routes
  if (openActivityBtn) {
    openActivityBtn.addEventListener('click', () => {
      getActiveTab(tab => {
        if (!tab) return;
        const url = (tab.url || '').split(/[?#]/)[0];
        const profileMatch = url.match(/https:\/\/www\.linkedin\.com\/in\/[^/]+\/?/);
        let baseProfileUrl = profileMatch ? profileMatch[0] : 'https://www.linkedin.com/in/';
        if (!baseProfileUrl.endsWith('/')) baseProfileUrl += '/';

        const activityUrl = baseProfileUrl + 'recent-activity/all/';
        chrome.tabs.update(tab.id, { url: activityUrl });
      });
    });
  }

  // Open options / API settings
  if (openOptionsLink) {
    openOptionsLink.addEventListener('click', e => {
      e.preventDefault();
      if (chrome.runtime.openOptionsPage) {
        chrome.runtime.openOptionsPage();
      } else {
        window.open(chrome.runtime.getURL('options.html'));
      }
    });
  }

  // ----- Sync profile -----
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

        chrome.tabs.sendMessage(tab.id, { action: 'scrapeProfile' }, response => {
          if (chrome.runtime.lastError) {
            const msg = chrome.runtime.lastError.message || 'Unknown error.';
            setStatus(
              'Could not read this tab. Make sure a LinkedIn profile page is open, then try again. (' +
                msg +
                ')',
              'error'
            );
            syncProfileBtn.disabled = false;
            return;
          }

          if (!response || !response.success) {
            const code = response && response.error;
            let friendly =
              'Could not read your LinkedIn profile. Open your profile (URL like https://www.linkedin.com/in/your-handle) and try again.';
            if (code === 'NOT_PROFILE_PAGE') {
              friendly =
                'Open your LinkedIn public profile page (URL starting with https://www.linkedin.com/in/...) in the active tab, then click “Sync profile” again.';
            } else if (code === 'SCRAPE_ERROR') {
              friendly =
                'LinkedIn did not load fully or changed layout. Refresh your profile page, then try “Sync profile” again.';
            }
            setStatus(friendly, 'error');
            syncProfileBtn.disabled = false;
            return;
          }

          const profile = response.profile || {};
          document.getElementById('profileName').innerText = profile.name || '';
          document.getElementById('profileHeadline').innerText = profile.headline || '';
          document.getElementById('profileLocation').innerText = profile.location || '';
          document.getElementById('profileConnections').innerText =
            profile.connections_count ?? '0';
          document.getElementById('profileFollowers').innerText =
            profile.followers_count ?? '0';
          document.getElementById('profileViews').innerText =
            profile.profile_views ?? '0';
          document.getElementById('profileSearchAppearances').innerText =
            profile.search_appearances ?? '0';
          document.getElementById('profilePostsCount').innerText =
            profile.posts_count ?? '0';
          profileSummaryDiv.style.display = 'block';

          setStatus('Sending profile to LinkInsight Pro...', 'info');

          postToApi('/api/linkedin/sync/profile', profile)
            .then(() => {
              setStatus('Profile synced successfully.', 'success');
            })
            .catch(err => {
              setStatus('Profile sync failed: ' + err, 'error');
            })
            .finally(() => {
              syncProfileBtn.disabled = false;
            });
        });
      });
    });
  }

  // ----- Sync posts/activity -----
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

        // UPDATED: scrapePosts -> scrapeActivity
        chrome.tabs.sendMessage(tab.id, { action: 'scrapeActivity' }, response => {
          if (chrome.runtime.lastError) {
            const msg = chrome.runtime.lastError.message || 'Unknown error.';
            setStatus(
              'The extension could not read this tab. Refresh your Activity page, then try again. (' +
                msg +
                ')',
              'error'
            );
            syncPostsBtn.disabled = false;
            return;
          }

          if (!response || !response.success) {
            const code = response && response.error;
            let friendly =
              'Could not read your LinkedIn activity. Open your profile → Activity page, then try again.';
            if (code === 'NOT_ACTIVITY_PAGE') {
              friendly =
                'Open your LinkedIn profile → Activity (URL containing /recent-activity/), then click “Sync posts” again.';
            } else if (code === 'NO_POSTS') {
              friendly =
                'No items were detected on this page. Scroll down to load more, then click “Sync posts” again.';
            } else if (code === 'SCRAPE_ERROR') {
              friendly =
                'LinkedIn did not load fully or changed layout. Refresh your Activity page, then try again.';
            }
            setStatus(friendly, 'error');
            syncPostsBtn.disabled = false;
            return;
          }

          const posts = response.posts || [];
          if (!posts.length) {
            setStatus(
              'No items detected on this page. Scroll down to load more, then click “Sync posts” again.',
              'error'
            );
            syncPostsBtn.disabled = false;
            return;
          }

          if (profileSummaryDiv.style.display === 'block') {
            document.getElementById('profilePostsCount').innerText = posts.length;
          }

          postsSummaryDiv.innerHTML = '';
          posts.forEach(post => {
            const item = document.createElement('div');
            item.className = 'postSummaryItem';
            const preview =
              post.content && post.content.length > 140
                ? post.content.slice(0, 140) + '…'
                : post.content || '(no text)';

            item.innerHTML = `
              <p><strong>${post.posted_at_human || 'Item'}:</strong> ${preview}</p>
              <p>
                👍 ${post.reactions ?? 0}
                &nbsp; 💬 ${post.comments ?? 0}
                ${post.reposts != null ? `&nbsp; 🔁 ${post.reposts}` : ''}
                ${post.impressions != null ? `&nbsp; 👁️ ${post.impressions}` : ''}
              </p>
              <hr />
            `;
            postsSummaryDiv.appendChild(item);
          });
          postsSummaryDiv.style.display = 'block';

          setStatus(`Collected ${posts.length} items. Sending to LinkInsight Pro...`, 'info');

          // UPDATED: send category to backend
          const activityCategory = response.activity_category || 'all';
          postToApi('/api/linkedin/sync/posts', { posts, activity_category: activityCategory })
            .then(() => {
              setStatus(`Activity synced successfully (${posts.length} items).`, 'success');
            })
            .catch(err => {
              setStatus('Activity sync failed: ' + err, 'error');
            })
            .finally(() => {
              syncPostsBtn.disabled = false;
            });
        });
      });
    });
  }
});
