/**
 * WP Testimonial Walls - Gutenberg Block
 * React-based block for the WordPress block editor
 */

(function(wp) {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { createElement: el, Component, Fragment } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { 
        PanelBody, 
        SelectControl, 
        RangeControl, 
        ToggleControl,
        Placeholder,
        Spinner,
        Notice
    } = wp.components;
    const { __ } = wp.i18n;
    const { apiFetch } = wp;

    /**
     * Block Component
     */
    class TestimonialWallBlock extends Component {
        constructor(props) {
            super(props);
            
            this.state = {
                walls: [],
                loading: true,
                error: null,
                preview: null,
                loadingPreview: false
            };
        }

        componentDidMount() {
            this.loadWalls();
            
            if (this.props.attributes.wallId) {
                this.loadPreview(this.props.attributes.wallId);
            }
        }

        componentDidUpdate(prevProps) {
            if (prevProps.attributes.wallId !== this.props.attributes.wallId && this.props.attributes.wallId) {
                this.loadPreview(this.props.attributes.wallId);
            }
        }

        loadWalls() {
            apiFetch({
                path: '/wp/v2/wall?status=publish&per_page=100'
            })
            .then(walls => {
                this.setState({ 
                    walls: walls.map(wall => ({
                        value: wall.id,
                        label: wall.title.rendered
                    })),
                    loading: false 
                });
            })
            .catch(error => {
                this.setState({ 
                    error: __('Failed to load walls', 'wp-testimonial-walls'),
                    loading: false 
                });
            });
        }

        loadPreview(wallId) {
            if (!wallId) {
                this.setState({ preview: null });
                return;
            }

            this.setState({ loadingPreview: true });

            wp.ajax.post('get_wall_preview', {
                wall_id: wallId,
                nonce: wpTestimonialWallsBlocks.nonce
            })
            .done(response => {
                this.setState({ 
                    preview: response,
                    loadingPreview: false 
                });
            })
            .fail(() => {
                this.setState({ 
                    preview: null,
                    loadingPreview: false 
                });
            });
        }

        render() {
            const { attributes, setAttributes } = this.props;
            const { wallId, layout, columns, showLogos } = attributes;
            const { walls, loading, error, preview, loadingPreview } = this.state;

            // Loading state
            if (loading) {
                return el(Placeholder, {
                    icon: 'format-quote',
                    label: __('Testimonial Wall', 'wp-testimonial-walls')
                }, el(Spinner));
            }

            // Error state
            if (error) {
                return el(Notice, {
                    status: 'error',
                    isDismissible: false
                }, error);
            }

            // No walls available
            if (walls.length === 0) {
                return el(Notice, {
                    status: 'warning',
                    isDismissible: false
                }, __('No testimonial walls found. Please create a wall first.', 'wp-testimonial-walls'));
            }

            // Wall selection
            if (!wallId) {
                return el(Fragment, null, [
                    el(InspectorControls, { key: 'inspector' },
                        el(PanelBody, {
                            title: __('Wall Settings', 'wp-testimonial-walls'),
                            initialOpen: true
                        },
                            el(SelectControl, {
                                label: __('Select Wall', 'wp-testimonial-walls'),
                                value: wallId,
                                options: [
                                    { value: '', label: __('Choose a wall...', 'wp-testimonial-walls') },
                                    ...walls
                                ],
                                onChange: (value) => setAttributes({ wallId: parseInt(value) })
                            })
                        )
                    ),
                    el(Placeholder, {
                        key: 'placeholder',
                        icon: 'format-quote',
                        label: __('Testimonial Wall', 'wp-testimonial-walls'),
                        instructions: __('Select a testimonial wall to display.', 'wp-testimonial-walls')
                    },
                        el(SelectControl, {
                            value: wallId,
                            options: [
                                { value: '', label: __('Choose a wall...', 'wp-testimonial-walls') },
                                ...walls
                            ],
                            onChange: (value) => setAttributes({ wallId: parseInt(value) })
                        })
                    )
                ]);
            }

            // Get wall options from preview
            const wallOptions = preview ? {
                layout: preview.layout,
                columns: preview.columns,
                showLogos: preview.show_logos
            } : {};

            return el(Fragment, null, [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, {
                        title: __('Wall Settings', 'wp-testimonial-walls'),
                        initialOpen: true
                    },
                        el(SelectControl, {
                            label: __('Select Wall', 'wp-testimonial-walls'),
                            value: wallId,
                            options: [
                                { value: '', label: __('Choose a wall...', 'wp-testimonial-walls') },
                                ...walls
                            ],
                            onChange: (value) => setAttributes({ wallId: parseInt(value) })
                        }),
                        
                        el(SelectControl, {
                            label: __('Layout Override', 'wp-testimonial-walls'),
                            value: layout,
                            options: [
                                { value: '', label: __('Use wall default', 'wp-testimonial-walls') },
                                { value: 'grid', label: __('Grid', 'wp-testimonial-walls') },
                                { value: 'slider', label: __('Slider', 'wp-testimonial-walls') },
                                { value: 'masonry', label: __('Masonry', 'wp-testimonial-walls') }
                            ],
                            onChange: (value) => setAttributes({ layout: value }),
                            help: preview ? __(`Wall default: ${wallOptions.layout}`, 'wp-testimonial-walls') : ''
                        }),
                        
                        (layout === 'grid' || layout === 'masonry' || (!layout && (wallOptions.layout === 'grid' || wallOptions.layout === 'masonry'))) &&
                        el(RangeControl, {
                            label: __('Columns Override', 'wp-testimonial-walls'),
                            value: columns,
                            onChange: (value) => setAttributes({ columns: value }),
                            min: 0,
                            max: 4,
                            help: columns === 0 ? 
                                (preview ? __(`Using wall default: ${wallOptions.columns}`, 'wp-testimonial-walls') : __('Using wall default', 'wp-testimonial-walls')) :
                                __('0 = use wall default', 'wp-testimonial-walls')
                        }),
                        
                        el(ToggleControl, {
                            label: __('Show Company Logos', 'wp-testimonial-walls'),
                            checked: showLogos,
                            onChange: (value) => setAttributes({ showLogos: value }),
                            help: preview ? __(`Wall default: ${wallOptions.showLogos ? 'enabled' : 'disabled'}`, 'wp-testimonial-walls') : ''
                        })
                    )
                ),
                
                el('div', {
                    key: 'preview',
                    className: 'wp-testimonial-wall-block-preview'
                },
                    loadingPreview ? 
                        el(Placeholder, {
                            icon: 'format-quote',
                            label: __('Loading preview...', 'wp-testimonial-walls')
                        }, el(Spinner)) :
                        
                        preview ? 
                            this.renderPreview(preview, { layout, columns, showLogos }) :
                            
                            el(Placeholder, {
                                icon: 'format-quote',
                                label: __('Testimonial Wall', 'wp-testimonial-walls'),
                                instructions: __('Preview not available', 'wp-testimonial-walls')
                            })
                )
            ]);
        }

        renderPreview(preview, overrides) {
            const effectiveLayout = overrides.layout || preview.layout;
            const effectiveColumns = overrides.columns || preview.columns;
            const effectiveShowLogos = overrides.showLogos !== undefined ? overrides.showLogos : preview.show_logos;

            return el('div', {
                className: `wp-testimonial-wall-preview wp-testimonial-wall-preview--${effectiveLayout}`,
                style: {
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    padding: '20px',
                    backgroundColor: '#f9f9f9'
                }
            }, [
                el('h3', { 
                    key: 'title',
                    style: { marginTop: 0, marginBottom: '10px' }
                }, preview.title),
                
                el('div', {
                    key: 'meta',
                    style: { 
                        fontSize: '12px', 
                        color: '#666', 
                        marginBottom: '15px',
                        display: 'flex',
                        gap: '15px',
                        flexWrap: 'wrap'
                    }
                }, [
                    el('span', { key: 'layout' }, `Layout: ${effectiveLayout}`),
                    (effectiveLayout === 'grid' || effectiveLayout === 'masonry') &&
                    el('span', { key: 'columns' }, `Columns: ${effectiveColumns}`),
                    el('span', { key: 'logos' }, `Logos: ${effectiveShowLogos ? 'Yes' : 'No'}`),
                    el('span', { key: 'count' }, `${preview.testimonials_count} testimonials`)
                ].filter(Boolean)),
                
                preview.testimonials.length > 0 ?
                    el('div', {
                        key: 'testimonials',
                        className: 'wp-testimonial-wall-preview__testimonials',
                        style: {
                            display: 'grid',
                            gap: '15px',
                            gridTemplateColumns: effectiveLayout === 'slider' ? '1fr' : 
                                `repeat(${Math.min(effectiveColumns, preview.testimonials.length)}, 1fr)`
                        }
                    }, 
                        preview.testimonials.slice(0, effectiveLayout === 'slider' ? 1 : 3).map((testimonial, index) =>
                            el('div', {
                                key: testimonial.id,
                                style: {
                                    background: 'white',
                                    border: '1px solid #e1e1e1',
                                    borderRadius: '4px',
                                    padding: '15px',
                                    fontSize: '14px'
                                }
                            }, [
                                el('p', { 
                                    key: 'content',
                                    style: { 
                                        fontStyle: 'italic', 
                                        marginBottom: '10px',
                                        lineHeight: '1.4'
                                    }
                                }, `"${testimonial.content}"`),
                                
                                el('div', {
                                    key: 'author',
                                    style: { fontSize: '12px' }
                                }, [
                                    el('strong', { key: 'name' }, testimonial.person_name),
                                    testimonial.company && 
                                    el('div', { 
                                        key: 'company',
                                        style: { color: '#666' }
                                    }, testimonial.company)
                                ].filter(Boolean))
                            ])
                        )
                    ) :
                    
                    el('p', {
                        key: 'empty',
                        style: { 
                            textAlign: 'center', 
                            color: '#666',
                            fontStyle: 'italic'
                        }
                    }, __('No testimonials in this wall', 'wp-testimonial-walls')),
                
                el('div', {
                    key: 'shortcode',
                    style: {
                        marginTop: '15px',
                        paddingTop: '15px',
                        borderTop: '1px solid #ddd',
                        fontSize: '12px'
                    }
                }, [
                    el('strong', { key: 'label' }, __('Shortcode: ', 'wp-testimonial-walls')),
                    el('code', { 
                        key: 'code',
                        style: { 
                            background: '#f1f1f1',
                            padding: '2px 6px',
                            borderRadius: '3px'
                        }
                    }, `[wp_testimonial_wall id="${preview.id}"]`)
                ])
            ]);
        }
    }

    /**
     * Register the block
     */
    registerBlockType('wp-testimonial-walls/wall', {
        title: __('Testimonial Wall', 'wp-testimonial-walls'),
        description: __('Display a testimonial wall with customizable layout and styling.', 'wp-testimonial-walls'),
        icon: 'format-quote',
        category: 'widgets',
        keywords: [
            __('testimonial', 'wp-testimonial-walls'),
            __('review', 'wp-testimonial-walls'),
            __('wall', 'wp-testimonial-walls')
        ],
        supports: {
            align: ['wide', 'full'],
            html: false
        },
        attributes: {
            wallId: {
                type: 'number',
                default: 0
            },
            layout: {
                type: 'string',
                default: ''
            },
            columns: {
                type: 'number',
                default: 0
            },
            showLogos: {
                type: 'boolean',
                default: true
            }
        },
        edit: TestimonialWallBlock,
        save: function() {
            // Server-side rendering
            return null;
        }
    });

})(window.wp);
