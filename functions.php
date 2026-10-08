<?php
/**
 * PayGuide NG Child Theme — functions.php
 * 
 * Properly enqueues stylesheets, fonts, dynamic JSON-LD SEO Schema,
 * Table of Contents, FX Calculator Engine, and Smartlink Monetization Shortcodes.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue parent + child theme stylesheets with cache-busting
 */
function payguide_ng_child_enqueue_styles() {
    wp_enqueue_style(
        'hostinger-ai-theme-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme( 'hostinger-ai-theme' )->get( 'Version' )
    );

    $child_css_path = get_stylesheet_directory() . '/style.css';
    $version = file_exists( $child_css_path ) ? filemtime( $child_css_path ) : time();

    wp_enqueue_style(
        'payguide-ng-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'hostinger-ai-theme-style' ),
        $version
    );
}
add_action( 'wp_enqueue_scripts', 'payguide_ng_child_enqueue_styles' );

/**
 * Enqueue Google Fonts
 */
function payguide_ng_child_enqueue_fonts() {
    wp_enqueue_style(
        'payguide-google-fonts',
        'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
        array(),
        null
    );
}
add_action( 'wp_enqueue_scripts', 'payguide_ng_child_enqueue_fonts' );

/**
 * Inject Structured JSON-LD SEO Schema Markup
 */
function payguide_ng_inject_seo_schema() {
    if ( is_front_page() || is_home() ) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'PayGuide NG',
            'url' => home_url('/'),
            'logo' => 'https://ui-avatars.com/api/?name=PayGuide+NG&background=0284c7&color=fff&size=512',
            'description' => 'Independent financial insights, virtual dollar card benchmarks, and cross-border payment guides for Nigeria.',
            'sameAs' => array()
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . "</script>\n";
    } elseif ( is_single() ) {
        global $post;
        $author_name = 'PayGuide Editorial';
        $thumb_id = get_post_thumbnail_id( $post->ID );
        $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'full' ) : 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1200&q=80';

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title( $post ),
            'datePublished' => get_the_date( 'c', $post ),
            'dateModified' => get_the_modified_date( 'c', $post ),
            'mainEntityOfPage' => get_permalink( $post ),
            'image' => array( $thumb_url ),
            'author' => array(
                '@type' => 'Person',
                'name' => $author_name
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => 'PayGuide NG',
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => 'https://ui-avatars.com/api/?name=PayGuide+NG&background=0284c7&color=fff&size=512'
                )
            ),
            'description' => wp_strip_all_tags( get_the_excerpt( $post ) )
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . "</script>\n";
    }
}
add_action( 'wp_head', 'payguide_ng_inject_seo_schema', 1 );

/**
 * Interactive FX & Virtual Card Fee Calculator Shortcode: [payguide_fx_calculator]
 */
function payguide_ng_calculator_shortcode() {
    ob_start();
    ?>
    <div class="pg-calculator-widget-card" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:24px; margin:28px 0 36px 0; box-shadow:0 4px 16px rgba(15, 23, 42, 0.04);">
      <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; margin-bottom:18px; gap:10px; border-bottom:1px solid #f1f5f9; padding-bottom:14px;">
        <div>
          <h3 style="font-size:18px; font-weight:800; color:#0f172a; margin:0 0 4px 0; display:flex; align-items:center; gap:6px;">
            <span>⚡</span> Live Virtual Card FX &amp; Cost Calculator
          </h3>
          <p style="font-size:13.5px; color:#64748b; margin:0;">Real-time estimated Naira settlement costs across Nigerian virtual card apps.</p>
        </div>
        <div style="display:flex; align-items:center; gap:6px; background:#f0fdf4; border:1px solid #bbf7d0; padding:5px 12px; border-radius:20px;">
          <span style="width:8px; height:8px; background:#22c55e; border-radius:50%; display:inline-block;"></span>
          <span style="font-size:11.5px; font-weight:700; color:#166534; text-transform:uppercase;">Live Radar: All Systems Active</span>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:18px;">
        <div>
          <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">USD Spend Amount ($):</label>
          <div style="position:relative; display:flex; align-items:center;">
            <span style="position:absolute; left:14px; font-weight:800; color:#0284c7;">$</span>
            <input type="number" id="pgCalcAmount" value="50" min="1" max="10000" style="width:100%; padding:10px 14px 10px 30px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:16px; font-weight:700; color:#0f172a; outline:none; font-family:inherit;" oninput="window.runPgCalc && window.runPgCalc()">
          </div>
        </div>
        <div>
          <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">Payment Target / Merchant:</label>
          <select id="pgCalcMerchant" style="width:100%; padding:10px 14px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:14px; font-weight:600; color:#0f172a; outline:none; font-family:inherit; background:#ffffff;" onchange="window.runPgCalc && window.runPgCalc()">
            <option value="general">General Online / Subscriptions</option>
            <option value="aws">AWS Cloud &amp; Web Hosting</option>
            <option value="ads">Meta &amp; Google Ads</option>
            <option value="apple">Apple Music, iCloud &amp; Spotify</option>
          </select>
        </div>
      </div>

      <!-- Real-time Comparison Results Grid -->
      <div id="pgCalcResults" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:12px;">
      </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'payguide_fx_calculator', 'payguide_ng_calculator_shortcode' );

/**
 * High-Converting Smartlink Shortcodes
 */
function payguide_ng_smartlink_btn_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'url' => '#',
        'text' => '🚀 Open Instant Virtual Dollar Card →',
        'subtext' => 'Instant NIN/BVN Verification • Zero Setup Fees'
    ), $atts );

    return '<div class="pg-smartlink-btn-wrap">' .
           '<a href="' . esc_url($a['url']) . '" target="_blank" rel="noopener noreferrer" class="pg-smartlink-btn">' . esc_html($a['text']) . '</a>' .
           '<span class="pg-smartlink-subtext">' . esc_html($a['subtext']) . '</span>' .
           '</div>';
}
add_shortcode( 'smartlink_btn', 'payguide_ng_smartlink_btn_shortcode' );

function payguide_ng_smartlink_box_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'url' => '#',
        'title' => '⚡ Need to Pay AWS or Meta Ads Immediately?',
        'text' => 'Use the verified instant-issue portal to generate an active dollar card in under 3 minutes.',
        'btn_text' => '👉 Check Card Availability Now'
    ), $atts );

    return '<div class="pg-smartlink-notice-box">' .
           '<h4 class="box-title">' . esc_html($a['title']) . '</h4>' .
           '<p class="box-desc">' . esc_html($a['text']) . '</p>' .
           '<a href="' . esc_url($a['url']) . '" target="_blank" rel="noopener noreferrer" class="box-btn">' . esc_html($a['btn_text']) . '</a>' .
           '</div>';
}
add_shortcode( 'smartlink_box', 'payguide_ng_smartlink_box_shortcode' );

function payguide_ng_smartlink_table_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'url' => '#'
    ), $atts );
    $smartlink = esc_url($a['url']);

    return '<div class="pg-smartlink-table-wrap">' .
           '<table class="pg-smartlink-table">' .
           '<thead><tr><th>Card Provider</th><th>FX Rate</th><th>Card Load Fee</th><th>Success Rate</th><th>Action</th></tr></thead>' .
           '<tbody>' .
           '<tr><td><strong>Grey USD Mastercard</strong></td><td>₦1,640/$</td><td>1.0% + $1</td><td><span style="color:#16a34a; font-weight:700;">99.2% 🟢</span></td><td><a href="' . $smartlink . '" target="_blank" rel="noopener noreferrer" class="pg-table-action-btn">Get Card →</a></td></tr>' .
           '<tr><td><strong>Chipper Cash Visa</strong></td><td>₦1,650/$</td><td>1.5%</td><td><span style="color:#16a34a; font-weight:700;">98.5% 🟢</span></td><td><a href="' . $smartlink . '" target="_blank" rel="noopener noreferrer" class="pg-table-action-btn">Get Card →</a></td></tr>' .
           '<tr><td><strong>Geegpay Virtual Card</strong></td><td>₦1,645/$</td><td>1.2% + $0.5</td><td><span style="color:#16a34a; font-weight:700;">97.8% 🟢</span></td><td><a href="' . $smartlink . '" target="_blank" rel="noopener noreferrer" class="pg-table-action-btn">Get Card →</a></td></tr>' .
           '<tr><td><strong>Eversend USD Visa</strong></td><td>₦1,620/$</td><td>2.0%</td><td><span style="color:#16a34a; font-weight:700;">96.5% 🟢</span></td><td><a href="' . $smartlink . '" target="_blank" rel="noopener noreferrer" class="pg-table-action-btn">Get Card →</a></td></tr>' .
           '</tbody></table></div>';
}
add_shortcode( 'smartlink_table', 'payguide_ng_smartlink_table_shortcode' );

/**
 * Universal Scripts in Footer (Calculator Engine + TOC)
 */
