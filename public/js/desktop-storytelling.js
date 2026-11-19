/**
 * Daisy Bakery - Interactive GSAP Entrance & Scrubbed Slide Scroll Storytelling
 * Orchestrated for desktop screens (>= 1024px)
 * 
 * Features:
 * - Direct scroll-tied scrub animation: speed adapts to scroll velocity; scrolling back up slides elements back.
 * - Bold, visible, tactile sliding motions (horizontal & vertical slides).
 * - Multi-layer parallax depth on floating bakery objects (strawberries, chef duck, cakes, paper note).
 */
(function () {
  'use strict';

  // Desktop guard: only run on large screens
  if (window.innerWidth < 1024) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function initStorytelling() {
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
      console.warn('[Daisy Storytelling] GSAP or ScrollTrigger not loaded.');
      return;
    }

    gsap.registerPlugin(ScrollTrigger);

    // If user prefers reduced motion, disable motion
    if (prefersReducedMotion) {
      document.querySelectorAll('.desktop-reveal, #taste-desktop-hero').forEach(el => {
        el.style.opacity = '1';
        el.style.transform = 'none';
      });
      return;
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * ACT I: ENTRANCE ANIMATION (On Initial Page Load)
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    const entranceTl = gsap.timeline({
      defaults: { ease: 'power3.out' }
    });

    // 1. Desktop Sticky Header slide down
    const header = document.querySelector('header.hidden.lg\\:flex');
    if (header) {
      entranceTl.fromTo(header,
        { y: -50, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.9, ease: 'power2.out' },
        0
      );
    }

    // 2. Hero outer scalloped brown container slide up & expand
    const heroFrame = document.querySelector('#taste-desktop-hero .rounded-\\[36px\\], #taste-desktop-hero .rounded-\\[44px\\]');
    if (heroFrame) {
      entranceTl.fromTo(heroFrame,
        { y: 40, scale: 0.95, opacity: 0 },
        { y: 0, scale: 1, opacity: 1, duration: 1.1, ease: 'power3.out' },
        0.1
      );
    }

    // 3. Hero rolling pin badge
    const rollingPin = document.querySelector('#taste-desktop-hero img[src*="rolling-pin"]');
    if (rollingPin) {
      entranceTl.fromTo(rollingPin,
        { scale: 0.5, rotation: -25, opacity: 0 },
        { scale: 1, rotation: 0, opacity: 1, duration: 0.8, ease: 'back.out(2)' },
        0.25
      );
    }

    // 4. Hero Headlines: "Freshly Baked Cakes" & "EVERY DAY" slide up
    const heroTitles = document.querySelectorAll('#taste-desktop-hero h1 span, #taste-desktop-hero h1');
    if (heroTitles.length) {
      entranceTl.fromTo(heroTitles,
        { y: 45, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.9, stagger: 0.12, ease: 'power3.out' },
        0.3
      );
    }

    // 5. Hero 3D Cake Container Spring & Rotation Entrance
    const hero3D = document.querySelector('#desktop-hero-3d-wrapper');
    if (hero3D) {
      entranceTl.fromTo(hero3D,
        { scale: 0.7, y: 35, opacity: 0 },
        { scale: 1, y: 0, opacity: 1, duration: 1.2, ease: 'back.out(1.6)' },
        0.35
      );
    }

    // 6. Left Column Copy & Opening Hours Badge slide in from left
    const heroLeftCol = document.querySelector('#taste-desktop-hero .col-span-12.lg\\:col-span-3:first-child');
    if (heroLeftCol) {
      entranceTl.fromTo(heroLeftCol.children,
        { x: -45, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.85, stagger: 0.12, ease: 'power2.out' },
        0.5
      );
    }

    // 7. Right Column Kicker & Explore CTA Button slide in from right
    const heroRightCol = document.querySelector('#taste-desktop-hero .col-span-12.lg\\:col-span-3:last-child');
    if (heroRightCol) {
      entranceTl.fromTo(heroRightCol.children,
        { x: 45, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.85, stagger: 0.12, ease: 'power2.out' },
        0.5
      );
    }

    // 8. Hero 2D Floating Strawberries pop in
    const heroStrawberries = document.querySelectorAll('#taste-desktop-hero .desktop-floating-strawberry');
    if (heroStrawberries.length) {
      entranceTl.fromTo(heroStrawberries,
        { scale: 0, opacity: 0 },
        { scale: 1, opacity: 1, duration: 0.7, stagger: 0.1, ease: 'back.out(2.2)' },
        0.7
      );
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * ACT II: HERO SCROLL SLIDE & 3D PARALLAX (Tied to scroll scrub)
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    if (hero3D) {
      gsap.to(hero3D, {
        y: 110,
        rotation: 1.5,
        ease: 'none',
        scrollTrigger: {
          trigger: '#taste-desktop-hero',
          start: 'top top',
          end: 'bottom top',
          scrub: 1.2
        }
      });
    }

    const heroInner = document.querySelector('#taste-desktop-hero .max-w-7xl');
    if (heroInner) {
      gsap.to(heroInner, {
        y: -50,
        ease: 'none',
        scrollTrigger: {
          trigger: '#taste-desktop-hero',
          start: 'top top',
          end: 'bottom top',
          scrub: 1.2
        }
      });
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * ACT III: SECTION 2 - TASTE SHOWCASE (Interactive Slide-In on Scroll)
     * Card 1 slides from LEFT, Card 2 from BOTTOM, Card 3 from RIGHT!
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    const sectionShowcase = document.querySelector('#taste-desktop-taste-showcase');
    if (sectionShowcase) {
      // Header and category badges slide down from top
      const showcaseHeader = sectionShowcase.querySelector('.text-center');
      if (showcaseHeader) {
        gsap.fromTo(showcaseHeader,
          { y: -60, opacity: 0 },
          {
            y: 0, opacity: 1, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionShowcase,
              start: 'top 92%',
              end: 'top 55%',
              scrub: 1.2
            }
          }
        );
      }

      // The 3 Cards Slide In from Left, Bottom, and Right
      const productCards = sectionShowcase.querySelectorAll('.grid > div');
      if (productCards.length >= 3) {
        // Card 1: Slide from LEFT
        gsap.fromTo(productCards[0],
          { x: -160, opacity: 0, rotation: -3 },
          {
            x: 0, opacity: 1, rotation: 0, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionShowcase.querySelector('.grid'),
              start: 'top 95%',
              end: 'top 50%',
              scrub: 1.2
            }
          }
        );

        // Card 2: Slide from BOTTOM
        gsap.fromTo(productCards[1],
          { y: 130, opacity: 0, scale: 0.92 },
          {
            y: 0, opacity: 1, scale: 1, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionShowcase.querySelector('.grid'),
              start: 'top 95%',
              end: 'top 50%',
              scrub: 1.2
            }
          }
        );

        // Card 3: Slide from RIGHT
        gsap.fromTo(productCards[2],
          { x: 160, opacity: 0, rotation: 3 },
          {
            x: 0, opacity: 1, rotation: 0, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionShowcase.querySelector('.grid'),
              start: 'top 95%',
              end: 'top 50%',
              scrub: 1.2
            }
          }
        );
      }

      // "View All Cakes & Menu" CTA Button slide up
      const viewAllBtn = sectionShowcase.querySelector('a[href*="menu"]');
      if (viewAllBtn) {
        gsap.fromTo(viewAllBtn,
          { y: 50, opacity: 0 },
          {
            y: 0, opacity: 1, ease: 'power2.out',
            scrollTrigger: {
              trigger: viewAllBtn,
              start: 'top 95%',
              end: 'top 75%',
              scrub: 1.2
            }
          }
        );
      }
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * ACT IV: SECTION 3 - BRAND STORY (Storybook Slide Sequence)
     * Chef Duck & Tart slide from LEFT, Photos & Text slide from RIGHT!
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    const sectionStory = document.querySelector('#taste-desktop-brand-story');
    if (sectionStory) {
      const storyContainer = sectionStory.querySelector('.rounded-3xl, .rounded-\\[36px\\]');
      if (storyContainer) {
        gsap.fromTo(storyContainer,
          { y: 80, scale: 0.94, opacity: 0 },
          {
            y: 0, scale: 1, opacity: 1, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionStory,
              start: 'top 90%',
              end: 'top 45%',
              scrub: 1.2
            }
          }
        );
      }

      // Chef Duck Mascot & Floating Tart slide from LEFT
      const duckCol = sectionStory.querySelector('.col-span-12.sm\\:col-span-5');
      if (duckCol) {
        gsap.fromTo(duckCol,
          { x: -180, opacity: 0, rotation: -5 },
          {
            x: 0, opacity: 1, rotation: 0, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionStory,
              start: 'top 85%',
              end: 'top 40%',
              scrub: 1.2
            }
          }
        );
      }

      // Story Text & Interior Photos slide from RIGHT
      const storyRightCol = sectionStory.querySelector('.col-span-12.sm\\:col-span-7');
      if (storyRightCol) {
        gsap.fromTo(storyRightCol,
          { x: 180, opacity: 0 },
          {
            x: 0, opacity: 1, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionStory,
              start: 'top 85%',
              end: 'top 40%',
              scrub: 1.2
            }
          }
        );
      }
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * ACT V: SECTION 4 - PROMO CARDS (Opposing Lateral Slide Scrub)
     * Left card slides from LEFT, Right card slides from RIGHT!
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    const sectionPromo = document.querySelector('#taste-desktop-seasonal-promo');
    if (sectionPromo) {
      const promoCards = sectionPromo.querySelectorAll('.grid > div');
      if (promoCards.length >= 2) {
        // Left Card (Croissant on Cream Texture): Slide from LEFT
        gsap.fromTo(promoCards[0],
          { x: -200, opacity: 0, rotation: -3 },
          {
            x: 0, opacity: 1, rotation: 0, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionPromo,
              start: 'top 92%',
              end: 'top 45%',
              scrub: 1.2
            }
          }
        );

        // Right Card (Mousse on Jam Texture): Slide from RIGHT
        gsap.fromTo(promoCards[1],
          { x: 200, opacity: 0, rotation: 3 },
          {
            x: 0, opacity: 1, rotation: 0, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionPromo,
              start: 'top 92%',
              end: 'top 45%',
              scrub: 1.2
            }
          }
        );

        // Croissant & Mousse cake depth slide forward
        const cutoutFoods = sectionPromo.querySelectorAll('img[alt*="Croissant"], img[alt*="Mousse"]');
        if (cutoutFoods.length) {
          gsap.fromTo(cutoutFoods,
            { y: 35, scale: 0.9 },
            {
              y: -15, scale: 1.05, ease: 'none',
              scrollTrigger: {
                trigger: sectionPromo,
                start: 'top bottom',
                end: 'bottom top',
                scrub: 1.4
              }
            }
          );
        }
      }
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * ACT VI: SECTION 5A - GIANT TICKER & OVERLAID CAKE
     * Text slides horizontally tied to scroll, Cake scales and tilts!
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    const sectionTicker = document.querySelector('#taste-desktop-ticker-cake');
    if (sectionTicker) {
      // Direct scroll-tied horizontal sliding on marquee text track!
      const marqueeTrack = sectionTicker.querySelector('.marquee-track');
      if (marqueeTrack) {
        // Pause standard CSS animation on scroll and let GSAP scrub drive the slide smoothly!
        marqueeTrack.style.animation = 'none';
        gsap.fromTo(marqueeTrack,
          { x: 100 },
          {
            x: -800,
            ease: 'none',
            scrollTrigger: {
              trigger: sectionTicker,
              start: 'top bottom',
              end: 'bottom top',
              scrub: 1.2
            }
          }
        );
      }

      // Overlaid cake depth scale & 3D tilt slide
      const overlaidCake = sectionTicker.querySelector('.cake-float-glow img');
      if (overlaidCake) {
        gsap.fromTo(overlaidCake,
          { scale: 0.82, rotation: -6, y: 40 },
          {
            scale: 1.15, rotation: 6, y: -40, ease: 'none',
            scrollTrigger: {
              trigger: sectionTicker,
              start: 'top bottom',
              end: 'bottom top',
              scrub: 1.4
            }
          }
        );
      }
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * ACT VII: SECTION 5B - STORE LOCATION (Pinned Note Drop & Wall Settle)
     * Background cafe art slides opposite; Pinned Note slides DOWN from top!
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    const sectionReceipt = document.querySelector('#taste-desktop-store-receipt');
    if (sectionReceipt) {
      // Background cafe art cinematic depth parallax
      const bgArt = sectionReceipt.querySelector('img[alt="Bakery Cafe Art"]');
      if (bgArt) {
        gsap.fromTo(bgArt,
          { yPercent: -15, scale: 1.1 },
          {
            yPercent: 15, scale: 1.2, ease: 'none',
            scrollTrigger: {
              trigger: sectionReceipt,
              start: 'top bottom',
              end: 'bottom top',
              scrub: 1.4
            }
          }
        );
      }

      // Pinned Canvas Note slides DOWN from the top as if pinned on the wall!
      const pinnedNote = sectionReceipt.querySelector('.aspect-\\[736\\/920\\]');
      if (pinnedNote) {
        gsap.fromTo(pinnedNote,
          { y: -160, opacity: 0, rotation: -4 },
          {
            y: 0, opacity: 1, rotation: 0, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionReceipt,
              start: 'top 90%',
              end: 'top 45%',
              scrub: 1.2
            }
          }
        );
      }
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * ACT VIII: SECTION 6 - FAQ (Opposing Slide In)
     * Left title slides from LEFT; Accordion cards slide from RIGHT!
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    const sectionFaq = document.querySelector('#taste-desktop-faq');
    if (sectionFaq) {
      // Left headline & CTA: Slide from LEFT
      const faqLeft = sectionFaq.querySelector('.col-span-12.lg\\:col-span-5');
      if (faqLeft) {
        gsap.fromTo(faqLeft,
          { x: -160, opacity: 0 },
          {
            x: 0, opacity: 1, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionFaq,
              start: 'top 90%',
              end: 'top 50%',
              scrub: 1.2
            }
          }
        );
      }

      // Right accordion items: Slide from RIGHT with cascading stagger
      const faqItems = sectionFaq.querySelectorAll('.space-y-4 > div');
      faqItems.forEach((item, i) => {
        gsap.fromTo(item,
          { x: 160 + i * 30, opacity: 0 },
          {
            x: 0, opacity: 1, ease: 'power2.out',
            scrollTrigger: {
              trigger: sectionFaq.querySelector('.space-y-4'),
              start: 'top 92%',
              end: 'top 45%',
              scrub: 1.2
            }
          }
        );
      });
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * ACT IX: WHIMSICAL FLOATING BAKERY OBJECTS & 2D STRAWBERRY SCRUB
     * Slides strawberries vertically and tilts them smoothly on scroll
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    document.querySelectorAll('.desktop-floating-strawberry').forEach((sb, index) => {
      // Skip hero strawberries which have their own entrance
      if (sb.closest('#taste-desktop-hero')) return;

      const direction = index % 2 === 0 ? 1 : -1;
      const travel = direction * (90 + (index * 25) % 60);

      gsap.fromTo(sb,
        { y: -travel, rotation: (index % 2 === 0 ? -18 : 18) },
        {
          y: travel,
          rotation: (index % 2 === 0 ? 18 : -18),
          ease: 'none',
          scrollTrigger: {
            trigger: sb.parentElement || sb,
            start: 'top bottom',
            end: 'bottom top',
            scrub: 1.5
          }
        }
      );
    });

    console.info('[Daisy Storytelling] GSAP Scrubbed Slide Storytelling initialized.');
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStorytelling);
  } else {
    setTimeout(initStorytelling, 50);
  }
})();
