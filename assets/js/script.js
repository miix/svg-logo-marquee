jQuery(document).ready(function ($) {
  const marquee = document.getElementById("marquee");

  if (marquee) {
    // Define the popover initialization function first
    const initializePopovers = (elements) => {
      elements.forEach((element) => {
        new bootstrap.Popover(element, {
          trigger: "hover",
          placement: "top",
          delay: { show: 100, hide: 2000 },
        });
      });
    };

    const logos = marquee.querySelectorAll(".svg-logo");
    const originalSetCount = logos.length / 3; // Since we now start with 3 sets
    let containerWidth = marquee.offsetWidth;
    let totalWidth = 0;

    // Calculate width of one set
    for (let i = 0; i < originalSetCount; i++) {
      totalWidth +=
        logos[i].offsetWidth + parseInt(getComputedStyle(marquee).gap);
    }

    // Check if more duplication is needed
    const needsDuplication = totalWidth < containerWidth * 4;
    const duplicateEnabled = !marquee
      .closest(".svg-marquee-container")
      .classList.contains("no-duplicate");

    if (needsDuplication && duplicateEnabled) {
      const minimumSets = 4;
      const existingSets = logos.length / originalSetCount;
      const totalSets = Math.max(
        Math.ceil((containerWidth * 4) / totalWidth),
        minimumSets
      );

      // Only add more sets if needed
      const additionalSetsNeeded = totalSets - existingSets;

      if (additionalSetsNeeded > 0) {
        for (let i = 0; i < additionalSetsNeeded; i++) {
          for (let j = 0; j < originalSetCount; j++) {
            const clone = logos[j].cloneNode(true);
            marquee.appendChild(clone);
          }
        }
      }
    }

    // Initialize popovers for all elements
    initializePopovers(document.querySelectorAll('[data-bs-toggle="popover"]'));

    if (
      getComputedStyle(marquee).getPropertyValue("--marquee-direction") ===
      "reverse"
    ) {
      marquee.style.animationDirection = "reverse";
    }

    // Update animation duration based on content width
    const totalContentWidth = marquee.scrollWidth;
    const duration = parseInt(
      getComputedStyle(marquee).getPropertyValue("--marquee-duration")
    );
    if (totalContentWidth > containerWidth) {
      const adjustedDuration = (duration * totalContentWidth) / containerWidth;
      marquee.style.setProperty("--marquee-duration", `${adjustedDuration}ms`);
    }

    // Remove loading class after everything is ready
    requestAnimationFrame(() => {
      marquee.closest(".svg-marquee-container").classList.remove("loading");
    });
  }
});
