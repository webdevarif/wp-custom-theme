// Import GSAP and plugins from npm
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
gsap.registerPlugin(ScrollTrigger);
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;

// SplitType
import SplitType from 'split-type'
window.SplitType = SplitType;