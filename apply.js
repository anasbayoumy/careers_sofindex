const API_URL = '/backend/api/apply.php';
const applyForm = document.getElementById('applyForm');
const applyMessage = document.getElementById('applyMessage');
const jobIdInput = document.getElementById('jobId');
const jobTitleElement = document.getElementById('jobTitle');
const jobDescriptionElement = document.getElementById('jobDescription');

// Form field elements
const cvPdfRadio = document.getElementById('cvPdf');
const cvLinkRadio = document.getElementById('cvLink');
const cvPdfField = document.getElementById('cvPdfField');
const cvLinkField = document.getElementById('cvLinkField');
const cvFileInput = document.getElementById('cvFile');
const cvUrlInput = document.getElementById('cvUrl');



// Handle CV type toggle
function handleCvTypeToggle() {
  if (cvPdfRadio.checked) {
    cvPdfField.style.display = 'block';
    cvLinkField.style.display = 'none';
    cvFileInput.required = true;
    cvUrlInput.required = false;
    cvUrlInput.value = '';
  } else {
    cvPdfField.style.display = 'none';
    cvLinkField.style.display = 'block';
    cvFileInput.required = false;
    cvUrlInput.required = true;
    cvFileInput.value = '';
  }
}



// Get job details from URL parameters
function getJobDetails() {
  const params = new URLSearchParams(window.location.search);
  const jobId = params.get('id');
  const jobTitle = params.get('title');
  
  if (jobId && jobTitle) {
    // Set the job ID in the hidden input
    jobIdInput.value = jobId;
    
    // Update the page title and header
    jobTitleElement.textContent = `Apply for the position of ${jobTitle}`;
    jobDescriptionElement.textContent = `Please fill out the form below to submit your application for the ${jobTitle} position.`;
    
    // Update the page title
    document.title = `Apply for ${jobTitle} - SOFINDEX Careers`;
    
    // Fetch additional job details if needed
    fetchJobDetails(jobId);
  } else {
    // No job ID or title provided, redirect to jobs page
    window.location.href = 'jobs.html';
  }
}

// Fetch additional job details from the API
async function fetchJobDetails(jobId) {
  try {
    const response = await fetch(`/backend/api/jobs.php?id=${jobId}`);
    const data = await response.json();
    
    if (data.success && data.job) {
      const job = data.job;
      
      // Update description with more specific job details
      jobDescriptionElement.innerHTML = `
        <strong>${job.title}</strong> - ${job.department} Department<br>
        <small>${job.location} • ${job.type} • ${job.experience}</small><br><br>
        Please fill out the form below to submit your application for this position.
      `;
    }
  } catch (error) {
    console.error('Error fetching job details:', error);
  }
}

