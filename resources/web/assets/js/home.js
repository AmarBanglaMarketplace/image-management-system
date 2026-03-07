import Swiper from "swiper";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import "swiper/css/effect-fade";
import "swiper/css/autoplay";

import {
    Pagination,
    Navigation,
    EffectFade,
    Autoplay
} from "swiper/modules";

/* Initialize all Swiper instances after DOM is fully loaded */
document.addEventListener("DOMContentLoaded", () => {
    /* Banner background swiper (fade effect, synced with content) */
    const swiperBg = new Swiper(".mySwiperBg", {
        modules: [Autoplay, EffectFade, Pagination, Navigation],
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        keyboard: {
            enabled: true
        },
        pagination: {
            el: ".custom-swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".custom-swiper-banner-next",
            prevEl: ".custom-swiper-banner-prev",
        },
        speed: 600,
        effect: "fade",
        allowTouchMove: false,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
    });

    /* Banner content swiper (slide effect, synced with background) */
    const swiperContent = new Swiper(".mySwiper", {
        modules: [Autoplay, EffectFade, Pagination, Navigation],
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        keyboard: {
            enabled: true
        },
        pagination: {
            el: ".custom-swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".custom-swiper-button-next",
            prevEl: ".custom-swiper-button-prev",
        },
        speed: 600,
        effect: "slide",
        allowTouchMove: false,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
    });

    /* Gaming section swiper - responsive grid */
    var gamingSwiper = new Swiper(".myGamingSwiper", {
        modules: [EffectFade, Pagination, Navigation],
        loop: true,
        spaceBetween: 24,
        navigation: {
            nextEl: ".custom-swiper-button-gaming-next",
            prevEl: ".custom-swiper-button-gaming-prev",
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            480: {
                slidesPerView: 2
            },
            768: {
                slidesPerView: 3
            },
            992: {
                slidesPerView: 4
            },
        },
    });

    /* Running events swiper - responsive */
    var runningEventSwiper = new Swiper(".myRunningEventSwiper", {
        modules: [EffectFade, Pagination, Navigation],
        loop: true,
        spaceBetween: 24,
        navigation: {
            nextEl: ".custom-swiper-button-running-next",
            prevEl: ".custom-swiper-button-running-prev",
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            480: {
                slidesPerView: 2
            },
            768: {
                slidesPerView: 3
            },
            992: {
                slidesPerView: 3
            },
            1200: {
                slidesPerView: 4
            },
        },
    });

    /* Upcoming events swiper - responsive */
    var upcomingEventSwiper = new Swiper(".myUpcomingEventSwiper", {
        modules: [EffectFade, Pagination, Navigation],
        loop: true,
        spaceBetween: 24,
        navigation: {
            nextEl: ".custom-swiper-button-upcoming-next",
            prevEl: ".custom-swiper-button-upcoming-prev",
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            480: {
                slidesPerView: 2
            },
            768: {
                slidesPerView: 3
            },
            992: {
                slidesPerView: 3
            },
            1200: {
                slidesPerView: 4
            },
        },
    });

    /* Latest blogs swiper - responsive */
    var latestBlogSwiper = new Swiper(".myLatestBlogSwiper", {
        modules: [EffectFade, Pagination, Navigation],
        loop: true,
        spaceBetween: 24,
        navigation: {
            nextEl: ".custom-swiper-button-latest-next",
            prevEl: ".custom-swiper-button-latest-prev",
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            480: {
                slidesPerView: 2
            },
            768: {
                slidesPerView: 3
            },
            992: {
                slidesPerView: 3
            },
        },
    });

    /* Sync banner navigation buttons between background and content swipers */
    document
        .querySelector(".custom-swiper-button-next")
        .addEventListener("click", () => {
            swiperBg.slideNext();
            swiperContent.slideNext();
        });

    document
        .querySelector(".custom-swiper-button-prev")
        .addEventListener("click", () => {
            swiperBg.slidePrev();
            swiperContent.slidePrev();
        });
});
