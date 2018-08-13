<?php
  // ini_set('display_errors', 1);
  // error_reporting(E_ALL ^ E_NOTICE);

  /**
   * This page is protected by Dartmouth's CAS auth system (uses Apache mod_auth_cas)
   * This section grabs user info from CAS and LDAP to automatically register the
   * user's name and email for annotation provenance. These accounts are not registered
   * on the annotation server, they are only used for attribution.
   */
  require_once('classes/DNDUser.class.php');

  //Get data from LDAP
  $error = '';
  try{
    //CAS returns full.name@DARTMOUTH.EDU as a server var
    $name_only = preg_split("/@/", $_SERVER['REMOTE_USER']);
    $name_only = $name_only[0];
    $user = new DNDUser($name_only);
    $user->do_lookup();
  } catch (Exception $e){
    $error = $e;
  }
  
  //Add constructed attributes and humanize LDAP names
  if($error == ''){
    $user->humanize();
  }
?>
<!doctype html>
<html class="no-js" lang="">
  
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Minimum Viable Annotator</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


    <link rel="stylesheet" href="css/normalize.min.css">
    <link rel="stylesheet" href="css/main.css">
      <!--[if lt IE 9]>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
          <script>window.html5 || document.write('<script src="js/vendor/html5shiv.js"><\/script>')</script>
      <![endif]-->
    <!-- Import JQuery -->
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <!-- Import JQueryUI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- This is loading out of order using grunt so loading manually here - JPB -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.1/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.1/js/select2.min.js"></script>        
    <!-- Annotator Core -->
    <script type="text/javascript" src="js/annotator-frontend.js"></script>
    <link rel="stylesheet" href="css/annotator-frontend.css">
  </head>

  <body>
    <!--[if lte IE 9]>
      <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
    <![endif]-->
    <div class="container">
      <header>
        <div id="title">Minimum Viable Annotator</div>
        <div id="login-box">
          Annotating as <?php echo $user->get_attribute('name'); ?> (<?php echo $user->get_attribute('email'); ?>) <a href="https://login.dartmouth.edu/logout.php?app=MEP&url=http://mediaecology.dartmouth.edu/shanghai_annotator/index.php">Log Out</a>
        </div>
      </header>
      <br clear="both">
      <?php if($error != ''){ ?>
      <section>
        <p>
          Error getting user information from LDAP: <?php echo $error; ?><br>
          Please contact us at <a href="mailto:mep@groups.dartmouth.edu">mep@groups.dartmouth.edu</a>.
        </p>
      </section>      
      <?php } ?>

      <section>
        <div id="page-video">
          <video id="monks-video" width="640" height="480" controls>
          <source src="https://rcweb.dartmouth.edu/MEP/shanghai_video/ChangXiangSi01.mp4" type="video/mp4">
          Your browser does not support the video tag.
          </video>
          <br />
        </div>
        <div id="index-container">
          <h4>Annotation Index</h4>
          <div>
            Filter by tag: <select id="tag-filter"></select><br>
            Filter by author: <select id="author-filter"></select>
          <div class="waldorf-index"></div>
        </div>
      </section>
    </div>
  </body>
  <script>
    // useSimplifiedCharacters = false;
    // waldorf = false;
    // $('#toggle-character-button').click(function(){
    //     useSimplifiedCharacters = !useSimplifiedCharacters;
    //     waldorf.UpdateViews();
    //     updateDisplay(waldorf.infoContainer.$container);
    //     return false;
    // });

    // function nextAnnotation(){
    //     var activeSet = useSimplifiedCharacters ? simplified : traditional;
    //     for(var i=0; i<activeSet.length; i++){
    //         if(activeSet[i].beginTime <= waldorf.player.videoElement.currentTime && 
    //             activeSet[i].endTime >= waldorf.player.videoElement.currentTime) return i;
    //         if(activeSet[i].beginTime >= waldorf.player.videoElement.currentTime) return i;
    //     }
    //     return activeSet.length - 1;
    // }

    // function updateDisplay(){
    //     container = waldorf.infoContainer.$container;
    //     var horizon = 2;
    //     var activeSet = useSimplifiedCharacters ? simplified : traditional;
    //     container.empty();
    //     var $panel = $("<div></div>").appendTo(container);
    //     for(i = currentAnnotation-horizon; i<=currentAnnotation+horizon; i++){
    //         if(i >= 0 && i < activeSet.length){
    //             var $content = $("<span class='waldorf-tag-"+activeSet[i].tags.join(", ")+"' style='font-size: 2em; display: block; margin-bottom: .5em'></span>");
    //             if(i == currentAnnotation && (activeSet[i].beginTime < waldorf.player.videoElement.currentTime &&
    //                activeSet[i].endTime > waldorf.player.videoElement.currentTime)){
    //                 $content.addClass('activeAnnotation');
    //             } 
    //             $content.append(activeSet[i].body.filter(item => item.purpose === "describing")[0].value);
    //             $content.append($('<br>'));
    //             //attach the annotation to the container and return the container
    //             $panel.append($content);
    //         }
    //     }
    //     return $panel;
    // }

    //This is an advanced option to create a custom renderer for annotations
    // var customRenderer = function(annotator, annotation, index){
    //     //This renderer assumes that annotations do not overlap, other than
    //     //equivalent simplified and traditional annotations
    //     //In this case we're only rendering annotations matching a certain tag

    //     //trap for no tags
    //     if(!$.isArray(annotation.tags)) return false;

    //     //only render if this annotation matches the currently displayed set
    //     if(((annotation.tags[0] == "simplified") && useSimplifiedCharacters) || 
    //          ((annotation.tags[0] == "traditional") && !useSimplifiedCharacters)){
    //         //Since the internal annotations array may be unordered, we want to
    //         //use the passed annotation ID as a key to search the simplified or 
    //         //traditional arrays (which are ordered) and render x annotations
    //         //before and after the current one, which will be highlighted.

    //         var key = annotation.id;
    //         var activeSet = useSimplifiedCharacters ? simplified : traditional;
    //         currentAnnotation = activeSet.indexOf(annotation);

    //         return updateDisplay();
    //     }
    // }

    // var customUnrenderer = function(annotator){
    //     currentAnnotation = nextAnnotation();
    //     updateDisplay();
    //     annotator.infoContainer.$container.find('span').removeClass('activeAnnotation');
    // }

    var resetFilters = function(){
      //getAnnotations returns all annotations in order of beginning time
      var annos = waldorf.GetAnnotations();
      var authors = {};
      var tags = [];
      var tagSelect = $('#tag-filter');
      var authorSelect = $('#author-filter');

      tags.push("All tags");
      authors['All Authors'] = 'All Authors';

      for(var a=0; a<annos.length; a++){
        for(var t=0; t<annos[a].tags.length; t++){
          if(tags.indexOf(annos[a].tags[t]) < 0) tags.push(annos[a].tags[t]);
        }
        for(var t=0; t<annos[a].tags.length; t++){
          if(tags.indexOf(annos[a].tags[t]) < 0) tags.push(annos[a].tags[t]);
        }
        if(typeof authors[annos[a].creator.email] === 'undefined') authors[annos[a].creator.email] = annos[a].creator.nickname;
      }
      tags.sort();
      resetTagFilter(tags, tagSelect);
      resetAuthorFilter(authors, authorSelect);

      tagSelect.selectedIndex = 0;
      authorSelect.selectedIndex = 0;
    }

    function resetTagFilter(tags, tagSelect){
      tagSelect.empty();
      for(var i=0; i<tags.length; i++){
        $('<option value="'+tags[i]+'">'+tags[i]+'</option>').appendTo(tagSelect);
      }
    }

    function resetAuthorFilter(authors, authorSelect){
      authorSelect.selectedIndex = 0;
      for(a in authors){
        $('<option value="'+a+'">'+authors[a]+'</option>').appendTo(authorSelect);
      };
    }

    var setListeners = function(_waldorf){
      //Set useful global
      waldorf = _waldorf;

        waldorf.$container.on("OnAnnotationsLoaded", 
          (event) => resetFilters());
        waldorf.$container.on("OnAnnotationRegistered",
          (event) => resetFilters());
        waldorf.$container.on("OnAnnotationRemoved",
          (event) => resetFilters());         
    }

    $('body').ready(function(){
      // This is for entering annotations
      var serverAddress = "http://ec2-18-221-127-156.us-east-2.compute.amazonaws.com:3000";
      var tagsAddress = "https://onomy.org/published/83/json";
      var apiKey = "facc287b-2f51-431d-87ec-773e12302fcf";
      waldorf = $("video").first().annotate({serverURL: serverAddress, 
                                  tagsURL: tagsAddress, 
                                  apiKey: apiKey, 
                                  kioskMode: false,
                                  cmsUsername: "<?php echo $user->get_attribute('name'); ?>",
                                  cmsEmail: "<?php echo $user->get_attribute('email'); ?>",
                                  displayIndex: true,
                                  callback: setListeners});
      $('#tag-filter').on("change", function(){
        var selectedTag = $('#tag-filter').val();
        $('.waldorf-index ul li').show();

        if(selectedTag == 'All tags'){
          return;
        } 

        $('.waldorf-index ul li').filter(function(i){
          return $(this).data('tags').indexOf(selectedTag) < 0;
        }).hide();
      });

      $('#author-filter').on("change",function(){
        var selectedAuthor = $('#author-filter').val();
        $('.waldorf-index ul li').show();

        if(selectedAuthor == 'All Authors') {
          return;
        }
        
        $('.waldorf-index ul li').filter(function(i){
          return $(this).data('creator') != selectedAuthor;
        }).hide();
      });
    });
  </script>   
</html>