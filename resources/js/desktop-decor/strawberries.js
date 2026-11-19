/**
 * Subtle parallax scroll for flat SVG strawberries in cream sections (>= 1280px only)
 */
export function initStrawberries() {
  if (!window.matchMedia('(min-width: 1280px)').matches) return;

  const elements = document.querySelectorAll('.desktop-parallax-strawberry');
  if (!elements.length) return;

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    // Keep stationary at base rotation
    return;
  }

  let ticking = false;

  const onScroll = () => {
    if (!ticking) {
      requestAnimationFrame(() => {
        elements.forEach((el) => {
          const rect = el.getBoundingClientRect();
          // Only animate when near viewport
          if (rect.top < window.innerHeight && rect.bottom > 0) {
            const speed = parseFloat(el.dataset.speed || '0.1');
            const baseRot = parseFloat(el.dataset.baseRot || '0');
            // Offset relative to viewport center
            const centerDiff = (window.innerHeight / 2) - rect.top;
            const dy = centerDiff * speed * 0.4;
            const rot = baseRot + Math.sin(centerDiff * 0.005) * 4;
            el.style.transform = `translate3d(0, ${dy}px, 0) rotate(${rot}deg)`;
          }
        });
        ticking = false;
      });
      ticking = true;
    }
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}
