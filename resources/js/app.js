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

const cupDesigner = document.querySelector('[data-cup-customizer]');

if (cupDesigner) {
    const preview = cupDesigner.querySelector('[data-cup-preview]');
    const printArea = cupDesigner.querySelector('[data-print-area]');
    const designItemsElement = cupDesigner.querySelector('[data-design-items]');
    const designFields = cupDesigner.querySelector('[data-design-fields]');
    const itemPlaceholder = cupDesigner.querySelector('[data-print-placeholder]');
    const layerHint = cupDesigner.querySelector('[data-layer-hint]');
    const styleOptions = [...cupDesigner.querySelectorAll('[data-cup-variant-option]')];
    const selectedVariantInput = cupDesigner.querySelector('[data-selected-variant]');
    const iconOptions = [...cupDesigner.querySelectorAll('[data-add-icon]')];
    const addTextButton = cupDesigner.querySelector('[data-add-text]');
    const artworkInput = cupDesigner.querySelector('[data-artwork-input]');
    const uploadCaption = cupDesigner.querySelector('[data-upload-caption]');
    const removeBackgroundButton = cupDesigner.querySelector('[data-remove-background]');
    const restoreBackgroundButton = cupDesigner.querySelector('[data-restore-background]');
    const backgroundRemovalStatus = cupDesigner.querySelector('[data-background-removal-status]');
    const artworkActions = cupDesigner.querySelector('[data-artwork-actions]');
    const orderForm = cupDesigner.matches('form') ? cupDesigner : cupDesigner.querySelector('form');
    const orderSubmitButton = orderForm?.querySelector('[type="submit"]');
    const layerEditor = cupDesigner.querySelector('[data-layer-editor]');
    const selectedContentInput = cupDesigner.querySelector('[data-selected-content]');
    const fontSizeControl = cupDesigner.querySelector('[data-font-size-control]');
    const fontSizeInput = cupDesigner.querySelector('[data-selected-font-size]');
    const fontSizeValue = cupDesigner.querySelector('[data-font-size-value]');
    const removeLayerButton = cupDesigner.querySelector('[data-remove-layer]');
    const quantityInput = cupDesigner.querySelector('[data-cup-quantity]');
    const orderTotal = cupDesigner.querySelector('[data-cup-order-total]');
    const orderUnitPrice = cupDesigner.querySelector('[data-cup-order-unit]');
    const discountNote = cupDesigner.querySelector('[data-cup-discount-note]');
    const designPreviewInput = cupDesigner.querySelector('[data-design-preview]');
    const unitPrice = Number(cupDesigner.dataset.unitPrice);
    const discountMinimum = Number(cupDesigner.dataset.discountMinimum);
    const discountPercentage = Number(cupDesigner.dataset.discountPercentage);
    const formatter = new Intl.NumberFormat('vi-VN');
    const defaultItems = JSON.parse(cupDesigner.dataset.initialItems ?? '[]');
    const clamp = (value, minimum, maximum) => Math.min(Math.max(value, minimum), maximum);
    let designItems = defaultItems.map((item) => ({
        type: item.type,
        content: String(item.content),
        x: Number(item.x),
        y: Number(item.y),
        width: Number(item.width),
        font_size: clamp(Number(item.font_size ?? 100), 50, 200),
        image_index: item.type === 'image' ? Number(item.image_index ?? 0) : null,
    }));
    let selectedItemIndex = null;
    let artworkFiles = []; // [{file, objectUrl, originalFile, backgroundRemovalApplied}]
    let currentArtworkFile = null;
    let originalArtworkFile = null;
    let backgroundRemovalApplied = false;
    let isProcessingBackgroundRemoval = false;
    let backgroundRemovalPipelinePromise = null;
    let isPreparingOrder = false;
    let submitPreparedOrder = false;
    let selectedVariant = selectedVariantInput?.value ?? 'black';
    let nextImageIndex = 0;

    const getTextFontSize = (fontScale) => clamp(window.innerWidth * 0.017, 13, 24) * fontScale / 100;

    const updateArtworkControls = () => {
        if (artworkInput) {
            artworkInput.disabled = isProcessingBackgroundRemoval;
        }

        const selectedItem = selectedItemIndex !== null ? designItems[selectedItemIndex] : null;
        const selectedImageIndex = selectedItem?.type === 'image' ? selectedItem.image_index : null;
        const selectedImageData = selectedImageIndex !== null ? artworkFiles[selectedImageIndex] : null;

        if (removeBackgroundButton) {
            removeBackgroundButton.disabled = !selectedImageData
                || selectedImageData.backgroundRemovalApplied
                || isProcessingBackgroundRemoval;
        }

        if (restoreBackgroundButton) {
            restoreBackgroundButton.hidden = !selectedImageData || !selectedImageData.backgroundRemovalApplied;
            restoreBackgroundButton.disabled = isProcessingBackgroundRemoval;
        }

        if (orderSubmitButton) {
            orderSubmitButton.disabled = isProcessingBackgroundRemoval;
        }

        if (removeLayerButton) {
            removeLayerButton.disabled = isProcessingBackgroundRemoval
                && selectedItem?.type === 'image';
        }

        [...(designItemsElement?.querySelectorAll('[data-layer-remove]') ?? [])].forEach((button, index) => {
            const item = designItems[index];
            button.disabled = isProcessingBackgroundRemoval && item?.type === 'image';
        });
    };

    const setArtworkInputFiles = (files) => {
        const fileTransfer = new DataTransfer();

        files.forEach((file) => fileTransfer.items.add(file));
        artworkInput.files = fileTransfer.files;
        artworkFiles = files.map((file) => ({
            file,
            objectUrl: URL.createObjectURL(file),
            originalFile: file,
            backgroundRemovalApplied: false,
        }));
    };

    const updateArtworkInputFiles = () => {
        const files = artworkFiles.map((data) => data.file);
        const fileTransfer = new DataTransfer();

        files.forEach((file) => fileTransfer.items.add(file));
        artworkInput.files = fileTransfer.files;
    };

    const showArtworkPreview = (artwork, caption = artwork.name, imageIndex = null) => {
        const idx = imageIndex !== null ? imageIndex : nextImageIndex++;
        const objectUrl = URL.createObjectURL(artwork);

        // Store artwork file info
        const existingIndex = artworkFiles.findIndex((_, i) => i === idx);
        if (existingIndex >= 0) {
            URL.revokeObjectURL(artworkFiles[existingIndex].objectUrl);
            artworkFiles[existingIndex] = {
                file: artwork,
                objectUrl,
                originalFile: artwork,
                backgroundRemovalApplied: false,
            };
        } else {
            artworkFiles.push({
                file: artwork,
                objectUrl,
                originalFile: artwork,
                backgroundRemovalApplied: false,
            });
        }

        if (uploadCaption) {
            uploadCaption.textContent = caption;
        }

        // Find existing image item or add new one
        const existingImageIndex = designItems.findIndex(
            (item) => item.type === 'image' && item.image_index === idx,
        );

        if (existingImageIndex >= 0) {
            designItems[existingImageIndex].content = 'artwork';
            selectedItemIndex = existingImageIndex;
            renderDesignItems();
        } else {
            addDesignItem('image', 'artwork', undefined, undefined, 38, idx);
        }
    };

    const getBackgroundRemovalPipeline = () => {
        if (!backgroundRemovalPipelinePromise) {
            backgroundRemovalPipelinePromise = import('@huggingface/transformers')
                .then(({ pipeline }) => pipeline(
                    'background-removal',
                'xrds/isnet-general-onnx-int8',
                    {
                        dtype: 'q8',
                    revision: '71eff2372ec9c8edbc6ca637ded591423d23b65a',
                progress_callback: ({ status, progress }) => {
                    if (!backgroundRemovalStatus) {
                        return;
                            }

                    if (status === 'download') {
                        backgroundRemovalStatus.textContent = 'Đang tải trước mô hình AI để tách nền nhanh hơn...';
                    } else if (status === 'progress' && Number.isFinite(progress)) {
                        backgroundRemovalStatus.textContent = `Đang tải mô hình AI... ${Math.round(progress)}%`;
                    }
                },
                    },
                ))
                .catch((error) => {
                    backgroundRemovalPipelinePromise = null;
                    throw error;
                });
        }

        return backgroundRemovalPipelinePromise;
    };

    const prepareBackgroundRemovalPipeline = () => {
        if (!backgroundRemovalStatus || backgroundRemovalPipelinePromise) {
            return;
        }

        backgroundRemovalStatus.textContent = 'Đang chuẩn bị mô hình AI để tách nền nhanh hơn...';

        getBackgroundRemovalPipeline()
            .then(() => {
                if (!isProcessingBackgroundRemoval && !backgroundRemovalApplied) {
                    backgroundRemovalStatus.textContent = 'Mô hình đã sẵn sàng. Bạn có thể tách nền ngay.';
                }
            })
            .catch((error) => {
                console.error('Không thể tải trước mô hình tách nền:', error);
                backgroundRemovalStatus.textContent = 'Chưa thể tải mô hình. Bấm “Tách nền tự động” để thử lại.';
            });
    };

    const updateDesignFields = () => {
        if (!designFields) {
            return;
        }

        designFields.replaceChildren();

        designItems.forEach((item, index) => {
            Object.entries(item).forEach(([key, value]) => {
                if (value === null || value === undefined) {
                    return;
                }
                const input = document.createElement('input');

                input.type = 'hidden';
                input.name = `design[items][${index}][${key}]`;
                input.value = String(value);
                designFields.append(input);
            });
        });

        if (itemPlaceholder) {
            itemPlaceholder.hidden = designItems.length > 0;
        }

        if (layerHint) {
            layerHint.hidden = designItems.length === 0;
        }
    };

    const updateSelectedLayer = () => {
        const hasSelectedItem = selectedItemIndex !== null && designItems[selectedItemIndex] !== undefined;
        const selectedItem = hasSelectedItem ? designItems[selectedItemIndex] : null;

        if (layerEditor) {
            layerEditor.hidden = !hasSelectedItem;
        }

        if (selectedContentInput && selectedItem) {
            selectedContentInput.value = selectedItem.content;
            selectedContentInput.disabled = selectedItem.type !== 'text';
            selectedContentInput.setAttribute('aria-label', selectedItem.type === 'text' ? 'Chỉnh sửa nội dung chữ' : 'Biểu tượng hoặc ảnh đã chọn');
        }

        if (fontSizeControl && fontSizeInput && fontSizeValue) {
            const isTextSelected = selectedItem?.type === 'text';

            fontSizeControl.hidden = !isTextSelected;

            if (isTextSelected) {
                fontSizeInput.value = String(selectedItem.font_size);
                fontSizeValue.value = `${selectedItem.font_size}%`;
            }
        }
    };

    const selectLayer = (index) => {
        selectedItemIndex = index;
        [...(designItemsElement?.children ?? [])].forEach((element, itemIndex) => {
            element.classList.toggle('is-selected', itemIndex === selectedItemIndex);
            element.setAttribute('aria-pressed', String(itemIndex === selectedItemIndex));
        });
        updateSelectedLayer();
    };

    const addResizeHandle = (element) => {
        const handle = document.createElement('span');

        handle.className = 'cup-design-resize-handle';
        handle.setAttribute('aria-hidden', 'true');
        element.append(handle);
    };

    const removeSelectedLayer = () => {
        if (selectedItemIndex === null) {
            return;
        }

        const [removedItem] = designItems.splice(selectedItemIndex, 1);
        const removedImageIndex = removedItem?.image_index ?? 0;

        if (removedItem?.type === 'image') {
            // Revoke the object URL for this image
            if (artworkFiles[removedImageIndex]?.objectUrl) {
                URL.revokeObjectURL(artworkFiles[removedImageIndex].objectUrl);
            }
            // Remove from artworkFiles array
            artworkFiles.splice(removedImageIndex, 1);
            // Update image indices in designItems
            designItems.forEach((item) => {
                if (item.type === 'image' && item.image_index > removedImageIndex) {
                    item.image_index--;
                }
            });
            nextImageIndex = Math.max(0, nextImageIndex - 1);
            updateArtworkInputFiles();

            if (uploadCaption) {
                uploadCaption.textContent = artworkFiles.length === 0
                    ? 'JPG, PNG hoặc WEBP · Tối đa 5 MB'
                    : `${artworkFiles.length} ảnh đã chọn`;
            }
            if (backgroundRemovalStatus) {
                backgroundRemovalStatus.textContent = '';
            }
            updateArtworkControls();
        }

        selectedItemIndex = designItems.length > 0 ? Math.min(selectedItemIndex, designItems.length - 1) : null;
        renderDesignItems();
    };

    const attachPointerControls = (element, index) => {
        element.addEventListener('pointerdown', (event) => {
            if (event.target.closest('[data-layer-remove]')) {
                return;
            }

            event.preventDefault();
            selectLayer(index);
            element.setPointerCapture(event.pointerId);

            const item = designItems[index];
            const areaRect = printArea.getBoundingClientRect();
            const startX = event.clientX;
            const startY = event.clientY;
            const initialX = item.x;
            const initialY = item.y;
            const initialWidth = item.width;
            const isResizing = event.target.closest('.cup-design-resize-handle') !== null;

            element.classList.toggle('is-resizing', isResizing);

            const moveLayer = (moveEvent) => {
                const deltaX = ((moveEvent.clientX - startX) / areaRect.width) * 100;
                const deltaY = ((moveEvent.clientY - startY) / areaRect.height) * 100;

                if (isResizing) {
                    item.width = Math.round(clamp(initialWidth + deltaX, 12, 80));
                } else {
                    item.x = Math.round(clamp(initialX + deltaX, 0, 100 - item.width));
                    item.y = Math.round(clamp(initialY + deltaY, 0, 94));
                }

                element.style.left = `${item.x}%`;
                element.style.top = `${item.y}%`;
                element.style.width = `${item.width}%`;
                updateDesignFields();
            };

            const finishMoving = () => {
                element.classList.remove('is-resizing');
                element.removeEventListener('pointermove', moveLayer);
                element.removeEventListener('pointerup', finishMoving);
                element.removeEventListener('pointercancel', finishMoving);
            };

            element.addEventListener('pointermove', moveLayer);
            element.addEventListener('pointerup', finishMoving);
            element.addEventListener('pointercancel', finishMoving);
        });

        element.addEventListener('click', () => selectLayer(index));
    };

    const renderDesignItems = () => {
        if (!designItemsElement) {
            return;
        }

        designItemsElement.replaceChildren();

        designItems.forEach((item, index) => {
            const element = document.createElement('button');

            element.type = 'button';
            element.className = `cup-design-item cup-design-item-${item.type}`;
            element.style.left = `${item.x}%`;
            element.style.top = `${item.y}%`;
            element.style.width = `${item.width}%`;

            if (item.type === 'text') {
                element.style.fontSize = `${getTextFontSize(item.font_size)}px`;
            }

            element.setAttribute('aria-label', item.type === 'text' ? `Chọn chữ ${item.content}` : `Chọn thiết kế ${item.content}`);
            element.setAttribute('aria-pressed', String(index === selectedItemIndex));

            if (item.type === 'image') {
                const imgData = artworkFiles[item.image_index ?? 0];

                if (imgData?.objectUrl) {
                    const image = document.createElement('img');

                    image.src = imgData.objectUrl;
                    image.alt = '';
                    element.append(image);
                } else {
                    element.textContent = `Ảnh ${(item.image_index ?? 0) + 1}`;
                }
            } else {
                element.textContent = item.content;
            }

            const removeButton = document.createElement('button');

            removeButton.type = 'button';
            removeButton.className = 'cup-design-item-remove';
            removeButton.dataset.layerRemove = 'true';
            removeButton.setAttribute('aria-label', 'Xóa nội dung này');
            removeButton.textContent = '×';
            removeButton.addEventListener('click', (event) => {
                event.stopPropagation();
                selectLayer(index);
                removeSelectedLayer();
            });
            element.append(removeButton);
            addResizeHandle(element);
            attachPointerControls(element, index);
            designItemsElement.append(element);
        });

        updateDesignFields();
        updateSelectedLayer();
    };

    const addDesignItem = (type, content, x, y, width = type === 'text' ? 60 : type === 'image' ? 38 : 20, imageIndex = null) => {
        if (designItems.length >= 20) {
            return;
        }

        if (x === undefined || y === undefined) {
            if (type === 'text') {
                const textCount = designItems.filter((item) => item.type === 'text').length;
                const textPositions = [[18, 8], [18, 77], [18, 17], [18, 58], [2, 28], [38, 28]];
                [x, y] = textPositions[(textCount - 1) % textPositions.length];
            } else {
                const artworkCount = designItems.filter((item) => item.type !== 'text').length;
                const artworkPositions = [[72, 8], [72, 68], [5, 8], [5, 68], [40, 8], [40, 68]];
                [x, y] = artworkPositions[(artworkCount - 1) % artworkPositions.length];
            }
        }

        designItems.push({
            type,
            content,
            x: clamp(Math.round(x), 0, 100 - width),
            y: clamp(Math.round(y), 0, 94),
            width,
            image_index: type === 'image' ? (imageIndex !== null ? imageIndex : nextImageIndex++) : null,
        });
        selectedItemIndex = designItems.length - 1;
        renderDesignItems();
    };

    const applyVariant = (variant) => {
        if (!preview || !selectedVariantInput) {
            return;
        }

        selectedVariant = variant;
        preview.dataset.cupVariant = variant;
        selectedVariantInput.value = variant;
        styleOptions.forEach((option) => {
            const isSelected = option.dataset.cupVariantOption === variant;

            option.classList.toggle('is-selected', isSelected);
            option.setAttribute('aria-pressed', String(isSelected));
        });
    };

    const renderCupPreviewSnapshot = async () => {
        if (!preview || !designPreviewInput) {
            return false;
        }

        const previewStage = preview.querySelector('[data-print-area]');

        if (!previewStage) {
            return false;
        }

        const stageRect = preview.getBoundingClientRect();

        if (!stageRect.width || !stageRect.height) {
            return false;
        }

        const width = 900;
        const height = Math.round(width * stageRect.height / stageRect.width);

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        if (!context) {
            return false;
        }

        canvas.width = width;
        canvas.height = height;
        context.fillStyle = '#fff';
        context.fillRect(0, 0, width, height);

        const scaleX = width / 800;
        const scaleY = height / 650;
        const style = window.getComputedStyle(previewStage);
        const handleColor = style.getPropertyValue('--cup-handle-color').trim() || '#242322';
        const interiorColor = style.getPropertyValue('--cup-interior-color').trim() || '#171717';
        const mugBody = new Path2D('M223 155h398l-37 378c-4 43-29 68-72 68H334c-43 0-68-25-72-68z');
        const mugHandle = new Path2D('M607 251h47c91 0 100 216 0 216h-56v-47h52c45 0 42-122 0-122h-43z');

        context.save();
        context.scale(scaleX, scaleY);
        context.fillStyle = 'rgba(45, 40, 35, .12)';
        context.beginPath();
        context.ellipse(415, 596, 240, 28, 0, 0, Math.PI * 2);
        context.fill();

        const handleGradient = context.createLinearGradient(607, 0, 654, 0);
        handleGradient.addColorStop(0, '#d1d1d1');
        handleGradient.addColorStop(.42, handleColor);
        handleGradient.addColorStop(1, '#c7c7c7');
        context.fillStyle = handleGradient;
        context.strokeStyle = 'rgba(80, 78, 76, .22)';
        context.lineWidth = 3;
        context.fill(mugHandle);
        context.stroke(mugHandle);

        const bodyGradient = context.createLinearGradient(223, 155, 621, 400);
        bodyGradient.addColorStop(0, '#d1d1d1');
        bodyGradient.addColorStop(.16, '#fbfbfb');
        bodyGradient.addColorStop(.78, '#fff');
        bodyGradient.addColorStop(1, '#d2d2d2');
        context.fillStyle = bodyGradient;
        context.shadowColor = 'rgba(52, 47, 41, .08)';
        context.shadowBlur = 12;
        context.shadowOffsetY = 15;
        context.fill(mugBody);
        context.shadowColor = 'transparent';
        context.shadowBlur = 0;
        context.shadowOffsetY = 0;
        context.fillStyle = '#fff';
        context.beginPath();
        context.ellipse(422, 156, 199, 31, 0, 0, Math.PI * 2);
        context.fill();
        context.fillStyle = interiorColor;
        context.beginPath();
        context.ellipse(422, 156, 185, 22, 0, 0, Math.PI * 2);
        context.fill();
        context.fillStyle = 'rgba(255, 255, 255, .38)';
        context.beginPath();
        context.moveTo(272, 198);
        context.lineTo(291, 198);
        context.lineTo(326, 538);
        context.lineTo(309, 538);
        context.closePath();
        context.fill();
        context.restore();

        const stageScaleX = width / stageRect.width;
        const stageScaleY = height / stageRect.height;
        const printAreaRect = previewStage.getBoundingClientRect();
        const printAreaX = (printAreaRect.left - stageRect.left) * stageScaleX;
        const printAreaY = (printAreaRect.top - stageRect.top) * stageScaleY;
        const printAreaWidth = printAreaRect.width * stageScaleX;
        const printAreaHeight = printAreaRect.height * stageScaleY;
        const designItemsInStage = [...previewStage.querySelectorAll('.cup-design-item')];
        const imagePromises = [];

        designItemsInStage.forEach((item, index) => {
            const itemRect = item.getBoundingClientRect();
            const x = (itemRect.left - stageRect.left) * stageScaleX;
            const y = (itemRect.top - stageRect.top) * stageScaleY;
            const itemWidth = itemRect.width * stageScaleX;
            const itemHeight = itemRect.height * stageScaleY;
            const designItem = designItems[index];

            if (designItem?.type === 'text' || designItem?.type === 'icon') {
                const style = window.getComputedStyle(item);

                context.font = `${style.fontStyle} ${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
                context.fillStyle = style.color;
                context.textAlign = 'center';
                context.textBaseline = 'middle';
                context.save();
                context.beginPath();
                context.rect(printAreaX, printAreaY, printAreaWidth, printAreaHeight);
                context.clip();
                context.beginPath();
                context.rect(x, y, itemWidth, itemHeight);
                context.clip();
                context.fillText(designItem.content, x + itemWidth / 2, y + itemHeight / 2, itemWidth);
                context.restore();
            } else if (designItem?.type === 'image') {
                const imageIndex = designItem.image_index ?? 0;
                const imgData = artworkFiles[imageIndex];

                if (imgData?.objectUrl) {
                    const image = item.querySelector('img');
                    const imageBitmap = new Image();

                    imagePromises.push(new Promise((resolve) => {
                        imageBitmap.onload = () => {
                            const imageScale = Math.min(itemWidth / imageBitmap.width, itemHeight / imageBitmap.height);
                            const imageWidth = imageBitmap.width * imageScale;
                            const imageHeight = imageBitmap.height * imageScale;

                            context.save();
                            context.beginPath();
                            context.rect(printAreaX, printAreaY, printAreaWidth, printAreaHeight);
                            context.clip();
                            context.drawImage(
                                imageBitmap,
                                x + (itemWidth - imageWidth) / 2,
                                y + (itemHeight - imageHeight) / 2,
                                imageWidth,
                                imageHeight,
                            );
                            context.restore();
                            resolve();
                        };
                        imageBitmap.onerror = () => resolve(false);
                        imageBitmap.src = imgData.objectUrl;
                    }));
                }
            }
        });

        const imagesLoaded = await Promise.all(imagePromises);

        if (imagesLoaded.includes(false)) {
            return false;
        }

        designPreviewInput.value = canvas.toDataURL('image/png');

        return true;
    };

    const updatePrice = () => {
        const quantity = clamp(Number(quantityInput?.value) || 1, 1, 99);
        const discount = quantity >= discountMinimum ? discountPercentage : 0;
        const total = Math.round(unitPrice * quantity * (100 - discount) / 100);

        if (orderTotal) {
            orderTotal.textContent = `${formatter.format(total)}đ`;
        }

        if (orderUnitPrice) {
            orderUnitPrice.textContent = `${formatter.format(unitPrice)}đ / cốc`;
        }

        if (discountNote) {
            discountNote.textContent = discount
                ? `Đã giảm ${discount}% cho đơn từ ${discountMinimum} cốc`
                : `Từ ${discountMinimum} cốc giảm ${discountPercentage}%`;
        }

        if (quantityInput) {
            quantityInput.value = String(quantity);
        }
    };

    styleOptions.forEach((option) => {
        option.addEventListener('click', () => applyVariant(option.dataset.cupVariantOption ?? 'black'));
    });

    iconOptions.forEach((option) => {
        option.addEventListener('click', () => addDesignItem('icon', option.dataset.addIcon ?? ''));
        option.addEventListener('dragstart', (event) => {
            event.dataTransfer?.setData('application/x-cup-design', JSON.stringify({
                type: 'icon',
                content: option.dataset.addIcon ?? '',
            }));
        });
    });

    addTextButton?.addEventListener('click', () => {
        addDesignItem('text', cupDesigner.dataset.newText ?? 'Chữ của bạn');
        selectedContentInput?.focus();
        selectedContentInput?.select();
    });

    addTextButton?.addEventListener('dragstart', (event) => {
        event.dataTransfer?.setData('application/x-cup-design', JSON.stringify({
            type: 'text',
            content: cupDesigner.dataset.newText ?? 'Chữ của bạn',
        }));
    });

    printArea?.addEventListener('dragover', (event) => event.preventDefault());
    printArea?.addEventListener('drop', (event) => {
        event.preventDefault();

        const droppedDesign = event.dataTransfer?.getData('application/x-cup-design');

        if (!droppedDesign) {
            return;
        }

        const { type, content } = JSON.parse(droppedDesign);
        const areaRect = printArea.getBoundingClientRect();
        const width = type === 'text' ? 60 : 20;
        const x = ((event.clientX - areaRect.left) / areaRect.width) * 100 - width / 2;
        const y = ((event.clientY - areaRect.top) / areaRect.height) * 100 - 5;

        addDesignItem(type, content, x, y, width);
    });

    artworkInput?.addEventListener('change', async () => {
        const files = Array.from(artworkInput.files ?? []);

        if (files.length === 0) {
            return;
        }

        // Validate first file
        const firstFile = files[0];

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(firstFile.type) || firstFile.size > 5 * 1024 * 1024) {
            const errorMessage = 'Chọn ảnh JPG, PNG hoặc WEBP có dung lượng không quá 5 MB.';

            artworkInput.setCustomValidity(errorMessage);
            artworkInput.reportValidity();
            artworkInput.setCustomValidity('');

            if (artworkFiles.length > 0) {
                setArtworkInputFiles(artworkFiles.map((d) => d.file));
            }

            updateArtworkControls();

            return;
        }

        artworkInput.setCustomValidity('');

        // Add each new file
        for (const file of files) {
            // Check for duplicates by name (simple check)
            const isDuplicate = artworkFiles.some((existing) => existing.file.name === file.name);

            if (!isDuplicate) {
                const tempObjectUrl = URL.createObjectURL(file);
                artworkFiles.push({
                    file,
                    objectUrl: tempObjectUrl,
                    originalFile: file,
                    backgroundRemovalApplied: false,
                });
            }
        }

        // Sync input files
        setArtworkInputFiles(artworkFiles.map((d) => d.file));

        if (uploadCaption) {
            uploadCaption.textContent = `${artworkFiles.length} ảnh đã chọn`;
        }

        // Re-render to show all images
        renderDesignItems();
        updateArtworkControls();
        prepareBackgroundRemovalPipeline();

        // AUTO BACKGROUND REMOVAL: Trigger immediately after adding last image
        const lastImageIndex = artworkFiles.length - 1;
        if (lastImageIndex >= 0) {
            // Select the newly added image item
            const newImageItemIndex = designItems.findIndex(
                (item) => item.type === 'image' && item.image_index === lastImageIndex,
            );
            if (newImageItemIndex >= 0) {
                selectLayer(newImageItemIndex);
            }

            // Show status
            if (backgroundRemovalStatus) {
                backgroundRemovalStatus.textContent = 'Đang xử lý ảnh...';
                backgroundRemovalStatus.classList.add('processing');
            }

            // Auto-trigger background removal for the new image
            await performAutoBackgroundRemoval();
        }
    });

    const performAutoBackgroundRemoval = async () => {
        const selectedItem = selectedItemIndex !== null ? designItems[selectedItemIndex] : null;
        const selectedImageIndex = selectedItem?.type === 'image' ? selectedItem.image_index : null;
        const selectedImageData = selectedImageIndex !== null ? artworkFiles[selectedImageIndex] : null;

        if (!selectedImageData || selectedImageData.backgroundRemovalApplied || isProcessingBackgroundRemoval) {
            return;
        }

        isProcessingBackgroundRemoval = true;
        updateArtworkControls();

        if (backgroundRemovalStatus) {
            backgroundRemovalStatus.textContent = 'Đang khởi tạo mô hình AI...';
        }

        try {
            const removeBackground = await getBackgroundRemovalPipeline();

            if (backgroundRemovalStatus) {
                backgroundRemovalStatus.textContent = 'Đang tách nền ảnh tự động...';
            }

            const result = await removeBackground(selectedImageData.file);
            const transparentImage = await result.toBlob('image/png');

            if (transparentImage.size > 5 * 1024 * 1024) {
                throw new Error('Ảnh PNG sau khi tách nền vượt quá giới hạn 5 MB.');
            }

            const filename = `${selectedImageData.file.name.replace(/\.[^.]+$/, '')}-da-tach-nen.png`;
            const processedArtwork = new File([transparentImage], filename, { type: 'image/png' });

            // Update artwork files
            URL.revokeObjectURL(selectedImageData.objectUrl);
            artworkFiles[selectedImageIndex] = {
                file: processedArtwork,
                objectUrl: URL.createObjectURL(processedArtwork),
                originalFile: selectedImageData.originalFile,
                backgroundRemovalApplied: true,
            };
            updateArtworkInputFiles();
            showArtworkPreview(processedArtwork, filename, selectedImageIndex);

            if (backgroundRemovalStatus) {
                backgroundRemovalStatus.textContent = '✓ Đã tách nền tự động!';
                backgroundRemovalStatus.classList.remove('processing');
                backgroundRemovalStatus.classList.add('success');
            }
        } catch (error) {
            console.error('Không thể tách nền ảnh:', error);

            if (backgroundRemovalStatus) {
                backgroundRemovalStatus.textContent = error instanceof Error && error.message.includes('vượt quá giới hạn')
                    ? 'Ảnh sau khi tách nền vượt giới hạn 5 MB. Hãy thử ảnh có kích thước nhỏ hơn.'
                    : 'Không thể tách nền tự động. Vui lòng thử lại.';
                backgroundRemovalStatus.classList.remove('processing');
                backgroundRemovalStatus.classList.add('error');
            }
        } finally {
            isProcessingBackgroundRemoval = false;
            updateArtworkControls();
        }
    };

    removeBackgroundButton?.addEventListener('click', async () => {
        const selectedItem = selectedItemIndex !== null ? designItems[selectedItemIndex] : null;
        const selectedImageIndex = selectedItem?.type === 'image' ? selectedItem.image_index : null;
        const selectedImageData = selectedImageIndex !== null ? artworkFiles[selectedImageIndex] : null;

        if (!selectedImageData || isProcessingBackgroundRemoval) {
            return;
        }

        isProcessingBackgroundRemoval = true;
        updateArtworkControls();

        if (backgroundRemovalStatus) {
            backgroundRemovalStatus.textContent = 'Đang khởi tạo mô hình AI...';
        }

        try {
            const removeBackground = await getBackgroundRemovalPipeline();

            if (backgroundRemovalStatus) {
                backgroundRemovalStatus.textContent = 'Đang tách nền ảnh...';
            }

            const result = await removeBackground(selectedImageData.file);
            const transparentImage = await result.toBlob('image/png');

            if (transparentImage.size > 5 * 1024 * 1024) {
                throw new Error('Ảnh PNG sau khi tách nền vượt quá giới hạn 5 MB.');
            }

            const filename = `${selectedImageData.file.name.replace(/\.[^.]+$/, '')}-da-tach-nen.png`;
            const processedArtwork = new File([transparentImage], filename, { type: 'image/png' });

            // Update artwork files
            URL.revokeObjectURL(selectedImageData.objectUrl);
            artworkFiles[selectedImageIndex] = {
                file: processedArtwork,
                objectUrl: URL.createObjectURL(processedArtwork),
                originalFile: selectedImageData.originalFile,
                backgroundRemovalApplied: true,
            };
            updateArtworkInputFiles();
            showArtworkPreview(processedArtwork, `${filename} · nền trong suốt`, selectedImageIndex);

            if (backgroundRemovalStatus) {
                backgroundRemovalStatus.textContent = 'Đã tách nền. Ảnh PNG trong suốt sẽ được gửi cùng đơn hàng.';
            }
        } catch (error) {
            console.error('Không thể tách nền ảnh:', error);

            if (backgroundRemovalStatus) {
                backgroundRemovalStatus.textContent = error instanceof Error && error.message.includes('vượt quá giới hạn')
                    ? 'Ảnh sau khi tách nền vượt giới hạn 5 MB. Hãy thử ảnh có kích thước nhỏ hơn.'
                    : 'Không thể tách nền ảnh. Ảnh gốc vẫn được giữ; hãy thử lại hoặc chọn ảnh khác.';
            }
        } finally {
            isProcessingBackgroundRemoval = false;
            updateArtworkControls();
        }
    });

    restoreBackgroundButton?.addEventListener('click', () => {
        const selectedItem = selectedItemIndex !== null ? designItems[selectedItemIndex] : null;
        const selectedImageIndex = selectedItem?.type === 'image' ? selectedItem.image_index : null;
        const selectedImageData = selectedImageIndex !== null ? artworkFiles[selectedImageIndex] : null;

        if (!selectedImageData) {
            return;
        }

        // Restore original file
        URL.revokeObjectURL(selectedImageData.objectUrl);
        artworkFiles[selectedImageIndex] = {
            file: selectedImageData.originalFile,
            objectUrl: URL.createObjectURL(selectedImageData.originalFile),
            originalFile: selectedImageData.originalFile,
            backgroundRemovalApplied: false,
        };
        updateArtworkInputFiles();
        showArtworkPreview(selectedImageData.originalFile, selectedImageData.originalFile.name, selectedImageIndex);
        updateArtworkControls();

        if (backgroundRemovalStatus) {
            backgroundRemovalStatus.textContent = 'Đã khôi phục ảnh gốc.';
        }
    });

    orderForm?.addEventListener('submit', async (event) => {
        if (isProcessingBackgroundRemoval) {
            event.preventDefault();

            if (backgroundRemovalStatus) {
                backgroundRemovalStatus.textContent = 'Vui lòng chờ tách nền ảnh hoàn tất trước khi đặt hàng.';
            }

            return;
        }

        if (submitPreparedOrder) {
            submitPreparedOrder = false;
            return;
        }

        if (designPreviewInput) {
            event.preventDefault();

            if (isPreparingOrder) {
                return;
            }

            isPreparingOrder = true;
            const previewRendered = await renderCupPreviewSnapshot();
            isPreparingOrder = false;

            if (!previewRendered) {
                if (backgroundRemovalStatus) {
                    backgroundRemovalStatus.textContent = 'Không thể tạo ảnh xem trước thiết kế. Vui lòng tải lại trang và thử lại.';
                }

                return;
            }

            submitPreparedOrder = true;
            orderForm.requestSubmit();
        }
    });

    selectedContentInput?.addEventListener('input', () => {
        if (selectedItemIndex === null || designItems[selectedItemIndex]?.type !== 'text') {
            return;
        }

        designItems[selectedItemIndex].content = selectedContentInput.value.slice(0, 80);
        const selectedElement = designItemsElement?.children[selectedItemIndex];

        if (selectedElement) {
            selectedElement.firstChild.textContent = designItems[selectedItemIndex].content;
            selectedElement.setAttribute('aria-label', `Chọn chữ ${designItems[selectedItemIndex].content}`);
        }

        updateDesignFields();
    });

    fontSizeInput?.addEventListener('input', () => {
        if (selectedItemIndex === null || designItems[selectedItemIndex]?.type !== 'text') {
            return;
        }

        const item = designItems[selectedItemIndex];
        const selectedElement = designItemsElement?.children[selectedItemIndex];

        item.font_size = Number(fontSizeInput.value);

        if (selectedElement) {
            selectedElement.style.fontSize = `${getTextFontSize(item.font_size)}px`;
        }

        if (fontSizeValue) {
            fontSizeValue.value = `${item.font_size}%`;
        }

        updateDesignFields();
    });

    window.addEventListener('resize', () => {
        designItems.forEach((item, index) => {
            const element = designItemsElement?.children[index];

            if (item.type === 'text' && element) {
                element.style.fontSize = `${getTextFontSize(item.font_size)}px`;
            }
        });
    });

    removeLayerButton?.addEventListener('click', removeSelectedLayer);

    quantityInput?.addEventListener('input', updatePrice);
    quantityInput?.addEventListener('change', updatePrice);
    applyVariant(selectedVariant);
    renderDesignItems();
    updatePrice();
}

const cartCountStorageKey = 'storefront-cart-count';
const cartCountElements = document.querySelectorAll('[data-live-cart-count]');
const updateCartBadge = (count) => {
    cartCountElements.forEach((element) => {
        element.textContent = String(count);
    });
};

if (cartCountElements.length > 0) {
    const cartCount = Math.max(0, Number(document.body.dataset.cartCount ?? 0));

    updateCartBadge(cartCount);
    window.localStorage.setItem(cartCountStorageKey, String(cartCount));

    window.addEventListener('storage', (event) => {
        if (event.key === cartCountStorageKey && event.newValue !== null) {
            const nextCount = Number(event.newValue);

            if (Number.isFinite(nextCount) && nextCount >= 0) {
                updateCartBadge(nextCount);
            }
        }
    });
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
