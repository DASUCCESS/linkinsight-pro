document.addEventListener('DOMContentLoaded', () => {
  const apiBaseUrlInput = document.getElementById('apiBaseUrl');
  const apiTokenInput = document.getElementById('apiToken');
  const saveBtn = document.getElementById('saveBtn');
  const fetchBtn = document.getElementById('fetchBtn');
  const statusEl = document.getElementById('status');

  function setStatus(text, type) {
    if (!statusEl) return;
    statusEl.textContent = text || '';
    statusEl.classList.remove('error', 'success');
    if (type) statusEl.classList.add(type);
  }

  function detectDefaultBaseUrl() {
    return new Promise(resolve => {
      chrome.tabs.query({ active: true, currentWindow: true }, tabs => {
        if (!tabs || !tabs.length) {
          resolve(null);
          return;
        }
        try {
          const url = new URL(tabs[0].url);
          if (url.protocol === 'http:' || url.protocol === 'https:') {
            resolve(url.origin);
          } else {
            resolve(null);
          }
        } catch {
          resolve(null);
        }
      });
    });
  }

  // Load stored values
  chrome.storage.sync.get(['li_api_base_url', 'li_api_token'], async result => {
    if (result.li_api_base_url) {
      apiBaseUrlInput.value = result.li_api_base_url;
    } else {
      const detected = await detectDefaultBaseUrl();
      apiBaseUrlInput.value = detected || 'http://127.0.0.1:8000';
    }

    if (result.li_api_token) {
      apiTokenInput.value = result.li_api_token;
    }
  });

  // Save manually
  saveBtn.addEventListener('click', () => {
    const baseUrl = apiBaseUrlInput.value.trim().replace(/\/+$/, '');
    const token = apiTokenInput.value.trim();

    chrome.storage.sync.set(
      {
        li_api_base_url: baseUrl,
        li_api_token: token || null
      },
      () => {
        setStatus('Settings saved.', 'success');
        setTimeout(() => setStatus('', null), 2000);
      }
    );
  });

  // Fetch token from app (expects /extension/api-token endpoint with cookie auth)
  fetchBtn.addEventListener('click', () => {
    const baseUrl = apiBaseUrlInput.value.trim().replace(/\/+$/, '');
    if (!baseUrl) {
      setStatus('Set API base URL first.', 'error');
      return;
    }

    setStatus('Requesting token from app...', null);

    fetch(baseUrl + '/extension/api-token', {
      method: 'GET',
      credentials: 'include'
    })
      .then(async resp => {
        const data = await resp.json().catch(() => null);
        if (!resp.ok) {
          const msg = data && data.message ? data.message : 'Failed to fetch token.';
          throw new Error(msg);
        }
        const token = data.token;
        apiTokenInput.value = token;

        chrome.storage.sync.set({ li_api_token: token }, () => {
          setStatus('Token fetched and saved.', 'success');
          setTimeout(() => setStatus('', null), 2500);
        });
      })
      .catch(err => {
        setStatus(
          err.message || 'Error fetching token. Make sure you are logged into the app.',
          'error'
        );
      });
  });
});