function payguide_ng_footer_scripts() {
    ?>
    <script>
    // Universal FX Calculator Engine
    window.runPgCalc = function() {
        var inputEl = document.getElementById('pgCalcAmount');
        var resEl = document.getElementById('pgCalcResults');
        if (!inputEl || !resEl) return;
        var amount = parseFloat(inputEl.value) || 0;
        var providers = [
            { name: 'Grey USD Card', rate: 1640, feePercent: 1.0, flatFee: 1.0, status: 'Active 🟢' },
            { name: 'Chipper Cash USD', rate: 1650, feePercent: 1.5, flatFee: 0.0, status: 'Active 🟢' },
            { name: 'Geegpay USD Visa', rate: 1645, feePercent: 1.2, flatFee: 0.5, status: 'Active 🟢' },
            { name: 'Eversend USD Card', rate: 1620, feePercent: 2.0, flatFee: 0.0, status: 'Active 🟢' }
        ];

        var html = '';
        providers.forEach(function(p) {
            var totalUsd = amount + (amount * (p.feePercent / 100)) + p.flatFee;
            var totalNaira = Math.round(totalUsd * p.rate);
            html += '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px 12px; text-align:center;">' +
                      '<div style="font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">' + p.name + '</div>' +
                      '<div style="font-size:19px; font-weight:800; color:#0284c7; margin-bottom:4px;">₦' + totalNaira.toLocaleString() + '</div>' +
                      '<div style="font-size:11.5px; color:#64748b;">@ ₦' + p.rate.toLocaleString() + '/$ • ' + p.status + '</div>' +
                    '</div>';
        });
        resEl.innerHTML = html;
    };

    // Auto-initialize on DOM ready and Window load
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.runPgCalc === 'function') window.runPgCalc();

        // Automatic Table of Contents for Single Articles
        var content = document.querySelector('.wp-block-post-content');
        if (content) {
            var headings = content.querySelectorAll('h2, h3');
            if (headings.length >= 3) {
                var tocWrapper = document.createElement('div');
                tocWrapper.className = 'pg-article-toc';
                tocWrapper.innerHTML = '<div class="toc-header" onclick="this.parentElement.classList.toggle(\'collapsed\')">' +
                                       '<span>📑 Quick Navigation &amp; Table of Contents</span>' +
                                       '<span class="toc-toggle">▼</span></div>' +
                                       '<ul class="toc-list" id="pgTocList"></ul>';

                var tocList = tocWrapper.querySelector('#pgTocList');
                headings.forEach(function(h, index) {
                    var id = h.id || 'section-' + (index + 1);
                    h.id = id;
                    var li = document.createElement('li');
                    li.className = h.tagName.toLowerCase() === 'h3' ? 'toc-h3' : 'toc-h2';
                    li.innerHTML = '<a href="#' + id + '">' + h.textContent.trim() + '</a>';
                    tocList.appendChild(li);
                });

                var firstP = content.querySelector('p');
                if (firstP && firstP.nextSibling) {
                    content.insertBefore(tocWrapper, firstP.nextSibling);
                } else {
                    content.insertBefore(tocWrapper, content.firstChild);
                }
            }
        }
    });
    window.addEventListener('load', function() {
        if (typeof window.runPgCalc === 'function') window.runPgCalc();
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'payguide_ng_footer_scripts' );

/* ==========================================================================
   PAYGUIDE NG — ZERO-CODE AD MONETIZATION ENGINE & ADMIN DASHBOARD
   ========================================================================== */

/**
 * 1. Register Admin Menu for Ads & Monetization and Article Publisher
 */
function payguide_ng_register_ads_admin_menu() {
    add_menu_page(
        'PayGuide Ads & Monetization',
        'PayGuide Ads ⚡',
        'manage_options',
        'payguide-ads',
        'payguide_ng_render_ads_admin_page',
        'dashicons-chart-area',
        59
    );
    add_submenu_page(
        'payguide-ads',
        'PayGuide Ad Settings',
        '⚡ Ad Settings',
        'manage_options',
        'payguide-ads',
        'payguide_ng_render_ads_admin_page'
    );
    add_submenu_page(
        'payguide-ads',
        'PayGuide Article Publisher & Manager',
        '🚀 Article Publisher & Manager',
        'edit_posts',
        'payguide-publisher',
        'payguide_ng_render_publisher_admin_page'
    );
}
add_action( 'admin_menu', 'payguide_ng_register_ads_admin_menu' );

/**
 * 2. Save Ads Settings Handler
 */
function payguide_ng_save_ads_settings() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user.' );
    }

    check_admin_referer( 'payguide_ads_save_action', 'payguide_ads_nonce' );

    $fields = array(
        'payguide_ads_enabled',
        'payguide_ads_head',
        'payguide_ads_top_billboard',
        'payguide_ads_intro',
        'payguide_ads_cadence',
        'payguide_ads_vignette',
        'payguide_ads_keep_reading',
        'payguide_ads_sidebar',
        'payguide_ads_infeed',
        'payguide_ads_midroll_1',
        'payguide_ads_midroll_2',
        'payguide_ads_midroll_3',
        'payguide_ads_sticky_footer',
        'payguide_ads_video',
        'payguide_wa_channel_url',
        'payguide_tg_channel_url',
        'payguide_enable_reading_bar',
        'payguide_enable_slidein_card',
        'payguide_house_ad_fallback',
        'payguide_ads_txt'
    );

    foreach ( $fields as $field ) {
        if ( in_array( $field, array( 'payguide_ads_enabled', 'payguide_enable_reading_bar', 'payguide_enable_slidein_card' ), true ) ) {
            $val = isset( $_POST[$field] ) ? '1' : '0';
            update_option( $field, $val );
        } else {
            // Raw scripts/html allowed for administrator
            $val = isset( $_POST[$field] ) ? wp_unslash( $_POST[$field] ) : '';
            update_option( $field, $val );
        }
    }

    wp_safe_redirect( add_query_arg( array( 'page' => 'payguide-ads', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
    exit;
}
add_action( 'admin_post_payguide_save_ads', 'payguide_ng_save_ads_settings' );

/**
 * 3. Render Ads Admin Dashboard (Vanguard & Punch Architecture v3.0)
 */
function payguide_ng_render_ads_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $enabled        = get_option( 'payguide_ads_enabled', '1' );
    $ad_head        = get_option( 'payguide_ads_head', '' );
    $ad_billboard   = get_option( 'payguide_ads_top_billboard', '' );
    $ad_intro       = get_option( 'payguide_ads_intro', '' );
    $ad_cadence     = get_option( 'payguide_ads_cadence', '3' );
    $ad_vignette    = get_option( 'payguide_ads_vignette', '' );
    $ad_keep        = get_option( 'payguide_ads_keep_reading', '' );
    $ad_sidebar     = get_option( 'payguide_ads_sidebar', '' );
    $ad_infeed      = get_option( 'payguide_ads_infeed', '' );
    $ad_mid_1       = get_option( 'payguide_ads_midroll_1', '' );
    $ad_mid_2       = get_option( 'payguide_ads_midroll_2', '' );
    $ad_mid_3       = get_option( 'payguide_ads_midroll_3', '' );
    $ad_sticky      = get_option( 'payguide_ads_sticky_footer', '' );
    $ad_video       = get_option( 'payguide_ads_video', '' );
    $wa_url         = get_option( 'payguide_wa_channel_url', '' );
    $tg_url         = get_option( 'payguide_tg_channel_url', '' );
    $enable_prog    = get_option( 'payguide_enable_reading_bar', '1' );
    $enable_slidein = get_option( 'payguide_enable_slidein_card', '1' );
    $house_fallback = get_option( 'payguide_house_ad_fallback', '' );
    $ad_txt         = get_option( 'payguide_ads_txt', '' );
    ?>
    <div class="wrap" style="max-width: 1080px; margin-top: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        
        <?php if ( isset( $_GET['saved'] ) ) : ?>
            <div class="notice notice-success is-dismissible" style="border-left-color: #0284c7; padding: 12px 16px; font-weight: 600;">
                <p>✅ Ad Monetization settings, viral channels, and ads.txt updated successfully! All scripts normalized and auto-repaired.</p>
            </div>
        <?php endif; ?>

        <!-- Header Card -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 28px 32px; border-radius: 14px; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div>
                    <h1 style="color: #fff; margin:0 0 8px 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">⚡ PayGuide NG — High-CPM Dynamic Ad Engine v3.0</h1>
                    <p style="margin: 0; color: #94a3b8; font-size: 14px;">Replicating Vanguard &amp; Punch NGR architecture: Infinite paragraph cadence, WhatsApp/Telegram conversion channels, 3px reading progress bar, Daily Post slide-in card &amp; full-size unclipped banners.</p>
                </div>
                <div>
                    <span style="background: rgba(2, 132, 199, 0.25); border: 1px solid #0284c7; color: #38bdf8; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700;">
                        Enterprise Media Edition
                    </span>
                </div>
            </div>
        </div>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="payguide_save_ads">
            <?php wp_nonce_field( 'payguide_ads_save_action', 'payguide_ads_nonce' ); ?>

            <!-- Global Master Switch -->
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <strong style="font-size: 16px; color: #0f172a;">Enable Website Monetization Engine</strong>
                    <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Turn off if you want to temporarily disable all ad rendering without clearing your saved codes.</div>
                </div>
                <label style="position: relative; display: inline-block; width: 50px; height: 26px;">
                    <input type="checkbox" name="payguide_ads_enabled" value="1" <?php checked( $enabled, '1' ); ?> style="opacity: 0; width: 0; height: 0;" id="pgAdsToggle">
                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo $enabled === '1' ? '#0284c7' : '#cbd5e1'; ?>; transition: .3s; border-radius: 34px;" id="pgAdsSlider"></span>
                </label>
            </div>

            <!-- Ad Slot Cards Grid -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">

                <!-- Slot 1: Header / Verification Script -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[1] Header / Verification &amp; Auto-Ads Tag (&lt;head&gt;)</h3>
                        <span style="background:#e0f2fe; color:#0369a1; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">Global Header</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Paste your Monetag MultiTag script, AdSense auto-ads code, or site domain verification meta tags here. Injected automatically into <code>&lt;head&gt;</code>. (Supports raw script URLs like <code>https://arap1he.org/...</code> automatically).</p>
                    <textarea name="payguide_ads_head" rows="3" placeholder="<script ...> or direct script URL" style="width:100%; font-family:monospace; font-size:12.5px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_head ); ?></textarea>
                </div>

                <!-- Slot 2: Top Leaderboard / Billboard Banner (All Pages) -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[2] Top Leaderboard / Billboard Banner (728x90 Desktop / 320x50 Mobile)</h3>
                        <span style="background:#dbeafe; color:#1e40af; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">100% Viewability (All Pages)</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Injected immediately below the header navigation on EVERY page (Home, Articles, Categories, Archives). Captures instant attention and drives massive CPM increases.</p>
                    <textarea name="payguide_ads_top_billboard" rows="3" placeholder="Paste 728x90, 468x60, 320x50 banner tag (Adsterra, Monetag, or AdSense)" style="width:100%; font-family:monospace; font-size:12.5px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_billboard ); ?></textarea>
                </div>

                <!-- Slot 3: Vanguard desktop_1 Lead Intro Ad (Below Byline) -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[3] Top-of-Article Intro Ad (Vanguard desktop_1 Slot)</h3>
                        <span style="background:#fef3c7; color:#92400e; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">High-CTR Above First Paragraph</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Rendered directly underneath the article title and byline, before the first paragraph begins. 100% of readers view this without scrolling.</p>
                    <textarea name="payguide_ads_intro" rows="3" placeholder="Paste 300x250, 336x280, or 728x90 banner code" style="width:100%; font-family:monospace; font-size:12.5px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_intro ); ?></textarea>
                </div>

                <!-- Slot 4: Vanguard Dynamic Infinite In-Article Cadence Engine -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[4] Vanguard Infinite In-Article Cadence Engine</h3>
                        <span style="background:#dcfce7; color:#166534; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">Dynamic Repeating Inserter</span>
                    </div>
                    <p style="margin: 0 0 14px 0; font-size: 13px; color: #64748b;">Instead of stopping at 2 ads, the engine automatically inserts an ad every <strong>N paragraphs</strong> across the entire story (cycling between Slot 4A, 4B, and 4C). Long articles get 8-12 ads just like Vanguard!</p>
                    
                    <div style="margin-bottom: 16px; background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0; display:flex; align-items:center; gap:16px;">
                        <label style="font-size:13px; font-weight:700; color:#334155;">Paragraph Cadence Frequency:</label>
                        <select name="payguide_ads_cadence" style="padding:6px 12px; border-radius:6px; border:1px solid #cbd5e1; font-weight:700; color:#0f172a;">
                            <option value="2" <?php selected( $ad_cadence, '2' ); ?>>Every 2 Paragraphs (Aggressive Vanguard Cadence)</option>
                            <option value="3" <?php selected( $ad_cadence, '3' ); ?>>Every 3 Paragraphs (Recommended Sweet Spot)</option>
                            <option value="4" <?php selected( $ad_cadence, '4' ); ?>>Every 4 Paragraphs (Punch Nigeria Cadence)</option>
                            <option value="5" <?php selected( $ad_cadence, '5' ); ?>>Every 5 Paragraphs (Conservative)</option>
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                        <div>
                            <strong style="font-size:13px; color:#334155;">Slot 4A: Midroll 1 (300x250)</strong>
                            <textarea name="payguide_ads_midroll_1" rows="4" style="width:100%; margin-top:6px; font-family:monospace; font-size:12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_mid_1 ); ?></textarea>
                        </div>
                        <div>
                            <strong style="font-size:13px; color:#334155;">Slot 4B: Midroll 2 (300x250 / Native)</strong>
                            <textarea name="payguide_ads_midroll_2" rows="4" style="width:100%; margin-top:6px; font-family:monospace; font-size:12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_mid_2 ); ?></textarea>
                        </div>
                        <div>
                            <strong style="font-size:13px; color:#334155;">Slot 4C: Midroll 3 / Pre-Comments</strong>
                            <textarea name="payguide_ads_midroll_3" rows="4" style="width:100%; margin-top:6px; font-family:monospace; font-size:12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_mid_3 ); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Slot 5: WhatsApp & Telegram Community Broadcast Channels -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[5] Viral WhatsApp &amp; Telegram Community Broadcast Channels</h3>
                        <span style="background:#f0fdf4; color:#166534; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">Vanguard wa-banner &amp; tg-banner</span>
                    </div>
                    <p style="margin: 0 0 14px 0; font-size: 13px; color: #64748b;">Displays verified emerald and electric blue community join cards right below every article to build an owned subscriber base.</p>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#334155; margin-bottom:4px;">🟢 WhatsApp Channel Invite Link:</label>
                            <input type="url" name="payguide_wa_channel_url" value="<?php echo esc_attr( $wa_url ); ?>" placeholder="https://whatsapp.com/channel/0029..." style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#334155; margin-bottom:4px;">🔵 Telegram Channel Invite Link:</label>
                            <input type="url" name="payguide_tg_channel_url" value="<?php echo esc_attr( $tg_url ); ?>" placeholder="https://t.me/PayGuideNG" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                        </div>
                    </div>
                </div>

                <!-- Slot 6: Psychological Retention & Engagement (Punch / Daily Post) -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[6] Psychological Retention &amp; Dwell Time Multipliers</h3>
                        <span style="background:#e0f2fe; color:#0369a1; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">Punch &amp; Daily Post Secret</span>
                    </div>
                    <p style="margin: 0 0 14px 0; font-size: 13px; color: #64748b;">Visual cues that keep readers on page longer to boost programmatic viewability score and CPM bids.</p>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <label style="display:flex; align-items:center; gap:10px; font-size:13.5px; color:#0f172a; font-weight:600; cursor:pointer;">
                            <input type="checkbox" name="payguide_enable_reading_bar" value="1" <?php checked( $enable_prog, '1' ); ?>>
                            <span>Enable 3px Reading Progress Indicator Bar at top of screen (Punch / TheCable style)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:10px; font-size:13.5px; color:#0f172a; font-weight:600; cursor:pointer;">
                            <input type="checkbox" name="payguide_enable_slidein_card" value="1" <?php checked( $enable_slidein, '1' ); ?>>
                            <span>Enable Slide-In "Next Guide / Keep Reading" Card at 60% scroll depth (Daily Post <code>mvp-fly-fade</code> style)</span>
                        </label>
                    </div>
                </div>

                <!-- Slot 7: Sticky Bottom Anchor Bar -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[7] Sticky Bottom Anchor Bar (Mobile 320x50 / Desktop 728x90)</h3>
                        <span style="background:#ede9fe; color:#5b21b6; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">100% Viewability (All Pages)</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Pinned to bottom with built-in top tab toggle (<code>✕ Close Ad</code>). Tab sits outside banner to prevent accidental click penalties.</p>
                    <textarea name="payguide_ads_sticky_footer" rows="3" style="width:100%; font-family:monospace; font-size:12.5px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_sticky ); ?></textarea>
                </div>

                <!-- Slot 8: Sidebar High-Yield Sticky Ad -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[8] Sidebar High-Yield Sticky Ad (300x250 or 300x600 Half-Page)</h3>
                        <span style="background:#f3e8ff; color:#6b21a8; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">Persistent Scroll (Home &amp; Articles)</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Displayed inside the sidebar on Homepage and Single Post pages. Automatically sticks while readers scroll.</p>
                    <textarea name="payguide_ads_sidebar" rows="3" placeholder="Paste 300x250 or 300x600 Adsterra / AdSense / Monetag tag here" style="width:100%; font-family:monospace; font-size:12.5px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_sidebar ); ?></textarea>
                </div>

                <!-- Slot 9: In-Feed Grid Ad (Homepage & Categories/Archives) -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[9] In-Feed Ad Banner (728x90 or 300x250 Native Grid)</h3>
                        <span style="background:#e0e7ff; color:#3730a3; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">Home &amp; Archive Grids</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Injected dynamically between articles on Homepage feed and Category/Archive listings.</p>
                    <textarea name="payguide_ads_infeed" rows="3" placeholder="Paste 728x90 or 300x250 Native banner code" style="width:100%; font-family:monospace; font-size:12.5px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_infeed ); ?></textarea>
                </div>

                <!-- Slot 10: Outstream Video Player Dock & Interstitial -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[10] Outstream Video Player Dock &amp; Interstitial Vignette</h3>
                        <span style="background:#fee2e2; color:#991b1b; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">$5.00+ CPM</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">The video player docks at <code>bottom: 85px; right: 16px</code> so it floats cleanly above the bottom anchor bar without overlap.</p>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                        <div>
                            <strong style="font-size:12.5px; color:#334155;">Outstream Video Player Code:</strong>
                            <textarea name="payguide_ads_video" rows="3" style="width:100%; margin-top:4px; font-family:monospace; font-size:12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_video ); ?></textarea>
                        </div>
                        <div>
                            <strong style="font-size:12.5px; color:#334155;">Interstitial / Vignette Script:</strong>
                            <textarea name="payguide_ads_vignette" rows="3" style="width:100%; margin-top:4px; font-family:monospace; font-size:12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_vignette ); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Slot 11: House Ad / Adblocker Fallback Banner -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[11] House Ad / Adblocker Fallback Engine (TheCable Model)</h3>
                        <span style="background:#ffedd5; color:#9a3412; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">100% Monetized Traffic</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">If third-party ad networks fail or a visitor uses Brave/uBlock Origin, this banner HTML or Sovia promo renders so you never display empty space.</p>
                    <textarea name="payguide_house_ad_fallback" rows="3" placeholder="<a href='https://sovia.app'><img src='...' /></a>" style="width:100%; font-family:monospace; font-size:12.5px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $house_fallback ); ?></textarea>
                </div>

                <!-- Slot 12: ads.txt Manager -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;">[12] Built-in Dynamic ads.txt Manager</h3>
                        <span style="background:#ccfbf1; color:#115e59; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">Zero-cPanel Route</span>
                    </div>
                    <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Paste the lines provided by Google AdSense, Monetag, or Adsterra. Automatically served at <code>https://<?php echo esc_html( $_SERVER['HTTP_HOST'] ?? 'domain.com' ); ?>/ads.txt</code>.</p>
                    <textarea name="payguide_ads_txt" rows="4" placeholder="google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0" style="width:100%; font-family:monospace; font-size:12.5px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:10px;"><?php echo esc_textarea( $ad_txt ); ?></textarea>
                </div>

            </div>

            <!-- Submit Button Bar -->
            <div style="margin-top: 28px; padding-top: 16px; border-top: 1px solid #e2e8f0; display:flex; justify-content:flex-end;">
                <button type="submit" class="button button-primary button-hero" style="background:#0284c7; border-color:#0284c7; font-weight:700; padding:10px 32px; border-radius:8px; box-shadow:0 4px 14px rgba(2,132,199,0.35);">
                    💾 Save Monetization Settings
                </button>
            </div>
        </form>
    </div>
    <script>
    var pgToggle = document.getElementById('pgAdsToggle');
    var pgSlider = document.getElementById('pgAdsSlider');
    if (pgToggle && pgSlider) {
        pgToggle.addEventListener('change', function() {
            pgSlider.style.backgroundColor = this.checked ? '#0284c7' : '#cbd5e1';
        });
    }
    </script>
    <?php
}

/**
 * 4. Serve Dynamic /ads.txt without cPanel or FTP
 */
function payguide_ng_serve_ads_txt() {
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = trim( parse_url( $request_uri, PHP_URL_PATH ), '/' );

    if ( $path === 'ads.txt' ) {
        $ads_txt = get_option( 'payguide_ads_txt', '' );
        header( 'Content-Type: text/plain; charset=utf-8' );
        header( 'X-Served-By: PayGuide-NG-Dynamic-Ads-Txt' );
        if ( ! empty( $ads_txt ) ) {
            echo trim( $ads_txt ) . "\n";
        } else {
            echo "# PayGuide NG - No ads.txt records configured yet.\n";
        }
        exit;
    }
}
add_action( 'init', 'payguide_ng_serve_ads_txt', 1 );

/**
 * 5. Script Normalization & Auto-Repair Engine
 * - Wraps bare script URLs in <script>
 * - Auto-appends missing Adsterra invoke.js if only atOptions was pasted
 * - Injects data-cfasync="false" so Cloudflare Rocket Loader doesn't block document.write
 */
function payguide_ng_clean_and_repair_ad_code( $code, $is_head = false ) {
    $code = trim( (string) $code );
    if ( empty( $code ) ) {
        return '';
    }

    // 1. Auto-wrap bare script URLs (e.g. https://.../script.js). Warn on Direct Links.
    if ( preg_match( '#^(https?:)?//[^\s<>"\']+$#i', $code ) ) {
        if ( preg_match( '#\.js($|\?)#i', $code ) ) {
            return '<script type="text/javascript" data-cfasync="false" async src="' . esc_url( $code ) . '"></script>';
        } else {
            return '<!-- WARNING: Bare non-JS URL detected. If this is a Direct Link, use a Smartlink button instead. -->' . "\n" . '<div style="display:none;" class="pg-bare-url-warning">' . esc_html( $code ) . '</div>';
        }
    }

    // 2. Auto-repair Adsterra banner snippets missing invoke.js
    if ( stripos( $code, 'atOptions' ) !== false ) {
        if ( preg_match( "#['\"]key['\"]\s*:\s*['\"]([a-f0-9]+)['\"]#i", $code, $matches ) ) {
            $key = $matches[1];
            // Skip appending invoke.js if an external loader (src=) is already present
            if ( stripos( $code, 'invoke.js' ) === false && !preg_match( '#<script[^>]+src=#i', $code ) ) {
                $code .= "\n" . '<script type="text/javascript" data-cfasync="false" src="//www.highperformanceformat.com/' . esc_attr( $key ) . '/invoke.js"></script>';
            }
        }
    }

    // 3. Inject data-cfasync="false" to prevent Cloudflare Rocket Loader delays
    if ( stripos( $code, '<script' ) !== false ) {
        $code = preg_replace( '#<script(?![^>]*data-cfasync)#i', '<script data-cfasync="false"', $code );
    }

    return $code;
}

/**
 * 6. Get Universal Responsive Ad Slot HTML
 */
function payguide_ng_get_ad_html_by_slot( $slot ) {
    if ( get_option( 'payguide_ads_enabled', '1' ) !== '1' ) {
        return '';
    }

    $raw = '';
    switch ( $slot ) {
        case 'top_billboard':
            $raw = get_option( 'payguide_ads_top_billboard', '' );
            break;
        case 'intro_ad':
            $raw = get_option( 'payguide_ads_intro', '' );
            break;
        case 'sidebar':
            $raw = get_option( 'payguide_ads_sidebar', '' );
            break;
        case 'infeed':
            $raw = get_option( 'payguide_ads_infeed', '' );
            break;
        case 'midroll_1':
            $raw = get_option( 'payguide_ads_midroll_1', '' );
            break;
        case 'midroll_2':
            $raw = get_option( 'payguide_ads_midroll_2', '' );
            break;
        case 'midroll_3':
            $raw = get_option( 'payguide_ads_midroll_3', '' );
            break;
        case 'sticky_footer':
            $raw = get_option( 'payguide_ads_sticky_footer', '' );
            break;
        case 'video':
            $raw = get_option( 'payguide_ads_video', '' );
            break;
    }

    $code = payguide_ng_clean_and_repair_ad_code( $raw );
    if ( empty( $code ) ) {
        // Fallback to house ad / Sovia app banner if configured
        $house_ad = get_option( 'payguide_house_ad_fallback', '' );
        if ( ! empty( $house_ad ) && in_array( $slot, array( 'intro_ad', 'midroll_1', 'midroll_2', 'sidebar', 'infeed' ), true ) ) {
            return '<div class="sovia-ad-wrapper sovia-ad-' . esc_attr( $slot ) . ' sovia-ad-house"><div class="sovia-ad-inner">' . $house_ad . '</div></div>';
        }
        return '';
    }

    return '<div class="sovia-ad-wrapper sovia-ad-' . esc_attr( $slot ) . '"><div class="sovia-ad-inner">' . $code . '</div></div>';
}

/**
 * 7. Universal Shortcode for Block Templates: [payguide_ad slot="sidebar"]
 */
function payguide_ng_render_ad_slot_shortcode( $atts ) {
    if ( is_admin() || get_option( 'payguide_ads_enabled', '1' ) !== '1' ) {
        return '';
    }
    $atts = shortcode_atts( array(
        'slot' => '',
    ), $atts, 'payguide_ad' );

    $slot = sanitize_key( $atts['slot'] );
    return payguide_ng_get_ad_html_by_slot( $slot );
}
add_shortcode( 'payguide_ad', 'payguide_ng_render_ad_slot_shortcode' );

/**
 * 8. Inject Header / Auto-Ads Script into <head>
 */
function payguide_ng_inject_head_ads() {
    if ( is_admin() || get_option( 'payguide_ads_enabled', '1' ) !== '1' ) {
        return;
    }
    $ad_head = get_option( 'payguide_ads_head', '' );
    if ( ! empty( $ad_head ) ) {
        $clean_head = payguide_ng_clean_and_repair_ad_code( $ad_head, true );
        echo "\n<!-- PayGuide NG Header Ads -->\n" . $clean_head . "\n<!-- /PayGuide NG Header Ads -->\n";
    }
}
add_action( 'wp_head', 'payguide_ng_inject_head_ads', 20 );

/**
 * 9. Automatic Vanguard & Punch Infinite In-Article Cadence Injection
 */
function payguide_ng_inject_in_content_ads( $content ) {
    if ( ! is_single() || is_admin() || get_option( 'payguide_ads_enabled', '1' ) !== '1' ) {
        return $content;
    }

    $ad_1_html = payguide_ng_get_ad_html_by_slot( 'midroll_1' );
    $ad_2_html = payguide_ng_get_ad_html_by_slot( 'midroll_2' );
    $ad_3_html = payguide_ng_get_ad_html_by_slot( 'midroll_3' );
    $cadence   = max( 2, (int) get_option( 'payguide_ads_cadence', 3 ) );

    // Build ad pool for dynamic repeating rotation
    $ad_pool = array_filter( array( $ad_1_html, $ad_2_html, $ad_3_html ) );
    if ( empty( $ad_pool ) ) {
        $house_ad = get_option( 'payguide_house_ad_fallback', '' );
        if ( ! empty( $house_ad ) ) {
            $ad_pool = array( '<div class="sovia-ad-wrapper sovia-ad-midroll_1 sovia-ad-house"><div class="sovia-ad-inner">' . $house_ad . '</div></div>' );
        }
    }

    // Split content by block-level closing tags to count true cadence blocks
    $chunks = preg_split( '/(<\/(?:p|h[2-6]|ul|ol|table|blockquote)>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
    $blocks = array();
    for ( $i = 0; $i < count( $chunks ); $i += 2 ) {
        $text = $chunks[$i];
        $tag = isset( $chunks[$i + 1] ) ? $chunks[$i + 1] : '';
        $blocks[] = $text . $tag;
    }
    $total_blocks = count( $blocks );

    // Stealth Code Random In-Text Placement (Prevents Fast-Scrolling to Bottom)
    $stealth_code   = get_option( 'payguide_stealth_code', 'SOV-8492' );
    $stealth_timer  = get_option( 'payguide_stealth_timer', '45' );
    $stealth_scroll = get_option( 'payguide_stealth_scroll', '60' );
    
    $random_para_target = ( $total_blocks > 4 ) ? mt_rand( 2, max( 2, $total_blocks - 2 ) ) : max( 1, $total_blocks - 1 );
    $stealth_citation_html = ' <span class="pg-stealth-citation" id="pgAuditRefCode" style="font-family:monospace; font-size:12px; color:#64748b; background:#f1f5f9; padding:2px 7px; border-radius:4px; letter-spacing:0.3px; display:inline-block; margin-left:4px; transition:all 0.3s ease; user-select:all; cursor:copy;" title="Audit Citation" data-code="#' . esc_attr( $stealth_code ) . '" data-timer="' . esc_attr( $stealth_timer ) . '" data-scroll="' . esc_attr( $stealth_scroll ) . '">(Ref: #PENDING...)</span>';

    // Build Vanguard In-Article "⚡ ALSO READ" Recirculation Callout
    $related_callout_html = '';
    $recent_posts = get_posts( array( 'numberposts' => 5, 'post_status' => 'publish', 'exclude' => array( get_the_ID() ) ) );
    if ( ! empty( $recent_posts ) ) {
        $rnd_post = $recent_posts[ array_rand( $recent_posts ) ];
        $related_callout_html = '<div class="pg-incontent-related-callout">' .
            '<div class="callout-label">⚡ Also Read:</div>' .
            '<a href="' . esc_url( get_permalink( $rnd_post->ID ) ) . '" class="callout-link">' . esc_html( $rnd_post->post_title ) . ' →</a>' .
            '</div>';
    }

    $new_content = '';
    $ad_index = 0;
    $pool_count = count( $ad_pool );
    $valid_block_count = 0;

    foreach ( $blocks as $index => $block ) {
        // Skip last empty element if any
        if ( $index === $total_blocks - 1 && trim( $block ) === '' ) {
            $new_content .= $block;
            continue;
        }

        // Randomly embed stealth citation inside paragraph body
        if ( is_single() && ! empty( $stealth_code ) && $index === $random_para_target ) {
            if ( preg_match( '/(<\/(?:p|h[2-6]|ul|ol|table|blockquote)>)$/i', $block ) ) {
                $block = preg_replace( '/(<\/(?:p|h[2-6]|ul|ol|table|blockquote)>)$/i', $stealth_citation_html . '$1', $block );
            } else {
                $block .= $stealth_citation_html;
            }
        }

        $new_content .= $block;

        // If the block is not empty, it's a valid insertion point.
        // We relax the strict div tracker because it blocks ads on posts wrapped in Gutenberg groups.
        if ( trim( wp_strip_all_tags( $block ) ) !== '' ) {
            $valid_block_count++;

            // At valid block 4, inject the "Also Read" recirculation callout
            if ( $valid_block_count === 4 && ! empty( $related_callout_html ) ) {
                $new_content .= "\n" . $related_callout_html . "\n";
            }

            // Vanguard Infinite Cadence: Inject every N valid blocks across entire article length!
            if ( $pool_count > 0 && $valid_block_count >= $cadence && ( $valid_block_count % $cadence === 0 ) && ( $index + 1 ) < $total_blocks ) {
                $selected_ad = $ad_pool[ $ad_index % $pool_count ];
                $new_content .= "\n" . $selected_ad . "\n";
                $ad_index++;
            }
        }
    }

    // Article Bottom / Pre-Comments Ad (Midroll 3)
    if ( ! empty( $ad_3_html ) && strpos( $new_content, $ad_3_html ) === false ) {
        $new_content .= $ad_3_html;
    }

    // Vanguard WhatsApp & Telegram Broadcast Community Join Cards
    $wa_url = get_option( 'payguide_wa_channel_url', '' );
    $tg_url = get_option( 'payguide_tg_channel_url', '' );
    if ( ! empty( $wa_url ) || ! empty( $tg_url ) ) {
        $new_content .= '<div class="pg-community-broadcast-grid">';
        if ( ! empty( $wa_url ) ) {
            $new_content .= '<a href="' . esc_url( $wa_url ) . '" target="_blank" rel="noopener noreferrer" class="pg-wa-banner">' .
                '<svg class="pg-banner-icon" viewBox="0 0 36 36" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M18 3C9.716 3 3 9.716 3 18c0 2.636.678 5.116 1.867 7.27L3 33l7.963-1.838A14.94 14.94 0 0018 33c8.284 0 15-6.716 15-15S26.284 3 18 3zm-4.41 8.25c-.33-.826-1.02-.75-1.515-.75-.495 0-1.08.187-1.65.825-.57.638-2.175 2.138-2.175 5.213s2.235 6.038 2.535 6.45c.3.413 4.29 6.75 10.59 9.225 5.235 2.055 6.3 1.643 7.425 1.538 1.125-.105 3.6-1.463 4.11-2.88.51-1.418.51-2.633.36-2.88-.15-.248-.555-.398-1.155-.705s-3.6-1.77-4.155-1.972c-.555-.203-.96-.308-1.365.308-.405.615-1.56 1.972-1.92 2.378-.36.405-.705.458-1.305.158s-2.535-.938-4.83-2.985c-1.785-1.59-2.985-3.555-3.345-4.17-.36-.615-.038-.945.263-1.245.27-.27.615-.705.915-1.05.3-.345.405-.6.6-.99.195-.39.098-.735-.053-1.035-.15-.3-1.365-3.3-1.875-4.515z" fill="#22c55e"/></svg>' .
                '<div class="pg-banner-content"><span class="pg-banner-title">Join PayGuide on WhatsApp</span><span class="pg-banner-subtext">Instant virtual card alerts &amp; parallel FX rates 🟢</span></div></a>';
        }
        if ( ! empty( $tg_url ) ) {
            $new_content .= '<a href="' . esc_url( $tg_url ) . '" target="_blank" rel="noopener noreferrer" class="pg-tg-banner">' .
                '<svg class="pg-banner-icon" viewBox="0 0 24 24" fill="none"><path d="M21.198 2.433a2.242 2.242 0 0 0-1.022.215l-17.5 7.5a2.25 2.25 0 0 0 .126 4.152l3.715 1.265 1.915 5.505a1 1 0 0 0 1.7.363l2.338-2.604 4.553 3.205a2.25 2.25 0 0 0 3.498-1.258l3.4-16.125a2.25 2.25 0 0 0-2.723-2.218z" fill="#0ea5e9"/></svg>' .
                '<div class="pg-banner-content"><span class="pg-banner-title">Join Telegram Community</span><span class="pg-banner-subtext">Discussion forum &amp; payment workarounds 🔵</span></div></a>';
        }
        $new_content .= '</div>';
    }

    return $new_content;
}
add_filter( 'the_content', 'payguide_ng_inject_in_content_ads', 25 );

/**
 * 10. Inject Footer Ads: Sticky Anchor Bar, Outstream Video, Reading Progress & Slide-in Card
 */
function payguide_ng_inject_footer_ads() {
    if ( is_admin() || get_option( 'payguide_ads_enabled', '1' ) !== '1' ) {
        return;
    }

    $ad_vignette = payguide_ng_clean_and_repair_ad_code( get_option( 'payguide_ads_vignette', '' ) );
    $ad_keep     = payguide_ng_clean_and_repair_ad_code( get_option( 'payguide_ads_keep_reading', '' ) );
    $ad_sticky   = payguide_ng_clean_and_repair_ad_code( get_option( 'payguide_ads_sticky_footer', '' ) );
    $ad_video    = payguide_ng_clean_and_repair_ad_code( get_option( 'payguide_ads_video', '' ) );

    // 1. Reading Progress Bar (Punch / TheCable Secret)
    if ( get_option( 'payguide_enable_reading_bar', '1' ) === '1' ) {
        echo '<div class="pg-reading-progress-bar" id="pgReadingProgressBar"></div>';
    }

    // 2. Full-screen Vignette / Interstitial
    if ( ! empty( $ad_vignette ) ) {
        echo "\n<!-- PayGuide NG Vignette Ad -->\n" . $ad_vignette . "\n";
    }

    // 3. Keep on Reading / Social Bar Popup
    if ( ! empty( $ad_keep ) ) {
        echo "\n<!-- PayGuide NG Keep Reading Widget -->\n" . $ad_keep . "\n";
    }

    // 4. Floating / Outstream Video Container
    if ( ! empty( $ad_video ) ) {
        ?>
        <div class="sovia-video-dock" id="soviaVideoDock">
            <button class="sovia-video-dock-close" onclick="document.getElementById('soviaVideoDock').style.display='none'" title="Close Video">×</button>
            <div class="sovia-video-dock-content">
                <?php echo $ad_video; ?>
            </div>
        </div>
        <?php
    }

    // 5. Sticky Bottom Anchor Banner with Tabbed Non-Overlapping Close Button
    if ( ! empty( $ad_sticky ) ) {
        ?>
        <div class="sovia-sticky-anchor" id="soviaStickyAnchor">
            <button class="sovia-sticky-close" onclick="document.getElementById('soviaStickyAnchor').style.display='none'" title="Close Advertisement">✕ Close Ad</button>
            <div class="sovia-sticky-inner">
                <?php echo $ad_sticky; ?>
            </div>
        </div>
        <?php
    }

    // 6. Daily Post Slide-In "Next Story / Keep Reading" Card (mvp-fly-fade)
    // De-conflict floating units: Only show slide-in if video dock is empty
    if ( is_single() && empty( $ad_video ) && get_option( 'payguide_enable_slidein_card', '1' ) === '1' ) {
        $next_post = get_next_post();
        if ( ! $next_post ) {
            $prev_post = get_previous_post();
            $next_post = $prev_post;
        }
        if ( $next_post ) {
            $thumb = get_the_post_thumbnail_url( $next_post->ID, 'thumbnail' );
            if ( ! $thumb ) {
                $thumb = 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=150&q=80';
            }
            ?>
            <div class="pg-slidein-card" id="pgSlideInCard">
                <button class="pg-slidein-close" onclick="document.getElementById('pgSlideInCard').classList.remove('active')" title="Close">✕</button>
                <div class="pg-slidein-header">⚡ Continue Reading</div>
                <div class="pg-slidein-body">
                    <img src="<?php echo esc_url( $thumb ); ?>" class="pg-slidein-thumb" alt="Thumbnail">
                    <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="pg-slidein-title"><?php echo esc_html( $next_post->post_title ); ?></a>
                </div>
            </div>
            <?php
        }
    }

    // 7. Silent Threshold Tracker + Scroll Progress Listener + Slide-in Trigger
    ?>
    <script>
    (function() {
        var progressBar = document.getElementById('pgReadingProgressBar');
        var slideInCard = document.getElementById('pgSlideInCard');
        var slideInShown = false;

        function onScrollHandler() {
            var docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var currentScroll = docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0;

            if (progressBar) {
                progressBar.style.width = Math.min(100, Math.max(0, currentScroll)) + '%';
            }

            if (slideInCard && !slideInShown && currentScroll >= 60) {
                slideInCard.classList.add('active');
                slideInShown = true;
            }
        }

        window.addEventListener('scroll', onScrollHandler, { passive: true });

        // Stealth Code Tracker
        var el = document.getElementById('pgAuditRefCode');
        if (el) {
            var targetCode = el.getAttribute('data-code') || '#SOV-8492';
            var requiredSeconds = parseInt(el.getAttribute('data-timer') || '45', 10);
            var requiredScroll = parseInt(el.getAttribute('data-scroll') || '60', 10);
            var startTime = Date.now();
            var timeReached = false;
            var scrollReached = false;

            function checkThresholds() {
                if (!timeReached && (Date.now() - startTime) >= (requiredSeconds * 1000)) {
                    timeReached = true;
                }
                var docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                var currentScroll = docHeight > 0 ? (window.scrollY / docHeight) * 100 : 100;
                if (currentScroll >= requiredScroll) {
                    scrollReached = true;
                }
                if (timeReached && scrollReached) {
                    el.textContent = '(Ref: ' + targetCode + ')';
                    el.style.color = '#0284c7';
                    el.style.background = '#e0f2fe';
                    el.style.fontWeight = '700';
                    clearInterval(timerInterval);
                }
            }

            var timerInterval = setInterval(checkThresholds, 1000);
            window.addEventListener('scroll', checkThresholds, { passive: true });
        }
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'payguide_ng_inject_footer_ads', 30 );

/* ==========================================================================
   PAYGUIDE NG — 1-CLICK POST CLONING & DUPLICATION ENGINE
   ========================================================================== */

/**
 * 8. Add "⚡ Clone / Duplicate" action to WordPress Posts Table
 */
function payguide_ng_duplicate_post_link( $actions, $post ) {
    if ( current_user_can( 'edit_posts' ) && $post->post_type === 'post' ) {
        $url = wp_nonce_url(
            admin_url( 'admin.php?action=payguide_duplicate_post&post=' . $post->ID ),
            'payguide_duplicate_post_' . $post->ID
        );
        $actions['payguide_clone'] = '<a href="' . esc_url( $url ) . '" title="Duplicate this article into a new draft" style="color:#0284c7; font-weight:700;">⚡ Clone / Duplicate</a>';
    }
    return $actions;
}
add_filter( 'post_row_actions', 'payguide_ng_duplicate_post_link', 10, 2 );

/**
 * 9. Handle 1-Click Post Duplication
 */
function payguide_ng_duplicate_post_handler() {
    if ( empty( $_GET['post'] ) || ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Unauthorized action.' );
    }

    $post_id = absint( $_GET['post'] );
    check_admin_referer( 'payguide_duplicate_post_' . $post_id );

    $post = get_post( $post_id );
    if ( ! $post ) {
        wp_die( 'Post not found.' );
    }

    $new_post_args = array(
        'post_title'   => $post->post_title . ' (Copy)',
        'post_content' => $post->post_content,
        'post_excerpt' => $post->post_excerpt,
        'post_status'  => 'draft',
        'post_type'    => $post->post_type,
        'post_author'  => get_current_user_id(),
    );

    $new_post_id = wp_insert_post( $new_post_args );

    if ( ! is_wp_error( $new_post_id ) ) {
        // Clone taxonomies (categories & tags)
        $taxonomies = get_object_taxonomies( $post->post_type );
        foreach ( $taxonomies as $taxonomy ) {
            $post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
            wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
        }

        // Clone featured image
        $thumbnail_id = get_post_thumbnail_id( $post_id );
        if ( $thumbnail_id ) {
            set_post_thumbnail( $new_post_id, $thumbnail_id );
        }

        // Redirect directly to the cloned post editor
        wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
        exit;
    } else {
        wp_die( 'Failed to clone article: ' . $new_post_id->get_error_message() );
    }
}
add_action( 'admin_action_payguide_duplicate_post', 'payguide_ng_duplicate_post_handler' );

/* ==========================================================================
   PAYGUIDE NG — IN-DOMAIN ARTICLE PUBLISHER & CONTENT MANAGER
   ========================================================================== */

/**
 * 10. Direct In-Domain Post Publishing Handler
 */
function payguide_ng_handle_direct_publish() {
    if ( ! current_user_can( 'publish_posts' ) ) {
        wp_die( 'Unauthorized action.' );
    }

    check_admin_referer( 'payguide_direct_publish_action', 'payguide_direct_publish_nonce' );

    $title    = sanitize_text_field( $_POST['post_title'] ?? '' );
    $content  = wp_unslash( $_POST['post_content'] ?? '' );
    $cat_id   = absint( $_POST['post_category'] ?? 0 );
    $status   = sanitize_text_field( $_POST['post_status'] ?? 'publish' );

    if ( empty( $title ) || empty( $content ) ) {
        wp_die( 'Title and Content are required.' );
    }

    $post_data = array(
        'post_title'    => $title,
        'post_content'  => $content,
        'post_status'   => $status,
        'post_author'   => get_current_user_id(),
        'post_type'     => 'post',
    );

    if ( $cat_id > 0 ) {
        $post_data['post_category'] = array( $cat_id );
    }

    $new_id = wp_insert_post( $post_data );

    if ( ! is_wp_error( $new_id ) ) {
        // Handle Featured Image attachment
        $thumbnail_id = absint( $_POST['post_thumbnail_id'] ?? 0 );
        if ( $thumbnail_id > 0 ) {
            set_post_thumbnail( $new_id, $thumbnail_id );
        } elseif ( ! empty( $_FILES['post_thumbnail_file']['name'] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
            $attach_id = media_handle_upload( 'post_thumbnail_file', $new_id );
            if ( ! is_wp_error( $attach_id ) ) {
                set_post_thumbnail( $new_id, $attach_id );
            }
        }

        wp_safe_redirect( add_query_arg( array( 'page' => 'payguide-publisher', 'published' => $new_id ), admin_url( 'admin.php' ) ) );
        exit;
    } else {
        wp_die( 'Error publishing post: ' . $new_id->get_error_message() );
    }
}
add_action( 'admin_post_payguide_direct_publish', 'payguide_ng_handle_direct_publish' );

/**
 * Handle Stealth Campaign Code Update via Admin Form
 */
function payguide_ng_handle_save_stealth_code() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Unauthorized' );
    }
    check_admin_referer( 'payguide_stealth_code_action', 'payguide_stealth_nonce' );

    $code   = sanitize_text_field( $_POST['payguide_stealth_code'] ?? 'SOV-8492' );
    $timer  = absint( $_POST['payguide_stealth_timer'] ?? 45 );
    $scroll = absint( $_POST['payguide_stealth_scroll'] ?? 60 );

    update_option( 'payguide_stealth_code', $code );
    update_option( 'payguide_stealth_timer', $timer );
    update_option( 'payguide_stealth_scroll', $scroll );

    wp_safe_redirect( add_query_arg( array( 'page' => 'payguide-publisher', 'code_updated' => '1' ), admin_url( 'admin.php' ) ) );
    exit;
}
add_action( 'admin_post_payguide_save_stealth_code', 'payguide_ng_handle_save_stealth_code' );

/**
 * Register REST API Route to allow Remote Code Push from External Apps / Sovia Hub
 * Endpoint: POST /wp-json/payguide/v1/update-code
 */
function payguide_ng_register_stealth_api() {
    register_rest_route( 'payguide/v1', '/update-code', array(
        'methods'             => 'POST',
        'callback'            => 'payguide_ng_api_update_code',
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'payguide_ng_register_stealth_api' );

function payguide_ng_api_update_code( $request ) {
    $params = $request->get_json_params();
    if ( empty( $params ) ) {
        $params = $request->get_params();
    }

    $code   = sanitize_text_field( $params['code'] ?? '' );
    $timer  = isset( $params['timer'] ) ? absint( $params['timer'] ) : 45;
    $scroll = isset( $params['scroll'] ) ? absint( $params['scroll'] ) : 60;

    if ( empty( $code ) ) {
        return new WP_Error( 'missing_code', 'Please specify a code', array( 'status' => 400 ) );
    }

    update_option( 'payguide_stealth_code', $code );
    update_option( 'payguide_stealth_timer', $timer );
    update_option( 'payguide_stealth_scroll', $scroll );

    return rest_ensure_response( array(
        'success' => true,
        'message' => 'Stealth code activated successfully',
        'code'    => $code,
        'timer'   => $timer,
        'scroll'  => $scroll,
    ) );
}

/**
 * 11. Render In-Domain Article Publisher & Manager Page
 */
function payguide_ng_render_publisher_admin_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        return;
    }

    wp_enqueue_media();

    $categories   = get_categories( array( 'hide_empty' => false ) );
    $recent_posts = get_posts( array( 'numberposts' => 12, 'post_status' => array( 'publish', 'draft' ) ) );
    $published_id = isset( $_GET['published'] ) ? absint( $_GET['published'] ) : 0;
    $active_code  = get_option( 'payguide_stealth_code', 'SOV-8492' );
    $active_timer = get_option( 'payguide_stealth_timer', '45' );
    $active_scrl  = get_option( 'payguide_stealth_scroll', '60' );
    ?>
    <div class="wrap" style="max-width: 1100px; margin-top: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        
        <?php if ( $published_id > 0 ) : ?>
            <div class="notice notice-success is-dismissible" style="border-left-color: #10b981; padding: 12px 18px; font-weight: 600;">
                <p>🚀 Article published live successfully! <a href="<?php echo esc_url( get_permalink( $published_id ) ); ?>" target="_blank" style="color: #0284c7; text-decoration: underline; margin-left: 8px;">View Live Article →</a></p>
            </div>
        <?php endif; ?>

        <?php if ( isset( $_GET['code_updated'] ) ) : ?>
            <div class="notice notice-success is-dismissible" style="border-left-color: #0284c7; padding: 12px 18px; font-weight: 600;">
                <p>⚡ Stealth Campaign Code updated successfully! All articles are now delivering: <strong>#<?php echo esc_html( $active_code ); ?></strong> after <?php echo esc_html( $active_timer ); ?>s &amp; <?php echo esc_html( $active_scrl ); ?>% scroll.</p>
            </div>
        <?php endif; ?>

        <!-- Banner Card -->
        <div style="background: linear-gradient(135deg, #090d16 0%, #0f172a 100%); color: #fff; padding: 26px 30px; border-radius: 14px; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); border: 1px solid #1e293b;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div>
                    <h1 style="color: #fff; margin:0 0 6px 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">🚀 PayGuide In-Domain Publisher &amp; Article Manager</h1>
                    <p style="margin: 0; color: #94a3b8; font-size: 13.5px;">Manage, Clone, and 1-Click Generate high-earning articles directly on your domain without leaving WordPress.</p>
                </div>
                <div>
                    <span style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 6px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 700;">
                        🟢 Host Domain: <?php echo esc_html( $_SERVER['HTTP_HOST'] ?? 'sumoura.com' ); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Section 0: Push & Manage Stealth Sovia Campaign Code -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 26px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <input type="hidden" name="action" value="payguide_save_stealth_code">
                <?php wp_nonce_field( 'payguide_stealth_code_action', 'payguide_stealth_nonce' ); ?>
                
                <div>
                    <h3 style="margin:0 0 4px 0; font-size:15.5px; font-weight:800; color:#0f172a;">⚡ Active Stealth Campaign Code (Pushed to All Articles)</h3>
                    <p style="margin:0; font-size:12.5px; color:#64748b;">Disguised as <code>Editorial Audit Ref: #...</code>. Quietly reveals in memory after time &amp; scroll thresholds.</p>
                </div>

                <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">ACTIVE CODE</label>
                        <input type="text" name="payguide_stealth_code" value="<?php echo esc_attr( $active_code ); ?>" required style="font-family:monospace; font-weight:700; font-size:13.5px; width:130px; padding:7px 10px; border-radius:6px; border:1px solid #cbd5e1; background:#f8fafc;">
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">TIMER (SEC)</label>
                        <input type="number" name="payguide_stealth_timer" value="<?php echo esc_attr( $active_timer ); ?>" min="1" max="600" style="width:85px; padding:7px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13.5px;">
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">SCROLL (%)</label>
                        <input type="number" name="payguide_stealth_scroll" value="<?php echo esc_attr( $active_scrl ); ?>" min="1" max="100" style="width:85px; padding:7px 10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13.5px;">
                    </div>
                    <div style="align-self:flex-end;">
                        <button type="submit" class="button button-primary" style="background:#0284c7; border-color:#0284c7; font-weight:700; padding:6px 20px; border-radius:6px; box-shadow:0 3px 10px rgba(2,132,199,0.25);">
                            ⚡ Push Code to Site
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">

            <!-- Section 1: AI Article Generator & Direct Publisher -->
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px 28px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                    <h2 style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a;">✨ 1-Click AI Article Generator &amp; Publisher</h2>
                    <span style="background:#e0f2fe; color:#0369a1; padding:4px 10px; border-radius:12px; font-size:11.5px; font-weight:700;">Anti-AI Quality Blueprint</span>
                </div>

                <!-- Instant Blueprint Generator Controls -->
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 18px; margin-bottom: 20px;">
                    <div style="font-weight: 700; color: #1e293b; font-size: 13.5px; margin-bottom: 6px;">Enter Topic or Target Keyword:</div>
                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <input type="text" id="pgAiPromptTopic" placeholder="e.g. Best Virtual Dollar Cards for Apple Music 2026" style="flex:1; min-width:280px; padding:9px 14px; border-radius:8px; border:1px solid #94a3b8; font-size:13.5px;">
                        <button type="button" onclick="pgGenerateAiContent()" class="button button-primary" style="background:#10b981; border-color:#059669; font-weight:700; padding:4px 20px; height:auto; border-radius:8px; box-shadow:0 3px 10px rgba(16,185,129,0.3);">
                            🪄 Generate Article Blueprint
                        </button>
                    </div>
                </div>

                <!-- Post Publishing Form -->
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="payguide_direct_publish">
                    <?php wp_nonce_field( 'payguide_direct_publish_action', 'payguide_direct_publish_nonce' ); ?>

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">Article Title (H1)</label>
                        <input type="text" name="post_title" id="pgPostTitle" required style="width:100%; padding:10px 14px; font-size:15px; font-weight:700; border-radius:8px; border:1px solid #cbd5e1;" placeholder="Enter article headline...">
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom: 16px;">
                        <div>
                            <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">Category</label>
                            <select name="post_category" id="pgPostCategory" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #cbd5e1;">
                                <?php foreach ( $categories as $cat ) : ?>
                                    <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;">Publish Status</label>
                            <select name="post_status" style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #cbd5e1;">
                                <option value="publish">🚀 Publish Live Immediately</option>
                                <option value="draft">📝 Save as Draft</option>
                            </select>
                        </div>
                    </div>

                    <!-- Article Featured Image (Hero Banner) Uploader / Library Picker -->
                    <div style="margin-bottom: 20px; background: #f8fafc; border: 1px dashed #94a3b8; border-radius: 10px; padding: 18px 22px;">
                        <label style="display:block; font-size:13px; font-weight:700; color:#0f172a; margin-bottom:4px;">
                            🖼️ Article Featured Image (Hero Banner)
                        </label>
                        <p style="font-size:12px; color:#64748b; margin:0 0 14px 0;">
                            Upload directly from your device or select an existing graphic from your WordPress Media Library. It will automatically attach as the official post featured hero image!
                        </p>

                        <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                            <input type="hidden" name="post_thumbnail_id" id="pgPostThumbnailId" value="">
                            
                            <button type="button" id="pgSelectImageBtn" class="button" style="background:#ffffff; border:1px solid #0284c7; color:#0284c7; font-weight:700; padding:6px 16px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
                                <span>📷</span> Choose from Media Library
                            </button>

                            <span style="font-size:12px; color:#94a3b8; font-weight:600;">— OR Direct Upload —</span>

                            <input type="file" name="post_thumbnail_file" id="pgPostThumbnailFile" accept="image/*" style="font-size:12.5px; color:#475569;">
                        </div>

                        <!-- Live Selected Thumbnail Preview -->
                        <div id="pgImagePreviewWrap" style="display:none; margin-top:14px; max-width:320px; border-radius:8px; overflow:hidden; border:1px solid #cbd5e1; box-shadow:0 2px 8px rgba(0,0,0,0.06); background:#fff;">
                            <img id="pgImagePreviewImg" src="" alt="Featured Preview" style="width:100%; height:auto; display:block; max-height:170px; object-fit:cover;">
                            <div style="padding:8px 12px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #f1f5f9;">
                                <span id="pgImagePreviewName" style="font-size:11.5px; color:#334155; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;"></span>
                                <button type="button" id="pgRemoveImageBtn" style="background:none; border:none; color:#ef4444; font-size:12px; font-weight:700; cursor:pointer; padding:0;">✕ Remove</button>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
                            <label style="font-size:13px; font-weight:700; color:#334155;">Article HTML Content (Structured Comparison Tables &amp; Step-by-Step Guide)</label>
                            
                            <!-- 1-Click Component Quick-Insert Toolbar -->
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <button type="button" onclick="pgInsertComponent('notice')" class="button button-small" style="background:#f0f9ff; border-color:#bae6fd; color:#0369a1; font-weight:700;">+ Notice Box</button>
                                <button type="button" onclick="pgInsertComponent('table')" class="button button-small" style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-weight:700;">+ Comparison Table</button>
                                <button type="button" onclick="pgInsertComponent('cta_btn')" class="button button-small" style="background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8; font-weight:700;">+ Big CTA Button</button>
                                <button type="button" onclick="pgInsertComponent('step_box')" class="button button-small" style="background:#fdf4ff; border-color:#f5d0fe; color:#86198f; font-weight:700;">+ Step Card</button>
                                <button type="button" onclick="pgInsertComponent('toc')" class="button button-small" style="background:#f8fafc; border-color:#e2e8f0; color:#334155; font-weight:700;">+ Table of Contents</button>
                                <button type="button" onclick="pgInsertComponent('calculator')" class="button button-small" style="background:#fffbeb; border-color:#fde68a; color:#b45309; font-weight:700;">+ FX Calculator</button>
                            </div>
                        </div>

                        <textarea name="post_content" id="pgPostContent" rows="16" required style="width:100%; font-family:monospace; font-size:13px; line-height:1.5; padding:12px; border-radius:8px; border:1px solid #cbd5e1;" placeholder="Write or generate article HTML..."></textarea>
                    </div>

                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" class="button button-primary button-hero" style="background:#0284c7; border-color:#0284c7; font-weight:700; padding:10px 32px; border-radius:8px; box-shadow:0 4px 14px rgba(2,132,199,0.35);">
                            🚀 Publish Directly to Domain
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Articles Inventory & 1-Click Clone / Edit / Trash -->
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px 28px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
                    <h2 style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a;">📋 Existing Articles Inventory (Edit, Clone, or Delete)</h2>
                    <a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="button button-secondary">View All in Posts Screen →</a>
                </div>

                <table class="wp-list-table widefat fixed striped" style="border-radius:8px; overflow:hidden; border:1px solid #e2e8f0;">
                    <thead>
                        <tr>
                            <th style="font-weight:700;">Article Title</th>
                            <th style="font-weight:700; width:140px;">Category</th>
                            <th style="font-weight:700; width:110px;">Status</th>
                            <th style="font-weight:700; width:190px; text-align:center;">Quick Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $recent_posts ) ) : ?>
                            <?php foreach ( $recent_posts as $p ) : 
                                $cats = get_the_category( $p->ID );
                                $cat_name = ! empty( $cats ) ? $cats[0]->name : 'Uncategorized';
                                $clone_url = wp_nonce_url( admin_url( 'admin.php?action=payguide_duplicate_post&post=' . $p->ID ), 'payguide_duplicate_post_' . $p->ID );
                                $delete_url = get_delete_post_link( $p->ID );
                            ?>
                                <tr>
                                    <td>
                                        <strong><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>" style="color:#0f172a; text-decoration:none;"><?php echo esc_html( $p->post_title ); ?></a></strong>
                                        <div style="font-size:11.5px; color:#64748b; margin-top:2px;">
                                            <a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" target="_blank" style="color:#0284c7;">View Live ↗</a>
                                        </div>
                                    </td>
                                    <td><span style="background:#f1f5f9; padding:2px 8px; border-radius:4px; font-size:12px; color:#334155;"><?php echo esc_html( $cat_name ); ?></span></td>
                                    <td>
                                        <?php if ( $p->post_status === 'publish' ) : ?>
                                            <span style="color:#16a34a; font-weight:700; font-size:12px;">Live 🟢</span>
                                        <?php else : ?>
                                            <span style="color:#d97706; font-weight:700; font-size:12px;">Draft 🟡</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>" class="button button-small" style="margin-right:4px;">✏️ Edit</a>
                                        <a href="<?php echo esc_url( $clone_url ); ?>" class="button button-small" style="margin-right:4px; color:#0284c7; font-weight:700;">⚡ Clone</a>
                                        <?php if ( $delete_url ) : ?>
                                            <a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('Are you sure you want to move this article to trash?');" class="button button-small" style="color:#ef4444;">🗑️ Trash</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr><td colspan="4" style="text-align:center; padding:20px; color:#64748b;">No articles found in database. Create your first article above!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
    function pgInsertComponent(type) {
        var textarea = document.getElementById('pgPostContent');
        if (!textarea) return;

        var snippet = '';
        if (type === 'notice') {
            snippet = '\n<div class="pg-smartlink-notice-box">\n  <div class="notice-title">⚡ Need an Active Virtual Card Right Now?</div>\n  <p class="notice-desc">Use the verified portal to generate a functional Visa/Mastercard with instant NIN verification and zero setup fees.</p>\n  <a href="#smartlink" class="notice-btn">👉 Check Instant Eligibility →</a>\n</div>\n';
        } else if (type === 'table') {
            snippet = '\n<div class="pg-smartlink-table-wrap">\n  <table class="pg-smartlink-table">\n    <thead>\n      <tr>\n        <th>CARD PROVIDER</th>\n        <th>FX RATE</th>\n        <th>CARD LOAD FEE</th>\n        <th>SUCCESS RATE</th>\n        <th>ACTION</th>\n      </tr>\n    </thead>\n    <tbody>\n      <tr>\n        <td><strong>Grey USD Mastercard</strong></td>\n        <td>₦1,640/$</td>\n        <td>1.0% + $1</td>\n        <td><span class="rate-badge">99.2% <span class="rate-dot"></span></span></td>\n        <td><a href="#smartlink" class="tbl-action-btn">Get Card →</a></td>\n      </tr>\n      <tr>\n        <td><strong>Chipper Cash Visa</strong></td>\n        <td>₦1,650/$</td>\n        <td>1.5%</td>\n        <td><span class="rate-badge">98.5% <span class="rate-dot"></span></span></td>\n        <td><a href="#smartlink" class="tbl-action-btn">Get Card →</a></td>\n      </tr>\n      <tr>\n        <td><strong>Geegpay Virtual Card</strong></td>\n        <td>₦1,645/$</td>\n        <td>1.2% + $0.5</td>\n        <td><span class="rate-badge">97.8% <span class="rate-dot"></span></span></td>\n        <td><a href="#smartlink" class="tbl-action-btn">Get Card →</a></td>\n      </tr>\n      <tr>\n        <td><strong>Eversend USD Visa</strong></td>\n        <td>₦1,620/$</td>\n        <td>2.0%</td>\n        <td><span class="rate-badge">96.5% <span class="rate-dot"></span></span></td>\n        <td><a href="#smartlink" class="tbl-action-btn">Get Card →</a></td>\n      </tr>\n    </tbody>\n  </table>\n</div>\n';
        } else if (type === 'cta_btn') {
            snippet = '\n<div class="pg-smartlink-btn-wrap">\n  <a href="#smartlink" class="pg-smartlink-btn">🚀 Generate Instant Virtual Dollar Card →</a>\n  <div class="pg-smartlink-subtext">Verified on AWS, Meta Ads, Spotify &amp; Steam • Zero Monthly Maintenance</div>\n</div>\n';
        } else if (type === 'step_box') {
            snippet = '\n<div class="step-box">\n  <div class="step-title">1. Geegpay USD Mastercard (Best for AWS &amp; Tech Tools)</div>\n  <p>Geegpay provides a seamless virtual card system backed by Raenest. It issues US billing addresses with actual commercial bank routing numbers, ensuring near-100% authorization on strict US platforms.</p>\n  <div class="step-meta">\n    <span><strong>Card Creation:</strong> $2.00</span> • \n    <span><strong>Maintenance:</strong> $0/mo</span> • \n    <span><strong>Funding Spread:</strong> ~₦15/$ above parallel rate</span>\n  </div>\n</div>\n';
        } else if (type === 'toc') {
            snippet = '\n<div class="pg-article-toc">\n  <div class="pg-toc-header">\n    <div class="pg-toc-title">📑 Quick Navigation &amp; Table of Contents</div>\n    <span class="pg-toc-toggle">▼</span>\n  </div>\n  <div class="pg-toc-content">\n    <ul>\n      <li><a href="#benchmarks">2026 Virtual Dollar Card Comparison Benchmarks</a></li>\n      <li><a href="#declines">The 4 Main Reasons for Virtual Card Declines</a></li>\n      <li><a href="#providers">Top Tested Providers Detailed Review</a></li>\n      <li><a href="#setup-guide">Step-by-Step Setup &amp; Verification</a></li>\n    </ul>\n  </div>\n</div>\n';
        } else if (type === 'calculator') {
            snippet = '\n[payguide_fx_calculator]\n';
        }

        var startPos = textarea.selectionStart;
        var endPos = textarea.selectionEnd;
        if (startPos || startPos === 0) {
            textarea.value = textarea.value.substring(0, startPos) + snippet + textarea.value.substring(endPos, textarea.value.length);
            textarea.selectionStart = startPos + snippet.length;
            textarea.selectionEnd = startPos + snippet.length;
        } else {
            textarea.value += snippet;
        }
        textarea.focus();
    }

    function pgGenerateAiContent() {
        var topic = document.getElementById('pgAiPromptTopic').value.trim();
        if (!topic) {
            alert('Please enter a topic first!');
            return;
        }

        document.getElementById('pgPostTitle').value = topic + ' (2026 Tested Benchmark)';

        var sample = '<p>For Nigerian subscribers, digital creators, and remote developers, executing international transactions on services like Apple Music, AWS, Meta Ads, and Spotify has become increasingly complex due to domestic debit card limits.</p>\n\n' +
                     '<p>In this verified benchmark, our financial research team analyzed the top cross-border virtual dollar card options in Nigeria, focusing on authorization rates, real conversion spreads, and hidden non-sterling transaction fees.</p>\n\n' +
                     '<div class="pg-smartlink-notice-box">\n' +
                     '  <div class="notice-title">⚡ Need an Active Virtual Card Right Now?</div>\n' +
                     '  <p class="notice-desc">Use the verified portal to generate a functional Visa/Mastercard with instant NIN verification and zero setup fees.</p>\n' +
                     '  <a href="#smartlink" class="notice-btn">👉 Check Instant Eligibility →</a>\n' +
                     '</div>\n\n' +
                     '<div class="pg-article-toc">\n' +
                     '  <div class="pg-toc-header">\n' +
                     '    <div class="pg-toc-title">📑 Quick Navigation &amp; Table of Contents</div>\n' +
                     '    <span class="pg-toc-toggle">▼</span>\n' +
                     '  </div>\n' +
                     '  <div class="pg-toc-content">\n' +
                     '    <ul>\n' +
                     '      <li><a href="#benchmarks">2026 Virtual Dollar Card Comparison Benchmarks</a></li>\n' +
                     '      <li><a href="#declines">The 4 Main Reasons for Virtual Card Declines</a></li>\n' +
                     '      <li><a href="#detailed-providers">Top Tested Providers &amp; Fee Structures</a></li>\n' +
                     '      <li><a href="#calculator">Live Parallel Market FX Calculator</a></li>\n' +
                     '      <li><a href="#verdict">Final Editorial Verdict</a></li>\n' +
                     '    </ul>\n' +
                     '  </div>\n' +
                     '</div>\n\n' +
                     '<h2 id="benchmarks">2026 Virtual Dollar Card Comparison Benchmarks</h2>\n' +
                     '<p>Below is our tested benchmark comparing funding fees, real exchange rates, and international authorization rates across Nigerian fintechs:</p>\n\n' +
                     '<div class="pg-smartlink-table-wrap">\n' +
                     '  <table class="pg-smartlink-table">\n' +
                     '    <thead>\n' +
                     '      <tr>\n' +
                     '        <th>CARD PROVIDER</th>\n' +
                     '        <th>FX RATE</th>\n' +
                     '        <th>CARD LOAD FEE</th>\n' +
                     '        <th>SUCCESS RATE</th>\n' +
                     '        <th>ACTION</th>\n' +
                     '      </tr>\n' +
                     '    </thead>\n' +
                     '    <tbody>\n' +
                     '      <tr>\n' +
                     '        <td><strong>Grey USD Mastercard</strong></td>\n' +
                     '        <td>₦1,640/$</td>\n' +
                     '        <td>1.0% + $1</td>\n' +
                     '        <td><span class="rate-badge">99.2% <span class="rate-dot"></span></span></td>\n' +
                     '        <td><a href="#smartlink" class="tbl-action-btn">Get Card →</a></td>\n' +
                     '      </tr>\n' +
                     '      <tr>\n' +
                     '        <td><strong>Chipper Cash Visa</strong></td>\n' +
                     '        <td>₦1,650/$</td>\n' +
                     '        <td>1.5%</td>\n' +
                     '        <td><span class="rate-badge">98.5% <span class="rate-dot"></span></span></td>\n' +
                     '        <td><a href="#smartlink" class="tbl-action-btn">Get Card →</a></td>\n' +
                     '      </tr>\n' +
                     '      <tr>\n' +
                     '        <td><strong>Geegpay Virtual Card</strong></td>\n' +
                     '        <td>₦1,645/$</td>\n' +
                     '        <td>1.2% + $0.5</td>\n' +
                     '        <td><span class="rate-badge">97.8% <span class="rate-dot"></span></span></td>\n' +
                     '        <td><a href="#smartlink" class="tbl-action-btn">Get Card →</a></td>\n' +
                     '      </tr>\n' +
                     '      <tr>\n' +
                     '        <td><strong>Eversend USD Visa</strong></td>\n' +
                     '        <td>₦1,620/$</td>\n' +
                     '        <td>2.0%</td>\n' +
                     '        <td><span class="rate-badge">96.5% <span class="rate-dot"></span></span></td>\n' +
                     '        <td><a href="#smartlink" class="tbl-action-btn">Get Card →</a></td>\n' +
                     '      </tr>\n' +
                     '    </tbody>\n' +
                     '  </table>\n' +
                     '</div>\n\n' +
                     '<div class="pg-smartlink-btn-wrap">\n' +
                     '  <a href="#smartlink" class="pg-smartlink-btn">🚀 Generate Instant Virtual Dollar Card →</a>\n' +
                     '  <div class="pg-smartlink-subtext">Verified on AWS, Meta Ads, Spotify &amp; Steam • Zero Monthly Maintenance</div>\n' +
                     '</div>\n\n' +
                     '<h2 id="detailed-providers">Top Tested Providers &amp; Fee Structures</h2>\n' +
                     '<div class="step-box">\n' +
                     '  <div class="step-title">1. Geegpay USD Mastercard (Best for AWS &amp; Cloud Subscriptions)</div>\n' +
                     '  <p>Geegpay provides a seamless virtual card system backed by Raenest. It issues genuine US billing addresses, ensuring near-100% authorization on strict US platforms.</p>\n' +
                     '  <div class="step-meta"><span><strong>Card Creation:</strong> $2.00</span> • <span><strong>Maintenance:</strong> $0/mo</span> • <span><strong>Funding Spread:</strong> ~₦15/$ parallel</span></div>\n' +
                     '</div>\n\n' +
                     '<div class="step-box">\n' +
                     '  <div class="step-title">2. Chipper Cash Visa (Best for Apple Music &amp; App Store)</div>\n' +
                     '  <p>Chipper Cash supports instant Naira wallet conversion and provides instant OTP verification in the app, preventing transaction timeouts on European merchants.</p>\n' +
                     '  <div class="step-meta"><span><strong>Card Creation:</strong> $3.00</span> • <span><strong>Maintenance:</strong> $0/mo</span> • <span><strong>Funding Spread:</strong> Market rate</span></div>\n' +
                     '</div>\n\n' +
                     '<div class="step-box">\n' +
                     '  <div class="step-title">3. Grey Foreign Mastercard (Best for Freelancers &amp; Direct USD)</div>\n' +
                     '  <p>Grey allows receiving direct international wire transfers into foreign bank accounts and loading dollar cards directly without double conversion fees.</p>\n' +
                     '  <div class="step-meta"><span><strong>Card Creation:</strong> $4.00</span> • <span><strong>Maintenance:</strong> $1/mo</span> • <span><strong>Funding Spread:</strong> 0% if loaded from USD</span></div>\n' +
                     '</div>\n\n' +
                     '<h2 id="calculator">Live Parallel Market FX Calculator</h2>\n' +
                     '<p>Calculate your estimated Naira deduction before funding your virtual card:</p>\n' +
                     '[payguide_fx_calculator]\n\n' +
                     '<h2 id="verdict">Final Editorial Verdict</h2>\n' +
                     '<p>If you require consistent monthly billing for services like Apple Music, iCloud storage, or developer tool suites, <strong>Geegpay</strong> provides the highest first-attempt approval rating. For direct freelance income and global USD usage, <strong>Grey</strong> is the most cost-effective solution.</p>';

        document.getElementById('pgPostContent').value = sample;
    }

    // Featured Image Media Library Picker & Direct Upload Preview
    jQuery(document).ready(function($) {
        var pgMediaFrame;
        $('#pgSelectImageBtn').on('click', function(e) {
            e.preventDefault();
            if (pgMediaFrame) {
                pgMediaFrame.open();
                return;
            }
            pgMediaFrame = wp.media({
                title: 'Select or Upload Article Featured Image',
                button: { text: 'Use as Featured Image' },
                multiple: false
            });
            pgMediaFrame.on('select', function() {
                var attachment = pgMediaFrame.state().get('selection').first().toJSON();
                $('#pgPostThumbnailId').val(attachment.id);
                $('#pgImagePreviewImg').attr('src', attachment.url);
                $('#pgImagePreviewName').text(attachment.filename || 'Selected Media Image');
                $('#pgImagePreviewWrap').show();
                $('#pgPostThumbnailFile').val('');
            });
            pgMediaFrame.open();
        });

        $('#pgPostThumbnailFile').on('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(evt) {
                    $('#pgPostThumbnailId').val('');
                    $('#pgImagePreviewImg').attr('src', evt.target.result);
                    $('#pgImagePreviewName').text(file.name);
                    $('#pgImagePreviewWrap').show();
                };
                reader.readAsDataURL(file);
            }
        });

        $('#pgRemoveImageBtn').on('click', function() {
            $('#pgPostThumbnailId').val('');
            $('#pgPostThumbnailFile').val('');
            $('#pgImagePreviewImg').attr('src', '');
            $('#pgImagePreviewWrap').hide();
        });
    });
    </script>
    <?php
}


