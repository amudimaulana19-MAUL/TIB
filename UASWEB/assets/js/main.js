/**
 * Premium Football Shoes Marketplace
 * Main JavaScript
 */

$(document).ready(function() {
    // Loading Screen
    $(window).on('load', function() {
        setTimeout(function() {
            $('#loading-screen').addClass('hidden');
            setTimeout(function() {
                $('#loading-screen').remove();
            }, 500);
        }, 500);
    });

    // Navbar Scroll Effect
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });

    // Back to Top Button
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').addClass('visible');
        } else {
            $('.back-to-top').removeClass('visible');
        }
    });

    $('.back-to-top').click(function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 600);
    });

    // Dark Mode Toggle
    $('#darkModeToggle').click(function() {
        $('body').toggleClass('dark-mode');
        localStorage.setItem('darkMode', $('body').hasClass('dark-mode'));
    });

    // Check saved dark mode preference
    if (localStorage.getItem('darkMode') === 'true') {
        $('body').addClass('dark-mode');
    }

    // Mobile Menu Toggle
    $('.mobile-menu-toggle').click(function() {
        $('.mobile-menu').toggleClass('active');
        $(this).toggleClass('active');
    });

    // Sidebar Toggle (Admin)
    $('.sidebar-toggle').click(function() {
        $('.sidebar').toggleClass('active');
    });

    // Close sidebar when clicking outside (mobile)
    $(document).click(function(e) {
        if ($(window).width() < 992) {
            if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('.sidebar-toggle').length) {
                $('.sidebar').removeClass('active');
            }
        }
    });

    // Initialize AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }

    // SweetAlert Config
    if (typeof Swal !== 'undefined') {
        Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }

    // Product Card Hover Effect
    $('.product-card').hover(
        function() {
            $(this).find('.product-actions').css('bottom', '0');
        },
        function() {
            $(this).find('.product-actions').css('bottom', '-50px');
        }
    );

    // Add to Cart
    $('.add-to-cart').click(function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const quantity = $(this).data('quantity') || 1;
        
        $.ajax({
            url: 'ajax/add_to_cart.php',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: result.message,
                        timer: 1500
                    });
                    updateCartCount();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: result.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan. Silakan coba lagi.'
                });
            }
        });
    });

    // Add to Wishlist
    $('.add-to-wishlist').click(function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const btn = $(this);
        
        $.ajax({
            url: 'ajax/add_to_wishlist.php',
            method: 'POST',
            data: {
                product_id: productId,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    if (result.action === 'added') {
                        btn.find('i').removeClass('far').addClass('fas');
                        btn.addClass('active');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Produk ditambahkan ke wishlist',
                            timer: 1500
                        });
                    } else {
                        btn.find('i').removeClass('fas').addClass('far');
                        btn.removeClass('active');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Produk dihapus dari wishlist',
                            timer: 1500
                        });
                    }
                    updateWishlistCount();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: result.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan. Silakan coba lagi.'
                });
            }
        });
    });

    // Update Cart Count
    function updateCartCount() {
        $.ajax({
            url: 'ajax/get_cart_count.php',
            method: 'GET',
            success: function(response) {
                $('.cart-count').text(response);
            }
        });
    }

    // Update Wishlist Count
    function updateWishlistCount() {
        $.ajax({
            url: 'ajax/get_wishlist_count.php',
            method: 'GET',
            success: function(response) {
                $('.wishlist-count').text(response);
            }
        });
    }

    // Live Search
    $('#liveSearch').on('input', function() {
        const query = $(this).val();
        if (query.length >= 3) {
            $.ajax({
                url: 'ajax/live_search.php',
                method: 'GET',
                data: { q: query },
                success: function(response) {
                    $('#searchResults').html(response).show();
                }
            });
        } else {
            $('#searchResults').hide();
        }
    });

    // Close search results when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#liveSearch').length && !$(e.target).closest('#searchResults').length) {
            $('#searchResults').hide();
        }
    });

    // Product Filter
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: 'ajax/filter_products.php',
            method: 'GET',
            data: formData,
            success: function(response) {
                $('#productGrid').html(response);
            }
        });
    });

    // Load More Products (Infinite Scroll)
    let loading = false;
    let page = 2;
    
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 100 && !loading) {
            loading = true;
            $('.loading-more').show();
            
            $.ajax({
                url: 'ajax/load_more_products.php',
                method: 'GET',
                data: { page: page },
                success: function(response) {
                    if (response.trim() !== '') {
                        $('#productGrid').append(response);
                        page++;
                        loading = false;
                        $('.loading-more').hide();
                    } else {
                        $('.loading-more').hide();
                        $('.no-more-products').show();
                    }
                }
            });
        }
    });

    // Quantity Selector
    $('.qty-btn').click(function() {
        const input = $(this).siblings('.qty-input');
        let value = parseInt(input.val());
        
        if ($(this).hasClass('qty-plus')) {
            value++;
        } else if ($(this).hasClass('qty-minus') && value > 1) {
            value--;
        }
        
        input.val(value);
        input.trigger('change');
    });

    // Update Cart Quantity
    $('.qty-input').on('change', function() {
        const cartItemId = $(this).data('cart-item-id');
        const quantity = $(this).val();
        
        $.ajax({
            url: 'ajax/update_cart_quantity.php',
            method: 'POST',
            data: {
                cart_item_id: cartItemId,
                quantity: quantity,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    location.reload();
                }
            }
        });
    });

    // Remove from Cart
    $('.remove-from-cart').click(function(e) {
        e.preventDefault();
        const cartItemId = $(this).data('cart-item-id');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Produk akan dihapus dari keranjang',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'ajax/remove_from_cart.php',
                    method: 'POST',
                    data: {
                        cart_item_id: cartItemId,
                        csrf_token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: result.message,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            }
        });
    });

    // Remove from Wishlist
    $('.remove-from-wishlist').click(function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Produk akan dihapus dari wishlist',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'ajax/remove_from_wishlist.php',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        csrf_token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: result.message,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            }
        });
    });

    // Product Image Gallery
    $('.thumbnail').click(function() {
        const src = $(this).data('src');
        $('.main-image').attr('src', src);
        $('.thumbnail').removeClass('active');
        $(this).addClass('active');
    });

    // Product Rating
    $('.rating-star').click(function() {
        const rating = $(this).data('rating');
        $('.rating-star').each(function() {
            const starRating = $(this).data('rating');
            if (starRating <= rating) {
                $(this).removeClass('far').addClass('fas text-warning');
            } else {
                $(this).removeClass('fas text-warning').addClass('far');
            }
        });
        $('#ratingInput').val(rating);
    });

    // Form Validation
    $('form[data-validate]').on('submit', function(e) {
        let isValid = true;
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if ($(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Mohon lengkapi semua field yang wajib diisi'
            });
        }
    });

    // Image Preview
    $('input[type="file"]').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(this).siblings('.image-preview').html('<img src="' + e.target.result + '" class="img-fluid rounded">');
            }.bind(this);
            reader.readAsDataURL(file);
        }
    });

    // Price Range Slider
    if ($('#priceRange').length) {
        $('#priceRange').slider({
            range: true,
            min: 0,
            max: 5000000,
            values: [0, 5000000],
            slide: function(event, ui) {
                $('#priceMin').val(ui.values[0]);
                $('#priceMax').val(ui.values[1]);
                $('#priceDisplay').text(formatRupiah(ui.values[0]) + ' - ' + formatRupiah(ui.values[1]));
            }
        });
    }

    // Format Rupiah
    function formatRupiah(number) {
        return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Initialize Tooltips
    if (typeof $.fn.tooltip !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Initialize Popovers
    if (typeof $.fn.popover !== 'undefined') {
        $('[data-toggle="popover"]').popover();
    }

    // Smooth Scroll for Anchor Links
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });

    // Counter Animation
    $('.counter').each(function() {
        const $this = $(this);
        const target = parseInt($this.data('target'));
        
        $({ countNum: 0 }).animate({
            countNum: target
        }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                $this.text(Math.floor(this.countNum));
            },
            complete: function() {
                $this.text(target);
            }
        });
    });

    // Initialize on page load
    updateCartCount();
    updateWishlistCount();
});

// Export functions for use in other scripts
window.updateCartCount = function() {
    $.ajax({
        url: 'ajax/get_cart_count.php',
        method: 'GET',
        success: function(response) {
            $('.cart-count').text(response);
        }
    });
};

window.updateWishlistCount = function() {
    $.ajax({
        url: 'ajax/get_wishlist_count.php',
        method: 'GET',
        success: function(response) {
            $('.wishlist-count').text(response);
        }
    });
};
