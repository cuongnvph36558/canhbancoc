//
const menuToggle = document.querySelector('.menu-toggle');
const mainNavigation = document.querySelector('#main-navigation');

const adminSidebarToggle = document.querySelector('.admin-sidebar-toggle');
const adminShell = document.querySelector('.admin-shell');
const adminSidebar = document.querySelector('#admin-sidebar');

if (document.querySelector('[data-vietnam-address]')) {
    import('./vietnam-addresses.js')
        .then(({ initializeVietnamAddressFields }) => initializeVietnamAddressFields())
        .catch(() => {
            document.querySelectorAll('[data-address-error]').forEach((message) => {
                message.textContent = 'Không tải được danh sách địa chỉ. Vui lòng tải lại trang.';
                message.hidden = false;
            });
        });
}

menuToggle?.addEventListener('click', () => {
    const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';

    menuToggle.setAttribute('aria-expanded', String(!isExpanded));
    menuToggle.setAttribute('aria-label', isExpanded ? 'Mở menu' : 'Đóng menu');
    mainNavigation?.classList.toggle('is-open', !isExpanded);
});

if (adminShell && adminSidebarToggle) {
    const closeAdminSidebar = () => {
        adminShell.classList.remove('admin-sidebar-open');
        adminSidebarToggle.setAttribute('aria-expanded', 'false');
        adminSidebarToggle.setAttribute('aria-label', 'Mở menu quản trị');
    };

    adminSidebarToggle.addEventListener('click', (event) => {
        event.stopPropagation();

        const isOpen = adminShell.classList.toggle('admin-sidebar-open');

        adminSidebarToggle.setAttribute('aria-expanded', String(isOpen));
        adminSidebarToggle.setAttribute('aria-label', isOpen ? 'Đóng menu quản trị' : 'Mở menu quản trị');
    });

    document.addEventListener('click', (event) => {
        if (!adminShell.classList.contains('admin-sidebar-open')) {
            return;
        }

        if (event.target instanceof Node && adminSidebar?.contains(event.target)) {
            return;
        }

        closeAdminSidebar();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAdminSidebar();
        }
    });
}

const storefront = document.querySelector('.cup-original');
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

if (storefront) {
    const revealItems = [...storefront.querySelectorAll('.reveal, .reveal-up, .reveal-scale, .reveal-fade, .reveal-stagger')];

    if (!prefersReducedMotion.matches && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -6% 0px',
        });

        storefront.classList.add('motion-ready');
        revealItems.forEach((item) => revealObserver.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }
}

// Magnetic button effect
document.querySelectorAll('.magnetic-btn').forEach((btn) => {
    btn.addEventListener('mousemove', (e) => {
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        
        btn.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
    });
    
    btn.addEventListener('mouseleave', () => {
        btn.style.transform = '';
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#') return;
        
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

const cupSearch = document.querySelector('#cup-search');
const cupProducts = [...document.querySelectorAll('[data-cup-product]')];
const cupFilters = [...document.querySelectorAll('[data-cup-filter]')];
const cupNoResults = document.querySelector('#cup-no-results');
let selectedCupCategory = 'all';

const filterCups = () => {
    const searchTerm = cupSearch?.value.trim().toLocaleLowerCase('vi') ?? '';
    let visibleProducts = 0;

    cupProducts.forEach((product) => {
        const matchesCategory = selectedCupCategory === 'all' || product.dataset.category === selectedCupCategory;
        const matchesSearch = product.dataset.name?.toLocaleLowerCase('vi').includes(searchTerm) ?? false;
        const isVisible = matchesCategory && matchesSearch;

        product.hidden = !isVisible;
        visibleProducts += Number(isVisible);
    });

    if (cupNoResults) {
        cupNoResults.hidden = visibleProducts > 0;
    }
};

cupSearch?.addEventListener('input', filterCups);

cupFilters.forEach((filter) => {
    filter.addEventListener('click', () => {
        selectedCupCategory = filter.dataset.cupFilter ?? 'all';

        cupFilters.forEach((button) => {
            const isSelected = button === filter;

            button.classList.toggle('is-selected', isSelected);
            button.setAttribute('aria-pressed', String(isSelected));
        });

        filterCups();
    });
});

document.querySelectorAll('[data-product-gallery]').forEach((gallery) => {
    const mainImage = gallery.querySelector('[data-product-main-image]');
    const thumbnails = [...gallery.querySelectorAll('[data-product-thumbnail]')];
    const imageCounter = gallery.querySelector('[data-product-image-counter]');

    thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', () => {
            if (!mainImage) {
                return;
            }

            mainImage.src = thumbnail.dataset.productImage ?? '';
            mainImage.alt = thumbnail.dataset.productAlt ?? '';

            thumbnails.forEach((item) => {
                const isSelected = item === thumbnail;

                item.classList.toggle('is-selected', isSelected);
                item.setAttribute('aria-pressed', String(isSelected));
            });

            if (imageCounter) {
                imageCounter.textContent = `${index + 1} / ${thumbnails.length}`;
            }
        });
    });
});

// Cốc Mơ — Storefront (gọn & tổ chức lại)
const cartCountStorageKey = 'storefront-cart-count';
const cartCountElements = document.querySelectorAll('[data-live-cart-count]');
const updateCartBadge = (count) => {
    cartCountElements.forEach((element) => {
        element.textContent = String(count);
    });
    window.localStorage.setItem(cartCountStorageKey, String(count));
};
window.updateCartBadge = updateCartBadge;