// Handle form submission
applyForm.onsubmit = async function(e) {
  e.preventDefault();
  
  // Clear previous messages
  applyMessage.textContent = '';
  applyMessage.className = 'apply-message';
  
  // Get form data
  const formData = new FormData(applyForm);
  
  // Basic validation for required fields
  const requiredFields = ['full_name', 'email', 'phone', 'linkedin_url'];
  for (const field of requiredFields) {
    if (!formData.get(field)?.trim()) {
      showMessage('Please fill in all required fields.', 'error');
      return;
    }
  }
  
  // Email validation
  const email = formData.get('email');
  if (!isValidEmail(email)) {
    showMessage('Please enter a valid email address.', 'error');
    return;
  }
  
  // LinkedIn URL validation
  const linkedinUrl = formData.get('linkedin_url');
  if (!isValidUrl(linkedinUrl)) {
    showMessage('Please enter a valid LinkedIn profile URL.', 'error');
    return;
  }
  
  // Check for duplicate submissions
  const phone = formData.get('phone');
  const duplicateCheck = await checkDuplicateSubmission(email, phone);
  if (duplicateCheck.error) {
    showMessage(duplicateCheck.error, 'error');
    return;
  }
  
  if (duplicateCheck.isDuplicate) {
    showMessage(duplicateCheck.message, 'error');
    return;
  }
  
  // CV validation based on type
  const cvType = formData.get('cv_type');
  if (cvType === 'pdf') {
    if (!cvFileInput.files[0]) {
      showMessage('Please upload your CV/Resume PDF file.', 'error');
      return;
    }
    // Check file size (5MB limit)
    if (cvFileInput.files[0].size > 5 * 1024 * 1024) {
      showMessage('CV file size must be less than 5MB.', 'error');
      return;
    }
  } else {
    const cvUrl = formData.get('cv_url');
    if (!cvUrl || !isValidUrl(cvUrl)) {
      showMessage('Please enter a valid CV/Resume URL.', 'error');
      return;
    }
  }
  
  // Cover letter validation based on type
  const coverLetterType = formData.get('cover_letter_type');
  if (coverLetterType === 'pdf') {
    if (!coverLetterFileInput.files[0]) {
      showMessage('Please upload your cover letter PDF file.', 'error');
      return;
    }
    // Check file size (5MB limit)
    if (coverLetterFileInput.files[0].size > 5 * 1024 * 1024) {
      showMessage('Cover letter file size must be less than 5MB.', 'error');
      return;
    }
  } else {
    const coverLetterText = formData.get('cover_letter_text');
    if (!coverLetterText?.trim()) {
      showMessage('Please write your cover letter.', 'error');
      return;
    }
  }
  
  try {
    // Show loading state
    const submitBtn = applyForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    // Prepare form data for submission
    const submitData = new FormData();
    
    // Add basic fields
    submitData.append('job_id', formData.get('job_id'));
    submitData.append('full_name', formData.get('full_name'));
    submitData.append('email', formData.get('email'));
    submitData.append('phone', formData.get('phone'));
    submitData.append('linkedin_url', formData.get('linkedin_url'));
    
    // Add portfolio URL if provided
    const portfolioUrl = formData.get('portfolio_url');
    if (portfolioUrl?.trim()) {
      submitData.append('portfolio_url', portfolioUrl);
    }
    
    // Add CV based on type
    const cvType = formData.get('cv_type');
    if (cvType === 'pdf') {
      submitData.append('cv_file', cvFileInput.files[0]);
      submitData.append('cv_type', 'pdf');
    } else {
      submitData.append('cv_url', formData.get('cv_url'));
      submitData.append('cv_type', 'link');
    }
    
      // Add cover letter text
  submitData.append('cover_letter_text', formData.get('cover_letter_text'));
    
    // Submit the form
    const response = await fetch(API_URL, {
      method: 'POST',
      body: submitData
    });
    
    const data = await response.json();
    
    if (data.success) {
      showMessage(data.message || 'Application submitted successfully!', 'success');
      applyForm.reset();
      
      // Redirect to success page or show success message
      setTimeout(() => {
        window.location.href = 'jobs.html?applied=true';
      }, 2000);
    } else {
      showMessage(data.error || 'Submission failed. Please try again.', 'error');
    }
  } catch (error) {
    console.error('Submission error:', error);
    showMessage('An error occurred. Please check your connection and try again.', 'error');
  } finally {
    // Reset button state
    const submitBtn = applyForm.querySelector('button[type="submit"]');
    submitBtn.textContent = 'Submit Application';
    submitBtn.disabled = false;
  }
};

// Helper function to show messages
function showMessage(message, type = 'info') {
  applyMessage.textContent = message;
  applyMessage.className = `apply-message ${type}`;
  
  // Auto-hide success messages after 5 seconds
  if (type === 'success') {
    setTimeout(() => {
      applyMessage.textContent = '';
      applyMessage.className = 'apply-message';
    }, 5000);
  }
}

// Helper function to validate email
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// Helper function to validate URL
function isValidUrl(url) {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
}

// Check for duplicate submissions
async function checkDuplicateSubmission(email, phone) {
  try {
    const response = await fetch(`${API_URL}?action=checkDuplicate&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}`);
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error checking duplicate:', error);
    return { error: 'Failed to check for duplicate submissions' };
  }
}

  // Initialize the page
  document.addEventListener('DOMContentLoaded', function() {
    getJobDetails();
    
    // Add event listeners for the entire toggle buttons
    document.querySelectorAll('.option-toggle').forEach(toggle => {
      toggle.addEventListener('click', function() {
        const radio = this.querySelector('input[type="radio"]');
        if (radio) {
          radio.checked = true;
          handleCvTypeToggle();
        }
      });
    });
    
    // Initialize form state
    handleCvTypeToggle();
  });
