/**
 * WP Testimonial Walls - Frontend JavaScript
 * Handles slider functionality, accessibility, and user interactions
 */

(function($) {
    'use strict';

    // Default configuration
    const defaults = {
        autoplay: true,
        autoplayDelay: 5000,
        pauseOnHover: true,
        keyboardNavigation: true,
        touchSwipe: true,
        lazyLoading: true
    };

    // Merge with global config
    const config = $.extend({}, defaults, window.wpTestimonialWalls?.config || {});

    /**
     * Testimonial Wall Class
     */
    class TestimonialWall {
        constructor(element) {
            this.$wall = $(element);
            this.layout = this.$wall.data('layout') || 'grid';
            this.columns = this.$wall.data('columns') || 3;
            this.currentSlide = 0;
            this.isPlaying = config.autoplay;
            this.autoplayTimer = null;
            this.touchStartX = 0;
            this.touchEndX = 0;

            this.init();
        }

        init() {
            if (this.layout === 'slider') {
                this.initSlider();
            } else if (this.layout === 'masonry') {
                this.initMasonry();
            }

            this.initAccessibility();
            this.initLazyLoading();
            this.bindEvents();
        }

        initSlider() {
            this.$container = this.$wall.find('.wp-testimonial-wall__container');
            this.$items = this.$wall.find('.wp-testimonial-wall__item');
            this.$controls = this.$wall.find('.wp-testimonial-wall__control');
            this.$dots = this.$wall.find('.wp-testimonial-wall__dot');

            // Set initial state
            this.updateSliderState();
            this.updatePlayPauseIcon();

            // Start autoplay if enabled
            if (this.isPlaying) {
                this.startAutoplay();
            }
        }

        initMasonry() {
            // Simple masonry layout using CSS Grid
            // The CSS handles most of the work, we just need to ensure proper loading
            this.$wall.addClass('wp-testimonial-wall--masonry-loaded');
        }

        initAccessibility() {
            // Add ARIA labels and roles
            this.$wall.attr('role', 'region');
            this.$wall.attr('aria-label', wpTestimonialWalls.strings.testimonials || 'Testimonials');

            if (this.layout === 'slider') {
                this.$items.each((index, item) => {
                    $(item).attr('role', 'tabpanel');
                    $(item).attr('aria-hidden', index !== this.currentSlide);
                });

                this.$dots.each((index, dot) => {
                    $(dot).attr('role', 'tab');
                    $(dot).attr('aria-selected', index === this.currentSlide);
                });
            }
        }

        initLazyLoading() {
            if (!config.lazyLoading) return;

            const images = this.$wall.find('img[loading="lazy"]');
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src || img.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                images.each((index, img) => {
                    imageObserver.observe(img);
                });
            }
        }

        bindEvents() {
            if (this.layout !== 'slider') return;

            // Control buttons
            this.$wall.on('click', '.wp-testimonial-wall__control--prev', () => {
                this.prevSlide();
            });

            this.$wall.on('click', '.wp-testimonial-wall__control--next', () => {
                this.nextSlide();
            });

            this.$wall.on('click', '.wp-testimonial-wall__control--play-pause', () => {
                this.togglePlayPause();
            });

            // Dots navigation
            this.$wall.on('click', '.wp-testimonial-wall__dot', (e) => {
                const slideIndex = $(e.target).data('slide');
                this.goToSlide(slideIndex);
            });

            // Keyboard navigation
            if (config.keyboardNavigation) {
                this.$wall.on('keydown', (e) => {
                    switch(e.key) {
                        case 'ArrowLeft':
                            e.preventDefault();
                            this.prevSlide();
                            break;
                        case 'ArrowRight':
                            e.preventDefault();
                            this.nextSlide();
                            break;
                        case ' ':
                            e.preventDefault();
                            this.togglePlayPause();
                            break;
                    }
                });
            }

            // Touch/swipe support
            if (config.touchSwipe) {
                this.$container.on('touchstart', (e) => {
                    this.touchStartX = e.touches[0].clientX;
                });

                this.$container.on('touchend', (e) => {
                    this.touchEndX = e.changedTouches[0].clientX;
                    this.handleSwipe();
                });
            }

            // Pause on hover
            if (config.pauseOnHover) {
                this.$wall.on('mouseenter', () => {
                    this.pauseAutoplay();
                });

                this.$wall.on('mouseleave', () => {
                    if (this.isPlaying) {
                        this.startAutoplay();
                    }
                });
            }

            // Pause when tab is not visible
            $(document).on('visibilitychange', () => {
                if (document.hidden) {
                    this.pauseAutoplay();
                } else if (this.isPlaying) {
                    this.startAutoplay();
                }
            });
        }

        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.$items.length;
            this.updateSliderState();
            this.announceSlideChange();
        }

        prevSlide() {
            this.currentSlide = this.currentSlide === 0 ? this.$items.length - 1 : this.currentSlide - 1;
            this.updateSliderState();
            this.announceSlideChange();
        }

        goToSlide(index) {
            if (index >= 0 && index < this.$items.length) {
                this.currentSlide = index;
                this.updateSliderState();
                this.announceSlideChange();
            }
        }

        updateSliderState() {
            // Update items
            this.$items.removeClass('wp-testimonial-wall__item--active wp-testimonial-wall__item--hidden');
            this.$items.eq(this.currentSlide).addClass('wp-testimonial-wall__item--active');
            this.$items.not(':eq(' + this.currentSlide + ')').addClass('wp-testimonial-wall__item--hidden');

            // Update dots
            this.$dots.removeClass('wp-testimonial-wall__dot--active');
            this.$dots.eq(this.currentSlide).addClass('wp-testimonial-wall__dot--active');

            // Update ARIA attributes
            this.$items.attr('aria-hidden', true);
            this.$items.eq(this.currentSlide).attr('aria-hidden', false);

            this.$dots.attr('aria-selected', false);
            this.$dots.eq(this.currentSlide).attr('aria-selected', true);

            // Transform container for smooth transition
            const translateX = -this.currentSlide * 100;
            this.$container.css('transform', `translateX(${translateX}%)`);
        }

        togglePlayPause() {
            this.isPlaying = !this.isPlaying;
            
            if (this.isPlaying) {
                this.startAutoplay();
            } else {
                this.pauseAutoplay();
            }
            
            this.updatePlayPauseIcon();
            this.announcePlayState();
        }

        startAutoplay() {
            this.pauseAutoplay(); // Clear existing timer
            
            if (this.isPlaying && this.$items.length > 1) {
                this.autoplayTimer = setInterval(() => {
                    this.nextSlide();
                }, config.autoplayDelay);
            }
        }

        pauseAutoplay() {
            if (this.autoplayTimer) {
                clearInterval(this.autoplayTimer);
                this.autoplayTimer = null;
            }
        }

        updatePlayPauseIcon() {
            this.$wall.toggleClass('wp-testimonial-wall--playing', this.isPlaying);
            this.$wall.toggleClass('wp-testimonial-wall--paused', !this.isPlaying);
        }

        handleSwipe() {
            const swipeThreshold = 50;
            const diff = this.touchStartX - this.touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }
        }

        announceSlideChange() {
            // Announce slide change to screen readers
            const announcement = wpTestimonialWalls.strings.slideChanged || 
                `Slide ${this.currentSlide + 1} of ${this.$items.length}`;
            this.announce(announcement);
        }

        announcePlayState() {
            const announcement = this.isPlaying ? 
                (wpTestimonialWalls.strings.playing || 'Slideshow playing') :
                (wpTestimonialWalls.strings.paused || 'Slideshow paused');
            this.announce(announcement);
        }

        announce(message) {
            // Create or update live region for screen reader announcements
            let $liveRegion = this.$wall.find('.wp-testimonial-wall__live-region');
            
            if (!$liveRegion.length) {
                $liveRegion = $('<div class="wp-testimonial-wall__live-region screen-reader-text" aria-live="polite"></div>');
                this.$wall.append($liveRegion);
            }
            
            $liveRegion.text(message);
        }

        destroy() {
            this.pauseAutoplay();
            this.$wall.off();
            this.$wall.removeData('testimonialWall');
        }
    }

    /**
     * jQuery Plugin
     */
    $.fn.testimonialWall = function(options) {
        return this.each(function() {
            if (!$(this).data('testimonialWall')) {
                $(this).data('testimonialWall', new TestimonialWall(this));
            }
        });
    };

    /**
     * Auto-initialize on DOM ready
     */
    $(document).ready(function() {
        $('.wp-testimonial-wall').testimonialWall();
    });

    /**
     * Re-initialize on AJAX content load
     */
    $(document).on('wp-testimonial-walls-refresh', function() {
        $('.wp-testimonial-wall').each(function() {
            if (!$(this).data('testimonialWall')) {
                $(this).testimonialWall();
            }
        });
    });

    /**
     * Handle window resize for responsive adjustments
     */
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            $('.wp-testimonial-wall').each(function() {
                const wall = $(this).data('testimonialWall');
                if (wall && wall.layout === 'masonry') {
                    // Trigger masonry recalculation if needed
                    $(this).trigger('wp-testimonial-wall-resize');
                }
            });
        }, 250);
    });

    /**
     * Expose for external access
     */
    window.wpTestimonialWalls = window.wpTestimonialWalls || {};
    window.wpTestimonialWalls.TestimonialWall = TestimonialWall;

})(jQuery);
