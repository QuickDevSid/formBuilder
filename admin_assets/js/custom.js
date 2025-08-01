$(document).ready(function () {

    $(".menu-item").click(function () {
      $(this).next(".submenu").slideToggle(400);
   
  });
  

  $(".menu-item, .submenu-item").hover(
    function () {
      $(this).addClass("hover");
    },
    function () {
      $(this).removeClass("hover");
    }
  );

  $(".menu-item").click(function () {
    $(".menu-item").removeClass("active");
    $(this).addClass("active");
  });

  $(".create-task").click(function (e) {
    e.preventDefault();
    alert("Create task functionality would go here");
  });
});

$(document).ready(function () {
  let manuallyMinimized = false;
  let hoverTimeout;

  $("#toggle-sidebar").click(function (e) {
    e.preventDefault(); // Prevent default link behavior
    $(".sidebar").toggleClass("minimized");
    manuallyMinimized = $(".sidebar").hasClass("minimized");
    if ($(".sidebar").hasClass("minimized")) {
      $(".menu-item").addClass("centered");
      $(".submenu-icon").addClass("large");
      $(".nav-content").fadeOut(300);
      $(".child-menu").slideUp(300);
      // $('.footer-menu').fadeOut(300);
      $(".main-content").addClass("expand");
      $(
        ".fas.fa-chevron-down, .fas.fa-plus, .submenu-item i.fa-caret-down"
      ).fadeOut(200);
    } else {
      $(".menu-item").removeClass("centered");
      $(".submenu-icon").removeClass("large");
      $(".nav-content").fadeIn(300);
      $(".footer-menu").fadeIn(300);
      $(".main-content").removeClass("expand");
      $(
        ".fas.fa-chevron-down, .fas.fa-plus, .submenu-item i.fa-caret-down"
      ).fadeIn(300);
    }
  });
});

$(document).ready(function () {
  $(".submenu-item").click(function () {
    var clickedChildMenu = $(this).find(".child-menu");
    if (clickedChildMenu.is(":visible")) {
      clickedChildMenu.slideUp();
      $(this).find(".fa-caret-down").removeClass("rotated");
      return;
    }

    $(".submenu-item").not(this).find(".child-menu").slideUp();
    $(".submenu-item").not(this).find(".fa-caret-down").removeClass("rotated");
    clickedChildMenu.slideDown();
    $(this).find(".fa-caret-down").addClass("rotated");
  });
});
