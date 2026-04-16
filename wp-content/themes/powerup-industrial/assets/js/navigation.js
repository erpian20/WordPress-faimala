( function() {
  var siteHeader = document.querySelector( '.site-header' );
  var lastScrollY = window.scrollY || window.pageYOffset || 0;
  var ticking = false;
  var hideAfter = 80;

  var syncHeaderScrollState = function() {
    if ( ! siteHeader ) {
      ticking = false;
      return;
    }

    var currentScrollY = window.scrollY || window.pageYOffset || 0;

    if ( currentScrollY > 24 ) {
      siteHeader.classList.add( 'is-scrolled' );
    } else {
      siteHeader.classList.remove( 'is-scrolled' );
    }

    if ( currentScrollY <= 10 ) {
      siteHeader.classList.remove( 'is-hidden' );
    } else if ( currentScrollY > hideAfter && currentScrollY > lastScrollY ) {
      siteHeader.classList.add( 'is-hidden' );
    } else if ( currentScrollY < lastScrollY ) {
      siteHeader.classList.remove( 'is-hidden' );
    }

    lastScrollY = currentScrollY < 0 ? 0 : currentScrollY;
    ticking = false;
  };

  syncHeaderScrollState();
  window.addEventListener( 'scroll', function() {
    if ( ticking ) {
      return;
    }

    window.requestAnimationFrame( syncHeaderScrollState );
    ticking = true;
  }, { passive: true } );

  var menuToggle = document.querySelector( '.menu-toggle' );
  var menu = document.querySelector( '.site-navigation' );
  if ( menuToggle && menu ) {
    menuToggle.addEventListener( 'click', function() {
      var isExpanded = menuToggle.getAttribute( 'aria-expanded' ) === 'true';
      menuToggle.setAttribute( 'aria-expanded', isExpanded ? 'false' : 'true' );
      menu.classList.toggle( 'is-open' );
      menu.classList.toggle( 'nav-open' );
    } );
  }
} )();