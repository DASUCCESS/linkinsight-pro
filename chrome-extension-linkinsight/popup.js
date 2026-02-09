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
    if (/https:\/\/www\.linkedin\.com\/in\//.test(url)) {
        if (/\/recent-activity/.test(url)) {
            return 'Profile activity (posts)';
        }
        return 'Profile';
    }
    if (/https:\/\/www\.linkedin\.com\//.test(url)) {
        return 'LinkedIn page';
    }
    return 'Not LinkedIn';
}

function setStatus(text, type) {
    const el = document.getElementById('status');
    el.textContent = text || '';
    el.classList.remove('error', 'success');
    if (type) {
        el.classList.add(type);
    }
}

function withApiConfig(callback) {
    chrome.storage.sync.get(['li_api_base_url', 'li_api_token'], result => {
        const baseUrl = (result.li_api_base_url || '').trim().replace(/\/+$/, '');
        const token = (result.li_api_token || '').trim();

        if (!baseUrl || !token) {
            setStatus('Configure API base URL and token first.', 'error');
            return;
        }

        callback({ baseUrl, token });
    });
}

function postToApi(path, payload) {
    return new Promise((resolve, reject) => {
        withApiConfig(({ baseUrl, token }) => {
            fetch(baseUrl + path, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(payload)
            })
                .then(async resp => {
                    const data = await resp.json().catch(() => null);
                    if (!resp.ok) {
                        const err = data && data.message ? data.message : 'API error';
                        reject(err);
                        return;
                    }
                    resolve(data);
                })
                .catch(err => reject(err.message || 'Network error'));
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const currentUrlText = document.getElementById('currentUrlText');
    const pageTypeText = document.getElementById('pageTypeText');
    const syncProfileBtn = document.getElementById('syncProfileBtn');
    const syncPostsBtn = document.getElementById('syncPostsBtn');
    const openOptions = document.getElementById('openOptions');

    getActiveTab(tab => {
        if (!tab) {
            currentUrlText.textContent = 'No active tab';
            pageTypeText.textContent = '-';
            return;
        }
        currentUrlText.textContent = tab.url;
        pageTypeText.textContent = detectPageType(tab.url);
    });

    syncProfileBtn.addEventListener('click', () => {
        setStatus('Scraping profile...', null);
        getActiveTab(tab => {
            if (!tab) {
                setStatus('No active tab.', 'error');
                return;
            }

            chrome.tabs.sendMessage(
                tab.id,
                { type: 'SCRAPE_PROFILE' },
                response => {
                    if (chrome.runtime.lastError) {
                        setStatus('Cannot access this page. Is it a LinkedIn profile?', 'error');
                        return;
                    }
                    if (!response || !response.success) {
                        setStatus(response && response.error ? response.error : 'Scrape failed.', 'error');
                        return;
                    }

                    setStatus('Sending profile to API...', null);
                    postToApi('/api/linkedin/sync/profile', response.data)
                        .then(() => setStatus('Profile synced successfully.', 'success'))
                        .catch(err => setStatus('API error: ' + err, 'error'));
                }
            );
        });
    });

    syncPostsBtn.addEventListener('click', () => {
        setStatus('Scraping posts...', null);
        getActiveTab(tab => {
            if (!tab) {
                setStatus('No active tab.', 'error');
                return;
            }

            chrome.tabs.sendMessage(
                tab.id,
                { type: 'SCRAPE_POSTS' },
                response => {
                    if (chrome.runtime.lastError) {
                        setStatus('Cannot access this page. Open the recent-activity page.', 'error');
                        return;
                    }
                    if (!response || !response.success) {
                        setStatus(response && response.error ? response.error : 'Scrape failed.', 'error');
                        return;
                    }

                    const posts = response.data || [];
                    if (!posts.length) {
                        setStatus('No posts detected on this page.', 'error');
                        return;
                    }

                    setStatus('Sending posts to API (' + posts.length + ')...', null);
                    postToApi('/api/linkedin/sync/posts', { posts })
                        .then(() => setStatus('Posts synced successfully.', 'success'))
                        .catch(err => setStatus('API error: ' + err, 'error'));
                }
            );
        });
    });

    openOptions.addEventListener('click', e => {
        e.preventDefault();
        if (chrome.runtime.openOptionsPage) {
            chrome.runtime.openOptionsPage();
        } else {
            window.open(chrome.runtime.getURL('options.html'));
        }
    });
});
