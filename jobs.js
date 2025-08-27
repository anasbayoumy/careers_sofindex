const API_URL = '/backend/api/jobs.php';

const jobsList = document.getElementById('jobsList');
const departmentFilter = document.getElementById('departmentFilter');
const typeFilter = document.getElementById('typeFilter');
const locationFilter = document.getElementById('locationFilter');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');
const activeFiltersCount = document.getElementById('activeFiltersCount');
const jobsResultsCount = document.getElementById('jobsResultsCount');

let jobs = [];

// Show loading indicator
function showLoading() {
  jobsList.innerHTML = `
    <div class="loading-container">
      <div class="loading-spinner"></div>
      <p class="loading-text">Loading available positions...</p>
    </div>
  `;
}

// Hide loading indicator
function hideLoading() {
  // Loading will be hidden when renderJobs is called
}

function renderJobs(jobsToRender) {
  jobsList.innerHTML = '';
  if (jobsToRender.length === 0) {
    jobsList.innerHTML = '<p class="no-jobs">No jobs found matching your criteria.</p>';
    return;
  }

  jobsToRender.forEach((job, idx) => {
    const card = document.createElement('div');
    card.className = 'job-card hover-lift animate-fade-in';
    card.style.animationDelay = `${0.05 * idx}s`;
    
    let desc = '';
    if (job.description) {
      desc = job.description.length > 150 ? job.description.substring(0, 150) + '...' : job.description;
    }

    card.innerHTML = `
      <div class="job-card-header">
        <div class="job-card-title-block">
          <h3 class="job-card-title"><a href="job.html?id=${job.id}">${job.title}</a></h3>
          <div class="job-card-meta">
            <span class="job-meta-item"><span class="icon-users"></span>${job.department}</span>
            <span class="job-meta-item"><span class="icon-location"></span>${job.location}</span>
            <span class="job-meta-item"><span class="icon-clock"></span>${job.experience}</span>
          </div>
        </div>
        <span class="job-type-badge">${job.type}</span>
      </div>
      <p class="job-card-desc">${desc}</p>
      <div class="job-card-actions">
        <a href="job.html?id=${job.id}" class="btn-primary">View Details</a>
      </div>
    `;
    jobsList.appendChild(card);
  });
}

function populateFilters(jobs) {
  const departments = Array.from(new Set(jobs.map(j => j.department))).sort();
  const types = Array.from(new Set(jobs.map(j => j.type))).sort();
  const locations = Array.from(new Set(jobs.map(j => j.location))).sort();
  
  departmentFilter.innerHTML = '<option value="">All Departments</option>' + departments.map(d => `<option value="${d}">${d}</option>`).join('');
  typeFilter.innerHTML = '<option value="">All Types</option>' + types.map(t => `<option value="${t}">${t}</option>`).join('');
  locationFilter.innerHTML = '<option value="">All Locations</option>' + locations.map(l => `<option value="${l}">${l}</option>`).join('');
}

function filterJobs() {
  let filtered = jobs;
  
  if (departmentFilter.value) {
    filtered = filtered.filter(j => j.department === departmentFilter.value);
  }
  if (typeFilter.value) {
    filtered = filtered.filter(j => j.type === typeFilter.value);
  }
  if (locationFilter.value) {
    filtered = filtered.filter(j => j.location === locationFilter.value);
  }
  
  renderJobs(filtered);
  
  // Update results count
  if (jobsResultsCount) {
    jobsResultsCount.textContent = `Showing ${filtered.length} of ${jobs.length} positions`;
  }
  
  // Update clear filters button
  const activeCount = [departmentFilter.value, typeFilter.value, locationFilter.value].filter(Boolean).length;
  if (activeFiltersCount) {
    activeFiltersCount.textContent = activeCount;
  }
  if (clearFiltersBtn) {
    clearFiltersBtn.style.display = activeCount > 0 ? 'block' : 'none';
  }
}

// Check if user just applied for a job
function checkApplicationStatus() {
  const params = new URLSearchParams(window.location.search);
  const applied = params.get('applied');
  
  if (applied === 'true') {
    // Show success message
    const successMessage = document.createElement('div');
    successMessage.className = 'application-success';
    successMessage.innerHTML = `
      <div class="success-content">
        <h3>Application Submitted Successfully!</h3>
        <p>Thank you for your interest in joining SOFINDEX. We have received your application and will review it carefully.</p>
        <p>You will hear from us within 5-7 business days.</p>
      </div>
    `;
    
    // Insert at the top of the jobs list
    jobsList.parentNode.insertBefore(successMessage, jobsList);
    
    // Remove the success message after 10 seconds
    setTimeout(() => {
      if (successMessage.parentNode) {
        successMessage.parentNode.removeChild(successMessage);
      }
    }, 10000);
    
    // Clean up URL
    window.history.replaceState({}, document.title, window.location.pathname);
  }
}

// Fetch jobs from API
async function fetchJobs() {
  try {
    showLoading();
    
    const response = await fetch(API_URL);
    const data = await response.json();
    
    if (data.success && data.jobs) {
      jobs = data.jobs;
      populateFilters(jobs);
      filterJobs();
      checkApplicationStatus();
    } else {
      console.error('Failed to fetch jobs:', data.error || 'Unknown error');
      jobsList.innerHTML = '<p class="error-message">Failed to load jobs. Please try again later.</p>';
    }
  } catch (error) {
    console.error('Error fetching jobs:', error);
    jobsList.innerHTML = '<p class="error-message">Network error. Please check your connection and try again.</p>';
  }
}

// Event listeners
departmentFilter.addEventListener('change', filterJobs);
typeFilter.addEventListener('change', filterJobs);
locationFilter.addEventListener('change', filterJobs);

if (clearFiltersBtn) {
  clearFiltersBtn.addEventListener('click', () => {
    departmentFilter.value = '';
    typeFilter.value = '';
    locationFilter.value = '';
    filterJobs();
  });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  fetchJobs();
});
