import './bootstrap';

const initScrollAnimations = () => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const animatedElements = document.querySelectorAll('[data-animate]');

  if (!animatedElements.length) {
    return;
  }

  if (prefersReducedMotion) {
    animatedElements.forEach((element) => {
      element.classList.add('opacity-100');
    });
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        const { target } = entry;
        const animation = target.dataset.animate || 'fade-up';
        const delay = target.dataset.animateDelay;

        target.classList.remove('opacity-0', '-translate-y-6');
        target.classList.add(`animate-${animation}`);

        if (delay) {
          target.style.animationDelay = `${delay}s`;
        }

        observer.unobserve(target);
      });
    },
    {
      threshold: 0.25,
      rootMargin: '0px 0px -10% 0px',
    }
  );

  animatedElements.forEach((element) => {
    const animation = element.dataset.animate || 'fade-up';

    element.classList.add('opacity-0');

    if (animation === 'fade-up') {
      element.classList.add('-translate-y-6');
    }

    observer.observe(element);
  });
};

const onReady = (callback) => {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', callback, { once: true });
  } else {
    callback();
  }
};

onReady(() => {
  initScrollAnimations();
});