if (cartCountElements.length > 0) {
    const cartCount = Math.max(0, Number(document.body.dataset.cartCount ?? 0));
    updateCartBadge(cartCount);

    window.addEventListener('storage', (event) => {
        if (event.key === cartCountStorageKey && event.newValue !== null) {
            const nextCount = Number(event.newValue);
            if (Number.isFinite(nextCount) && nextCount >= 0) updateCartBadge(nextCount);
        }
    });
}

// Lazy-load customizer nếu đang ở trang customizer
if (document.querySelector('[data-cup-customizer]')) {
    Promise.all([
        import('./customizer.js'),
        import('./vietnam-addresses.js').catch(() => null),
    ]).then(([_, addresses]) => {
        if (addresses?.initializeVietnamAddressFields) {
            addresses.initializeVietnamAddressFields();
            // Đăng ký global để customizer có thể gọi lại khi mở modal
            window.initVietnamAddressFields = () => addresses.initializeVietnamAddressFields();
        }
    }).catch((err) => console.error('Không thể tải customizer:', err));
}

document.querySelectorAll('[data-support-chat]').forEach((chat) => {
    const transcript = chat.querySelector('[data-chat-messages]');
    const status = chat.querySelector('[data-chat-status]');
    const pollUrl = chat.dataset.pollUrl;
    const currentUserId = Number(chat.dataset.currentUserId);
    const chatComposer = chat.querySelector('.chat-composer');
    const imageInput = chatComposer?.querySelector('[data-chat-image-input]');
    const imagePreview = chatComposer?.querySelector('[data-chat-image-preview]');
    let lastMessageId = Number(chat.dataset.lastMessageId ?? 0);

    if (!transcript || !status || !pollUrl) {
        return;
    }

    transcript.scrollTop = transcript.scrollHeight;

    // Handle image preview
    const handleImagePreview = () => {
        if (!imageInput || !imagePreview) {
            return;
        }

        const file = imageInput.files?.[0];

        if (!file) {
            imagePreview.innerHTML = '';
            imagePreview.hidden = true;
            return;
        }

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            imagePreview.innerHTML = '<span class="chat-form-error">Chọn ảnh JPG, PNG hoặc WEBP có dung lượng không quá 5 MB.</span>';
            imagePreview.hidden = false;
            imageInput.value = '';
            return;
        }

        const objectUrl = URL.createObjectURL(file);

        imagePreview.innerHTML = `
            <img src="${objectUrl}" alt="Xem trước ảnh sẽ gửi">
            <button type="button" class="chat-image-preview-remove" data-remove-image aria-label="Xóa ảnh">×</button>
        `;
        imagePreview.hidden = false;

        imagePreview.querySelector('[data-remove-image]')?.addEventListener('click', () => {
            imageInput.value = '';
            imagePreview.innerHTML = '';
            imagePreview.hidden = true;
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }
        });
    };

    imageInput?.addEventListener('change', handleImagePreview);

    const createMessageElement = (message) => {
        const article = document.createElement('article');
        article.className = 'chat-message';
        article.dataset.chatMessageId = String(message.id);

        if (Number(message.sender_id) === currentUserId) {
            article.classList.add('is-own');
        }

        const sender = document.createElement('span');
        sender.className = 'chat-message-sender';
        sender.textContent = Number(message.sender_id) === currentUserId
            ? 'Bạn'
            : (message.sender_role === 'admin'
                ? (chat.dataset.viewerRole === 'admin' ? 'Admin' : 'Cốc Mơ')
                : (chat.dataset.contactName ?? 'Khách hàng'));

        article.append(sender);

        if (message.image_url) {
            const imageContainer = document.createElement('p');
            imageContainer.className = 'chat-message-image';
            const img = document.createElement('img');
            img.src = message.image_url;
            img.alt = 'Hình ảnh trong tin nhắn';
            img.loading = 'lazy';
            imageContainer.append(img);
            article.append(imageContainer);
        }

        if (message.body) {
            const body = document.createElement('p');
            body.textContent = message.body;
            article.append(body);
        }

        const time = document.createElement('time');
        time.textContent = message.time;
        article.append(time);

        return article;
    };

    const appendMessage = (message) => {
        transcript.querySelector('[data-chat-empty]')?.remove();
        const article = createMessageElement(message);
        transcript.append(article);
        transcript.scrollTop = transcript.scrollHeight;
    };

    const poll = async () => {
        try {
            const url = new URL(pollUrl, window.location.origin);
            url.searchParams.set('after', String(lastMessageId));

            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`Máy chủ trả về mã ${response.status}.`);
            }

            const payload = await response.json();

            if (!Array.isArray(payload.messages)) {
                throw new Error('Dữ liệu tin nhắn không hợp lệ.');
            }

            payload.messages.forEach((message) => {
                if (!Number.isInteger(message.id) || (typeof message.body !== 'string' && !message.image_url)) {
                    throw new Error('Dữ liệu tin nhắn không hợp lệ.');
                }

                if (message.id > lastMessageId) {
                    appendMessage(message);
                    lastMessageId = message.id;
                }
            });

            status.textContent = '';
        } catch (error) {
            status.textContent = 'Không thể cập nhật tin nhắn. Vui lòng kiểm tra kết nối và thử lại.';
            console.error('Không thể tải tin nhắn hỗ trợ:', error);
        } finally {
            window.setTimeout(poll, 5000);
        }
    };

    poll();
});
