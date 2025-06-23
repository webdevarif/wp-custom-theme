export function initAnimations() {
  // === COUNTER ANIMATION ===
  document.querySelectorAll('[data-counter]').forEach(counter => {
    const target = +counter.getAttribute('data-counter');
    let current = 0;
    const increment = Math.ceil(target / 100);
    const updateCounter = () => {
      if (current < target) {
        current += increment;
        if (current > target) current = target;
        counter.textContent = current;
        requestAnimationFrame(updateCounter);
      } else {
        counter.textContent = target;
      }
    };
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) {
          updateCounter();
          observer.disconnect();
        }
      }, { threshold: 0.2 });
      observer.observe(counter);
    } else {
      updateCounter();
    }
  });

  // === ACCORDION ===
  document.querySelectorAll('[data-accordion]').forEach(accordion => {
    accordion.querySelectorAll('[data-accordion-header]').forEach(header => {
      header.addEventListener('click', () => {
        const item = header.closest('[data-accordion-item]');
        const body = item.querySelector('[data-accordion-body]');
        const isOpen = item.classList.contains('open');
        accordion.querySelectorAll('[data-accordion-item]').forEach(i => {
          i.classList.remove('open');
          i.querySelector('[data-accordion-body]').style.height = '0';
        });
        if (!isOpen) {
          item.classList.add('open');
          body.style.height = body.scrollHeight + 'px';
        }
      });
    });
  });

  // === FADE/SLIDE ANIMATIONS ===
  if (window.gsap) {
    document.querySelectorAll('[data-anim]').forEach(el => {
      const animType = el.getAttribute('data-anim') || 'fade-up';
      let props = { opacity: 0, y: 40, duration: 0.8, ease: 'power2.out' };
      if (animType === 'fade-left') props = { opacity: 0, x: -40, duration: 0.8, ease: 'power2.out' };
      if (animType === 'fade-right') props = { opacity: 0, x: 40, duration: 0.8, ease: 'power2.out' };
      if (animType === 'fade-up') props = { opacity: 0, y: 40, duration: 0.8, ease: 'power2.out' };
      if (animType === 'fade-down') props = { opacity: 0, y: -40, duration: 0.8, ease: 'power2.out' };
      window.gsap.from(el, {
        ...props,
        scrollTrigger: {
          trigger: el,
          start: 'top 85%',
          toggleActions: 'play none none none'
        }
      });
    });
  }

  // === CLASS-BASED ANIMATION MODULE ===
  class Animation {
    constructor() {
      this.init();
    }
    init() {
      this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Nokia|Opera Mini/i.test(navigator.userAgent) ? true : false;
      this.detectMobile();
      this.imageAnimation();
      this.fadeAnimation();
      this.splitTextAnimation();
      this.initAccordion();
      this.initCounter();
    }
    start() {}
    stop() { console.log('MainApp stopped.'); }
    detectMobile() { if (this.isMobile) document.body.classList.add('is-mobile'); }
    imageAnimation() {
      this.animateRevealContainers();
      this.animateInviewContainers();
      this.animateParallaxImages();
    }
    animateRevealContainers() {
      document.querySelectorAll('.reveal').forEach(container => {
        const image = container.querySelector('img');
        if (!image) return;
        window.gsap.timeline({
          scrollTrigger: { trigger: container, toggleActions: 'play none none none' },
        })
          .set(container, { autoAlpha: 1 })
          .from(container, 1.5, { xPercent: -100, ease: 'Power2.out' })
          .from(image, 1.5, { xPercent: 100, scale: 1.3, delay: -1.5, ease: 'Power2.out' });
      });
    }
    animateInviewContainers() {
      document.querySelectorAll('.inview').forEach(container => {
        const element = container.querySelector('.inview-wrapper');
        const delayAttr = container.getAttribute('delay') || '0';
        if (!element) return;
        window.gsap.fromTo(element, { scale: 1.2 }, {
          scale: 1,
          duration: 2,
          delay: parseFloat(delayAttr),
          ease: 'power2.out',
          scrollTrigger: { trigger: container, start: 'top bottom' },
          onStart: () => container.classList.add('animate')
        });
      });
    }
    animateParallaxImages() {
      document.querySelectorAll('.parallax-anim img').forEach(image => {
        window.gsap.timeline({
          scrollTrigger: { trigger: image.closest('.parallax-anim'), scrub: 0.5 },
        })
          .from(image, { yPercent: -30, ease: 'none' })
          .to(image, { yPercent: 30, ease: 'none' });
      });
    }
    fadeAnimation() {
      document.querySelectorAll('.fade-anim').forEach(e => {
        let fadeFrom = e.getAttribute('data-fade-from') || 'bottom';
        let onScroll = parseInt(e.getAttribute('data-on-scroll') || '1');
        let duration = parseFloat(e.getAttribute('data-duration') || '0.3');
        let fadeOffset = parseInt(e.getAttribute('data-fade-offset') || '20');
        let rotation = parseFloat(e.getAttribute('data-rotation') || '0');
        let delay = parseFloat(e.getAttribute('data-delay') || '0.15');
        let ease = e.getAttribute('data-ease') || 'power1.out';
        let animationProps = { opacity: 0, ease, rotation, duration, delay };
        switch (fadeFrom) {
          case 'top': animationProps.y = -fadeOffset; break;
          case 'left': animationProps.x = -fadeOffset; break;
          case 'bottom': animationProps.y = fadeOffset; break;
          case 'right': animationProps.x = fadeOffset; break;
        }
        if (onScroll === 1) {
          animationProps.scrollTrigger = { trigger: e, start: 'top 80%' };
        }
        window.gsap.from(e, animationProps);
      });
    }
    splitTextAnimation() {
      const splitTextContainer = document.querySelectorAll('.text-anim');
      const splitWordContainer = document.querySelectorAll('.word-anim');
      const textAnimation = window.gsap.utils.toArray('.textmove-anim');
      const charComeItems = document.querySelectorAll('.char-anim');
      const animateSplitText = (element, config, type = 'words') => {
        const splitText = new window.SplitType(element, { types: 'chars,words,lines', lineClass: 'line' });
        const textToAnimate = type === 'words' ? splitText.words : type === 'chars' ? splitText.chars : splitText.lines;
        window.gsap.from(textToAnimate, { ...config, scrollTrigger: { trigger: element, start: 'top 85%' } });
      };
      const getTranslationValues = (element) => {
        const direction = element.getAttribute('data-direction') || 'horizontal';
        return direction === 'vertical' ? { x: 0, y: 20 } : { x: 20, y: 0 };
      };
      splitTextContainer.forEach((element) => {
        const variant = element.getAttribute('data-variant');
        const { x, y } = getTranslationValues(element);
        const config = { duration: 1, delay: 0.5, x, y, autoAlpha: 0, stagger: 0.05 };
        if (variant === '1') {
          animateSplitText(element, config);
        } else if (variant === '2') {
          animateSplitText(element, { ...config, ease: 'power2.out' }, 'chars');
        } else if (variant === '3') {
          element.animation?.progress(1).kill();
          element.split?.revert();
          element.split = new window.SplitType(element, { types: 'chars,words,lines', lineClass: 'line' });
          window.gsap.set(element, { perspective: 400 });
          window.gsap.set(element.split.chars, { opacity: 0, x: 50 });
          element.animation = window.gsap.to(element.split.chars, {
            x: 0, y: 0, rotateX: 0, opacity: 1, duration: 1, ease: 'back.out', stagger: 0.02,
            scrollTrigger: { trigger: element, start: 'top 90%' },
          });
        }
      });
      splitWordContainer.forEach((element) => {
        const staggerAmount = parseFloat(element.getAttribute('data-stagger') || '0.04');
        const delay = parseFloat(element.getAttribute('data-delay') || '0.1');
        const duration = parseFloat(element.getAttribute('data-duration') || '0.75');
        const { x, y } = getTranslationValues(element);
        const splitText = new window.SplitType(element, { types: 'chars,words' });
        const config = {
          duration, delay, autoAlpha: 0, stagger: staggerAmount, scrollTrigger: { trigger: element, start: 'top 90%' },
          x, y
        };
        window.gsap.from(splitText.words, config);
      });
      textAnimation.forEach((element) => {
        const delay = parseFloat(element.getAttribute('data-delay') || '0.1');
        const timeline = window.gsap.timeline({
          scrollTrigger: {
            trigger: element,
            start: 'top 85%',
            scrub: false,
            markers: false,
            toggleActions: 'play none none none'
          }
        });
        const splitText = new window.SplitType(element, { types: 'lines' });
        window.gsap.set(element, { perspective: 400 });
        timeline.from(splitText.lines, {
          duration: 1,
          delay,
          opacity: 0,
          rotationX: -80,
          force3D: true,
          transformOrigin: 'top center -50',
          stagger: 0.1
        });
      });
      charComeItems.forEach((element) => {
        const staggerAmount = parseFloat(element.getAttribute('data-stagger') || '0.05');
        const delay = parseFloat(element.getAttribute('data-delay') || '0.1');
        const duration = parseFloat(element.getAttribute('data-duration') || '1');
        const ease = element.getAttribute('data-ease') || 'power2.out';
        const { x, y } = getTranslationValues(element);
        const splitText = new window.SplitType(element, { types: 'chars,words' });
        const config = {
          duration,
          delay,
          autoAlpha: 0,
          ease,
          stagger: staggerAmount,
          scrollTrigger: { trigger: element, start: 'top 85%' },
          x, y
        };
        window.gsap.from(splitText.chars, config);
      });
    }
    initAccordion() {
      let r = document.querySelectorAll('.accordion-item'),
        a = document.querySelectorAll('.accordion-itemV4');
      r.forEach(t => {
        let o = t.querySelector('.accordion-header');
        o.addEventListener('click', () => {
          var e = o.classList.contains('open');
          r.forEach(e => {
            var t = e.querySelector('.accordion-header'),
              o = e.querySelector('.accordion-body');
            t.classList.remove('open', 'active'), o.style.height = '0', e.style.borderColor = 'transparent', e.style.paddingBottom = '0';
          });
          e || (o.classList.add('open', 'active'), (e = t.querySelector('.accordion-body')).style.height = e.scrollHeight + 'px', t.style.border = '1px solid black', t.style.paddingBottom = '40px');
        });
      });
      a.forEach((t, o) => {
        let n = t.querySelector('.accordion-headerV4'),
          i = t.querySelector('.accordion-bodyV4');
        t.setAttribute('data-active', 'false'), n.addEventListener('click', () => {
          var r, e = n.classList.toggle('open');
          r = o, a.forEach((e, t) => {
            var o;
            t !== r && (t = e.querySelector('.accordion-headerV4'), o = e.querySelector('.accordion-bodyV4'), t.classList.remove('open', 'active'), o.style.height = '0', o.style.marginBottom = '0', e.setAttribute('data-active', 'false'));
          });
          e ? (i.style.height = i.scrollHeight + 'px', n.classList.add('active'), t.setAttribute('data-active', 'true'), i.style.marginBottom = '20px') : (i.style.height = '0', n.classList.remove('active'), t.setAttribute('data-active', 'false'), i.style.marginBottom = '0');
        });
      });
    }
    initCounter() {
      let r = document.querySelector('#counter');
      if (r) {
        let t = document.querySelectorAll('.counter'),
          o = new IntersectionObserver(e => {
            var [e] = e;
            e.isIntersecting && (t.forEach((r, e) => {
              let n = () => {
                  var e = +r.getAttribute('data-value'),
                    t = +r.innerText || 0,
                    o = Math.ceil(e / 100);
                  t < e && (r.innerText = Math.min(t + o, e), setTimeout(n, 20));
                },
                t = (r.innerText = '0', n(), r.parentElement);
              t.style.opacity = '0', t.style.transform = 'translateY(20px)', setTimeout(() => {
                t.style.transition = 'all 0.5s ease', t.style.opacity = '1', t.style.transform = 'translateY(0)';
              }, 200 * e);
            }), o.unobserve(r));
          }, {
            threshold: .15
          });
        o.observe(r);
      }
    }
  }
  // Create an instance of the Animation class and start the application
  const app = new Animation();
  app.start();
} 