document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registrationForm");

  // Form validation
  form.addEventListener("submit", (event) => {
    event.preventDefault();

    if (validateForm()) {
      // Show loading state
      const submitBtn = document.querySelector(".submitBtn");
      const btnText = submitBtn.querySelector(".btnText");
      const originalText = btnText.textContent;

      submitBtn.classList.add("loading");
      btnText.textContent = "Submitting...";

      form.submit();
    }
  });

  // Validate form fields
  function validateForm() {
    clearErrors();
    let isValid = true;

    // First Name validation
    const firstName = document.getElementById("firstName");
    if (firstName.value.trim().length < 2) {
      showError(firstName, "First name must be at least 2 characters");
      isValid = false;
    }

    // Last Name validation
    const lastName = document.getElementById("lastName");
    if (lastName.value.trim().length < 2) {
      showError(lastName, "Last name must be at least 2 characters");
      isValid = false;
    }

    // Email validation
    const email = document.getElementById("email");
    if (!isValidEmail(email.value)) {
      showError(email, "Please enter a valid email address");
      isValid = false;
    }

    // Phone number validation
    const phoneNumber = document.getElementById("phoneNumber");
    if (phoneNumber.value.trim().length < 10) {
      showError(phoneNumber, "Phone number must be at least 10 digits");
      isValid = false;
    }

    // Password validation
    const password = document.getElementById("password");
    if (password.value.length < 8) {
      showError(password, "Password must be at least 8 characters");
      isValid = false;
    }

    // Confirm password validation
    const confirmPassword = document.getElementById("confirmPassword");
    if (password.value !== confirmPassword.value) {
      showError(confirmPassword, "Passwords don't match");
      isValid = false;
    }

    return isValid;
  }

  // Helper function to validate email
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // Show error message
  function showError(input, message) {
    const errorElement = input.nextElementSibling;
    errorElement.textContent = message;
    input.style.borderColor = "#e74c3c";
  }

  // Clear all error messages
  function clearErrors() {
    const errorElements = document.querySelectorAll(".error-message");
    errorElements.forEach((element) => {
      element.textContent = "";
    });

    const inputs = document.querySelectorAll("input, select");
    inputs.forEach((input) => {
      input.style.borderColor = "#ddd";
    });
  }

  // Real-time validation for password matching
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirmPassword");

  confirmPassword.addEventListener("input", () => {
    if (password.value !== confirmPassword.value) {
      showError(confirmPassword, "Passwords don't match");
    } else {
      confirmPassword.nextElementSibling.textContent = "";
      confirmPassword.style.borderColor = "#4070f4";
    }
  });
});
