// job-detail.js
const API_URL = '/backend/api/jobs.php';
const jobDetail = document.getElementById('jobDetail');
const jobLoading = document.getElementById('jobLoading');
const jobContent = document.getElementById('jobContent');

function getJobId() {
  const params = new URLSearchParams(window.location.search);
  return params.get('id');
}

// Show loading state
function showLoading() {
  jobLoading.style.display = 'flex';
  jobContent.style.display = 'none';
  jobContent.innerHTML = '';
  
  // Update loading text to be more specific
  const loadingText = jobLoading.querySelector('.loading-text');
  if (loadingText) {
    loadingText.textContent = 'Loading job details...';
  }
}

// Hide loading state
function hideLoading() {
  jobLoading.style.display = 'none';
  jobContent.style.display = 'block';
}

// simple HTML escape to avoid XSS
function escapeHtml(input) {
  if (input === undefined || input === null) return '';
  return String(input)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

function listHtml(arr) {
  if (!arr || arr.length === 0) return '<li class="no-data">Not specified</li>';
  
  // Handle both arrays and comma-separated strings
  let items = arr;
  if (typeof arr === 'string') {
    items = arr.split(',').map(item => item.trim()).filter(item => item.length > 0);
  } else if (!Array.isArray(arr)) {
    return '<li class="no-data">Not specified</li>';
  }
  
  if (items.length === 0) return '<li class="no-data">Not specified</li>';
  
  return items.map(item => `<li>${escapeHtml(item)}</li>`).join('');
}

function formatDate(dateString) {
  if (!dateString) return 'Not specified';
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  } catch (e) {
    return dateString;
  }
}

function renderJob(job) {
  if (!job) return showNotFound();

  // canonical URL for sharing
  const jobUrl = `${location.origin}${location.pathname}?id=${encodeURIComponent(job.id)}`;

  jobContent.innerHTML = `
    <div class="job-detail-layout">
      <div class="job-detail-header">
        <a href="jobs.html" class="back-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
          </svg>
          Back to Jobs
        </a>
        <h1 class="job-title">${escapeHtml(job.title)}</h1>
        <div class="job-meta">
          <span class="job-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
            <b>Department:</b> ${escapeHtml(job.department)}
          </span>
          <span class="job-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
            <b>Location:</b> ${escapeHtml(job.location)}
          </span>
          <span class="job-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
            <b>Type:</b> ${escapeHtml(job.type)}
          </span>
          <span class="job-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
            <b>Experience:</b> ${escapeHtml(job.experience)}
          </span>
          <span class="job-meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
            <b>Posted:</b> ${formatDate(job.posted)}
          </span>
        </div>
      </div>

      <div class="job-detail-main">
        <div class="job-detail-content">
          <section class="job-section">
            <h2>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
              About this Role
            </h2>
            <p class="job-description">${escapeHtml(job.description)}</p>
          </section>

          <section class="job-section">
            <h2>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
              </svg>
              Key Responsibilities
            </h2>
            <ul class="job-list">
              ${listHtml(job.responsibilities)}
            </ul>
          </section>

          <section class="job-section">
            <h2>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
              </svg>
              Requirements
            </h2>
            <ul class="job-list">
              ${listHtml(job.requirements)}
            </ul>
          </section>
        </div>

        <aside class="job-detail-sidebar">
          <div class="sidebar-card">
            <h3>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
              Quick Apply
            </h3>
            <p>Ready to join our team? Start your application now.</p>
            <button id="apply-btn" class="btn-primary w-full">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
              Apply for this Position
            </button>
          </div>

          <div class="sidebar-card">
            <h3>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
              What We Offer
            </h3>
            <ul class="job-list">
              ${listHtml(job.benefits)}
            </ul>
          </div>

          <div class="sidebar-card">
            <h3>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
              Share this Position
            </h3>
            <div class="share-buttons">
              <button class="share-btn linkedin" onclick="shareToLinkedIn('${escapeHtml(job.title)}', '${jobUrl}')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.047-1.852-3.047-1.853 0-2.136 1.445-2.136 2.939v5.677H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
                LinkedIn
              </button>
              <button class="share-btn twitter" onclick="shareToTwitter('${escapeHtml(job.title)}', '${jobUrl}')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.665 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                </svg>
                Twitter
              </button>
            </div>
          </div>
        </aside>
      </div>

      <div class="job-detail-cta">
        <h2>Ready to make an impact?</h2>
        <p>Join our team and help us build the future of technology. We're excited to learn more about you.</p>
        <button id="apply-cta" class="btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
          </svg>
          Apply for this Position
        </button>
      </div>
    </div>
  `;

  // attach handlers
  document.getElementById('apply-btn').addEventListener('click', () => {
    location.href = `apply.html?id=${encodeURIComponent(job.id)}&title=${encodeURIComponent(job.title)}`;
  });
  document.getElementById('apply-cta').addEventListener('click', () => {
    location.href = `apply.html?id=${encodeURIComponent(job.id)}&title=${encodeURIComponent(job.title)}`;
  });
}

function showNotFound() {
  hideLoading();
  jobContent.innerHTML = `
    <div class="not-found">
      <div class="not-found-content">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
        <h2>Job not found</h2>
        <p>We couldn't find the job you're looking for.</p>
        <a href="jobs.html" class="btn-primary">Back to Jobs</a>
      </div>
    </div>
  `;
}

function showError(message) {
  hideLoading();
  jobContent.innerHTML = `
    <div class="error-state">
      <div class="error-content">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
        <h2>Something went wrong</h2>
        <p>${message}</p>
        <button onclick="location.reload()" class="btn-primary">Try Again</button>
      </div>
    </div>
  `;
}

// Share functions
function shareToLinkedIn(title, url) {
  const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}`;
  window.open(linkedinUrl, '_blank');
}

function shareToTwitter(title, url) {
  const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
  window.open(twitterUrl, '_blank');
}

// fetch & render logic
(function loadJob() {
  // Show loading immediately when page loads
  showLoading();
  
  const id = getJobId();
  if (!id) {
    showNotFound();
    return;
  }
  
  // Update loading message to be more specific
  const loadingText = jobLoading.querySelector('.loading-text');
  if (loadingText) {
    loadingText.textContent = `Loading job details...`;
  }

  fetch(API_URL)
    .then(res => {
      if (!res.ok) throw new Error('Failed to fetch jobs');
      return res.json();
    })
    .then(data => {
      // support several response shapes: array, { jobs: [...] }, or single object
      let job = null;
      if (Array.isArray(data)) {
        job = data.find(j => String(j.id) === String(id));
      } else if (data && Array.isArray(data.jobs)) {
        job = data.jobs.find(j => String(j.id) === String(id));
      } else if (data && data.id && String(data.id) === String(id)) {
        job = data;
      }

      if (job) {
        hideLoading();
        renderJob(job);
      } else {
        showNotFound();
      }
    })
    .catch(err => {
      console.error(err);
      showError('An error occurred while loading the job. Please try again later.');
    });
})();
