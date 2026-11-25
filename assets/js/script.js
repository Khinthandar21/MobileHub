(() => {
  const adminSidebarNav = document.querySelector('.sidebar-nav');
  if (adminSidebarNav) {
    const sidebarScrollKey = 'mobilehub-admin-sidebar-scroll';
    const saveSidebarScroll = () => {
      try { sessionStorage.setItem(sidebarScrollKey, String(adminSidebarNav.scrollTop)); } catch (error) { /* Keep navigation working if storage is unavailable. */ }
    };
    try {
      const savedSidebarScroll = sessionStorage.getItem(sidebarScrollKey);
      if (savedSidebarScroll !== null) {
        requestAnimationFrame(() => { adminSidebarNav.scrollTop = Number(savedSidebarScroll) || 0; });
      }
    } catch (error) { /* Use the browser's default sidebar position. */ }
    adminSidebarNav.addEventListener('scroll', saveSidebarScroll, { passive: true });
    adminSidebarNav.addEventListener('click', (event) => {
      if (event.target.closest('a')) saveSidebarScroll();
    });
    window.addEventListener('pagehide', saveSidebarScroll);
  }

  const themeKey = 'mobilehub-theme';
  const themeButtons = document.querySelectorAll('#themeToggle, #customerThemeToggle');

  function setTheme(theme, persist = false) {
    const selectedTheme = theme === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-bs-theme', selectedTheme);

    if (persist) {
      try { localStorage.setItem(themeKey, selectedTheme); } catch (error) { /* Keep the current page theme. */ }
    }

    themeButtons.forEach((button) => {
      const nextTheme = selectedTheme === 'dark' ? 'light' : 'dark';
      const label = `Switch to ${nextTheme} mode`;
      button.setAttribute('aria-label', label);
      button.setAttribute('title', label);
      const icon = button.querySelector('i');
      if (icon) icon.className = selectedTheme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
    });
  }

  let initialTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
  try {
    const savedTheme = localStorage.getItem(themeKey);
    if (savedTheme === 'dark' || savedTheme === 'light') initialTheme = savedTheme;
  } catch (error) { /* Use the page default when storage is unavailable. */ }
  setTheme(initialTheme);

  themeButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-bs-theme');
      setTheme(currentTheme === 'dark' ? 'light' : 'dark', true);
    });
  });

  document.querySelectorAll('.customer-photo-preview').forEach((photo) => {
    photo.addEventListener('click', (event) => {
      event.preventDefault();
      const image = document.getElementById('customerPhotoModalImage');
      const modalElement = document.getElementById('customerPhotoModal');
      if (!image || !modalElement || !window.bootstrap?.Modal) return;
      image.src = photo.currentSrc || photo.src;
      image.alt = `${photo.dataset.photoName || 'Customer'} profile photo`;
      window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
    });
  });

  const profilePhotoInput = document.getElementById('avatar');
  const profilePhotoPreview = document.getElementById('accountAvatarPreview');
  const profilePhotoPlaceholder = document.getElementById('accountAvatarPlaceholder');
  const profilePhotoDeleteButton = document.getElementById('deleteProfilePhoto');
  const removeAvatarFlag = document.getElementById('removeAvatarFlag');
  const profilePhotoStatus = document.getElementById('profilePhotoStatus');

  const resetProfilePhotoSelection = () => {
    if (!profilePhotoInput || !profilePhotoPreview || !profilePhotoDeleteButton || !removeAvatarFlag) return;
    profilePhotoInput.value = '';
    removeAvatarFlag.value = '0';
    const hasOriginal = profilePhotoDeleteButton.dataset.hasOriginal === '1';
    if (hasOriginal) {
      profilePhotoPreview.src = profilePhotoPreview.dataset.originalSrc;
      profilePhotoPreview.classList.remove('d-none');
      profilePhotoPlaceholder?.classList.add('d-none');
    } else {
      profilePhotoPreview.removeAttribute('src');
      profilePhotoPreview.classList.add('d-none');
      profilePhotoPlaceholder?.classList.remove('d-none');
    }
    profilePhotoDeleteButton.disabled = !hasOriginal;
    profilePhotoDeleteButton.setAttribute('aria-disabled', String(!hasOriginal));
    profilePhotoDeleteButton.querySelector('span:last-child').textContent = 'Remove photo';
    profilePhotoDeleteButton.classList.remove('is-undo');
    if (profilePhotoStatus) profilePhotoStatus.textContent = 'JPG, PNG, or WEBP · Up to 2 MB. Save your profile to apply photo changes.';
  };

  profilePhotoInput?.addEventListener('change', () => {
    const file = profilePhotoInput.files?.[0];
    if (!file || !profilePhotoPreview || !profilePhotoDeleteButton || !removeAvatarFlag) return;

    removeAvatarFlag.value = '0';
    profilePhotoPreview.src = URL.createObjectURL(file);
    profilePhotoPreview.dataset.photoName = 'Selected';
    profilePhotoPreview.classList.remove('d-none');
    profilePhotoPlaceholder?.classList.add('d-none');
    profilePhotoDeleteButton.disabled = false;
    profilePhotoDeleteButton.setAttribute('aria-disabled', 'false');
    profilePhotoDeleteButton.querySelector('span:last-child').textContent = 'Cancel change';
    profilePhotoDeleteButton.classList.add('is-undo');
    if (profilePhotoStatus) profilePhotoStatus.textContent = `${file.name} selected. Save your profile to apply this photo.`;
  });

  profilePhotoDeleteButton?.addEventListener('click', () => {
    if (!profilePhotoInput || !profilePhotoPreview || !removeAvatarFlag) return;
    if (profilePhotoInput.files?.length) {
      resetProfilePhotoSelection();
      return;
    }
    if (profilePhotoDeleteButton.dataset.hasOriginal !== '1') return;

    const removing = removeAvatarFlag.value !== '1';
    removeAvatarFlag.value = removing ? '1' : '0';
    profilePhotoPreview.classList.toggle('d-none', removing);
    profilePhotoPlaceholder?.classList.toggle('d-none', !removing);
    profilePhotoDeleteButton.querySelector('span:last-child').textContent = removing ? 'Undo remove' : 'Remove photo';
    profilePhotoDeleteButton.classList.toggle('is-undo', removing);
    if (profilePhotoStatus) profilePhotoStatus.textContent = removing
      ? 'Photo will be removed when you save your profile.'
      : 'Photo removal cancelled. Save your profile to apply any other changes.';
  });

  const customerSuccessToast = document.getElementById('customerSuccessToast');
  if (customerSuccessToast) {
    const dismissToast = () => {
      customerSuccessToast.classList.add('is-hiding');
      window.setTimeout(() => customerSuccessToast.remove(), 250);
    };
    customerSuccessToast.querySelector('.profile-toast-close')?.addEventListener('click', dismissToast);
    window.setTimeout(dismissToast, 5000);
  }

  const customerProfileForm = document.getElementById('profile-details');
  const currentPasswordInput = document.getElementById('current_password');
  const newPasswordInput = document.getElementById('new_password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const passwordMatchFeedback = document.getElementById('password-match-feedback');

  const updatePasswordFeedback = () => {
    if (!newPasswordInput || !confirmPasswordInput) return;
    const newValue = newPasswordInput.value;
    const confirmValue = confirmPasswordInput.value;
    const changingPassword = newValue !== '' || confirmValue !== '';
    if (currentPasswordInput) currentPasswordInput.required = newValue !== '';
    newPasswordInput.required = confirmValue !== '';
    confirmPasswordInput.required = newValue !== '';

    if (!passwordMatchFeedback) return;
    confirmPasswordInput.classList.remove('is-invalid', 'is-valid');
    passwordMatchFeedback.classList.remove('text-danger', 'text-success');
    if (!changingPassword) {
      passwordMatchFeedback.textContent = '';
    } else if (!newValue) {
      passwordMatchFeedback.textContent = 'Enter a new password to confirm.';
    } else if (!confirmValue) {
      passwordMatchFeedback.textContent = 'Re-enter your new password to confirm it.';
    } else if (newValue !== confirmValue) {
      confirmPasswordInput.classList.add('is-invalid');
      passwordMatchFeedback.classList.add('text-danger');
      passwordMatchFeedback.textContent = 'Passwords do not match.';
    } else {
      confirmPasswordInput.classList.add('is-valid');
      passwordMatchFeedback.classList.add('text-success');
      passwordMatchFeedback.textContent = 'Passwords match.';
    }
  };

  newPasswordInput?.addEventListener('input', updatePasswordFeedback);
  confirmPasswordInput?.addEventListener('input', updatePasswordFeedback);
  customerProfileForm?.addEventListener('submit', (event) => {
    updatePasswordFeedback();
    if (newPasswordInput?.value && newPasswordInput.value !== confirmPasswordInput?.value) {
      event.preventDefault();
      confirmPasswordInput?.focus();
    }
  });
  if (customerProfileForm?.dataset.profileErrors === '1') {
    const errorTarget = document.getElementById(customerProfileForm.dataset.errorTarget || 'profile-details');
    requestAnimationFrame(() => {
      errorTarget?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      if (customerProfileForm.dataset.errorTarget === 'password-section' && currentPasswordInput?.classList.contains('is-invalid')) {
        currentPasswordInput.focus({ preventScroll: true });
      }
    });
  }

  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const adminApp = sidebar?.closest('.app');
  const desktopSidebarKey = 'mobilehub-admin-sidebar-collapsed';
  const isDesktopAdmin = () => window.matchMedia('(min-width: 992px)').matches;
  const updateSidebarToggle = () => {
    if (!sidebarToggle) return;
    const expanded = isDesktopAdmin()
      ? !adminApp?.classList.contains('sidebar-collapsed')
      : Boolean(sidebar?.classList.contains('open'));
    sidebarToggle.setAttribute('aria-expanded', String(expanded));
    sidebarToggle.setAttribute('aria-label', expanded ? 'Collapse navigation' : 'Expand navigation');
    sidebarToggle.title = expanded ? 'Collapse navigation' : 'Expand navigation';
    const icon = sidebarToggle.querySelector('i');
    if (icon) icon.className = expanded ? 'bi bi-list' : 'bi bi-layout-sidebar';
  };

  try {
    if (isDesktopAdmin() && localStorage.getItem(desktopSidebarKey) === '1') {
      adminApp?.classList.add('sidebar-collapsed');
    }
  } catch (error) { /* Use the expanded sidebar if storage is unavailable. */ }

  const closeSidebar = () => {
    sidebar?.classList.remove('open');
    backdrop?.classList.remove('show');
    updateSidebarToggle();
  };

  sidebarToggle?.addEventListener('click', () => {
    if (isDesktopAdmin()) {
      const collapsed = adminApp?.classList.toggle('sidebar-collapsed') ?? false;
      try { localStorage.setItem(desktopSidebarKey, collapsed ? '1' : '0'); } catch (error) { /* Keep the current state for this page. */ }
    } else {
      sidebar?.classList.toggle('open');
      backdrop?.classList.toggle('show');
    }
    updateSidebarToggle();
  });
  backdrop?.addEventListener('click', closeSidebar);
  window.addEventListener('resize', () => {
    if (isDesktopAdmin()) closeSidebar();
    updateSidebarToggle();
  });
  updateSidebarToggle();

  const confirmModalElement = document.getElementById('actionConfirmModal');
  const confirmAcceptButton = document.getElementById('actionConfirmAccept');
  let pendingConfirmation = null;
  const confirmModal = confirmModalElement && window.bootstrap?.Modal
    ? window.bootstrap.Modal.getOrCreateInstance(confirmModalElement)
    : null;

  document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (event) => {
      if (!confirmModal) {
        if (!window.confirm(element.getAttribute('data-confirm') || 'Are you sure?')) event.preventDefault();
        return;
      }

      event.preventDefault();
      pendingConfirmation = element;
      const confirmAction = (element.dataset.confirmButton || '').toLowerCase();
      const isClearCart = confirmAction.includes('clear');
      const isRemove = confirmAction.includes('remove');
      document.getElementById('actionConfirmTitle').textContent = element.dataset.confirmTitle || (isClearCart ? 'Clear your cart?' : (isRemove ? 'Remove from cart?' : 'Confirm action?'));
      document.getElementById('actionConfirmMessage').textContent = element.dataset.confirm || 'Are you sure?';
      confirmAcceptButton.textContent = element.dataset.confirmButton || (isClearCart ? 'Clear cart' : 'Confirm');
      const isDestructive = ['remove', 'delete', 'clear', 'deactivate'].some((action) => confirmAction.includes(action));
      confirmAcceptButton.classList.toggle('btn-danger', isDestructive);
      confirmAcceptButton.classList.toggle('btn-primary', !confirmAcceptButton.classList.contains('btn-danger'));
      confirmModal.show();
    });
  });

  confirmAcceptButton?.addEventListener('click', () => {
    const element = pendingConfirmation;
    pendingConfirmation = null;
    confirmModal?.hide();
    if (!element) return;
    if (element.form && element.type === 'submit') {
      element.form.requestSubmit(element);
    } else if (element instanceof HTMLAnchorElement) {
      window.location.assign(element.href);
    }
  });

  document.querySelectorAll('[data-admin-toast]').forEach((toast) => {
    let dismissed = false;
    const dismissToast = () => {
      if (dismissed) return;
      dismissed = true;
      toast.classList.add('is-hiding');
      window.setTimeout(() => toast.remove(), 250);
    };
    toast.querySelector('.admin-toast-close')?.addEventListener('click', dismissToast);
    window.setTimeout(dismissToast, 5000);
  });
})();
