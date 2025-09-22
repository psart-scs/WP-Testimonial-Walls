/**
 * WP Testimonial Walls - Admin JavaScript
 * Handles admin interface functionality
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initMediaUploader();
        initTestimonialManager();
        initWallPreview();
    });

    /**
     * Initialize media uploader for logos
     */
    function initMediaUploader() {
        let mediaUploader;

        $(document).on('click', '.testimonial-logo-upload', function(e) {
            e.preventDefault();

            const button = $(this);
            const targetInput = button.data('target');
            const previewContainer = button.closest('.testimonial-logo-wrapper').find('.testimonial-logo-preview');

            // If the uploader object has already been created, reopen the dialog
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Create the media uploader
            mediaUploader = wp.media({
                title: wpTestimonialWallsAdmin.strings.selectLogo,
                button: {
                    text: wpTestimonialWallsAdmin.strings.selectLogo
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });

            // When an image is selected, run a callback
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Set the image ID
                $('#' + targetInput).val(attachment.id);
                
                // Show preview
                const imgHtml = `<img src="${attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url}" alt="${attachment.alt}" />`;
                previewContainer.html(imgHtml);
                
                // Show remove button
                button.siblings('.testimonial-logo-remove').show();
            });

            // Open the uploader dialog
            mediaUploader.open();
        });

        // Remove logo
        $(document).on('click', '.testimonial-logo-remove', function(e) {
            e.preventDefault();

            const button = $(this);
            const wrapper = button.closest('.testimonial-logo-wrapper');
            const targetInput = wrapper.find('input[type="hidden"]');
            const previewContainer = wrapper.find('.testimonial-logo-preview');

            // Clear values
            targetInput.val('');
            previewContainer.empty();
            button.hide();
        });
    }

    /**
     * Initialize testimonial manager for walls
     */
    function initTestimonialManager() {
        // Make selected testimonials sortable
        $('.selected-list').sortable({
            handle: '.drag-handle',
            placeholder: 'testimonial-item-placeholder',
            update: function() {
                // Update hidden inputs order
                updateTestimonialOrder();
            }
        });

        // Add testimonial to wall
        $(document).on('click', '.add-testimonial', function(e) {
            e.preventDefault();

            const button = $(this);
            const item = button.closest('.testimonial-item');
            const testimonialId = item.data('id');
            const testimonialTitle = item.find('.testimonial-title').text();

            // Create new item for selected list
            const newItem = $(`
                <div class="testimonial-item" data-id="${testimonialId}">
                    <span class="dashicons dashicons-menu drag-handle"></span>
                    <span class="testimonial-title">${testimonialTitle}</span>
                    <button type="button" class="button button-small remove-testimonial">
                        ${wpTestimonialWallsAdmin.strings.remove || 'Remove'}
                    </button>
                    <input type="hidden" name="wall_testimonials[]" value="${testimonialId}" />
                </div>
            `);

            // Add to selected list
            $('.selected-list').append(newItem);

            // Remove from available list
            item.remove();

            // Update order
            updateTestimonialOrder();
        });

        // Remove testimonial from wall
        $(document).on('click', '.remove-testimonial', function(e) {
            e.preventDefault();

            if (!confirm(wpTestimonialWallsAdmin.strings.confirmRemove)) {
                return;
            }

            const button = $(this);
            const item = button.closest('.testimonial-item');
            const testimonialId = item.data('id');
            const testimonialTitle = item.find('.testimonial-title').text();

            // Create new item for available list
            const newItem = $(`
                <div class="testimonial-item" data-id="${testimonialId}">
                    <span class="testimonial-title">${testimonialTitle}</span>
                    <button type="button" class="button button-small add-testimonial">
                        ${wpTestimonialWallsAdmin.strings.add || 'Add'}
                    </button>
                </div>
            `);

            // Add to available list (sorted alphabetically)
            const availableList = $('.available-list');
            let inserted = false;

            availableList.find('.testimonial-item').each(function() {
                const existingTitle = $(this).find('.testimonial-title').text();
                if (testimonialTitle.localeCompare(existingTitle) < 0) {
                    $(this).before(newItem);
                    inserted = true;
                    return false;
                }
            });

            if (!inserted) {
                availableList.append(newItem);
            }

            // Remove from selected list
            item.remove();

            // Update order
            updateTestimonialOrder();
        });

        function updateTestimonialOrder() {
            $('.selected-list .testimonial-item').each(function(index) {
                $(this).find('input[name="wall_testimonials[]"]').val($(this).data('id'));
            });
        }
    }

    /**
     * Initialize wall preview functionality
     */
    function initWallPreview() {
        $(document).on('click', '.preview-wall', function(e) {
            e.preventDefault();

            const button = $(this);
            const wallId = button.data('wall-id');

            if (!wallId) {
                return;
            }

            // Show loading state
            button.prop('disabled', true).text('Loading...');

            // Create modal
            const modal = $(`
                <div class="wp-testimonial-wall-preview-modal">
                    <div class="wp-testimonial-wall-preview-backdrop"></div>
                    <div class="wp-testimonial-wall-preview-content">
                        <div class="wp-testimonial-wall-preview-header">
                            <h2>Wall Preview</h2>
                            <button type="button" class="wp-testimonial-wall-preview-close">×</button>
                        </div>
                        <div class="wp-testimonial-wall-preview-body">
                            <div class="wp-testimonial-wall-preview-loading">Loading preview...</div>
                        </div>
                    </div>
                </div>
            `);

            // Add modal styles
            if (!$('#wp-testimonial-wall-preview-styles').length) {
                $('head').append(`
                    <style id="wp-testimonial-wall-preview-styles">
                    .wp-testimonial-wall-preview-modal {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        z-index: 100000;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .wp-testimonial-wall-preview-backdrop {
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0, 0, 0, 0.7);
                    }
                    .wp-testimonial-wall-preview-content {
                        position: relative;
                        background: white;
                        border-radius: 4px;
                        max-width: 90vw;
                        max-height: 90vh;
                        overflow: hidden;
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                    }
                    .wp-testimonial-wall-preview-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 20px;
                        border-bottom: 1px solid #ddd;
                    }
                    .wp-testimonial-wall-preview-header h2 {
                        margin: 0;
                    }
                    .wp-testimonial-wall-preview-close {
                        background: none;
                        border: none;
                        font-size: 24px;
                        cursor: pointer;
                        padding: 0;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .wp-testimonial-wall-preview-body {
                        padding: 20px;
                        max-height: 70vh;
                        overflow-y: auto;
                    }
                    .wp-testimonial-wall-preview-loading {
                        text-align: center;
                        padding: 40px;
                        color: #666;
                    }
                    </style>
                `);
            }

            // Add to body
            $('body').append(modal);

            // Load preview content via AJAX
            $.post(ajaxurl, {
                action: 'get_wall_preview',
                wall_id: wallId,
                nonce: wpTestimonialWallsAdmin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    const data = response.data;
                    let previewHtml = `
                        <h3>${data.title}</h3>
                        <p><strong>Layout:</strong> ${data.layout}</p>
                        <p><strong>Columns:</strong> ${data.columns}</p>
                        <p><strong>Show Logos:</strong> ${data.show_logos ? 'Yes' : 'No'}</p>
                        <p><strong>Testimonials:</strong> ${data.testimonials_count}</p>
                    `;

                    if (data.testimonials.length > 0) {
                        previewHtml += '<h4>Sample Testimonials:</h4>';
                        data.testimonials.forEach(function(testimonial) {
                            previewHtml += `
                                <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 4px;">
                                    <p><em>"${testimonial.content}..."</em></p>
                                    <p><strong>${testimonial.person_name}</strong>
                                    ${testimonial.company ? `<br><small>${testimonial.company}</small>` : ''}
                                    </p>
                                </div>
                            `;
                        });
                    }

                    previewHtml += `
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                            <p><strong>Shortcode:</strong></p>
                            <input type="text" value='[wp_testimonial_wall id="${wallId}"]' 
                                   style="width: 100%; padding: 8px;" readonly onclick="this.select();" />
                        </div>
                    `;

                    modal.find('.wp-testimonial-wall-preview-body').html(previewHtml);
                } else {
                    modal.find('.wp-testimonial-wall-preview-body').html(
                        '<p style="color: red;">Error loading preview: ' + response.data + '</p>'
                    );
                }
            })
            .fail(function() {
                modal.find('.wp-testimonial-wall-preview-body').html(
                    '<p style="color: red;">Error loading preview.</p>'
                );
            })
            .always(function() {
                button.prop('disabled', false).text('Preview');
            });

            // Close modal events
            modal.on('click', '.wp-testimonial-wall-preview-close, .wp-testimonial-wall-preview-backdrop', function() {
                modal.remove();
            });

            // Escape key to close
            $(document).on('keyup.preview-modal', function(e) {
                if (e.keyCode === 27) {
                    modal.remove();
                    $(document).off('keyup.preview-modal');
                }
            });
        });
    }

})(jQuery);
