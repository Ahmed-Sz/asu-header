
(function ($, Drupal) {
  
  //gold border for active menu
  $(function () {
    setNavigation();
  });

  function setNavigation() {
    //get the url
    var path = window.location.pathname;
    //check if path is mainpage and add border only to home icon
    if(path === "/"){
      $('.nav-link-home').addClass('active');
      return;
    }
    val = true; 
    //for each a in mainmenu. Compare the url
    $(".navbar-nav a").each(function () {
      var href = $(this).attr('href');
      if (href.includes(path)) {
        //if url matches with href make its parent active
        if ( val ){
          val = false;
          $(this).closest('.nav-item').addClass('active');
        }
      }
    });
  }
})(jQuery, Drupal);


//   // $('.menu-link').click(function(){
//   //   console.log("pressed");
//   //   var value = $(this).attr('aria-expanded') == 'false';
//   //   if(value){
//   //     $(this).closest(".menu-item").addClass("show");
//   //     $(this).attr("aria-expanded","true");
//   //     $(this).next().addClass("show");
//   //   }
//   //   else{
//   //     $(this).closest(".menu-item").removeClass("show");
//   //     $(this).attr("aria-expanded","false");
//   //     $(this).next().removeClass("show");
//   //   }
//   // });
// });